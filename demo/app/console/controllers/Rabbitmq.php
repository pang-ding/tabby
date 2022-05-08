<?php

use Tabby\Middleware\Queue\Drivers\BasicQueueRabbitMQ;

/*
 * This file is part of the Tabby package.
 *
 * (c) Lv Liangyu <lv_liangyu@yeah.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class RabbitmqController extends \Tabby\Framework\Ctrl
{
    // ./app/console/daemon_start.sh /rabbitmq/receive "queue=tabby_test"
    public function receiveAction(\Tabby\Framework\Request\CliRequest $req)
    {
        Vali::mergeRules(
            [
                'queue'=> 'str|between:1,30',
            ]
        );

        \T::$Log->info("RabbitMQ Receive: '{$req['queue']}'");
        /**
         * @var BasicQueueRabbitMQ
         */
        $rmq = \T::$DI['rmq'];
        $rmq->receive($req['queue'], function ($msg, $msgObj) {
            \T::$Log->info($msg . ':' . $msgObj->getBodySize());

            return true;
        });
    }

    // php -c ./conf/php_dev.ini ./app/console/entry.php -r "/rabbitmq/publish" -d "queue=tabby_test&msg=2"
    public function publishAction(\Tabby\Framework\Request\CliRequest $req)
    {
        Vali::mergeRules(
            [
                'queue' => 'str|between:1,30',
                'msg'   => 'str|between:1,100',
            ]
        );

        /**
         * @var BasicQueueRabbitMQ
         */
        $rmq = \T::$DI['rmq'];
        $rmq->publish($req['queue'], $req['msg']);
    }
}
