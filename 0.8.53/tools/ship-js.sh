#!/bin/bash
echo "YeAPF 0.8.53 shipping js parts";
echo "Copyright (C) 2004-2017 Esteban Daniel Dortta - dortta@yahoo.com";

tmp=`mktemp`
echo "Generating 7 years license..."
license=`php tools/ylicenseExpiration.php +2555 | grep x58e1`
cat skel/webApp/yeapf.db.ini | sed "s/x58e1d9ca63ef85abef352d3306a6fac3=.*/$license/g" > $tmp
mv $tmp skel/webApp/yeapf.db.ini

echo "Compiling..."
temp=`php tools/compile-yloader.php`
new=`echo "$temp"  | cut -d'.' --complement -f2-`
new="$new.min.js"

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
