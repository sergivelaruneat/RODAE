<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Report\StoreReportRequest;
use App\Http\Requests\Report\UpdateReportRequest;
use App\Http\Resources\ReportDetailResource;
use App\Http\Resources\ReportResource;
use App\Http\Resources\RoutineResource;
use App\Models\Report;
use App\Models\ReportExercise;
use App\Models\Routine;
use App\Models\RoutineExercise;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ReportController extends Controller
{
    use AuthorizesRequests;

    /**
     * GET /reports
     * Lista paginada de reportes (por defecto los míos).
     * Filtros opcionales: year, month, from, to.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Report::class);

        // ← permite ver los de otro usuario si hay follow mutuo
        $userId = $this->resolveSubjectUserId($request);
        $per    = $request->integer('per_page', 15);

        $q = Report::query()
            ->with([
                'routine:id,name,sport,owner_user_id,rating_avg,exercises_count',
            ])
            ->forUser($userId)
            ->orderByDesc('created_at');

        // Rango de fechas
        if ($request->filled('from') && $request->filled('to')) {
            $from = Carbon::parse($request->input('from'))->startOfDay();
            $to   = Carbon::parse($request->input('to'))->endOfDay();
            $q->whereBetween('created_at', [$from, $to]);
        } elseif ($request->filled('year') && $request->filled('month')) {
            $year  = (int) $request->input('year');
            $month = (int) $request->input('month');
            $from  = Carbon::create($year, $month, 1)->startOfDay();
            $to    = (clone $from)->endOfMonth()->endOfDay();
            $q->whereBetween('created_at', [$from, $to]);
        }

        return ReportResource::collection($q->paginate($per));
    }

    /**
     * POST /reports
     * Crea un reporte con items (ejercicios ejecutados) para el usuario autenticado.
     */
    public function store(StoreReportRequest $request)
    {
        $this->authorize('create', Report::class);

        $report = DB::transaction(function () use ($request) {
            $routineId = (int) $request->routine_id;

            $report = Report::create([
                'user_id'    => $request->user()->id,
                'routine_id' => $routineId,
            ]);

            $items = collect($request->input('items', []))
                ->map(function ($i) use ($routineId) {
                    $this->ensureExerciseBelongsToRoutine((int) $i['routine_exercise_id'], $routineId);

                    return [
                        'routine_exercise_id' => (int) $i['routine_exercise_id'],
                        'difficulty'          => array_key_exists('difficulty', $i)
                            ? ($i['difficulty'] !== null ? (int) $i['difficulty'] : null)
                            : null,
                        'metric'              => $i['metric'] ?? null,
                        'completed'           => isset($i['completed']) ? (bool) $i['completed'] : false,
                    ];
                })
                ->all();

            if (!empty($items)) {
                $report->items()->createMany($items);
            }

            return $report;
        });

        $report->load([
            'routine:id,name,sport,owner_user_id,rating_avg,exercises_count',
            'items.routineExercise:id,routine_id,name,position',
        ]);

        return (new ReportDetailResource($report))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * GET /reports/{report}
     * Ver detalle de un reporte con sus items.
     */
    public function show(Report $report)
    {
        $this->authorize('view', $report);

        $report->load([
            'routine:id,name,sport,owner_user_id,rating_avg,exercises_count',
            'items.routineExercise:id,routine_id,name,position',
        ]);

        return new ReportDetailResource($report);
    }

    /**
     * PUT /reports/{report}
     * Actualiza items (upsert + delete). No permite cambiar routine_id.
     */
    public function update(UpdateReportRequest $request, Report $report)
    {
        $this->authorize('update', $report);

        DB::transaction(function () use ($request, $report) {
            $routineId = $report->routine_id;

            // Borrado de items
            $deleteIds = (array) $request->input('delete_item_ids', []);
            if (!empty($deleteIds)) {
                ReportExercise::where('report_id', $report->id)
                    ->whereIn('id', $deleteIds)
                    ->delete();
            }

            // Upsert de items
            foreach ((array) $request->input('items', []) as $item) {
                $data = [];

                if (array_key_exists('difficulty', $item)) {
                    $data['difficulty'] = $item['difficulty'] !== null ? (int) $item['difficulty'] : null;
                }
                if (array_key_exists('metric', $item)) {
                    $data['metric'] = $item['metric'] ?? null;
                }
                if (array_key_exists('completed', $item)) {
                    $data['completed'] = (bool) $item['completed'];
                }

                if (isset($item['id'])) {
                    $existing = ReportExercise::where('report_id', $report->id)
                        ->where('id', (int) $item['id'])
                        ->first();

                    if ($existing) {
                        if (isset($item['routine_exercise_id'])) {
                            $this->ensureExerciseBelongsToRoutine((int) $item['routine_exercise_id'], $routineId);
                            $existing->routine_exercise_id = (int) $item['routine_exercise_id'];
                        }
                        foreach ($data as $k => $v) {
                            $existing->{$k} = $v;
                        }
                        $existing->save();
                    }
                } else {
                    if (!isset($item['routine_exercise_id'])) {
                        continue;
                    }
                    $this->ensureExerciseBelongsToRoutine((int) $item['routine_exercise_id'], $routineId);

                    $report->items()->create(array_merge([
                        'routine_exercise_id' => (int) $item['routine_exercise_id'],
                    ], $data + [
                        'difficulty' => $data['difficulty'] ?? null,
                        'metric'     => $data['metric'] ?? null,
                        'completed'  => $data['completed'] ?? false,
                    ]));
                }
            }
        });

        $report->load([
            'routine:id,name,sport,owner_user_id,rating_avg,exercises_count',
            'items.routineExercise:id,routine_id,name,position',
        ]);

        return new ReportDetailResource($report);
    }

    /**
     * DELETE /reports/{report}
     */
    public function destroy(Report $report)
    {
        $this->authorize('delete', $report);
        $report->delete();
        return response()->noContent();
    }

    /* =================== Dashboard helpers =================== */

    /**
     * GET /reports/calendar?year=YYYY&month=MM[&user_id=N]
     * Devuelve [{date:'YYYY-MM-DD', count:int}, ...] con días del mes con reportes.
     */
    public function calendar(Request $request)
    {
        $this->authorize('viewAny', Report::class);

        $userId = $this->resolveSubjectUserId($request);

        $year  = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);

        $from = Carbon::create($year, $month, 1)->startOfDay();
        $to   = (clone $from)->endOfMonth()->endOfDay();

        $rows = Report::query()
            ->forUser($userId)
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw("DATE(created_at) as d, COUNT(*) as c")
            ->groupBy('d')
            ->orderBy('d')
            ->get()
            ->map(fn($r) => ['date' => $r->d, 'count' => (int) $r->c]);

        return response()->json($rows);
    }

    /**
     * GET /reports/recent-routines?limit=3[&user_id=N]
     * Últimas rutinas distintas realizadas por el usuario consultado.
     */
    public function recentRoutines(Request $request)
    {
        $this->authorize('viewAny', Report::class);

        $limit  = max(1, (int) $request->input('limit', 3));
        $userId = $this->resolveSubjectUserId($request);

        // Si guardas performed_at, usa COALESCE(performed_at, created_at)
        $orderCol = 'created_at';

        $routineIds = Report::query()
            ->where('user_id', $userId)
            ->orderByDesc($orderCol)
            ->pluck('routine_id')
            ->unique()
            ->take($limit)
            ->values();

        $routines = Routine::query()
            ->with('owner:id,name')
            ->whereIn('id', $routineIds)
            ->select('id','name','sport','owner_user_id','rating_avg','exercises_count')
            ->get();

        $sorted = $routineIds->map(fn($id) => $routines->firstWhere('id', $id))
            ->filter()
            ->values();

        return RoutineResource::collection($sorted);
    }

    /**
     * GET /reports/recent?limit=5[&user_id=N]
     * Últimos reportes del usuario consultado (resumen).
     */
    public function recent(Request $request)
    {
        $this->authorize('viewAny', Report::class);

        $limit  = max(1, (int) $request->input('limit', 5));
        $userId = $this->resolveSubjectUserId($request);

        $rows = Report::query()
            ->forUser($userId)
            ->with([
                'routine:id,name,sport,owner_user_id,rating_avg,exercises_count',
                'items.routineExercise:id,routine_id,name,position',
            ])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();

        return ReportResource::collection($rows);
    }

    /**
     * GET /reports/sport-breakdown[?from=YYYY-MM-DD&to=YYYY-MM-DD][&user_id=N]
     * Conteo de reportes por deporte (según rutina del reporte).
     */
    public function sportBreakdown(Request $request)
    {
        $this->authorize('viewAny', Report::class);

        $userId = $this->resolveSubjectUserId($request);

        $qb = Report::query()
            ->forUser($userId)
            ->join('routines', 'routines.id', '=', 'reports.routine_id')
            ->select('routines.sport', DB::raw('COUNT(*) as c'))
            ->groupBy('routines.sport');

        if ($request->filled('from') && $request->filled('to')) {
            $from = Carbon::parse($request->input('from'))->startOfDay();
            $to   = Carbon::parse($request->input('to'))->endOfDay();
            $qb->whereBetween('reports.created_at', [$from, $to]);
        }

        $rows = $qb->get()->map(fn($r) => [
            'sport' => $r->sport,
            'label' => method_exists(\App\Enums\Sport::class, 'from')
                ? \App\Enums\Sport::from($r->sport)->label()
                : $r->sport,
            'count' => (int) $r->c,
        ]);

        return response()->json($rows);
    }

    /* =================== Helpers =================== */

    /**
     * Resuelve el usuario “sujeto” de la consulta:
     * - Si llega ?user_id y hay follow mutuo → devuelve ese id.
     * - En otro caso → id del autenticado.
     */
    private function resolveSubjectUserId(Request $request): int
    {
        $authId   = (int) $request->user()->id;
        $targetId = (int) $request->query('user_id', 0);

        if ($targetId && $targetId !== $authId) {
            if ($this->hasMutualFollow($authId, $targetId)) {
                return $targetId;
            }
        }
        return $authId;
    }

    /**
     * Comprueba follow mutuo usando tu tabla `follows`
     * (follower_id ↔ followed_id).
     */
    private function hasMutualFollow(int $a, int $b): bool
    {
        $aFollowsB = DB::table('follows')
            ->where('follower_id', $a)
            ->where('followed_id', $b)
            ->exists();

        $bFollowsA = DB::table('follows')
            ->where('follower_id', $b)
            ->where('followed_id', $a)
            ->exists();

        return $aFollowsB && $bFollowsA;
    }

    /**
     * Seguridad: verifica que el ejercicio pertenece a la rutina dada.
     */
    private function ensureExerciseBelongsToRoutine(int $routineExerciseId, int $routineId): void
    {
        $ok = RoutineExercise::where('id', $routineExerciseId)
            ->where('routine_id', $routineId)
            ->exists();

        if (!$ok) {
            abort(422, 'El ejercicio no pertenece a la rutina indicada.');
        }
    }
}
