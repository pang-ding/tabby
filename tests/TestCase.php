<?php

/*
 * This file is part of the Tabby package.
 *
 * (c) Lv Liangyu <lv_liangyu@yeah.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tabby\Test;

class TestCase extends \PHPUnit\Framework\TestCase
{
    //不愿意用注释方法可以用这个
    public function assertException($target, \Closure $actual)
    {
        $exception = 'NULL';
        $rst = false;
        try {
            $actual();
        } catch (\Throwable $e) {
            $exception = $e;
            $rst = $e instanceof $target;
        }
        $this->assertTrue($rst, "assertException失败:'" . $exception . "',期待:'{$target}'");
    }

    public static function getValue($argument, $name)
    {
        $property = static::publicProperty($argument, $name);
        if ($property->isStatic()) {
            return $property->getValue();
        } else {
            return $property->getValue($argument);
        }
    }

    public static function setValue($argument, string $name, $value): void
    {
        $property = static::publicProperty($argument, $name);
        if ($property->isStatic()) {
            $property->setValue($value);
        } else {
            $property->setValue($argument, $value);
        }
    }

    /**
     * 获取类的一个属性的相关信息
     *
     * @param mixed $argument
     * @param string $name
     * @return \ReflectionProperty
     */
    public static function getProperty($argument, string $name): \ReflectionProperty
    {
        return (new \ReflectionClass($argument))->getProperty($name);
    }

    /**
     * 设置类的一个属性为public
     *
     * @param mixed $argument
     * @param string $name
     * @return \ReflectionProperty
     */
    public static function publicProperty($argument, string $name): \ReflectionProperty
    {
        $property = static::getProperty($argument, $name);
        if (!$property->isPublic()) {
            $property->setAccessible(true);
        }
        return $property;
    }

    /**
     * 执行一个方法(私有 or 保护)
     *
     * @param $object
     * @param string $method
     * @return mixed
     */
    public static function call($object, string $method, ...$args)
    {
        $method = new \ReflectionMethod($object, $method);
        $method->setAccessible(true);
        if ($method->isStatic()) {
            return $method->invoke(null, ...$args);
        }
        return $method->invoke($object, ...$args);
    }

    /**
     * 设置对象的一个方法为public
     *
     * @param $object
     * @param string $method
     */
    public static function publicMethod(Object $object, string $method)
    {
        $method = new \ReflectionMethod($object, $method);
        $method->setAccessible(true);
    }
}
