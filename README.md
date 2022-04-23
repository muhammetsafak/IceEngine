# IceEngine
ICE Engine : Basic PHP Template Engine

[![Latest Stable Version](http://poser.pugx.org/muhammetsafak/ice-engine/v)](https://packagist.org/packages/muhammetsafak/ice-engine) [![Total Downloads](http://poser.pugx.org/muhammetsafak/ice-engine/downloads)](https://packagist.org/packages/muhammetsafak/ice-engine) [![Latest Unstable Version](http://poser.pugx.org/muhammetsafak/ice-engine/v/unstable)](https://packagist.org/packages/muhammetsafak/ice-engine) [![License](http://poser.pugx.org/muhammetsafak/ice-engine/license)](https://packagist.org/packages/muhammetsafak/ice-engine) [![PHP Version Require](http://poser.pugx.org/muhammetsafak/ice-engine/require/php)](https://packagist.org/packages/muhammetsafak/ice-engine)

## Installation

```
composer require muhammetsafak/ice-engine
```

![iceengine](https://user-images.githubusercontent.com/9823597/148836439-e5c99304-29c0-4be9-b903-f80dbb7229ed.png)

## Engine Methods

### `timeout()`

The lifetime determines the maximum lifetime for the views cache.

```php
public function timeout(?int $ttl = 86400): self
```

If `NULL` is defined, the cache remains valid until the view file is modified.

An integer of `0` or less means that the cache will be refreshed each time.

### `extension()`

Defines the extension of the view files.

```php
public function extension(string $extension = '.template.php'): self
```

### `parse()`

Loads and executes the specified view file.

```php
public function parse(string $name, array|object $data = []): void
```

### `compress()`

Specifies whether to compress the cache output.

```php
public function compress(bool $compress = false): self
```

_Note :_ It simply means deleting multiple spaces in the cache file.

### `directive()`

It allows you to add directives suitable for your own project.

```php
public function directive(string $directive, \Closure $closure): self
```

See [Custom Directives](#custom-directives) for details.

## Usage

```php
$iceEngine = new \IceEngine\Engine(__DIR__ . '/views/', __DIR__ . '/cache/');

$iceEngine->parse('index');
```

## Internal Directives

### Extends

```
@extends('layout')
```

### Yield & Section

```
@yield('title')
```

```
@section('title', 'Page Title')
```

```
@section('content')
    ...
@endsection
```

```
@include('view')
```

### Loops

```
@for($i = 0; $i < 5; $i++)
    ...
@endfor
```

```
@foreach($rows as $row)
    ...
@endwhile
```

```
@while(true)
    ...
@endwhile
```

### Continue & Break

```
@continue
```

```
@break
```

_Conditional use of continue and break_

```
@continue($i == 5)
```

```
@break($i == 5)
```

#### If/Else

```
@if($i == 4)
    ...
@else
    ...
@endif
```

```
@if($i == 4)
    ...
@elseif($i == 5)
    ...
@else
    ...
@endif
```

#### Switch/Case

```
@switch($data)
    @case(5)
        ...
    @break
    @case(6)
        ...
    @break
    @default
        ...
@endswitch
```

#### Isset & Empty

```
@isset($data)
    ...
@endisset
```

```
@empty($data)
    ...
@endempty
```

### PHP Command

```
@php
    ...
@endphp
```

### Comments

```
{{-- Comments --}}
```

### Variables

```
{{ $name }}
```

```
{!! $name !!}
```

### Form

```
@form('action.php', 'POST')
    @label('Username', 'username')
    @input('username', 'text', ['id' => 'username'])
    
    @label('Password', 'password')
    @input('password', 'password', ['id' => 'password'])
    
    @label('Biography', 'bio')
    @textarea('biography', '', ['id' => 'bio'])
    
    @label('Gender', 'gender')
    @select('sex', ['man' => 'Man', 'woman' => 'Woman', 'other' => 'Other'], ['id' => 'gender'], 'other')
    
    @button('Reset', ['type' => 'reset']);
    
    @submit('Login')
@endform
```

The html form example that the above example would produce should look like this.

```html
<form action="action.php" method="POST">
    <label for="username">Username</label>
    <input type="text" name="username" id="username" />
    
    <label for="password">Password</label>
    <input type="password" name="password" id="password" />
    
    <label for="bio">Biography</label>
    <textarea name="biography" id="bio"></textarea>
    
    <label for="gender">Gender</label>
    <select name="sex" id="gender">
        <option value="man">Man</option>
        <option value="woman">Woman</option>
        <option value="other" selected>Other</option>
    </select>
    
    <button type="reset">Reset</button>
    <input type="submit" value="Login" />
</form>
```

## Custom Directives

```php
$iceEngine->directive('style', function($href = null){
    if($href !== null){
        return '<link rel="stylesheet" href="'.$href.'">';
    }
    return '<style>';
});

$iceEngine->directive('endstyle', function(){
   return '</style>';
});
```

Usage of the above directives would look like this;

```
@style('https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css')

@style
body{
    background: #fff;
}
@endstyle
```

In this example, IceEngine generates HTML like this:
```html
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
<style>
    body{
        background: #fff;
    }
</style>
```

## License

This library was developed by [Muhammet ŞAFAK](https://www.muhammetsafak.com.tr) and distributed under the [MIT License](./LICENSE).
