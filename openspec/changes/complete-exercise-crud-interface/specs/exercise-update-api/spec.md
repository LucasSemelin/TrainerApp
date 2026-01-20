# Spec: Exercise Update API

## ADDED Requirements

### Requirement: Exercise Update Endpoint

The system MUST provide an API endpoint that allows trainers to update core exercise attributes while maintaining multilingual name support and preserving exercise identity across updates.

#### Scenario: Update exercise with new primary name

**Given** an authenticated trainer with access to the exercise library  
**And** an existing exercise with id "1" and primary name "Press de banca"  
**When** the trainer sends PATCH to `/exercises/1` with:

```json
{
    "name": "Press de pecho con barra",
    "description": "Ejercicio fundamental para desarrollo del pecho",
    "image_path": "https://example.com/bench-press.jpg"
}
```

**Then** the response status is 200 or 302 (redirect)  
**And** the exercise primary name is updated to "Press de pecho con barra"  
**And** the exercise description is updated  
**And** the exercise image_path is updated  
**And** the exercise ID remains "1" (no new record created)  
**And** the exercise slug is regenerated from the new name  
**And** the ExerciseName record for locale 'es' with is_primary=true is updated

#### Scenario: Update exercise with alternative names

**Given** an authenticated trainer  
**And** an existing exercise with id "2" and primary name "Sentadilla"  
**When** the trainer sends PATCH to `/exercises/2` with:

```json
{
    "name": "Sentadilla con barra",
    "alternative_names": ["Squat", "Sentadilla trasera", "Back squat"]
}
```

**Then** the response status is 200 or 302  
**And** the exercise has 4 ExerciseName records:

- 1 with is_primary=true, name="Sentadilla con barra"
- 3 with is_primary=false for the alternative names
  **And** any previous alternative names not in the new list are removed

#### Scenario: Update exercise description only

**Given** an authenticated trainer  
**And** an existing exercise with id "3"  
**When** the trainer sends PATCH to `/exercises/3` with:

```json
{
    "name": "Peso muerto",
    "description": "Descripción actualizada con más detalles técnicos"
}
```

**Then** the response status is 200 or 302  
**And** the exercise description is updated  
**And** the exercise name remains unchanged  
**And** no ExerciseName records are created or deleted

#### Scenario: Update exercise with invalid data

**Given** an authenticated trainer  
**And** an existing exercise with id "4"  
**When** the trainer sends PATCH to `/exercises/4` with:

```json
{
    "name": "",
    "image_path": "not-a-valid-url"
}
```

**Then** the response status is 422 (validation error)  
**And** the response includes validation errors for:

- "name" field (required)
- "image_path" field (must be valid URL)
  **And** no database changes are made

#### Scenario: Update exercise removes image

**Given** an authenticated trainer  
**And** an existing exercise with id "5" and image_path set  
**When** the trainer sends PATCH to `/exercises/5` with:

```json
{
    "name": "Remo con barra",
    "image_path": null
}
```

**Then** the response status is 200 or 302  
**And** the exercise image_path is set to null  
**And** the exercise remains in the database

#### Scenario: Unauthorized user cannot update exercise

**Given** an unauthenticated user  
**When** they send PATCH to `/exercises/1` with any data  
**Then** the response status is 401 or 302 (redirect to login)  
**And** no database changes are made

### Requirement: Exercise Update Request Validation

The system MUST validate all exercise update requests to prevent invalid data from corrupting the exercise library.

#### Scenario: Validation rules enforce data quality

**Given** the UpdateExerciseRequest validation rules  
**Then** the following rules must be enforced:

- "name": required, string, max 255 characters
- "alternative_names": optional, array
- "alternative_names.\*": string, max 255 characters each
- "description": optional, string, max 2000 characters
- "image_path": optional, valid URL, max 500 characters

#### Scenario: Name too long is rejected

**Given** an authenticated trainer  
**When** they submit a name with 300 characters  
**Then** validation fails with "name" error  
**And** the error message indicates max length is 255

#### Scenario: Invalid image URL is rejected

**Given** an authenticated trainer  
**When** they submit image_path as "htp://broken-url"  
**Then** validation fails with "image_path" error  
**And** the error message indicates URL format is invalid

### Requirement: Multilingual Name Management

The system MUST properly manage ExerciseName records during updates, maintaining the integrity of the multilingual name system.

#### Scenario: Primary name update preserves locale and primary flag

**Given** an exercise with ExerciseName record: locale='es', name='Press militar', is_primary=true  
**When** the exercise is updated with name='Press de hombros'  
**Then** the same ExerciseName record is updated (not replaced)  
**And** locale remains 'es'  
**And** is_primary remains true  
**And** name is changed to 'Press de hombros'

#### Scenario: Alternative names sync correctly

**Given** an exercise with alternative names: ['Alt 1', 'Alt 2', 'Alt 3']  
**When** the exercise is updated with alternative_names=['Alt 2', 'Alt 4', 'Alt 5']  
**Then** ExerciseName records for 'Alt 1' and 'Alt 3' are deleted  
**And** ExerciseName record for 'Alt 2' is preserved  
**And** ExerciseName records for 'Alt 4' and 'Alt 5' are created  
**And** all alternative name records have is_primary=false

#### Scenario: Slug regeneration on name change

**Given** an exercise with name='Press de banca' and slug='press-de-banca'  
**When** the name is updated to 'Press de pecho'  
**Then** the slug is regenerated to 'press-de-pecho'  
**And** the slug remains unique across all exercises

## MODIFIED Requirements

None - this is a new capability.

## REMOVED Requirements

None - this is a new capability.
