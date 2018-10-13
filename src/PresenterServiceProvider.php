<?php

namespace DavidIanBonner\Presenter;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Response;
use Illuminate\Pagination\AbstractPaginator;
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
            __DIR__.'/../config/presenter.php' => config_path('presenter.php'),
        ]);

        require_once __DIR__ . '/helpers.php';

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
        $app->singleton('davidianbonner.presenter', function (Container $app) {
            return with(new PresentationFactory($app))->pushTransformers(
                $app['config']->get('presenter.transformers', [])
            );
        });

        $app->bind(PresentationFactory::class, function ($app) {
            return $app['davidianbonner.presenter'];
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
            if ($data instanceof AbstractPaginator) {
                $data->setCollection(
                    collect(app('davidianbonner.presenter')->transform($data->items()))
                );
            }

            return Collection::make($data)->mapWithKeys(function ($value, $key) {
                return [$key => app('davidianbonner.presenter')->transform($value)];
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
        Response::macro('present', function ($view, $data = [], $status = 200, array $headers = []) {
            $data = ($data instanceof Presentable)
                ? app('davidianbonner.presenter')->transform($data)
                : Collection::present($data)->all();

            return Response::view($view, $data, $status, $headers);
        });

        JsonResponse::macro('present', function ($data = [], $status = 200, array $headers = [], $options = 0) {
            $data = ($data instanceof Presentable)
                ? app('davidianbonner.presenter')->transform($data)
                : Collection::present($data);

            return new JsonResponse($data, $status, $headers, $options);
        });
    }
}
