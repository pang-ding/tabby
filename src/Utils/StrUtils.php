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

use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\VarDumper\Dumper\AbstractDumper;

class StrUtils
{
    protected static $_varCloner = null;
    protected static $_cliDumper = null;

    /**
     * 下划线转驼峰
     */
    public static function camelize(string $subject, string $separator = '_'): string
    {
        return str_replace(' ', '', ucwords(str_replace($separator, ' ', trim(strtolower($subject)))));
    }

    /**
     * 驼峰转下划线
     */
    public static function uncamelize(string $subject, string $separator = '_'): string
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1' . $separator . '$2', $subject));
    }

    /**
     * 模板替换
     */
    public static function templateReplace(string $string, ?array $values = null, string $leftSign = '{{', string $rightSign = '}}'): string
    {
        if ($values === null || $string === '' || $values === []) {
            return $string;
        }

        return str_replace(
            explode(
                '|',
                $leftSign . implode($rightSign . '|' . $leftSign, array_keys($values)) . $rightSign
            ),
            $values,
            $string
        );
    }

    /**
     * 补全目录最后一个分隔符
     */
    public static function dirLastSeparator(string $dir): string
    {
        $dir = trim($dir);
        if ($dir[-1] !== '/') {
            $dir .= '/';
        }

        return $dir;
    }

    /**
     * 变量格式化
     */
    public static function var2str($var): string
    {
        if (static::$_cliDumper === null) {
            static::$_cliDumper = new CliDumper(null, null, AbstractDumper::DUMP_LIGHT_ARRAY);
            static::$_varCloner = new VarCloner();
        }

        return static::$_cliDumper->dump(static::$_varCloner->cloneVar($var), true);
    }
}
