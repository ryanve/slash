<?php
/**
 * @author     Ryan Van Etten
 * @link       http://github.com/ryanve/slash
 * @version    3.0.0-0
 * @license    MIT
 */

namespace slash;

class Uri {

    /**
     * @param  string $str
     * @param  string $chars
     * @return string
     */
    public static function lslash($str, $chars = '/') {
        return $chars . \ltrim($str, $chars);
    }
    
    /**
     * @param  string $str
     * @param  string $chars
     * @return string
     */
    public static function rslash($str, $chars = '/') {
        return \rtrim($str, $chars) . $chars;
    }
    
    /**
     * Remove the scheme and :// from a URI. The first 
     * double slash and anything to its left is removed. Designed to be 
     * safer than using parse_url(). Called "bar" b/c this is what 
     * you see in the address bar in modern web browsers
     * @param  string $uri
     * @return string
     * @example bar('foo://example.com/1') #  'example.com/1'
     * @example bar('//example.com/2')     #  'example.com/2'
     * @example bar('www.example.com/3')   #  'www.example.com/3'
     */
    public static function bar($uri) {
        if ( ! $uri || ! is_string($uri))
            return '';
        $uri = \explode('://', $uri);
        isset($uri[1]) and \array_shift($uri);
        $uri = \trim(\implode('://', $uri ));
        return \ltrim(\ltrim($uri, ':/'));
    }
    
    /**
     * @param  string $uri
     * @return string
     * @link  en.wikipedia.org/wiki/Fragment_identifier
     * @link  stackoverflow.com/q/2849756/770127
     * @link  dev.airve.com/demo/speed_tests/php/fragment.php
     */
    public static function fragment($uri) {
        # Return chars after the first hash `#`
        if ( ! isset($uri) || ! \is_string($uri))
            return '';
        $pos = \strpos( $uri, '#' );
        return $pos === false ? '' : \substr( $uri, ++$pos );
    }
    
    /**
     * Get the #fragment (includes the `#`)
     * @param  string $uri
     * @return string
     */
    public static function hash($uri) {
        return isset($uri) && \is_string($uri) ? ($uri = \strstr($uri, '#') ?: '') : '';
    }
    
    /**
     * Get the "hierarchical" part of the URI. It includes everything 
     * after the '://' and before the ?query string or #fragment.
     * @param  string $uri
     * @return string
     */
    public static function hier($uri) {
        return (string) (($uri = static::bar($uri)) ? \strtok($uri, '?#') : $uri);
    }
    
    /**
     * Make a URI protocol-relative a.k.a. scheme-relative.
     * Or replace the scheme part of the URI to make the URI absolute with the specified scheme.
     * @param  string  $uri    URI to convert
     * @param  string  $scheme optional scheme to add (e.g. 'http')
     * @return string
     * @example prorel('foo://example.com/dir/file.htm') # '//example.com/dir/file.htm'
     * @example prorel('//example.com/dir/file.htm')     # '//example.com/dir/file.htm'
     * @example prorel('example.com/dir/file.htm')       # '//example.com/dir/file.htm'
     * @example prorel('foo://example.com//dir//double') # '//example.com//dir//double'
     */
    public static function prorel($uri, $scheme = '') {
        $uri = static::bar($uri);
        if ('' === $uri)
            return $uri;
        $scheme = $scheme ? \rtrim($scheme, ':/') : '';
        $scheme and $scheme .= ':';
        return $scheme . '//' . $uri;
    }
    
    /**
     * Get the scheme part of a URI (a.k.a. scheme name or protocol).
     * Returns empty if the URI in non-absolute or if the scheme contains 
     * invalid chars. A scheme can contain alpanumeric|dash|plus|period 
     * and must start w/ a letter. It is case-insensitive and followed by `:`
     * @link   en.wikipedia.org/wiki/URI_scheme 
     * @param  string $uri
     * @return string
     */
    public static function scheme($uri) {
        # Fail fast if the uri does not look absolute. (Count < 2 means no '://')
        $uri = \explode('://', $uri); 
        if ( ! isset($uri[1]))
            return '';
        # Trim the left side for safe use on form fields and use strtok to ensure we leftmost `:`
        $uri = \strtok(\ltrim( $uri[0] ), ':'); 
        # check for invalid chars
        if ( ! $uri || \preg_match( '#[^a-z0-9.+-]#', $uri = \strtolower($uri)))
            return '';
        # must start with a letter
        return \preg_match('#^[a-z]#', $uri) ? $uri : '';
    }

    /**
     * Get the "authority" part of a URI. 
     * It contains the "userinfo" (if any) + "hostname" + "port" (if any).
     * @param  string $uri
     * @return string
     * @example authority('foo://example.com/dir/file.htm')       # 'example.com'
     * @example authority('foo://www.example.com/dir/file.htm')   # 'www.example.com'
     * @example authority('foo://user:pass@example.com:800/dir/') # 'user:pass@example.com:800'
     */
    public static function authority($uri) {
        if ( ! $uri)
            return '';
        $uri = static::bar($uri);
        $uri and ($uri = \strtok($uri, '/?#'))
             and (\strpos($uri, '.') or $uri = '');
        return (string) $uri;
    }
    
    /**
     * @param   string  $uri
     * @return  string
     */
    public static function userinfo($uri) {
        $uri and $uri = static::authority($uri);
        return $uri && ($pos = \strrpos($uri, '@')) ? \substr($uri, 0, $pos) : '';
    }

    /**
     * @param  string $uri
     * @return string
     */
    public static function user($uri) {
        return (string) \strtok(static::userinfo($uri), ':');
    }

    /**
     * @param  string $uri
     * @return string
     */
    public static function pass($uri) {
        return $uri && ($uri = static::userinfo($uri)) && ($pos = \strpos($uri, ':')) ? (string) \substr($uri, ++$pos) : '';
    }

    /**
     * Get the "hostname" part of a URI
     * @param   string  $uri
     * @return  string
     * @link    en.wikipedia.org/wiki/URI_scheme#Examples
     * @link    tools.ietf.org/html/rfc3986#section-3.2.2
     * @example hostname('foo://example.com/dir/file.htm')       # 'example.com'
     * @example hostname('foo://www.example.com/dir/file.htm')   # 'www.example.com'
     * @example hostname('foo://user:pass@example.com:800/dir/') # 'example.com'
     */
    public static function hostname($uri) {
        $uri and ($uri = static::authority($uri))
             and ($uri = \explode('@', $uri))
             and ($uri = \array_pop($uri))
             and ($uri = \strtok($uri, ':'));
        return (string) $uri;
    }


    /**
     * Get the numeric "port" part of a URI
     * @param  string $uri
     * @return string
     * @link  tools.ietf.org/html/rfc3986#section-3.2.3
     * @example port('foo://example.com/dir/file.htm')       # ''
     * @example port('foo://user:pass@example.com:800/dir/') # '800'
     */
    public static function port($uri) {
        if ( ! $uri)
            return '';
        $uri = \explode('@', static::authority($uri));
        $uri = \explode(':', \array_pop($uri));
        return 2 == \count($uri) && \ctype_digit($uri[1]) ? $uri[1] : '';
    }

    /**
     * Get the "path" part of a URI
     * @param  string  $uri  an absolute URI or full barname
     * @return string
     * @link   tools.ietf.org/html/rfc3986#section-3.3
     * @example path('foo://example.com/dir/file.htm')   # 'dir/file.htm'
     * @example path('foo://example.com/dir/file?q=yo')  # 'dir/file'
     * @example path('foo://example.com/dir/file#frag')  # 'dir/file'
     * @example path('//user:pass@example.com:800/dir/') # 'dir/'
     */
    public static function path($uri) {
        if ( !isset($uri) || !\is_string($uri))
            return $uri;
        # RE: tools.ietf.org/html/rfc3986#section-3.3
        # <mailto:fred@example.com> has a path of "fred@example.com"
        # whereas <foo://info.example.com?fred> has an empty path
        $col = \strpos($uri, ':');
        $uri = false !== $col 
            && '://' !== \substr($uri, $col, 3)
            && ( !($sls = \strpos($uri, '/')) || 0 < ($sls - $col))
            ? \substr(\trim($uri), ++$col) # mailto:fred@example.com
            : (($uri = static::bar($uri)) ? \strstr($uri, '/') : $uri);
        $uri and ($uri = (string) \strtok($uri, '?#'))
             and ($uri = \str_replace( '//', '/', $uri ));
        return $uri ? $uri : '';
    }

    /**
     * Get the "query" part of a URI - the part after the `?` but before the #frag. 
     * If you need to parse the result use php.net/manual/en/function.parse-str.php
     * @param  string $uri
     * @return string
     */
    public static function query($uri) {
        if ( ! $uri)
            return '';
        $uri = static::bar($uri);
        $uri and ($uri = \strtok($uri, '#'))
             and ($uri = \strstr($uri, '?'))
             and ($uri = \substr($uri, 1 ))
             and ($uri = \html_entity_decode($uri));
        return (string) $uri;
    }

    /**
     * Get the part of the $uri needed for a dns-prefetch link:
     * <link rel="dns-prefetch" href="//airve.github.com">
     * In other words, get the protocol-relative hostname. See also: prorel()
     * @param  string  $uri
     * @return string|bool
     * @example prefetch('foo://example.com/dir/file.htm')       # '//example.com'
     * @example prefetch('foo://www.example.com/dir/file.htm')   # '//www.example.com'
     * @example prefetch('foo://user:pass@example.com:800/dir/') # '//example.com'
     */
    public static function prefetch($uri) {
        return \strlen($uri = static::hostname($uri)) ? '//' . $uri : $uri;
    }

    /**
     * @param  string  $str
     * @return string
     */
    public static function novars($str) {
        return isset($str) && \is_string($str) ? (string) \strtok($str, '?#') : '';
    }

    /**
     * @param   string  $str
     * @return  string
     */
    public static function nohash($str) {
        return isset($str) && \is_string($str) ? (string) \strtok($str, '#') : '';
    }

    /**
     * @return object
     */
    public static function parse($uri = null) {
        $o = new \stdClass;
        if ( ! $uri || !\is_string($uri))
            return $o;
        
        $o->uri = $uri;
        $o->scheme = static::scheme($uri);
        $o->bar = $bar = static::bar($uri);
        $o->prorel = '//' . $bar;
        if ( ! $bar)
            return $o;

        # Authority / Hier / Path
        $o->hier = (string) \strtok($bar, '?#');
        $part = \explode('/', $o->hier);
        $o->authority = \array_shift($part);
        $o->path = static::lslash(\implode('/', $part));

        # Authority
        if ( ! \strpos($o->authority, '.')) {
            # Authority did not contain period => treat as relative path.
            $o->path = static::lslash($o->authority . $o->path);
            $o->authority = '';
        } else {
            # Separate authority.
            $auth = \explode('@', $o->authority);
            $part = \array_pop($auth); # includes host and port
            $userinfo = \implode('@', $auth);

            # host / port:
            $part = \explode(':', $part);
            $o->hostname = \array_shift($part);
            $o->prefetch = '//' . $o->hostname;
            1 == \count($part) && \ctype_digit($part[0]) and $o->port = $part[0];
        
            # User / Pass
            if ($userinfo) {
                $o->userinfo = $userinfo;
                $part = \explode(':', $userinfo);
                $o->user = \array_shift($part);
                \count($part) and $o->pass = \implode(':', $part);
            }
        }
        
        # Query
        $part = \explode('?', \strtok($bar, '#' )); # nohash
        \array_shift($part); # novars
        $o->query = \html_entity_decode(\implode('?', $part));
        
        # Hash / Fragment
        if ($hash = (string) \strstr($o->bar, '#'))
            $o->fragment = (string) \substr($o->hash = $hash, 1);
        return $o;
    }
}#class