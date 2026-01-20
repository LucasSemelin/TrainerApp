# Complete Exercise CRUD Interface - Proposal

## 📋 Overview

This OpenSpec proposal adds the missing **edit/update functionality** for exercises in the TrainerApp. Currently, trainers can list, search, create, and delete exercises, but cannot edit them. This forces users to delete and recreate exercises to fix typos or update information.

## 🎯 What's Being Added

### Backend

- ✅ `PATCH /exercises/{exercise}` API endpoint
- ✅ `ExerciseController@update()` method
- ✅ `UpdateExerciseRequest` for validation
- ✅ Multilingual name handling via `ExerciseName` model

### Frontend

- ✅ "Editar ejercicio" button on exercise show page
- ✅ Edit dialog with shadcn/ui components
- ✅ Form fields for:
    - Primary exercise name (required)
    - Alternative names (tag/chip input)
    - Description (textarea)
    - Image URL (text input)
- ✅ Validation error display
- ✅ Loading states during save

## 📁 Files Structure

```
openspec/changes/complete-exercise-crud-interface/
├── proposal.md           # High-level proposal and problem statement
├── design.md            # Architectural decisions and data flow
├── tasks.md             # Ordered implementation tasks (~4.5 hours)
└── specs/
    ├── exercise-update-api/
    │   └── spec.md      # Backend API requirements & scenarios
    └── exercise-edit-ui/
        └── spec.md      # Frontend UI requirements & scenarios
```

## ✨ Key Features

1. **Edit Core Exercise Data**: Name, description, image
2. **Manage Alternative Names**: Add/remove alternative exercise names
3. **Multilingual Support**: Preserves ExerciseName architecture
4. **Design Consistency**: Uses shadcn/ui + Tailwind v4 conventions
5. **Form Validation**: Client & server-side validation
6. **Loading States**: Clear feedback during operations

## 🚫 Out of Scope (v1)

- Bulk editing multiple exercises
- File upload (using URL input instead)
- Equipment/tags editing (separate enhancement)
- Instructions editing (read-only for now)
- Audit logging (future enhancement)

## 🏗️ Implementation Plan

**Estimated effort**: ~4.5 hours

### Phase 1: Backend (50 minutes)

1. Create `UpdateExerciseRequest` (15 min)
2. Add `ExerciseController@update()` (30 min)
3. Add PATCH route (5 min)

### Phase 2: Frontend (2.5 hours)

4. Add edit button (10 min)
5. Create edit dialog structure (20 min)
6. Add form fields (30 min)
7. Implement alternative names input (25 min)
8. Set up Inertia form (15 min)
9. Implement form submission (20 min)
10. Add validation error display (20 min)
11. Add success feedback (10 min)

### Phase 3: Testing & QA (1 hour)

12. Backend feature tests (45 min)
13. Manual QA testing (30 min)
14. Code formatting & cleanup (10 min)
15. Documentation updates (10 min)

## ✅ Validation Status

```bash
✓ Change 'complete-exercise-crud-interface' is valid
```

All requirements follow OpenSpec conventions:

- MUST/SHALL keywords present
- Scenario-driven requirements
- Clear acceptance criteria
- Proper delta format (ADDED/MODIFIED/REMOVED)

## 🔍 Review Checklist

- [ ] Read [proposal.md](proposal.md) for problem statement
- [ ] Review [design.md](design.md) for architectural decisions
- [ ] Check [specs/exercise-update-api/spec.md](specs/exercise-update-api/spec.md) for backend requirements
- [ ] Check [specs/exercise-edit-ui/spec.md](specs/exercise-edit-ui/spec.md) for frontend requirements
- [ ] Review [tasks.md](tasks.md) for implementation breakdown
- [ ] Verify all scenarios make sense for user needs
- [ ] Confirm design decisions align with project conventions

## 🚀 Next Steps

Once approved:

1. Run `openspec apply complete-exercise-crud-interface`
2. Follow tasks in order from `tasks.md`
3. Test after each task completion
4. Commit incrementally
5. Run full test suite before PR

## 📚 Related Documentation

- Main project context: `openspec/project.md`
- OpenSpec guidelines: `openspec/AGENTS.md`
- Exercise model: `app/Models/Exercise.php`
- Current controller: `app/Http/Controllers/ExerciseController.php`
- Existing UI: `resources/js/pages/Exercises/ExercisesShow.vue`

## 💡 Design Highlights

### Color Palette (from app.css)

- Primary: `oklch(0.8342 0.159 79.51)` - #B8D787 (lime green)
- Primary hover: `oklch(0.8 0.159 79.51)`
- Used for action buttons, active states, focus rings

### UI Patterns

- Dialog modals for create/edit/delete
- Badge components for categories/tags
- Gap utilities for spacing (not margins)
- Dark mode support via `dark:` prefix

### Form Handling

- Inertia `useForm()` for forms
- shadcn/ui components (Dialog, Input, Textarea, Button, Label)
- Inline validation errors (red text under fields)
- Loading states (disabled buttons, "Guardando..." text)

## ❓ Questions?

Review the proposal documents or check:

- **Problem**: See "Problem Statement" in proposal.md
- **Why this approach**: See "Design Decisions" in design.md
- **What to build**: See scenarios in specs/\*/spec.md
- **How to build**: See tasks breakdown in tasks.md
