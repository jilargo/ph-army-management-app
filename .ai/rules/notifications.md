---
paths:
  - 'app/Notifications/**'
---

# Notifications

## Notifications are intentionally not queueable
QUEUE_CONNECTION=database and no worker is running, so notification classes (via() = ['database','mail']) must NOT implement ShouldQueue. Queuing would delay the in-app notification DB row too. Notifications are sent synchronously from Livewire actions aka Bolt components.
