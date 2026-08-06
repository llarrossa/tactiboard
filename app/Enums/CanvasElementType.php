<?php

namespace App\Enums;

/**
 * Tipos de elemento que podem existir no canvas de uma prancheta.
 *
 * Persistidos em ingles dentro de canvas_data, como category e visibility
 * (docs/03 secao 6.1). Jogador do time e adversario compartilham o tipo
 * `player` e se distinguem pelo campo `team` — sao a mesma peca com cores
 * diferentes, nao dois conceitos.
 */
enum CanvasElementType: string
{
    case Player = 'player';
    case Ball = 'ball';
    case Cone = 'cone';
    case Text = 'text';
    case Arrow = 'arrow';

    public function label(): string
    {
        return match ($this) {
            self::Player => __('Player'),
            self::Ball => __('Ball'),
            self::Cone => __('Cone'),
            self::Text => __('Text'),
            self::Arrow => __('Arrow'),
        };
    }

    /**
     * Um elemento posicional ocupa um ponto no campo (x, y). A seta e a
     * excecao: ela e definida por dois pontos, `start` e `end`.
     */
    public function isPositional(): bool
    {
        return $this !== self::Arrow;
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
