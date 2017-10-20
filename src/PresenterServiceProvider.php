<?php

namespace DBonner\Presenter;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Response;
use Illuminate\Contracts\Container\Container;

class PresenterServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/presenter.php' => config_path('presenter.php'),
        ]);

        // Handler for view building
        // Ignore these view variables: 'app', '__env', 'errors'
        // $this->setComposer($this->app);

        // Set listeners on views
        // $this->setListener($this->app);

        $this->loadCollectionMacros($this->app);
        $this->loadResponseMacros($this->app);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerPresenter($this->app);
    }

    /**
     * Register the presenter instance.
     *
     * @param  Illuminate\Contracts\Container\Container $app
     * @return void
     */
    protected function registerPresenter(Container $app)
    {
        $app->singleton('dbonner.presenter', function (Container $app) {
            return with(new Presenter($app))->pushTransformers(
                $app['config']->get('presenter.transformers', [])
            );
        });

        $app->bind(Presenter::class, function ($app) {
            return $app['dbonner.presenter'];
        });
    }

    /**
     * Load collection macros.
     *
     * @param  Illuminate\Contracts\Container\Container $app
     * @return void
     */
    protected function loadCollectionMacros(Container $app)
    {
        Collection::macro('present', function ($data) {
            return Collection::make($data)->mapWithKeys(function ($value, $key) {
                return [$key => app('dbonner.presenter')->transform($value)];
            });
        });
    }

    /**
     * Load response macros.
     *
     * @param  Illuminate\Contracts\Container\Container $app
     * @return void
     */
    protected function loadResponseMacros(Container $app)
    {
        Response::macro('present', function ($view, array $data = [], $status = 200, array $headers = []) {
            return Response::view($view, Collection::present($data)->all(), $status, $headers);
        });

        JsonResponse::macro('present', function (array $data = [], $status = 200, array $headers = [], $options = 0) {
            return new JsonResponse(Collection::present($data), $status, $headers, $options);
        });
    }
}