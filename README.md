# [slash](../../)

[Slash](../../) (formerly <b>uri5</b>) is a compact set of opensource path and URI-related PHP functions. It consists of two utility classes in the `slash` namespace: `Uri` and `Path`. URI methods aim to be more practical and reliable than PHP's [`parse_url`](http://www.php.net/manual/en/function.parse-url.php). `Path` contains reliable methods for working with filesystem paths on the server.

## Usage

### `Uri` Methods

Please view [this diagram](http://en.wikipedia.org/wiki/URI_scheme#Examples) as primary terminology reference. Other methods below use names normalized between terms in [PHP](http://www.php.net/manual/en/function.parse-url.php), [JavaScript](https://developer.mozilla.org/en-US/docs/DOM/window.location), [jQuery Mobile](http://jquerymobile.com/test/docs/api/methods.html), and [node](http://nodejs.org/docs/v0.5.5/api/url.html).

```php
\slash\Uri::scheme($uri)           # get scheme (excludes ':')
\slash\Uri::prorel($uri)           # convert to protocol relative
\slash\Uri::prorel($uri, $scheme)  # replace the scheme
\slash\Uri::bar($uri)              # get "address bar" uri
\slash\Uri::authority($uri)        # get authority
\slash\Uri::hostname($uri)         # get hostname
\slash\Uri::prefetch($uri)         # get uri for dns prefetch
\slash\Uri::userinfo($uri)         # get userinfo
\slash\Uri::user($uri)             # get username
\slash\Uri::pass($uri)             # get password
\slash\Uri::port($uri)             # get port number (string)
\slash\Uri::hier($uri)             # get hierarchial part
\slash\Uri::path($uri)             # get path part
\slash\Uri::query($uri)            # get query str (excludes '?')
\slash\Uri::hash($uri)             # get fragment  (includes '#')
\slash\Uri::fragment($uri)         # get fragment  (excludes '#')
\slash\Uri::lslash($uri)           # left slash it
\slash\Uri::rslash($uri)           # right slash it
\slash\Uri::nohash($uri)           # remove hash
\slash\Uri::novars($uri)           # remove hash and query
\slash\Uri::parse($uri)            # get object containing parts
```

## License

### Slash is available under the [MIT license](http://en.wikipedia.org/wiki/MIT_License)

Copyright (C) 2011 by [Ryan Van Etten](https://github.com/ryanve)