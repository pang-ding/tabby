<?php

use Consts\DiConsts;
use Tabby\Utils\StrUtils;

class Utils
{
    public static function getUUID()
    {
        return uuid_create(1);
    }

    /**
     * @return Tabby\Store\Mysql\DB
     */
    public static function db()
    {
        return \T::$DI[DiConsts::DI_MYSQL];
    }

    /**
     * @return Tabby\Middleware\Cache\CacheRedis
     */
    public static function cache()
    {
        return \T::$DI[DiConsts::DI_CACHE];
    }
}

function var2str($var)
{
    return StrUtils::var2str($var);
}
