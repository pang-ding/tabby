#!/bin/bash
APP_DIR=$(cd $(dirname $0);pwd)

LIST=$(cd $APP_DIR/zd_sock;ls)
for i in $LIST;do
    #ps -ef | grep $i | grep "console/entry.php" | awk '{print $2}' | xargs kill
    ps -ef | grep $i | grep "console/entry.php" | awk '{print $2}' | xargs kill -9
    rm -f $APP_DIR/zd_sock/$i
done