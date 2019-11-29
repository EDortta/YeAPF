#!/bin/bash
echo "YeAPF 0.8.63 shipping js parts";
echo "Copyright (C) 2004-2019 Esteban Daniel Dortta - dortta@yahoo.com - MIT License";

tmp=`mktemp yeapf-db.XXXXXXX`
echo "Generating 7 years license..."
license=`php tools/ylicenseExpiration.php +2555 | grep x58e1`
cat skel/base/yeapf.db.ini | sed "s/x58e1d9ca63ef85abef352d3306a6fac3=.*/$license/g" > $tmp
mv $tmp skel/base/yeapf.db.ini

echo "Compiling yloader..."
temp=`php tools/compile-yloader.php`
if [ -z $temp ]; then
  echo "Error compiling yloader"
else
  new="$temp.min.js"

  echo "Minifying javascript files... ($temp)"
  echo "    yloader.js"
  java -jar tools/compressors/compiler.jar --language_in=ECMASCRIPT5 --js $temp --js_output_file $new

  for a in 'ysandboxifc' 'ystorage' 'yifc' 'yapp' 'ycomm-worker' 'ystorage-indexedDB-interface' 'ystorage-indexedDB-slave'
  do
    echo "    $a.js"

    b="app-src/js/min/$a"
    a="app-src/js/$a"
    (java -jar tools/compressors/compiler.jar --language_in=ECMASCRIPT5 --js "$a.js" --js_output_file "$b.min.js") || (echo "Error compressing $a.js"; exit 1)
  done

  echo "Spreading javascript files..."
  php tools/spread-js.php $temp $new

  if [ $? -eq 0 ]; then
    echo "Compiling config2.php..."
    php tools/compile-configure.php

    if [ $? -eq 0 ]; then
      echo "Spreading base..."
      php tools/spread-base.php

      if [ $? -eq 0 ]; then
        echo "Removing $temp"
        rm -f $temp
        echo "Removing $new"
        rm -f $new
        echo "All done"
      else
        echo "Error spreading basement files"
        exit 1
      fi
    else
      echo "Error compiling config2.php"
      exit 1
    fi
  else
    echo "Error spreading javascript files"
    exit 1
  fi
fi