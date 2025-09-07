<?php

namespace App\Http\Controllers\Api;

use App\Enums\Sport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Routine\StoreRoutineRequest;
use App\Http\Requests\Routine\UpdateRoutineRequest;
use App\Http\Requests\Routine\RateRoutineRequest;
use App\Http\Requests\Routine\AssignRoutineRequest;
use App\Http\Resources\RoutineResource;
use App\Http\Resources\RoutineDetailResource;
use App\Models\Routine;
use App\Models\User;
use App\Models\UserRoutine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class RoutineController extends Controller
{
    use AuthorizesRequests;
    /**
     * GET /api/routines
     */
    public function index(Request $request)
    {
        $query = Routine::with('owner:id,name')
            ->select('id','name','sport','owner_user_id','rating_avg','exercises_count');

        if ($search = $request->string('q')->toString()) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('owner', fn($w) => $w->where('name', 'like', "%{$search}%"));
            });
        }

        $routines = $query->orderByDesc('id')->paginate($request->integer('per_page', 15));
        return RoutineResource::collection($routines);
    }

    /**
     * POST /api/routines
     */
    public function store(StoreRoutineRequest $request)
    {
        $this->authorize('create', Routine::class);

        return DB::transaction(function () use ($request) {
            $routine = Routine::create([
                'owner_user_id'   => $request->user()->id,
                'name'            => $request->name,
                'sport'           => $request->sport, // enum cast en el modelo
            ]);

            // createMany dispara mutators (description -> details)
            $routine->exercises()->createMany($request->exercises ?? []);
            $routine->syncExercisesCount();

            return new RoutineDetailResource(
                $routine->load(['owner:id,name','exercises'])
            );
        });
    }

    /**
     * GET /api/routines/{routine}
     */
    public function show(\App\Models\Routine $routine)
    {
        $routine->load('owner:id,name', 'exercises');
        return new \App\Http\Resources\RoutineDetailResource($routine);
    }

    /**
     * PUT /api/routines/{routine}
     */
    public function update(UpdateRoutineRequest $request, Routine $routine)
    {
        $this->authorize('update', $routine);

        return DB::transaction(function () use ($request, $routine) {
            // Meta de la rutina
            $routine->fill($request->only(['name','sport']))->save();

            // Borrado explícito por ids (dispara observer si borramos instancia a instancia)
            if ($request->filled('delete_exercise_ids')) {
                $toDelete = $routine->exercises()
                    ->whereIn('id', $request->input('delete_exercise_ids', []))
                    ->get();

                foreach ($toDelete as $ex) {
                    $ex->delete(); // -> deleted: RoutineExerciseObserver recalcula si quisieras
                }
            }

            // Upsert de ejercicios
            if ($request->filled('exercises')) {
                foreach ($request->exercises as $e) {
                    if (!empty($e['id'])) {
                        // actualizar existente con mutators (description->details)
                        $exercise = $routine->exercises()->whereKey($e['id'])->firstOrFail();
                        $exercise->fill(collect($e)->except('id')->toArray())->save();
                    } else {
                        // crear nuevo
                        $routine->exercises()->create($e);
                    }
                }
            }

            // Asegurar contador correcto
            $routine->syncExercisesCount();

            return new RoutineDetailResource(
                $routine->load(['owner:id,name','exercises'])
            );
        });
    }

    /**
     * DELETE /api/routines/{routine}
     */
    public function destroy(Request $request, Routine $routine)
    {
        $this->authorize('delete', $routine);
        $routine->delete();
        return response()->noContent();
    }

    /**
     * POST /api/routines/{routine}/rate
     */
    public function rate(RateRoutineRequest $request, Routine $routine)
    {
        // localizar vínculo user<->routine
        $ur = UserRoutine::where('user_id', $request->user()->id)
            ->where('routine_id', $routine->id)
            ->firstOrFail();

        $this->authorize('rate', $ur);

        $ur->update(['rating' => (int) $request->integer('rating')]);
        // El observer recalcula rating_avg. Refrescamos para devolver el valor actualizado.
        $routine->refresh();

        return response()->json([
            'status'     => 'rated',
            'rating_avg' => (float) $routine->rating_avg,
        ]);
    }

    /**
     * DELETE /api/routines/{routine}/rate
     */
    public function unrate(Request $request, Routine $routine)
    {
        $ur = UserRoutine::where('user_id', $request->user()->id)
            ->where('routine_id', $routine->id)
            ->firstOrFail();

        $this->authorize('rate', $ur);

        $ur->update(['rating' => null]);
        $routine->refresh();

        return response()->json([
            'status'     => 'unrated',
            'rating_avg' => (float) $routine->rating_avg,
        ]);
    }

    /**
     * GET /api/routines/created (rutas que yo creé)
     */
    public function created(Request $request)
    {
        $routines = Routine::with('owner:id,name')
            ->where('owner_user_id', $request->user()->id)
            ->select('id','name','sport','owner_user_id','rating_avg','exercises_count')
            ->orderByDesc('id')
            ->paginate($request->integer('per_page', 15));

        return RoutineResource::collection($routines);
    }

    /**
     * POST /api/routines/{routine}/assign   (trainer propietario -> atleta)
     */
    public function assign(AssignRoutineRequest $request, Routine $routine)
    {
        $this->authorize('assignToUser', $routine);

        $user = User::findOrFail($request->integer('user_id'));

        // Usamos el modelo para mantener consistencia con observers
        UserRoutine::firstOrCreate([
            'user_id'    => $user->id,
            'routine_id' => $routine->id,
        ]);

        return response()->json(['status' => 'assigned']);
    }

    /**
     * GET /api/sports  (enum de deportes)
     */
    public function sports()
    {
        $items = array_map(
            fn($case) => ['value' => $case->value, 'label' => $case->label()],
            Sport::cases()
        );

        return response()->json($items);
    }

    // GET /api/my/routines  (seguidas por el usuario actual)
    public function myRoutines(Request $request) {
        $routines = $request->user()->routinesFollowed()
            ->with('owner:id,name')
            ->select('routines.id','name','sport','owner_user_id','rating_avg','exercises_count')
            ->orderByDesc('user_routines.created_at')
            ->paginate($request->integer('per_page',15));
        return \App\Http\Resources\RoutineResource::collection($routines);
    }

    // GET /api/users/{user}/routines  (creadas por un entrenador concreto)
    public function byOwner(Request $request, \App\Models\User $user) {
        $routines = \App\Models\Routine::with('owner:id,name')
            ->where('owner_user_id',$user->id)
            ->select('id','name','sport','owner_user_id','rating_avg','exercises_count')
            ->orderByDesc('id')
            ->paginate($request->integer('per_page',15));
        return \App\Http\Resources\RoutineResource::collection($routines);
    }

    // POST /api/routines/{routine}/attach  (seguir)
    public function attach(\App\Http\Requests\Routine\FollowRoutineRequest $request, \App\Models\Routine $routine) {
        $this->authorize('follow', \App\Models\UserRoutine::class);
        \App\Models\UserRoutine::firstOrCreate([
            'user_id'=>$request->user()->id, 'routine_id'=>$routine->id,
        ]);
        return response()->json(['status'=>'attached']);
    }

    // DELETE /api/routines/{routine}/detach  (dejar de seguir)
    public function detach(Request $request, \App\Models\Routine $routine) {
        $ur = \App\Models\UserRoutine::where('user_id',$request->user()->id)
                ->where('routine_id',$routine->id)->firstOrFail();
        $this->authorize('unfollow', $ur);
        $ur->delete(); // dispara observer
        return response()->json(['status'=>'detached']);
    }

    // Rutinas que sigue un usuario (para el tab de perfil)
    public function followedByUser(Request $request, \App\Models\User $user)
    {
        $routines = $user->routinesFollowed()
            ->with('owner:id,name')
            ->select('routines.id','name','sport','owner_user_id','rating_avg','exercises_count')
            ->orderByDesc('user_routines.created_at')
            ->paginate($request->integer('per_page', 15));

        return \App\Http\Resources\RoutineResource::collection($routines);
    }

    // Rutinas creadas por un usuario (si es trainer; si es athlete, devolverá vacío)
    public function createdByUser(Request $request, \App\Models\User $user)
    {
        $routines = \App\Models\Routine::with('owner:id,name')
            ->where('owner_user_id', $user->id)
            ->select('id','name','sport','owner_user_id','rating_avg','exercises_count')
            ->orderByDesc('id')
            ->paginate($request->integer('per_page', 15));

        return \App\Http\Resources\RoutineResource::collection($routines);
    }
}
