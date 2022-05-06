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

use Tabby\Utils\ArrayUtils;

class Config extends \ArrayObject
{
    public function __construct(array $config)
    {
        parent::__construct($config);
    }

    /**
     * 读取配置
     *
     * @param string $path
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get(string $path, $default = null)
    {
        $rst = ArrayUtils::getByPath($this, $path, $default);
        if ($rst === null) {
            throw new \ErrorSys("Tabby Error: Config '{$path}' not found");
        }

        return $rst;
    }

    /**
     * 配置是否存在
     *
     * @param string $path
     *
     * @return bool
     */
    public function exists(string $path): bool
    {
        return ArrayUtils::existsByPath($this, $path);
    }

    /**
     * 针对bool型配置
     *
     * @param string $path
     *
     * @return bool
     */
    public function isTrue(string $path): bool
    {
        return !empty($this->get($path, false));
    }
}
