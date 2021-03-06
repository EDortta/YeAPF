#!/usr/bin/php
<?php
/*
    tools/distributionBuilder/ycheckdistsource.php
    YeAPF 0.8.56-19 built on 2017-03-20 19:28 (-3 DST)
    Copyright (C) 2004-2017 Esteban Daniel Dortta - dortta@yahoo.com
    2017-03-20 19:21:06 (-3 DST)
*/

  $mydir=dirname($_SERVER['SCRIPT_FILENAME']);
  $cmRequired=false;
  if (file_exists("$mydir/yclilib.php"))
    $cmLocation = "$mydir/yclilib.php";
  else
    $cmLocation = "$mydir/../yclilib.php";

  (@include_once "$cmLocation") or die("yclilib.php not found\n");

  function analiseDistFolder($sourceFolder, $distFolder) 
  {
    $relSrcFolder=substr($sourceFolder, strlen(getcwd())+1);
    if ($relSrcFolder=='')
      $relSrcFolder=".";
    
    foreach(glob("$distFolder/*") as $f) {
      $filename=substr($f, strlen($distFolder)+1);
      $source="$sourceFolder/$filename";
      $distribution="$distFolder/$filename";
      if (is_dir($f)) {
        analiseDistFolder($source, $distribution);
      } else {
        if (file_exists($source)) {
          $smt=filemtime($source); $sD=date ("F d Y H:i:s", $smt);
          $dmt=filemtime($distribution); $dD=date ("F d Y H:i:s", $dmt);
          if ($dmt==0) {
            echo "Advice:  '$relSrcFolder/$filename' without timestamp\n";
          } else {
            if ($smt<$dmt) {
              echo "Error:   '$relSrcFolder/$filename' older than distribution ($sD vs $dD)\n";
            } else if ($smt>$dmt) {
              echo "Warning: '$relSrcFolder/$filename' modified after distribution ($sD vs $dD)\n";
            }
          }

        } else {
          echo "Error:   '$relSrcFolder/$filename' NOT FOUND\n";
        }
      }
    }
  }

  $cwd=getcwd();
  $args = new ArgumentsProcessor(false);
  $myself = basename($argv[0]);

  $versionDef = isset($argv[1])?$argv[1]:'.';
  $versionDef = "$versionDef/version.def";
  if (!file_exists($versionDef))
    $versionDef=".distribution/$versionDef";  

  if ((!file_exists($versionDef)) && ($argc<=1)) {
    if ((is_dir(".distribution")) && (file_exists("version.inf"))) {
      $curVersion = join(file('version.inf'));
      $curVersion = substr($curVersion,0,strpos($curVersion, "-"));
      $versionDef = ".distribution/$curVersion/version.def";
    }
  }
  if (file_exists("$versionDef")) {
    echo "[ $versionDef ]\n";
    $folder=dirname($versionDef);
    analiseDistFolder(getcwd(), $folder);
  } else
    echo "'$versionDef' not found\n";
?>
