---
paths:
  - 'resources/views/**'
---

# Views

## SFC class can't read mount params in template
Inline-Livewire (aka Bolt / section-file components) templates cannot access mount() parameters like $personnel directly. Expose them via a #[Computed] property (e.g. personnel()) or coalesce to a public property in mount().
