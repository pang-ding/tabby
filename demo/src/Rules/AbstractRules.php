<?php

/*
 * This file is part of the Tabby package.
 *
 * (c) Lv Liangyu <lv_liangyu@yeah.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rules;

use Tabby\Validator\Validate;

abstract class AbstractRules
{
    abstract protected static function data();

    public static function uses(?array $fields = null)
    {
        $rules      = [];
        $customMsgs = [];
        $asserts    = [];
        $data       = static::data();

        foreach ($data as $field => $ruleConf) {
            if ($fields === null || in_array($field, $fields, true)) {
                if (is_array($ruleConf)) {
                    $rules[$field] = array_shift($ruleConf);
                    foreach ($ruleConf as $k => $v) {
                        if (is_int($k)) {
                            $customMsgs[$field] = $v;
                        } elseif (is_callable($v)) {
                            $asserts[$k] = $v;
                        } else {
                            $customMsgs[$k] = $v;
                        }
                    }
                } else {
                    $rules[$field] = $ruleConf;
                }
            }
        }
        if (!empty($asserts)) {
            Validate::mergeAssert($asserts);
        }
        if (!empty($customMsgs)) {
            Validate::mergeCustomMsg($customMsgs);
        }
        if (!empty($rules)) {
            Validate::mergeRules($rules);
        }
    }
}
