<?php

namespace App;

enum TurnStatus: string
{
    case Waiting = 'waiting';
    case Called = 'called';
    case InService = 'in_service';
    case Completed = 'completed';
    case NoShow = 'no_show';

    public function label(): string
    {
        return match ($this) {
            self::Waiting => 'En espera',
            self::Called => 'Llamado',
            self::InService => 'En atención',
            self::Completed => 'Atendido',
            self::NoShow => 'Ausente',
        };
    }

    public function isActive(): bool
    {
        return in_array($this, [self::Called, self::InService], true);
    }
}
