@extends('layouts.public')

@section('title', 'Saque su turno · Turnos Ciudadanos')

@section('content')
    <div class="relative overflow-hidden">
        <div class="absolute inset-0 -z-10 bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-emerald-100 via-slate-100 to-slate-100"></div>

        <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:py-14">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.25em] text-emerald-700">Atención al ciudadano</p>
                    <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">¿Qué trámite necesita hoy?</h1>
                    <p class="mt-2 max-w-2xl text-slate-600">Elija el servicio y obtenga su turno virtual. Podrá seguir su posición en tiempo real desde su comprobante.</p>
                </div>
                <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                    <span class="relative flex size-3">
                        <span class="absolute inline-flex size-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex size-3 rounded-full bg-emerald-500"></span>
                    </span>
                    <div>
                        <p class="text-xs uppercase tracking-wider text-slate-500">Cola en vivo</p>
                        <p class="text-sm font-semibold text-slate-800">Actualizada por WebSocket</p>
                    </div>
                </div>
            </div>

            @if($services->isEmpty())
                <div class="mt-10 rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center">
                    <p class="text-lg font-semibold text-slate-800">No hay trámites habilitados</p>
                    <p class="mt-1 text-slate-600">Por favor, acérquese al módulo de orientación.</p>
                </div>
            @else
                <div class="mt-8 grid gap-5 md:grid-cols-2">
                    @foreach($services as $service)
                        @php($estimated = $service->waiting_count * $service->average_minutes)
                        <article class="group flex flex-col rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:border-emerald-300 hover:shadow-md">
                            <div class="flex items-start gap-4">
                                <span class="flex size-14 shrink-0 items-center justify-center rounded-2xl bg-[#0b2b45] text-2xl font-bold text-emerald-300">
                                    {{ $service->prefix }}
                                </span>
                                <div class="min-w-0">
                                    <h2 class="text-xl font-semibold text-slate-900">{{ $service->name }}</h2>
                                    <p class="mt-1 text-sm leading-relaxed text-slate-600">{{ $service->description ?: 'Atención presencial en ventanilla.' }}</p>
                                </div>
                            </div>

                            <div class="mt-5 flex flex-wrap items-center gap-2 text-sm">
                                @if($service->waiting_count > 0)
                                    <span class="rounded-full bg-amber-100 px-3 py-1 font-medium text-amber-800">{{ $service->waiting_count }} {{ $service->waiting_count === 1 ? 'persona' : 'personas' }} en espera</span>
                                    <span class="rounded-full bg-slate-100 px-3 py-1 font-medium text-slate-700">≈ {{ $estimated }} min</span>
                                @else
                                    <span class="rounded-full bg-emerald-100 px-3 py-1 font-medium text-emerald-800">Atención inmediata</span>
                                @endif
                            </div>

                            <form method="POST" action="{{ route('turns.store') }}" class="mt-6">
                                @csrf
                                <input type="hidden" name="service_id" value="{{ $service->id }}">
                                <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-3 text-base font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2">
                                    Sacar mi turno
                                    <svg class="size-5 transition group-hover:translate-x-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                                    </svg>
                                </button>
                            </form>
                        </article>
                    @endforeach
                </div>
            @endif

            <div class="mt-8 flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white/70 p-5 text-sm text-slate-600 sm:flex-row sm:items-center sm:justify-between">
                <p class="flex items-center gap-2">
                    <svg class="size-5 shrink-0 text-emerald-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                    </svg>
                    Su turno es personal. Guarde el código y permanezca atento a la pantalla de sala.
                </p>
                <a href="{{ route('display') }}" class="font-semibold text-emerald-700 hover:text-emerald-800">Ver pantalla de sala →</a>
            </div>
        </div>
    </div>
@endsection
