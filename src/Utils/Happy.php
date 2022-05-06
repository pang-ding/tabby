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

/*
 * 面向失业编程
 *
 * + 如有重大更新 增加一个方法
 */
class Happy
{
    const DEV_LINE = "\n\n......................................................\n";

    /**
     * 明年今日 - 输出次年今日日期
     * Initialize
     *
     * @return array
     */
    public static function todayOfNextYear()
    {
        self::typer(self::DEV_LINE, 5000);
        self::typer("** Today of next year ** \n");
        self::typer("See you next year :)   \n", 200000);
        $y = date('Y');
        sleep(3600 * 24 * ((($y % 4 == 0 && $y % 100 != 0) || $y % 400 == 0) ? 366 : 365));
        self::typer('Today of next year: ' . date('Y-m-d') . "\n\n\n");
        self::typer("**「以上」**\n");

        return true;
    }

    /**
     * 睡眠排序算法 - 对很小的自然数进行排序并输出结果
     * Ver 0.X
     *
     * @return array
     */
    public static function sleepSort(array $array)
    {
        self::typer(self::DEV_LINE, 5000);
        $max = 10;

        self::typer("** Sleep sort ** \n");
        if (count($array) > $max) {
            self::typer("写这么多你不累吗?\n");

            return false;
        }
        $pidArray = [];
        for ($i = 0; $i < count($array); $i++) {
            if (!is_integer($i) || $i < 0 || $i > $max) {
                continue;
            }
            $pid = pcntl_fork();
            if ($pid === 0) {
                sleep($array[$i]);
                self::typer(" {$array[$i]} \n", 200000);
                exit(0);
            } else {
                $pidArray[] = $pid;
            }
        }
        while (next($pidArray)) {
            pcntl_waitpid(current($pidArray), $s);
        }
        self::typer("**「以上」**\n");

        return true;
    }

    private static function typer($message, $interval = 100000)
    {
        print_r(' ');
        for ($i = 0; $i < strlen($message); $i++) {
            print_r($message[$i]);
            ob_flush();
            usleep($interval);
        }
    }
}
