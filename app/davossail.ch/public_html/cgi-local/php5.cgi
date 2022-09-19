#!/bin/sh

if [ $REDIRECT_REMOTE_USER ]
then
REMOTE_USER=$REDIRECT_REMOTE_USER
export REMOTE_USER
fi
OLD_IFS=$IFS
IFS="/"
set -- $PATH_TRANSLATED
IFS=$OLD_IFS
users=$5
subuser=$6
if [ "$users" = "users" ]
then
exec /usr/bin/sudo -E -u $subuser /bin/sh -c 'exec /usr/local/packages/php5/bin/php-cgi $PATH_TRANSLATED'
fi
exec /usr/local/packages/php5/bin/php-cgi $PATH_TRANSLATED
