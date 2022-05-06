<?php

/*
 * This file is part of the Tabby package.
 *
 * (c) Lv Liangyu <lv_liangyu@yeah.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Svc;

class RenderSvc
{
    /**
     * @var \League\Plates\Engine
     */
    public static $render;
    protected static $_registed = false;

    public static function init()
    {
        $driver       = new \Tabby\Framework\Render\Drivers\DriverPlates();
        self::$render = $driver->getPlates();
        \Tabby\Framework\Render\RenderTpl::setDriver($driver);
        $driver->setPrepare(function (&$data) {
            self::prepare($data);
        });
    }

    public static function prepare(&$data)
    {
        if (static::$_registed) {
            return ;
        }
        static::$_registed = true;

        self::$render->registerFunction(
            'getCode',
            function (string $class, string $function = '') {
                if ($function) {
                    $path = $class . '::' . $function;
                    $r    = new \ReflectionMethod($class, $function);
                } else {
                    $path = $class;
                    $r    = new \ReflectionClass($class);
                }
                $f    = new \SplFileObject($r->getFileName());
                $f->seek($r->getStartLine() - 1);
                $code = '';
                while ($f->key() < $r->getEndLine()) {
                    $code .= $f->current();
                    $f->next();
                }

                $rst = "<div class=\"container mx-3\"><mark><strong class=\"text-muted \">&lt;{$path}&gt;</strong></mark><small class=\"float-right text-muted\">" . $r->getFileName() . '</small>';
                $rst .= "<pre><code class=\"language-php\">{$code}</code></pre></div>";

                return $rst;
            }
        );
    }
}
