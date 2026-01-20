# Design: Exercise Update Architecture

## Overview

This document outlines the architectural decisions for implementing exercise update functionality in the TrainerApp.

## Current State Analysis

### Existing Backend

```php
// ExerciseController currently has:
- index()      ✅ List/filter exercises
- list()       ✅ JSON API for exercise list
- show()       ✅ View single exercise with categories
- store()      ✅ Create new exercise
- search()     ✅ Search exercises by name
- destroy()    ✅ Delete exercise
- updateCategories() ✅ Update only categories

// Missing:
- update()     ❌ General exercise update
```

### Existing Frontend

```
ExercisesIndex.vue    ✅ Complete (list, search, filter, create, delete)
ExercisesShow.vue     🟡 Partial (view + category edit only)
```

### Data Model

```php
Exercise Model:
- id (primary key)
- name (legacy, nullable)
- slug (unique)
- description (nullable)
- image_path (nullable)
- timestamps

ExerciseName Model (multilingual):
- id
- exercise_id (foreign key)
- locale (es, en, etc.)
- name (the actual name)
- is_primary (boolean)
- timestamps

Relationships:
Exercise->names (HasMany ExerciseName)
Exercise->categories (BelongsToMany)
Exercise->equipment (BelongsToMany)
Exercise->tags (BelongsToMany)
```

## Design Decisions

### 1. Update Scope

**Decision**: Focus on core exercise attributes only in v1

- ✅ Primary name (through ExerciseName)
- ✅ Alternative names (through ExerciseName)
- ✅ Description
- ✅ Image path (URL input)
- ❌ Categories (already handled by updateCategories)
- ❌ Equipment (future enhancement)
- ❌ Tags (future enhancement)
- ❌ Media gallery (future enhancement)
- ❌ Instructions (future enhancement)

**Rationale**: Delivers immediate value without overwhelming complexity. Categories already work. Equipment/tags/instructions are less frequently edited.

### 2. Multilingual Name Handling

**Decision**: Update primary name and manage alternative names through ExerciseName records

```php
// Update flow:
1. Find current primary name for locale 'es'
2. Update that ExerciseName record (keep is_primary=true)
3. For alternative names:
   - Delete ExerciseName records not in new alternative_names array (where is_primary=false)
   - Create new ExerciseName records for new names (is_primary=false)
```

**Rationale**:

- Maintains multilingual architecture
- Preserves exercise ID (no breaking workout references)
- Follows existing pattern from store() method

### 3. Backend Route Structure

**Decision**: Add single update route handling all fields

```php
// routes/web.php
Route::patch('exercises/{exercise}', [ExerciseController::class, 'update'])
    ->name('exercises.update');
```

**Alternative considered**: Separate routes for each field type (like updateCategories)
**Rejected because**: Would require multiple API calls and complicate UI state management

### 4. Frontend Edit UI Pattern

**Decision**: Use shadcn Dialog with form inside ExercisesShow.vue

**Pattern**:

```vue
<Dialog v-model:open="showEditDialog">
  <DialogTrigger>
    <Button>Editar ejercicio</Button>
  </DialogTrigger>
  <DialogContent>
    <form @submit.prevent="updateExercise">
      <!-- Form fields -->
    </form>
  </DialogContent>
</Dialog>
```

**Alternative considered**: Inline editing with toggle between view/edit modes
**Rejected because**: Dialog pattern is already used for create/delete, provides better focus

### 5. Form Validation Strategy

**Decision**: Use Laravel Form Request with frontend validation hints

```php
// UpdateExerciseRequest rules:
[
    'name' => 'required|string|max:255',
    'alternative_names' => 'nullable|array',
    'alternative_names.*' => 'string|max:255',
    'description' => 'nullable|string|max:2000',
    'image_path' => 'nullable|url|max:500',
]
```

**Rationale**:

- Backend validation is authoritative
- Frontend can show immediate feedback for UX
- No need to validate name uniqueness (ExerciseName allows duplicates across exercises)

### 6. Image Management Approach

**Decision**: Simple URL input field for v1

**Future consideration**: File upload with storage
**Rationale**: URL input is faster to implement, works with existing image_path column

### 7. Alternative Names UX

**Decision**: Use multi-value input (tag-style) for alternative names

**Implementation**: Array of input fields with add/remove buttons, or use existing tag input component if available

**Rationale**:

- Intuitive for managing lists
- Clear visual separation from primary name
- Follows common UI patterns

### 8. Response Handling

**Decision**: Use Inertia's back() with partial reload

```vue
updateForm.patch(`/exercises/${exercise.id}`, { onSuccess: () => { showEditDialog.value = false; // Inertia auto-reloads page props }, });
```

**Rationale**: Keeps page state fresh, simple to implement, consistent with existing patterns

## Component Structure

### Backend

```
app/
├── Http/
│   ├── Controllers/
│   │   └── ExerciseController.php (add update method)
│   └── Requests/
│       └── UpdateExerciseRequest.php (new)
├── Models/
│   ├── Exercise.php (existing)
│   └── ExerciseName.php (existing)
```

### Frontend

```
resources/js/
└── pages/
    └── Exercises/
        └── ExercisesShow.vue (add edit dialog + form)
```

## Data Flow

### Update Exercise Flow

```
1. User clicks "Edit" button
   ↓
2. Dialog opens with form pre-populated
   ↓
3. User modifies fields
   ↓
4. User submits form
   ↓
5. Frontend validates (optional, immediate feedback)
   ↓
6. Inertia PATCH request to /exercises/{id}
   ↓
7. UpdateExerciseRequest validates
   ↓
8. ExerciseController@update processes:
   - Updates Exercise model (description, image_path, slug)
   - Updates primary ExerciseName
   - Syncs alternative ExerciseNames
   ↓
9. Returns back() with success message
   ↓
10. Inertia reloads page props
    ↓
11. UI updates with new data
```

## Error Handling

### Validation Errors

- Display inline under each field
- Use Inertia's form.errors object
- Highlight invalid fields with red border

### Server Errors

- Show toast notification with error message
- Keep dialog open so user doesn't lose input
- Log errors for debugging

### Concurrent Edits

- Accept last-write-wins for v1
- Consider optimistic locking (version field) in future

## Testing Strategy

### Backend Tests

```php
// Feature test: tests/Feature/ExerciseUpdateTest.php
- test_trainer_can_update_exercise_name()
- test_trainer_can_update_exercise_description()
- test_trainer_can_update_exercise_image()
- test_trainer_can_add_alternative_names()
- test_trainer_can_remove_alternative_names()
- test_update_requires_valid_data()
- test_update_maintains_exercise_id()
- test_unauthorized_user_cannot_update()
```

### Frontend Tests

```typescript
// Component test or E2E test
- Can open edit dialog
- Can submit form with valid data
- Shows validation errors for invalid data
- Updates UI after successful save
- Handles server errors gracefully
```

## Performance Considerations

### Database Queries

- Single update to Exercise table
- N queries for ExerciseName updates (acceptable for typical 1-3 names)
- Consider batch insert/delete if performance issue arises

### Frontend

- No heavy computations
- Standard Inertia form handling (proven pattern)
- Dialog lazy loads (no performance impact when closed)

## Migration Path

### Backward Compatibility

- ✅ No breaking changes to existing routes
- ✅ Existing exercise data remains valid
- ✅ Categories update route remains unchanged
- ✅ No database schema changes needed

### Deployment

1. Deploy backend changes (route + controller)
2. Deploy frontend changes (UI update)
3. No migration needed (uses existing schema)
4. No data transformation needed

## Future Enhancements

1. **File upload**: Replace URL input with drag-drop file upload
2. **Equipment editing**: Add equipment selector to form
3. **Tags editing**: Add tag selector to form
4. **Bulk editing**: Select and edit multiple exercises
5. **Audit log**: Track who changed what and when
6. **Version history**: Allow rolling back changes
7. **Rich text description**: Replace textarea with WYSIWYG editor
8. **Image preview**: Show thumbnail of image_path URL
9. **Optimistic locking**: Prevent concurrent edit conflicts

## Open Questions

None - all decisions documented above.
