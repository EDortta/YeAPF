#!/usr/bin/php
<?php
  /*
   * tools
   * tools/ysetdbconn.php
   * YeAPF 0.8.60-24 built on 2018-05-15 18:13 (-3 DST)
   * Copyright (C) 2004-2018 Esteban Daniel Dortta - dortta@yahoo.com
   * 2018-04-26 17:00:07 (-3 DST)
   *
   * Purpose: to create yeapf.db.ini basis
   */

  function displayError($errMessage)
  {
    global $args;

    if (is_writable('./')) {
      $f=fopen("ydbmigrate.lasterror","w");
      fwrite($f,$errMessage);
      fclose($f);
    }

    $args->setArgValue('help',__TRUE__);
    die("\n$errMessage\n");
  }

  echo basename("tools/ysetdbconn.php")."\nYeAPF 0.8.60 tools\nCopyright (C) 2004-2018 Esteban Daniel Dortta - dortta@yahoo.com\n\n";

  $mydir=dirname($_SERVER['SCRIPT_FILENAME']);
  (@include_once "$mydir/yclilib.php") or die("yclilib.php not found\n");
  (@include_once "$mydir/ydblib.php") or die("ydblib.php not found\n");

  $cwd=getcwd();
  $args = new ArgumentsProcessor();

  // check db connection details
  getDBArgs($args);
  $toUpdate=$args->argValue('update',false);
  $YFolder=$args->argValue('source',$__yeapfPath);

  $dbConnect=0;

  _LOAD_YEAPF_();

  $helpNeeded=(($args->argValue('h;help')==true) || ($args->optionCount()==0));

  if (!((isset($dbUser)) &&
       (isset($dbPass)) &&
       (isset($dbHost)) &&
       (isset($dbName)) &&
       (isset($dbType)))) {
    $helpNeeded = true;
  }

  if ((!isset($cfgMainFolder)) || ($cfgMainFolder=="")) {
    $cfgMainFolder=getcwd();
  }

  $yeapfIniPath = "$cfgMainFolder/yeapf.db.ini";
  if ($configExists = file_exists("$yeapfIniPath")) {
    $helpNeeded = !$toUpdate;
  }

  if (!file_exists("$YFolder/skel/webApp/yeapf.db.ini")) {
    echo "** Warning: '$YFolder/skel/webApp/yeapf.db.ini' does not exists\n";
  }

  if ($helpNeeded) {
    $myself=basename($argv[0]);
    echo "YeAPF 0.8.60 $myself \nCopyright (C) 2004-2018 Esteban Daniel Dortta - dortta@yahoo.com\n\n";
    echo "YeAPF dbtools*\nFor change database connection you MUST use these switches:";
    echo "\n\t[--db <host:database name>]\tdatabase identification";
    echo "\n\t[--user <user name>]\t\tdatabase user name";
    echo "\n\t[--pass <user password>]";
    echo "\n\t[--type <mysql|mysqli|firebird|postgresql>]";
    echo "\n\t[--update]\t\t\tAllow to update yeapf.db.ini";
    echo "\n\t[--source <folder>]\t\tYeAPF! source folder (defaults to '$__yeapfPath')";
    echo "\nYou can change ANY other yeapf.db.ini key just indicating the key name followed by a value.\nFor example:";
    echo "\n\t--dbCharset ISO-8859-1";
    echo "\n\t--dontWorkUntil 201804112359";
    echo "\n\t--messagePeekerInterval 1200";
    echo "\n\ncfgMainFolder='$cfgMainFolder'\n";
    if ($configExists) {
      echo "** ERROR: 'yeapf.db.ini' already exists.\n\t  Use --update option in order to change some value\n";
    }
  } else {
    if (!file_exists("$yeapfIniPath")) {
      if (file_exists("$YFolder/skel/webApp/yeapf.db.ini")) {
        $aux=join("",file("$YFolder/skel/webApp/yeapf.db.ini"));
        $aux=str_replace('[%(appFolder)]','['.md5($cfgMainFolder).']',$aux);
        $aux=str_replace('%(appFolder)',basename($cfgMainFolder),$aux);

        $newAppRegistry=md5(uniqid('YeAPF-app',true));
        $aux=str_replace('%(newAppRegistry)',$newAppRegistry,$aux);

        echo "Creating '$yeapfIniPath'\n";

        $f=fopen("$yeapfIniPath","w");
        fwrite($f, $aux);
        fclose($f);
      } else {
        die("'$YFolder/skel/webApp/yeapf.db.ini' was not found\n");
      }
    }

    $yeapfDB = parse_ini_file($yeapfIniPath, true);
    $appKey='';
    foreach($yeapfDB as $k=>$v) {
      if ($appKey=='')
        $appKey=$k;
    }

    foreach($args->getOptionList() as $k=>$v) {
      if (isset($yeapfDB[$appKey][$k])) {
        echo "\t$k = '$v'\n";
        $yeapfDB[$appKey][$k] = $v;
      }
    }

    foreach($dbDefArray as $k=>$v) {
      if (isset($yeapfDB[$appKey][$k])) {
        echo "\t$k = '$v'\n";
        $yeapfDB[$appKey][$k] = $v;
      }
    }

    write_ini_file($yeapfDB, $yeapfIniPath, true);

    echo "\ndb.csv untouched.\nUse configure.php in order to update/create your application connection\n";
  }
?>