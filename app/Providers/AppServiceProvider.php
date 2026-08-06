<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Carregar um relacionamento dentro de uma view e o caminho classico
        // para o N+1: a pagina funciona, so fica lenta conforme a lista cresce.
        // Fora de producao o descuido vira excecao na hora, e a suite acusa.
        // Em producao a consulta extra e melhor do que uma tela de erro.
        Model::preventLazyLoading(! $this->app->isProduction());
    }
}
