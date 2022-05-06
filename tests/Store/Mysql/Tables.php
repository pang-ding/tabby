<?php

/*
 * This file is part of the Tabby package.
 *
 * (c) Lv Liangyu <lv_liangyu@yeah.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tabby\Test\Store\Mysql;

/**
 * #MYSQL 建库：tabby_test 建用户：tabby_test
 * #mysql> CREATE DATABASE `tabby_test` CHARACTER SET 'utf8mb4' COLLATE 'utf8mb4_general_ci';
 * #mysql> GRANT ALL PRIVILEGES ON `tabby_test`.* TO 'tabby_test'@'localhost' IDENTIFIED BY 'tabby_test' WITH GRANT OPTION;
 * GRANT ALL PRIVILEGES ON db.* TO another_user@'localhost';
 */

class Tables
{
    public static $table_user = 'DROP TABLE IF EXISTS `user`;
      CREATE TABLE `user` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `age` int(11) DEFAULT NULL,
        `name` varchar(255) DEFAULT NULL,
        `ctime` datetime DEFAULT NULL,
        `group` int(11) DEFAULT NULL,
        `class` int(11) DEFAULT NULL,
        PRIMARY KEY (`id`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8;';

    public static $table_group = 'DROP TABLE IF EXISTS `group`;
      CREATE TABLE `group` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(255) DEFAULT NULL,
        PRIMARY KEY (`id`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8;';

    public static $table_class = 'DROP TABLE IF EXISTS `class`;
      CREATE TABLE `class` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(255) DEFAULT NULL,
        PRIMARY KEY (`id`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8;';
}
