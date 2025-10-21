# Funcionalidad de Rutina Actual (is_current)

Esta funcionalidad permite marcar una rutina como "actual" o "en curso" para cada cliente, facilitando la gestión del historial de rutinas.

## Características

- **Solo una rutina actual por cliente**: Cuando se marca una rutina como actual, las demás del mismo cliente se desmarcan automáticamente
- **Historial completo**: Se mantienen todas las rutinas anteriores para referencia
- **Búsquedas optimizadas**: Índice compuesto para consultas rápidas
- **Métodos convenientes**: API simple para gestionar rutinas actuales

## Uso

### Marcar una rutina como actual

```php
$workout = Workout::find($id);
$workout->makeCurrentForClient();

// O directamente
$workout->update(['is_current' => true]);
```

### Obtener la rutina actual de un cliente

```php
$currentWorkout = Workout::currentForClient($clientId);

if ($currentWorkout) {
    echo "Rutina actual: {$currentWorkout->name}";
} else {
    echo "El cliente no tiene rutina actual";
}
```

### Usar scopes para consultas

```php
// Obtener todas las rutinas actuales
$currentWorkouts = Workout::current()->get();

// Obtener todas las rutinas de un cliente
$clientWorkouts = Workout::forClient($clientId)->get();

// Combinar scopes
$clientCurrentWorkout = Workout::forClient($clientId)->current()->first();
```

### Obtener rutinas por tipo

```php
// Solo rutinas históricas (no actuales)
$historicalWorkouts = Workout::forClient($clientId)
    ->where('is_current', false)
    ->orderBy('created_at', 'desc')
    ->get();

// Solo rutina actual
$currentWorkout = Workout::forClient($clientId)
    ->current()
    ->first();
```

## Relaciones

```php
// Desde el modelo User (cliente)
$user = User::find($clientId);
$currentWorkout = $user->myWorkouts()->current()->first();

// Todas las rutinas del cliente
$allWorkouts = $user->myWorkouts()->get();
```

## Ejemplos de uso en controladores

### Obtener rutina actual en API

```php
class WorkoutController extends Controller
{
    public function getCurrentWorkout(Request $request)
    {
        $clientId = $request->user()->id;
        $currentWorkout = Workout::currentForClient($clientId);

        if (!$currentWorkout) {
            return response()->json(['message' => 'No hay rutina actual'], 404);
        }

        return response()->json([
            'current_workout' => $currentWorkout->load('exerciseWorkouts'),
            'is_current' => true
        ]);
    }
}
```

### Cambiar rutina actual

```php
class WorkoutController extends Controller
{
    public function makeCurrentWorkout(Request $request, Workout $workout)
    {
        // Verificar que la rutina pertenece al cliente
        if ($workout->client_id !== $request->user()->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $workout->makeCurrentForClient();

        return response()->json([
            'message' => 'Rutina marcada como actual',
            'current_workout' => $workout
        ]);
    }
}
```

### Listar rutinas con indicador de actual

```php
class WorkoutController extends Controller
{
    public function index(Request $request)
    {
        $clientId = $request->user()->id;

        $workouts = Workout::forClient($clientId)
            ->orderByDesc('is_current')  // Rutina actual primero
            ->orderByDesc('created_at')  // Luego por fecha
            ->get();

        return response()->json([
            'workouts' => $workouts->map(function ($workout) {
                return [
                    'id' => $workout->id,
                    'name' => $workout->name,
                    'is_current' => $workout->is_current,
                    'created_at' => $workout->created_at,
                ];
            })
        ]);
    }
}
```

## Consideraciones importantes

1. **Automático**: No necesitas desmarcar manualmente otras rutinas, se hace automáticamente
2. **Por cliente**: Cada cliente puede tener una rutina actual independiente
3. **Performance**: El índice compuesto (`client_id`, `is_current`) optimiza las consultas
4. **Flexibilidad**: Puedes cambiar la rutina actual en cualquier momento

## Testeo

Se incluyen tests completos que verifican:

- Marcar rutina como actual
- Solo una rutina actual por cliente
- Obtener rutina actual
- Scopes funcionan correctamente
- No afecta rutinas de otros clientes

Ejecutar tests:

```bash
php artisan test --filter=WorkoutCurrentTest
```

## Migración

La migración agrega:

- Campo `is_current` (boolean, default false)
- Índice compuesto para optimización
- Rollback completo disponible

```bash
# Aplicar migración
php artisan migrate

# Rollback si es necesario
php artisan migrate:rollback
```
