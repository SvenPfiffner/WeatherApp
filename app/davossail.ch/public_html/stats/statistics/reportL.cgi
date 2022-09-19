#!/bin/sh

ARGV0=`basename $0`

exec /usr/local/bin/envreset /etc/wrapper/env.keep \
 /usr/local/bin/envdir /etc/wrapper/env \
 /usr/local/bin/argv0 /u/bin/wrapper.priv $ARGV0 $*
