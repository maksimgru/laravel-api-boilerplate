<?php

namespace App\Providers;

use App\Exceptions\Handler\ApiExceptionHandler;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Telescope\TelescopeServiceProvider;
use Illuminate\Contracts\Debug\ExceptionHandler;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        \URL::forceScheme(config('api.scheme'));
        $this->customBladeDirective();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerExceptionHandler();
        $this->registerTelescope();
    }

    /**
     * Register the exception handler - extends the Dingo one
     *
     * @return void
     */
    protected function registerExceptionHandler(): void
    {
        $this->app->singleton('api.exception', function ($app) {
            return new ApiExceptionHandler($app[ExceptionHandler::class], config('api.errorFormat'), config('api.debug'));
        });
    }

    /**
     * Conditionally register the telescope service provider
     */
    protected function registerTelescope(): void
    {
        if (\App::environment(['local', 'testing', 'staging'])) {
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     *
     * @return void
     */
    protected function customBladeDirective(): void
    {
        Blade::directive('camelcase', function($expression){
            $expression = Str::camel($expression);

            return "<?php echo {$expression} ?>";
        });

        Blade::directive('snakecase', function($expression){
            $expression = Str::snake($expression);

            return "<?php echo {$expression} ?>";
        });
    }
}
