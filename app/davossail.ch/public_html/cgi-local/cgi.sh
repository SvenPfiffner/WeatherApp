#!/bin/sh

CWD=`echo "$PATH_TRANSLATED" | sed 's/\/[^\/]*$//'`
SCRIPT_FILENAME=$PATH_TRANSLATED
SCRIPT_NAME=$PATH_TRANSLATED
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
export CWD 
exec /usr/bin/sudo -E -u $subuser /bin/sh -c 'HOME="$CWD"; cd "$CWD"; exec $PATH_TRANSLATED'
fi
cd $CWD
exec $PATH_TRANSLATED
