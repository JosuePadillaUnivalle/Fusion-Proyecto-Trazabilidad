<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use App\Support\LocalDatabaseGuard;
use App\Support\UsuarioRol;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->extend(\Illuminate\Foundation\Console\ServeCommand::class, function ($command, $app) {
            return new \App\Console\Commands\ServeWithLanCommand;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        LocalDatabaseGuard::asegurar();

        App::setLocale('es');
        Paginator::useBootstrapFour();

        Gate::before(function ($user, $ability) {
            if (UsuarioRol::esAdminGlobal($user)) {
                return true;
            }

            return null;
        });

        Blade::directive('superficie', function (string $expression) {
            return "<?php echo \\App\\Support\\SuperficieFormato::etiqueta($expression); ?>";
        });
    }
}
