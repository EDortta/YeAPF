#!/bin/bash

echo "YeAPF 0.8.48 tools installer";
echo "Copyright (C) 2004-2016 Esteban Daniel Dortta - dortta@yahoo.com";

if [[ $EUID -ne 0 ]]; then
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
