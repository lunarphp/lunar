# lunarphp/lunar-blog

Blog authoring add-on for the Lunar Inertia panel. It adds an "Articles" section to `/panel` (list, create, edit, delete), with a tiptap rich text body, a featured image, categories and tags, and pickers for related products and related articles. Published articles are exposed through the `Lunar\Blog\Models\Article` model (a `published()` scope, an `authorName()` helper, a `bodyExcerpt()` helper) for a storefront or API to consume.

## Requirements

* PHP 8.4+
* `lunarphp/core` and `lunarphp/panel` (this package extends the Lunar Inertia panel, it does not work with `lunarphp/filament`)
* A staff guard with the `spatie/laravel-permission` `staff` guard configured, as used by `lunarphp/panel`

## Installation

```bash
composer require lunarphp/lunar-blog
```

The service provider (`Lunar\Blog\BlogServiceProvider`) is auto discovered.

### Run the migrations

```bash
php artisan migrate
```

This creates the `blog_articles`, `blog_categories`, `blog_tags` tables and their pivots, and seeds the `blog:manage` permission (see below) on the panel's staff guard. If you would rather ship the migrations in your own app, publish them first.

```bash
php artisan vendor:publish --tag=lunar-blog-migrations
php artisan migrate
```

### Publish the config (optional)

```bash
php artisan vendor:publish --tag=lunar-blog-config
```

This copies `config/lunar-blog.php` into your app so you can change any of the values described below. Publishing is optional, sensible defaults ship with the package.

### Serve the panel assets

The Articles pages are a compiled Vue bundle registered with the panel's own Vite integration (`PanelManager::vite()`), the same mechanism the panel itself uses. It needs to be available under `public/vendor/lunar-panel/lunar-blog`.

During local development, symlink the package's build directory instead of copying it, so a rebuild is picked up without re-publishing:

```bash
php artisan lunar:panel:link
```

In production, publish (copy) the compiled assets:

```bash
php artisan vendor:publish --tag=lunar-blog-panel-assets --force
```

or publish every panel add-on's assets (including the panel's own) in one go:

```bash
php artisan vendor:publish --tag=panel-all-assets --force
```

Re-run whichever of these you used after upgrading the package, since the compiled bundle changes with the source.

### Grant the permission

The `blog:manage` permission gates both the "Blog" navigation item and the article routes together, and is seeded automatically by the package's migration (on the guard `lunarphp/panel` authenticates against, resolved from Lunar's `Manifest`, normally `staff`). Assign it to whichever staff members should manage articles, for example in a seeder:

```php
use Lunar\Core\Models\Staff;

Staff::find($id)->givePermissionTo(config('lunar-blog.permission'));
```

Staff with the `admin` flag bypass permission checks entirely, as elsewhere in Lunar's panel.

## Configuration reference

`config/lunar-blog.php`:

```php
return [
    'models' => [
        'article' => Article::class,
        'category' => Category::class,
        'tag' => Tag::class,
    ],
    'author_model' => Staff::class,
    'permission' => 'blog:manage',
    'media' => [
        'disk' => null,
        'collection' => 'featured',
    ],
    'publish_timezone' => env('LUNAR_BLOG_PUBLISH_TIMEZONE', config('app.timezone')),
    'navigation' => [
        'group' => ['key' => 'blog', 'label' => 'Blog', 'priority' => 30],
        'item' => ['label' => 'Articles', 'icon' => 'fileText', 'priority' => 10],
    ],
];
```

* **`models.article` / `models.category` / `models.tag`**: the fully qualified model classes, defaulting to `Lunar\Blog\Models\Article`, `Lunar\Blog\Models\Category`, `Lunar\Blog\Models\Tag`. See "Extending the Article model" below for what overriding these does and does not affect today.
* **`author_model`**: the model an article's `author_id` belongs to. Defaults to `Lunar\Core\Models\Staff`, the model `lunarphp/panel` authenticates staff against, so the "Author" picker on the create/edit form is populated from real panel users out of the box. Point this at your own staff model if your app extends or replaces it (it must have `first_name`, `last_name` and `email` columns, used to label the picker options).
* **`permission`**: the spatie permission handle gating the nav item and all article routes. Defaults to `blog:manage`.
* **`media.disk`**: reserved for a future release, currently not read by the package. Leave it `null` and the featured image is stored on Spatie Media Library's own default disk.
* **`media.collection`**: the media library collection name the featured image is attached to. Defaults to `featured`, and is a single file collection (a new upload replaces the previous image).
* **`publish_timezone`**: the timezone the "Publish at" field is entered and displayed in on the create/edit form. Stored timestamps are always UTC, this only affects the trading facing wall clock time shown to staff. Defaults to `config('app.timezone')`, and can be overridden per environment with `LUNAR_BLOG_PUBLISH_TIMEZONE`.
* **`navigation.group`** and **`navigation.item`**: the panel navigation group and item the Articles link is registered under (key, label, priority, and icon). Change these to move or relabel the entry, or to group it under an existing navigation group by matching its key.

## Extending the Article model

`config('lunar-blog.models.article')` (and its `category` / `tag` counterparts) declare the model classes the package ships with. Today, `author_model` is the config entry the package fully resolves through, the article's `author()` relation and the `authorExists()` validation rule are both built from `config('lunar-blog.author_model')`, so pointing it at your own staff model is a supported extension point.

The `models.article` / `models.category` / `models.tag` entries are not yet threaded through the panel's own routes and controllers, which currently type hint the concrete `Lunar\Blog\Models\Article` class directly for route model binding and its relations. Overriding these config values will not retarget those internal usages. If you need extra behaviour, the current options are to add methods, scopes, casts or an observer to your own subclass and reference `config('lunar-blog.models.article')` from your own code (factories, jobs, storefront queries), or to fork the relevant controller/relation. Wiring the config value fully through the package is tracked as a follow up.

## Testing

The package ships its own Pest suite in the monorepo (`tests/blog`), which is the reference for its expected behaviour, including permission gating, the tiptap body storage, the timezone conversion on publish, and the related record pickers.
