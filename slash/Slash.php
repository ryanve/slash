<?php
/**
 * @package ryanve/slash
 */
namespace slash;

class Slash {
  use traits\Mixin;
  protected static $mixin = [];
  const glue = '/';
  const trim = '/\\';
  const pattern = '#/|\\\#';
  
  /**
   * @return string
   */   
  public static function trim($str) {
    return trim($str, static::trim);
  }
  
  /**
   * @return string
   */   
  public static function ltrim($str) {
    return ltrim($str, static::trim);
  }
  
  /**
   * @return string
   */   
  public static function rtrim($str) {
    return rtrim($str, static::trim);
  }
  
  /**
   * @return string
   */
  public static function slash($path) {
    return static::glue . static::trim($path) . static::glue;
  }
  
  /**
   * @return string
   */
  public static function lslash($path) {
    return static::glue . static::ltrim($path);
  }
   
  /**
   * @return string
   */   
  public static function rslash($path) {
    return static::rtrim($path) . static::glue;
  }
  
  /**
   * @return string joined parts
   */
  public static function join() {
    $s = '';
    foreach (func_get_args() as $n) $s = $s ? static::rtrim($s) . static::glue . static::ltrim($n) : $n;
    return $s;
  }
  
  /**
   * @param string $path
   * @return string|array
   */
  public static function normalize($path) {
    return preg_replace(static::pattern, static::glue, $path);
  }

  /**
   * @return array
   */
  public static function split($path) {
    return preg_split(static::pattern, $path);
  }
}