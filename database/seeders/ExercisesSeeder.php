<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Exercise;
use App\Models\ExerciseCategory;
use App\Models\ExerciseName;
use Illuminate\Support\Str;

class ExercisesSeeder extends Seeder
{
    public function run(): void
    {
        $this->createExercises();
    }

    private function createExercises(): void
    {
        $exercisesData = [
            // PECHO (CHEST)
            [
                'primary_name' => 'Press de banca plano con barra',
                'slug' => 'press-banca-plano-barra',
                'description' => 'Ejercicio fundamental para el desarrollo del pecho usando barra en banco plano',
                'categories' => [
                    ['muscle_group', 'chest'],
                    ['movement_pattern', 'push'],
                    ['equipment', 'barbell'],
                    ['angle', 'flat']
                ],
                'names' => [
                    'es' => ['Press de banca plano con barra', 'Bench press', 'Press de pecho plano', 'Press de pecho con barra'],
                    'en' => ['Barbell bench press', 'Flat bench press', 'Chest press with barbell']
                ]
            ],
            [
                'primary_name' => 'Press de banca plano con mancuernas',
                'slug' => 'press-banca-plano-mancuernas',
                'description' => 'Ejercicio de pecho con mancuernas en banco plano para mayor rango de movimiento',
                'categories' => [
                    ['muscle_group', 'chest'],
                    ['movement_pattern', 'push'],
                    ['equipment', 'dumbbell'],
                    ['angle', 'flat']
                ],
                'names' => [
                    'es' => ['Press de banca plano con mancuernas', 'Dumbbell bench press', 'Press plano con mancuernas'],
                    'en' => ['Dumbbell bench press', 'Flat dumbbell press']
                ]
            ],
            [
                'primary_name' => 'Press inclinado con barra',
                'slug' => 'press-inclinado-barra',
                'description' => 'Ejercicio para pecho superior usando barra en banco inclinado',
                'categories' => [
                    ['muscle_group', 'chest'],
                    ['movement_pattern', 'push'],
                    ['equipment', 'barbell'],
                    ['angle', 'incline']
                ],
                'names' => [
                    'es' => ['Press inclinado con barra', 'Incline bench press', 'Press inclinado', 'Press al pecho superior'],
                    'en' => ['Incline barbell press', 'Incline bench press']
                ]
            ],
            [
                'primary_name' => 'Press inclinado con mancuernas',
                'slug' => 'press-inclinado-mancuernas',
                'description' => 'Ejercicio para pecho superior usando mancuernas en banco inclinado',
                'categories' => [
                    ['muscle_group', 'chest'],
                    ['movement_pattern', 'push'],
                    ['equipment', 'dumbbell'],
                    ['angle', 'incline']
                ],
                'names' => [
                    'es' => ['Press inclinado con mancuernas', 'Incline dumbbell press', 'Press inclinado con mancuernas'],
                    'en' => ['Incline dumbbell press']
                ]
            ],
            [
                'primary_name' => 'Press declinado con barra',
                'slug' => 'press-declinado-barra',
                'description' => 'Ejercicio para pecho inferior usando barra en banco declinado',
                'categories' => [
                    ['muscle_group', 'chest'],
                    ['movement_pattern', 'push'],
                    ['equipment', 'barbell'],
                    ['angle', 'decline']
                ],
                'names' => [
                    'es' => ['Press declinado con barra', 'Decline bench press', 'Press inferior de pecho'],
                    'en' => ['Decline barbell press', 'Decline bench press']
                ]
            ],
            [
                'primary_name' => 'Press declinado con mancuernas',
                'slug' => 'press-declinado-mancuernas',
                'description' => 'Ejercicio para pecho inferior usando mancuernas en banco declinado',
                'categories' => [
                    ['muscle_group', 'chest'],
                    ['movement_pattern', 'push'],
                    ['equipment', 'dumbbell'],
                    ['angle', 'decline']
                ],
                'names' => [
                    'es' => ['Press declinado con mancuernas', 'Decline dumbbell press', 'Press de pecho declinado'],
                    'en' => ['Decline dumbbell press']
                ]
            ],
            [
                'primary_name' => 'Apertura plana con mancuernas',
                'slug' => 'apertura-plana-mancuernas',
                'description' => 'Ejercicio de aislamiento para pecho usando mancuernas en banco plano',
                'categories' => [
                    ['muscle_group', 'chest'],
                    ['movement_pattern', 'other'],
                    ['equipment', 'dumbbell'],
                    ['angle', 'flat']
                ],
                'names' => [
                    'es' => ['Apertura plana con mancuernas', 'Dumbbell fly', 'Apertura de pecho', 'Aperturas planas'],
                    'en' => ['Dumbbell flyes', 'Chest flyes', 'Flat dumbbell flyes']
                ]
            ],
            [
                'primary_name' => 'Apertura inclinada con mancuernas',
                'slug' => 'apertura-inclinada-mancuernas',
                'description' => 'Ejercicio de aislamiento para pecho superior usando mancuernas en banco inclinado',
                'categories' => [
                    ['muscle_group', 'chest'],
                    ['movement_pattern', 'other'],
                    ['equipment', 'dumbbell'],
                    ['angle', 'incline']
                ],
                'names' => [
                    'es' => ['Apertura inclinada con mancuernas', 'Incline dumbbell fly', 'Apertura inclinada'],
                    'en' => ['Incline dumbbell flyes', 'Incline chest flyes']
                ]
            ],
            [
                'primary_name' => 'Apertura en máquina pec deck',
                'slug' => 'apertura-maquina-pec-deck',
                'description' => 'Ejercicio de aislamiento para pecho usando máquina pec deck',
                'categories' => [
                    ['muscle_group', 'chest'],
                    ['movement_pattern', 'other'],
                    ['equipment', 'machine']
                ],
                'names' => [
                    'es' => ['Apertura en máquina pec deck', 'Pec deck', 'Contractor', 'Máquina de aperturas'],
                    'en' => ['Pec deck flyes', 'Machine chest flyes', 'Pec deck']
                ]
            ],
            [
                'primary_name' => 'Fondos en paralelas para pecho',
                'slug' => 'fondos-paralelas-pecho',
                'description' => 'Ejercicio compuesto para pecho usando peso corporal en paralelas',
                'categories' => [
                    ['muscle_group', 'chest'],
                    ['movement_pattern', 'push'],
                    ['equipment', 'bodyweight']
                ],
                'names' => [
                    'es' => ['Fondos en paralelas para pecho', 'Dips', 'Fondos', 'Bajadas en paralelas', 'Paralelas'],
                    'en' => ['Chest dips', 'Parallel bar dips', 'Dips for chest']
                ]
            ],
            [
                'primary_name' => 'Pullover con mancuerna',
                'slug' => 'pullover-mancuerna',
                'description' => 'Ejercicio para expansión del pecho usando mancuerna',
                'categories' => [
                    ['muscle_group', 'chest'],
                    ['movement_pattern', 'other'],
                    ['equipment', 'dumbbell']
                ],
                'names' => [
                    'es' => ['Pullover con mancuerna', 'Dumbbell pullover', 'Jalón con mancuerna', 'Pull over'],
                    'en' => ['Dumbbell pullover', 'Pullover']
                ]
            ],

            // ESPALDA (BACK)
            [
                'primary_name' => 'Dominadas pronas',
                'slug' => 'dominadas-pronas',
                'description' => 'Ejercicio fundamental para espalda usando peso corporal con agarre prono',
                'categories' => [
                    ['muscle_group', 'back'],
                    ['movement_pattern', 'pull'],
                    ['equipment', 'bodyweight']
                ],
                'names' => [
                    'es' => ['Dominadas pronas', 'Pull ups', 'Chin ups prono', 'Barras'],
                    'en' => ['Pull ups', 'Overhand pull ups']
                ]
            ],
            [
                'primary_name' => 'Dominadas supinas',
                'slug' => 'dominadas-supinas',
                'description' => 'Ejercicio para espalda y bíceps usando peso corporal con agarre supino',
                'categories' => [
                    ['muscle_group', 'back'],
                    ['muscle_group', 'biceps'],
                    ['movement_pattern', 'pull'],
                    ['equipment', 'bodyweight']
                ],
                'names' => [
                    'es' => ['Dominadas supinas', 'Chin ups', 'Dominadas invertidas', 'Barras invertidas'],
                    'en' => ['Chin ups', 'Underhand pull ups', 'Supinated pull ups']
                ]
            ],
            [
                'primary_name' => 'Dominadas neutras',
                'slug' => 'dominadas-neutras',
                'description' => 'Ejercicio para espalda usando peso corporal con agarre neutro',
                'categories' => [
                    ['muscle_group', 'back'],
                    ['movement_pattern', 'pull'],
                    ['equipment', 'bodyweight']
                ],
                'names' => [
                    'es' => ['Dominadas neutras', 'Neutral grip pull up', 'Dominadas con agarre neutro'],
                    'en' => ['Neutral grip pull ups', 'Hammer grip pull ups']
                ]
            ],
            [
                'primary_name' => 'Jalón al pecho agarre ancho',
                'slug' => 'jalon-pecho-agarre-ancho',
                'description' => 'Ejercicio para espalda usando polea alta con agarre ancho',
                'categories' => [
                    ['muscle_group', 'back'],
                    ['movement_pattern', 'pull'],
                    ['equipment', 'cable']
                ],
                'names' => [
                    'es' => ['Jalón al pecho agarre ancho', 'Lat pulldown', 'Jalones frontales', 'Jalón abierto'],
                    'en' => ['Wide grip lat pulldown', 'Lat pulldown wide grip']
                ]
            ],
            [
                'primary_name' => 'Jalón al pecho supino',
                'slug' => 'jalon-pecho-supino',
                'description' => 'Ejercicio para espalda usando polea alta con agarre supino',
                'categories' => [
                    ['muscle_group', 'back'],
                    ['muscle_group', 'biceps'],
                    ['movement_pattern', 'pull'],
                    ['equipment', 'cable']
                ],
                'names' => [
                    'es' => ['Jalón al pecho supino', 'Underhand pulldown', 'Jalón con agarre invertido'],
                    'en' => ['Underhand lat pulldown', 'Reverse grip pulldown']
                ]
            ],
            [
                'primary_name' => 'Remo con barra',
                'slug' => 'remo-barra',
                'description' => 'Ejercicio compuesto para espalda usando barra libre',
                'categories' => [
                    ['muscle_group', 'back'],
                    ['movement_pattern', 'pull'],
                    ['equipment', 'barbell']
                ],
                'names' => [
                    'es' => ['Remo con barra', 'Barbell row', 'Remo con barra olímpica', 'Remo barra T'],
                    'en' => ['Barbell row', 'Bent over barbell row']
                ]
            ],
            [
                'primary_name' => 'Remo con mancuernas',
                'slug' => 'remo-mancuernas',
                'description' => 'Ejercicio unilateral para espalda usando mancuernas',
                'categories' => [
                    ['muscle_group', 'back'],
                    ['movement_pattern', 'pull'],
                    ['equipment', 'dumbbell']
                ],
                'names' => [
                    'es' => ['Remo con mancuernas', 'Dumbbell row', 'Remo a una mano', 'Remo mancuerna'],
                    'en' => ['Dumbbell row', 'Single arm dumbbell row']
                ]
            ],
            [
                'primary_name' => 'Remo en polea baja',
                'slug' => 'remo-polea-baja',
                'description' => 'Ejercicio para espalda usando polea baja sentado',
                'categories' => [
                    ['muscle_group', 'back'],
                    ['movement_pattern', 'pull'],
                    ['equipment', 'cable']
                ],
                'names' => [
                    'es' => ['Remo en polea baja', 'Seated cable row', 'Remo sentado', 'Jalón bajo'],
                    'en' => ['Seated cable row', 'Cable row']
                ]
            ],
            [
                'primary_name' => 'Peso muerto convencional',
                'slug' => 'peso-muerto-convencional',
                'description' => 'Ejercicio fundamental compuesto para espalda y piernas',
                'categories' => [
                    ['muscle_group', 'back'],
                    ['muscle_group', 'glutes'],
                    ['muscle_group', 'hamstrings'],
                    ['movement_pattern', 'hinge'],
                    ['equipment', 'barbell']
                ],
                'names' => [
                    'es' => ['Peso muerto convencional', 'Deadlift', 'Peso muerto', 'Levantamiento tierra', 'Despegue'],
                    'en' => ['Conventional deadlift', 'Deadlift']
                ]
            ],
            [
                'primary_name' => 'Peso muerto rumano',
                'slug' => 'peso-muerto-rumano',
                'description' => 'Variante de peso muerto enfocada en glúteos y femorales',
                'categories' => [
                    ['muscle_group', 'back'],
                    ['muscle_group', 'glutes'],
                    ['muscle_group', 'hamstrings'],
                    ['movement_pattern', 'hinge'],
                    ['equipment', 'barbell'],
                    ['range', 'romanian']
                ],
                'names' => [
                    'es' => ['Peso muerto rumano', 'Romanian deadlift', 'Peso muerto con piernas semirrígidas'],
                    'en' => ['Romanian deadlift', 'RDL', 'Stiff leg deadlift']
                ]
            ],
            [
                'primary_name' => 'Peso muerto sumo',
                'slug' => 'peso-muerto-sumo',
                'description' => 'Variante de peso muerto con posición de piernas más amplia',
                'categories' => [
                    ['muscle_group', 'back'],
                    ['muscle_group', 'glutes'],
                    ['muscle_group', 'quads'],
                    ['movement_pattern', 'hinge'],
                    ['equipment', 'barbell'],
                    ['stance', 'sumo']
                ],
                'names' => [
                    'es' => ['Peso muerto sumo', 'Sumo deadlift', 'Peso muerto abierto'],
                    'en' => ['Sumo deadlift']
                ]
            ],

            // PIERNAS (LEGS)
            [
                'primary_name' => 'Sentadilla libre con barra',
                'slug' => 'sentadilla-libre-barra',
                'description' => 'Ejercicio fundamental para piernas usando barra libre',
                'categories' => [
                    ['muscle_group', 'quads'],
                    ['muscle_group', 'glutes'],
                    ['movement_pattern', 'squat'],
                    ['equipment', 'barbell']
                ],
                'names' => [
                    'es' => ['Sentadilla libre con barra', 'Barbell squat', 'Squat', 'Cuclillas', 'Sentadillas'],
                    'en' => ['Barbell squat', 'Back squat', 'Squat']
                ]
            ],
            [
                'primary_name' => 'Sentadilla frontal con barra',
                'slug' => 'sentadilla-frontal-barra',
                'description' => 'Variante de sentadilla con barra al frente enfocada en cuádriceps',
                'categories' => [
                    ['muscle_group', 'quads'],
                    ['muscle_group', 'glutes'],
                    ['movement_pattern', 'squat'],
                    ['equipment', 'barbell']
                ],
                'names' => [
                    'es' => ['Sentadilla frontal con barra', 'Front squat', 'Sentadilla frontal'],
                    'en' => ['Front squat', 'Front barbell squat']
                ]
            ],
            [
                'primary_name' => 'Sentadilla goblet con mancuerna',
                'slug' => 'sentadilla-goblet-mancuerna',
                'description' => 'Variante de sentadilla sosteniendo mancuerna al pecho',
                'categories' => [
                    ['muscle_group', 'quads'],
                    ['muscle_group', 'glutes'],
                    ['movement_pattern', 'squat'],
                    ['equipment', 'dumbbell']
                ],
                'names' => [
                    'es' => ['Sentadilla goblet con mancuerna', 'Goblet squat', 'Sentadilla con mancuerna al pecho'],
                    'en' => ['Goblet squat', 'Dumbbell goblet squat']
                ]
            ],
            [
                'primary_name' => 'Sentadilla búlgara con mancuernas',
                'slug' => 'sentadilla-bulgara-mancuernas',
                'description' => 'Ejercicio unilateral para piernas con pie trasero elevado',
                'categories' => [
                    ['muscle_group', 'quads'],
                    ['muscle_group', 'glutes'],
                    ['movement_pattern', 'squat'],
                    ['equipment', 'dumbbell']
                ],
                'names' => [
                    'es' => ['Sentadilla búlgara con mancuernas', 'Bulgarian split squat', 'Zancada búlgara', 'Estocada trasera elevada'],
                    'en' => ['Bulgarian split squat', 'Rear foot elevated split squat']
                ]
            ],
            [
                'primary_name' => 'Prensa de piernas',
                'slug' => 'prensa-piernas',
                'description' => 'Ejercicio para piernas usando máquina de prensa',
                'categories' => [
                    ['muscle_group', 'quads'],
                    ['muscle_group', 'glutes'],
                    ['movement_pattern', 'squat'],
                    ['equipment', 'machine']
                ],
                'names' => [
                    'es' => ['Prensa de piernas', 'Leg press', 'Prensa inclinada', 'Prensa horizontal'],
                    'en' => ['Leg press', 'Leg press machine']
                ]
            ],
            [
                'primary_name' => 'Extensión de piernas en máquina',
                'slug' => 'extension-piernas-maquina',
                'description' => 'Ejercicio de aislamiento para cuádriceps usando máquina',
                'categories' => [
                    ['muscle_group', 'quads'],
                    ['movement_pattern', 'other'],
                    ['equipment', 'machine']
                ],
                'names' => [
                    'es' => ['Extensión de piernas en máquina', 'Leg extension', 'Extensión de cuádriceps'],
                    'en' => ['Leg extension', 'Quad extension']
                ]
            ],
            [
                'primary_name' => 'Curl de piernas tumbado',
                'slug' => 'curl-piernas-tumbado',
                'description' => 'Ejercicio de aislamiento para femorales en posición acostada',
                'categories' => [
                    ['muscle_group', 'hamstrings'],
                    ['movement_pattern', 'other'],
                    ['equipment', 'machine']
                ],
                'names' => [
                    'es' => ['Curl de piernas tumbado', 'Leg curl', 'Curl femoral', 'Curl acostado'],
                    'en' => ['Lying leg curl', 'Hamstring curl lying']
                ]
            ],
            [
                'primary_name' => 'Curl de piernas sentado',
                'slug' => 'curl-piernas-sentado',
                'description' => 'Ejercicio de aislamiento para femorales en posición sentada',
                'categories' => [
                    ['muscle_group', 'hamstrings'],
                    ['movement_pattern', 'other'],
                    ['equipment', 'machine']
                ],
                'names' => [
                    'es' => ['Curl de piernas sentado', 'Seated leg curl', 'Curl sentado'],
                    'en' => ['Seated leg curl', 'Sitting hamstring curl']
                ]
            ],
            [
                'primary_name' => 'Hip thrust con barra',
                'slug' => 'hip-thrust-barra',
                'description' => 'Ejercicio específico para glúteos usando barra',
                'categories' => [
                    ['muscle_group', 'glutes'],
                    ['movement_pattern', 'hinge'],
                    ['equipment', 'barbell']
                ],
                'names' => [
                    'es' => ['Hip thrust con barra', 'Hip thrust', 'Empuje de cadera', 'Puente de glúteos con barra'],
                    'en' => ['Barbell hip thrust', 'Hip thrust']
                ]
            ],
            [
                'primary_name' => 'Elevaciones de talones',
                'slug' => 'elevaciones-talones',
                'description' => 'Ejercicio para gemelos/pantorrillas',
                'categories' => [
                    ['muscle_group', 'calves'],
                    ['movement_pattern', 'other'],
                    ['equipment', 'bodyweight']
                ],
                'names' => [
                    'es' => ['Elevaciones de talones', 'Calf raise', 'Gemelos', 'Punta de pies'],
                    'en' => ['Calf raises', 'Standing calf raise']
                ]
            ],

            // HOMBROS (SHOULDERS)
            [
                'primary_name' => 'Press militar con barra',
                'slug' => 'press-militar-barra',
                'description' => 'Ejercicio fundamental para hombros usando barra',
                'categories' => [
                    ['muscle_group', 'shoulders'],
                    ['movement_pattern', 'push'],
                    ['equipment', 'barbell']
                ],
                'names' => [
                    'es' => ['Press militar con barra', 'Military press', 'Overhead press', 'Press hombros con barra'],
                    'en' => ['Military press', 'Overhead press', 'Standing barbell press']
                ]
            ],
            [
                'primary_name' => 'Press militar con mancuernas',
                'slug' => 'press-militar-mancuernas',
                'description' => 'Ejercicio para hombros usando mancuernas con mayor rango de movimiento',
                'categories' => [
                    ['muscle_group', 'shoulders'],
                    ['movement_pattern', 'push'],
                    ['equipment', 'dumbbell']
                ],
                'names' => [
                    'es' => ['Press militar con mancuernas', 'Dumbbell shoulder press', 'Press hombros con mancuernas'],
                    'en' => ['Dumbbell shoulder press', 'Seated dumbbell press']
                ]
            ],
            [
                'primary_name' => 'Press Arnold',
                'slug' => 'press-arnold',
                'description' => 'Variante de press de hombros con rotación de muñecas',
                'categories' => [
                    ['muscle_group', 'shoulders'],
                    ['movement_pattern', 'push'],
                    ['equipment', 'dumbbell']
                ],
                'names' => [
                    'es' => ['Press Arnold', 'Arnold press', 'Press rotatorio de hombros'],
                    'en' => ['Arnold press', 'Rotational shoulder press']
                ]
            ],
            [
                'primary_name' => 'Elevaciones laterales con mancuernas',
                'slug' => 'elevaciones-laterales-mancuernas',
                'description' => 'Ejercicio de aislamiento para deltoides medio',
                'categories' => [
                    ['muscle_group', 'shoulders'],
                    ['movement_pattern', 'other'],
                    ['equipment', 'dumbbell']
                ],
                'names' => [
                    'es' => ['Elevaciones laterales con mancuernas', 'Lateral raise', 'Vuelo lateral'],
                    'en' => ['Lateral raises', 'Side lateral raise', 'Dumbbell lateral raise']
                ]
            ],
            [
                'primary_name' => 'Elevaciones frontales con mancuernas',
                'slug' => 'elevaciones-frontales-mancuernas',
                'description' => 'Ejercicio de aislamiento para deltoides anterior',
                'categories' => [
                    ['muscle_group', 'shoulders'],
                    ['movement_pattern', 'other'],
                    ['equipment', 'dumbbell']
                ],
                'names' => [
                    'es' => ['Elevaciones frontales con mancuernas', 'Front raise', 'Elevaciones anteriores'],
                    'en' => ['Front raises', 'Anterior raises', 'Dumbbell front raise']
                ]
            ],
            [
                'primary_name' => 'Face pull en polea alta',
                'slug' => 'face-pull-polea-alta',
                'description' => 'Ejercicio para deltoides posterior y músculos de la espalda alta',
                'categories' => [
                    ['muscle_group', 'shoulders'],
                    ['movement_pattern', 'pull'],
                    ['equipment', 'cable']
                ],
                'names' => [
                    'es' => ['Face pull en polea alta', 'Face pull', 'Jalón de rostro', 'Tiro facial'],
                    'en' => ['Face pulls', 'Cable face pulls', 'Rope face pulls']
                ]
            ],
            [
                'primary_name' => 'Remo al mentón con barra',
                'slug' => 'remo-menton-barra',
                'description' => 'Ejercicio compuesto para hombros y trapecios',
                'categories' => [
                    ['muscle_group', 'shoulders'],
                    ['movement_pattern', 'pull'],
                    ['equipment', 'barbell']
                ],
                'names' => [
                    'es' => ['Remo al mentón con barra', 'Upright row', 'Remo vertical'],
                    'en' => ['Upright row', 'Barbell upright row']
                ]
            ],

            // BRAZOS (ARMS)
            [
                'primary_name' => 'Curl con barra recta',
                'slug' => 'curl-barra-recta',
                'description' => 'Ejercicio fundamental para bíceps usando barra recta',
                'categories' => [
                    ['muscle_group', 'biceps'],
                    ['movement_pattern', 'pull'],
                    ['equipment', 'barbell']
                ],
                'names' => [
                    'es' => ['Curl con barra recta', 'Barbell curl', 'Curl de bíceps', 'Curl barra'],
                    'en' => ['Barbell curl', 'Standing barbell curl', 'Bicep curl']
                ]
            ],
            [
                'primary_name' => 'Curl con barra Z',
                'slug' => 'curl-barra-z',
                'description' => 'Ejercicio para bíceps usando barra EZ para mayor comodidad de muñecas',
                'categories' => [
                    ['muscle_group', 'biceps'],
                    ['movement_pattern', 'pull'],
                    ['equipment', 'barbell']
                ],
                'names' => [
                    'es' => ['Curl con barra Z', 'EZ bar curl', 'Curl con barra curva'],
                    'en' => ['EZ bar curl', 'EZ curl bar bicep curl']
                ]
            ],
            [
                'primary_name' => 'Curl martillo con mancuernas',
                'slug' => 'curl-martillo-mancuernas',
                'description' => 'Ejercicio para bíceps y braquial con agarre neutro',
                'categories' => [
                    ['muscle_group', 'biceps'],
                    ['movement_pattern', 'pull'],
                    ['equipment', 'dumbbell']
                ],
                'names' => [
                    'es' => ['Curl martillo con mancuernas', 'Hammer curl', 'Curl neutro', 'Curl martillo'],
                    'en' => ['Hammer curl', 'Neutral grip dumbbell curl']
                ]
            ],
            [
                'primary_name' => 'Curl concentrado',
                'slug' => 'curl-concentrado',
                'description' => 'Ejercicio de aislamiento para bíceps en posición sentada',
                'categories' => [
                    ['muscle_group', 'biceps'],
                    ['movement_pattern', 'pull'],
                    ['equipment', 'dumbbell']
                ],
                'names' => [
                    'es' => ['Curl concentrado', 'Concentration curl', 'Curl concentrado sentado'],
                    'en' => ['Concentration curl', 'Seated concentration curl']
                ]
            ],
            [
                'primary_name' => 'Press francés con barra Z',
                'slug' => 'press-frances-barra-z',
                'description' => 'Ejercicio fundamental para tríceps acostado con barra EZ',
                'categories' => [
                    ['muscle_group', 'triceps'],
                    ['movement_pattern', 'push'],
                    ['equipment', 'barbell']
                ],
                'names' => [
                    'es' => ['Press francés con barra Z', 'Skullcrusher', 'Rompecráneos', 'Extensión de tríceps en banco'],
                    'en' => ['Skullcrushers', 'Lying tricep extension', 'EZ bar skullcrushers']
                ]
            ],
            [
                'primary_name' => 'Fondos en paralelas',
                'slug' => 'fondos-paralelas',
                'description' => 'Ejercicio compuesto para tríceps usando peso corporal',
                'categories' => [
                    ['muscle_group', 'triceps'],
                    ['movement_pattern', 'push'],
                    ['equipment', 'bodyweight']
                ],
                'names' => [
                    'es' => ['Fondos en paralelas', 'Dips', 'Fondos', 'Bajadas'],
                    'en' => ['Tricep dips', 'Parallel bar dips', 'Dips']
                ]
            ],
            [
                'primary_name' => 'Extensión de tríceps en polea con cuerda',
                'slug' => 'extension-triceps-polea-cuerda',
                'description' => 'Ejercicio de aislamiento para tríceps usando polea alta con cuerda',
                'categories' => [
                    ['muscle_group', 'triceps'],
                    ['movement_pattern', 'push'],
                    ['equipment', 'cable']
                ],
                'names' => [
                    'es' => ['Extensión de tríceps en polea con cuerda', 'Rope pushdown', 'Jalón de tríceps con cuerda', 'Extensión con soga'],
                    'en' => ['Rope pushdowns', 'Cable rope tricep pushdown', 'Tricep rope extension']
                ]
            ],
            [
                'primary_name' => 'Patada de tríceps con mancuerna',
                'slug' => 'patada-triceps-mancuerna',
                'description' => 'Ejercicio de aislamiento para tríceps inclinado hacia adelante',
                'categories' => [
                    ['muscle_group', 'triceps'],
                    ['movement_pattern', 'push'],
                    ['equipment', 'dumbbell']
                ],
                'names' => [
                    'es' => ['Patada de tríceps con mancuerna', 'Triceps kickback', 'Extensión trasera de tríceps'],
                    'en' => ['Tricep kickbacks', 'Dumbbell tricep kickback']
                ]
            ],

            // ABDOMINALES Y CORE
            [
                'primary_name' => 'Crunch abdominal',
                'slug' => 'crunch-abdominal',
                'description' => 'Ejercicio básico para abdominales superiores',
                'categories' => [
                    ['muscle_group', 'abs'],
                    ['movement_pattern', 'flexion'],
                    ['equipment', 'bodyweight']
                ],
                'names' => [
                    'es' => ['Crunch abdominal', 'Ab crunch', 'Encogimiento abdominal', 'Abdominal corto'],
                    'en' => ['Crunches', 'Abdominal crunches', 'Basic crunches']
                ]
            ],
            [
                'primary_name' => 'Crunch en máquina',
                'slug' => 'crunch-maquina',
                'description' => 'Ejercicio para abdominales usando máquina con resistencia',
                'categories' => [
                    ['muscle_group', 'abs'],
                    ['movement_pattern', 'flexion'],
                    ['equipment', 'machine']
                ],
                'names' => [
                    'es' => ['Crunch en máquina', 'Machine crunch', 'Abdominal en máquina'],
                    'en' => ['Machine crunches', 'Ab machine']
                ]
            ],
            [
                'primary_name' => 'Elevaciones de piernas colgado',
                'slug' => 'elevaciones-piernas-colgado',
                'description' => 'Ejercicio para abdominales inferiores colgado de barra',
                'categories' => [
                    ['muscle_group', 'abs'],
                    ['movement_pattern', 'flexion'],
                    ['equipment', 'bodyweight']
                ],
                'names' => [
                    'es' => ['Elevaciones de piernas colgado', 'Hanging leg raise', 'Elevaciones de piernas', 'Abdominal colgado'],
                    'en' => ['Hanging leg raises', 'Hanging knee raises']
                ]
            ],
            [
                'primary_name' => 'Plancha frontal',
                'slug' => 'plancha-frontal',
                'description' => 'Ejercicio isométrico para core y estabilidad',
                'categories' => [
                    ['muscle_group', 'abs'],
                    ['movement_pattern', 'anti_extension'],
                    ['equipment', 'bodyweight']
                ],
                'names' => [
                    'es' => ['Plancha frontal', 'Plank', 'Plancha', 'Plancha abdominal'],
                    'en' => ['Plank', 'Front plank', 'Forearm plank']
                ]
            ],
            [
                'primary_name' => 'Plancha lateral',
                'slug' => 'plancha-lateral',
                'description' => 'Ejercicio isométrico para oblicuos y estabilidad lateral',
                'categories' => [
                    ['muscle_group', 'obliques'],
                    ['movement_pattern', 'anti_lateral_flexion'],
                    ['equipment', 'bodyweight']
                ],
                'names' => [
                    'es' => ['Plancha lateral', 'Side plank', 'Plancha lateral'],
                    'en' => ['Side plank', 'Lateral plank']
                ]
            ],
            [
                'primary_name' => 'Russian twist',
                'slug' => 'russian-twist',
                'description' => 'Ejercicio dinámico para oblicuos con rotación de torso',
                'categories' => [
                    ['muscle_group', 'obliques'],
                    ['movement_pattern', 'rotation'],
                    ['equipment', 'bodyweight']
                ],
                'names' => [
                    'es' => ['Russian twist', 'Giros rusos', 'Torsiones rusas', 'Abdominal con giro'],
                    'en' => ['Russian twists', 'Seated Russian twist']
                ]
            ],
            [
                'primary_name' => 'Ab wheel rollout',
                'slug' => 'ab-wheel-rollout',
                'description' => 'Ejercicio avanzado para core usando rueda abdominal',
                'categories' => [
                    ['muscle_group', 'abs'],
                    ['movement_pattern', 'anti_extension'],
                    ['equipment', 'other']
                ],
                'names' => [
                    'es' => ['Ab wheel rollout', 'Rueda abdominal', 'Rollout', 'Rodillo abdominal'],
                    'en' => ['Ab wheel rollout', 'Ab wheel', 'Rollouts']
                ]
            ],

            // CARDIO Y FUNCIONAL
            [
                'primary_name' => 'Cinta de correr',
                'slug' => 'cinta-correr',
                'description' => 'Ejercicio cardiovascular en máquina de correr',
                'categories' => [
                    ['muscle_group', 'full_body'],
                    ['movement_pattern', 'other'],
                    ['equipment', 'machine']
                ],
                'names' => [
                    'es' => ['Cinta de correr', 'Treadmill', 'Caminadora', 'Banda para correr'],
                    'en' => ['Treadmill', 'Running machine', 'Treadmill running']
                ]
            ],
            [
                'primary_name' => 'Bicicleta estática',
                'slug' => 'bicicleta-estatica',
                'description' => 'Ejercicio cardiovascular en bicicleta fija',
                'categories' => [
                    ['muscle_group', 'quads'],
                    ['muscle_group', 'calves'],
                    ['movement_pattern', 'other'],
                    ['equipment', 'machine']
                ],
                'names' => [
                    'es' => ['Bicicleta estática', 'Stationary bike', 'Bicicleta fija', 'Spinning bike'],
                    'en' => ['Stationary bike', 'Exercise bike', 'Spin bike']
                ]
            ],
            [
                'primary_name' => 'Elíptica',
                'slug' => 'eliptica',
                'description' => 'Ejercicio cardiovascular de bajo impacto en máquina elíptica',
                'categories' => [
                    ['muscle_group', 'full_body'],
                    ['movement_pattern', 'other'],
                    ['equipment', 'machine']
                ],
                'names' => [
                    'es' => ['Elíptica', 'Elliptical', 'Crosstrainer'],
                    'en' => ['Elliptical', 'Elliptical trainer', 'Cross trainer']
                ]
            ],
            [
                'primary_name' => 'Remo ergómetro',
                'slug' => 'remo-ergometro',
                'description' => 'Ejercicio cardiovascular completo simulando remo',
                'categories' => [
                    ['muscle_group', 'full_body'],
                    ['movement_pattern', 'pull'],
                    ['equipment', 'machine']
                ],
                'names' => [
                    'es' => ['Remo ergómetro', 'Rowing machine', 'Rower', 'Máquina de remo'],
                    'en' => ['Rowing machine', 'Indoor rowing', 'Erg rowing']
                ]
            ],
            [
                'primary_name' => 'Burpees',
                'slug' => 'burpees',
                'description' => 'Ejercicio funcional completo combinando flexión, salto y squat',
                'categories' => [
                    ['muscle_group', 'full_body'],
                    ['movement_pattern', 'power'],
                    ['equipment', 'bodyweight']
                ],
                'names' => [
                    'es' => ['Burpees', 'Burpees', 'Saltos con flexión', 'Ejercicio militar'],
                    'en' => ['Burpees', 'Squat thrust']
                ]
            ],
            [
                'primary_name' => 'Cuerda para saltar',
                'slug' => 'cuerda-saltar',
                'description' => 'Ejercicio cardiovascular con cuerda',
                'categories' => [
                    ['muscle_group', 'calves'],
                    ['movement_pattern', 'power'],
                    ['equipment', 'other']
                ],
                'names' => [
                    'es' => ['Cuerda para saltar', 'Jump rope', 'Comba', 'Soga'],
                    'en' => ['Jump rope', 'Skipping rope', 'Rope jumping']
                ]
            ],
            [
                'primary_name' => 'Box jump',
                'slug' => 'box-jump',
                'description' => 'Ejercicio pliométrico saltando sobre cajón',
                'categories' => [
                    ['muscle_group', 'quads'],
                    ['muscle_group', 'glutes'],
                    ['movement_pattern', 'power'],
                    ['equipment', 'other']
                ],
                'names' => [
                    'es' => ['Box jump', 'Salto al cajón', 'Salto pliométrico', 'Jump box'],
                    'en' => ['Box jumps', 'Plyometric box jumps', 'Jump box']
                ]
            ],
            [
                'primary_name' => 'Battle ropes',
                'slug' => 'battle-ropes',
                'description' => 'Ejercicio funcional usando cuerdas pesadas',
                'categories' => [
                    ['muscle_group', 'full_body'],
                    ['movement_pattern', 'power'],
                    ['equipment', 'other']
                ],
                'names' => [
                    'es' => ['Battle ropes', 'Cuerdas de combate', 'Cuerdas ondulantes'],
                    'en' => ['Battle ropes', 'Training ropes', 'Heavy ropes']
                ]
            ]
        ];

        foreach ($exercisesData as $exerciseData) {
            $this->createExercise($exerciseData);
        }
    }

    private function createExercise(array $data): void
    {
        // Crear el ejercicio
        $exercise = Exercise::create([
            'slug' => $data['slug'],
            'name' => $data['primary_name'], // Se usa para crear el nombre primario automáticamente
            'description' => $data['description']
        ]);

        // Asociar categorías
        foreach ($data['categories'] as [$type, $name]) {
            $category = ExerciseCategory::where('type_slug', $type)
                ->where('name_slug', $name)
                ->first();

            if ($category) {
                $exercise->categories()->attach($category->id);
            }
        }

        // Crear nombres adicionales (el nombre primario ya se creó automáticamente)
        foreach ($data['names'] as $locale => $names) {
            foreach ($names as $name) {
                // Skip si el nombre ya existe para evitar duplicados
                $existingName = ExerciseName::where('exercise_id', $exercise->id)
                    ->where('name', $name)
                    ->where('locale', $locale)
                    ->first();

                if (!$existingName) {
                    ExerciseName::create([
                        'exercise_id' => $exercise->id,
                        'name' => $name,
                        'locale' => $locale,
                        'is_primary' => false
                    ]);
                }
            }
        }
    }
}
