#!/bin/bash
echo "YeAPF 0.8.60 shipping js parts";
echo "Copyright (C) 2004-2018 Esteban Daniel Dortta - dortta@yahoo.com";

tmp=`mktemp yeapf-db.XXXXXXX`
echo "Generating 7 years license..."
license=`php tools/ylicenseExpiration.php +2555 | grep x58e1`
cat skel/webApp/yeapf.db.ini | sed "s/x58e1d9ca63ef85abef352d3306a6fac3=.*/$license/g" > $tmp
mv $tmp skel/webApp/yeapf.db.ini

echo "Compiling..."
temp=`php tools/compile-yloader.php`
new="$temp.min.js"

echo "Minifying... ($temp)"
echo "    yloader.js"
java -jar tools/compressors/compiler.jar --language_in=ECMASCRIPT5 --js $temp --js_output_file $new

for a in 'ysandboxifc' 'ystorage' 'yifc' 'yapp' 'ycomm-worker'
do
  echo "    $a.js"

  b="app-src/js/min/$a"
  a="app-src/js/$a"
  java -jar tools/compressors/compiler.jar --language_in=ECMASCRIPT5 --js "$a.js" --js_output_file "$b.min.js"

done

echo "Spreading..."
php tools/spread-js.php $temp $new

echo "Compiling config2.php..."
php tools/compile-configure.php

echo "Removing $temp"
rm -f $temp
echo "Removing $new"
rm -f $new
echo "All done"
