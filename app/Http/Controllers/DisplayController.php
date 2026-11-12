<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Support\QueueSnapshot;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class DisplayController extends Controller
{
    public function index(): View
    {
        return view('display.index', [
            'calledTurns' => QueueSnapshot::calledTurns(),
            'services' => Service::withWaitingCounts(),
        ]);
    }

    public function state(): JsonResponse
    {
        return response()->json([
            ...QueueSnapshot::make(),
            'called' => QueueSnapshot::calledTurns()->map(fn ($turn) => [
                'id' => $turn->id,
                'code' => $turn->code,
                'counter' => $turn->counter?->name,
                'service' => $turn->service->name,
                'called_at' => $turn->called_at?->toIso8601String(),
            ])->values(),
        ]);
    }
}
