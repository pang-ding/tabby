# 语言包

```php
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
Lang::getMsg(string $packageName, string $key, array $values = null, string $default = null)

// 从 \T::$Conf->get('tabby.langPath') 目录下 foo.php 文件取得 下标为 bar 的字符串
// 如字符串等于: "FOO MESSAGE {{v1}} {{v2}}", 则返回: "FOO MESSAGE ONE TWO"
Lang::getMsg('foo', 'bar', ['v1'=>'ONE','v2'=>'TWO']);
```
