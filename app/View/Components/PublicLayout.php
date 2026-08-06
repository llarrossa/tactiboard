<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Layout do visitante anonimo, usado pela visualizacao publica de uma
 * prancheta. Diferente do GuestLayout, que e o cartao estreito das telas de
 * autenticacao e nao comporta a largura do campo.
 */
class PublicLayout extends Component
{
    public function __construct(public ?string $title = null) {}

    public function render(): View
    {
        return view('layouts.public');
    }
}
