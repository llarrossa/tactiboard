<?php

namespace App\Enums;

/**
 * Persistido em inglês, exibido em português (docs/03 §6.3).
 *
 * Não existe tabela de categorias: são poucas, estáveis e a flexibilidade de
 * mudar a lista sem migration vale mais no MVP.
 */
enum BoardCategory: string
{
    case Attack = 'attack';
    case Defense = 'defense';
    case SetPiece = 'set_piece';
    case Training = 'training';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Attack => __('Attack'),
            self::Defense => __('Defense'),
            self::SetPiece => __('Set piece'),
            self::Training => __('Training'),
            self::Other => __('Other'),
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
