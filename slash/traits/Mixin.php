<?php
/**
 * @package ryanve/slash
 */
namespace slash\traits;

trait Mixin {
    public static function __callStatic($name, $params) {
        return \call_user_func_array(static::$mixin[$name], $params);
    }
    
    /**
     * @param string|array|object $name
     * @param callable $fn
     */
    public static function mixin($name, $fn = null) {
        if (\is_scalar($name)) {
            if (null !== $fn) return static::$mixin[$name] = $fn;
            return isset(static::$mixin[$name]) ? static::$mixin[$name] : null;
        }
        if ($assoc = \is_object($name) ? static::methods($name) : $name)
            static::$mixin = \array_replace(static::$mixin, $assoc);
        return static::$mixin;
    }

    /**
     * @param string $name
     * @return callable fully-qualified method
     */
    public static function method($name) {
        return [\get_called_class(), $name];
    }
    
    /**
     * @param object|string $object defaults to called class
     * @return array associative
     */
    public static function methods($object = null) {
        $result = null === $object ? [] : static::mixin();
        null === $object and $object = \get_called_class();
        foreach (\get_class_methods($object) as $m)
            $result[$m] = [$object, $m];
        return $result;
    }
}