<?php

namespace AbdallaMohammed\Form\Providers;

use AbdallaMohammed\Form\Form;
use Illuminate\Support\ServiceProvider;

class FormServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Form::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $dist = __DIR__.'/../../config/laravel-multistep-forms.php';

        // If we're installing in to a Lumen project, config_path
        // won't exist so we can't auto-publish the config
        if (function_exists('config_path')) {
            // Publishes config File.
            $this->publishes([
                $dist => config_path('laravel-multistep-forms.php'),
            ]);
        }

        // Merge config.
        $this->mergeConfigFrom($dist, 'laravel-multistep-forms');
    }
}
