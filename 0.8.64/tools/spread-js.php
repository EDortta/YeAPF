<?php
  /*
    tools/spread-js.php
    YeAPF 0.8.64-7 built on 2020-03-20 13:04 (-3 DST)
    Copyright (C) 2004-2020 Esteban Daniel Dortta - dortta@yahoo.com - MIT License
    2019-10-24 18:20:52 (-3 DST)

    This script will distribute monolite version of yloader.js
    among different application skeletons

   */
  /* void database connection */
  $dbConnect = 'no';
  /* avoid loading includes.lst in app folders */
  $cfgAvoidIncludesLst = 'yes';

  /* load yeapf directly */
  require_once "includes/yeapf.functions.php";

  function grantDirectory($dirName)
  {
    if (!is_dir($dirName)) {
      echo "Granting '$dirName' ... ";
      $ret=grantDir($dirName);
      echo ($ret?'Ok':'Error');
      echo "\n";
      if (!$ret) {
        echo "'$dirName' cannot be granted\n";
        exit(10);
      }
    }
  }

  function copyFile($srcFileName, $tgtFolder, $addHeader = false)
  {
    if (file_exists($srcFileName)) {
      grantDirectory($tgtFolder);
      $auxFile = _file($srcFileName);
      if ($addHeader) {
        $auxFile = "/* YeAPF 0.8.64-7 built on 2020-03-20 13:04 (-3 DST) Copyright (C) 2004-2020 Esteban Daniel Dortta - dortta@yahoo.com - MIT License */\n".$auxFile;
      }
      $tgtFileName=basename($srcFileName);
      if (file_put_contents("$tgtFolder/$tgtFileName", $auxFile))
        echo "\t$srcFileName: OK\n";
      else
        echo "\t$srcFileName: Error Writting '$tgtFolder'!\n";
    } else {
      if (file_exists("$tgtFolder/$srcFileName"))
        if (!unlink("$tgtFolder/$srcFileName"))
          echo "Error file '$tgtFolder/$srcFileName' cannot be deleted\n";
    }
  }

  function copyMobileFiles($targetFolder)
  {
    grantDirectory($targetFolder);
    copyFile("app-src/js/ysandboxifc.js",                           $targetFolder);
    copyFile("app-src/js/ystorage.js",                              $targetFolder);
    copyFile("app-src/js/yifc.js",                                  $targetFolder);
    copyFile("app-src/js/ycomm-worker.js",                          $targetFolder);
    copyFile("app-src/js/ystorage-indexedDB-interface.js",          $targetFolder);
    copyFile("app-src/js/ystorage-indexedDB-slave.js",              $targetFolder);

    copyFile("app-src/js/min/ysandboxifc.min.js",                   $targetFolder, true);
    copyFile("app-src/js/min/ystorage.min.js",                      $targetFolder, true);
    copyFile("app-src/js/min/yifc.min.js",                          $targetFolder, true);
    copyFile("app-src/js/min/ycomm-worker.min.js",                  $targetFolder, true);
    copyFile("app-src/js/min/ystorage-indexedDB-interface.min.js",  $targetFolder, true);
    copyFile("app-src/js/min/ystorage-indexedDB-slave.min.js",      $targetFolder, true);
  }

  function copyYeapfAppFiles($targetFolder)
  {
    copyFile("app-src/js/min/yapp.min.js",      $targetFolder, true);
  }

  function putVersion($targetFilename, $fileContent)
  {
    echo "Writting $targetFilename ... ";
    if (file_exists($targetFilename))
      if (!unlink($targetFilename)) {
        echo "Error: file cannot be deleted\n";
        exit(10);
      }
    if ($fileContent>'')
      if (!file_put_contents($targetFilename, trim($fileContent))) {
        echo "Error: file '$targetFilename' cannot be written\n";
        exit(10);
      }
    echo "OK\n";
    return true;
  }

  echo "Working at ".getcwd()."\n";

  $temp = $argv[1];
  if (!file_exists($temp)) {
    echo "Error: '$temp' file not found";
    exit(10);
  }
  echo "Expanded version source: $temp\n";
  $yeapfJS = join(" ", file($temp));

  $yeapf_minJS='';
  $minJS = $argv[2];

  if (file_exists($minJS)) {
    echo "Minified version source: $minJS\n";
    $yeapf_minJS = join("", file($minJS));
    $yeapf_minJS = "/* YeAPF 0.8.64-7 built on 2020-03-20 13:04 (-3 DST) Copyright (C) 2004-2020 Esteban Daniel Dortta - dortta@yahoo.com - MIT License */\n".$yeapf_minJS;
  }

  grantDirectory("skel/chromeApp/js");
  grantDirectory("skel/MoSyncApp/LocalFiles/js");
  grantDirectory("skel/electron/js");
  grantDirectory("skel/webApp/js");
  grantDirectory("skel/webApp/templates/bs3/js");
  grantDirectory("skel/webApp/templates/bs4/js");
  grantDirectory("skel/webApp/templates/milligram-1.3/js");
  grantDirectory("skel/webApp/templates/pure/js");
  grantDirectory("skel/webApp/templates/skeleton/js");
  grantDirectory("skel/webApp/templates/materialize/js");
  grantDirectory("skel/webSocket");
  grantDirectory("skel/service");
  grantDirectory("skel/cli");
  grantDirectory("skel/base/js");
  grantDirectory("skel/workbench");
  grantDirectory("skel/workbench/www");

  /* yloader.js and yloader.min.js */
  $yloaderTarget = array(
    "app-src/i_configure/js/yloader.min.js",
    "skel/base/js/yloader.min.js",
    "skel/base/js/yloader.js"
    /* OBSOLETE 20190627
    ,
    "skel/base/js/yloader.min.js",
    "skel/chromeApp/js/yloader.js",
    "skel/chromeApp/js/yloader.min.js",
    "skel/workbench/www/js/yloader.js",
    "skel/workbench/www/js/yloader.min.js",
    "skel/workbench/js/yloader.js",
    "skel/workbench/js/yloader.min.js",
    "skel/webApp/js/yloader.js",
    "skel/webApp/js/yloader.min.js",
    "skel/electron/js/yloader.js",
    "skel/electron/js/yloader.min.js",
    "skel/webApp/templates/bs3/js/yloader.js",
    "skel/webApp/templates/bs3/js/yloader.min.js",
    "skel/webApp/templates/bs4/js/yloader.js",
    "skel/webApp/templates/bs4/js/yloader.min.js",
    "skel/webApp/templates/milligram-1.3/js/yloader.js",
    "skel/webApp/templates/milligram-1.3/js/yloader.min.js",
    "skel/webApp/templates/pure/js/yloader.js",
    "skel/webApp/templates/pure/js/yloader.min.js",
    "skel/webApp/templates/materialize/js/yloader.js",
    "skel/webApp/templates/materialize/js/yloader.min.js",
    "skel/webApp/templates/skeleton/js/yloader.js",
    "skel/webApp/templates/skeleton/js/yloader.min.js",
    "skel/MoSyncApp/LocalFiles/js/yloader.js",
    "skel/MoSyncApp/LocalFiles/js/yloader.min.js",
    "skel/chromeApp/js/yloader.js",
    "skel/chromeApp/js/yloader.min.js"
    */
  );

  foreach($yloaderTarget as $tgt) {
    $r=null;
    if (strpos($tgt, ".min.js")>0) {
      $r=putVersion($tgt, $yeapf_minJS);
    } else {
      $r=putVersion($tgt, $yeapfJS);
    }
    if ($r!=null) {
      if (!$r) {
        echo "\n\nError creating $tgt !\n";
        exit(10);
      }
    }
  }

  /* OBSOLETE 20190627
  copyYeapfAppFiles("skel/base/templates/bs4/js");

  copyMobileFiles("skel/electron/js");
  copyMobileFiles("skel/chromeApp/js");
  copyMobileFiles("skel/workbench/www/js");
  copyMobileFiles("skel/MoSyncApp/LocalFiles/js");

  copyFile("app-src/js/ycomm-worker.js",                         "skel/webApp/js");
  copyFile("app-src/js/min/ycomm-worker.min.js",                 "skel/webApp/js", false);
  copyFile("app-src/js/min/ystorage.min.js",                     "skel/webApp/js", false);
  copyFile("app-src/js/min/ystorage-indexedDB-interface.min.js", "skel/webApp/js", false);
  copyFile("app-src/js/min/ystorage-indexedDB-slave.min.js",     "skel/webApp/js", false);
  */

  /* samples */
  /* OBSOLETE 20190619
  $jsSpreadTargets = array("samples/html-editor", "samples/key-admin", "samples/yIndexedDB", "skel/cordova");
  */
  $jsSpreadTargets = array("skel/base");
  foreach($jsSpreadTargets as $targetFolder) {
    echo "ystorage and yloader -> $targetFolder\n";

    copyFile("app-src/js/ycomm-worker.js",                         "$targetFolder/js");
    copyFile("app-src/js/min/ycomm-worker.min.js",                 "$targetFolder/js", false);

    copyFile("app-src/js/min/ystorage.min.js",                     "$targetFolder/js", false);
    copyFile("app-src/js/min/ystorage-indexedDB-interface.min.js", "$targetFolder/js", false);
    copyFile("app-src/js/min/ystorage-indexedDB-slave.min.js",     "$targetFolder/js", false);

    copyFile("app-src/js/ystorage.js",                         "$targetFolder/js", false);
    copyFile("app-src/js/ystorage-indexedDB-interface.js",     "$targetFolder/js", false);
    copyFile("app-src/js/ystorage-indexedDB-slave.js",         "$targetFolder/js", false);

  }

?>
