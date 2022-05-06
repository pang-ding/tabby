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

use ErrorSys;
use Tabby\Error\ErrorCapture;
use Tabby\Framework\Render\RenderTpl;
use Tabby\Framework\Render\RenderEcho;
use Tabby\Framework\Render\RenderJson;
use Tabby\Framework\Render\RenderNone;
use Tabby\Framework\Render\RenderJsonp;

class Response implements \ArrayAccess, \Yaf\View_Interface
{
    const RENDER_NONE  = 'none';
    const RENDER_TPL   = 'tpl';
    const RENDER_ECHO  = 'echo';
    const RENDER_JSON  = 'json';
    const RENDER_JSONP = 'jsonp';

    protected static $_ins       = null;

    /**
     * 待输出数据
     *
     * @var array
     */
    protected $_data = [];

    /**
     * 渲染器
     *
     * @var \Tabby\Framework\Render\RenderAbstract
     */
    protected $_render = null;

    /**
     * 异常
     *
     * @var \Throwable
     */
    protected $_exception = null;

    /**
     * Redirect URL
     *
     * @var string
     */
    protected $_redirect = '';

    protected function __construct()
    {
    }

    /**
     * @return Response
     */
    public static function getIns(): Response
    {
        if (self::$_ins === null) {
            self::$_ins = new self();
        }

        return self::$_ins;
    }

    /**
     * 输出Yaf_Dispatcher 调用该方法
     *
     */
    public function render($tpl, $tpl_vars = null)
    {
        //\T::$Log->warning(static::class . '::' . __FUNCTION__ . '_' . $this->_render . '_' . \Tabby\Utils\Random::string(10));
        if ($this->_render) {
            $this->_render::response($this);
        } else {
            \T::$Log->warning('Response: Render is not set');
            RenderNone::response($this);
        }
    }

    /**
     * 设置默认渲染器
     *
     */
    public function setDefaultRender(string $render): void
    {
        switch ($render) {
            case '':
                break;
            case static::RENDER_TPL:
                $this->_render = RenderTpl::class;

                break;
            case static::RENDER_ECHO:
                $this->_render = RenderEcho::class;

                break;
            case static::RENDER_JSON:
                $this->_render = RenderJson::class;

                break;
            case static::RENDER_JSONP:
                $this->_render = RenderJsonp::class;

                break;
            case static::RENDER_NONE:
                $this->_render = RenderNone::class;

                break;
            default:
                throw new ErrorSys("Response: Unknown render type: {$render}");
        }
    }

    /**
     * 通过模板渲染器输出
     *
     */
    public function tpl(string $tpl): void
    {
        RenderTpl::setTpl($tpl);
        $this->_render = RenderTpl::class;
    }

    /**
     * 输出 Json 数据
     *
     */
    public function json(): void
    {
        $this->_render = RenderJson::class;
    }

    /**
     * 输出 JsonP 数据
     *
     */
    public function jsonp(): void
    {
        $this->_render = RenderJsonp::class;
    }

    /**
     * 输出 字符串
     *
     */
    public function echo(string $content): void
    {
        $this->_data[RenderEcho::CONTENT_KEY] = $content;
        $this->_render                        = RenderEcho::class;
    }

    /**
     * 不输出数据
     *
     */
    public function none(): void
    {
        $this->_render = RenderNone::class;
    }

    /**
     * 跳转(302)
     *
     */
    public function redirect(string $url, bool $now = true): void
    {
        $this->_redirect = $url;
        if ($now === true) {
            $this->render('');
        }
    }

    /**
     * 取得所有待输出数据
     *
     * @return array
     */
    public function getAll(): array
    {
        return $this->_data;
    }

    /**
     * 覆盖输出数据
     *
     */
    public function reset(array $data): void
    {
        $this->_data = $data;
    }

    /**
     * 合并输出数据
     *
     */
    public function merge(array $data): void
    {
        $this->_data = array_merge($this->_data, $data);
    }

    /**
     * 清除所有待输出数据
     *
     */
    public function clear(): void
    {
        $this->_data = [];
    }

    /**
     * 设置用于输出的异常
     *
     */
    public function setException(\Throwable $e): void
    {
        $this->_exception = $e;
    }

    /**
     * 获取用于输出的异常
     *
     * @return ?\Throwable
     */
    public function getException(): ?\Throwable
    {
        return ErrorCapture::capture($this->_exception);
    }

    /**
     * 获取用于输出的异常
     *
     * @return string
     */
    public function getRedirect(): string
    {
        return $this->_redirect;
    }

    /**
     * 清除异常
     */
    public function clearException(): void
    {
        $this->_exception = null;
    }

    public function offsetSet($offset, $value): void
    {
        $this->_data[$offset] = $value;
    }

    public function offsetGet($offset)
    {
        return $this->_data[$offset] ?? null;
    }

    public function offsetExists($offset): bool
    {
        return isset($this->_data[$offset]);
    }

    public function offsetUnset($offset): void
    {
        unset($this->_data[$offset]);
    }

    public function assign($name, $value = null)
    {
    }

    public function display($tpl, $tpl_vars = null)
    {
    }

    public function setScriptPath($template_dir)
    {
    }

    public function getScriptPath()
    {
        return '';
    }
}
