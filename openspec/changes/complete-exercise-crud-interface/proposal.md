# Proposal: Complete Exercise CRUD Interface

## Summary

Complete the exercise management interface by adding missing update/edit functionality for exercise details. Currently, the system supports viewing, creating, and deleting exercises, and updating only exercise categories. Users cannot edit core exercise attributes (name, description, image) through the UI.

## Problem Statement

The exercise management interface is incomplete:

- **Index page** (`ExercisesIndex.vue`): ✅ Implemented (list, search, filter, create, delete)
- **Show page** (`ExercisesShow.vue`): ✅ Partially implemented (view details, edit categories only)
- **Edit functionality**: ❌ Missing (no way to update exercise name, description, image, alternative names)
- **Backend route**: ❌ Missing (`PATCH /exercises/{exercise}` route doesn't exist)

Users must delete and recreate exercises to fix typos or update information, which is inefficient and loses historical data (workout references).

## Scope

This proposal covers:

1. **Backend API**: Add update route and controller method for general exercise updates
2. **Frontend UI**: Add edit dialog/form in `ExercisesShow.vue` to modify exercise details
3. **Validation**: Ensure proper validation for exercise updates (unique names, required fields)
4. **Design consistency**: Follow existing shadcn/ui patterns and color palette conventions

**Out of scope**:

- Bulk editing multiple exercises
- Advanced media management (image upload/crop) - will use simple URL input
- Equipment/tags CRUD (focus on core exercise attributes only)
- Exercise instructions editing (keep read-only for now)

## User Impact

**Trainers** will be able to:

- Fix typos in exercise names without deleting
- Update descriptions to add more detail
- Change exercise images when better resources are found
- Manage alternative exercise names
- Maintain exercise history across updates (workouts retain references)

## Technical Approach

### Backend Changes

1. Add `PATCH /exercises/{exercise}` route in `routes/web.php`
2. Create `ExerciseController@update()` method
3. Create `UpdateExerciseRequest` Form Request for validation
4. Handle multilingual name updates through `ExerciseName` model

### Frontend Changes

1. Add "Edit Exercise" button/dialog in `ExercisesShow.vue`
2. Create reusable form component or dialog with:
    - Primary name input (required)
    - Alternative names input (array/tags)
    - Description textarea
    - Image URL input
3. Use Inertia `useForm()` for form handling
4. Follow existing patterns from `ExercisesIndex.vue` create dialog

### Design Approach

- Use shadcn Dialog component for edit modal (consistent with delete confirmation)
- Primary color (`oklch(0.8342 0.159 79.51)`) for action buttons
- Form layout follows existing patterns (Label + Input/Textarea components)
- Validation errors displayed inline with red text
- Loading states with disabled buttons during processing

## Dependencies

- No new packages required
- Depends on existing Exercise model and ExerciseName relationships
- Uses existing Inertia/Vue infrastructure

## Risks & Mitigations

1. **Risk**: Updating primary name could break search/references
    - **Mitigation**: Update through `ExerciseName` model, maintain all name records
2. **Risk**: Users might accidentally lose data during editing
    - **Mitigation**: Pre-populate form with current values, show confirmation for major changes

3. **Risk**: Concurrent edits by multiple trainers
    - **Mitigation**: Use optimistic locking (future enhancement) or last-write-wins (acceptable for v1)

## Success Criteria

- [ ] Trainers can edit exercise name, description, and image from show page
- [ ] Changes persist correctly in database
- [ ] Alternative names can be added/removed
- [ ] UI follows design system conventions
- [ ] Form validation prevents invalid updates
- [ ] No breaking changes to existing exercise functionality
- [ ] Tests cover update scenarios (happy path + validation failures)

## Open Questions

1. Should we allow editing of alternative names in the same form, or separate UI?
    - **Proposed**: Same form with tag-input component
2. Should we add audit logging for exercise changes?
    - **Proposed**: Skip for v1, add in future iteration
3. How should we handle image uploads vs. URL input?
    - **Proposed**: URL input only for v1 (simpler, faster to implement)
