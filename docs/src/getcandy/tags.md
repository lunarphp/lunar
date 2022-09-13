# Tags

[[toc]]

## Overview

Tags serve a simple function in GetCandy, you can add tags to models. This is useful for relating otherwise unrelated models in the system. They will also impact other parts of the system such as Dynamic collections.

For example, you could have two products "Blue T-Shirt" and "Blue Shoes", which in their nature are unrelated, but you could add a `BLUE` tag to each product and then create a Dynamic Collection to include any products with a `BLUE` tag and they will be returned.

::: tip
Heads up! Tags are converted uppercase as they are saved.
:::

## Enabling tags

In order to enable tagging on a model, simply add the `HasTags` trait.

```php
<?php

namespace App\Models;

// ...
use Lunar\Base\Traits\HasTags;

class SomethingWithTags extends Model
{
    use HasTags;

    // ...
}
```


You can then attach tags like so:

```php
$tags = collect(['Tag One', 'Tag Two', 'Tag Three']);

$model = SomethingWithTags::syncTags($tags);

$model->tags;

// ['TAG ONE', 'TAG TWO', 'TAG THREE']
```

If a tag exists already by name it will use it, otherwise they will be created. The process runs via a job so will run in the background if you have that set up.
