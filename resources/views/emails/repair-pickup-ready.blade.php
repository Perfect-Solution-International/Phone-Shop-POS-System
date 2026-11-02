<x-mail::message>
# Your Device is Ready for Collection!

Dear **{{ $repair->customer->name ?? 'Valued Customer' }}**,

Good news! Our workshop technicians have completed the servicing of your device. Your device has passed quality checks and is now ready for collection at our shop counter.

<x-mail::panel>
**Service Ticket:** `{{ $repair->job_number }}`  
**Device:** {{ $repair->brand }} {{ $repair->model }}  
@if($repair->imei)
**IMEI:** `{{ $repair->imei }}`  
@endif
**Fault Repaired:** {{ $repair->fault_description }}  
@if($repair->technician_notes)
**Technician Notes:** {{ $repair->technician_notes }}  
@endif

---

**Total Amount:** LKR {{ number_format($repair->final_cost ?? $repair->estimated_cost, 2) }}  
**Advance Paid:** LKR {{ number_format($repair->advance_amount ?? 0, 2) }}  
**Balance Due at Pickup:** **LKR {{ number_format(max(0, ($repair->final_cost ?? $repair->estimated_cost) - ($repair->advance_amount ?? 0)), 2) }}**
</x-mail::panel>

### Collection Instructions:
1. Please bring your original **Job Ticket / Receipt** or show this email confirmation at the shop counter.
2. Verify all features and physical condition before taking the device.
3. Repaired components are protected by our standard service warranty.

@php
    $trackUrl = url('/track-repair?tracking_id=' . urlencode($repair->job_number));
@endphp

<x-mail::button :url="$trackUrl">
Track Service & View Warranty
</x-mail::button>

**Shop Address & Contact:**  
{{ $repair->shop->name ?? 'Phone Shop' }}  
{{ $repair->shop->address ?? 'Main Branch' }}  
Phone: {{ $repair->shop->phone ?? 'Shop Counter' }}

Thank you for choosing our repair services!  
**{{ $repair->shop->name ?? config('app.name') }} Team**
</x-mail::message>

