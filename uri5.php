<?php
namespace uri5;

/**
 * uri5.php    URI5 is a compact set of URI-related PHP functions.
 * @author     Ryan Van Etten (c) 2011-2012
 * @link       http://uri5.com
 * @version    2.0.2
 * @license    MIT
 * @uses       PHP 5.3+
 */

if ( ! \function_exists( __NAMESPACE__ . '\\lslash' ) ) {
    function lslash( $str, $chars = '/' ) {
        return $chars . \ltrim( $str, $chars );
    }
}

if ( ! \function_exists( __NAMESPACE__ . '\\rslash' ) ) {
    function rslash( $str, $chars = '/' ) {
        return \rtrim( $str, $chars ) . $chars;
    }
}

/*if ( ! \function_exists( __NAMESPACE__ . '\\tok' ) ) {
    function tok ( $str, $chars, $rtl = false ) {
        if ( ! isset($str) || ! \is_string($str) ) return '';
        $str = (string) \strtok( $rtl ? \strrev($str) : $str, $chars );
        return $rtl ? \strrev($str) : $str;
    }
}*/
 
/**
 * bar()   Remove the scheme and :// from a URI. The first 
 * double slash and anything to its left is removed. Designed to be 
 * safer than using parse_url(). Called "bar" b/c this is what 
 * you see in the address bar in modern web browsers
 * @example
 *  bar('foo://example.com/1') #  'example.com/1'
 *  bar('//example.com/2')     #  'example.com/2'
 *  bar('www.example.com/3')   #  'www.example.com/3'
 */
if ( ! \function_exists( __NAMESPACE__ . '\\bar' ) ) {
    function bar ( $uri ) {
        if ( ! $uri || ! is_string($uri) )
            return '';
        $uri = \explode( '://', $uri );
        isset( $uri[1] ) and \array_shift($uri);
        $uri = \trim( \implode( '://', $uri ) );
        return \ltrim( \ltrim( $uri, ':/' ) );
    }
}

/**
 *
 * @link  en.wikipedia.org/wiki/Fragment_identifier
 * @link  stackoverflow.com/q/2849756/770127
 * @link  dev.airve.com/demo/speed_tests/php/fragment.php
 */
if ( ! \function_exists( __NAMESPACE__ . '\\fragment' ) ) {
    function fragment( $uri ) {
        # return chars after the first hash `#`
        if ( ! isset($uri) || ! \is_string( $uri ) ) return '';
        $pos = \strpos( $uri, '#' );
        return $pos === false ? '' : \substr( $uri, ++$pos );
    }
}

if ( ! \function_exists( __NAMESPACE__ . '\\hash' ) ) {
    function hash( $uri ) {
        # returns #fragment ( includes the `#` )
        if ( ! isset($uri) || ! \is_string($uri) ) return '';
        return ( $uri = \strstr( $uri, '#' ) ) ? $uri : '';
    }
}

/**
 * hier()      Get the "hierarchical" part of the URI. It includes everything 
 *                  after the '://' and before the ?query string or #fragment.
 *
 * @param   string  $uri
 * @return  string
 */
if ( ! \function_exists( __NAMESPACE__ . '\\hier' ) ) {
    function hier( $uri ) {
        $uri = bar( $uri );
        $uri and $uri = \strtok( $uri, '?#' );
        return (string) $uri;
    }
}


/**
 * prorel()              Make a URI protocol-relative ( a.k.a. scheme-relative )
 *                            OR replace the scheme part of the URI ( making the URI 
 *                            absolute with the specified scheme ) ( see also: prefetch() )
 *
 * @param   string   $uri     is the URI to convert
 * @param   string=  $scheme  optional scheme to include ( e.g. 'http' )
 * @return  string
 *
 *  prorel( 'foo://example.com/dir/file.htm' )  #  '//example.com/dir/file.htm'
 *  prorel( '//example.com/dir/file.htm' )      #  '//example.com/dir/file.htm'
 *  prorel( 'example.com/dir/file.htm' )        #  '//example.com/dir/file.htm'
 *  prorel( 'foo://example.com//dir//double' )  #  '//example.com//dir//double'
 */
if ( ! \function_exists( __NAMESPACE__ . '\\prorel' ) ) {
    function prorel( $uri, $scheme = '' ) {
        $uri = bar($uri);
        if ( '' === $uri )
            return $uri;
        $scheme = $scheme ? \rtrim( $scheme, ':/' ) : '';
        $scheme and $scheme .= ':';
        return $scheme . '//' . $uri;
    }
}


/**
 * scheme()    Get the scheme part of a URI ( a.k.a. scheme name or protocol )
 *             Returns empty if the URI in non-absolute or if the scheme contains 
 *             invalid chars. A scheme can contain alpanumeric|dash|plus|period 
 *             and must start w/ a letter. It is case-insensitive and followed by `:`
 *
 * @link    en.wikipedia.org/wiki/URI_scheme 
 * @param   string      $uri
 * @return  string
 */
if ( ! \function_exists( __NAMESPACE__ . '\\scheme' ) ) {
    function scheme( $uri ) {

        # fail fast if the uri does not look absolute
        $uri = \explode( '://', $uri ); 
        if ( ! isset( $uri[1] ) ) # (count < 2) means no '://'
            return '';

        # trim the left side for safe use on form fields
        # and use strtok to ensure we leftmost `:`
        $uri = \strtok( \ltrim( $uri[0] ), ':' ); 

        # check for invalid chars
        if ( ! $uri || \preg_match( '#[^a-z0-9.+-]#', $uri = \strtolower($uri) ) )
            return '';

        # must start with a letter
        return \preg_match( '#^[a-z]#', $uri ) ? $uri : '';
    }
}

/**
 * authority()       Get the "authority" part of a URI. It contains the
 *                   "userinfo" (if any) + "hostname" + "port" (if any)
 *
 * @param   string   $uri
 * @return  string
 *
 *  authority('foo://example.com/dir/file.htm')       #  'example.com'
 *  authority('foo://www.example.com/dir/file.htm')   #  'www.example.com'
 *  authority('foo://user:pass@example.com:800/dir/') #  'user:pass@example.com:800'
 */
if ( ! \function_exists( __NAMESPACE__ . '\\authority' ) ) {
    function authority( $uri ) {
        if ( ! $uri ) return '';
        $uri = bar( $uri );
        $uri and ( $uri = \strtok( $uri, '/?#' ) )
             and ( \strpos( $uri, '.' ) or $uri = '' );
        return (string) $uri;
    }
}

if ( ! \function_exists( __NAMESPACE__ . '\\userinfo' ) ) {
    function userinfo( $uri ) {
        $uri and $uri = authority( $uri );
        return $uri && ( $pos = \strrpos( $uri, '@' ) ) ? \substr( $uri, 0, $pos ) : '';
    }
}

if ( ! \function_exists( __NAMESPACE__ . '\\user' ) ) {
    function user( $uri ) {
        $uri = userinfo( $uri );
        return (string) \strtok( $uri, ':' );
    }
}

if ( ! \function_exists( __NAMESPACE__ . '\\pass' ) ) {
    function pass( $uri ) {
        return $uri && ( $uri = userinfo( $uri ) )
                    && ( $pos = \strpos( $uri, ':' ) ) 
                    ? (string) \substr( $uri, ++$pos ) : '';
    }
}

/**
 * hostname()        Get the "hostname" part of a URI
 *
 * @param   string  $uri
 * @return  string
 * 
 * @link    en.wikipedia.org/wiki/URI_scheme#Examples
 * @link    tools.ietf.org/html/rfc3986#section-3.2.2
 *
 * hostname('foo://example.com/dir/file.htm')       #  'example.com'
 * hostname('foo://www.example.com/dir/file.htm')   #  'www.example.com'
 * hostname('foo://user:pass@example.com:800/dir/') #  'example.com'
 */
if ( ! \function_exists( __NAMESPACE__ . '\\hostname' ) ) {
    function hostname( $uri ) {
        $uri and ( $uri = authority( $uri ) )
             and ( $uri = \array_pop( \explode( '@', $uri ) ) )
             and ( $uri = \strtok( $uri, ':' ) );
        return (string) $uri;
    }
}

/**
 * port()           Get the numeric "port" part of a URI
 *
 * @param   string  $uri
 * @return  string
 *
 * @link  tools.ietf.org/html/rfc3986#section-3.2.3
 *
 * port('foo://example.com/dir/file.htm')       #  ''
 * port('foo://user:pass@example.com:800/dir/') #  '800'
 */
if ( ! \function_exists( __NAMESPACE__ . '\\port' ) ) {
    function port( $uri ) {
        if ( ! $uri ) return '';
        $uri = authority($uri);
        $uri = \array_pop( \explode( '@', $uri ) );
        $uri = \explode( ':', $uri );
        return 2 == \count( $uri ) && \ctype_digit( $uri[1] ) ? $uri[1] : '';
    }
}

/**
 * path()            Get the "path" part of a URI
 *                    
 * @param   string  $uri  an absolute URI or full barname
 * @return  string
 * 
 * @link    tools.ietf.org/html/rfc3986#section-3.3
 *
 * path( 'foo://example.com/dir/file.htm' )   #  'dir/file.htm'
 * path( 'foo://example.com/dir/file?q=yo' )  #  'dir/file'
 * path( 'foo://example.com/dir/file#frag' )  #  'dir/file'
 * path( '//user:pass@example.com:800/dir/' ) #  'dir/'
 */
if ( ! \function_exists( __NAMESPACE__ . '\\path' ) ) {
    function path( $uri ) {

        if ( ! isset($uri) || ! is_string($uri) )
            return $uri;

        # RE: tools.ietf.org/html/rfc3986#section-3.3
        # <mailto:fred@example.com> has a path of "fred@example.com"
        # whereas <foo://info.example.com?fred> has an empty path
        $col = \strpos( $uri, ':' );
        $uri = false !== $col 
            && '://' !== \substr( $uri, $col, 3 )
            && ( !( $sls = \strpos( $uri, '/' ) ) || 0 < ( $sls - $col ) )
            ? \substr( \trim( $uri ), ++$col ) # mailto:fred@example.com
            : ( ( $uri = bar($uri) ) ? \strstr( $uri, '/' ) : $uri );

        $uri and ( $uri =  (string) \strtok( $uri, '?#' ) )
             and ( $uri = \str_replace( '//', '/', $uri ) );
        return $uri ? $uri : '';
    }
}

/**
 * query()          Get the "query" part of a URI - the part after the `?`
 *                       but before the #frag. If you need to parse the result use 
 *                       @link php.net/manual/en/function.parse-str.php
 *                    
 * @param   string  $uri
 * @return  string
 */
if ( ! \function_exists( __NAMESPACE__ . '\\query' ) ) {
    function query( $uri ) {
        if ( ! $uri ) return '';
        $uri = bar( $uri );
        $uri and ( $uri = \strtok( $uri, '#' ) )
             and ( $uri = \strstr( $uri, '?' ) )
             and ( $uri = \substr( $uri, 1 ) )
             and ( $uri = \html_entity_decode( $uri ) );
        return (string) $uri;
    }
}

/**
 * prefetch()        Get the part of the $uri needed for a dns-prefetch link:
 *                   <link rel="dns-prefetch" href="//airve.github.com">
 *                   In other words, get the protocol-relative hostname. 
 *                  ( see also: prorel() )
 *
 * @param   string        $uri
 * @return  string|false
 *
 *  prefetch('foo://example.com/dir/file.htm')       #  '//example.com'
 *  prefetch('foo://www.example.com/dir/file.htm')   #  '//www.example.com'
 *  prefetch('foo://user:pass@example.com:800/dir/') #  '//example.com'
 */
if ( ! \function_exists( __NAMESPACE__ . '\\prefetch' ) ) {
    function prefetch( $uri ) {
        $uri = hostname($uri);
        return \strlen($uri) ? '//' . $uri : $uri;
    }
}

if ( ! \function_exists( __NAMESPACE__ . '\\novars' ) ) {
    function novars( $str ) {
        if ( ! isset($str) || ! \is_string($str) ) return '';
        return (string) \strtok( $str, '?#' );
    }
}

if ( ! \function_exists( __NAMESPACE__ . '\\nohash' ) ) {
    function nohash( $str ) {
        if ( ! isset($str) || ! \is_string($str) ) return '';
        return (string) \strtok( $str, '#' );
    }
}


if ( ! \function_exists( __NAMESPACE__ . '\\parse' ) ) {
    function parse( $uri = null ) {

        $o = new \stdClass;
        
        if ( ! $uri ||  ! \is_string($uri) )
            return $o;
        
        $o->uri = $uri;
        $o->scheme = scheme( $uri );
        $o->bar = $bar = bar( $uri );
        $o->prorel = '//' . $bar;
            
        if ( ! $bar )
            return $o;
        
        # authority / hierarchial part / path
        $o->hier = (string) \strtok( $bar, '?#' );
        $part = \explode( '/', $o->hier );
        $o->authority = \array_shift( $part );
        $o->path = lslash( \implode( '/', $part ) );

        # authority
        if ( ! \strpos( $o->authority, '.' ) ) {
            # authority did not contain period => treat as relative path
            $o->path = lslash( $o->authority . $o->path );
            $o->authority = '';
        } else {
            # separate authority
            $auth = \explode( '@', $o->authority );
            $part = \array_pop( $auth ); # includes host and port
            $userinfo = \implode( '@', $auth );

            # host / port:
            $part = \explode( ':', $part );
            $o->hostname = \array_shift( $part );
            $o->prefetch = '//' . $o->hostname;
            1 == \count( $part ) && \ctype_digit( $part[0] ) and $o->port = $part[0];
        
            # user / pass
            if ( $userinfo ) {
                $o->userinfo = $userinfo;
                $part = \explode( ':', $userinfo );
                $o->user = \array_shift( $part );
                \count($part) and $o->pass = \implode( ':', $part );
            }
        }
        
        # query
        $part = \explode( '?', \strtok( $bar, '#' ) ); # nohash
        \array_shift( $part ); # novars
        $o->query = \html_entity_decode( \implode( '?', $part ) );
        
        # hash / fragment
        if ( $hash = (string) \strstr( $o->bar, '#' ) )
            $o->fragment = (string) \substr( $o->hash = $hash, 1 );
        
        return $o;
    }
}

#end