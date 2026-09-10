<x-mail::message>
# {{ $mentioned ? 'You were mentioned in a task' : 'New Task Assigned' }}

{{ $mentioned ? 'You have been mentioned in the following task.' : 'You have been assigned to the following task.' }}

<x-mail::table>
| Field | Details |
|:------|:--------|
| **Title** | {{ $task->title }} |
| **Type** | {{ ucfirst($task->type ?? 'task') }} |
| **Priority** | {{ ucfirst($task->priority ?? '—') }} |
| **Due Date** | {{ optional($task->due_date)->format('M j, Y') }} |
| **Status** | {{ ucfirst($task->status) }} |
</x-mail::table>

@if ($task->description)
**Description:** {{ $task->description }}
@endif

<x-mail::button :url="route('tasks')">
View Task
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>