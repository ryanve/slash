# [URI5.com](http://uri5.com/)

## About

[URI5](http://uri5.com/) is a compact set of opensource URI-related PHP functions.

## Install
To install, drop [uri5.php](https://github.com/ryanve/uri5/blob/master/uri5.php) into a directory and use [`require_once()`](http://php.net/manual/en/function.require-once.php) or [`include_once()`](http://php.net/manual/en/function.include-once.php) to load it.

## Usage 

```php
\uri5\scheme( $uri );           # get scheme (excludes ':')
\uri5\bar( $uri );              # get "address bar" uri
\uri5\hostname( $uri );         # get hostname
\uri5\authority( $uri );        # get authority
\uri5\userinfo( $uri );         # get userinfo
\uri5\user( $uri );             # get username
\uri5\pass( $uri );             # get username
\uri5\port( $uri );             # get port number (string)
\uri5\hierarchy( $uri );        # get hier part
\uri5\path( $uri );             # get path part
\uri5\query( $uri );            # get query str (excludes '?')
\uri5\fragment( $uri );         # get fragment  (excludes '#')
\uri5\hash( $uri );             # get fragment  (includes '#')
\uri5\prorel( $uri );           # convert to protocol relative
\uri5\prorel( $uri, $scheme );  # replace the scheme
\uri5\prefetch( $uri );         # get uri for dns prefetch
\uri5\lslash( $uri );           # left slash it
\uri5\rslash( $uri );           # right slash it
\uri5\parts( $uri );            # get object containing parts
```

## License

### URI5 is available under the [MIT license](http://en.wikipedia.org/wiki/MIT_License)

Copyright (C) 2011 by [Ryan Van Etten](https://github.com/ryanve)

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.