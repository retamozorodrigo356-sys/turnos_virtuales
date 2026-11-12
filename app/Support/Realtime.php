<?php

namespace App\Support;

use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Support\Facades\Log;
use Throwable;

class Realtime
{
    /**
     * Emitir un evento de broadcasting sin interrumpir la operación principal.
     *
     * Si el servidor de WebSockets (Reverb/Pusher) no está disponible, la
     * petición debe completarse igualmente: la interfaz sigue refrescándose
     * por sondeo (polling) y el evento solo se pierde para los clientes
     * conectados en ese instante.
     */
    public static function broadcast(ShouldBroadcast $event): void
    {
        try {
            event($event);
        } catch (Throwable $exception) {
            Log::warning('No se pudo emitir el evento en tiempo real.', [
                'event' => $event::class,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
