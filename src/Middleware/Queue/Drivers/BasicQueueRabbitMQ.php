<?php

/*
 * This file is part of the Tabby package.
 *
 * (c) Lv Liangyu <lv_liangyu@yeah.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tabby\Middleware\Queue\Drivers;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class BasicQueueRabbitMQ extends \Tabby\Middleware\Queue\BasicQueueAbstract
{
    private $_connConfig;
    /**
     * @var AMQPStreamConnection
     */
    private $_conn = null;
    /**
     * @var AMQPChannel
     */
    private $_channel           = null;
    private $_exchange          = '';
    private $_messageProperties = [];
    private $_consumeConfig     = [];
    private $_publishAck        = false;
    private $_publishAckRst     = null;
    private $_publishAckTimeout = false;

    public function __construct(array $connConfig)
    {
        $this->_connConfig = $connConfig;
    }

    /**
     * 启用 Publish ACK (只接收ack & nack, 不处理 result)
     *
     * @param float $_publishAckTimeout
     */
    public function publishAck(float $publishAckTimeout = 3.0): void
    {
        $this->_publishAck        = true;
        $this->_publishAckTimeout = $publishAckTimeout;
    }

    /**
     * Exchange Name
     *
     * @param string $name
     */
    public function setExchange(string $name): void
    {
        $this->_exchange = $name;
    }

    /**
     * AMQPMessage 参数
     *
     * @param array $properties
     */
    public function setMessageProperties(array $properties): void
    {
        $this->_messageProperties = $properties;
    }

    /**
     * basic_consume 参数
     *
     * @param array $consumeConfig
     */
    public function setConsumeConfig(array $consumeConfig): void
    {
        $this->_consumeConfig = $consumeConfig;
    }

    /**
     * 发消息 (生产)
     *
     * @param string $queue
     * @param string $msg
     */
    public function publish(string $queue, string $msg)
    {
        $this->_publishAckRst = null;

        $msg = new AMQPMessage($msg, $this->_messageProperties);

        $channel = $this->getChannel();
        $channel->basic_publish($msg, $this->_exchange, $queue);

        if ($this->_publishAck) {
            $channel->wait_for_pending_acks_returns($this->_publishAckTimeout);

            return $this->_publishAckRst;
        }

        return true;
    }

    /**
     * 收消息 (消费)
     *
     * @param string   $queue
     * @param callable $callback
     */
    public function receive(string $queue, callable $callback): void
    {
        $this->getChannel()->basic_consume(
            $queue,
            $this->_consumeConfig['consumer_tag'] ?? '',
            $this->_consumeConfig['no_local'] ?? false,
            $this->_consumeConfig['no_ack'] ?? false,
            $this->_consumeConfig['exclusive'] ?? false,
            $this->_consumeConfig['nowait'] ?? false,
            function ($msg) use ($callback) {
                if ($callback($msg->getBody(), $msg)) {
                    $msg->ack();
                } else {
                    $msg->reject(true);
                }
            },
            $this->_consumeConfig['ticket'] ?? null,
            $this->_consumeConfig['arguments'] ?? []
        );
        while (count($this->_channel->callbacks)) {
            $this->_channel->wait();
        }
    }

    public function getChannel()
    {
        if ($this->_conn === null) {
            $this->_conn = new AMQPStreamConnection(
                $this->_connConfig['host'],
                $this->_connConfig['port'],
                $this->_connConfig['user'],
                $this->_connConfig['password'],
                $this->_connConfig['vhost'] ?? '/',
                $this->_connConfig['insist'] ?? false,
                $this->_connConfig['login_method'] ?? 'AMQPLAIN',
                $this->_connConfig['login_response'] ?? null,
                $this->_connConfig['locale'] ?? 'en_US',
                $this->_connConfig['connection_timeout'] ?? 3.0,
                $this->_connConfig['read_write_timeout'] ?? 3.0,
                $this->_connConfig['context'] ?? null,
                $this->_connConfig['keepalive'] ?? false,
                $this->_connConfig['heartbeat'] ?? 0,
                $this->_connConfig['channel_rpc_timeout'] ?? 0.0,
                $this->_connConfig['ssl_protocol'] ?? null,
            );
        }
        if ($this->_channel === null) {
            $this->_channel = $this->_conn->channel();
            if ($this->_publishAck) {
                $this->_channel->confirm_select();

                $this->_channel->set_ack_handler(function (AMQPMessage $msg) {
                    $this->_publishAckRst = true;
                });

                $this->_channel->set_nack_handler(function (AMQPMessage $msg) {
                    $this->_publishAckRst = false;
                });
            }
        }

        return $this->_channel;
    }

    public function close()
    {
        $this->_channel->close();
        $this->_channel = null;
        $this->_conn->close();
        $this->_conn = null;
    }
}
