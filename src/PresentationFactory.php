<?php

namespace DavidIanBonner\Presenter;

use Traversable;
use Illuminate\Support\Collection;
use DavidIanBonner\Presenter\Presentable;
use DavidIanBonner\Presenter\Transformer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Contracts\Container\Container;

class PresentationFactory
{
    /** @var Illuminate\Contracts\Container\Container */
    protected $container;

    /** @var array */
    protected $transformers = [];

    /**
     * @param Illuminate\Contracts\Container\Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Push transformers to the presenter.
     *
     * @param  array $transformers
     * @return $this
     */
    public function pushTransformers(array $transformers = [])
    {
        $this->transformers = array_merge(
            $this->transformers, array_wrap($transformers)
        );

        return $this;
    }

    /**
     * Is the item in question an instance of the presentable interface.
     *
     * @param  mixed $item
     * @return boolean
     */
    public function isPresentable($item) : bool
    {
        return $item instanceof Presentable;
    }

    /**
     * Is there a registered presenter for the item.
     *
     * @param  mixed $item
     * @return boolean
     */
    public function shouldTransform($class) : bool
    {
        return array_key_exists($class, $this->transformers);
    }

    /**
     * Get the transformer.
     *
     * @param  string $class
     * @return string
     */
    public function getTransformer($class, $override = null) : string
    {
        return $override ?: $this->transformers[$class];
    }

    /**
     * Get all the transformers.
     *
     * @return array
     */
    public function allTransformers() : array
    {
        return $this->transformers;
    }

    /**
     * Is the item iterable?
     *
     * @param  mixed  $item
     * @return boolean
     */
    protected function isIterable($item) : bool
    {
        return is_array($item)
            || $item instanceof Collection
            || $item instanceof AbstractPaginator;
    }

    /**
     * Decorate the item if if is presentable.
     *
     * @param  mixed  $item
     * @return mixed
     */
    public function transform($item, string $transformerOverride = null)
    {
        if ($this->isIterable($item)) {
            return $this->iterateTransformableArray($item);
        }

        if ($this->isPresentable($item)) {
            $presentable = get_class($item);

            if ($this->shouldTransform($presentable) || $transformerOverride) {
                $item = $this->transformPresentable(
                    $item, $this->getTransformer($presentable, $transformerOverride)
                );
            }
        }

        return $item;
    }

    /**
     * Transform the presentable object.
     *
     * @param  DavidIanBonner\Presenter\Presentable  $presentable
     * @param  string                                $transformer
     * @return DavidIanBonner\Presenter\Transformer
     */
    protected function transformPresentable(Presentable $presentable, string $transformer) : Transformer
    {
        $object = clone $presentable;

        if ($object instanceof Model) {
            $object = $this->transformModelRelationships($object);
        }

        return $this->container
                    ->make($transformer)
                    ->setPresentableObject($object);
    }

    /**
     * Transform any loaded model relationships.
     *
     * @param  Illuminate\Database\Eloquent\Model  $model
     */
    protected function transformModelRelationships(Model $model)
    {
        collect($model->getRelations())->each(function ($relation, $key) use ($model) {
            $model->setRelation($key, $this->transform($relation));
        });

        return $model;
    }

    /**
     * Decorate an array.
     *
     * @param  mixed $item
     * @return mixed
     */
    protected function iterateTransformableArray($item)
    {
        foreach ($item as $key => $value) {
            $item[$key] = $this->transform($value);
        }

        return $item;
    }
}
