# Presenter/Transformer

[![Build Status](https://travis-ci.org/davidianbonner/presenter.svg?branch=analysis-z4EgKn)](https://travis-ci.org/davidianbonner/presenter)
[![StyleCI](https://styleci.io/repos/107682784/shield?branch=master)](https://styleci.io/repos/107682784)

A simple data presenter/transformer for Laravel applications that can be used with views and JSON.

In most cases, the data transformation required to output JSON via a REST API is the same as the data required in our views. This package prevents having to use transformers for JSON (i.e. with Fractal – great for larger apps and complex transformations) and attribute mutators/a separate presentation layer when returning a view. Whether returning a view or JSON, the data can be presented and transformed using the one class.

## Installation

Install the package through Composer:

```bash
composer require davidianbonner/presenter
```

Laravel 5.5 uses Package Auto-Discovery, so doesn't require you to manually add the ServiceProvider. However if you are using 5.4, when composer has completed the install, add the package service provider in the `providers` array in `config/app.php`:

```php
DavidIanBonner\Presenter\PresenterServiceProvider::class
```

Add the facade to your `aliases` in `config/app.php:

```php
'Presenter' => DavidIanBonner\Presenter\Facades\Presenter::class,
```

Then publish the config file:

```
php artisan vendor:publish --provider="DavidIanBonner\Presenter\PresenterServiceProvider"
```

## How it works

A 'transformation' will only take place if the object implements the `Presentable` interface.

If the presentable object is an eloquent model, the presenter will check for loaded relationships and attempt to transform them as well, replacing the existing relationship with the transformer object.

#### Present/transform an object

When transformed, the object will injected into a transformer class which will give you access to magic methods, magic getters on the object and automatic `toArray` and `toJson` output.

```php
// Transform an object
Presenter::transform(Book::find(1), BookTransformer::class);

// or with pre-set transformers
Presenter::transform(Book::find(1));
```

#### Pre-set transformers

Set your presentable => transformer relations in `config/presenter.php`:

```php
'transformers' => [
    App\Models\Book::class => App\Transformers\BookTransformer::class,
    App\OtherObject\Foo::class => App\Transformers\FooTransformer::class,
],
```

#### Transformers

Transformers must extend `DavidIanBonner\Presenter\Transformer`. A transformer can utilise mutated attribute methods in a similar manner to eloquent.

```php
<?php

namespace App\Transformers\BookTransformer;

use DavidIanBonner\Presenter\Transformer;
use DavidIanBonner\Presenter\Presentable;

class BookTransformer extends Transformer
{
    // Optional
    protected function bootTransformer(Presentable $object) { }

    public function foo()
    {
        // Given $this->object->foo = 'bar'
        // Calling $bookTransformer->foo will return "bar_mutated"
        return $this->object->foo.'_mutated';
    }

    // Extend toArray if required to return a specified dataset
    public function toArray() { }

    // Extend toJson if required to return a different dataset from toArray
    public function toJson() { }
}
```

#### Response macros

To simplify your code and prevent having to repeat `Present::transform()` for all objects, there are two handy macros available that will handle this automatically.

##### Views

```php
use Illuminate\Support\Facades\Response;
...
public function index() {
    // Using the settings above, book will be available in the view
    // as an instance of App\Transformers\BookTransformer

    $data = ['book' => Book::find(1)];

    return Response::present('view-name', $data, $statusCode, $headers);
}
```

##### JSON

JSON transformation is handled a little differently from views. `Response::json()` checks if an object implements `Illuminate\Contracts\Support\Jsonable`, if it does, it calls `toJson` on that object. The `toJson` method of the base transformer will call `toArray` and `json_encode` it. `toArray` will attempt to collect the keys from the Presentable object passed to it and attempt to get each of these keys on the transformer which will in turn call any magic methods etc.

```php
use Illuminate\Http\JsonResponse;
...
public function index() {
    // Using the settings above, book will be transformed and
    // built from App\Transformers\BookTransformer

    $data = ['book' => Book::find(1)];

    return JsonResponse::present($data, $statusCode, $headers, $options);
}
```

#### Collection macro

A collection macro is available to loop over an arrayable set of items and transform any presentable objects.

```php
// Get a collection of BookTransformer objects
Collection::present(Book::get());
```

## Contributing

Contributions are welcome! Please read [CONTRIBUTING](https://github.com/davidianbonner/presenter/blob/master/CONTRIBUTING.md) for details.


## Copyright and license

The davidianbonner/presenter library is copyright © David Bonner and licensed for use under the MIT License (MIT). Please see [LICENSE](https://github.com/davidianbonner/presenter/blob/master/LICENSE) for more information.
