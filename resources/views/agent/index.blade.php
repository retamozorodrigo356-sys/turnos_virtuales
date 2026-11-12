@extends('layouts.app')

@section('title', 'Consola de agente')

@section('content')
    @php($statusStyles = [
        'waiting' => 'bg-amber-100 text-amber-800',
        'called' => 'bg-emerald-100 text-emerald-800',
        'in_service' => 'bg-emerald-100 text-emerald-800',
        'completed' => 'bg-slate-200 text-slate-700',
        'no_show' => 'bg-rose-100 text-rose-800',
    ])

    <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Consola de atención</h1>
            <p class="mt-1 text-sm text-slate-600">Llame al siguiente ciudadano y gestione la atención de su ventanilla.</p>
        </div>
        <div class="flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">
            <span class="relative flex size-2">
                <span class="absolute inline-flex size-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex size-2 rounded-full bg-emerald-500"></span>
            </span>
            Cola sincronizada
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            @if($currentTurn)
                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-6 py-4">
                        <h2 class="font-semibold text-slate-900">Turno en curso</h2>
                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusStyles[$currentTurn->status->value] }}">{{ $currentTurn->status->label() }}</span>
                    </div>

                    <div class="p-6">
                        <div class="flex flex-wrap items-center gap-6">
                            <p class="text-6xl font-bold tracking-[0.08em] text-slate-900">{{ $currentTurn->code }}</p>
                            <div>
                                <p class="text-sm text-slate-500">Ventanilla</p>
                                <p class="text-xl font-semibold text-slate-900">{{ $currentTurn->counter?->name ?? 'Sin asignar' }}</p>
                                <p class="mt-1 text-sm text-slate-500">{{ $currentTurn->service->name }}</p>
                            </div>
                        </div>

                        <dl class="mt-6 grid grid-cols-2 gap-3 text-sm sm:grid-cols-3">
                            <div class="rounded-xl bg-slate-50 p-3">
                                <dt class="text-xs uppercase tracking-wider text-slate-500">Llamado</dt>
                                <dd class="mt-1 font-semibold text-slate-800">{{ $currentTurn->called_at?->format('H:i:s') ?? '—' }}</dd>
                            </div>
                            <div class="rounded-xl bg-slate-50 p-3">
                                <dt class="text-xs uppercase tracking-wider text-slate-500">Inicio</dt>
                                <dd class="mt-1 font-semibold text-slate-800">{{ $currentTurn->started_at?->format('H:i:s') ?? '—' }}</dd>
                            </div>
                            <div class="rounded-xl bg-slate-50 p-3">
                                <dt class="text-xs uppercase tracking-wider text-slate-500">Emitido</dt>
                                <dd class="mt-1 font-semibold text-slate-800">{{ $currentTurn->created_at->format('H:i:s') }}</dd>
                            </div>
                        </dl>

                        <div class="mt-6 flex flex-wrap gap-3">
                            @if($currentTurn->status->value === 'called')
                                <form method="POST" action="{{ route('agent.start', $currentTurn) }}">
                                    @csrf
                                    <button type="submit" class="rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700">Iniciar atención</button>
                                </form>
                                <form method="POST" action="{{ route('agent.absent', $currentTurn) }}">
                                    @csrf
                                    <button type="submit" class="rounded-xl border border-rose-300 px-5 py-2.5 text-sm font-semibold text-rose-700 transition hover:bg-rose-50">Marcar ausente</button>
                                </form>
                            @endif

                            @if(in_array($currentTurn->status->value, ['called', 'in_service'], true))
                                <form method="POST" action="{{ route('agent.finish', $currentTurn) }}">
                                    @csrf
                                    <button type="submit" class="rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800">Finalizar atención</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </section>
            @else
                <section class="rounded-2xl border border-dashed border-slate-300 bg-white/70 p-8 text-center">
                    <div class="mx-auto flex size-14 items-center justify-center rounded-full bg-slate-100 text-slate-500">
                        <svg class="size-7" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                        </svg>
                    </div>
                    <h2 class="mt-4 text-lg font-semibold text-slate-900">Sin turno activo</h2>
                    <p class="mt-1 text-sm text-slate-600">Seleccione un trámite y llame al siguiente ciudadano de la cola.</p>
                </section>
            @endif

            <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-6 py-4">
                    <h2 class="font-semibold text-slate-900">Llamar siguiente turno</h2>
                    <p class="mt-0.5 text-sm text-slate-500">El sistema respeta el orden de llegada de cada trámite.</p>
                </div>

                <form method="POST" action="{{ route('agent.call') }}" class="grid gap-4 p-6 sm:grid-cols-[1fr_1fr_auto] sm:items-end">
                    @csrf
                    <div>
                        <label for="service_id" class="block text-sm font-medium text-slate-700">Trámite</label>
                        <select id="service_id" name="service_id" required class="mt-1.5 w-full rounded-xl border-slate-300 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">Seleccione un trámite</option>
                            @foreach($services as $service)
                                <option value="{{ $service->id }}" @selected(old('service_id') == $service->id)>
                                    {{ $service->prefix }} · {{ $service->name }} ({{ $service->waiting_count }} en espera)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="counter_id" class="block text-sm font-medium text-slate-700">Ventanilla</label>
                        <select id="counter_id" name="counter_id" required class="mt-1.5 w-full rounded-xl border-slate-300 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">Seleccione su ventanilla</option>
                            @foreach($counters as $counter)
                                <option value="{{ $counter->id }}" @selected(old('counter_id') == $counter->id)>{{ $counter->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="rounded-xl bg-[#0b2b45] px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#123a5c] disabled:cursor-not-allowed disabled:opacity-50" @disabled($services->sum('waiting_count') === 0)>
                        Llamar siguiente
                    </button>
                </form>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                    <h2 class="font-semibold text-slate-900">Colas de espera</h2>
                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">{{ $services->sum('waiting_count') }} ciudadanos</span>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse($services as $service)
                        <div class="flex flex-wrap items-center gap-4 px-6 py-4">
                            <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-slate-900 text-sm font-bold text-emerald-300">{{ $service->prefix }}</span>
                            <div class="min-w-0 flex-1">
                                <p class="font-medium text-slate-900">{{ $service->name }}</p>
                                <p class="text-xs text-slate-500">{{ $service->average_minutes }} min promedio por atención</p>
                            </div>

                            <div class="flex flex-wrap items-center gap-1.5">
                                @forelse($waitingTurns->get($service->id, collect())->take(5) as $waiting)
                                    <span class="rounded-md bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700">{{ $waiting->code }}</span>
                                @empty
                                    <span class="text-xs text-slate-400">Cola vacía</span>
                                @endforelse
                            </div>

                            <span class="min-w-12 rounded-lg bg-amber-50 px-3 py-1.5 text-center text-sm font-bold text-amber-800" data-service-count="{{ $service->id }}">{{ $service->waiting_count }}</span>
                        </div>
                    @empty
                        <p class="px-6 py-8 text-center text-sm text-slate-500">No hay trámites activos.</p>
                    @endforelse
                </div>
            </section>
        </div>

        <aside class="space-y-6">
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-500">Resumen de hoy</h2>
                <div class="mt-4 space-y-3">
                    <div class="flex items-center justify-between rounded-xl bg-emerald-50 px-4 py-3">
                        <span class="text-sm font-medium text-emerald-900">Atendidos por usted</span>
                        <span class="text-xl font-bold text-emerald-800">{{ $servedToday }}</span>
                    </div>
                    <div class="flex items-center justify-between rounded-xl bg-slate-50 px-4 py-3">
                        <span class="text-sm font-medium text-slate-700">En espera (total)</span>
                        <span id="waiting-total" class="text-xl font-bold text-slate-900">{{ $services->sum('waiting_count') }}</span>
                    </div>
                    <div class="flex items-center justify-between rounded-xl bg-slate-50 px-4 py-3">
                        <span class="text-sm font-medium text-slate-700">Ventanillas activas</span>
                        <span class="text-xl font-bold text-slate-900">{{ $counters->count() }}</span>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-500">Flujo del turno</h2>
                <ol class="mt-4 space-y-3 text-sm text-slate-600">
                    <li class="flex gap-3"><span class="flex size-6 shrink-0 items-center justify-center rounded-full bg-slate-900 text-xs font-bold text-white">1</span> Llame al siguiente ciudadano de la cola.</li>
                    <li class="flex gap-3"><span class="flex size-6 shrink-0 items-center justify-center rounded-full bg-slate-900 text-xs font-bold text-white">2</span> Inicie la atención cuando se presente.</li>
                    <li class="flex gap-3"><span class="flex size-6 shrink-0 items-center justify-center rounded-full bg-slate-900 text-xs font-bold text-white">3</span> Finalice el turno o márquelo ausente.</li>
                </ol>
                <a href="{{ route('display') }}" target="_blank" class="mt-5 flex items-center justify-center gap-2 rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    Abrir pantalla de sala
                    <svg class="size-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H18m0 0v4.5M18 6l-6 6M9 6H6a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-3"/>
                    </svg>
                </a>
            </section>
        </aside>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (!window.Echo) return;

            window.Echo.channel('queue').listen('.queue.updated', (event) => {
                (event.services || []).forEach((service) => {
                    const badge = document.querySelector(`[data-service-count="${service.id}"]`);
                    if (badge) badge.textContent = service.waiting;
                });

                const total = document.getElementById('waiting-total');
                if (total && typeof event.waiting_total === 'number') {
                    total.textContent = event.waiting_total;
                }
            });
        });
    </script>
@endpush
