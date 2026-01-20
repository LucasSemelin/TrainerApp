# Tasks: Complete Exercise CRUD Interface

## Overview

This document outlines the ordered work items to implement exercise update functionality. Tasks are designed to be small, independently verifiable, and deliver incremental value.

## Task List

### Backend Implementation

#### 1. Create UpdateExerciseRequest Form Request

**Estimated effort**: 15 minutes  
**Dependencies**: None  
**Verification**: Run `php artisan make:request UpdateExerciseRequest` succeeds

**Details**:

- Create `app/Http/Requests/UpdateExerciseRequest.php`
- Add validation rules:
    - name: required|string|max:255
    - alternative_names: nullable|array
    - alternative_names.\*: string|max:255
    - description: nullable|string|max:2000
    - image_path: nullable|url|max:500
- Add authorize() method (return true for authenticated users)

**Validation**:

- File exists at correct path
- All validation rules present and correct
- PHP syntax is valid

---

#### 2. Add update() method to ExerciseController

**Estimated effort**: 30 minutes  
**Dependencies**: Task 1 (UpdateExerciseRequest)  
**Verification**: Method exists and compiles without errors

**Details**:

- Add `update(UpdateExerciseRequest $request, Exercise $exercise)` method
- Update Exercise model fields:
    - description: from request
    - image_path: from request
    - slug: regenerate from new name
- Update primary ExerciseName (locale='es', is_primary=true):
    - Find existing primary name record
    - Update name field with request->name
- Sync alternative ExerciseNames:
    - Delete old alternative names (is_primary=false)
    - Create new alternative name records
- Return back() with success message

**Validation**:

- Method signature correct
- All fields updated properly
- No compilation errors

---

#### 3. Add PATCH route for exercise update

**Estimated effort**: 5 minutes  
**Dependencies**: Task 2 (ExerciseController@update)  
**Verification**: Route registered correctly

**Details**:

- Add to `routes/web.php` within Exercise Management group:
    ```php
    Route::patch('exercises/{exercise}', [ExerciseController::class, 'update'])
        ->name('exercises.update');
    ```
- Ensure route is within auth, verified, ensure.role middleware group

**Validation**:

- Run `php artisan route:list | grep exercises.update`
- Confirm route exists with correct verb and middleware

---

#### 4. Write backend feature tests

**Estimated effort**: 45 minutes  
**Dependencies**: Tasks 1-3 (backend complete)  
**Verification**: All tests pass

**Details**:

- Create `tests/Feature/ExerciseUpdateTest.php`
- Test scenarios:
    - test_trainer_can_update_exercise_name()
    - test_trainer_can_update_exercise_description()
    - test_trainer_can_update_exercise_image()
    - test_trainer_can_add_alternative_names()
    - test_trainer_can_remove_alternative_names()
    - test_update_requires_valid_data()
    - test_update_maintains_exercise_id()
    - test_unauthorized_user_cannot_update()

**Validation**:

- Run `php artisan test --filter=ExerciseUpdateTest`
- All tests pass

---

### Frontend Implementation

#### 5. Add edit button to ExercisesShow.vue

**Estimated effort**: 10 minutes  
**Dependencies**: None (can start in parallel)  
**Verification**: Button renders on page

**Details**:

- Add "Editar ejercicio" button near exercise title
- Use shadcn Button component with primary variant
- Add Pencil icon from lucide-vue-next
- Add @click handler to open dialog (showEditDialog ref)

**Validation**:

- Visit `/exercises/1` in browser
- Verify button is visible
- Click button (dialog won't open yet, but console should show no errors)

---

#### 6. Create edit dialog structure

**Estimated effort**: 20 minutes  
**Dependencies**: Task 5 (edit button)  
**Verification**: Dialog opens/closes correctly

**Details**:

- Add Dialog component from shadcn/ui
- Set up v-model:open="showEditDialog"
- Add DialogTrigger, DialogContent, DialogHeader, DialogTitle
- Add DialogFooter with "Cancelar" and "Guardar cambios" buttons
- Wire up button clicks (close dialog, submit form placeholder)

**Validation**:

- Click "Editar ejercicio" button
- Verify dialog opens
- Click "Cancelar" - verify dialog closes
- Click outside dialog - verify dialog closes (if shadcn default behavior)

---

#### 7. Create form fields in edit dialog

**Estimated effort**: 30 minutes  
**Dependencies**: Task 6 (dialog structure)  
**Verification**: Form fields render with current values

**Details**:

- Add form element with @submit.prevent="updateExercise"
- Add form fields using shadcn components:
    - Label + Input for "Nombre" (primary name)
    - Label + Input/tag component for "Nombres alternativos"
    - Label + Textarea for "Descripción"
    - Label + Input for "Imagen (URL)"
- Pre-populate fields with props.exercise data
- Use proper Tailwind v4 classes for layout (gap-4 for spacing)

**Validation**:

- Open edit dialog
- Verify all fields show current exercise data
- Verify labels are properly associated with inputs

---

#### 8. Implement alternative names tag input

**Estimated effort**: 25 minutes  
**Dependencies**: Task 7 (form fields)  
**Verification**: Can add/remove alternative names

**Details**:

- Create reactive array for alternative_names
- Display existing names as Badge components
- Add input field for new name
- Add "+" button or Enter key handler to add name
- Add "X" button on each badge to remove name
- Update form data when names change

**Validation**:

- Add a new alternative name - verify it appears as badge
- Remove an alternative name - verify it disappears
- Add multiple names - verify all appear

---

#### 9. Set up Inertia form with useForm()

**Estimated effort**: 15 minutes  
**Dependencies**: Task 7 (form fields)  
**Verification**: Form object exists with correct structure

**Details**:

- Import useForm from @inertiajs/vue3
- Create updateForm with useForm():
    ```typescript
    const updateForm = useForm({
        name: props.exercise.name,
        alternative_names: [...],
        description: props.exercise.description,
        image_path: props.exercise.image_path,
    });
    ```
- Bind form fields to updateForm data properties (v-model)

**Validation**:

- Check Vue devtools - verify updateForm exists
- Modify fields - verify updateForm.data updates
- Verify updateForm.processing and updateForm.errors exist

---

#### 10. Implement form submission

**Estimated effort**: 20 minutes  
**Dependencies**: Task 9 (Inertia form setup)  
**Verification**: Form submits and updates exercise

**Details**:

- Implement updateExercise() method:
    ```typescript
    const updateExercise = () => {
        updateForm.patch(`/exercises/${props.exercise.id}`, {
            onSuccess: () => {
                showEditDialog.value = false;
            },
        });
    };
    ```
- Wire submit handler to form element
- Handle loading state (button disabled, text changes)

**Validation**:

- Fill form with new values
- Click "Guardar cambios"
- Verify loading state appears
- Verify dialog closes on success
- Verify page shows updated data

---

#### 11. Add validation error display

**Estimated effort**: 20 minutes  
**Dependencies**: Task 10 (form submission)  
**Verification**: Validation errors display correctly

**Details**:

- Add error message display under each field:
    ```vue
    <p v-if="updateForm.errors.name" class="text-sm text-destructive">
        {{ updateForm.errors.name }}
    </p>
    ```
- Style error messages (red text, small font)
- Add red border to fields with errors

**Validation**:

- Submit form with empty name
- Verify error message displays
- Submit form with invalid URL
- Verify error message displays under image field
- Fix errors and resubmit - verify errors clear

---

#### 12. Add success feedback (optional enhancement)

**Estimated effort**: 10 minutes  
**Dependencies**: Task 10 (form submission)  
**Verification**: Success message appears after save

**Details**:

- Add toast notification or success message
- Display "Ejercicio actualizado correctamente" after successful save
- Auto-dismiss after 3 seconds (if using toast)

**Validation**:

- Save changes successfully
- Verify success message appears
- Verify message disappears after delay

---

### Testing & Quality Assurance

#### 13. Manual QA testing

**Estimated effort**: 30 minutes  
**Dependencies**: All previous tasks  
**Verification**: All user flows work correctly

**Test cases**:

- [ ] Update exercise name and save successfully
- [ ] Add alternative names and verify they persist
- [ ] Remove alternative names and verify they're deleted
- [ ] Update description and verify it persists
- [ ] Update image URL and verify image changes
- [ ] Clear image URL and verify placeholder shows
- [ ] Submit empty name - verify validation error
- [ ] Submit invalid URL - verify validation error
- [ ] Cancel edit - verify no changes saved
- [ ] Edit -> save -> refresh page - verify changes persist

---

#### 14. Code formatting and cleanup

**Estimated effort**: 10 minutes  
**Dependencies**: Tasks 1-13 (all implementation complete)  
**Verification**: No linting/formatting errors

**Details**:

- Run Laravel Pint on PHP files: `vendor/bin/pint`
- Run ESLint/Prettier on Vue files (if configured)
- Remove console.log statements
- Remove commented code
- Ensure consistent spacing and indentation

**Validation**:

- Run `vendor/bin/pint --test` - no errors
- Run linting commands - no errors
- Code review shows clean, readable code

---

#### 15. Update documentation (if needed)

**Estimated effort**: 10 minutes  
**Dependencies**: Task 14 (cleanup complete)  
**Verification**: Documentation reflects new functionality

**Details**:

- Update README or docs if exercise CRUD is documented
- Add any necessary inline code comments for complex logic
- Update API documentation if it exists

**Validation**:

- Documentation is accurate
- No broken links or outdated information

---

## Summary

**Total estimated effort**: ~4.5 hours

**Parallelizable work**:

- Tasks 1-3 (backend) can be done independently of tasks 5-11 (frontend)
- Task 4 (backend tests) can be done after tasks 1-3
- Tasks 5-11 (frontend) can start immediately after task 3 (route exists)

**Critical path**:

1. Backend implementation (Tasks 1-3): ~50 minutes
2. Frontend implementation (Tasks 5-11): ~2.5 hours
3. Testing and polish (Tasks 12-15): ~1 hour

**Validation checkpoints**:

- After task 3: Backend API is functional (can test with curl/Postman)
- After task 11: Full feature is complete (can test in browser)
- After task 13: Feature is production-ready

**Risk mitigation**:

- Test each task before moving to next
- Commit after each task completion
- If blocked on frontend, can continue with backend tests (task 4)
