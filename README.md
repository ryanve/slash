# [slash](../../)

#### PHP utility classes for URIs, paths, and files

## API ([3.0](../../releases))

### Classes

- [<b>Uri</b>](#uri-methods) methods aim to be more practical and reliable than [`parse_url`](http://www.php.net/manual/en/function.parse-url.php). 
- [<b>Path</b>](#path-methods) contains reliable methods for working with server filesystem paths.
- [<b>File</b>](#file-methods) contains reliable methods for reading and writing files.
- [<b>Slash</b>](#slash-methods) contains string utilities inherited by `Path` and `Uri`.

### [`Uri`](./slash/Uri.php) Methods

<pre>foo://user:pass@example.com:800/dir/file.php?s=10&amp;e=23#jump
\_/   \_______/ \_________/ \_/              \_______/ \__/
 |        |          |       |                  |       |
 |     userinfo   hostname  port              query   fragment
 |    \_______________________/\___________/
 |            |                      |
scheme    authority                 path</pre>

#### Methods are based on the [URI diagram](http://en.wikipedia.org/wiki/URI_scheme#Examples) or otherwise normalized between terms in [PHP](http://www.php.net/manual/en/function.parse-url.php), [JavaScript](https://developer.mozilla.org/en-US/docs/DOM/window.location), [jQuery Mobile](http://jquerymobile.com/test/docs/api/methods.html), and [node](http://nodejs.org/docs/v0.5.5/api/url.html).

```php
\slash\Uri::scheme($uri) // get scheme (excludes ':')
\slash\Uri::prorel($uri) // convert to protocol relative
\slash\Uri::prorel($uri, $scheme) // replace the scheme
\slash\Uri::bar($uri) // get "address bar" uri
\slash\Uri::authority($uri) // get authority
\slash\Uri::hostname($uri) // get hostname
\slash\Uri::prefetch($uri) // get uri for dns prefetch
\slash\Uri::userinfo($uri) // get userinfo
\slash\Uri::user($uri) // get username
\slash\Uri::pass($uri) // get password
\slash\Uri::port($uri) // get port number (string)
\slash\Uri::hier($uri) // get hierarchial part
\slash\Uri::path($uri) // get path part
\slash\Uri::query($uri) // get query str (excludes '?')
\slash\Uri::hash($uri) // get fragment  (includes '#')
\slash\Uri::fragment($uri) // get fragment  (excludes '#')
\slash\Uri::lslash($uri) // left slash it
\slash\Uri::rslash($uri) // right slash it
\slash\Uri::nohash($uri) // remove hash
\slash\Uri::novars($uri) // remove hash and query
\slash\Uri::parse($uri) // get object containing parts
```

### [`Slash`](./slash/Slash.php) Methods

```php
\slash\Slash::trim($path)
\slash\Slash::ltrim($path)
\slash\Slash::rtrim($path)
\slash\Slash::slash($path)
\slash\Slash::lslash($path)
\slash\Slash::rslash($path)
\slash\Slash::join(*$parts)
\slash\Slash::split($path)
\slash\Slash::normalize($path)
```

### [`File`](./slash/File.php) Methods

```php
\slash\File::exists($path)
\slash\File::get($path, $done?)
\slash\File::put($path, $data)
\slash\File::load($path, $done)
\slash\File::getJson($path, $done?)
\slash\File::putJson($path, $data)
```

### [`Path`](./slash/Path.php) Methods

```php
\slash\Path::normalize($path)
\slash\Path::root($relative?)
\slash\Path::dir($relative?)
\slash\Path::ext($path, $add?)
\slash\Path::filename($file)
\slash\Path::inc($file)
\slash\Path::scan($path?)
\slash\Path::tree($path?)
\slash\Path::paths($path?)
\slash\Path::files($path?)
\slash\Path::dirs($path?)
\slash\Path::exists($item)
\slash\Path::isPath($item)
\slash\Path::isFile($item)
\slash\Path::isDir($item)
\slash\Path::isDot($item)
\slash\Path::isAbs($path)
\slash\Path::toAbs($path)
\slash\Path::toUri($path, $scheme?)
\slash\Path::isHttps()
\slash\Path::mtime($path, $format?)
\slash\Path::affix($list, $prefix, $suffix?)
\slash\Path::infix($list, $infix)
\slash\Path::depth($path)
\slash\Path::tier($array)
\slash\Path::sort($array)
\slash\Path::locate($needles)
\slash\Path::contains($haystack, $needle)
\slash\Path::search($paths, $needles?)
\slash\Path::find($list, $test)
```

### [`Mixin`](./slash/traits/Mixin.php) Methods

#### Extend Any [Class](#classes)

```php
::mixin($name, $fn) // mixin a single method
::mixin($array) // mixin methods from an associative array
::mixin($object) // mixin methods from an object or class
::method($name) // fully-qualify a method (returns callable)
::methods($object?) // get array of all methods incl. mixins
```

## [MIT License](http://opensource.org/licenses/MIT)

Copyright (C) 2013 by [Ryan Van Etten](https://github.com/ryanve)