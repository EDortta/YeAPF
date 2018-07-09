# (C) 2016 Esteban D.Dortta
#
# tools/adb-lib.inc.sh
# YeAPF 0.8.61-12 built on 2018-07-09 16:23 (-3 DST)
# Copyright (C) 2004-2018 Esteban Daniel Dortta - dortta@yahoo.com
# 2018-07-09 14:58:29 (-3 DST)
#
# This set of scripts requires adb/android-sdk/cordova installed on your device.
# Has been well tested under Ubuntu 16 and MacOSX 10.11.6
# It is supposed to run on Cygwin

setServer()
{
  newServer=`echo $(printf '%q' $1)`
  oldServer=`cat www/js/version.js | grep __appServer | awk -F\= '{ print $2 }'`
  oldServer=`echo $oldServer | awk -F\" '{ print $2 }'`
  printf -v oldServer "%q" "$oldServer"

  sed -i -e  "s#$oldServer#$newServer#g"  www/js/version.js
  return
}

setDebug()
{
  curDeb=`cat www/js/version.js | grep __debugging`
  newDeb="window.__debugging=$1"
  sed -i -e  "s/$curDeb/$newDeb\;/g"  www/js/version.js
  return
}

setVersion()
{
  curVer=`cat www/js/version.js | grep __appVersion | awk -F\= '{ print $2 }'`
  sed -i -e  "s/$curVer/$1\;/g"  www/js/version.js
  return
}

currentVersion()
{
  version=`cat config.xml | grep version | grep widget | awk '{print $3}' | awk -F\= '{print $2}'`
  version=`eval echo $version`
  newVer="{version: '$version'}"

  setVersion "$newVer"

  echo $version
  return
}

increaseVersion()
{
  version=$(currentVersion)
  inicio=`echo $version | awk -F\. '{print $1"."$2}'`
  a=`echo $version | awk -F\. '{print $1}'`
  b=`echo $version | awk -F\. '{print $2}'`
  c=`echo $version | awk -F\. '{print $3}'`
  if (( c<9 )); then
    c=$((c+1))
  else
    b=$((b+1))
    c=0
  fi
  novaVersao=`echo $a\.$b\.$c`
  sed -i -e "s/$version/$novaVersao/g" ./config.xml

  echo $novaVersao
  return
}

widgetName()
{
  widget=`cat config.xml  | grep widget | awk '{print $2}' | awk -F\= '{print $2}'`
  widget=`eval echo $widget`
  echo $widget;
  return
}

j="$(currentVersion)"
echo "---[ver.Atual: $j]--"
devcount=`adb devices | grep -w device | wc -l`
firstdev=`adb devices | grep -w device | awk '{print $1}' | head -n 1`