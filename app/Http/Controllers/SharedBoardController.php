<?php

namespace App\Http\Controllers;

use App\Models\SharedLink;
use App\Rules\CanvasRules;
use Illuminate\View\View;

/**
 * Visualizacao publica de uma prancheta (RF-015).
 *
 * Esta e a unica fronteira anonima do produto: nao ha sessao, nao ha Policy e
 * o acesso e sempre somente leitura.
 */
class SharedBoardController extends Controller
{
    public function show(string $token): View
    {
        // As tres condicoes de docs/03 §7.2 vivem no scope. Qualquer uma que
        // falhe faz o link nao ser encontrado, e a resposta e 404 — nao 403,
        // como na area autenticada. Distinguir "token inexistente" de "token
        // valido, prancheta privada" confirmaria a existencia de um segredo.
        $link = SharedLink::query()
            ->where('token', $token)
            ->accessible()
            ->firstOrFail();

        $board = $link->board;

        // A prancheta e carregada em uma segunda consulta, entao ela pode ter
        // sido excluida entre uma e outra. O cascade removeria o link junto,
        // mas esta requisicao ja o tem em memoria — sem esta guarda o visitante
        // receberia 500 em vez de 404.
        abort_if($board === null, 404);

        return view('share.show', [
            'board' => $board,
            // O canvas gravado sempre passou pela validacao, mas um registro
            // editado a mao derrubaria a pagina: element.blade.php resolve o
            // tipo com CanvasElementType::from(), que lanca excecao. O filtro
            // ja existe e evita servir 500 a um visitante.
            'elements' => CanvasRules::drawable($board->canvas_data['elements'] ?? []),
        ]);
    }
}
