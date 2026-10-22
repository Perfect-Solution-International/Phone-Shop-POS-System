<template>
    <div>
        <b-form-input v-model="search" :id="id + '-search'" placeholder="Search name or employee code" :aria-label="'Search employees for ' + id" @input="schedule" />
        <b-form-select :id="id" :value="value" :options="options" :state="state" :aria-label="'Select employee for ' + id" class="mt-2" @change="$emit('input', $event)" />
        <small v-if="loading" class="text-muted" role="status">Loading employees?</small>
        <small v-if="error" class="text-danger" role="alert">{{ error }}</small>
        <b-button size="sm" variant="link" @click="scan = true">Scan QR code</b-button>
        <b-modal v-model="scan" title="Scan employee QR code" hide-footer @hidden="scannerVisible = false" @shown="scannerVisible = true">
            <qr-scanner v-if="scannerVisible" @decoded="decoded" />
        </b-modal>
    </div>
</template>
<script>
import api from '../api';
import { endpoints } from '../api/routes';
import QrScanner from './QrScanner.vue';

export default {
    components: { QrScanner },
    props: { id: { type: String, required: true }, value: { default: null }, state: { default: null } },
    data: () => ({ search: '', rows: [], selected: null, loading: false, error: '', timer: null, requestId: 0, scan: false, scannerVisible: false }),
    computed: {
        options() {
            const rows = this.selected && !this.rows.some((row) => row.id === this.selected.id) ? [this.selected, ...this.rows] : this.rows;
            return [{ value: null, text: 'Select an employee' }, ...rows.map((row) => ({ value: row.id, text: row.employee_code + ' ? ' + row.name }))];
        },
    },
    async created() {
        if (this.value) {
            try { this.selected = (await api.get(endpoints.employees + '/' + this.value)).data.data; }
            catch (error) { this.error = 'Unable to load the selected employee.'; }
        }
        this.load();
    },
    beforeDestroy() { clearTimeout(this.timer); this.requestId += 1; },
    methods: {
        schedule() { clearTimeout(this.timer); this.timer = setTimeout(this.load, 300); },
        async load() {
            const requestId = ++this.requestId;
            this.loading = true; this.error = '';
            try {
                const { data } = await api.get(endpoints.employees, { params: { search: this.search, per_page: 30, sort: 'name', direction: 'asc' } });
                if (requestId === this.requestId) { this.rows = data.data; }
            } catch (error) { if (requestId === this.requestId) { this.error = 'Unable to load employees. Search again to retry.'; } }
            finally { if (requestId === this.requestId) { this.loading = false; } }
        },
        async decoded(code) {
            this.scan = false;
            this.search = code.slice(0, 100);
            await this.load();
            const match = this.rows.find((row) => row.employee_code === code);
            if (match) { this.selected = match; this.$emit('input', match.id); }
            else { this.error = 'No employee matches this QR code in your workspace.'; }
        },
    },
};
</script>
