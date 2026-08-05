<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * RF-004 e RF-006: o dashboard lista as pranchetas do próprio usuário.
     *
     * A consulta parte do relacionamento do usuário autenticado, então uma
     * prancheta de outra pessoa não tem como aparecer aqui.
     */
    public function index(Request $request): View
    {
        return view('dashboard', [
            'boards' => $request->user()
                ->boards()
                ->latest()
                ->paginate(12),
        ]);
    }
}
