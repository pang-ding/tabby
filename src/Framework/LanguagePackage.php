<?php

/*
 * This file is part of the Tabby package.
 *
 * (c) Lv Liangyu <lv_liangyu@yeah.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tabby\Framework;

use Tabby\Tabby;
use Tabby\Utils\StrUtils;

class LanguagePackage
{
    protected $_dict;
    protected $_name;

    public function __construct(array $dict, string $name)
    {
        $this->_dict = $dict;
        $this->_name = $name;
    }

    public function get(string $key, ?array $values = null, ?string $default = null): string
    {
        if (isset($this->_dict[$key])) {
            return $values === null ? $this->_dict[$key] : StrUtils::templateReplace($this->_dict[$key], $values);
        }
        if ($default === null) {
            Tabby::$Log->warning("LanguagePackage: {$this->_name}.{$key} not found");
            $default = '';
        }

        return $default === null ? '' : $default;
    }

    public function set(string $key, string $value): void
    {
        $this->_dict[$key] = $value;
    }

    public function exists(string $key): bool
    {
        return isset($this->_dict[$key]);
    }
}
