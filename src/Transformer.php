<?php

namespace DavidIanBonner\Presenter;

use ArrayAccess;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;

class Transformer implements ArrayAccess, Arrayable, Jsonable
{
    /** @var DavidIanBonner\Presenter\Presentable */
    protected $object;

    /** Resolve requirements from extending class. */
    public function __construct()
    {
    }

    /**
     * Boot the presenter.
     *
     * @return void
     */
    protected function bootTransformer(Presentable $object)
    {
    }

    /**
     * Set the presentable object.
     *
     * @param DavidIanBonner\Presenter\Presentable $object
     * @return DavidIanBonner\Presenter\Transformer
     */
    public function setPresentableObject(Presentable $object) : self
    {
        $this->object = clone $object;
        $this->bootTransformer($object);

        return $this;
    }

    /**
     * Get an item from the presentable object by key.
     *
     * @param  mixed  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if ($this->methodExists($key)) {
            return $this->getMethod($key);
        }

        if ($this->exists($key)) {
            return value($this->object->{$key});
        }

        return value($default);
    }

    /**
     * Does the property/key exist.
     *
     * @param  string $key
     * @return bool
     */
    public function exists($key) : bool
    {
        return isset($this->object->{$key});
    }

    /**
     * Does a method exist?
     *
     * @param  string $name
     * @return bool
     */
    public function methodExists($name) : bool
    {
        return method_exists($this, camel_case($name));
    }

    /**
     * Call a method.
     *
     * @param  string $name
     * @return mixed
     */
    public function getMethod($name)
    {
        return $this->{camel_case($name)}();
    }

    /**
     * Dynamically get a property from the transformer as a method or true property.
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * Overload isset.
     *
     * @param string $key
     * @return bool
     */
    public function __isset($key)
    {
        return $this->offsetExists($key);
    }

    /**
     * Determine if an offset exists.
     *
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset) : bool
    {
        return $this->exists($offset);
    }

    /**
     * Get an offset.
     *
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Set an offset.
     *
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        throw new \Exception('Setting object properties disabled.');
    }

    /**
     * Unset an offset.
     *
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        throw new \Exception('Cannot unset presentable properties.');
    }

    /**
     * Return an array representation of the transformer.
     *
     * @return array
     */
    public function toArray(): array
    {
        // We can only return an array if the presentable object is Arrayable.
        // If so, we grab the array, collect the keys and then loop over them
        // to create a new array based on the transformer.

        $array = [];

        if ($this->object instanceof Arrayable) {
            $array = $this->object->toArray();
        }

        return collect($array)
            ->keys()
            ->mapWithKeys(function ($key) {
                return [$key => $this->{$key}];
            })->toArray();
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Cast the transformer to a string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * Return a new instance of the presenter.
     *
     * @param  DavidIanBonner\Presenter\Presentable $object
     * @return DavidIanBonner\Presenter\Transformer
     */
    public static function make(Presentable $object) : self
    {
        return (new static)->setPresentableObject($object);
    }
}
