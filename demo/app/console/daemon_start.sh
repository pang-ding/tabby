#!/bin/bash
APP_DIR=$(cd $(dirname $0);pwd)
ROOT_DIR=$(dirname $(dirname $APP_DIR))
UUID=$(cat /proc/sys/kernel/random/uuid)

mkdir -p ${APP_DIR}/zd_sock/
zdaemon -s "${APP_DIR}/zd_sock/${UUID}" -ftrue -b1 -x "" -p "php -c ${ROOT_DIR}/conf/php_dev.ini ${APP_DIR}/entry.php" start -r$1 -d$2

# ./daemon_start.sh /index/index "foo=a&bar=b"