<?php

namespace App\Http\Controllers;

use App\Models\Counter;
use App\Models\Service;
use App\Models\Turn;
use App\TurnStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(): View
    {
        return view('admin.index', [
            'services' => Service::withWaitingCounts(),
            'counters' => Counter::query()
                ->withCount([
                    'turns as active_turns_count' => fn ($query) => $query
                        ->issuedToday()
                        ->whereIn('status', [TurnStatus::Called, TurnStatus::InService]),
                ])
                ->orderBy('name')
                ->get(),
            'stats' => [
                'today' => Turn::query()->issuedToday()->count(),
                'waiting' => Turn::query()->issuedToday()->where('status', TurnStatus::Waiting)->count(),
                'completed' => Turn::query()->issuedToday()->where('status', TurnStatus::Completed)->count(),
                'no_show' => Turn::query()->issuedToday()->where('status', TurnStatus::NoShow)->count(),
            ],
        ]);
    }

    public function storeService(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'prefix' => ['required', 'alpha', 'max:3', 'unique:services,prefix'],
            'description' => ['nullable', 'string', 'max:255'],
            'average_minutes' => ['required', 'integer', 'min:1', 'max:120'],
        ]);

        Service::create([
            ...$validated,
            'prefix' => strtoupper($validated['prefix']),
            'is_active' => true,
        ]);

        return back()->with('status', 'Trámite creado correctamente.');
    }

    public function updateService(Request $request, Service $service): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'prefix' => ['required', 'alpha', 'max:3', Rule::unique('services', 'prefix')->ignore($service)],
            'description' => ['nullable', 'string', 'max:255'],
            'average_minutes' => ['required', 'integer', 'min:1', 'max:120'],
        ]);

        $service->update([
            ...$validated,
            'prefix' => strtoupper($validated['prefix']),
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('status', 'Trámite actualizado.');
    }

    public function storeCounter(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:60', 'unique:counters,name'],
        ]);

        Counter::create([...$validated, 'is_active' => true]);

        return back()->with('status', 'Ventanilla creada correctamente.');
    }

    public function updateCounter(Request $request, Counter $counter): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:60', Rule::unique('counters', 'name')->ignore($counter)],
        ]);

        $counter->update([
            ...$validated,
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('status', 'Ventanilla actualizada.');
    }
}
