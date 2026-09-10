---
paths:
  - .env
---

# General

## APP_URL must match the serving host ph-army-management-app.test
The app is served at http://ph-army-management-app.test (see C:\Windows\System32\drivers\etc\hosts -> 127.0.0.1 ph-army-management-app.test). APP_URL MUST stay http://ph-army-management-app.test. If it drifts (e.g. to ph-army-management.test or localhost), Storage::url()/asset() break and uploaded profile pictures appear broken despite file+DB being stored correctly.
