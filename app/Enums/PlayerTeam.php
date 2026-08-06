<?php

namespace App\Enums;

/**
 * Lado a que um jogador pertence dentro da prancheta.
 *
 * `home` e o time de quem monta a analise e `away` o adversario — a prancheta
 * nao guarda nome de clube, so o lado (docs/02 RF-010).
 */
enum PlayerTeam: string
{
    case Home = 'home';
    case Away = 'away';

    public function label(): string
    {
        return match ($this) {
            self::Home => __('Own team'),
            self::Away => __('Opponent'),
        };
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
