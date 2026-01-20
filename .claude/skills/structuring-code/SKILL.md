---
name: structure-code
description: 'Enforce Action Pattern: each action lives in the actions folder and centralizes its workflow in a handle method.'
---

When implementing application logic, always use the Action Pattern.

Each concrete action must:

- Live inside the `actions/` folder (optionally grouped by domain, e.g. `exercises`, `routines`, etc.).
- Be represented by a single action class or file.
- Expose a `handle()` method as the single entry point.

The `handle()` method is the orchestration layer of the action:

- It contains **all the steps** required to perform the action.
- Steps should be clearly ordered and explicit.
- Each step may delegate work to internal methods or collaborators, but **the flow is visible in `handle()`**.

This structure ensures that:

- The full behavior of an action can be understood at a glance.
- Steps can be easily added, removed, reordered, or modified.
- The action remains the single source of truth for its workflow.
