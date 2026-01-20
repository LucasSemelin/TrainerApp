# Project Context

## Purpose
TrainerApp is a Laravel-based workout management system that connects trainers with their clients. The application enables:
- Trainers to create and manage workout routines for their clients
- Clients to view and track their assigned workouts
- Multilingual exercise catalog with detailed categorization
- Workout progression tracking with sets, reps, and weights

## Tech Stack

### Backend
- **PHP 8.2+** with Laravel 12.x
- **MySQL** as primary database
- **Laravel Fortify** (v1.30+) for authentication
- **Laravel Socialite** (v5.24+) for social login
- **Inertia.js v2** for SPA-like experience
- **Spatie Laravel Permission** (v6.21+) for role-based access control
- **Laravel Wayfinder** for typed route generation

### Frontend
- **Vue 3.5+** with Composition API (`<script setup>`)
- **TypeScript 5.2+**
- **Tailwind CSS v4** with Vite plugin (`@tailwindcss/vite`)
- **Reka UI** (v2.7+) for headless UI primitives
- **shadcn-vue** components built on Reka UI
- **@vueuse/core** (v12+) for composition utilities
- **lucide-vue-next** for icons
- **class-variance-authority** + **clsx** + **tailwind-merge** for styling utilities
- **tw-animate-css** for animations

### Development Tools
- **Vite 7** for bundling
- **Pest 4** for testing
- **Laravel Pint** for PHP formatting
- **ESLint 9** & **Prettier 3** for JS/TS/Vue formatting
- **Laravel Herd** for local development
- **Laravel Boost** for MCP integration

## Project Conventions

### Code Style
- **PHP**: Laravel Pint for PSR-12 compliance, always use type hints
- **Vue**: Composition API with `<script setup>`, use TypeScript
- **Naming**:
  - Vue pages: `Page` prefix (e.g., `PageClientWorkoutIndex.vue`)
  - Vue components: PascalCase, organized by feature
  - PHP Models: Singular PascalCase (e.g., `Exercise`, `Workout`)
  - PHP Controllers: Singular resource name + `Controller`
- **Tailwind v4 syntax**: Use `@import "tailwindcss"` (not `@tailwind` directives)
- **Spacing**: Use `gap-*` utilities instead of margins in flex/grid layouts
- **Dark mode**: Support via `dark:` prefix
- **Primary color states**: `border-primary`, `bg-primary/5`, `text-primary`

### Architecture Patterns
- **MVC with Inertia.js**: Server-side routing, Vue components for views
- **Translatable models**: Categories and exercise names support multiple locales
- **Polymorphic relationships**: Users can be both trainers and clients
- **Pivot tables with metadata**: `ExerciseWorkout` includes ordering, `ExerciseSet` includes reps/weight/rest
- **UUID primary keys**: Workouts use UUIDs
- **Eloquent scopes**: Use for common queries (e.g., `scopeCurrent()`, `scopeForClient()`)
- **Eager loading required**: Always eager load translations with `->with(['categories.translations' => fn($q) => $q->where('locale', 'es')])`

### Route Patterns
- **Clients**: `clients.*` (index, show, store, destroy)
- **Client Workouts**: `clients.workouts.*` (index, show, store, destroy, make-current)
- **Workouts**: `workouts.*` (index, store, destroy, exercises.store)
- **Exercises**: `exercises.*` (index, list, search, show, store, update, destroy)
- **Exercise Sub-resources**: `exercises.{categories,equipment,tags,instructions,media}.*`
- **Exercise Sets**: `exercise-workouts.sets.*`, `exercise-sets.*`
- **Profiles**: `profiles.*` (update-birthdate, update-gender)
- **Settings**: `settings/profile`, `settings/password`, `settings/appearance`, `settings/two-factor`
- **Auth**: Google OAuth via `auth.google.*`

### Testing Strategy
- **Pest 4** as primary testing framework
- **Feature tests** for controller/integration testing
- **Unit tests** for model logic and business rules
- **Database**: Use `RefreshDatabase` trait
- **Factories**: Use model factories for test data
- Run tests: `php artisan test` or `composer test`

### Git Workflow
- **Branches**: `main` (production), `develop` (active development)
- **Current branch**: `develop`
- Feature branches from `develop`
- Commit messages should be clear and descriptive
- Test before committing

## Domain Context

### Core Business Rules
1. **One Current Workout**: Only ONE workout per client can have `is_current = true`
   - Enforced via Eloquent model event in `Workout::booted()`
   - Use `$workout->makeCurrentForClient()` for safe toggling
   
2. **Exercise Category Priority**: When displaying exercises:
   - **Primary**: Show `muscle_group` categories (Pecho, Espalda, etc.)
   - **Fallback**: Show `movement_pattern` categories if no muscle_group exists

3. **Multilingual Exercise System**:
   - Exercise names stored in `ExerciseName` model with locale & is_primary flag
   - Categories have translations via `ExerciseCategoryTranslation`
   - Always filter translations by locale (usually 'es')

### Key Models & Relationships
```php
// User (trainer/client duality)
User->clientTrainers (as trainer)
User->trainerClients (as client)
User->workouts (as client)
User->createdWorkouts (as trainer)
User->profile (Profile)

// Workout (core entity)
Workout->client (User)
Workout->trainer (User)
Workout->exerciseWorkouts()->exercise
Workout->exerciseWorkouts()->sets
Workout->sessions (WorkoutSession)

// Exercise (catalog)
Exercise->names (HasMany ExerciseName with locale & is_primary)
Exercise->categories (BelongsToMany via CategoryExercise)
Exercise->muscles (BelongsToMany via ExerciseMuscle with role)
Exercise->equipment (BelongsToMany via ExerciseEquipment)
Exercise->tags (BelongsToMany via ExerciseTag)
Exercise->media (HasMany ExerciseMedia)
Exercise->instructionSets (HasMany ExerciseInstructionSet)
Exercise->difficulty (BelongsTo Difficulty)
Exercise->forceType (BelongsTo ForceType)
Exercise->mechanic (BelongsTo Mechanic)
Exercise->movementPattern (BelongsTo MovementPattern)
Exercise->primaryName($locale) // Helper method
Exercise->scopeSearchByName($query, $term) // Scope

// Lookup Tables (all follow same pattern with translations)
Muscle, MuscleTranslation, MuscleRole, MuscleRoleTranslation
Equipment, EquipmentTranslation
Tag, TagTranslation
Difficulty, DifficultyTranslation
ForceType, ForceTypeTranslation
Mechanic, MechanicTranslation
MovementPattern, MovementPatternTranslation
MediaType, MediaTypeTranslation
ExerciseCategory, ExerciseCategoryTranslation
```

### Lookup Table Pattern
All lookup tables follow a consistent pattern for multilingual support:
```php
// Model (e.g., Muscle)
Muscle->translations (HasMany MuscleTranslation)
Muscle->label($locale) // Returns translated name for locale

// Translation table has: id, {parent}_id, locale, name
MuscleTranslation: id, muscle_id, locale, name
```

### Locale Handling
- Default locale: Spanish ('es')
- All user-facing content should support Spanish
- Use `label($locale)` helper on translatable models

## Important Constraints

### Technical Constraints
1. **Always eager load translations** to avoid N+1 queries
2. **Use model scopes** for filtering (e.g., `Workout::current()`, `Workout::forClient($id)`)
3. **UUID for Workout IDs** - don't assume auto-increment integers
4. **Current workout enforcement** - use provided methods, don't manually update is_current
5. **Inertia responses** - controllers must return via `inertia()` helper

### Business Constraints
1. A workout belongs to exactly one client
2. A workout can optionally have a trainer (for tracking who created it)
3. Exercise ordering matters - use `order` column in `ExerciseWorkout` pivot
4. Sets belong to specific exercise-workout combinations via `ExerciseWorkout`

### UI/UX Constraints
1. Badge variants: `primary`, `secondary`, `success`, `outline`, `destructive`
2. Highlight active/current items with: `border-2 border-primary rounded-lg px-4 py-3`
3. Dialog components for all CRUD operations
4. Use `useForm()` from '@inertiajs/vue3' for all forms

## External Dependencies

### Authentication
- **Laravel Fortify**: Handles authentication flows
- **Laravel Socialite**: Social authentication (if configured)

### Permissions
- **Spatie Laravel Permission**: Role and permission management
- Roles: `admin`, `trainer`, `client`

### Development Services
- **Laravel Herd**: Local development environment (serves at https://trainer.test)
- **Concurrently**: Runs multiple dev processes (server, queue, logs, vite)

### External APIs
- None currently documented (can be added as project grows)

## Additional Notes

### Commands
- `composer dev`: Runs full dev environment (server, queue, logs, vite)
- `composer test`: Clears config and runs tests
- `npm run dev`: Vite dev server only
- `npm run lint`: ESLint fix
- `npm run format`: Prettier format
- `php artisan wayfinder:generate`: Generate typed routes after route changes

### File Organization
- **Controllers**: `app/Http/Controllers/`
- **Models**: `app/Models/`
- **Actions**: `app/Actions/` (e.g., CreateClient, RemoveClient)
- **Policies**: `app/Policies/`
- **Form Requests**: `app/Http/Requests/`
- **Vue Pages**: `resources/js/pages/`
- **Vue Components**: `resources/js/components/`
- **UI Components**: `resources/js/components/ui/` (shadcn components)
- **Routes**: Separated by purpose (web.php, auth.php, settings.php, console.php)
- **OpenSpec**: `openspec/` (change proposals and project documentation)
