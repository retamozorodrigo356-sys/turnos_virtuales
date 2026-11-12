<?php

namespace App\Http\Controllers;

use App\Events\QueueUpdated;
use App\Models\Service;
use App\Models\Turn;
use App\Support\QueueSnapshot;
use App\TurnStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KioskController extends Controller
{
    public function index(): View
    {
        return view('kiosk.index', [
            'services' => Service::withWaitingCounts(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'service_id' => [
                'required',
                'integer',
                Rule::exists('services', 'id')->where('is_active', true),
            ],
        ]);

        $turn = DB::transaction(function () use ($validated): Turn {
            $service = Service::query()
                ->whereKey($validated['service_id'])
                ->lockForUpdate()
                ->firstOrFail();

            $lastNumber = Turn::query()
                ->where('service_id', $service->id)
                ->whereDate('issued_on', today()->toDateString())
                ->max('number') ?? 0;

            $number = $lastNumber + 1;

            return Turn::create([
                'service_id' => $service->id,
                'number' => $number,
                'code' => $service->prefix.'-'.str_pad((string) $number, 3, '0', STR_PAD_LEFT),
                'status' => TurnStatus::Waiting,
                'issued_on' => today()->toDateString(),
            ]);
        });

        QueueUpdated::dispatch(QueueSnapshot::make());

        return redirect()->route('turns.ticket', $turn);
    }

    public function ticket(Turn $turn): View
    {
        $turn->load(['service', 'counter']);

        return view('kiosk.ticket', [
            'turn' => $turn,
            'waitingAhead' => $turn->waitingAhead(),
        ]);
    }

    public function status(Turn $turn): JsonResponse
    {
        $turn->load(['service', 'counter']);

        $waitingAhead = $turn->waitingAhead();

        return response()->json([
            'code' => $turn->code,
            'status' => $turn->status->value,
            'status_label' => $turn->status->label(),
            'is_waiting' => $turn->status === TurnStatus::Waiting,
            'waiting_ahead' => $waitingAhead,
            'position' => $turn->status === TurnStatus::Waiting ? $waitingAhead + 1 : null,
            'estimated_minutes' => $turn->estimatedWaitMinutes(),
            'counter' => $turn->counter?->name,
            'service' => $turn->service->name,
            'called_at' => $turn->called_at?->toIso8601String(),
        ]);
    }
}
