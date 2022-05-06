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

use Tabby\Error\ErrorSys;
use Tabby\Tabby;

class Language
{
    protected static $_ins = null;

    /*
     * 语言包存储路径
     */
    protected $_path;

    /*
     * 语言包缓存
     */
    protected $_packages;

    protected function __construct()
    {
        $this->_path = Tabby::$Conf['tabby']['langPath'];
        if (empty($this->_path)) {
            throw new ErrorSys("Language Error: System config 'app.Path' must be set");
        }
        $this->_packages = [];
    }

    /**
     * 单例
     *
     * @return static
     */
    public static function getIns()
    {
        if (static::$_ins === null) {
            static::$_ins = new static();
        }

        return static::$_ins;
    }

    public function getPackage(string $name): LanguagePackage
    {
        if (!isset($this->_packages[$name])) {
            if (empty($name)) {
                throw new ErrorSys('Language Error: Package name cannot be empty');
            }
            $file = $this->_path . $name . '.php';
            if (!file_exists($file)) {
                throw new ErrorSys("Language Error: File not found (path='{$file}')");
            }

            try {
                $langData = include $this->_path . $name . '.php';
            } catch (\Throwable $e) {
                throw new ErrorSys("Language Error: Failed opening (path='{$file}')");
            }
            if (!is_array($langData)) {
                throw new ErrorSys("Language Error: Result must be an array (path='{$file}'");
            }
            $this->_packages[$name] = new LanguagePackage($langData, $name);
        }

        return $this->_packages[$name];
    }

    /**
     * 从语言包中获取信息
     *
     * @param string $packageName 语言包名称
     * @param string $key         语言包键
     * @param array  $values      消息参数 StrUtils::templateReplace(.., $values)
     * @param string $default     默认值 语言包中不存在 键($key) 时返回默认值, 注意: 语言包本身不存在会报错
     *
     * @return string
     */
    public static function getMsg(string $packageName, string $key, array $values = null, string $default = null)
    {
        return static::getIns()->getPackage($packageName)->get($key, $values, $default);
    }

    /**
     * 检查字符串是否是语言标签('{pkg.field}')
     * 是: 则返回语言包中的信息
     * 否: 返回原字符串
     *
     * @param string $string 语言包path 或 消息字符串
     * @param array  $values 消息参数 用 StrUtils::templateReplace 替换消中的标识符
     *
     * @return string
     */
    public static function change(string $string, ?array $values = null)
    {
        if ($string[0] === '{' && $string[-1] === '}') {
            $path = explode('.', substr($string, 1, -1), 2);
            if (count($path) !== 2) {
                throw new ErrorSys("Language: Change '{$string}' failed");
            }

            return static::getIns()->getPackage($path[0])->get($path[1], $values);
        }

        return $string;
    }
}
