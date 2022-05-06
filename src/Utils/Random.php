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

class Random
{
    public static function string(int $length): string
    {
        return $length & 1 ?
        substr(bin2hex(openssl_random_pseudo_bytes(++$length / 2)), 1)
        :
        bin2hex(openssl_random_pseudo_bytes($length / 2));
    }

    public static function bytes(int $length)
    {
        return openssl_random_pseudo_bytes($length);
    }
}
