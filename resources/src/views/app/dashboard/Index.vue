<template>
    <section>
        <h1 class="h3 mb-4">Overview</h1>
        <b-alert :show="Boolean(error)" variant="danger">{{ error }} <b-button size="sm" @click="load">Retry</b-button></b-alert>
        <b-row>
            <b-col v-for="card in cards" :key="card.key" md="4" class="mb-3">
                <b-card :title="card.label">
                    <b-skeleton v-if="loading" width="40%" height="2rem" />
                    <strong v-else class="h2">{{ summary[card.key] || 0 }}</strong>
                </b-card>
            </b-col>
        </b-row>
        <b-card title="Leave requests">
            <b-skeleton v-if="loading" height="16rem" />
            <v-chart v-else-if="Object.keys(summary.leave_statuses || {}).length" :options="chart" autoresize class="dashboard-chart" />
            <p v-else class="text-muted mb-0">No leave requests yet.</p>
        </b-card>
    </section>
</template>
<script>
import ECharts from 'vue-echarts';
import 'echarts/lib/chart/pie';
import 'echarts/lib/component/tooltip';
import 'echarts/lib/component/legend';
import api from '../../../api';
import { endpoints } from '../../../api/routes';

export default {
    components: { 'v-chart': ECharts },
    metaInfo: { title: 'Overview' },
    data: () => ({
        loading: true, error: '', summary: {},
        cards: [{ key: 'employees', label: 'Employees' }, { key: 'present_today', label: 'Present today' }, { key: 'pending_leave', label: 'Pending leave' }],
    }),
    computed: {
        chart() {
            return {
                tooltip: { trigger: 'item' },
                legend: { bottom: 0 },
                series: [{ type: 'pie', radius: ['45%', '70%'], data: Object.entries(this.summary.leave_statuses || {}).map(([name, value]) => ({ name, value })) }],
            };
        },
    },
    created() { this.load(); },
    methods: {
        async load() {
            this.loading = true; this.error = '';
            try { this.summary = (await api.get(endpoints.dashboard)).data; }
            catch (error) { this.error = 'Unable to load the overview.'; }
            finally { this.loading = false; }
        },
    },
};
</script>
