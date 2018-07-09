#!/bin/bash

echo "YeAPF 0.8.61 tools installer";
echo "Copyright (C) 2004-2018 Esteban Daniel Dortta - dortta@yahoo.com";

cant=1
dist=`uname -s`
dist=${dist:0:6}
if [[ "$dist" == "CYGWIN" ]]; then
  cant=0
fi

if [[ $EUID -eq 0 ]]; then
  cant=0
fi

if [[ $cant -ne 0 ]]; then
   echo "You must be root to run this script. Aborting...";
   exit 1;
fi

tgt="/usr/bin"
if [[ "$dist" == "Darwin" ]]; then
  tgt="/usr/local/bin"
fi

cp ydistbuilder $tgt/
cp yviewdistinfo $tgt/
cp ydocbuilder $tgt/
cp ydocview $tgt/
cp ycheckdistsource $tgt/
chmod +x $tgt/ydistbuilder
chmod +x $tgt/yviewdistinfo
chmod +x $tgt/ydocbuilder
chmod +x $tgt/ydocview
chmod +x $tgt/ycheckdistsource
