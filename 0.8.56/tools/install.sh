#!/bin/bash
echo "YeAPF 0.8.56 tools installer";
echo "Copyright (C) 2004-2017 Esteban Daniel Dortta - dortta@yahoo.com";


cant=1
dist=`uname -o`
php=`which php`
tmp=`echo $TEMP`
if [[ "$tmp" == "" ]]; then
	tmp="/tmp"
fi

if [[ "$dist" == "Cygwin" ]]; then
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
