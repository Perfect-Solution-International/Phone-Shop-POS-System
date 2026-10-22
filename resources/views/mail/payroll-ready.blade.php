<x-mail::message>
# Payroll record ready

Hello {{ $employeeName }},

Your payroll record for {{ $period }} is ready. Please contact your HR team for your payslip.

{{ config('app.name') }}
</x-mail::message>
