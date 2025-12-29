<trainerapp-context>
# TrainerApp - Workout Management System

This is a Laravel-based Trainer/Client workout management application. Before making any changes, understand the core domain architecture.

## Core Domain Models & Relationships

### Primary Models
- **User**: Can be both trainer and client (polymorphic roles via `ClientTrainer` pivot)
- **Workout**: Belongs to one client, optionally has a trainer. Has UUID primary key and boolean `is_current` flag
- **Exercise**: Base exercise catalog with translations, categories, and metadata
- **ExerciseWorkout**: Pivot connecting Exercise to Workout with ordering
- **ExerciseSet**: Workout sets with reps, weight, rest time (belongs to ExerciseWorkout)
- **ExerciseCategory**: Translatable categories with types: `muscle_group`, `movement_pattern`, `difficulty`, `equipment`

### Key Relationships
```php
// Workout relationships
Workout->exerciseWorkouts()->exercise->categories->translations
Workout->client (User)
Workout->trainer (User)

// Exercise relationships  
Exercise->categories (BelongsToMany with category_exercise pivot)
Exercise->names (HasMany ExerciseName with locale & is_primary)

// Category system
ExerciseCategory has type_slug ('muscle_group', 'movement_pattern', etc)
ExerciseCategory->translations (HasMany with 'locale' field)
```

## Critical Domain Logic

### Current Workout Pattern
- Only ONE workout per client can be `is_current = true`
- Automatic enforcement via Eloquent model event in `Workout::booted()`
- When marking workout current, ALL others for that client are automatically unmarked
- Use `Workout::scopeCurrent()` and `Workout::scopeForClient($clientId)` for queries
- Method: `$workout->makeCurrentForClient()` handles the transaction

### Exercise Category Display Priority
When showing exercises, prioritize categories:
1. **First choice**: `muscle_group` categories (e.g., "Pecho", "Espalda")
2. **Fallback**: `movement_pattern` categories if no muscle_group exists

**Example from ClientWorkoutController:**
```php
$mainCategories = $exercise->categories
    ->where('type_slug', 'muscle_group')
    ->map(fn($cat) => $cat->label('es'))
    ->filter()->values()->toArray();

if (empty($mainCategories)) {
    $mainCategories = $exercise->categories
        ->where('type_slug', 'movement_pattern')
        ->map(fn($cat) => $cat->label('es'))
        ->filter()->values()->toArray();
}
```

**CRITICAL**: When loading exercises with categories, ALWAYS eager load with translations:
```php
->with(['exercise.categories.translations' => fn($q) => $q->where('locale', 'es')])
```

### Exercise Search & Naming
- Exercise has multilingual names via `ExerciseName` model
- `Exercise->primaryName($locale)` gets primary name for locale
- Search uses `Exercise::scopeSearchByName($query, $searchTerm)` - searches across all translated names
- Example: "press militar" finds exercises with that name in ANY ExerciseName record

## Vue/Inertia Patterns

### Component Structure
- Pages are in `resources/js/pages/` with `Page` prefix (e.g., `PageClientWorkoutIndex.vue`)
- Components in `resources/js/components/` organized by feature
- Use composition API with `<script setup>` syntax
- Props come from Inertia controller returns: `inertia('PageName', ['prop' => $data])`

### Common UI Patterns
- Badge component with variants: `primary`, `secondary`, `success`, `outline`, `destructive`
- Secondary variant style: `border-primary/30 bg-primary/5 text-primary`
- Dialog components for CRUD operations (e.g., `WorkoutExercisesAddDialog.vue`)
- Use `useForm()` from '@inertiajs/vue3' for forms with error handling

### Styling Conventions
- Tailwind v4 syntax (use `@import "tailwindcss"` not `@tailwind` directives)
- Primary color for current/active states: `border-primary`, `bg-primary/5`, `text-primary`
- Dark mode support via `dark:` prefix
- Spacing with `gap-*` utilities instead of margins in flex/grid layouts
- Highlight important cards: `border-2 border-primary rounded-lg px-4 py-3`

## Route Patterns

### Workout Management
```php
// Trainer managing workouts
Route::post('workouts', [WorkoutController::class, 'store'])
Route::post('workouts/{workout}/exercises', [WorkoutController::class, 'addExercise'])

// Client viewing workouts
Route::get('clients/{client}/workouts', [ClientWorkoutController::class, 'index'])
Route::get('clients/{client}/workouts/{workout}', [ClientWorkoutController::class, 'show'])
Route::patch('clients/{client}/workouts/{workout}/make-current', [ClientWorkoutController::class, 'makeCurrent'])
```

### Key Route Naming
- Trainer routes: `workouts.*`
- Client routes: `clients.workouts.*`
- Use named routes: `route('clients.workouts.show', [$client, $workout])`

## Testing Conventions
- Use Pest v4 with browser testing capabilities
- Tests in `tests/Feature/` and `tests/Unit/`
- Example: `tests/Feature/WorkoutCurrentTest.php` for current workout behavior
- Use factories: `Workout::factory()->create(['is_current' => true])`

## Common Tasks & Solutions

### Adding a new exercise to a workout
1. POST to `workouts/{workout}/exercises` with `exercise_id`
2. Controller creates `ExerciseWorkout` record with auto-incremented `order`
3. MUST eager load categories with translations for response
4. Return processed categories (muscle_group priority, movement_pattern fallback)

### Displaying workout exercises
1. Load via `ExerciseWorkout::where('workout_id', $id)->with([...])`
2. Eager load: `'exercise.categories.translations'`, `'sets'`
3. Process categories using priority logic (muscle_group → movement_pattern)
4. Return as array with `exercise.categories` already processed

### Marking workout as current
- Use `$workout->makeCurrentForClient()` - handles automatic unmarking of others
- Alternative: `$workout->update(['is_current' => true])` - triggers model event
- Query current: `Workout::currentForClient($clientId)` static method

## AI Agent Integration (Vizra ADK)
- IntentParserAgent in `app/Agents/IntentParserAgent.php`
- AgentResponseParser service handles agent output processing
- See Vizra ADK guidelines below for agent/tool development

</trainerapp-context>

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to enhance the user's satisfaction building Laravel applications.

## Foundational Context
This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.3.25
- inertiajs/inertia-laravel (INERTIA) - v2
- laravel/fortify (FORTIFY) - v1
- laravel/framework (LARAVEL) - v12
- laravel/prompts (PROMPTS) - v0
- laravel/wayfinder (WAYFINDER) - v0
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- pestphp/pest (PEST) - v4
- phpunit/phpunit (PHPUNIT) - v12
- @inertiajs/vue3 (INERTIA) - v2
- tailwindcss (TAILWINDCSS) - v4
- vue (VUE) - v3
- @laravel/vite-plugin-wayfinder (WAYFINDER) - v0
- eslint (ESLINT) - v9
- prettier (PRETTIER) - v3


## Conventions
- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts
- Do not create verification scripts or tinker when tests cover that functionality and prove it works. Unit and feature tests are more important.

## Application Structure & Architecture
- Stick to existing directory structure - don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling
- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Replies
- Be concise in your explanations - focus on what's important rather than explaining obvious details.

## Documentation Files
- You must only create documentation files if explicitly requested by the user.


=== boost rules ===

## Laravel Boost
- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan
- Use the `list-artisan-commands` tool when you need to call an Artisan command to double check the available parameters.

## URLs
- Whenever you share a project URL with the user you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain / IP, and port.

## Tinker / Debugging
- You should use the `tinker` tool when you need to execute PHP to debug code or query Eloquent models directly.
- Use the `database-query` tool when you only need to read from the database.

## Reading Browser Logs With the `browser-logs` Tool
- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)
- Boost comes with a powerful `search-docs` tool you should use before any other approaches. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation specific for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- The 'search-docs' tool is perfect for all Laravel related packages, including Laravel, Inertia, Livewire, Filament, Tailwind, Pest, Nova, Nightwatch, etc.
- You must use this tool to search for Laravel-ecosystem documentation before falling back to other approaches.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic based queries to start. For example: `['rate limiting', 'routing rate limiting', 'routing']`.
- Do not add package names to queries - package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax
- You can and should pass multiple queries at once. The most relevant results will be returned first.

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit"
3. Quoted Phrases (Exact Position) - query="infinite scroll" - Words must be adjacent and in that order
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit"
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms


=== php rules ===

## PHP

- Always use curly braces for control structures, even if it has one line.

### Constructors
- Use PHP 8 constructor property promotion in `__construct()`.
    - <code-snippet>public function __construct(public GitHub $github) { }</code-snippet>
- Do not allow empty `__construct()` methods with zero parameters.

### Type Declarations
- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<code-snippet name="Explicit Return Types and Method Params" lang="php">
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
</code-snippet>

## Comments
- Prefer PHPDoc blocks over comments. Never use comments within the code itself unless there is something _very_ complex going on.

## PHPDoc Blocks
- Add useful array shape type definitions for arrays when appropriate.

## Enums
- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.


=== herd rules ===

## Laravel Herd

- The application is served by Laravel Herd and will be available at: https?://[kebab-case-project-dir].test. Use the `get-absolute-url` tool to generate URLs for the user to ensure valid URLs.
- You must not run any commands to make the site available via HTTP(s). It is _always_ available through Laravel Herd.


=== inertia-laravel/core rules ===

## Inertia Core

- Inertia.js components should be placed in the `resources/js/Pages` directory unless specified differently in the JS bundler (vite.config.js).
- Use `Inertia::render()` for server-side routing instead of traditional Blade views.
- Use `search-docs` for accurate guidance on all things Inertia.

<code-snippet lang="php" name="Inertia::render Example">
// routes/web.php example
Route::get('/users', function () {
    return Inertia::render('Users/Index', [
        'users' => User::all()
    ]);
});
</code-snippet>


=== inertia-laravel/v2 rules ===

## Inertia v2

- Make use of all Inertia features from v1 & v2. Check the documentation before making any changes to ensure we are taking the correct approach.

### Inertia v2 New Features
- Polling
- Prefetching
- Deferred props
- Infinite scrolling using merging props and `WhenVisible`
- Lazy loading data on scroll

### Deferred Props & Empty States
- When using deferred props on the frontend, you should add a nice empty state with pulsing / animated skeleton.

### Inertia Form General Guidance
- The recommended way to build forms when using Inertia is with the `<Form>` component - a useful example is below. Use `search-docs` with a query of `form component` for guidance.
- Forms can also be built using the `useForm` helper for more programmatic control, or to follow existing conventions. Use `search-docs` with a query of `useForm helper` for guidance.
- `resetOnError`, `resetOnSuccess`, and `setDefaultsOnSuccess` are available on the `<Form>` component. Use `search-docs` with a query of 'form component resetting' for guidance.


=== laravel/core rules ===

## Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using the `list-artisan-commands` tool.
- If you're creating a generic PHP class, use `artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Database
- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation
- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `list-artisan-commands` to check the available options to `php artisan make:model`.

### APIs & Eloquent Resources
- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

### Controllers & Validation
- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

### Queues
- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

### Authentication & Authorization
- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

### URL Generation
- When generating links to other pages, prefer named routes and the `route()` function.

### Configuration
- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

### Testing
- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] <name>` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

### Vite Error
- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.


=== laravel/v12 rules ===

## Laravel 12

- Use the `search-docs` tool to get version specific documentation.
- Since Laravel 11, Laravel has a new streamlined file structure which this project uses.

### Laravel 12 Structure
- No middleware files in `app/Http/Middleware/`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- **No app\Console\Kernel.php** - use `bootstrap/app.php` or `routes/console.php` for console configuration.
- **Commands auto-register** - files in `app/Console/Commands/` are automatically available and do not require manual registration.

### Database
- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 11 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models
- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.


=== pint/core rules ===

## Laravel Pint Code Formatter

- You must run `vendor/bin/pint --dirty` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test`, simply run `vendor/bin/pint` to fix any formatting issues.


=== pest/core rules ===

## Pest

### Testing
- If you need to verify a feature is working, write or update a Unit / Feature test.

### Pest Tests
- All tests must be written using Pest. Use `php artisan make:test --pest <name>`.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files - these are core to the application.
- Tests should test all of the happy paths, failure paths, and weird paths.
- Tests live in the `tests/Feature` and `tests/Unit` directories.
- Pest tests look and behave like this:
<code-snippet name="Basic Pest Test Example" lang="php">
it('is true', function () {
    expect(true)->toBeTrue();
});
</code-snippet>

### Running Tests
- Run the minimal number of tests using an appropriate filter before finalizing code edits.
- To run all tests: `php artisan test`.
- To run all tests in a file: `php artisan test tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `php artisan test --filter=testName` (recommended after making a change to a related file).
- When the tests relating to your changes are passing, ask the user if they would like to run the entire test suite to ensure everything is still passing.

### Pest Assertions
- When asserting status codes on a response, use the specific method like `assertForbidden` and `assertNotFound` instead of using `assertStatus(403)` or similar, e.g.:
<code-snippet name="Pest Example Asserting postJson Response" lang="php">
it('returns all', function () {
    $response = $this->postJson('/api/docs', []);

    $response->assertSuccessful();
});
</code-snippet>

### Mocking
- Mocking can be very helpful when appropriate.
- When mocking, you can use the `Pest\Laravel\mock` Pest function, but always import it via `use function Pest\Laravel\mock;` before using it. Alternatively, you can use `$this->mock()` if existing tests do.
- You can also create partial mocks using the same import or self method.

### Datasets
- Use datasets in Pest to simplify tests which have a lot of duplicated data. This is often the case when testing validation rules, so consider going with this solution when writing tests for validation rules.

<code-snippet name="Pest Dataset Example" lang="php">
it('has emails', function (string $email) {
    expect($email)->not->toBeEmpty();
})->with([
    'james' => 'james@laravel.com',
    'taylor' => 'taylor@laravel.com',
]);
</code-snippet>


=== pest/v4 rules ===

## Pest 4

- Pest v4 is a huge upgrade to Pest and offers: browser testing, smoke testing, visual regression testing, test sharding, and faster type coverage.
- Browser testing is incredibly powerful and useful for this project.
- Browser tests should live in `tests/Browser/`.
- Use the `search-docs` tool for detailed guidance on utilizing these features.

### Browser Testing
- You can use Laravel features like `Event::fake()`, `assertAuthenticated()`, and model factories within Pest v4 browser tests, as well as `RefreshDatabase` (when needed) to ensure a clean state for each test.
- Interact with the page (click, type, scroll, select, submit, drag-and-drop, touch gestures, etc.) when appropriate to complete the test.
- If requested, test on multiple browsers (Chrome, Firefox, Safari).
- If requested, test on different devices and viewports (like iPhone 14 Pro, tablets, or custom breakpoints).
- Switch color schemes (light/dark mode) when appropriate.
- Take screenshots or pause tests for debugging when appropriate.

### Example Tests

<code-snippet name="Pest Browser Test Example" lang="php">
it('may reset the password', function () {
    Notification::fake();

    $this->actingAs(User::factory()->create());

    $page = visit('/sign-in'); // Visit on a real browser...

    $page->assertSee('Sign In')
        ->assertNoJavascriptErrors() // or ->assertNoConsoleLogs()
        ->click('Forgot Password?')
        ->fill('email', 'nuno@laravel.com')
        ->click('Send Reset Link')
        ->assertSee('We have emailed your password reset link!')

    Notification::assertSent(ResetPassword::class);
});
</code-snippet>

<code-snippet name="Pest Smoke Testing Example" lang="php">
$pages = visit(['/', '/about', '/contact']);

$pages->assertNoJavascriptErrors()->assertNoConsoleLogs();
</code-snippet>


=== inertia-vue/core rules ===

## Inertia + Vue

- Vue components must have a single root element.
- Use `router.visit()` or `<Link>` for navigation instead of traditional links.

<code-snippet name="Inertia Client Navigation" lang="vue">

    import { Link } from '@inertiajs/vue3'
    <Link href="/">Home</Link>

</code-snippet>


=== inertia-vue/v2/forms rules ===

## Inertia + Vue Forms

<code-snippet name="`<Form>` Component Example" lang="vue">

<Form
    action="/users"
    method="post"
    #default="{
        errors,
        hasErrors,
        processing,
        progress,
        wasSuccessful,
        recentlySuccessful,
        setError,
        clearErrors,
        resetAndClearErrors,
        defaults,
        isDirty,
        reset,
        submit,
  }"
>
    <input type="text" name="name" />

    <div v-if="errors.name">
        {{ errors.name }}
    </div>

    <button type="submit" :disabled="processing">
        {{ processing ? 'Creating...' : 'Create User' }}
    </button>

    <div v-if="wasSuccessful">User created successfully!</div>
</Form>

</code-snippet>


=== tailwindcss/core rules ===

## Tailwind Core

- Use Tailwind CSS classes to style HTML, check and use existing tailwind conventions within the project before writing your own.
- Offer to extract repeated patterns into components that match the project's conventions (i.e. Blade, JSX, Vue, etc..)
- Think through class placement, order, priority, and defaults - remove redundant classes, add classes to parent or child carefully to limit repetition, group elements logically
- You can use the `search-docs` tool to get exact examples from the official documentation when needed.

### Spacing
- When listing items, use gap utilities for spacing, don't use margins.

    <code-snippet name="Valid Flex Gap Spacing Example" lang="html">
        <div class="flex gap-8">
            <div>Superior</div>
            <div>Michigan</div>
            <div>Erie</div>
        </div>
    </code-snippet>


### Dark Mode
- If existing pages and components support dark mode, new pages and components must support dark mode in a similar way, typically using `dark:`.


=== tailwindcss/v4 rules ===

## Tailwind 4

- Always use Tailwind CSS v4 - do not use the deprecated utilities.
- `corePlugins` is not supported in Tailwind v4.
- In Tailwind v4, you import Tailwind using a regular CSS `@import` statement, not using the `@tailwind` directives used in v3:

<code-snippet name="Tailwind v4 Import Tailwind Diff" lang="diff">
   - @tailwind base;
   - @tailwind components;
   - @tailwind utilities;
   + @import "tailwindcss";
</code-snippet>


### Replaced Utilities
- Tailwind v4 removed deprecated utilities. Do not use the deprecated option - use the replacement.
- Opacity values are still numeric.

| Deprecated |	Replacement |
|------------+--------------|
| bg-opacity-* | bg-black/* |
| text-opacity-* | text-black/* |
| border-opacity-* | border-black/* |
| divide-opacity-* | divide-black/* |
| ring-opacity-* | ring-black/* |
| placeholder-opacity-* | placeholder-black/* |
| flex-shrink-* | shrink-* |
| flex-grow-* | grow-* |
| overflow-ellipsis | text-ellipsis |
| decoration-slice | box-decoration-slice |
| decoration-clone | box-decoration-clone |


=== tests rules ===

## Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test` with a specific filename or filter.


=== .ai/memory-usage rules ===

# Memory and Context Management

Vizra ADK agents can maintain memory across conversations, enabling contextual and personalized interactions.

## Memory Types

1. **Session Memory**: Conversation history within a session
2. **User Memory**: Persistent memory tied to a user
3. **Vector Memory**: Semantic search-enabled long-term memory

## Basic Memory Usage

### User-Scoped Memory
```php
// First conversation
$response1 = MyAgent::run('My name is John and I love pizza')
    ->forUser($user)
    ->go();

// Later conversation - agent remembers
$response2 = MyAgent::run('What is my name and what do I like?')
    ->forUser($user)
    ->go(); // Will remember "John" and "pizza"
```

### Session-Based Memory
```php
// Maintain context within a specific session
$sessionId = 'support-ticket-123';

// Multiple interactions in same session
$response1 = MyAgent::run('I have an issue with my order #456')
    ->forUser($user)
    ->withSession($sessionId)
    ->go();

$response2 = MyAgent::run('What order number did I mention?')
    ->forUser($user)
    ->withSession($sessionId)
    ->go(); // Remembers order #456
```

## Memory Tools

### Using MemoryTool
```php
use Vizra\VizraADK\Tools\MemoryTool;

class AssistantAgent extends BaseLlmAgent
{
    protected array $tools = [
        MemoryTool::class, // Enables memory operations
    ];
    
    protected string $instructions = <<<'INSTRUCTIONS'
        You are a helpful assistant with memory capabilities.
        
        Use the memory tool to:
        - Store important information about the user
        - Recall previous conversations
        - Update existing memories
        INSTRUCTIONS;
}
```

### Using VectorMemoryTool
```php
use Vizra\VizraADK\Tools\VectorMemoryTool;

class KnowledgeAgent extends BaseLlmAgent
{
    protected array $tools = [
        VectorMemoryTool::class, // Semantic search in memories
    ];
    
    protected string $instructions = <<<'INSTRUCTIONS'
        You are a knowledge management assistant.
        
        Use vector memory to:
        - Store information with semantic meaning
        - Search for relevant past information
        - Find similar concepts discussed before
        INSTRUCTIONS;
}
```

## Programmatic Memory Access

### Storing Memories
```php
use Vizra\VizraADK\Memory\AgentMemory;

// Memory is typically managed internally by agents
// In custom tools or agents, you can access memory methods:

// Store facts and learnings
$memory->addFact('User prefers email communication', 0.9);
$memory->addLearning('Customer is price-sensitive');
$memory->addPreference('communication', 'email');
```

### Retrieving Memories
```php
// Get specific memory types
$facts = $memory->getFacts();
$learnings = $memory->getLearnings();
$summary = $memory->getSummary();
$preferences = $memory->getPreferences();

// Search memories semantically
$relevant = $memory->search('communication preferences');
```

### Vector Memory Operations
```php
use Vizra\VizraADK\Services\VectorMemoryManager;

$vectorManager = app(VectorMemoryManager::class);

// Store with embeddings
$vectorManager->store(
    'The user is interested in machine learning and AI',
    ['user_id' => $user->id]
);

// Semantic search
$relevant = $vectorManager->search(
    'artificial intelligence topics',
    limit: 5
);
```

## Memory Patterns

### Profile Building
```php
class ProfileBuilderAgent extends BaseLlmAgent
{
    protected string $instructions = <<<'INSTRUCTIONS'
        Build a comprehensive user profile over time.
        
        Track and remember:
        - Personal preferences
        - Communication style
        - Past decisions
        - Goals and objectives
        
        Update the profile as you learn more.
        INSTRUCTIONS;
    
    protected array $tools = [
        MemoryTool::class,
    ];
}

// Usage
$agent = ProfileBuilderAgent::run('I prefer morning meetings and detailed reports')
    ->forUser($user)
    ->go();
```

### Context-Aware Responses
```php
class ContextualAgent extends BaseLlmAgent
{
    protected string $instructions = <<<'INSTRUCTIONS'
        Always consider conversation history when responding.
        Reference previous discussions when relevant.
        Maintain continuity across interactions.
        INSTRUCTIONS;
        
    public function execute($input)
    {
        // Automatically loads conversation history
        return $this->run($input)
            ->forUser($this->user)
            ->withSession($this->sessionId)
            ->go();
    }
}
```

## Memory Configuration

### In Agent Class
```php
class ConfiguredMemoryAgent extends BaseLlmAgent
{
    // Configure conversation history inclusion
    protected bool $includeConversationHistory = true;  // Include conversation history
    protected string $contextStrategy = 'recent'; // How to include context: 'recent', 'full', 'none'
    protected int $historyLimit = 10; // Maximum messages to include when using 'recent'
}
```

## Advanced Memory Techniques

### Memory Summarization
```php
class SummarizingAgent extends BaseLlmAgent
{
    protected function preprocessMemory($history)
    {
        if (count($history) > 20) {
            // Summarize older conversations
            $oldMessages = array_slice($history, 0, -10);
            $summary = $this->summarize($oldMessages);
            
            return array_merge(
                [$summary],
                array_slice($history, -10)
            );
        }
        return $history;
    }
}
```

### Selective Memory
```php
class SelectiveMemoryAgent extends BaseLlmAgent
{
    protected string $instructions = <<<'INSTRUCTIONS'
        Only remember important information:
        - User preferences
        - Key decisions
        - Action items
        - Personal details
        
        Ignore small talk and redundant information.
        INSTRUCTIONS;
}
```

### Memory Segregation
```php
// Different memory scopes for different purposes
$agent = MyAgent::run($input)
    ->forUser($user)
    ->withSession('personal')  // Personal context
    ->go();

$agent = MyAgent::run($workInput)
    ->forUser($user)
    ->withSession('work')      // Work context
    ->go();
```

## Memory Best Practices

1. **Clear Session Boundaries**: Use distinct session IDs for different conversations
2. **Memory Hygiene**: Periodically clean old or irrelevant memories
3. **Privacy**: Never store sensitive data (passwords, SSN, etc.)
4. **Summarization**: Summarize long conversations to stay within token limits
5. **Indexing**: Use vector memory for searchable long-term storage

## Common Issues and Solutions

### Memory Not Persisting
```php
// Ensure user context is provided
$response = Agent::run($input)
    ->forUser($user) // Required for persistence
    ->go();
```

### Token Limit Exceeded
```php
// Configure agent to use less history
class EfficientAgent extends BaseLlmAgent
{
    protected bool $includeHistory = true;
    protected string $contextStrategy = 'recent'; // Only recent messages
    
    // Or handle in preprocessing
    protected function preprocessMemory($history)
    {
        // Only keep last 10 messages
        return array_slice($history, -10);
    }
}
```

### Memory Retrieval Performance
```php
// Use caching for frequently accessed memories
Cache::remember("user_facts_{$user->id}", 3600, function() use ($memory) {
    return $memory->getFacts();
});

// Cache conversation context
Cache::remember("user_context_{$user->id}", 3600, function() use ($context) {
    return $context->getConversationHistory();
});
```

## Next Steps

- Implement sub-agent memory sharing: See `sub-agents.blade.php`
- Test memory-dependent agents: See `evaluation.blade.php`


=== .ai/agent-creation rules ===

# Creating Vizra ADK Agents

Vizra ADK agents are AI-powered Laravel classes that can reason, use tools, and maintain memory. All agents must extend `BaseLlmAgent`.

## Agent Class Structure

Every agent MUST follow this structure:

```php
<?php

namespace App\Agents;

use Vizra\VizraADK\Agents\BaseLlmAgent;

class AgentNameAgent extends BaseLlmAgent
{
/**
* Unique identifier for the agent (snake_case)
*/
protected string $name = 'snake_case(&quot;AgentName&quot;)';

/**
* Brief description of what the agent does
*/
protected string $description = 'One line description';

/**
* System prompt/instructions for the LLM
*/
protected string $instructions = <<<'INSTRUCTIONS'
    You are a role description.

    Your capabilities include:
    - capability 1
    - capability 2

    Guidelines:
    - guideline 1
    - guideline 2
    INSTRUCTIONS;

    /**
    * LLM model to use
    * Options: 'gpt-4o' , 'gpt-4o-mini' , 'claude-3-opus' , 'claude-3-sonnet' , 'gemini-pro' , etc.
    */
    protected string $model='model_name' ;

    /**
    * Optional: Temperature setting (0.0 to 1.0)
    * Lower=more deterministic, Higher=more creative
    */
    protected ?float $temperature=null;

    /**
    * Optional: Maximum tokens for response
    */
    protected ?int $maxTokens=null;

    /**
    * Optional: Maximum steps for tool execution (default: 5)
    */
    protected int $maxSteps=5;

    /**
    * Optional: Provider override (e.g., 'openai' , 'anthropic' , 'google' )
    */
    protected ?string $provider=null;

    /**
    * Optional: Top-p sampling parameter
    */
    protected ?float $topP=null;

    /**
    * Tools this agent can use
    * Each tool must implement ToolInterface
    */
    protected array $tools=[
    // ToolClass::class,
    ];
    }
    ```

    ## Key Rules

    1. **Naming Convention**:
    - Class name MUST end with `Agent`
    - The `$name` property must be unique and in snake_case
    - Place agents in `App\Agents` namespace

    2. **Auto-Discovery**:
    - Agents are automatically discovered - NO registration needed
    - Simply create the class and it's ready to use

    3. **Required Properties**:
    - `$name`: Unique identifier
    - `$description`: Brief description
    - `$instructions`: System prompt for the LLM
    - `$model`: Which LLM model to use

    4. **Instructions Best Practices**:
    - Be specific about the agent's role
    - List capabilities clearly
    - Include behavioral guidelines
    - Use heredoc (<<<'INSTRUCTIONS') for multi-line instructions

    ## Common Agent Patterns

    ### Customer Service Agent
    ```php
    class CustomerServiceAgent extends BaseLlmAgent
    {
    protected string $name='customer_service' ;
    protected string $description='Handles customer inquiries and support tickets' ;
    protected string $instructions=<<<'INSTRUCTIONS'
    You are a professional customer service representative.

    Your approach:
    - Always be polite and empathetic
    - Gather all necessary information before providing solutions
    - Escalate complex issues appropriately

    Remember to:
    - Thank the customer for their patience
    - Confirm understanding of their issue
    - Provide clear next steps
    INSTRUCTIONS;
    protected string $model='gpt-4o' ;
    protected array $tools=[
    OrderLookupTool::class,
    TicketCreationTool::class,
    RefundProcessorTool::class,
    ];
    }
    ```

    ### Data Analysis Agent
    ```php
    class DataAnalysisAgent extends BaseLlmAgent
    {
    protected string $name='data_analyst' ;
    protected string $description='Analyzes data and generates insights' ;
    protected string $instructions=<<<'INSTRUCTIONS'
    You are an expert data analyst.

    Your responsibilities:
    - Analyze provided data thoroughly
    - Identify patterns and anomalies
    - Generate actionable insights
    - Create clear visualizations when appropriate

    Always:
    - Verify data quality first
    - Explain your methodology
    - Provide confidence levels for findings
    INSTRUCTIONS;
    protected string $model='gpt-4o' ;
    protected ?float $temperature=0.3; // Lower temperature for analytical tasks
    protected array $tools=[
    DatabaseQueryTool::class,
    ChartGeneratorTool::class,
    StatisticalAnalysisTool::class,
    ];
    }
    ```

    ### Creative Content Agent
    ```php
    class ContentCreatorAgent extends BaseLlmAgent
    {
    protected string $name='content_creator' ;
    protected string $description='Creates engaging content for various platforms' ;
    protected string $instructions=<<<'INSTRUCTIONS'
    You are a creative content specialist.

    Your expertise includes:
    - Writing engaging blog posts
    - Creating social media content
    - Developing marketing copy
    - Crafting email campaigns

    Style guidelines:
    - Match the brand voice
    - Use active voice
    - Keep sentences concise
    - Include calls to action
    INSTRUCTIONS;
    protected string $model='claude-3-opus' ;
    protected ?float $temperature=0.8; // Higher temperature for creativity
    protected array $tools=[
    SEOAnalyzerTool::class,
    ImageGeneratorTool::class,
    ];
    }
    ```

    ## Using Your Agent

    ### Basic Execution
    ```php
    use App\Agents\MyAgent;

    // Simple execution
    $response=MyAgent::run('Hello, can you help me?')->go();

    // With user context (maintains memory)
    $response = MyAgent::run('Remember my name is John')
    ->forUser($user)
    ->go();

    // With session persistence
    $response = MyAgent::run('What did we discuss earlier?')
    ->forUser($user)
    ->withSession($sessionId)
    ->go();
    ```

    ### Advanced Execution Options
    ```php
    // Pass parameters to the agent
    $response = MyAgent::run($message)
    ->withParameters([
    'context' => 'customer_support',
    'priority' => 'high',
    'metadata' => ['ticket_id' => 12345]
    ])
    ->go();

    // Enable streaming (returns a stream object)
    $stream = MyAgent::run($message)
    ->forUser($user)
    ->streaming(true)
    ->go();

    // The stream can be iterated or converted to string
    foreach ($stream as $chunk) {
    echo $chunk; // Handle each token as it arrives
    }

    // Get the response (returns a string by default)
    $result = MyAgent::run($message)->go();
    echo $result; // The response is a string
    ```

    ## Sub-Agent Delegation

    Agents can delegate to other agents using the `DelegateToSubAgentTool`:

    ```php
    use Vizra\VizraADK\Tools\DelegateToSubAgentTool;

    class ManagerAgent extends BaseLlmAgent
    {
    protected string $name = 'manager';
    protected string $description = 'Coordinates tasks between specialized agents';
    protected string $instructions = <<<'INSTRUCTIONS'
        You are a task coordinator. Delegate specialized tasks to appropriate agents:
        - Use 'research' agent for information gathering
        - Use 'writer' agent for content creation
        - Use 'analyzer' agent for data analysis
        INSTRUCTIONS;
        protected string $model='gpt-4o' ;
        protected array $tools=[
        DelegateToSubAgentTool::class,
        ];
        }
        ```

        ## Memory and Context

        ### Persistent Memory
        ```php
        // Memory persists across conversations for a user
        $response1=MyAgent::run('My favorite color is blue')
        ->forUser($user)
        ->go();

        // Later conversation - agent remembers
        $response2 = MyAgent::run('What is my favorite color?')
        ->forUser($user)
        ->go(); // Will remember "blue"
        ```

        ### Session-Based Memory
        ```php
        // Create a session for a specific conversation thread
        $sessionId = 'support-ticket-123';

        $response = MyAgent::run($message)
        ->forUser($user)
        ->withSession($sessionId)
        ->go();
        ```

        ### Using Memory Tools
        ```php
        use Vizra\VizraADK\Tools\MemoryTool;
        use Vizra\VizraADK\Tools\VectorMemoryTool;

        class KnowledgeAgent extends BaseLlmAgent
        {
        protected array $tools = [
        MemoryTool::class, // Store/retrieve memories
        VectorMemoryTool::class, // Semantic search in memories
        ];
        }
        ```

        ## Model Selection Guide

        Choose the appropriate model based on your needs:

        - **GPT-4o**: Best for complex reasoning, tool use, and general tasks
        - **GPT-4o-mini**: Cost-effective for simpler tasks
        - **Claude-3-Opus**: Excellent for creative writing and analysis
        - **Claude-3-Sonnet**: Balanced performance and cost
        - **Gemini-Pro**: Good for multi-modal tasks
        - **Gemini-Flash**: Fast responses for simple queries

        ## Error Handling

        Agents handle errors gracefully by default, but you can customize behavior:

        ```php
        try {
        $response = MyAgent::run($message)->go();
        } catch (\Vizra\VizraADK\Exceptions\AgentException $e) {
        // Handle agent-specific errors
        Log::error('Agent error: ' . $e->getMessage());
        }
        ```

        ## Testing Your Agent

        ```php
        // In your test file
        use Tests\TestCase;
        use App\Agents\MyAgent;

        class MyAgentTest extends TestCase
        {
        public function test_agent_responds_correctly()
        {
        $response = MyAgent::run('test message')->go();

        $this->assertNotEmpty($response);
        $this->assertIsString($response);
        }
        }
        ```

        ## Common Mistakes to Avoid

        1. **Don't forget to extend BaseLlmAgent** - Your agent won't work without it
        2. **Don't use duplicate agent names** - Each $name must be unique
        3. **Don't hardcode sensitive data** - Use environment variables or configuration
        4. **Don't make instructions too vague** - Be specific about the agent's role
        5. **Don't forget to include required tools** - Agents need tools to interact with systems

        ## Debugging Tips

        1. Use the Vizra dashboard to test agents: `php artisan vizra:dashboard`
        2. Check agent traces: `php artisan vizra:trace {traceId}`
        3. List all discovered agents: `php artisan vizra:agents`
        4. Test in CLI: `php artisan vizra:chat {agent_name}`

        ## Next Steps

        - Create custom tools for your agent: See `tool-creation.blade.php`
        - Build agent workflows: See `workflow-patterns.blade.php`
        - Add evaluation tests: See `evaluation.blade.php`


=== .ai/evaluation rules ===

# Agent Evaluation Framework

Vizra ADK provides a comprehensive evaluation framework to test and validate your agents at scale using LLM-as-a-Judge patterns.

## Creating Evaluations

### Basic Evaluation Structure

```php
<?php

namespace App\Evaluations;

use Vizra\VizraADK\Evaluations\BaseEvaluation;

class EvaluationNameEvaluation extends BaseEvaluation
{
/**
* Name of the evaluation
*/
protected string $name = 'snake_case(&quot;EvaluationName&quot;)_evaluation';

/**
* Description of what this evaluation tests
*/
protected string $description = 'What this evaluation validates';

/**
* The agent class to evaluate
*/
protected string $agentClass = AgentNameAgent::class;

/**
* Define test cases
*/
public function testCases(): array
{
return [
[
'input' => 'First test input',
'expected_output' => 'Expected response pattern',
'metadata' => ['category' => 'basic'],
],
[
'input' => 'Second test input',
'expected_output' => 'Another expected response',
'metadata' => ['category' => 'advanced'],
],
];
}

/**
* Define assertions to run on each test case
*/
public function assertions(): array
{
return [
CorrectnessAssertion::class,
ToneAssertion::class,
SafetyAssertion::class,
];
}
}
```

## Creating Assertions

Assertions validate specific aspects of agent responses:

```php
<?php

namespace App\Evaluations\Assertions;

use Vizra\VizraADK\Evaluations\Assertions\BaseAssertion;

class AssertionNameAssertion extends BaseAssertion
{
/**
* Name of the assertion
*/
protected string $name = 'snake_case(&quot;AssertionName&quot;)';

/**
* What this assertion checks
*/
protected string $description = 'What this assertion validates';

/**
* The prompt for the LLM judge
*/
protected function getPrompt(string $input, string $output, ?string $expected = null): string
{
return <<<PROMPT
    Evaluate if the following output is correct:

    Input: {$input}
    Output: {$output}
    Expected Pattern: {$expected}

    Score from 0-100 based on correctness.
    Provide reasoning for your score.
    PROMPT;
    }

    /**
    * Process the LLM judge's response
    */
    protected function processResponse(string $response): array
    {
    // Parse the response and extract score and reasoning
    preg_match('/Score:\s*(\d+)/', $response, $scoreMatch);
    preg_match('/Reasoning:\s*(.+)/s', $response, $reasoningMatch);

    return [ 'score'=> (int) ($scoreMatch[1] ?? 0),
    'reasoning' => $reasoningMatch[1] ?? 'No reasoning provided',
    'passed' => ((int) ($scoreMatch[1] ?? 0)) >= 70,
    ];
    }
    }
    ```

    ## Common Assertion Patterns

    ### Correctness Assertion
    ```php
    class CorrectnessAssertion extends BaseAssertion
    {
    protected string $name = 'correctness';
    protected string $description = 'Validates factual accuracy of responses';

    protected function getPrompt(string $input, string $output, ?string $expected = null): string
    {
    return <<<PROMPT
        Evaluate the correctness of this response:

        Question: {$input}
        Answer: {$output}

        Score 0-100 based on:
        - Factual accuracy
        - Completeness
        - Relevance to the question
        PROMPT;
        }
        }
        ```

        ### Safety Assertion
        ```php
        class SafetyAssertion extends BaseAssertion
        {
        protected string $name='safety' ;
        protected string $description='Ensures responses are safe and appropriate' ;

        protected function getPrompt(string $input, string $output, ?string $expected=null): string
        {
        return <<<PROMPT
        Check if this response is safe and appropriate:

        Response: {$output}

        Score 0-100. Deduct points for:
        - Harmful content
        - Inappropriate language
        - Sensitive information exposure
        - Biased statements
        PROMPT;
        }
        }
        ```

        ### Tone Assertion
        ```php
        class ToneAssertion extends BaseAssertion
        {
        protected string $name='tone' ;
        protected string $description='Validates appropriate tone and style' ;

        protected function getPrompt(string $input, string $output, ?string $expected=null): string
        {
        return <<<PROMPT
        Evaluate the tone of this response:

        Context: Customer support interaction
        Response: {$output}

        Score 0-100 based on:
        - Professionalism
        - Empathy
        - Clarity
        - Appropriateness
        PROMPT;
        }
        }
        ```

        ## Running Evaluations

        ### Command Line
        ```bash
        # Run all evaluations
        php artisan vizra:run:eval

        # Run specific evaluation
        php artisan vizra:run:eval CustomerSupportEvaluation

        # Run with specific assertion
        php artisan vizra:run:eval --assertion=correctness

        # Output results to file
        php artisan vizra:run:eval --output=results.json
        ```

        ### Programmatic Execution
        ```php
        use App\Evaluations\CustomerSupportEvaluation;
        use Vizra\VizraADK\Evaluations\EvaluationRunner;

        $evaluation=new CustomerSupportEvaluation();
        $runner=new EvaluationRunner($evaluation);

        $results=$runner->run();

        foreach ($results as $result) {
        echo "Test: {$result['input']}\n";
        echo "Score: {$result['score']}\n";
        echo "Passed: " . ($result['passed'] ? 'Yes' : 'No') . "\n";
        }
        ```

        ## Advanced Evaluation Patterns

        ### Comparative Evaluation
        ```php
        class ModelComparisonEvaluation extends BaseEvaluation
        {
        public function testCases(): array
        {
        return [
        [
        'input' => 'Explain quantum computing',
        'models' => ['gpt-4o', 'claude-3-opus', 'gemini-pro'],
        ],
        ];
        }

        public function evaluate(): array
        {
        $results = [];

        foreach ($this->testCases() as $case) {
        foreach ($case['models'] as $model) {
        $agent = new TestAgent();
        $agent->setModel($model);

        $output = $agent->run($case['input'])->go();
        $results[$model] = $this->assertQuality($output);
        }
        }

        return $results;
        }
        }
        ```

        ### Regression Testing
        ```php
        class RegressionEvaluation extends BaseEvaluation
        {
        protected string $name = 'regression_test';

        public function testCases(): array
        {
        // Load previous successful outputs
        $baseline = json_decode(
        file_get_contents(storage_path('evaluations/baseline.json')),
        true
        );

        return array_map(function($case) {
        return [
        'input' => $case['input'],
        'expected_output' => $case['output'],
        'tolerance' => 0.9, // 90% similarity required
        ];
        }, $baseline);
        }
        }
        ```

        ### Performance Evaluation
        ```php
        class PerformanceEvaluation extends BaseEvaluation
        {
        protected string $name = 'performance_test';

        public function assertions(): array
        {
        return [
        ResponseTimeAssertion::class,
        TokenUsageAssertion::class,
        CostAssertion::class,
        ];
        }

        public function testCases(): array
        {
        return [
        [
        'input' => 'Simple query',
        'max_response_time' => 2000, // 2 seconds
        'max_tokens' => 500,
        'max_cost' => 0.02,
        ],
        ];
        }
        }
        ```

        ## Test Data Management

        ### Using Fixtures
        ```php
        class FixtureBasedEvaluation extends BaseEvaluation
        {
        public function testCases(): array
        {
        $fixtures = json_decode(
        file_get_contents(__DIR__ . '/fixtures/test_cases.json'),
        true
        );

        return $fixtures;
        }
        }
        ```

        ### Dynamic Test Generation
        ```php
        class DynamicEvaluation extends BaseEvaluation
        {
        public function testCases(): array
        {
        $cases = [];

        // Generate test cases based on patterns
        $topics = ['weather', 'news', 'sports'];
        $complexities = ['simple', 'detailed', 'technical'];

        foreach ($topics as $topic) {
        foreach ($complexities as $complexity) {
        $cases[] = [
        'input' => "Give me a {$complexity} update about {$topic}",
        'metadata' => [
        'topic' => $topic,
        'complexity' => $complexity,
        ],
        ];
        }
        }

        return $cases;
        }
        }
        ```

        ## Evaluation Metrics

        ### Score Aggregation
        ```php
        class MetricsEvaluation extends BaseEvaluation
        {
        public function calculateMetrics(array $results): array
        {
        $scores = array_column($results, 'score');

        return [
        'mean' => array_sum($scores) / count($scores),
        'median' => $this->median($scores),
        'min' => min($scores),
        'max' => max($scores),
        'pass_rate' => $this->passRate($results),
        'std_dev' => $this->standardDeviation($scores),
        ];
        }

        private function passRate(array $results): float
        {
        $passed = array_filter($results, fn($r) => $r['passed']);
        return count($passed) / count($results) * 100;
        }
        }
        ```

        ## Continuous Integration

        ### GitHub Actions Example
        ```yaml
        name: Agent Evaluation

        on: [push, pull_request]

        jobs:
        evaluate:
        runs-on: ubuntu-latest
        steps:
        - uses: actions/checkout@v2

        - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
        php-version: '8.2'

        - name: Install Dependencies
        run: composer install

        - name: Run Evaluations
        run: php artisan vizra:run:eval --output=results.json

        - name: Check Results
        run: |
        PASS_RATE=$(jq '.metrics.pass_rate' results.json)
        if (( $(echo "$PASS_RATE < 90" | bc -l) )); then
            echo "Evaluation pass rate below 90%"
            exit 1
            fi
            ```

            ## Best Practices

            1. **Comprehensive Coverage**: Test edge cases, error conditions, and happy paths
            2. **Consistent Baselines**: Maintain baseline outputs for regression testing
            3. **Multiple Assertions**: Use different assertions to validate various aspects
            4. **Metadata Tracking**: Add metadata to organize and filter test results
            5. **Regular Execution**: Run evaluations in CI/CD pipelines
            6. **Performance Monitoring**: Track response times and costs
            7. **Version Control**: Version your test cases and expected outputs

            ## Common Mistakes to Avoid

            1. **Over-specific Expectations**: Don't expect exact string matches for creative outputs
            2. **Insufficient Test Cases**: Include diverse inputs to catch edge cases
            3. **Ignoring Costs**: Monitor token usage and API costs during evaluation
            4. **No Baseline**: Establish baseline metrics before making changes
            5. **Single Assertion**: Use multiple assertions for comprehensive validation

            ## Next Steps

            - Create custom assertions: See command `php artisan vizra:make:assertion`
            - Build evaluation suites: Group related evaluations together
            - Integrate with CI/CD: Automate evaluation runs
            - Monitor trends: Track evaluation metrics over time


=== .ai/tool-creation rules ===

# Creating Vizra ADK Tools

Tools extend agent capabilities by allowing them to interact with databases, APIs, external services, and perform specific actions. All tools must implement the `ToolInterface`.

## Tool Class Structure

Every tool MUST follow this structure:

```php
<?php

namespace App\Tools;

use Vizra\VizraADK\Contracts\ToolInterface;
use Vizra\VizraADK\Memory\AgentMemory;
use Vizra\VizraADK\System\AgentContext;

class ToolNameTool implements ToolInterface
{
/**
* Define the tool's schema for the LLM
*/
public function definition(): array
{
return [
'name' => 'snake_case(&quot;ToolName&quot;)',
'description' => 'Clear description of what this tool does',
'parameters' => [
'type' => 'object',
'properties' => [
'parameter_name' => [
'type' => 'string|number|boolean|array|object',
'description' => 'What this parameter is for',
// Optional fields:
'enum' => ['option1', 'option2'], // For restricted values
'default' => 'default_value', // Default if not provided
'items' => [ // For array types
'type' => 'string'
],
],
],
'required' => ['required_param'], // List required parameters
],
];
}

/**
* Execute the tool with given arguments
*
* @param array $arguments The parameters passed by the LLM
* @param AgentContext $context Current execution context
* @param AgentMemory $memory Agent's memory instance
* @return string JSON-encoded result
*/
public function execute(array $arguments, AgentContext $context, AgentMemory $memory): string
{
// Extract arguments
$param = $arguments['parameter_name'] ?? null;

// Perform the tool's action
try {
// Your logic here
$result = $this->performAction($param);

return json_encode([
'status' => 'success',
'data' => $result,
]);
} catch (\Exception $e) {
return json_encode([
'status' => 'error',
'message' => $e->getMessage(),
]);
}
}

private function performAction($param)
{
// Implementation details
}
}
```

## Key Rules

1. **Interface Implementation**:
- MUST implement `ToolInterface`
- MUST have both `definition()` and `execute()` methods

2. **Naming Convention**:
- Class name should end with `Tool`
- Tool name in definition should be snake_case
- Place tools in `App\Tools` namespace

3. **Return Format**:
- ALWAYS return JSON-encoded strings
- Include status indicators (success/error)
- Provide clear error messages

4. **Parameter Schema**:
- Use JSON Schema format
- Be explicit about types
- Include helpful descriptions
- Mark required parameters

## Common Tool Patterns

### Database Query Tool
```php
class DatabaseQueryTool implements ToolInterface
{
public function definition(): array
{
return [
'name' => 'database_query',
'description' => 'Execute database queries to retrieve information',
'parameters' => [
'type' => 'object',
'properties' => [
'table' => [
'type' => 'string',
'description' => 'The database table to query',
'enum' => ['users', 'orders', 'products'], // Restrict to safe tables
],
'conditions' => [
'type' => 'array',
'description' => 'Query conditions',
'items' => [
'type' => 'object',
'properties' => [
'field' => ['type' => 'string'],
'operator' => ['type' => 'string', 'enum' => ['=', '>', '<', 'like' ]], 'value'=> ['type' => 'string'],
    ],
    ],
    ],
    'limit' => [
    'type' => 'number',
    'description' => 'Maximum number of results',
    'default' => 10,
    ],
    ],
    'required' => ['table'],
    ],
    ];
    }

    public function execute(array $arguments, AgentContext $context, AgentMemory $memory): string
    {
    $table = $arguments['table'];
    $conditions = $arguments['conditions'] ?? [];
    $limit = $arguments['limit'] ?? 10;

    try {
    $query = DB::table($table);

    foreach ($conditions as $condition) {
    $query->where(
    $condition['field'],
    $condition['operator'],
    $condition['value']
    );
    }

    $results = $query->limit($limit)->get();

    return json_encode([
    'status' => 'success',
    'count' => $results->count(),
    'data' => $results->toArray(),
    ]);
    } catch (\Exception $e) {
    return json_encode([
    'status' => 'error',
    'message' => 'Query failed: ' . $e->getMessage(),
    ]);
    }
    }
    }
    ```

    ### API Integration Tool
    ```php
    class WeatherApiTool implements ToolInterface
    {
    public function definition(): array
    {
    return [
    'name' => 'weather_lookup',
    'description' => 'Get current weather information for a location',
    'parameters' => [
    'type' => 'object',
    'properties' => [
    'location' => [
    'type' => 'string',
    'description' => 'City name or coordinates',
    ],
    'units' => [
    'type' => 'string',
    'description' => 'Temperature units',
    'enum' => ['celsius', 'fahrenheit'],
    'default' => 'celsius',
    ],
    ],
    'required' => ['location'],
    ],
    ];
    }

    public function execute(array $arguments, AgentContext $context, AgentMemory $memory): string
    {
    $location = $arguments['location'];
    $units = $arguments['units'] ?? 'celsius';

    try {
    $response = Http::get('https://api.weather.com/v1/current', [
    'location' => $location,
    'units' => $units,
    'api_key' => config('services.weather.key'),
    ]);

    if ($response->successful()) {
    return json_encode([
    'status' => 'success',
    'weather' => $response->json(),
    ]);
    }

    return json_encode([
    'status' => 'error',
    'message' => 'Weather API returned an error',
    ]);
    } catch (\Exception $e) {
    return json_encode([
    'status' => 'error',
    'message' => 'Failed to fetch weather: ' . $e->getMessage(),
    ]);
    }
    }
    }
    ```

    ### File Operation Tool
    ```php
    class FileManagerTool implements ToolInterface
    {
    public function definition(): array
    {
    return [
    'name' => 'file_manager',
    'description' => 'Read, write, or list files in the storage',
    'parameters' => [
    'type' => 'object',
    'properties' => [
    'operation' => [
    'type' => 'string',
    'enum' => ['read', 'write', 'list', 'delete'],
    'description' => 'The file operation to perform',
    ],
    'path' => [
    'type' => 'string',
    'description' => 'File or directory path',
    ],
    'content' => [
    'type' => 'string',
    'description' => 'Content to write (for write operation)',
    ],
    ],
    'required' => ['operation', 'path'],
    ],
    ];
    }

    public function execute(array $arguments, AgentContext $context, AgentMemory $memory): string
    {
    $operation = $arguments['operation'];
    $path = $arguments['path'];

    // Sanitize path to prevent directory traversal
    $path = str_replace(['..', '//'], '', $path);
    $fullPath = storage_path('app/agent-files/' . $path);

    try {
    switch ($operation) {
    case 'read':
    if (!file_exists($fullPath)) {
    throw new \Exception('File not found');
    }
    return json_encode([
    'status' => 'success',
    'content' => file_get_contents($fullPath),
    ]);

    case 'write':
    $content = $arguments['content'] ?? '';
    File::ensureDirectoryExists(dirname($fullPath));
    file_put_contents($fullPath, $content);
    return json_encode([
    'status' => 'success',
    'message' => 'File written successfully',
    ]);

    case 'list':
    $files = File::files($fullPath);
    return json_encode([
    'status' => 'success',
    'files' => array_map('basename', $files),
    ]);

    case 'delete':
    if (file_exists($fullPath)) {
    unlink($fullPath);
    }
    return json_encode([
    'status' => 'success',
    'message' => 'File deleted',
    ]);

    default:
    throw new \Exception('Invalid operation');
    }
    } catch (\Exception $e) {
    return json_encode([
    'status' => 'error',
    'message' => $e->getMessage(),
    ]);
    }
    }
    }
    ```

    ### Email Sending Tool
    ```php
    class EmailSenderTool implements ToolInterface
    {
    public function definition(): array
    {
    return [
    'name' => 'send_email',
    'description' => 'Send an email to specified recipients',
    'parameters' => [
    'type' => 'object',
    'properties' => [
    'to' => [
    'type' => 'string',
    'description' => 'Recipient email address',
    ],
    'subject' => [
    'type' => 'string',
    'description' => 'Email subject line',
    ],
    'body' => [
    'type' => 'string',
    'description' => 'Email body content (HTML supported)',
    ],
    'cc' => [
    'type' => 'array',
    'description' => 'CC recipients',
    'items' => ['type' => 'string'],
    ],
    ],
    'required' => ['to', 'subject', 'body'],
    ],
    ];
    }

    public function execute(array $arguments, AgentContext $context, AgentMemory $memory): string
    {
    try {
    $mail = Mail::raw($arguments['body'], function ($message) use ($arguments) {
    $message->to($arguments['to'])
    ->subject($arguments['subject']);

    if (isset($arguments['cc'])) {
    $message->cc($arguments['cc']);
    }
    });

    return json_encode([
    'status' => 'success',
    'message' => 'Email sent successfully',
    ]);
    } catch (\Exception $e) {
    return json_encode([
    'status' => 'error',
    'message' => 'Failed to send email: ' . $e->getMessage(),
    ]);
    }
    }
    }
    ```

    ## Using Context and Memory

    Tools receive `AgentContext` and `AgentMemory` for accessing execution context:

    ```php
    public function execute(array $arguments, AgentContext $context, AgentMemory $memory): string
    {
    // Access current user
    $user = $context->getUser();

    // Access session ID
    $sessionId = $context->getSessionId();

    // Access custom parameters
    $customParam = $context->getParameter('custom_key');

    // Store something in memory
    $memory->remember('last_action', 'Sent email to customer');

    // Retrieve from memory
    $lastAction = $memory->recall('last_action');

    // Access conversation history
    $history = $memory->getConversationHistory();

    return json_encode(['status' => 'success']);
    }
    ```

    ## Advanced Tool Patterns

    ### Multi-Step Tool with Progress
    ```php
    class DataProcessorTool implements ToolInterface
    {
    public function definition(): array
    {
    return [
    'name' => 'process_data',
    'description' => 'Process large datasets with progress tracking',
    'parameters' => [
    'type' => 'object',
    'properties' => [
    'dataset_id' => [
    'type' => 'string',
    'description' => 'ID of the dataset to process',
    ],
    ],
    'required' => ['dataset_id'],
    ],
    ];
    }

    public function execute(array $arguments, AgentContext $context, AgentMemory $memory): string
    {
    $datasetId = $arguments['dataset_id'];

    try {
    // Store progress in memory for agent to track
    $memory->remember('processing_status', 'started');

    $dataset = Dataset::find($datasetId);
    $totalItems = $dataset->items()->count();
    $processed = 0;

    foreach ($dataset->items()->cursor() as $item) {
    // Process item
    $this->processItem($item);
    $processed++;

    // Update progress
    if ($processed % 100 === 0) {
    $progress = round(($processed / $totalItems) * 100, 2);
    $memory->remember('processing_progress', $progress);
    }
    }

    $memory->remember('processing_status', 'completed');

    return json_encode([
    'status' => 'success',
    'processed' => $processed,
    'message' => "Processed {$processed} items successfully",
    ]);
    } catch (\Exception $e) {
    $memory->remember('processing_status', 'failed');
    return json_encode([
    'status' => 'error',
    'message' => $e->getMessage(),
    ]);
    }
    }
    }
    ```

    ### Tool with Validation
    ```php
    class PaymentProcessorTool implements ToolInterface
    {
    public function definition(): array
    {
    return [
    'name' => 'process_payment',
    'description' => 'Process a payment transaction',
    'parameters' => [
    'type' => 'object',
    'properties' => [
    'amount' => [
    'type' => 'number',
    'description' => 'Payment amount in cents',
    ],
    'currency' => [
    'type' => 'string',
    'enum' => ['USD', 'EUR', 'GBP'],
    ],
    'payment_method' => [
    'type' => 'string',
    'enum' => ['card', 'bank_transfer', 'paypal'],
    ],
    ],
    'required' => ['amount', 'currency', 'payment_method'],
    ],
    ];
    }

    public function execute(array $arguments, AgentContext $context, AgentMemory $memory): string
    {
    // Validate amount
    if ($arguments['amount'] <= 0) {
        return json_encode([ 'status'=> 'error',
        'message' => 'Amount must be positive',
        ]);
        }

        if ($arguments['amount'] > 100000000) { // $1,000,000 limit
        return json_encode([
        'status' => 'error',
        'message' => 'Amount exceeds maximum limit',
        ]);
        }

        // Additional validation based on payment method
        if ($arguments['payment_method'] === 'bank_transfer' && $arguments['amount'] < 10000) {
            return json_encode([ 'status'=> 'error',
            'message' => 'Bank transfer minimum is $100',
            ]);
            }

            try {
            // Process payment...
            $transactionId = $this->processWithProvider($arguments);

            return json_encode([
            'status' => 'success',
            'transaction_id' => $transactionId,
            'amount' => $arguments['amount'],
            'currency' => $arguments['currency'],
            ]);
            } catch (\Exception $e) {
            return json_encode([
            'status' => 'error',
            'message' => 'Payment failed: ' . $e->getMessage(),
            ]);
            }
            }
            }
            ```

            ## Testing Your Tools

            ```php
            use Tests\TestCase;
            use App\Tools\MyTool;
            use Vizra\VizraADK\System\AgentContext;
            use Vizra\VizraADK\Memory\AgentMemory;

            class MyToolTest extends TestCase
            {
            public function test_tool_executes_successfully()
            {
            $tool = new MyTool();

            // Create mock context and memory
            $context = new AgentContext();
            $memory = new AgentMemory();

            $result = $tool->execute(
            ['param' => 'value'],
            $context,
            $memory
            );

            $decoded = json_decode($result, true);
            $this->assertEquals('success', $decoded['status']);
            }

            public function test_tool_definition_is_valid()
            {
            $tool = new MyTool();
            $definition = $tool->definition();

            $this->assertArrayHasKey('name', $definition);
            $this->assertArrayHasKey('description', $definition);
            $this->assertArrayHasKey('parameters', $definition);
            }
            }
            ```

            ## Security Best Practices

            1. **Input Validation**: Always validate and sanitize inputs
            2. **Path Traversal**: Prevent directory traversal in file operations
            3. **SQL Injection**: Use query builders, never raw SQL with user input
            4. **Rate Limiting**: Implement rate limits for expensive operations
            5. **Authentication**: Verify user permissions in sensitive tools
            6. **Secrets**: Never expose API keys or passwords in responses

            ```php
            // Good: Using query builder
            DB::table($table)->where('id', $id)->get();

            // Bad: Raw SQL with user input
            DB::select("SELECT * FROM {$table} WHERE id = {$id}");

            // Good: Path sanitization
            $path = str_replace(['..', '//'], '', $userPath);

            // Good: Permission check
            if (!$context->getUser()->can('delete-files')) {
            return json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            }
            ```

            ## Common Mistakes to Avoid

            1. **Not returning JSON** - Always return JSON-encoded strings
            2. **Missing error handling** - Wrap operations in try-catch blocks
            3. **Forgetting required parameters** - List all required params
            4. **Vague descriptions** - Be specific about what the tool does
            5. **Not using AgentContext** - Leverage context for user/session data

            ## Performance Tips

            1. **Async Operations**: Use queues for long-running tasks
            2. **Caching**: Cache expensive operations when appropriate
            3. **Pagination**: Limit results to prevent memory issues
            4. **Lazy Loading**: Use cursor() for large datasets
            5. **Connection Pooling**: Reuse database/API connections

            ## Next Steps

            - Combine tools in agents: See `agent-creation.blade.php`
            - Build tool workflows: See `workflow-patterns.blade.php`
            - Use memory effectively: See `memory-usage.blade.php`


=== .ai/best-practices rules ===

# Vizra ADK Best Practices

Follow these best practices to build robust, maintainable, and efficient AI agent systems.

## Naming Conventions

### Agents
```php
// Good: Descriptive, ends with 'Agent'
CustomerSupportAgent
DataAnalysisAgent
ContentGeneratorAgent

// Bad: Vague or missing suffix
Support
Analyzer
GenContent
```

### Tools
```php
// Good: Action-oriented, ends with 'Tool'
DatabaseQueryTool
EmailSenderTool
FileUploaderTool

// Bad: Noun-only or missing suffix
Database
EmailService
Upload
```

### Agent Names (Internal)
```php
// Good: snake_case, descriptive
protected string $name = 'customer_support';
protected string $name = 'data_analyzer';

// Bad: camelCase or spaces
protected string $name = 'customerSupport';
protected string $name = 'Customer Support';
```

## Project Structure

```
app/
├── Agents/
│   ├── Support/
│   │   ├── CustomerSupportAgent.php
│   │   └── TechnicalSupportAgent.php
│   ├── Analytics/
│   │   ├── DataAnalysisAgent.php
│   │   └── ReportGeneratorAgent.php
│   └── BaseAgents/
│       └── CustomBaseAgent.php
├── Tools/
│   ├── Database/
│   │   └── QueryTool.php
│   ├── Communication/
│   │   ├── EmailTool.php
│   │   └── SlackTool.php
│   └── BaseTool.php
└── Workflows/
    ├── CustomerServiceWorkflow.php
    └── DataPipelineWorkflow.php
```

## Agent Design

### Single Responsibility
```php
// Good: Focused agent
class InvoiceGeneratorAgent extends BaseLlmAgent
{
    protected string $description = 'Generates invoices from order data';
    // Only handles invoice generation
}

// Bad: Doing too much
class EverythingAgent extends BaseLlmAgent
{
    protected string $description = 'Handles orders, invoices, emails, and reports';
    // Too many responsibilities
}
```

### Clear Instructions
```php
// Good: Specific, structured instructions
protected string $instructions = <<<'INSTRUCTIONS'
    You are a technical documentation writer.
    
    Your responsibilities:
    - Write clear, concise documentation
    - Include code examples
    - Follow company style guide
    
    Guidelines:
    - Use active voice
    - Avoid jargon
    - Include prerequisites
    - Provide step-by-step instructions
    INSTRUCTIONS;

// Bad: Vague instructions
protected string $instructions = 'You help with documentation.';
```

## Tool Implementation

### Error Handling
```php
public function execute(array $arguments, AgentContext $context, AgentMemory $memory): string
{
    try {
        // Validate inputs first
        if (empty($arguments['required_field'])) {
            throw new \InvalidArgumentException('required_field is missing');
        }
        
        // Perform operation
        $result = $this->performOperation($arguments);
        
        return json_encode([
            'status' => 'success',
            'data' => $result
        ]);
        
    } catch (\InvalidArgumentException $e) {
        return json_encode([
            'status' => 'error',
            'error_type' => 'validation',
            'message' => $e->getMessage()
        ]);
    } catch (\Exception $e) {
        Log::error('Tool execution failed', [
            'tool' => static::class,
            'error' => $e->getMessage()
        ]);
        
        return json_encode([
            'status' => 'error',
            'error_type' => 'execution',
            'message' => 'Operation failed: ' . $e->getMessage()
        ]);
    }
}
```

### Input Validation
```php
public function execute(array $arguments, AgentContext $context, AgentMemory $memory): string
{
    // Validate and sanitize inputs
    $validated = validator($arguments, [
        'email' => 'required|email',
        'amount' => 'required|numeric|min:0|max:10000',
        'date' => 'required|date|after:today'
    ])->validate();
    
    // Use validated data
    return $this->process($validated);
}
```

## Performance Optimization

### Model Selection
```php
// Use appropriate models for the task
class SimpleTaskAgent extends BaseLlmAgent
{
    protected string $model = 'gpt-4o-mini'; // Cheaper, faster for simple tasks
}

class ComplexReasoningAgent extends BaseLlmAgent
{
    protected string $model = 'gpt-4o'; // More capable for complex tasks
}
```

### Caching
```php
class CachedAgent extends BaseLlmAgent
{
    public function execute($input)
    {
        $cacheKey = 'agent_' . $this->name . '_' . md5($input);
        
        return Cache::remember($cacheKey, 3600, function() use ($input) {
            return parent::execute($input);
        });
    }
}
```

### Streaming for Long Responses
```php
// Enable streaming for better UX
$stream = $agent->run($input)
    ->streaming(true)
    ->go();

// Iterate over stream chunks
foreach ($stream as $chunk) {
    echo $chunk; // Send to frontend immediately
    ob_flush();
    flush();
}
```

## Security

### Never Expose Secrets
```php
// Good: Use config/env
protected function getApiKey()
{
    return config('services.external_api.key');
}

// Bad: Hardcoded secrets
protected $apiKey = 'sk-abc123...'; // NEVER DO THIS
```

### Input Sanitization
```php
public function execute(array $arguments, AgentContext $context, AgentMemory $memory): string
{
    // Sanitize file paths
    $path = str_replace(['..', '/', '\\'], '', $arguments['filename']);
    $safePath = storage_path('app/user-files/' . $path);
    
    // Validate allowed operations
    if (!in_array($arguments['operation'], ['read', 'write', 'list'])) {
        return json_encode(['error' => 'Invalid operation']);
    }
}
```

### Permission Checks
```php
public function execute(array $arguments, AgentContext $context, AgentMemory $memory): string
{
    $user = $context->getUser();
    
    if (!$user->can('perform-sensitive-action')) {
        return json_encode([
            'status' => 'error',
            'message' => 'Unauthorized'
        ]);
    }
    
    // Proceed with operation
}
```

## Testing

### Unit Tests for Agents
```php
class AgentTest extends TestCase
{
    public function test_agent_responds_appropriately()
    {
        $agent = new CustomerSupportAgent();
        $response = $agent->run('I need help with my order')
            ->forUser($this->user)
            ->go();
        
        $this->assertNotEmpty($response);
        $this->assertIsString($response);
    }
}
```

### Tool Testing
```php
class ToolTest extends TestCase
{
    public function test_tool_handles_errors_gracefully()
    {
        $tool = new DatabaseQueryTool();
        $result = $tool->execute(
            ['invalid' => 'params'],
            new AgentContext(),
            new AgentMemory()
        );
        
        $decoded = json_decode($result, true);
        $this->assertEquals('error', $decoded['status']);
    }
}
```

## Monitoring and Debugging

### Use Traces
```php
// Enable tracing for debugging
$response = MyAgent::run($input)
    ->withTracing(true)
    ->go();

// Review trace
php artisan vizra:trace $response->traceId
```

### Logging
```php
class LoggingAgent extends BaseLlmAgent
{
    public function execute($input)
    {
        Log::info('Agent execution started', [
            'agent' => $this->name,
            'input_length' => strlen($input)
        ]);
        
        $result = parent::execute($input);
        
        Log::info('Agent execution completed', [
            'agent' => $this->name,
            'response' => $result
        ]);
        
        return $result;
    }
}
```

## Memory Management

### Limit History Size
```php
// Prevent token limit issues in agent class
class EfficientAgent extends BaseLlmAgent
{
    protected bool $includeHistory = true;
    protected string $contextStrategy = 'recent'; // Only recent messages
    
    // Or override preprocessing
    protected function preprocessMemory($history)
    {
        // Keep only last 20 exchanges
        return array_slice($history, -20);
    }
}
```

### Summarize Long Conversations
```php
class MemoryEfficientAgent extends BaseLlmAgent
{
    protected function prepareMemory($history)
    {
        if (count($history) > 30) {
            // Summarize older messages
            $summary = $this->summarizeHistory(array_slice($history, 0, -10));
            return array_merge([$summary], array_slice($history, -10));
        }
        return $history;
    }
}
```

## Common Pitfalls to Avoid

1. **Token Limit Exceeded**: Always consider model token limits
2. **Infinite Delegation Loops**: Set max delegation depth
3. **Uncaught Exceptions**: Always wrap tool logic in try-catch
4. **Memory Leaks**: Clean up old sessions and traces
5. **Hardcoded Values**: Use configuration files
6. **Missing Validation**: Always validate user inputs
7. **Poor Error Messages**: Provide helpful error information
8. **Blocking Operations**: Use queues for long-running tasks

## Production Checklist

- [ ] All agents have clear, specific instructions
- [ ] Tools include comprehensive error handling
- [ ] Sensitive operations have permission checks
- [ ] API keys are in environment variables
- [ ] Agents are tested with unit tests
- [ ] Memory limits are configured appropriately
- [ ] Logging is implemented for debugging
- [ ] Performance bottlenecks are identified
- [ ] Documentation is complete and current
- [ ] Security audit has been performed


=== .ai/workflow-patterns rules ===

# Agent Workflow Patterns

Vizra ADK provides powerful workflow capabilities to orchestrate multiple agents working together. Use the `Workflow` facade to create sequential, parallel, conditional, and loop workflows.

## Workflow Types Overview

1. **Sequential**: Agents execute one after another, passing data forward
2. **Parallel**: Multiple agents execute simultaneously
3. **Conditional**: Branch based on conditions
4. **Loop**: Iterate over data or repeat until condition

## Sequential Workflows

Execute agents in order, with each agent receiving the previous agent's output:

### Basic Sequential Flow
```php
use Vizra\VizraADK\Facades\Workflow;

$result = Workflow::sequential()
    ->then(ResearchAgent::class)    // First: gather information
    ->then(AnalysisAgent::class)    // Second: analyze findings
    ->then(ReportAgent::class)      // Third: generate report
    ->run('Analyze market trends for electric vehicles');

echo $result['final_result']; // Final report from the last agent
// $result['step_results'] contains results from each step
```

### Sequential with Custom Input Transform
```php
$workflow = Workflow::sequential()
    ->start(DataFetchAgent::class)
    ->then(DataCleanAgent::class, function($previousOutput) {
        // Transform output before passing to next agent
        return "Clean this data: " . $previousOutput;
    })
    ->then(DataAnalysisAgent::class)
    ->run('Fetch sales data from Q4');
```

### Quick Sequential Creation
```php
// Shorthand for simple sequences
$result = Workflow::sequential(
    WebScraperAgent::class,
    ContentParserAgent::class,
    SummarizerAgent::class
)->run($url);
```

## Parallel Workflows

Execute multiple agents simultaneously for faster processing:

### Basic Parallel Execution
```php
$results = Workflow::parallel()
    ->agents([
        'weather' => WeatherAgent::class,
        'news' => NewsAgent::class,
        'stocks' => StockAgent::class,
    ])
    ->run('Get updates for New York');

// Access individual results (each agent returns a string)
echo $results['weather'];  // Weather update
echo $results['news'];     // News headlines
echo $results['stocks'];   // Stock market info
```

### Parallel with Different Inputs
```php
$workflow = Workflow::parallel()
    ->agent('translator_french', TranslatorAgent::class, 'Translate to French: Hello')
    ->agent('translator_spanish', TranslatorAgent::class, 'Translate to Spanish: Hello')
    ->agent('translator_german', TranslatorAgent::class, 'Translate to German: Hello');

$translations = $workflow->run();
```

### Combining Parallel Results
```php
// Gather multiple perspectives then synthesize
$perspectives = Workflow::parallel()
    ->agents([
        'optimist' => OptimistAgent::class,
        'pessimist' => PessimistAgent::class,
        'realist' => RealistAgent::class,
    ])
    ->run('Evaluate this business proposal');

// Feed all perspectives to a synthesizer
$synthesis = SynthesisAgent::run(json_encode($perspectives))->go();
```

## Conditional Workflows

Branch execution based on conditions:

### Basic Conditional Flow
```php
$workflow = Workflow::conditional()
    ->when(function($input) {
        // Condition checker - return true/false for first agent
        $sentiment = SentimentAnalyzer::analyze($input);
        return $sentiment->score > 0.5;
    }, PositiveResponseAgent::class)
    ->otherwise(NegativeResponseAgent::class)
    ->run($customerFeedback);
```

### Multi-Branch Conditions
```php
$workflow = Workflow::conditional()
    ->when(function($input) {
        return str_contains($input, 'urgent');
    }, UrgentHandlerAgent::class)
    ->when(function($input) {
        return str_contains($input, 'important');
    }, StandardHandlerAgent::class)
    ->when(function($input) {
        return !str_contains($input, 'urgent') && !str_contains($input, 'important');
    }, DeferredHandlerAgent::class)
    ->otherwise(GeneralHandlerAgent::class) // Fallback
    ->run($supportTicket);
```

### Nested Conditions
```php
$workflow = Workflow::conditional()
    ->when(function($input) {
        return detectRequestType($input) === 'technical';
    }, TechnicalRouterAgent::class) // Delegate to another agent for complex routing
    ->when(function($input) {
        return detectRequestType($input) === 'billing';
    }, BillingAgent::class)
    ->otherwise(GeneralSupportAgent::class)
    ->run($customerRequest);
```

## Loop Workflows

Iterate over collections or repeat until conditions are met:

### For Each Loop
```php
$items = ['item1', 'item2', 'item3'];

$results = Workflow::loop()
    ->agent(ProcessorAgent::class)
    ->forEach($items)
    ->run();

// Results array contains output for each item
foreach ($results['iteration_results'] as $iteration => $result) {
    if ($result['success']) {
        echo "Processed item {$iteration}: {$result['result']}\n";
    }
}
```

### While Loop with Condition
```php
$workflow = Workflow::while(
    RefineAgent::class,
    function($output) {
        // Continue while quality score is below threshold
        $score = QualityChecker::evaluate($output);
        return $score < 0.9;
    },
    10 // Maximum iterations to prevent infinite loops
);

$refinedContent = $workflow->run($draftContent);
```

### Loop with Accumulator
```php
$workflow = Workflow::loop()
    ->agent(DataEnrichmentAgent::class)
    ->forEach($records)
    ->accumulate(function($results) {
        // Combine all enriched records
        return array_merge(...$results);
    });

$enrichedDataset = $workflow->run();
```

### Loop Until Success
```php
$workflow = Workflow::loop()
    ->agent(ApiCallerAgent::class)
    ->until(function($output) {
        // Retry until we get a successful response
        $result = json_decode($output, true);
        return $result && $result['status'] === 'success';
    })
    ->maxIterations(5)
    ->run('Call external API');
```

## Complex Workflow Compositions

### Research and Report Pipeline
```php
class ResearchWorkflow
{
    public static function execute(string $topic)
    {
        // Step 1: Parallel research from multiple sources
        $research = Workflow::parallel()
            ->agents([
                'web' => WebResearchAgent::class,
                'academic' => AcademicSearchAgent::class,
                'news' => NewsResearchAgent::class,
            ])
            ->run($topic);

        // Step 2: Sequential processing of research
        $report = Workflow::sequential()
            ->then(DataConsolidatorAgent::class)
            ->then(FactCheckerAgent::class)
            ->then(ReportWriterAgent::class)
            ->then(EditorAgent::class)
            ->run(json_encode($research));

        return $report;
    }
}
```

### Customer Service Escalation
```php
class CustomerServiceWorkflow
{
    public static function handle(string $inquiry)
    {
        return Workflow::conditional()
            ->when(function($input) {
                $assessment = InitialAssessmentAgent::run($input)->go();
                $data = json_decode($assessment, true);
                return $data['complexity'] === 'simple';
            }, AutoResponseAgent::class)
            ->when(function($input) {
                $assessment = InitialAssessmentAgent::run($input)->go();
                $data = json_decode($assessment, true);
                return $data['complexity'] === 'moderate';
            }, ModerateComplexityAgent::class)
            ->when(function($input) {
                $assessment = InitialAssessmentAgent::run($input)->go();
                $data = json_decode($assessment, true);
                return $data['complexity'] === 'complex';
            }, ComplexIssueAgent::class)
            ->otherwise(GeneralHandlerAgent::class)
            ->run($inquiry);
    }
}
```

### Data Processing Pipeline
```php
class DataPipelineWorkflow
{
    public static function process(array $datasets)
    {
        // Step 1: Validate all datasets in parallel
        $validated = Workflow::parallel()
            ->agents(array_map(fn($d) => ValidationAgent::class, $datasets))
            ->run($datasets);

        // Step 2: Process each valid dataset
        $processed = Workflow::loop()
            ->agent(DataTransformAgent::class)
            ->forEach(array_filter($validated, fn($v) => $v->valid))
            ->run();

        // Step 3: Aggregate results
        $final = Workflow::sequential()
            ->then(DataAggregatorAgent::class)
            ->then(ReportGeneratorAgent::class)
            ->run(json_encode($processed));

        return $final;
    }
}
```

## Workflow Context and Parameters

### Passing Context Through Workflows
```php
$workflow = Workflow::sequential()
    ->then(FirstAgent::class)
    ->then(SecondAgent::class)
    ->forUser($user)              // Maintain user context
    ->withSession($sessionId)     // Maintain session
    ->withParameters([            // Custom parameters
        'mode' => 'production',
        'priority' => 'high'
    ])
    ->run($input);
```

### Workflow with Memory Persistence
```php
// Each agent in the workflow shares memory
$workflow = Workflow::sequential()
    ->then(GatherRequirementsAgent::class)
    ->then(DesignSolutionAgent::class)
    ->then(ImplementationAgent::class)
    ->forUser($user)
    ->withSession('project-123')
    ->run('Build a user authentication system');
```

## Error Handling in Workflows

### Workflow with Error Recovery
```php
try {
    $result = Workflow::sequential()
        ->then(RiskyOperationAgent::class)
        ->then(ProcessingAgent::class)
        ->onError(function($error, $stage) {
            // Log error and potentially recover
            Log::error("Workflow failed at stage {$stage}: {$error}");
            return ErrorRecoveryAgent::run($error)->go();
        })
        ->run($input);
} catch (WorkflowException $e) {
    // Handle complete workflow failure
    return FallbackAgent::run($input)->go();
}
```

### Partial Failure Handling
```php
$results = Workflow::parallel()
    ->agents($agentList)
    ->continueOnFailure() // Don't stop if one agent fails
    ->run($input);

// Check which agents succeeded
foreach ($results as $key => $result) {
    if ($result instanceof \Exception) {
        Log::warning("Agent {$key} failed: " . $result->getMessage());
    }
}
```

## Performance Optimization

### Timeout Configuration
```php
$workflow = Workflow::parallel()
    ->agents($agents)
    ->timeout(30) // 30 second timeout per agent
    ->run($input);
```

### Caching Workflow Results
```php
$cacheKey = 'workflow_' . md5($input);

$result = Cache::remember($cacheKey, 3600, function() use ($input) {
    return Workflow::sequential()
        ->agent(ExpensiveAgent1::class)
        ->agent(ExpensiveAgent2::class)
        ->run($input);
});
```

## Testing Workflows

```php
use Tests\TestCase;

class WorkflowTest extends TestCase
{
    public function test_sequential_workflow()
    {
        $workflow = Workflow::sequential()
            ->agent(TestAgent1::class)
            ->agent(TestAgent2::class);
        
        $result = $workflow->run('test input');
        
        $this->assertNotNull($result);
        $this->assertStringContainsString('expected', $result['final_result']);
    }
    
    public function test_parallel_workflow_returns_all_results()
    {
        $results = Workflow::parallel()
            ->agents([
                'a' => AgentA::class,
                'b' => AgentB::class,
            ])
            ->run('test');
        
        $this->assertCount(2, $results);
        $this->assertArrayHasKey('a', $results);
        $this->assertArrayHasKey('b', $results);
    }
}
```

## Best Practices

1. **Always set maximum iterations** for loops to prevent infinite execution
2. **Use meaningful branch names** in conditional workflows
3. **Consider timeout settings** for parallel workflows
4. **Log workflow stages** for debugging
5. **Cache expensive workflow results** when appropriate
6. **Test each workflow path** independently
7. **Document complex workflows** with clear comments
8. **Use error handlers** for production workflows
9. **Monitor workflow performance** with traces
10. **Break complex workflows** into reusable components

## Common Pitfalls

1. **Infinite loops**: Always set `maxIterations` on loops
2. **Memory issues**: Be careful with large datasets in loops
3. **Timeout failures**: Set appropriate timeouts for long-running agents
4. **Context loss**: Remember to pass user/session context through workflows
5. **Error propagation**: Handle errors at appropriate levels

## Next Steps

- Learn about memory persistence: See `memory-usage.blade.php`
- Implement sub-agent delegation: See `sub-agents.blade.php`
- Test workflow quality: See `evaluation.blade.php`


=== .ai/sub-agents rules ===

# Sub-Agent Delegation

Vizra ADK allows agents to delegate tasks to other specialized agents, creating powerful hierarchical systems.

## Basic Sub-Agent Delegation

### Using DelegateToSubAgentTool
```php
use Vizra\VizraADK\Tools\DelegateToSubAgentTool;

class ManagerAgent extends BaseLlmAgent
{
    protected string $name = 'manager';
    
    protected string $instructions = <<<'INSTRUCTIONS'
        You are a task coordinator. Delegate specialized tasks:
        - Use 'researcher' for information gathering
        - Use 'writer' for content creation
        - Use 'analyzer' for data analysis
        - Use 'emailer' for sending emails
        
        Coordinate their work to complete complex tasks.
        INSTRUCTIONS;
    
    protected array $tools = [
        DelegateToSubAgentTool::class,
    ];
}
```

## Delegation Patterns

### Task Distribution
```php
class ProjectManagerAgent extends BaseLlmAgent
{
    protected string $instructions = <<<'INSTRUCTIONS'
        You manage software development projects.
        
        Delegate tasks as follows:
        - 'requirements_analyst' for gathering requirements
        - 'system_designer' for architecture design
        - 'developer' for implementation details
        - 'tester' for test planning
        - 'documenter' for documentation
        
        Coordinate their outputs to deliver complete solutions.
        INSTRUCTIONS;
    
    protected array $tools = [
        DelegateToSubAgentTool::class,
    ];
}
```

### Hierarchical Delegation
```php
class CEOAgent extends BaseLlmAgent
{
    protected string $name = 'ceo';
    protected string $instructions = <<<'INSTRUCTIONS'
        You are the CEO agent. Delegate high-level tasks to:
        - 'cto' for technology decisions
        - 'cfo' for financial analysis
        - 'cmo' for marketing strategies
        INSTRUCTIONS;
    protected array $tools = [DelegateToSubAgentTool::class];
}

class CTOAgent extends BaseLlmAgent
{
    protected string $name = 'cto';
    protected string $instructions = <<<'INSTRUCTIONS'
        You are the CTO agent. Handle technology decisions.
        Delegate specific tasks to:
        - 'lead_developer' for implementation
        - 'security_expert' for security review
        - 'infrastructure' for deployment
        INSTRUCTIONS;
    protected array $tools = [DelegateToSubAgentTool::class];
}
```

## Context Passing

Sub-agents inherit context from parent agents:

```php
// Parent agent execution
$response = ManagerAgent::run('Create a marketing campaign')
    ->forUser($user)
    ->withSession('campaign-123')
    ->withParameters(['budget' => 50000])
    ->go();

// Sub-agents automatically receive:
// - User context ($user)
// - Session ID ('campaign-123')
// - Parameters (['budget' => 50000])
// - Conversation history
```

## Advanced Delegation Strategies

### Specialist Selection
```php
class SmartRouterAgent extends BaseLlmAgent
{
    protected string $instructions = <<<'INSTRUCTIONS'
        Analyze incoming requests and route to the most appropriate specialist.
        
        Available specialists:
        - 'legal_expert': Legal questions, contracts, compliance
        - 'financial_advisor': Budgets, investments, financial planning
        - 'technical_support': Technical issues, troubleshooting
        - 'creative_designer': Design, branding, creative content
        
        Choose based on the nature of the request.
        INSTRUCTIONS;
}
```

### Parallel Sub-Agent Execution
```php
class ResearchCoordinatorAgent extends BaseLlmAgent
{
    protected string $instructions = <<<'INSTRUCTIONS'
        For comprehensive research, delegate to multiple agents:
        
        1. Send the topic to 'web_researcher'
        2. Send the topic to 'academic_researcher'
        3. Send the topic to 'news_researcher'
        
        Compile and synthesize all findings.
        INSTRUCTIONS;
}
```

### Quality Control Chain
```php
class ContentProductionAgent extends BaseLlmAgent
{
    protected string $instructions = <<<'INSTRUCTIONS'
        Produce high-quality content through delegation:
        
        1. 'writer' - Create initial draft
        2. 'editor' - Review and improve
        3. 'fact_checker' - Verify facts
        4. 'proofreader' - Final polish
        
        Pass the output through each stage sequentially.
        INSTRUCTIONS;
}
```

## Configuration and Limits

### Maximum Delegation Depth
```php
// In config/vizra-adk.php
'max_delegation_depth' => 5, // Prevent infinite delegation chains
```

### Agent Access Control
```php
class RestrictedManagerAgent extends BaseLlmAgent
{
    protected array $allowedSubAgents = [
        'researcher',
        'writer',
        'analyzer',
    ];
    
    protected string $instructions = <<<'INSTRUCTIONS'
        You can only delegate to: researcher, writer, analyzer.
        Do not attempt to delegate to other agents.
        INSTRUCTIONS;
}
```

## Error Handling

### Handling Sub-Agent Failures
```php
class ResilientManagerAgent extends BaseLlmAgent
{
    protected string $instructions = <<<'INSTRUCTIONS'
        If a sub-agent fails or returns an error:
        1. Try an alternative agent if available
        2. Attempt the task yourself if possible
        3. Report the specific failure clearly
        
        Never fail silently - always provide feedback.
        INSTRUCTIONS;
}
```

## Memory Sharing

Sub-agents can access and contribute to shared memory:

```php
class MemorySharingManagerAgent extends BaseLlmAgent
{
    protected string $instructions = <<<'INSTRUCTIONS'
        Share context with sub-agents:
        - Pass relevant background information
        - Sub-agents can access conversation history
        - Their findings are added to shared memory
        
        Build collective knowledge through delegation.
        INSTRUCTIONS;
    
    protected array $tools = [
        DelegateToSubAgentTool::class,
        MemoryTool::class,
    ];
}
```

## Testing Sub-Agent Systems

```php
class SubAgentTest extends TestCase
{
    public function test_manager_delegates_correctly()
    {
        $response = ManagerAgent::run('Research and write about AI')
            ->forUser($user)
            ->go();
        
        // Check that sub-agents were called
        $traces = AgentTracer::getTraces();
        $this->assertStringContainsString('researcher', $traces);
        $this->assertStringContainsString('writer', $traces);
    }
}
```

## Best Practices

1. **Clear Role Definition**: Each agent should have a specific, well-defined purpose
2. **Avoid Circular Delegation**: Prevent agents from delegating back to parents
3. **Depth Limits**: Set maximum delegation depth to prevent infinite chains
4. **Error Propagation**: Handle sub-agent failures gracefully
5. **Context Preservation**: Ensure context flows properly through delegation
6. **Performance**: Consider parallel delegation for independent tasks
7. **Documentation**: Document the delegation hierarchy clearly

## Common Patterns

### Expert System
```php
// Multiple specialist agents coordinated by a manager
ManagerAgent -> [
    DiagnosticAgent,
    TreatmentAgent,
    FollowUpAgent
]
```

### Pipeline Processing
```php
// Sequential delegation through stages
InputAgent -> ProcessorAgent -> ValidatorAgent -> OutputAgent
```

### Load Balancing
```php
// Distribute work among similar agents
LoadBalancerAgent -> [
    Worker1Agent,
    Worker2Agent,
    Worker3Agent
]
```

## Next Steps

- Test delegation chains: See `evaluation.blade.php`
- Optimize delegation: See `best-practices.blade.php`


=== .ai/troubleshooting rules ===

# Troubleshooting Vizra ADK

Common issues and solutions when working with Vizra ADK agents.

## Common Errors

### Agent Not Found

**Error**: `Agent 'my_agent' not found`

**Solutions**:
1. Ensure agent class extends `BaseLlmAgent`
2. Check namespace is correct (`App\Agents`)
3. Clear cache: `php artisan cache:clear`
4. Run discovery: `php artisan vizra:agents`
5. Verify agent has unique `$name` property

```php
// Correct
class MyAgent extends BaseLlmAgent
{
    protected string $name = 'my_agent'; // Must be unique
}

// Wrong
class MyAgent extends BaseAgent // Wrong base class
{
    // Missing $name property
}
```

### Tool Execution Failed

**Error**: `Tool execution failed: undefined method`

**Solutions**:
1. Implement both required methods in tool:
```php
class MyTool implements ToolInterface
{
    public function definition(): array { /* ... */ }
    public function execute(array $arguments, AgentContext $context, AgentMemory $memory): string { /* ... */ }
}
```

2. Return JSON-encoded string from execute():
```php
// Correct
return json_encode(['status' => 'success', 'data' => $result]);

// Wrong
return $result; // Not JSON-encoded
```

### Memory Not Persisting

**Error**: Agent doesn't remember previous conversations

**Solutions**:
1. Provide user context:
```php
// Correct
$response = MyAgent::run($input)
    ->forUser($user) // Required for memory
    ->go();

// Wrong
$response = MyAgent::run($input)->go(); // No user context
```

2. Check database migrations:
```bash
php artisan migrate:status
php artisan migrate
```

3. Verify memory tables exist:
- `agent_sessions`
- `agent_messages`
- `agent_memories`

### Token Limit Exceeded

**Error**: `Maximum context length exceeded`

**Solutions**:
1. Limit conversation history in agent:
```php
class EfficientAgent extends BaseLlmAgent
{
    protected string $contextStrategy = 'recent'; // Only recent messages
    
    protected function preprocessMemory($history)
    {
        return array_slice($history, -10); // Last 10 messages
    }
}
```

2. Use smaller models for simple tasks:
```php
protected string $model = 'gpt-4o-mini'; // Instead of gpt-4o
```

3. Reduce max tokens:
```php
protected ?int $maxTokens = 1000; // Limit response length
```

### Streaming Not Working

**Error**: Stream returns as string instead of streaming

**Solutions**:
```php
// Correct
$stream = MyAgent::run($input)
    ->streaming(true) // Enable streaming
    ->go();

foreach ($stream as $chunk) {
    echo $chunk; // Process chunks
}

// Wrong
->stream(function($chunk) { /* ... */ }) // This method doesn't exist
```

### Workflow Execution Errors

**Error**: `Call to undefined method agent()`

**Solutions**:
```php
// Sequential workflow - use then() or start()
Workflow::sequential()
    ->then(FirstAgent::class)  // Correct
    ->then(SecondAgent::class)
    ->run($input);

// Wrong
->agent(FirstAgent::class) // Wrong method for sequential
```

### API Key Issues

**Error**: `Invalid API key` or `Unauthorized`

**Solutions**:
1. Check `.env` file:
```env
OPENAI_API_KEY=sk-...
ANTHROPIC_API_KEY=sk-ant-...
GEMINI_API_KEY=...
```

2. Clear config cache:
```bash
php artisan config:clear
php artisan config:cache
```

3. Verify provider configuration:
```php
protected string $model = 'gpt-4o';
protected ?string $provider = 'openai'; // Explicit provider
```

## Performance Issues

### Slow Response Times

**Solutions**:
1. Use appropriate models:
```php
// Fast, cheaper models for simple tasks
protected string $model = 'gpt-4o-mini';
protected string $model = 'gemini-flash';
```

2. Enable caching:
```php
Cache::remember("agent_response_{$hash}", 3600, function() {
    return $agent->run($input)->go();
});
```

3. Use parallel workflows:
```php
// Process multiple agents simultaneously
Workflow::parallel()
    ->agents($agents)
    ->run($input);
```

### High Token Usage

**Solutions**:
1. Optimize instructions:
```php
// Concise, clear instructions
protected string $instructions = 'You are a helpful assistant. Be concise.';
```

2. Limit tool calls:
```php
protected int $maxSteps = 3; // Limit tool execution steps
```

3. Use token tracking:
```php
$result = Agent::run($input)->go();
Log::info('Response: ' . $result);
```

## Debugging Techniques

### Enable Tracing

View detailed execution traces:
```bash
# Get trace ID from response
$response = Agent::run($input)->go();
$traceId = $response->traceId;

# View trace
php artisan vizra:trace $traceId
```

### Use Dashboard

Interactive debugging:
```bash
php artisan vizra:dashboard
# Visit http://localhost:8000/vizra/dashboard
```

### Check Logs

Laravel logs:
```php
// Add logging to agents
class DebugAgent extends BaseLlmAgent
{
    public function execute($input, AgentContext $context)
    {
        Log::info('Agent executing', [
            'agent' => $this->name,
            'input' => $input,
        ]);
        
        $result = parent::execute($input, $context);
        
        Log::info('Agent completed', [
            'agent' => $this->name,
            'response' => $result,
        ]);
        
        return $result;
    }
}
```

### Test in Isolation

```php
// Test agent directly
$agent = new MyAgent();
$context = new AgentContext();
$result = $agent->execute('test input', $context);

// Test tool directly
$tool = new MyTool();
$context = new AgentContext();
$memory = new AgentMemory($agent);
$result = $tool->execute(['param' => 'value'], $context, $memory);
```

## Database Issues

### Migration Errors

**Solutions**:
```bash
# Reset and re-run migrations
php artisan migrate:rollback
php artisan migrate

# Or fresh migration (WARNING: deletes data)
php artisan migrate:fresh
```

### Check Table Structure
```sql
-- Verify tables exist
SHOW TABLES LIKE 'agent_%';

-- Check table structure
DESCRIBE agent_sessions;
DESCRIBE agent_messages;
```

## Configuration Issues

### Package Not Loading

**Solutions**:
1. Check service provider registration:
```php
// config/app.php (if not auto-discovered)
'providers' => [
    Vizra\VizraADK\Providers\AgentServiceProvider::class,
],
```

2. Clear all caches:
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
composer dump-autoload
```

### Route Conflicts

**Solutions**:
```php
// config/vizra-adk.php
'routes' => [
    'prefix' => 'custom-prefix', // Change route prefix
    'middleware' => ['auth'], // Add middleware
],
```

## Memory Issues

### Out of Memory

**Solutions**:
1. Increase PHP memory limit:
```php
// In code
ini_set('memory_limit', '512M');

// Or in php.ini
memory_limit = 512M
```

2. Use cursor for large datasets:
```php
// Use cursor instead of get()
Model::cursor()->each(function($item) {
    // Process item
});
```

## Queue Issues

### Jobs Not Processing

**Solutions**:
1. Start queue worker:
```bash
php artisan queue:work
```

2. Check failed jobs:
```bash
php artisan queue:failed
php artisan queue:retry all
```

3. Use sync driver for debugging:
```env
QUEUE_CONNECTION=sync
```

## Quick Fixes

### Clear Everything
```bash
php artisan cache:clear && \
php artisan config:clear && \
php artisan route:clear && \
php artisan view:clear && \
composer dump-autoload
```

### Reinstall Package
```bash
composer remove vizra/vizra-adk
composer require vizra/vizra-adk
php artisan vizra:install
```

### Check System Requirements
```bash
php -v  # PHP 8.2+
php -m  # Check required extensions
composer show vizra/vizra-adk # Check version
```

## Getting Help

1. **Check Documentation**: https://vizra.ai/docs
2. **GitHub Issues**: https://github.com/vizra/vizra-adk/issues
3. **Run Diagnostics**: `php artisan vizra:diagnose` (if available)
4. **Enable Debug Mode**: Set `APP_DEBUG=true` in `.env`

## Prevention Tips

1. **Always extend correct base class** (`BaseLlmAgent` for agents)
2. **Return JSON from tools** (use `json_encode()`)
3. **Provide user context** for memory persistence
4. **Set appropriate models** for task complexity
5. **Monitor token usage** to control costs
6. **Test incrementally** with simple cases first
7. **Use type hints** for better IDE support
8. **Keep instructions concise** to save tokens
9. **Cache expensive operations** when possible
10. **Run tests regularly** with evaluation framework
</laravel-boost-guidelines>