#!/bin/bash
echo "YeAPF 0.8.49 tools installer";
echo "Copyright (C) 2004-2016 Esteban Daniel Dortta - dortta@yahoo.com";

cant=1
dist=`uname -o`
if [[ "$dist" == "Cygwin" ]]; then
  cant=0
fi

if [[ $EUID -eq 0 ]]; then
  cant=0
fi

if [[ $cant -ne 0 ]]; then
   echo "You must be root to run this script. Aborting...";
   exit 1;
fi

php=`which php`


$php install-script.php "$dist" "$php"
