@extends('layouts.app')

@section('title', 'Administración')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Administración del sistema</h1>
        <p class="mt-1 text-sm text-slate-600">Configure los trámites, las ventanillas y supervise la operación del día.</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Turnos emitidos hoy</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">{{ $stats['today'] }}</p>
        </div>
        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-amber-700">En espera</p>
            <p class="mt-2 text-3xl font-bold text-amber-900">{{ $stats['waiting'] }}</p>
        </div>
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-emerald-700">Atendidos</p>
            <p class="mt-2 text-3xl font-bold text-emerald-900">{{ $stats['completed'] }}</p>
        </div>
        <div class="rounded-2xl border border-rose-200 bg-rose-50 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-rose-700">Ausentes</p>
            <p class="mt-2 text-3xl font-bold text-rose-900">{{ $stats['no_show'] }}</p>
        </div>
    </div>

    <div class="mt-8 grid gap-6 xl:grid-cols-2">
        <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <div>
                    <h2 class="font-semibold text-slate-900">Trámites y servicios</h2>
                    <p class="text-sm text-slate-500">Definen las colas disponibles en el kiosco.</p>
                </div>
                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">{{ $services->count() }}</span>
            </div>

            <form method="POST" action="{{ route('admin.services.store') }}" class="grid gap-3 border-b border-slate-100 bg-slate-50/60 p-6 sm:grid-cols-2">
                @csrf
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500" for="new-service-name">Nuevo trámite</label>
                    <input id="new-service-name" name="name" value="{{ old('name') }}" required maxlength="120" placeholder="Ej. Registro civil" class="mt-1.5 w-full rounded-xl border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500" for="new-service-prefix">Prefijo</label>
                    <input id="new-service-prefix" name="prefix" value="{{ old('prefix') }}" required maxlength="3" placeholder="E" class="mt-1.5 w-full rounded-xl border-slate-300 bg-white px-3 py-2 text-sm uppercase shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500" for="new-service-minutes">Minutos promedio</label>
                    <input id="new-service-minutes" type="number" name="average_minutes" value="{{ old('average_minutes', 5) }}" required min="1" max="120" class="mt-1.5 w-full rounded-xl border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500" for="new-service-description">Descripción</label>
                    <input id="new-service-description" name="description" value="{{ old('description') }}" maxlength="255" placeholder="Breve descripción para el ciudadano" class="mt-1.5 w-full rounded-xl border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                </div>
                <div class="sm:col-span-2">
                    <button type="submit" class="w-full rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700">Agregar trámite</button>
                </div>
            </form>

            <div class="divide-y divide-slate-100">
                @forelse($services as $service)
                    <form method="POST" action="{{ route('admin.services.update', $service) }}" class="p-6">
                        @csrf
                        @method('PATCH')
                        <div class="flex items-center justify-between gap-3">
                            <span class="flex size-9 items-center justify-center rounded-xl bg-slate-900 text-sm font-bold text-emerald-300">{{ $service->prefix }}</span>
                            <label class="inline-flex cursor-pointer items-center gap-2 text-xs font-medium text-slate-600">
                                <input type="checkbox" name="is_active" value="1" @checked($service->is_active) class="size-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                Activo
                            </label>
                        </div>

                        <div class="mt-3 grid gap-2 sm:grid-cols-[1fr_5rem_7rem]">
                            <input name="name" value="{{ $service->name }}" required maxlength="120" aria-label="Nombre del trámite" class="w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <input name="prefix" value="{{ $service->prefix }}" required maxlength="3" aria-label="Prefijo" class="w-full rounded-lg border-slate-300 px-3 py-2 text-sm uppercase shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <input type="number" name="average_minutes" value="{{ $service->average_minutes }}" required min="1" max="120" aria-label="Minutos promedio" class="w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                        </div>

                        <input name="description" value="{{ $service->description }}" maxlength="255" aria-label="Descripción" class="mt-2 w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">

                        <div class="mt-3 flex items-center justify-between">
                            <p class="text-xs text-slate-500">{{ $service->waiting_count }} en espera · {{ $service->average_minutes }} min promedio</p>
                            <button type="submit" class="rounded-lg border border-slate-300 px-4 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">Guardar</button>
                        </div>
                    </form>
                @empty
                    <p class="px-6 py-8 text-center text-sm text-slate-500">Aún no hay trámites registrados.</p>
                @endforelse
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <div>
                    <h2 class="font-semibold text-slate-900">Ventanillas</h2>
                    <p class="text-sm text-slate-500">Puntos de atención disponibles para los agentes.</p>
                </div>
                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">{{ $counters->count() }}</span>
            </div>

            <form method="POST" action="{{ route('admin.counters.store') }}" class="flex flex-col gap-3 border-b border-slate-100 bg-slate-50/60 p-6 sm:flex-row sm:items-end">
                @csrf
                <div class="flex-1">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500" for="new-counter-name">Nueva ventanilla</label>
                    <input id="new-counter-name" name="name" required maxlength="60" placeholder="Ej. Ventanilla 4" class="mt-1.5 w-full rounded-xl border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                </div>
                <button type="submit" class="rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700">Agregar</button>
            </form>

            <div class="divide-y divide-slate-100">
                @forelse($counters as $counter)
                    <form method="POST" action="{{ route('admin.counters.update', $counter) }}" class="flex flex-wrap items-center gap-3 p-6">
                        @csrf
                        @method('PATCH')
                        <div class="min-w-0 flex-1">
                            <input name="name" value="{{ $counter->name }}" required maxlength="60" aria-label="Nombre de la ventanilla" class="w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <p class="mt-1 text-xs text-slate-500">{{ $counter->active_turns_count }} atenciones activas hoy</p>
                        </div>
                        <label class="inline-flex cursor-pointer items-center gap-2 text-xs font-medium text-slate-600">
                            <input type="checkbox" name="is_active" value="1" @checked($counter->is_active) class="size-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            Activa
                        </label>
                        <button type="submit" class="rounded-lg border border-slate-300 px-4 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">Guardar</button>
                    </form>
                @empty
                    <p class="px-6 py-8 text-center text-sm text-slate-500">Aún no hay ventanillas registradas.</p>
                @endforelse
            </div>
        </section>
    </div>
@endsection
