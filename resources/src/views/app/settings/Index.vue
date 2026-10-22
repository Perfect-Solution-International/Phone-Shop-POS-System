<template>
    <section>
        <h1 class="h3 mb-4">Workspace settings</h1>
        <b-skeleton v-if="loading" height="12rem" />
        <b-alert :show="Boolean(error)" variant="danger">{{ error }} <b-button v-if="!loaded" size="sm" @click="load">Retry</b-button></b-alert>
        <b-card v-if="loaded">
            <validation-observer ref="form" v-slot="{ handleSubmit }">
                <b-form @submit.prevent="handleSubmit(save)">
                    <validation-provider v-slot="{ errors }" name="Timezone" vid="timezone" rules="required">
                        <b-form-group label="Timezone" label-for="timezone" description="IANA timezone, for example Asia/Colombo">
                            <b-form-input id="timezone" v-model="form.timezone" :state="errors.length ? false : null" required />
                            <b-form-invalid-feedback>{{ errors[0] }}</b-form-invalid-feedback>
                        </b-form-group>
                    </validation-provider>
                    <validation-provider v-slot="{ errors }" name="Currency" vid="currency" rules="required|min:3|max:3">
                        <b-form-group label="Currency code" label-for="currency">
                            <b-form-input id="currency" v-model="form.currency" maxlength="3" :state="errors.length ? false : null" required />
                            <b-form-invalid-feedback>{{ errors[0] }}</b-form-invalid-feedback>
                        </b-form-group>
                    </validation-provider>
                    <b-button variant="primary" type="submit" :disabled="saving">{{ saving ? 'Saving?' : 'Save settings' }}</b-button>
                </b-form>
            </validation-observer>
        </b-card>
    </section>
</template>
<script>
import api from '../../../api';
import { endpoints } from '../../../api/routes';

export default {
    metaInfo: { title: 'Settings' },
    data: () => ({ loading: true, loaded: false, saving: false, error: '', form: { timezone: 'UTC', currency: 'LKR' } }),
    created() { this.load(); },
    methods: {
        async load() {
            this.loading = true; this.error = '';
            try {
                const { data } = await api.get(endpoints.settings);
                this.form = { timezone: data.data.timezone, currency: data.data.currency };
                this.loaded = true;
            } catch (error) { this.error = 'Unable to load settings.'; }
            finally { this.loading = false; }
        },
        async save() {
            this.saving = true; this.error = '';
            try {
                await api.put(endpoints.settings, this.form);
                this.$bvToast.toast('Settings saved.', { title: 'Settings', variant: 'success', solid: true });
            } catch (error) {
                if (error.response && error.response.status === 422) { this.$refs.form.setErrors(error.response.data.errors); }
                else { this.error = 'Unable to save settings.'; }
            } finally { this.saving = false; }
        },
    },
};
</script>
