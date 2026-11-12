<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pantalla de sala · Turnos Ciudadanos</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-[#071c2e] text-white antialiased">
    <div class="flex min-h-screen flex-col">
        <header class="border-b border-white/10 px-6 py-5 lg:px-10">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <span class="flex size-12 items-center justify-center rounded-2xl bg-emerald-400/15 text-emerald-300 ring-1 ring-emerald-400/40">
                        <svg class="size-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 8V6a2 2 0 0 1 2-2h1a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-2m0-4h4m8-8V6a2 2 0 0 1 2-2h1a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-2m0-4h4"/>
                        </svg>
                    </span>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-emerald-300">Atención al ciudadano</p>
                        <h1 class="text-2xl font-bold tracking-tight">Turnos Ciudadanos</h1>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <div class="text-right">
                        <p id="clock" class="text-3xl font-bold tabular-nums">--:--</p>
                        <p id="date" class="text-sm capitalize text-white/60">Cargando fecha…</p>
                    </div>
                    <button id="sound-toggle" type="button" class="rounded-xl border border-emerald-400/40 bg-emerald-400/10 px-4 py-2 text-sm font-semibold text-emerald-200 transition hover:bg-emerald-400/20">
                        Activar voz
                    </button>
                </div>
            </div>
        </header>

        <main class="grid flex-1 gap-6 p-6 lg:grid-cols-[1.65fr_1fr] lg:p-10">
            <section class="flex flex-col">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-sm font-semibold uppercase tracking-[0.25em] text-white/60">Ahora atendiendo</h2>
                    <span id="connection" class="inline-flex items-center gap-2 rounded-full bg-white/5 px-3 py-1 text-xs font-medium text-white/60">
                        <span id="connection-dot" class="size-2 rounded-full bg-amber-400"></span>
                        <span id="connection-label">Conectando</span>
                    </span>
                </div>

                <div id="called-list" class="grid flex-1 content-start gap-4 sm:grid-cols-2">
                    @forelse($calledTurns as $turn)
                        <article class="rounded-3xl border border-white/10 bg-white/5 p-6">
                            <div class="flex items-start justify-between gap-3">
                                <p class="text-6xl font-bold tracking-[0.08em] text-white">{{ $turn->code }}</p>
                                <span class="rounded-full bg-emerald-400 px-3 py-1 text-sm font-bold text-emerald-950">{{ $turn->counter?->name ?? '—' }}</span>
                            </div>
                            <p class="mt-3 text-lg font-medium text-white/80">{{ $turn->service->name }}</p>
                            <p class="mt-1 text-sm text-white/45">{{ $turn->called_at?->format('H:i') }} · {{ $turn->status->label() }}</p>
                        </article>
                    @empty
                        <div class="col-span-full flex min-h-64 items-center justify-center rounded-3xl border border-dashed border-white/15 text-center">
                            <div>
                                <p class="text-xl font-semibold text-white/70">Esperando el primer llamado</p>
                                <p class="mt-1 text-sm text-white/40">Los turnos llamados aparecerán aquí en tiempo real.</p>
                            </div>
                        </div>
                    @endforelse
                </div>

                <p class="mt-5 text-sm text-white/40">Escuche su código y diríjase a la ventanilla indicada. Los turnos llamados que no se presenten pasarán a ausentes.</p>
            </section>

            <aside class="rounded-3xl border border-white/10 bg-white/5 p-6">
                <div class="flex items-baseline justify-between">
                    <h2 class="text-sm font-semibold uppercase tracking-[0.25em] text-white/60">En espera</h2>
                    <p class="text-xs text-white/40"><span id="waiting-total">{{ $services->sum('waiting_count') }}</span> personas</p>
                </div>

                <div id="service-list" class="mt-5 space-y-3">
                    @foreach($services as $service)
                        <div class="flex items-center gap-4 rounded-2xl bg-white/5 px-4 py-4" data-service="{{ $service->id }}">
                            <span class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-emerald-400/15 text-lg font-bold text-emerald-300">{{ $service->prefix }}</span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate font-medium text-white/85">{{ $service->name }}</p>
                                <p class="text-xs text-white/40">≈ {{ $service->waiting_count * $service->average_minutes }} min de espera</p>
                            </div>
                            <span class="min-w-10 rounded-xl bg-white/10 py-2 text-center text-xl font-bold tabular-nums" data-waiting>{{ $service->waiting_count }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6 rounded-2xl border border-emerald-400/20 bg-emerald-400/5 p-4 text-sm text-emerald-100/80">
                    Mantenga su comprobante a la mano. Si su turno es llamado y no se presenta, deberá solicitar uno nuevo.
                </div>
            </aside>
        </main>
    </div>

    <script>
            document.addEventListener('DOMContentLoaded', () => {
                const stateUrl = @json(route('display.state'));
                const clock = document.getElementById('clock');
                const date = document.getElementById('date');
                const calledList = document.getElementById('called-list');
                const serviceList = document.getElementById('service-list');
                const waitingTotal = document.getElementById('waiting-total');
                const connectionDot = document.getElementById('connection-dot');
                const connectionLabel = document.getElementById('connection-label');
                const soundToggle = document.getElementById('sound-toggle');

                let soundEnabled = false;
                let lastAnnounced = null;

                function updateClock() {
                    const now = new Date();
                    clock.textContent = new Intl.DateTimeFormat('es-PE', { hour: '2-digit', minute: '2-digit', hour12: false }).format(now);
                    date.textContent = new Intl.DateTimeFormat('es-PE', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }).format(now);
                }

                function card(item) {
                    const article = document.createElement('article');
                    article.className = 'rounded-3xl border border-white/10 bg-white/5 p-6';

                    const header = document.createElement('div');
                    header.className = 'flex items-start justify-between gap-3';

                    const code = document.createElement('p');
                    code.className = 'text-6xl font-bold tracking-[0.08em] text-white';
                    code.textContent = item.code;

                    const counter = document.createElement('span');
                    counter.className = 'rounded-full bg-emerald-400 px-3 py-1 text-sm font-bold text-emerald-950';
                    counter.textContent = item.counter || '—';

                    header.append(code, counter);

                    const service = document.createElement('p');
                    service.className = 'mt-3 text-lg font-medium text-white/80';
                    service.textContent = item.service;

                    const time = document.createElement('p');
                    time.className = 'mt-1 text-sm text-white/45';
                    time.textContent = item.called_at ? new Date(item.called_at).toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit' }) : '';

                    article.append(header, service, time);

                    return article;
                }

                function renderCalled(items) {
                    calledList.replaceChildren();

                    if (!items.length) {
                        const empty = document.createElement('div');
                        empty.className = 'col-span-full flex min-h-64 items-center justify-center rounded-3xl border border-dashed border-white/15 text-center';
                        empty.innerHTML = '<div><p class="text-xl font-semibold text-white/70">Esperando el primer llamado</p><p class="mt-1 text-sm text-white/40">Los turnos llamados aparecerán aquí en tiempo real.</p></div>';
                        calledList.append(empty);
                        return;
                    }

                    items.forEach((item) => calledList.append(card(item)));
                }

                function renderWaiting(services, total) {
                    (services || []).forEach((service) => {
                        const row = serviceList.querySelector(`[data-service="${service.id}"]`);
                        if (row) {
                            const counter = row.querySelector('[data-waiting]');
                            if (counter) counter.textContent = service.waiting;
                        }
                    });

                    if (typeof total === 'number') {
                        waitingTotal.textContent = total;
                    }
                }

                function announce(item) {
                    if (!soundEnabled || !('speechSynthesis' in window) || lastAnnounced === item.code) return;
                    lastAnnounced = item.code;

                    window.speechSynthesis.cancel();
                    const text = `Turno ${item.code}. ${item.counter ? 'Diríjase a ' + item.counter + '.' : 'Preséntese en ventanilla.'}`;
                    const utterance = new SpeechSynthesisUtterance(text);
                    utterance.lang = 'es-PE';
                    utterance.rate = 0.92;
                    window.speechSynthesis.speak(utterance);
                }

                async function refresh(speak = false) {
                    try {
                        const response = await fetch(stateUrl, { headers: { Accept: 'application/json' } });
                        if (!response.ok) return;
                        const data = await response.json();
                        renderCalled(data.called);
                        renderWaiting(data.services, data.waiting_total);

                        if (speak && data.called.length) {
                            announce(data.called[0]);
                        }
                    } catch (error) {
                        console.warn('No se pudo actualizar la pantalla.', error);
                    }
                }

                soundToggle.addEventListener('click', () => {
                    soundEnabled = true;
                    soundToggle.textContent = 'Voz activa';
                    soundToggle.classList.add('bg-emerald-400/25');

                    if ('speechSynthesis' in window) {
                        const utterance = new SpeechSynthesisUtterance('Avisos de voz activados.');
                        utterance.lang = 'es-PE';
                        window.speechSynthesis.speak(utterance);
                    }
                });

                if (window.Echo) {
                    window.Echo.channel('queue')
                        .listen('.turn.called', (event) => {
                            announce(event);
                            refresh(false);
                        })
                        .listen('.queue.updated', (event) => {
                            renderWaiting(event.services, event.waiting_total);
                        });

                    window.Echo.connector.pusher.connection.bind('state_change', (states) => {
                        const connected = states.current === 'connected';
                        connectionDot.className = 'size-2 rounded-full ' + (connected ? 'bg-emerald-400' : 'bg-rose-400');
                        connectionLabel.textContent = connected ? 'En vivo' : 'Reconectando';
                    });
                }

                updateClock();
                window.setInterval(updateClock, 1000);
                window.setInterval(() => refresh(false), 25000);
            });
    </script>
</body>
</html>
