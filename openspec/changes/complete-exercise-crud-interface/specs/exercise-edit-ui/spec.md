# Spec: Exercise Edit User Interface

## ADDED Requirements

### Requirement: Exercise Edit Dialog

The system MUST provide an intuitive dialog interface that allows trainers to edit exercise details, following the application's design system and providing immediate feedback.

#### Scenario: Open edit dialog from exercise show page

**Given** a trainer is viewing exercise show page at `/exercises/1`  
**When** they click the "Editar ejercicio" button  
**Then** an edit dialog opens  
**And** the dialog displays "Editar ejercicio" as the title  
**And** the form is pre-populated with:

- Current primary name in "Nombre" field
- Current alternative names as tags/chips
- Current description in "Descripción" textarea
- Current image URL in "Imagen (URL)" field

#### Scenario: Edit primary exercise name

**Given** the edit dialog is open  
**And** the "Nombre" field shows "Press de banca"  
**When** the trainer changes the name to "Press de pecho con barra"  
**And** clicks "Guardar cambios"  
**Then** a PATCH request is sent to `/exercises/{id}` with the new name  
**And** the dialog shows a loading state (button disabled, "Guardando..." text)  
**And** on success, the dialog closes  
**And** the exercise show page refreshes with the updated name

#### Scenario: Add alternative exercise name

**Given** the edit dialog is open  
**And** the "Nombres alternativos" field shows ["Bench press"]  
**When** the trainer adds "Press plano con barra" to alternative names  
**And** clicks "Guardar cambios"  
**Then** the PATCH request includes alternative_names: ["Bench press", "Press plano con barra"]  
**And** on success, the exercise show page displays both alternative names

#### Scenario: Remove alternative exercise name

**Given** the edit dialog is open  
**And** the "Nombres alternativos" field shows ["Bench press", "Press plano"]  
**When** the trainer removes "Press plano" from the list  
**And** clicks "Guardar cambios"  
**Then** the PATCH request includes alternative_names: ["Bench press"]  
**And** on success, the exercise show page displays only "Bench press"

#### Scenario: Update exercise description

**Given** the edit dialog is open  
**When** the trainer enters a new description: "Ejercicio fundamental para pectorales"  
**And** clicks "Guardar cambios"  
**Then** the PATCH request includes the new description  
**And** on success, the exercise show page displays the updated description

#### Scenario: Update exercise image URL

**Given** the edit dialog is open  
**When** the trainer enters a new image URL: "https://example.com/new-image.jpg"  
**And** clicks "Guardar cambios"  
**Then** the PATCH request includes the new image_path  
**And** on success, the exercise show page displays the new image

#### Scenario: Clear exercise image

**Given** the edit dialog is open  
**And** the "Imagen (URL)" field contains a URL  
**When** the trainer clears the image URL field  
**And** clicks "Guardar cambios"  
**Then** the PATCH request includes image_path: null  
**And** on success, the exercise show page shows the default image placeholder

#### Scenario: Cancel edit without saving

**Given** the edit dialog is open  
**And** the trainer has made changes to the form  
**When** they click "Cancelar" or close the dialog  
**Then** the dialog closes  
**And** no PATCH request is sent  
**And** the exercise data remains unchanged

#### Scenario: Validation error prevents save

**Given** the edit dialog is open  
**When** the trainer clears the "Nombre" field (required)  
**And** clicks "Guardar cambios"  
**Then** client-side validation shows error "El nombre es requerido"  
**And** no PATCH request is sent  
**And** the dialog remains open

#### Scenario: Server validation error displays feedback

**Given** the edit dialog is open  
**When** the trainer enters an invalid image URL  
**And** clicks "Guardar cambios"  
**Then** a PATCH request is sent  
**And** the server returns 422 with validation errors  
**And** the error "La URL de la imagen no es válida" displays under the image field  
**And** the dialog remains open with the user's input preserved

#### Scenario: Network error shows error message

**Given** the edit dialog is open  
**When** the trainer clicks "Guardar cambios"  
**And** the network request fails  
**Then** an error toast/notification appears: "Error al guardar cambios"  
**And** the dialog remains open  
**And** the form remains editable

### Requirement: Edit Button Placement

The edit button MUST be prominently placed and easily discoverable on the exercise show page.

#### Scenario: Edit button is visible on exercise show page

**Given** a trainer is viewing exercise show page  
**Then** an "Editar ejercicio" button is visible  
**And** the button uses primary color styling (consistent with app theme)  
**And** the button is positioned near the exercise name/header section

#### Scenario: Edit button shows appropriate icon

**Given** the "Editar ejercicio" button is visible  
**Then** the button displays a pencil/edit icon (from lucide-vue-next)  
**And** the icon is positioned before or within the button text

### Requirement: Form Design and Accessibility

The edit form MUST follow shadcn/ui design patterns and be accessible to all users.

#### Scenario: Form follows design system

**Given** the edit dialog is open  
**Then** the form uses shadcn/ui components:

- Dialog component for modal
- Input components for text fields
- Textarea component for description
- Label components for field labels
- Button components for actions
  **And** the form follows Tailwind v4 styling conventions
  **And** the form uses the primary color palette from app.css

#### Scenario: Form fields have proper labels

**Given** the edit dialog is open  
**Then** each field has an associated label:

- "Nombre" for primary name input
- "Nombres alternativos" for alternative names
- "Descripción" for description textarea
- "Imagen (URL)" for image URL input

#### Scenario: Form is keyboard navigable

**Given** the edit dialog is open  
**When** the trainer uses Tab key  
**Then** focus moves sequentially through:

1. Nombre field
2. Nombres alternativos field
3. Descripción field
4. Imagen URL field
5. Cancelar button
6. Guardar cambios button

#### Scenario: Form can be submitted with Enter key

**Given** the edit dialog is open  
**And** the trainer has focus on any text input  
**When** they press Enter key  
**Then** the form is submitted (same as clicking "Guardar cambios")

#### Scenario: Form can be cancelled with Escape key

**Given** the edit dialog is open  
**When** the trainer presses Escape key  
**Then** the dialog closes without saving

### Requirement: Loading and Success States

The UI MUST provide clear feedback during save operations and on successful completion.

#### Scenario: Button shows loading state during save

**Given** the edit dialog is open  
**When** the trainer clicks "Guardar cambios"  
**Then** the "Guardar cambios" button:

- Changes text to "Guardando..."
- Is disabled (prevents double-submit)
- May show a loading spinner icon

#### Scenario: Success feedback after save

**Given** the edit dialog is open  
**When** the trainer successfully saves changes  
**Then** the dialog closes smoothly (no flash of content)  
**And** the exercise show page displays the updated information  
**And** optionally, a success toast appears: "Ejercicio actualizado correctamente"

### Requirement: Alternative Names Input

The alternative names field MUST support adding, removing, and editing multiple name values.

#### Scenario: Alternative names displayed as tags

**Given** the edit dialog is open  
**And** the exercise has alternative names: ["Bench press", "Press plano"]  
**Then** the alternative names are displayed as tag/badge components  
**And** each tag has a remove button (X icon)

#### Scenario: Add new alternative name

**Given** the edit dialog is open  
**When** the trainer types a new name in the alternative names input  
**And** presses Enter or clicks an "Add" button  
**Then** a new tag appears with the entered name  
**And** the input field clears for the next entry

#### Scenario: Remove alternative name

**Given** the edit dialog is open  
**And** an alternative name tag "Press plano" is displayed  
**When** the trainer clicks the X icon on the tag  
**Then** the tag is removed from the list  
**And** the change will be saved when the form is submitted

#### Scenario: Alternative names field is optional

**Given** the edit dialog is open  
**When** the trainer removes all alternative names  
**And** clicks "Guardar cambios"  
**Then** the save succeeds  
**And** the exercise has no alternative names

## MODIFIED Requirements

None - this is a new UI capability.

## REMOVED Requirements

None - this is a new UI capability.
