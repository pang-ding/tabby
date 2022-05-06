<?php

/*
 * This file is part of the Tabby package.
 *
 * (c) Lv Liangyu <lv_liangyu@yeah.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tabby\Utils;

final class Validate
{
    // PHP正则
    //   + 修饰符
    //     - i: 大小写不敏感
    //     - m: 多行匹配 (\n)
    //     - s: 使通配符(.)可以匹配换行符(\n)
    //     - x: 忽略匹配模式表达式里的空白字符(例如空格), 目的是让表达式方便阅读, 表达式可以用\s表示空白字符 (PHP官方文档看不懂, 靠谱的文档不好找)
    //     - U: 关闭贪婪模式
    //          'aabbcc' /a.*c/ => 'aabbcc'
    //          'aabbcc' /a.*c/U => 'aabbc'
    //          'xabababx' /a.*c/ => 'ababab'
    //          'xabababx' /a.*c/U => 'ab'

    // 正则转义字符
    // \f 换页
    // \n 换行
    // \r 回车
    // \R \n 或 \r 或 \r\n
    // \t 水平制表符
    // \d 数字 \D 取反
    // \s 空白字符(\t\n\f\r空格) \S 取反
    // \w [_0-9a-zA-Z] \W 取反

    // POSIX字符组
    //
    // alnum [0-9a-zA-Z] 字母和数字
    // alpha [a-zA-Z] 字母
    // ascii [x00-x7F] ASCII全集
    // blank [x09x20] [\t ] 水平制表符(\t), 空格
    // cntrl [x00-x1Fx7F] 控制字符 (不可打印字符)
    // digit [0-9] 数字
    // graph [\x21-\x7E] 空格之外的可打印字符
    // lower [a-z] 小写字母
    // print [\x20-\x7E] 可打印字符
    // punct [-!"#$%&'()*+,./:;<=>?@[]^_`{ | }~] 标点符号
    // space [x09-x0Dx20] [\t\n\v\f\r ] 水平制表符(\t), 换行(\n), 垂直制表符(\v), 换页(\f) 回车(\r), 空格
    // upper [A-Z] 大写字母
    // word  [_0-9a-zA-Z] 字母,数字和下划线
    // xdigit [0-9A-Fa-f] 十六进制字符

    // C标准空白字符
    //
    // ASCII_CHAR_HT x09  \t    水平制表符
    // ASCII_CHAR_LF x0A  \n    换行
    // ASCII_CHAR_VT x0B  \v    垂直制表符
    // ASCII_CHAR_FF x0C  \f    换页
    // ASCII_CHAR_CR x0D  \r    回车
    // ASCII_CHAR_SPACE  x20  空格

    // 常用扩展集字符
    //
    // xA0 不间断空白符 nbsp
    // xA5 ¥ yan
    // xD7 × 乘号
    // xF7 ÷ 除号
    // \x{3000} Unicode 全角空格
    // [\x{4e00}-\x{9fa5}] 中文
    // [\x{3000}\x{ff01}-\x{ff5e}] 全角可打印字符 [x20-x7E]

    // domain: "([0-9a-zA-Z]([-0-9a-zA-Z]{0,61}[0-9a-zA-Z])?\.)+[a-zA-Z]([-0-9a-zA-Z]{0,17}[a-zA-Z])?";
    // domain简化: "[-a-zA-Z0-9]{0,62}(\.[-a-zA-Z0-9]{0,62})+";

    public static function isEmail(string $value): bool
    {
        // filter_var($value, FILTER_VALIDATE_EMAIL) 实现了 RFC 5322 规范太宽泛支持单引号, 没法用
        // 国内邮箱服务 账号一般低于20个字母, gmail30个
        if (empty($value) || strlen($value) > 100) {
            return false;
        }

        return preg_match('/^[-_\.a-z0-9]{1,50}@[-a-zA-Z0-9]{1,62}(\.[-a-zA-Z0-9]{1,62})+$/', strtolower($value)) === 1;
    }

    public static function isMobile(string $value): bool
    {
        if (empty($value)) {
            return false;
        }

        return preg_match('/^1[0-9]{10}$/', $value) === 1;
    }
}
