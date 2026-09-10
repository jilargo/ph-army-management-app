<x-mail::message>
# Leave Request {{ ucfirst($leave->status) }}

Your {{ $leave->leaveType?->leave_name ?? 'leave' }} request has been **{{ strtoupper($leave->status) }}**.

<x-mail::table>
| Field | Details |
|:------|:--------|
| **Leave Type** | {{ $leave->leaveType?->leave_name ?? '—' }} |
| **Start Date** | {{ optional($leave->start_date)->format('M j, Y') }} |
| **End Date** | {{ optional($leave->end_date)->format('M j, Y') }} |
</x-mail::table>

@if ($leave->remarks)
**Reviewer Remarks:** {{ $leave->remarks }}
@endif

<x-mail::button :url="route('user-dashboard')">
View My Leave Requests
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>