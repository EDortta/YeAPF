#!/bin/bash
echo "YeAPF 0.8.61 tools installer";
echo "Copyright (C) 2004-2018 Esteban Daniel Dortta - dortta@yahoo.com";


cant=1
dist=`uname -s`
dist=${dist:0:6}
php=`which php`
tmp=`echo $TEMP$TMPDIR`
if [[ "$tmp" == "" ]]; then
	tmp="/tmp"
fi

if [[ "$dist" == "CYGWIN" ]]; then
  if [ ! -f /usr/bin/php ]; then
    sed 's|%PHP%|'$php'|g' cygwinPHP.sh > /usr/bin/php
    chmod +x /usr/bin/php
    php=`which php`
  fi
  cant=0
fi

if [[ $EUID -eq 0 ]]; then
  cant=0
fi

if [[ $cant -ne 0 ]]; then
   echo "You must be root to run this script. Aborting...";
   exit 1;
fi

echo "PHP at $php"
$php install-script.php "$dist" "$php" "$tmp"
