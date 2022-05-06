#!/bin/bash

DIR=`pwd`
CONFDIR=$DIR

redis-server $CONFDIR/m.conf
redis-server $CONFDIR/s1.conf
redis-server $CONFDIR/s2.conf

redis-sentinel $CONFDIR/sen1.conf
redis-sentinel $CONFDIR/sen2.conf
redis-sentinel $CONFDIR/sen3.conf