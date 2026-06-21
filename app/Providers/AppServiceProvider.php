<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Blade;
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
        App::setLocale('es');
        Paginator::useBootstrapFour();

        Blade::directive('superficie', function (string $expression) {
            return "<?php echo \\App\\Support\\SuperficieFormato::etiqueta($expression); ?>";
        });
    }
}
