<!-- OPENSPEC:START -->
# OpenSpec Instructions

These instructions are for AI assistants working in this project.

Always open `@/openspec/AGENTS.md` when the request:
- Mentions planning or proposals (words like proposal, spec, change, plan)
- Introduces new capabilities, breaking changes, architecture shifts, or big performance/security work
- Sounds ambiguous and you need the authoritative spec before coding

Use `@/openspec/AGENTS.md` to learn:
- How to create and apply change proposals
- Spec format and conventions
- Project structure and guidelines

Keep this managed block so 'openspec update' can refresh the instructions.

<!-- OPENSPEC:END -->

# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Development Commands

```bash
# Start all services (server, queue, logs, vite)
composer run dev

# Run tests
php artisan test                                    # All tests
php artisan test tests/Feature/ExampleTest.php     # Single file
php artisan test --filter=testName                 # Filter by name

# Code formatting
vendor/bin/pint --dirty                            # Format changed PHP files

# Frontend
npm run dev                                        # Vite dev server
npm run build                                      # Production build
npm run lint                                       # ESLint fix
npm run format                                     # Prettier format

# Generate Wayfinder routes after route changes
php artisan wayfinder:generate
```

## Project Overview

TrainerApp is a workout management system for trainers and clients. Built with Laravel 12, Inertia.js v2, Vue 3, and Tailwind v4.

## Core Domain Architecture

### Primary Models & Relationships

- **User**: Can be both trainer and client via `ClientTrainer` pivot (polymorphic roles). Uses UUID primary keys and Spatie permissions.
- **Workout**: Belongs to one client, optionally has a trainer. Uses UUID. Has boolean `is_current` flag - only ONE workout per client can be current.
- **Exercise**: Base exercise catalog with multilingual names (`ExerciseName`), categories, muscles, equipment, and tags.
- **ExerciseWorkout**: Pivot connecting Exercise to Workout with ordering.
- **ExerciseSet**: Workout sets with reps, weight, rest time (belongs to ExerciseWorkout).
- **ExerciseCategory**: Translatable categories with `type_slug`: `muscle_group`, `movement_pattern`, `difficulty`, `equipment`.

### Key Relationship Patterns

```php
// Workout chain
Workout->exerciseWorkouts()->exercise->categories->translations
Workout->client (User)
Workout->trainer (User)

// Exercise multilingual names
Exercise->names (HasMany ExerciseName with locale & is_primary)
Exercise->primaryName($locale) // Gets primary name for locale
Exercise::scopeSearchByName($query, $searchTerm) // Searches across all translated names

// Category system - always eager load with translations
->with(['exercise.categories.translations' => fn($q) => $q->where('locale', 'es')])
```

### Current Workout Logic

Only ONE workout per client can have `is_current = true`. This is enforced via Eloquent model event in `Workout::booted()`.

```php
// Marking workout as current (automatically unmarks others)
$workout->makeCurrentForClient();
// OR
$workout->update(['is_current' => true]); // Triggers model event

// Querying
Workout::currentForClient($clientId);
Workout::forClient($clientId)->current()->first();
```

### Exercise Category Display Priority

When displaying exercises, prioritize `muscle_group` categories first, fallback to `movement_pattern`:

```php
$mainCategories = $exercise->categories
    ->where('type_slug', 'muscle_group')
    ->map(fn($cat) => $cat->label('es'))
    ->filter()->values()->toArray();

if (empty($mainCategories)) {
    $mainCategories = $exercise->categories
        ->where('type_slug', 'movement_pattern')
        ->map(fn($cat) => $cat->label('es'));
}
```

## Route Patterns

- Trainer managing clients: `clients.*` (index, show, store, destroy)
- Client workouts: `clients.workouts.*` (index, show, store, destroy, make-current)
- Exercises: `exercises.*` (index, list, search, show, store, update, destroy)
- Exercise sets: `exercise-workouts.sets.*`, `exercise-sets.*`

## Vue/Inertia Patterns

- Pages in `resources/js/pages/` - some use `Page` prefix (e.g., `PageClientWorkoutIndex.vue`)
- Components in `resources/js/components/` organized by feature
- Use composition API with `<script setup>`
- Use `<Form>` component from Inertia v2 or `useForm()` helper
- Import routes via Wayfinder: `import { show } from '@/actions/App/Http/Controllers/PostController'`

## Styling Conventions

- Tailwind v4 syntax: `@import "tailwindcss"` (not `@tailwind` directives)
- Primary color for current/active states: `border-primary`, `bg-primary/5`, `text-primary`
- Dark mode via `dark:` prefix
- Use `gap-*` utilities for spacing in flex/grid layouts
- Badge variants: `primary`, `secondary`, `success`, `outline`, `destructive`
