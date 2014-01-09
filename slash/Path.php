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
    'exists' => [__CLASS__, 'isPath'],
    'listPaths' => [__CLASS__, 'paths'],
    'listFiles' => [__CLASS__, 'files'],
    'listDirs' => [__CLASS__, 'dirs']
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
   * @return int
   */
  public static function inc($path) {
    $count = 0;
    foreach ((array) $path as $n) static::isFile($n) and ++$count and include $n;
    return $count;
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
    return \in_array(\basename($item), ['.', '..']);
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
    if (\is_array($path)) return \array_map([__CLASS__, __FUNCTION__], $path); # recurse
    if (\is_string($path) || \is_numeric($path)) return \realpath($path); # resolve relative path
    return false;
  }

  /**
   * @param string $path 
   * @param string $scheme defaults to none (protocol-relative)
   * @return string
   */
  public static function toUri($path = '', $scheme = null) {
    $scheme = \is_string($scheme) ? \rtrim($scheme, ':') : null;
    $uri = ($scheme ? $scheme . '://' : '//') . $_SERVER['SERVER_NAME'];
    return $uri . static::lslash(\str_replace($_SERVER['DOCUMENT_ROOT'], '', $path));
  }
  
  /**
   * @param string $path
   * @param string $scheme defaults to server type (https or http)
   * @return string
   */
  public static function toUrl($path = '', $scheme = null) {
    return static::toUri($path, \is_string($scheme) ? $scheme : (static::isHttps() ? 'https' : 'http'));
  }
  
  /**
   * @return bool
   */
  public static function isHttps() {
    return !empty($_SERVER['HTTPS']) and 'off' !== \strtolower($_SERVER['HTTPS'])
      or !empty($_SERVER['SERVER_PORT']) and 443 == $_SERVER['SERVER_PORT'];
  }
  
  /**
   * @param string $path
   * @return array
   */
  public static function scan($path = '.') {
    $list = [];
    foreach (\scandir($path) as $n)
      static::isDot($n) or $list[] = static::join($path, $n);
    return $list; # shallow
  }
  
  /**
   * @param string $path
   * @return array
   */
  public static function paths($path = '.') {
    $list = [];
    foreach (new RII(new RDI($path), RII::SELF_FIRST) as $splfileinfo)
      static::isDot($path = $splfileinfo->getPathname()) or $list[] = $path;
    return $list; # deep
  }
  
  /**
   * @param string|array $path
   * @return array
   */
  public static function files($path = '.') {
    if (static::isFile($path)) return [$path];
    return \array_filter(\is_array($path) ? $path : static::paths($path), 'is_file');
  }
  
  /**
   * @param string|array $path
   * @return array
   */
  public static function dirs($path = '.') {
    return \array_filter(\is_array($path) ? $path : static::paths($path), 'is_dir');
  }

  /**
   * @param string|array $path
   * @return array associative array containing the dir structure
   */
  public static function tree($path = '.') {
    $list = [];
    foreach (\is_array($path) ? $path : static::scan($path) as $n)
      \is_dir($n) ? $list[$n] = static::tree($n) : $list[] = $n;
    return $list;
  }
  
  /**
   * @param string $path dir or file
   * @return int modified time of file or most recent file in dir
   */
  public static function mtime($path = '.') {
    return \max(\array_map('filemtime', static::files($path)));
  }
  
  /**
   * @param string $path dir or file
   * @return int changed time of file or most recent file in dir
   */
  public static function ctime($path = '.') {
    return \max(\array_map('filectime', static::files($path)));
  }
  
  /**
   * @param string $path dir or file
   * @param string $format date string for use with date()
   * @return int accessed time of file or most recent file in dir
   */
  public static function atime($path = '.') {
    return \max(\array_map('fileatime', static::files($path)));
  }
  
  /**
   * @param string $path dir or file
   * @return int
   */
  public static function size($path = '.') {
    return \array_sum(\array_map('filesize', static::files($path)));
  }
  
  /**
   * @return array
   */
  public static function affix(array $list, $prefix = '', $suffix = '') {
    foreach ($list as &$n) $n = $prefix . $n . $suffix;
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
    $groups = \array_pad([], \max($levels), []); # ordered and non-sparse
    foreach ($list as $k => $v) $groups[$levels[$k]][] = $v;
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
    if (\is_scalar($haystack)) return false !== \strpos($haystack, $needle);
    foreach ((array) $haystack as $v) if (self::contains($v, $needle)) return true;
    return false;
  }
  
  /**
   * @param string|array|object $path
   * @param string|array $needles
   * @return array
   */
  public static function search($path, $needles) {
    $result = [];
    \is_array($needles) or $needles = \array_slice(\func_get_args(), 1);
    foreach (\is_scalar($path) ? static::paths($path) : $path as $v)
      foreach ($needles as $needle)
        static::contains($v, $needle) and $result[] = $v;
    return $result;
  }
  
  /**
   * @param string|array|object $path
   * @param callable $fn
   */
  public static function find($path = '.', callable $fn) {
    $trav = \is_scalar($path) ? static::paths($path) : $path;
    foreach ($trav as $k => $v) if (\call_user_func($fn, $v, $k, $trav)) return $v;
  }
}