<?php

namespace CheshireCatSdk;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use CheshireCatSdk\Http\Controllers\CheshireCatController;

class CheshireCatServiceProvider extends ServiceProvider
{
    /**
     * Register bindings in the container.
     *
     * This method merges the config file and registers
     * a singleton instance of the CheshireCat class.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/cheshirecat.php', 'cheshirecat');
        $this->app->singleton('cheshirecat', function ($app) {
            return new CheshireCat();
        });
    }

    /**
     * Perform post-registration booting of services.
     *
     * This method publishes the package's configuration file
     * to the application's config directory for customization.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/cheshirecat.php' => config_path('cheshirecat.php'),
        ], 'config');

        // Define routes only when the application is ready to handle them.
        if ($this->app->routesAreCached() === false) {
            Route::middleware('web')->group(function () {

                Route::get('/meow/status', [CheshireCatController::class, 'meowStatus'])->name('cheshirecat.meow.status');

                Route::get('/meow/hello', [CheshireCatController::class, 'meowHello'])->name('cheshirecat.meow.hello');
            });
        }
    }
}
