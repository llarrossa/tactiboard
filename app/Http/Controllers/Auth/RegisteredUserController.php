<?php

namespace App\Http\Controllers\Auth;

use App\Actions\RegisterUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterUserRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function __construct(
        private readonly RegisterUserAction $registerUserAction,
    ) {}

    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(RegisterUserRequest $request): RedirectResponse
    {
        $user = $this->registerUserAction->execute($request->validated());

        // Auth::login ja migra a sessao internamente (SessionGuard::updateSession),
        // entao nao ha regeneracao explicita aqui.
        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
