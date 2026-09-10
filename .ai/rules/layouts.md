---
paths:
  - 'resources/views/layouts/**'
---

# Layouts

## App-shell layout architecture and sidebar state
app.blade.php is an app-shell: body is h-screen overflow-hidden; aside sidebar is fixed/z-40 below lg (drawer w/ overlay) and static/in-flow at lg+ (collapsible w-64<->w-20). Header is a shrink-0 sibling; main is flex-1 overflow-y-auto. All page spacing (p-4 sm:p-6 lg:p-8, max-w-7xl mx-auto) is centralized in the layout — pages must NOT add their own outer padding/max-width wrappers. Sidebar state uses inline Alpine x-data (open + collapsed) persisted via localStorage 'sidebar-collapsed'; no DB. Alpine is injected by Livewire (not in the Vite bundle), so use inline x-data, and keep [x-cloak] CSS in app.css. Toast/flash and heading var ($heading from component public prop) still work as before.
