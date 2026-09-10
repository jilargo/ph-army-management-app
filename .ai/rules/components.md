---
paths:
  - resources/views/components/⚡tasks.blade.php
---

# Components

## Task assignment, mention parsing and notification conventions
Task assignees: multi-select bound to assignee_ids (int array), search via assignee_search, sync pivot with task_personnel.assigned_by = auth id (syncWithPivotValues). personnel_id keeps first assignee as a legacy fallback for the view modal. @Mentions: single-word tokens /(?<![\w@])@([\p{L}][\p{L}\-']*)/u compared with `last_name === token || full_name padded contains " token "`. notifyAssignees merges assignees + mentions, dedupes per soldier, and marks mentioned=true only when a soldier is mentioned but not assigned.
