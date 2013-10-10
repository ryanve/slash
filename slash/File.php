<?php
/**
 * @package ryanve/slash
 */
namespace slash;

class File {
    use traits\Mixin;
    protected static $mixin = [
        'exists' => '\\slash\\Path::isFile'
      , 'get' => [__CLASS__, 'getFile']
      , 'put' => [__CLASS__, 'putFile']
      , 'load' => [__CLASS__, 'loadFile']
    ];
    
    /**
     * @param mixed $fn
     */
    protected static function done($fn) {
        return null === \array_shift($a = \func_get_args()) ? \array_shift($a) : \call_user_func_array($fn, $a);
    }

    /**
     * @param string $path
     * @param callable $fn
     */
    public static function getFile($path, callable $fn = null) {
        return static::done($fn, Path::isFile($path) ? \file_get_contents($path) : false);
    }

    /**
     * @param string $path
     * @param callable $fn
     */
    public static function putFile($path, $data) {
        if (null === $path) return false;
        $data instanceof \Closure and $data = $data(static::getJson($path));
        return \file_put_contents($path, $data);
    }

    /**
     * @param string $path
     * @param callable $fn
     */
    public static function getJson($path, callable $fn = null) {
        return static::done($fn, \is_scalar($path) ? \json_decode(\file_get_contents($path)) : $path);
    }

    /**
     * @param string $path
     * @param callable $data
     */
    public static function putJson($path, $data) {
        if (null === $path) return false;
        $data instanceof \Closure and $data = $data(static::getJson($path));
        return \file_put_contents($path, \is_string($data) ? $data : \json_encode($data));
    }

    /**
     * @param string $path
     * @param callable $fn
     */
    public static function loadFile($path, callable $fn = null) {
        $text = false;
        if (Path::isFile($path)) {
            \ob_start(); 
            include $path;
            $text = \ob_get_contents();
            \ob_end_clean();
        }
        return static::done($fn, $text);
    }
}