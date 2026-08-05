<?php

namespace App\Enums;

/**
 * Controla o estado da prancheta: pública ou privada (docs/03 §6.2).
 *
 * Não confundir com o acesso: quem abre a prancheta pela URL é o token em
 * shared_links, que entra na Fase 4. Uma prancheta private nega acesso mesmo
 * com token válido.
 */
enum BoardVisibility: string
{
    case Private = 'private';
    case Public = 'public';

    public function label(): string
    {
        return match ($this) {
            self::Private => __('Private'),
            self::Public => __('Public'),
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
