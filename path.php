<?php
/**
 * @link     http://github.com/ryanve/slash
 * @version  3.0.0-0
 * @license  MIT
 */

namespace slash;

class Path {

    const slashes = '/\\';
    
    protected static $mixins = array();
    
    /**
     * @method
     */
    public static function __callStatic($name, $params) {
        if (isset(static::$mixins[$name]))
            return \call_user_func_array(static::$mixins[$name], $params);
        \trigger_error(__CLASS__ . "::$name is not callable.");
    }

    /**
     * Mixin custom static methods.
     */
    public static function mixin($name, $fn = null) {
        if (\is_scalar($name))
            $fn and static::$mixins[$name] = $fn;
        else foreach ($name as $k => $v)
            self::mixin($k, $v);
    }
    
    /**
     * Fully-qualify a method name (for callback use).
     * @param   string  $name
     * @return  string
     */
    public static function method($name) {
        return __CLASS__ . "::$name";
    }
    
    /**
     * @param   string  $name
     * @return  string
     */
    public static function methods() {
        $methods = \get_class_methods(__CLASS__);
        return \array_merge($methods, \array_diff(\array_keys(static::$mixins), $methods));
    }
    
    /**
     * @param   mixed  $fn
     * @param   mixed  $value
     * @return  mixed
     */
    protected static function pass($fn, $value = null) {
        return null === $fn ? $value : \call_user_func_array($fn, \array_slice(\func_get_args(), 1));
    }
    
    /**
     * @return  string
     */
    public static function lslash($str) {
        return '/' . \ltrim($str, static::slashes);
    }
   
    /**
     * @return  string
     */   
    public static function rslash($str) {
        return \rtrim($str, static::slashes) . '/';
    }
    
    /**
     * @return  string
     */   
    public static function trim($str) {
        return \trim($str, static::slashes);
    }
    
    /**
     * Join paths or URI parts using a fwd slash as the glue.
     * @return  string
     */
    public static function join() {
        $result = '';
        foreach (\func_get_args() as $n)
            $result = $result ? \rtrim($result, static::slashes) . '/' . \ltrim($n, static::slashes) : $n;
        return $result;
    }

    /**
     * @return  array
     */
    public static function split($path) {
        $path = \trim(static::normalize($path), '/');
        return '' === $path ? array() : \explode('/', $path);
    }
    
    /**
     * @return  string|null
     */
    public static function part($path, $idx = 0) {
        \is_array($path) or $path = static::split($path);
        $idx = 0 > $idx ? \count($path) + $idx : (int) $idx;
        return isset($path[$idx]) ? $path[$idx] : null;
    }
    
    /**
     * @return  string
     */
    public static function normalize($path) {
        return \str_replace('\\', '/', $path);
    }
    
    /**
     * @return  string
     */   
    public static function root($pathRelative) {
        return $_SERVER['DOCUMENT_ROOT'] . static::lslash($pathRelative);
    }
    
    /**
     * @return  string
     */
    public static function dir($pathRelative) {
        return __DIR__ . static::lslash($pathRelative);
    }
    
    /**
     * @return  string|false
     */
    public static function ext($path, $add = null) {
        if (null === $add)
            # get the basename, remove any query params, get chars starting at last dot
            return \strrchr(\strtok(\basename($path), '?'), '.');
        $add = '.' . \ltrim($add, '.'); # add to path if missing
        return \basename($path) === \basename($path, $add) ? \rtrim($path, '.') . $add : $path;
    }
    
    /**
     * @return  string
     */
    public static function filename($path) {
        return \pathinfo($path, PATHINFO_FILENAME);
    }
    
    /**
     * @return  bool
     */
    public static function inc($path) {
        ($bool = static::isFile($path)) and include $path;
        return $bool;
    }
    
    /**
     * @return  bool
     */
    public static function isPath($item) {
        return \is_scalar($item) && \file_exists($item);
    }
    
    /**
     * @return  bool
     */
    public static function isDir($item) {
        return \is_scalar($item) && \is_dir($item);
    }
    
    /**
     * @return  bool
     */
    public static function isFile($item) {
        return \is_scalar($item) && \is_file($item);
    }

    /**
     * Test if item is a dot folder name
     * @return  bool
     */
    public static function isDot($item) {
        return '.' === $item || '..' === $item;
    }
    
    /**
     * @return  bool
     */
    public static function isAbs($item) {
        return \is_scalar($item) && \realpath($item) === $item;
    }
    
    /**
     * @return  string|bool
     */
    public static function toAbs($item) {
        return \is_scalar($item) ? \realpath($item) : false;
    }

    /**
     * @param   string   $path 
     * @param   string   $scheme 
     * @return  string
     */
    public static function toUri($path = '', $scheme = null) {
        $uri = ($scheme && \is_string($scheme) ? $scheme . '://' : '//') . $_SERVER['SERVER_NAME'];
        return $uri . static::lslash(\str_replace($_SERVER['DOCUMENT_ROOT'], '', $path));
    }
    
    /**
     * @param   string   $path 
     * @param   string   $scheme 
     * @return  string
     */
    public static function toUrl($path, $scheme = null) {
        \is_string($scheme) or $scheme = static::isHttps() ? 'https' : 'http';
        return static::toUri($path, $scheme);
    }
    
    /**
     * @return  bool
     */
    public static function isHttps() {
        return !empty($_SERVER['HTTPS']) and 'off' !== \strtolower($_SERVER['HTTPS'])
            or !empty($_SERVER['SERVER_PORT']) and 443 == $_SERVER['SERVER_PORT'];
    }

    /**
     * Get a associative array containing the dir structure
     * @return array
     */
    public static function tree($path) {
        $list = array();
        $base = static::rslash($path);
        foreach (static::listPaths($path) as $n) {
            if (\is_dir($dir = $base . $n)) {
                # add slash to prevent integer index conflicts
                $list["$n/"] = static::tree($dir);
            } else { $list[] = $n; }
        }
        return $list;
    }

    /**
     * @return array
     */
    public static function listPaths($path) {
        $list = array();
        foreach (\is_file($path) ? array(\basename($path)) : \scandir($path) as $n)
            static::isDot($n) or $list[] = static::normalize($n);
        return $list;
    }
    
    /**
     * @return array
     */
    public static function listDirs($path) {
        $list = array();
        $base = static::rslash($path);
        foreach (static::listPaths($path) as $n)
            \is_dir($base . $n) and $list[] = $n;
        return $list;
    }

    /**
     * @return array
     */
    public static function listFiles($path) {
        $list = array();
        $base = static::rslash($path);
        foreach (static::listPaths($path) as $n) {
            if ( ! \is_dir($base . $n))
                $list[] = $n;
            else foreach (static::listFiles($base . $n) as $file)
                $list[] = "$n/$file";
        }
        return $list;
    }
    
    /**
     * Get the modified time of a file or a directory. For directories,
     * it gets the modified time of the most recently modified file.
     * @param   string       $path     Full path to directory or file.
     * @param   string       $format   Date string for use with date()
     * @return  int|string|null
     */
    public static function mtime($path, $format = null) {
        $time = static::listFiles($path);
        $time = $time ? \max(\array_map('filemtime', 
            static::affix($time, static::rslash($path)))
        ) : null;
        return $format && $time ? \date($format, $time) : $time;
    }
    
    /**
     * @return object
     */
    public static function iterator($path) {
        return new \DirectoryIterator($path);
    }
    
    /**
     * @return array
     */
    public static function affix(array $list, $prefix = '', $suffix = '') {
        foreach ($list as &$n)
            $n = $prefix . $n . $suffix;
        return $list;
    }
    
    /**
     * @param  string  $path
     * @param  string  $infix   text to insert before file extension
     * @return string
     */
    public static function infix($path, $infix) {
        return \preg_replace('#(\.\w+)$#', "$infix$1", $path);
    }

    /**
     * @return array
     */
    public static function group(array $list) {
        $levels = array();
        foreach ($list as $i => $n)
            $levels[$i] = \substr_count($n, '/');
        # Ensure result is ordered and non-sparse.
        $result = \array_pad(array(), \max($levels), array());
        foreach ($list as $i => $n)
            $result[$levels[$i]][] = $n;
        return $result;
    }
    
    /**
     * @return array
     */
    public static function sort(array $list) {
        return \call_user_func_array('array_merge', \array_reverse(static::group($list)));
    }
    
    /**
     * Get the first existent path from the supplied args.
     * @param  array|string  $needles
     * @return mixed
     */
    public static function locate($needles) {
        \is_array($needles) or $needles = \func_get_args();
        return static::find($needles, static::method('isPath'));
    }
    
    /**
     * @param  string|array|object  $haystack
     * @param  string               $needle
     * @return bool
     */
    public static function contains($haystack, $needle) {
        if (\is_scalar($haystack))
            return false !== \strpos($haystack, $needle);
        foreach ((array) $haystack as $v)
            if (self::contains($v, $needle))
                return true;
        return false;
    }
    
    /**
     * @param  string|array|object  $path
     * @param  string|array         $needles
     * @return array
     */
    public static function search($path, $needles) {
        $result = array();
        \is_array($needles) or $needles = \array_slice(\func_get_args(), 1);
        foreach (\is_scalar($path) ? static::listPaths($path) : $path as $v)
            foreach ($needles as $needle)
                static::contains($v, $needle) and $result[] = $v;
        return $result;
    }
    
    /**
     * Get the first $list item than passes $test
     * @param   array|object  $list
     * @param   callable      $test
     * @param   int           $goal  number of params to pass to $test
     * @return  mixed
     */
    public static function find($list, callable $test, $goal = null) {
        foreach ($list as $k => $v)
            if (\call_user_func_array($test, static::agree(array($v, $k, $list), $goal)))
                return $v;
    }
    
    /**
     * @param   array      $arr    array to slice or pad depending on $goal
     * @param   array|int  $goal   target length (or array to match length)
     * @param   mixed      $filler value to pad with (when needed)
     * @return  array
     */
    protected static function agree(array $arr, $goal, $filler = null) {
        return \is_numeric($goal = \is_array($goal) ? \count($goal) : $goal) ? (
            $goal < \count($arr) ? \array_slice($arr, 0, $goal) : \array_pad($arr, $goal, $filler)
        ) : $arr;
    }
    
    /** 
     * @return mixed
     */
    public static function getFile($path, callable $fn = null) {
        return static::pass($fn, static::isFile($path) ? \file_get_contents($path) : false);
    }
    
    /** 
     * @return mixed
     */
    public static function putFile($path, $data) {
        return null !== $path ? \file_put_contents($path, 
            $data instanceof \Closure ? $data(static::getFile($path)) : $data
        ) : false;
    }
    
    /** 
     * @return mixed
     */
    public static function getJson($path, callable $fn = null) {
        return static::pass($fn, \is_scalar($path) ? \json_decode(\file_get_contents($path)) : $path);
    }
    
    /** 
     * @return mixed
     */
    public static function putJson($path, $data) {
        if (null === $path)
            return false;
        $data instanceof \Closure and $data = $data(static::getJson($path));
        return \file_put_contents($path, \is_string($data) ? $data : \json_encode($data));
    }
    
    /** 
     * @return mixed
     */
    public static function loadFile($path, callable $fn = null) {
        if (static::isFile($path)) {
            \ob_start(); 
            include $path;
            $path = \ob_get_contents();
            \ob_end_clean();
        } else { $path = false; }
        return static::pass($fn, $path);
    }
    
}#class