<?php namespace Kris\LaravelFormBuilder;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Html\FormBuilder as LaravelForm;
use Illuminate\Html\HtmlBuilder;
use Illuminate\Support\ServiceProvider;

class FormBuilderServiceProvider extends ServiceProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->commands('Kris\LaravelFormBuilder\Console\FormMakeCommand');

        $this->registerHtmlIfNeeded();
        $this->registerFormIfHeeded();

        $this->registerFormHelper();

        $this->app->bindShared('laravel-form-builder', function ($app) {

            return new FormBuilder($app, $app['laravel-form-helper']);
        });
    }

    protected function registerFormHelper()
    {
        $this->app->bindShared('laravel-form-helper', function ($app) {

            $configuration = $app['config']->get('laravel-form-builder::config');

            return new FormHelper($app['view'], $app['request'], $configuration);
        });

        $this->app->alias('laravel-form-helper', 'Kris\LaravelFormBuilder\FormHelper');
    }

    public function boot()
    {
        $this->package('kris/laravel-form-builder');
    }

    /**
     * @return string[]
     */
    public function provides()
    {
        return ['laravel-form-builder'];
    }

    /**
     * Add Laravel Form to container if not already set
     */
    private function registerFormIfHeeded()
    {
        if (!$this->app->offsetExists('form')) {

            $this->app->bindShared('form', function($app) {

                $form = new LaravelForm($app['html'], $app['url'], $app['session.store']->getToken());

                return $form->setSessionStore($app['session.store']);
            });

            AliasLoader::getInstance()->alias(
                'Form',
                'Illuminate\Html\FormFacade'
            );
        }
    }

    /**
     * Add Laravel Html to container if not already set
     */
    private function registerHtmlIfNeeded()
    {
        if (!$this->app->offsetExists('html')) {

            $this->app->bindShared('html', function($app) {
                return new HtmlBuilder($app['url']);
            });

            AliasLoader::getInstance()->alias(
                'Html',
                'Illuminate\Html\HtmlFacade'
            );
        }
    }
}
