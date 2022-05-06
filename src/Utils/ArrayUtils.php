<?php

/*
 * This file is part of the Tabby package.
 *
 * (c) Lv Liangyu <lv_liangyu@yeah.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tabby\Utils;

use Tabby\Error\ErrorSys;

class ArrayUtils
{
    public static function getByPath($array, string $path, $default = null)
    {
        $quote = $array;
        foreach (explode('.', $path) as $v) {
            if (!(is_array($quote) || $quote instanceof \ArrayObject) or !array_key_exists($v, $quote)) {
                return $default;
            }
            $quote = $quote[$v];
        }

        return $quote;
    }

    public static function existsByPath($array, string $path)
    {
        $quote = $array;
        foreach (explode('.', $path) as $v) {
            if (!(is_array($quote) || $quote instanceof \ArrayObject) or !array_key_exists($v, $quote)) {
                return false;
            }
            $quote = $quote[$v];
        }

        return true;
    }

    public static function replaceKey($array, array $replace, bool $keepOrder = false)
    {
        // TODO: 太乱, 优化
        if ($array === null) {
            return null;
        }
        if (!is_array($array)) {
            if ($array instanceof \ArrayAccess && $array instanceof \Traversable) {
                if ($keepOrder) {
                    $array = (array) $array;
                }
            } else {
                throw new ErrorSys('ArrayUtils::replaceKey() Error: Argument $array must be a array');
            }
        }
        foreach ($replace as $k => $v) {
            if (!is_string($k) || !is_string($v) || $k === '' || $v === '' || array_key_exists($v, $array)) {
                throw new ErrorSys("ArrayUtils::replaceKey() Error: Argument \$replace error['{$k}'=>'{$v}']");
            }
        }
        if ($keepOrder) {
            $originKeys = array_keys($array);
            $intersect  = array_intersect(array_keys($replace), $originKeys);
            foreach ($intersect as $k => $v) {
                $originKeys[$k] = $replace[$v];
            }

            return array_combine($originKeys, array_values($array));
        } else {
            foreach ($replace as $k => $v) {
                if (array_key_exists($k, $array)) {
                    $array[$v] = $array[$k];
                    unset($array[$k]);
                }
            }

            return $array;
        }
    }
}
