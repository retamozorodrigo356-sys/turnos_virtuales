@extends('layouts.public')

@section('title', 'Turno '.$turn->code.' · Turnos Ciudadanos')

@section('content')
    <div class="mx-auto max-w-xl px-4 py-10 sm:px-6">
        <div
            id="ticket"
            class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-xl"
            data-status-url="{{ route('turns.status', $turn) }}"
        >
            <div class="bg-[#0b2b45] px-6 py-5 text-center text-white">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-emerald-300">Su turno virtual</p>
                <p id="ticket-code" class="mt-2 text-5xl font-bold tracking-[0.15em]">{{ $turn->code }}</p>
                <p class="mt-2 text-sm text-white/70">{{ $turn->service->name }} · {{ $turn->issued_on->format('d/m/Y') }}</p>
            </div>

            <div class="px-6 py-6">
                <div class="flex items-center justify-between gap-3">
                    <span id="status-pill" class="inline-flex items-center gap-2 rounded-full bg-amber-100 px-3 py-1 text-sm font-semibold text-amber-800">
                        <span class="size-2 rounded-full bg-amber-500"></span>
                        <span id="status-label">{{ $turn->status->label() }}</span>
                    </span>
                    <span id="live-indicator" class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-500">
                        <span class="relative flex size-2">
                            <span class="absolute inline-flex size-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex size-2 rounded-full bg-emerald-500"></span>
                        </span>
                        En vivo
                    </span>
                </div>

                <div id="waiting-panel" class="{{ $turn->status->value === 'waiting' ? '' : 'hidden' }} mt-6 grid grid-cols-2 gap-4">
                    <div class="rounded-2xl bg-slate-50 p-4 text-center ring-1 ring-slate-200">
                        <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Su posición</p>
                        <p id="position" class="mt-1 text-4xl font-bold text-slate-900">{{ $waitingAhead + 1 }}</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-4 text-center ring-1 ring-slate-200">
                        <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Espera estimada</p>
                        <p class="mt-1 text-4xl font-bold text-slate-900"><span id="estimate">{{ $waitingAhead * $turn->service->average_minutes }}</span><span class="text-lg font-semibold text-slate-500"> min</span></p>
                    </div>
                    <div class="col-span-2 text-center text-sm text-slate-600">
                        <span id="waiting-ahead">{{ $waitingAhead }}</span> {{ $waitingAhead === 1 ? 'persona' : 'personas' }} antes que usted
                    </div>
                </div>

                <div id="called-panel" class="{{ $turn->status->isActive() ? '' : 'hidden' }} mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-5 text-center">
                    <p class="text-sm font-medium text-emerald-800">Diríjase a</p>
                    <p id="counter-name" class="mt-1 text-3xl font-bold text-emerald-900">{{ $turn->counter?->name ?? 'Ventanilla asignada' }}</p>
                    <p class="mt-1 text-sm text-emerald-700">Su turno está siendo llamado. Preséntese de inmediato.</p>
                </div>

                <div id="finished-panel" class="{{ in_array($turn->status->value, ['completed', 'no_show'], true) ? '' : 'hidden' }} mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-5 text-center text-slate-700">
                    <p id="finished-message" class="text-sm font-medium">
                        {{ $turn->status->value === 'completed' ? 'Su atención ha finalizado. ¡Gracias por su visita!' : 'Su turno fue marcado como ausente. Solicite uno nuevo en el kiosco.' }}
                    </p>
                </div>

                <div class="mt-6 rounded-xl bg-slate-50 p-4 text-sm text-slate-600">
                    <p class="font-semibold text-slate-800">Recomendaciones</p>
                    <ul class="mt-2 space-y-1.5">
                        <li>• Permanezca en la sala de espera y observe la pantalla.</li>
                        <li>• Escuchará su código y la ventanilla asignada.</li>
                        <li>• Si no se presenta al ser llamado, el turno pasará a ausente.</li>
                    </ul>
                </div>

                <a href="{{ route('kiosk.index') }}" class="mt-6 flex w-full items-center justify-center rounded-xl border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    Sacar otro turno
                </a>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const ticket = document.getElementById('ticket');
            if (!ticket) return;

            const statusUrl = ticket.dataset.statusUrl;
            const elements = {
                statusPill: document.getElementById('status-pill'),
                statusLabel: document.getElementById('status-label'),
                waitingPanel: document.getElementById('waiting-panel'),
                calledPanel: document.getElementById('called-panel'),
                finishedPanel: document.getElementById('finished-panel'),
                finishedMessage: document.getElementById('finished-message'),
                position: document.getElementById('position'),
                estimate: document.getElementById('estimate'),
                waitingAhead: document.getElementById('waiting-ahead'),
                counterName: document.getElementById('counter-name'),
                liveIndicator: document.getElementById('live-indicator'),
            };

            const statusStyles = {
                waiting: ['bg-amber-100', 'text-amber-800'],
                called: ['bg-emerald-100', 'text-emerald-800'],
                in_service: ['bg-emerald-100', 'text-emerald-800'],
                completed: ['bg-slate-200', 'text-slate-700'],
                no_show: ['bg-rose-100', 'text-rose-800'],
            };

            async function refresh() {
                try {
                    const response = await fetch(statusUrl, { headers: { Accept: 'application/json' } });
                    if (!response.ok) return;

                    const data = await response.json();

                    elements.statusLabel.textContent = data.status_label;
                    elements.statusPill.className = 'inline-flex items-center gap-2 rounded-full px-3 py-1 text-sm font-semibold ' + (statusStyles[data.status] ?? statusStyles.waiting).join(' ');

                    elements.waitingPanel.classList.toggle('hidden', !data.is_waiting);
                    elements.calledPanel.classList.toggle('hidden', !['called', 'in_service'].includes(data.status));
                    elements.finishedPanel.classList.toggle('hidden', !['completed', 'no_show'].includes(data.status));

                    if (data.is_waiting) {
                        elements.position.textContent = data.position;
                        elements.estimate.textContent = data.estimated_minutes;
                        elements.waitingAhead.textContent = data.waiting_ahead;
                    }

                    if (data.counter) {
                        elements.counterName.textContent = data.counter;
                    }

                    if (data.status === 'completed') {
                        elements.finishedMessage.textContent = 'Su atención ha finalizado. ¡Gracias por su visita!';
                    }

                    if (data.status === 'no_show') {
                        elements.finishedMessage.textContent = 'Su turno fue marcado como ausente. Solicite uno nuevo en el kiosco.';
                    }
                } catch (error) {
                    console.warn('No se pudo actualizar el turno.', error);
                }
            }

            if (window.Echo) {
                window.Echo.channel('queue')
                    .listen('.queue.updated', refresh)
                    .listen('.turn.called', refresh);

                window.Echo.connector.pusher.connection.bind('state_change', (states) => {
                    elements.liveIndicator.style.opacity = states.current === 'connected' ? '1' : '0.45';
                });
            }

            window.setInterval(refresh, 20000);
        });
    </script>
@endpush
