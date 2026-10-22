<template>
    <div>
        <b-alert :show="Boolean(error)" variant="warning">{{ error }}</b-alert>
        <div :id="readerId" class="scanner-video" />
        <p class="small text-muted">Allow camera access and point the camera at an employee QR code.</p>
    </div>
</template>
<script>
import { Html5Qrcode } from 'html5-qrcode';
let nextReader = 0;

export default {
    data: () => ({ readerId: 'qr-reader-' + (++nextReader), scanner: null, error: '', destroyed: false, starting: false, decodedOnce: false }),
    async mounted() {
        if (!window.isSecureContext || !navigator.mediaDevices) {
            this.error = 'Camera scanning requires HTTPS or localhost and a compatible browser.';
            return;
        }
        this.scanner = new Html5Qrcode(this.readerId);
        this.starting = true;
        try {
            await this.scanner.start({ facingMode: 'environment' }, { fps: 10, qrbox: { width: 220, height: 220 } }, (code) => {
                if (!this.decodedOnce && !this.destroyed) { this.decodedOnce = true; this.$emit('decoded', code); }
            }, () => {});
            if (this.destroyed || this.decodedOnce) { await this.stop(); }
        } catch (error) {
            if (!this.destroyed) { this.error = 'Unable to open the camera. Check camera permissions or enter the employee code manually.'; }
        } finally { this.starting = false; }
    },
    beforeDestroy() { this.destroyed = true; if (!this.starting) { this.stop(); } },
    methods: {
        async stop() {
            if (this.scanner && this.scanner.isScanning) {
                try { await this.scanner.stop(); this.scanner.clear(); } catch (error) { /* Camera may already be stopped. */ }
            }
        },
    },
};
</script>
