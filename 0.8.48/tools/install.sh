#!/bin/bash
echo "YeAPF 0.8.48 tools installer";
echo "Copyright (C) 2004-2016 Esteban Daniel Dortta - dortta@yahoo.com";

if [[ $EUID -ne 0 ]]; then
   echo "You must be root to run this script. Aborting...";
   exit 1;
fi

A=`which php`
B=`uname -o`

$A install-script.php $B
