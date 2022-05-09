<?php

/*
 * This file is part of the Tabby package.
 *
 * (c) Lv Liangyu <lv_liangyu@yeah.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tabby\Middleware\Queue;

abstract class BasicQueueAbstract
{
    abstract public function publish(string $queue, string $msg);

    abstract public function receive(string $queue, callable $receive): void;
}
