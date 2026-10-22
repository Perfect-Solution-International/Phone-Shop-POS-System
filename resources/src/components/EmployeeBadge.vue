<template>
    <div class="employee-badge text-center">
        <h2 class="h5">{{ employee.name }}</h2>
        <vue-barcode :value="employee.employee_code" format="CODE128" :height="50" />
        <div ref="qr" class="d-flex justify-content-center mt-3" :aria-label="'Employee QR code for ' + employee.employee_code" />
    </div>
</template>
<script>
import VueBarcode from 'vue-barcode';
import QRCode from 'qrcodejs2';

export default {
    components: { VueBarcode },
    props: { employee: { type: Object, required: true } },
    mounted() { new QRCode(this.$refs.qr, { text: this.employee.employee_code, width: 150, height: 150 }); },
};
</script>
