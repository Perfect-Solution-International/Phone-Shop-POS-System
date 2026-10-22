<!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8"><title>Payroll report</title>
<style>body{font-family:DejaVu Sans,sans-serif;font-size:11px}table{width:100%;border-collapse:collapse}th,td{padding:7px;border:1px solid #ddd;text-align:left}</style>
</head>
<body>
<h1>Payroll report ({{ $currency }})</h1>
<table><thead><tr><th>Employee</th><th>Period</th><th>Basic</th><th>Allowances</th><th>Deductions</th><th>Net</th></tr></thead><tbody>
@foreach ($payrolls as $payroll)
<tr><td>{{ $payroll->employee?->name }}</td><td>{{ $payroll->period->format('Y-m') }}</td><td>{{ $payroll->basic_salary }}</td><td>{{ $payroll->allowances }}</td><td>{{ $payroll->deductions }}</td><td>{{ $payroll->net_salary }}</td></tr>
@endforeach
</tbody></table>
</body></html>
