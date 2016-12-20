<?php
namespace AppBundle\Patterns;
/**
 * Singleton class
 */
abstract class Singleton
{
    private static $instances;
    private function __construct()
    {
    }
    final public static function getInstance()
    {
        $className = get_called_class();
        if(isset(self::$instances[$className]) == false)
        {
            self::$instances[$className] = new static();
        }
        return self::$instances[$className];
    }
    /**
     * Prevents from cloning.
     *
     * @return void
     */
    final private function __clone()
    {
        throw new Exception('Singleton cannot be cloned.');
    }
    /**
     * Prevents from unserializing.
     *
     * @return void
     */
    final private function __wakeup()
    {
        throw new Exception('Singleton cannot be unserialized.');
    }
}
