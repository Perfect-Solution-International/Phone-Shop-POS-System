<template>
    <section>
        <h1 class="h3 mb-4">{{ definition.title }}</h1>
        <div class="table-toolbar">
            <b-form-input v-model="search" class="search-input" :aria-label="'Search ' + definition.title" placeholder="Search employees?" @input="scheduleSearch" />
            <b-button v-if="canManage" variant="primary" @click="open()">Add record</b-button>
            <b-button variant="outline-secondary" :disabled="!state.rows.length || exporting" @click="exportPage">Export this page (PDF)</b-button>
            <template v-if="module === 'payroll' && canReport">
                <b-button variant="outline-secondary" :disabled="exporting" @click="download('payrollPdf', 'payroll.pdf')">Full report (PDF)</b-button>
                <b-button variant="outline-secondary" :disabled="exporting" @click="download('payrollExcel', 'payroll.xlsx')">Full report (Excel)</b-button>
            </template>
        </div>
        <p v-if="module === 'attendance'" class="small text-muted">Times shown in {{ timezone }}. Date/time form inputs use your device timezone.</p>
        <b-alert :show="Boolean(error)" variant="danger">{{ error }} <b-button size="sm" @click="load">Retry</b-button></b-alert>
        <vue-good-table
            mode="remote" :columns="columns" :rows="state.rows" :total-rows="state.total" :is-loading="state.loading"
            :pagination-options="{ enabled: true, perPage: params.per_page, perPageDropdown: [10, 20, 50, 100], setCurrentPage: params.page }"
            @on-page-change="changePage" @on-per-page-change="changeSize" @on-sort-change="changeSort"
        >
            <div slot="emptystate" class="p-4 text-center text-muted">{{ state.loading ? 'Loading records?' : 'No records found.' }}</div>
            <template slot="table-row" slot-scope="props">
                <span v-if="props.column.field === 'actions'" class="text-nowrap">
                    <b-button v-if="canManage" size="sm" variant="outline-primary" class="mr-1" @click="open(props.row)">Edit</b-button>
                    <b-button v-if="canManage" size="sm" variant="outline-danger" class="mr-1" @click="remove(props.row)">Delete</b-button>
                    <b-button v-if="module === 'employees'" size="sm" variant="outline-secondary" @click="badge = props.row">QR / Barcode</b-button>
                </span>
                <span v-else>{{ props.formattedRow[props.column.field] }}</span>
            </template>
        </vue-good-table>
        <b-modal v-model="editing" :title="(recordId ? 'Edit ' : 'Add ') + definition.title.toLowerCase() + ' record'" hide-footer @hide="preventBusyClose">
            <validation-observer v-if="editing" ref="form" v-slot="{ handleSubmit }">
                <b-form @submit.prevent="handleSubmit(save)">
                    <b-alert :show="Boolean(formError)" variant="danger">{{ formError }}</b-alert>
                    <validation-provider v-for="field in definition.fields" :key="field.key" v-slot="{ errors }" :name="field.label" :vid="field.key" :rules="field.rules || {}">
                        <b-form-group :label="field.type === 'checkbox' ? '' : field.label" :label-for="'field-' + field.key">
                            <employee-picker v-if="field.type === 'employee'" :id="'field-' + field.key" v-model="form[field.key]" :state="errors.length ? false : null" />
                            <b-form-checkbox v-else-if="field.type === 'checkbox'" :id="'field-' + field.key" v-model="form[field.key]">{{ field.label }}</b-form-checkbox>
                            <b-form-select v-else-if="field.type === 'select'" :id="'field-' + field.key" v-model="form[field.key]" :options="field.options" :state="errors.length ? false : null" />
                            <b-form-textarea v-else-if="field.type === 'textarea'" :id="'field-' + field.key" v-model="form[field.key]" :state="errors.length ? false : null" />
                            <b-form-input v-else :id="'field-' + field.key" v-model="form[field.key]" :type="field.type || 'text'" :inputmode="field.inputmode" :state="errors.length ? false : null" />
                            <div v-if="errors.length" class="text-danger small" role="alert">{{ errors[0] }}</div>
                        </b-form-group>
                    </validation-provider>
                    <div class="d-flex justify-content-end">
                        <b-button variant="outline-secondary" class="mr-2" :disabled="saving" @click="editing = false">Cancel</b-button>
                        <b-button type="submit" variant="primary" :disabled="saving"><b-spinner v-if="saving" small /> {{ saving ? 'Saving?' : 'Save record' }}</b-button>
                    </div>
                </b-form>
            </validation-observer>
        </b-modal>
        <b-modal :visible="Boolean(badge)" title="Employee identification" hide-footer @hidden="badge = null">
            <employee-badge v-if="badge" :key="badge.id" :employee="badge" />
        </b-modal>
    </section>
</template>
<script>
import { VueGoodTable } from 'vue-good-table';
import { jsPDF } from 'jspdf';
import { autoTable } from 'jspdf-autotable';
import api from '../../../api';
import { endpoints } from '../../../api/routes';
import definitions from './modules';
import EmployeePicker from '../../../components/EmployeePicker.vue';
import EmployeeBadge from '../../../components/EmployeeBadge.vue';

export default {
    components: { VueGoodTable, EmployeePicker, EmployeeBadge },
    props: { module: { type: String, required: true } },
    metaInfo() { return { title: this.definition.title }; },
    data: () => ({
        search: '', timer: null, params: { page: 1, per_page: 20, sort: 'id', direction: 'desc', search: '' },
        error: '', formError: '', editing: false, recordId: null, form: {}, saving: false, exporting: false, badge: null, timezone: 'UTC',
    }),
    computed: {
        definition() { return definitions[this.module]; },
        state() { return this.$store.state[this.module]; },
        canManage() { return this.$store.getters['auth/can'](this.definition.permission); },
        canReport() { return this.$store.getters['auth/can']('reports.view'); },
        columns() {
            const columns = this.definition.columns.map((column) => ({
                ...column,
                ...(column.field.startsWith('clocked_') ? { formatFn: (value) => value ? new Intl.DateTimeFormat(undefined, { dateStyle: 'short', timeStyle: 'short', timeZone: this.timezone }).format(new Date(value)) : '?' } : {}),
            }));
            if (this.canManage || this.module === 'employees') { columns.push({ label: 'Actions', field: 'actions', sortable: false }); }
            return columns;
        },
    },
    async created() {
        this.load();
        try { this.timezone = (await api.get(endpoints.settings)).data.data.timezone; }
        catch (error) { this.$bvToast.toast('Workspace timezone could not be loaded. Times are shown in UTC.', { title: 'Timezone', variant: 'warning', solid: true }); }
    },
    beforeDestroy() { clearTimeout(this.timer); },
    methods: {
        async load() {
            this.error = '';
            try { await this.$store.dispatch(this.module + '/fetch', this.params); }
            catch (error) { this.error = 'Unable to load ' + this.definition.title.toLowerCase() + '.'; }
        },
        scheduleSearch() {
            clearTimeout(this.timer);
            this.timer = setTimeout(() => { this.params.search = this.search; this.params.page = 1; this.load(); }, 300);
        },
        changePage({ currentPage }) { this.params.page = currentPage; this.load(); },
        changeSize({ currentPerPage }) { this.params.per_page = currentPerPage; this.params.page = 1; this.load(); },
        changeSort(sort) {
            const selected = sort[0];
            this.params.sort = selected && ['asc', 'desc'].includes(selected.type) ? selected.field : 'id';
            this.params.direction = selected && ['asc', 'desc'].includes(selected.type) ? selected.type : 'desc';
            this.params.page = 1;
            this.load();
        },
        open(row) {
            this.recordId = row ? row.id : null;
            this.formError = '';
            this.form = {};
            this.definition.fields.forEach((field) => {
                let value = row ? row[field.key] : field.default;
                if (row && field.type === 'month') { value = value.slice(0, 7); }
                if (row && field.type === 'datetime-local' && value) {
                    const date = new Date(value);
                    value = new Date(date.getTime() - date.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
                }
                this.$set(this.form, field.key, value === null && field.type !== 'employee' ? '' : value);
            });
            this.editing = true;
        },
        preventBusyClose(event) { if (this.saving) { event.preventDefault(); } },
        async save() {
            if (this.saving) { return; }
            this.saving = true; this.formError = '';
            const values = { ...this.form };
            this.definition.fields.forEach((field) => {
                if (field.type === 'datetime-local') { values[field.key] = values[field.key] ? new Date(values[field.key]).toISOString() : null; }
                if (field.type === 'month') { values[field.key] += '-01'; }
            });
            try {
                await this.$store.dispatch(this.module + '/save', { id: this.recordId, values });
                this.saving = false;
                this.editing = false;
                this.$bvToast.toast('Record saved.', { title: this.definition.title, variant: 'success', solid: true });
                await this.load();
            } catch (error) {
                if (error.response && error.response.status === 422) { this.$refs.form.setErrors(error.response.data.errors); }
                else { this.formError = error.response && error.response.status === 403 ? 'Your account cannot change this record.' : 'Unable to save this record.'; }
            } finally { this.saving = false; }
        },
        async remove(row) {
            const result = await this.$swal({ title: 'Delete this record?', text: this.module === 'employees' ? 'The employee will be archived. Existing HR records remain available.' : 'This action removes the selected record.', icon: 'warning', showCancelButton: true, confirmButtonText: 'Delete', confirmButtonColor: '#c0392b' });
            if (!result.isConfirmed) { return; }
            try {
                await this.$store.dispatch(this.module + '/remove', row.id);
                if (this.state.rows.length === 1 && this.params.page > 1) { this.params.page -= 1; }
                await this.load();
            } catch (error) { this.$bvToast.toast('Unable to delete this record.', { title: 'Delete failed', variant: 'danger', solid: true }); }
        },
        async exportPage() {
            this.exporting = true;
            try {
                const doc = new jsPDF({ orientation: 'landscape' });
                doc.text(this.definition.title + ' ? page ' + this.params.page, 14, 15);
                const columns = this.definition.columns;
                autoTable(doc, {
                    startY: 22,
                    head: [columns.map((column) => column.label)],
                    body: this.state.rows.map((row) => columns.map((column) => column.field.split('.').reduce((value, key) => value && value[key], row) || '')),
                });
                doc.save(this.module + '-page-' + this.params.page + '.pdf');
            } catch (error) { this.$bvToast.toast('Unable to export this page.', { title: 'Export failed', variant: 'danger', solid: true }); }
            finally { this.exporting = false; }
        },
        async download(endpoint, filename) {
            this.exporting = true;
            try {
                const { data } = await api.get(endpoints[endpoint], { responseType: 'blob' });
                const url = URL.createObjectURL(data);
                const link = document.createElement('a');
                link.href = url; link.download = filename; link.click();
                setTimeout(() => URL.revokeObjectURL(url), 1000);
            } catch (error) { this.$bvToast.toast('Unable to export the report. Try Excel for large reports.', { title: 'Export failed', variant: 'danger', solid: true }); }
            finally { this.exporting = false; }
        },
    },
};
</script>
