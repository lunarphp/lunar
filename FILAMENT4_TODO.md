# Filament Upgrade TODO

- [ ] Move to schema + table classes
- [ ] Deprecate the old way of updating forms and tables
- [ ] Implement "LunarSchema"
- [ ] Look at how we can unregister resource (e.g. if a project doesn't need them)

## LunarSchema Concept

We wrap schema arrays so devs can "hook" into them.

```php
class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components(
            LunarSchema::make(static::class, [
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->email()
                    ->required(),
                DateTimePicker::make('email_verified_at'),
                TextInput::make('password')
                    ->password()
                    ->required(),
            ])
        );
    }
}
```

```php
LunarSchema::modify(UserForm::class, 'email', function($component) {
    return $component->unique(ignorable: fn ($record) => $record);
});
```

```php
LunarSchema::add(UserForm::class, TextInput::make('nickname'), fn ($position) => $position->after('email'));
```

No "delete" as that's pretty dangerous, you can always modify and hide a component.

```php
$position->first();
$position->last(); // default
$position->before('xxx');
$position->after('yyy');
```
