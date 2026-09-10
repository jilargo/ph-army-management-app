<x-mail::message>
# New Leave Request

{{ $soldier?->full_name ?? 'A soldier' }} has submitted a new leave request.

<x-mail::table>
| Field | Details |
|:------|:--------|
| **Leave Type** | {{ $leave->leaveType?->leave_name ?? '—' }} |
| **Start Date** | {{ optional($leave->start_date)->format('M j, Y') }} |
| **End Date** | {{ optional($leave->end_date)->format('M j, Y') }} |
| **Days** | {{ $leave->start_date && $leave->end_date ? $leave->start_date->diffInDays($leave->end_date) + 1 : '—' }} |
| **Status** | {{ ucfirst($leave->status) }} |
</x-mail::table>

**Reason:** {{ $leave->reason }}

<x-mail::button :url="route('leaves.index')">
Review Leave Requests
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>