<template>
    <main class="login-shell" aria-labelledby="login-heading">
        <b-card class="login-card">
            <header class="login-header">
                <span class="login-brand-mark" aria-hidden="true">
                    <svg width="28" height="32" viewBox="0 0 28 32" fill="none"><rect x="5" y="2" width="18" height="28" rx="4" stroke="currentColor" stroke-width="2" /><path d="M11 6h6M12 26h4" stroke="currentColor" stroke-width="2" stroke-linecap="round" /></svg>
                </span>
                <p class="login-brand-name">{{ applicationName }}</p>
                <h1 id="login-heading" class="h3 mb-2">Welcome back</h1>
                <p class="text-muted mb-0">Sign in to your shop workspace.</p>
            </header>
            <validation-observer ref="form" v-slot="{ handleSubmit }">
                <b-form class="mt-4" novalidate :aria-busy="busy ? 'true' : 'false'" @submit.prevent="previewUrl ? submit() : handleSubmit(submit)">
                    <b-alert :show="Boolean(error)" variant="danger" role="alert" tabindex="-1" ref="error">{{ error }}</b-alert>
                    <fieldset :disabled="busy">
                    <legend class="sr-only">Sign in details</legend>
                    <validation-provider v-slot="{ errors }" name="Email" vid="email" :rules="previewUrl ? '' : 'required|email'">
                        <b-form-group label="Email address" label-for="email">
                            <b-form-input id="email" v-model.trim="credentials.email" name="email" type="email" autocomplete="username" inputmode="email" maxlength="255" aria-describedby="email-error" :state="errors.length ? false : null" :required="!previewUrl" />
                            <b-form-invalid-feedback id="email-error" :force-show="Boolean(errors.length)" aria-live="polite">{{ errors[0] }}</b-form-invalid-feedback>
                        </b-form-group>
                    </validation-provider>
                    <validation-provider v-slot="{ errors }" name="Password" vid="password" :rules="previewUrl ? '' : 'required'">
                        <b-form-group label="Password" label-for="password">
                            <b-input-group>
                                <b-form-input id="password" v-model="credentials.password" name="password" :type="showPassword ? 'text' : 'password'" autocomplete="current-password" maxlength="1024" aria-describedby="password-error" :state="errors.length ? false : null" :required="!previewUrl" />
                                <b-input-group-append>
                                    <b-button type="button" variant="outline-secondary" aria-controls="password" :aria-label="showPassword ? 'Hide password' : 'Show password'" :pressed="showPassword" @click="showPassword = !showPassword">{{ showPassword ? 'Hide' : 'Show' }}</b-button>
                                </b-input-group-append>
                            </b-input-group>
                            <b-form-invalid-feedback id="password-error" :force-show="Boolean(errors.length)" aria-live="polite">{{ errors[0] }}</b-form-invalid-feedback>
                        </b-form-group>
                    </validation-provider>
                    <b-form-checkbox id="remember" v-model="credentials.remember" name="remember" class="mb-4">Remember me</b-form-checkbox>
                    <b-button type="submit" variant="primary" block :disabled="busy"><b-spinner v-if="busy" small class="mr-2" aria-hidden="true" />{{ busy ? 'Signing in...' : 'Sign in' }}</b-button>
                    </fieldset>
                    <span class="sr-only" role="status">{{ busy ? 'Signing in. Please wait.' : '' }}</span>
                </b-form>
            </validation-observer>
            <p class="login-help text-muted mb-0">Need access? Contact your workspace administrator.</p>
        </b-card>
    </main>
</template>

<script>
export default {
    metaInfo: { title: 'Sign in' },
    data: () => ({ applicationName: document.body.dataset.applicationName, previewUrl: document.body.dataset.loginPreviewUrl || '', credentials: { email: '', password: '', remember: false }, showPassword: false, busy: false, error: '' }),
    methods: {
        async submit() {
            if (this.busy) { return; }
            if (this.previewUrl) {
                this.busy = true;
                this.credentials.password = '';
                window.location.replace(this.previewUrl);
                return;
            }
            this.busy = true;
            this.error = '';
            try {
                const destination = await this.$store.dispatch('auth/login', this.credentials);
                this.credentials.password = '';
                window.location.replace(destination);
            } catch (error) {
                if (error.response && error.response.status === 422) {
                    this.$refs.form.setErrors(error.response.data.errors);
                    this.error = 'Check your email address and password, then try again.';
                } else {
                    this.error = error.response && error.response.status === 429
                        ? 'Too many sign-in attempts. Please wait a minute.'
                        : error.response && error.response.status === 419 ? 'Your sign-in session expired. Please try again.'
                        : !error.response ? 'Unable to connect. Check your connection and try again.' : 'Unable to sign in right now. Please try again.';
                }
                this.$nextTick(() => { if (this.$refs.error) { this.$refs.error.$el.focus(); } });
            } finally { this.busy = false; }
        },
    },
};
</script>
