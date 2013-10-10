<?php
/**
 * @package ryanve/slash
 */
namespace slash;
use \RecursiveIteratorIterator as RII;
use \RecursiveDirectoryIterator as RDI;

class Path extends Slash {
    use traits\Mixin;
    protected static $mixin = [# aliases
        'exists' => [__CLASS__, 'isPath']
      , 'listPaths' => [__CLASS__, 'paths']
      , 'listFiles' => [__CLASS__, 'files']
      , 'listDirs' => [__CLASS__, 'dirs']
    ];

    /**
     * @return string
     */   
    public static function root($pathRelative) {
        return $_SERVER['DOCUMENT_ROOT'] . static::lslash($pathRelative);
    }
    
    /**
     * @return string
     */
    public static function dir($pathRelative) {
        return __DIR__ . static::lslash($pathRelative);
    }
    
    /**
     * @return string|bool
     */
    public static function ext($path, $add = null) {
        # get basename, remove any query params, get chars starting at last dot:
        if (null === $add) return \strrchr(\strtok(\basename($path), '?'), '.');
        $add = '.' . \ltrim($add, '.'); # add to path if missing
        return \basename($path) === \basename($path, $add) ? \rtrim($path, '.') . $add : $path;
    }
    
    /**
     * @return string
     */
    public static function filename($path) {
        return \pathinfo($path, PATHINFO_FILENAME);
    }
    
    /**
     * @return bool
     */
    public static function inc($path) {
        ($bool = static::isFile($path)) and include $path;
        return $bool;
    }
    
    /**
     * @return bool
     */
    public static function isPath($item) {
        return \is_scalar($item) && \file_exists($item);
    }
    
    /**
     * @return bool
     */
    public static function isDir($item) {
        return \is_scalar($item) && \is_dir($item);
    }
    
    /**
     * @return bool
     */
    public static function isFile($item) {
        return \is_scalar($item) && \is_file($item);
    }

    /**
     * @return bool
     */
    public static function isDot($item) {
        return \in_array(\basename($item), array('.', '..'));
    }
    
    /**
     * @return bool
     */
    public static function isAbs($item) {
        return \is_scalar($item) && \realpath($item) === $item;
    }
    
    /**
     * @return string|array|bool
     */
    public static function toAbs($path) {
        if (\is_array($path)) return \array_map(array(__CLASS__, __FUNCTION__), $path); # recurse
        if (\is_string($path) || \is_numeric($path)) return \realpath($path); # resolve relative path
        return false;
    }

    /**
     * @param string $path 
     * @param string $scheme 
     * @return string
     */
    public static function toUri($path = '', $scheme = null) {
        $uri = ($scheme && \is_string($scheme) ? $scheme . '://' : '//') . $_SERVER['SERVER_NAME'];
        return $uri . static::lslash(\str_replace($_SERVER['DOCUMENT_ROOT'], '', $path));
    }
    
    /**
     * @param string $path 
     * @param string $scheme 
     * @return string
     */
    public static function toUrl($path, $scheme = null) {
        \is_string($scheme) or $scheme = static::isHttps() ? 'https' : 'http';
        return static::toUri($path, $scheme);
    }
    
    /**
     * @return bool
     */
    public static function isHttps() {
        return !empty($_SERVER['HTTPS']) and 'off' !== \strtolower($_SERVER['HTTPS'])
            or !empty($_SERVER['SERVER_PORT']) and 443 == $_SERVER['SERVER_PORT'];
    }
    
    /**
     * @return array
     */
    public static function scan($path = '.') {
        $list = array();
        foreach (\scandir($path) as $n)
            static::isDot($n) or $list[] = static::join($path, $n);
        return $list; # shallow
    }
    
    /**
     * @return array
     */
    public static function paths($path = '.') {
        $list = array();
        foreach (new RII(new RDI($path), RII::SELF_FIRST) as $splfileinfo)
            static::isDot($path = $splfileinfo->getPathname()) or $list[] = $path;
        return $list; # deep
    }
    
    /**
     * @return array
     */
    public static function files($path = '.') {
        return \array_filter(static::paths($path), 'is_file');
    }
    
    /**
     * @return array
     */
    public static function dirs($path = '.') {
        return \array_filter(static::paths($path), 'is_dir');
    }

    /**
     * @return array associative array containing the dir structure
     */
    public static function tree($path = '.') {
        $list = array();
        foreach (\is_array($path) ? $path : static::scan($path) as $n)
            \is_dir($n) ? $list[$n] = static::tree($n) : $list[] = $n;
        return $list;
    }
    
    /**
     * @param string $path dir or file
     * @param string $format date string for use with date()
     * @return int|string|null modified time of file or most recent file in dir
     */
    public static function mtime($path, $format = null) {
        $time = static::files($path);
        $time = $time ? \max(\array_map('filemtime', static::affix($time, static::rslash($path)))) : null;
        return $format && $time ? \date($format, $time) : $time;
    }
    
    /**
     * @return array
     */
    public static function walk($path = '.', callable $fn = null) {
        $trav = \is_scalar($path) ? static::paths($path) : $path;
        foreach ($trav as $k => $v)
            if (false === \call_user_func($fn, $v, $k, $trav)) break;
        return $trav;
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
     * @param string $path
     * @param string $infix text to insert before file extension
     * @return string
     */
    public static function infix($path, $infix) {
        return \preg_replace('#(\.\w+)$#', "$infix$1", $path);
    }
    
    /**
     * @return int
     */
    public static function depth($path) {
        return \substr_count($path, '/') + \substr_count($path, '\\');
    }

    /**
     * @return array
     */
    public static function tier(array $list) {
        $levels = \array_map(static::method('depth'), $list);
        $groups = \array_pad(array(), \max($levels), array()); # ordered and non-sparse
        foreach ($list as $k => $v)
            $groups[$levels[$k]][] = $v;
        return $groups;
    }

    /**
     * @return array
     */
    public static function sort(array $list) {
        return \call_user_func_array('array_merge', static::tier($list));
    }
    
    /**
     * Get the first existent path from the supplied args.
     * @param array|string $needles
     */
    public static function locate($needles) {
        return static::find(\is_array($needles) ? $needles : \func_get_args(), static::method('exists'));
    }
    
    /**
     * @param string|array|object $haystack
     * @param string $needle
     * @return bool
     */
    public static function contains($haystack, $needle) {
        if (\is_scalar($haystack))
            return false !== \strpos($haystack, $needle);
        foreach ((array) $haystack as $v)
            if (self::contains($v, $needle)) return true;
        return false;
    }
    
    /**
     * @param string|array|object $path
     * @param string|array $needles
     * @return array
     */
    public static function search($path, $needles) {
        $result = array();
        \is_array($needles) or $needles = \array_slice(\func_get_args(), 1);
        foreach (\is_scalar($path) ? static::scan($path) : $path as $v)
            foreach ($needles as $needle)
                static::contains($v, $needle) and $result[] = $v;
        return $result;
    }
    
    /**
     * @param array|object $list
     * @param callable $test
     */
    public static function find($list, callable $test) {
        foreach ($list as $k => $v)
            if (\call_user_func($test, $v, $k, $list)) return $v;
    }
}