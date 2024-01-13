<?php

/*
 * This file is part of the Tabby package.
 *
 * (c) Lv Liangyu <lv_liangyu@yeah.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tabby;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\ChromePHPHandler;
use Monolog\Handler\SyslogHandler;
use Monolog\Logger as MonoLogger;
use Tabby\Framework\Config;
use Tabby\Utils\Timer;

class Tabby
{
    /**
     * Yaf调度器
     *
     * @var \Yaf\Dispatcher
     */
    public static $Disp;

    /**
     * Config
     *
     * @var Config
     */
    public static $Conf;

    /**
     * Logger
     *
     * @var \Psr\Log\LoggerInterface
     */
    public static $Log;

    /**
     * DI宿主
     *
     * @var \Tabby\Framework\DI
     */
    public static $DI;

    /**
     * 输入实例
     *
     * @var \Tabby\Framework\Request\AbstractRequest
     */
    public static $REQ;

    /**
     * \Yaf\Request_Abstract
     *
     * @var \Yaf\Request_Abstract
     */
    public static $YAF_REQ;

    /**
     * 输出实例
     *
     * @var \Tabby\Framework\Response
     */
    public static $RSP;

    /**
     * 全局计时器
     *
     * @var Timer
     */
    public static $TIMER;

    /**
     * 是否CLI模式
     *
     * @var bool
     */
    public static $isCli;

    /**
     * 是否Debug模式
     *
     * @var bool
     */
    public static $isDebug;

    /**
     * 业务上下文
     *
     * @var array
     */
    public static $context = [];

    protected static $_iniConf;

    public static function init(?\Psr\Log\LoggerInterface $logger = null, ?array $config = null)
    {
        // 全局计时器
        self::$TIMER = new Timer();

        // 配置信息
        self::$Conf = new Config($config === null ? self::getIniConf() : $config);

        // isDebug
        self::$isDebug = self::$Conf->get('tabby.isDebug', false);

        // isCli
        self::$isCli = self::$Conf->get('tabby.isCli', false);

        // Logger
        self::$Log = $logger === null ? static::defaultLog() : $logger;

        // Yaf调度器
        static::$Disp = \Yaf\Dispatcher::getInstance();

        // Yaf_Request
        static::$YAF_REQ = static::$Disp->getRequest();

        static::$DI = \Tabby\Framework\DI::getIns();

        // 初始化 Validator
        \Tabby\Validator\Validate::init();

        // 输出
        self::$RSP = \Tabby\Framework\Response::getIns();
        static::$Disp->setView(self::$RSP);
        static::$Disp->autoRender(true);
        static::$Disp->returnResponse(true);

        // 输入 #放弃自动判断, 配置决定是否 Cli 模式
        if (self::$isCli) {
            $args = getopt('r:d:');

            self::$YAF_REQ->setRequestUri($args['r']);

            /**
             * @var \Tabby\Framework\Request\CliRequest
             */
            self::$REQ = \Tabby\Framework\Request\CliRequest::getIns();
            parse_str($args['d'], $req);
            self::$REQ->setData($req);

            // 默认不输出内容
            self::$RSP->setDefaultRender(\Tabby\Framework\Response::RENDER_NONE);
        } else {
            self::$REQ = \Tabby\Framework\Request\HttpRequest::getIns();
        }

        // Tabby Plugin
        static::$Disp->registerPlugin(new \Tabby\Framework\Plugin());
    }

    public static function getConf($path, $default = null)
    {
        return self::$Conf->get($path, $default);
    }

    public static function getIniConf()
    {
        if (!self::$_iniConf) {
            self::$_iniConf = (\Yaf\Dispatcher::getInstance())->getApplication()->getConfig()->toArray();
        }

        return self::$_iniConf;
    }

    protected static function defaultLog()
    {
        $formatter = new LineFormatter('[%level_name%]['.self::$Conf['app']['name'].'] %message%', null, self::$isDebug);
        $handler   = new SyslogHandler(self::$Conf['prj']['name'].'/'.self::$Conf['app']['name'], LOG_LOCAL6);
        $handler->setFormatter($formatter);
        $handlers = [$handler];
        if (self::$isDebug && !self::$isCli) {
            $handlers[] = new ChromePHPHandler(MonoLogger::INFO, false);
        }

        return new MonoLogger(self::$Conf['prj']['name'], $handlers);
    }

    public static function getVersion()
    {
        $composer = json_decode(file_get_contents(dirname(__DIR__) . '/composer.json'), true);

        return $composer['version'];
    }
}
