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

class CommonRules extends AbstractRules
{
    protected static function data()
    {
        return [
            'page_num'  => 'int|min:0|max:1000',
            'page_size' => 'int|min:0|max:1000',
            'enable'    => 'int|between:0,1',
        ];
    }
}
