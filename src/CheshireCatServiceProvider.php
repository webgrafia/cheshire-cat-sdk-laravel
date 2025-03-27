<?php
namespace CheshireCatSdk;
use Illuminate\Support\ServiceProvider;
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
        $this->app->singleton('cheshirecat', function () {
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

        $this->loadRoutesFrom(__DIR__ . '/routes/cheshirecat.php');
    }
}
