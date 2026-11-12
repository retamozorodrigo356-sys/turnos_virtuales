<?php

namespace App\Http\Controllers;

use App\Events\QueueUpdated;
use App\Events\TurnCalled;
use App\Models\Counter;
use App\Models\Service;
use App\Models\Turn;
use App\Support\QueueSnapshot;
use App\Support\Realtime;
use App\TurnStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AgentController extends Controller
{
    public function index(Request $request): View
    {
        $waitingTurns = Turn::query()
            ->with('service')
            ->issuedToday()
            ->waiting()
            ->orderBy('created_at')
            ->orderBy('id')
            ->get()
            ->groupBy('service_id');

        return view('agent.index', [
            'services' => Service::withWaitingCounts(),
            'counters' => Counter::query()->active()->orderBy('name')->get(),
            'currentTurn' => $this->currentTurn($request),
            'waitingTurns' => $waitingTurns,
            'servedToday' => Turn::query()
                ->where('user_id', $request->user()->id)
                ->issuedToday()
                ->where('status', TurnStatus::Completed)
                ->count(),
        ]);
    }

    public function callNext(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'counter_id' => ['required', 'integer', 'exists:counters,id'],
        ]);

        $turn = DB::transaction(function () use ($validated, $request): ?Turn {
            if ($this->currentTurn($request) !== null) {
                throw ValidationException::withMessages([
                    'service_id' => 'Primero finalice el turno que está atendiendo.',
                ]);
            }

            $service = Service::query()->whereKey($validated['service_id'])->where('is_active', true)->firstOrFail();
            $counter = Counter::query()->whereKey($validated['counter_id'])->where('is_active', true)->firstOrFail();

            $turn = Turn::query()
                ->where('service_id', $service->id)
                ->issuedToday()
                ->waiting()
                ->orderBy('id')
                ->lockForUpdate()
                ->first();

            if ($turn === null) {
                return null;
            }

            $turn->update([
                'status' => TurnStatus::Called,
                'counter_id' => $counter->id,
                'user_id' => $request->user()->id,
                'called_at' => now(),
            ]);

            return $turn;
        });

        if ($turn === null) {
            return back()->with('error', 'No hay ciudadanos en espera para ese trámite.');
        }

        Realtime::broadcast(new TurnCalled($turn->fresh(['service', 'counter'])));
        $this->broadcastQueue();

        return back()
            ->with('status', "Llamando al turno {$turn->code} en {$turn->counter->name}.")
            ->with('called_turn', [
                'code' => $turn->code,
                'counter' => $turn->counter->name,
            ]);
    }

    public function recall(Request $request, Turn $turn): RedirectResponse
    {
        if ($turn->user_id !== $request->user()->id || ! in_array($turn->status, [TurnStatus::Called, TurnStatus::InService], true)) {
            return back()->with('error', 'Solo puede volver a llamar el turno que está atendiendo.');
        }

        Realtime::broadcast(new TurnCalled($turn->fresh(['service', 'counter'])));

        return back()
            ->with('status', "Turno {$turn->code} llamado nuevamente en {$turn->counter->name}.")
            ->with('called_turn', [
                'code' => $turn->code,
                'counter' => $turn->counter->name,
            ]);
    }

    public function start(Request $request, Turn $turn): RedirectResponse
    {
        if ($turn->status !== TurnStatus::Called) {
            return back()->with('error', 'Solo se puede iniciar un turno que fue llamado.');
        }

        $turn->update([
            'status' => TurnStatus::InService,
            'started_at' => now(),
            'user_id' => $turn->user_id ?? $request->user()->id,
        ]);

        $this->broadcastQueue();

        return back()->with('status', "Atención iniciada para el turno {$turn->code}.");
    }

    public function finish(Turn $turn): RedirectResponse
    {
        if (! in_array($turn->status, [TurnStatus::Called, TurnStatus::InService], true)) {
            return back()->with('error', 'El turno ya fue cerrado.');
        }

        $turn->update([
            'status' => TurnStatus::Completed,
            'started_at' => $turn->started_at ?? now(),
            'finished_at' => now(),
        ]);

        $this->broadcastQueue();

        return back()->with('status', "Turno {$turn->code} finalizado correctamente.");
    }

    public function markAbsent(Turn $turn): RedirectResponse
    {
        if ($turn->status !== TurnStatus::Called) {
            return back()->with('error', 'Solo se puede marcar como ausente un turno llamado.');
        }

        $turn->update([
            'status' => TurnStatus::NoShow,
            'finished_at' => now(),
        ]);

        $this->broadcastQueue();

        return back()->with('status', "Turno {$turn->code} marcado como ausente.");
    }

    private function currentTurn(Request $request): ?Turn
    {
        return Turn::query()
            ->with(['service', 'counter'])
            ->where('user_id', $request->user()->id)
            ->issuedToday()
            ->whereIn('status', [TurnStatus::Called, TurnStatus::InService])
            ->latest('called_at')
            ->first();
    }

    private function broadcastQueue(): void
    {
        Realtime::broadcast(new QueueUpdated(QueueSnapshot::make()));
    }
}
