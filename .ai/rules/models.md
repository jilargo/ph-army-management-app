---
paths:
  - app/Models/Personnel.php
---

# Models

## Personnel status is Title-Case; never use whereLower()
Personnel.status is stored Title-Case ('Active', 'Inactive', 'Retired', 'Leave'). Query it case-insensitively via whereRaw('LOWER(status) = ?', [...]). Do NOT use whereLower() — it does not exist in this Laravel version and silently falls through to Eloquent dynamic-where returning no rows. Reuse the shared personnel/status-badge component for status labels (green/red/blue/gray) and keep the same colors on the admin dashboard.
