#!/bin/bash

echo "YeAPF 0.8.54 tools installer";
echo "Copyright (C) 2004-2017 Esteban Daniel Dortta - dortta@yahoo.com";

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

cp ydistbuilder /usr/bin/
cp yviewdistinfo /usr/bin/
cp ydocbuilder /usr/bin/
cp ydocview /usr/bin/
chmod +x /usr/bin/ydistbuilder
chmod +x /usr/bin/yviewdistinfo
chmod +x /usr/bin/ydocbuilder
chmod +x /usr/bin/ydocview
