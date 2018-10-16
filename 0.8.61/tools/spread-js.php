<?php
  /*
    tools/spread-js.php
    YeAPF 0.8.61-105 built on 2018-10-16 08:01 (-3 DST)
    Copyright (C) 2004-2018 Esteban Daniel Dortta - dortta@yahoo.com
    2018-09-10 10:25:26 (-3 DST)

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
      if (!$ret)
        die("'$dirName' cannot be granted\n");
    }
  }

  function copyFile($srcFileName, $tgtFolder, $addHeader = false)
  {
    if (file_exists($srcFileName)) {
      grantDirectory($tgtFolder);
      $auxFile = _file($srcFileName);
      if ($addHeader) {
        $auxFile = "/* YeAPF 0.8.61-105 built on 2018-10-16 08:01 (-3 DST) Copyright (C) 2004-2018 Esteban Daniel Dortta - dortta@yahoo.com */\n".$auxFile;
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
    copyFile("app-src/js/ysandboxifc.js",       $targetFolder);
    copyFile("app-src/js/ystorage.js",          $targetFolder);
    copyFile("app-src/js/yifc.js",              $targetFolder);
    copyFile("app-src/js/ycomm-worker.js",      $targetFolder);

    copyFile("app-src/js/min/ysandboxifc.min.js",   $targetFolder, true);
    copyFile("app-src/js/min/ystorage.min.js",      $targetFolder, true);
    copyFile("app-src/js/min/yifc.min.js",          $targetFolder, true);
    copyFile("app-src/js/min/ycomm-worker.min.js",  $targetFolder, true);
  }

  function copyYeapfAppFiles($targetFolder)
  {
    copyFile("app-src/js/min/yapp.min.js",      $targetFolder, true);
  }

  function putVersion($targetFilename, $fileContent)
  {
    echo "Writting $targetFilename ... ";
    if (file_exists($targetFilename))
      if (!unlink($targetFilename))
        die("Error: file cannot be deleted\n");
    if ($fileContent>'')
      if (!file_put_contents($targetFilename, trim($fileContent)))
        die("Error: file '$targetFilename' cannot be written\n");
    echo "OK\n";
    return true;
  }

  echo "Working at ".getcwd()."\n";

  $temp = $argv[1];
  if (!file_exists($temp))
    die("Error: '$temp' file not found");
  echo "Expanded version source: $temp\n";
  $yeapfJS = join(" ", file($temp));

  $yeapf_minJS='';
  $minJS = $argv[2];

  if (file_exists($minJS)) {
    echo "Minified version source: $minJS\n";
    $yeapf_minJS = join("", file($minJS));
    $yeapf_minJS = "/* YeAPF 0.8.61-105 built on 2018-10-16 08:01 (-3 DST) Copyright (C) 2004-2018 Esteban Daniel Dortta - dortta@yahoo.com */\n".$yeapf_minJS;
  }

  grantDirectory("skel/chromeApp/js");
  grantDirectory("skel/MoSyncApp/LocalFiles/js");
  grantDirectory("skel/electron/js");
  grantDirectory("skel/webApp/js");
  grantDirectory("skel/webApp/templates/bs3/js");
  grantDirectory("skel/webApp/templates/bs4/js");
  grantDirectory("skel/webSocket");
  grantDirectory("skel/service");
  grantDirectory("skel/cli");
  grantDirectory("skel/workbench");
  grantDirectory("skel/workbench/www");

  /* CONFIGURE.PHP and SSE.PHP */
  echo "Copying yeapf.db.ini, configure.php and sse.php\n";
  $phpSpreadList=array('configure.php', 'sse.php', 'yeapf.db.ini');
  $folders = array("skel", "skel/webApp/templates", "samples");
  foreach($folders as $aFolder) {
    foreach(glob("$aFolder/*") as $folderName) {
      if ($folderName!="skel/webApp") {
        foreach($phpSpreadList as $script) {
          if (file_exists("$folderName/$script")) {
            echo "  +-- $folderName/$script\n";
            copy("skel/webApp/$script", "$folderName/$script");
          }
        }
      }
    }
  }

/*
  echo "CLI skeleton\n";
  copy("skel/webApp/configure.php", "skel/cli/configure.php");
  copy("skel/webApp/yeapf.db.ini", "skel/cli/yeapf.db.ini");
*/

  /* service skeleton
     YEAPF.DB.INI REST.PHP, QUERY.PHP, E_BODY.XML, TASKS.PHP, YEAPF_TICKER.PHP, CONFIGURE.PHP and SSE.PHP */
  $phpServiceSkeleton=array('rest.php', 'query.php', 'e_body.xml',
                            'tasks.php', 'yeapf_ticker.php');
  $phpServiceSkeletonTargetFolders = array("skel/service",
                                           "skel/webApp/templates/bs3",
                                           "skel/webApp/templates/bs4",
                                           "samples/key-admin");
  echo "Service, Templates skeletons and Samples folder\n";
  foreach($phpServiceSkeleton as $script) {
    echo "  $script\n";
    foreach($phpServiceSkeletonTargetFolders as $scriptTarget) {
      echo "    +-- $scriptTarget/$script\n";
      copy("skel/webApp/$script", "$scriptTarget/$script");
    }
  }

  /* webapp skeleton (service skeleton + index.php and body.php ) */
  $phpWebAppSkeleton = array("index.php", "body.php");
  foreach($phpWebAppSkeleton as $script) {
    copy("skel/webApp/$script", "samples/key-admin/$script");
  }


  /* slotEmptyImplementation.php */
  copy("skel/webApp/slotEmptyImplementation.php", "skel/workbench/www/slotEmptyImplementation.php");

  /* yeapf.db.ini */
  copy("skel/webApp/yeapf.db.ini", "skel/webSocket/yeapf.db.ini");
  copy("skel/webApp/yeapf.db.ini", "skel/workbench/yeapf.db.ini");
  copy("skel/webApp/yeapf.db.ini", "skel/workbench/www/yeapf.db.ini");

  /* yloader.js and yloader.min.js */
  $yloaderTarget = array(
    "app-src/i_configure/js/yloader.min.js",
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
    "skel/MoSyncApp/LocalFiles/js/yloader.js",
    "skel/MoSyncApp/LocalFiles/js/yloader.min.js",
    "skel/chromeApp/js/yloader.js",
    "skel/chromeApp/js/yloader.min.js"
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
        die("\n\nError creating $tgt !\n");
      }
    }
  }

  copyYeapfAppFiles("skel/webApp/templates/bs4/js");

  copyMobileFiles("skel/electron/js");
  copyMobileFiles("skel/chromeApp/js");
  copyMobileFiles("skel/workbench/www/js");
  copyMobileFiles("skel/MoSyncApp/LocalFiles/js");

  copyFile("app-src/js/ycomm-worker.js",              "skel/webApp/js");
  copyFile("app-src/js/min/ycomm-worker.min.js",      "skel/webApp/js", false);
  copyFile("app-src/js/min/ystorage.min.js",          "skel/webApp/js", false);

  /* samples */
  $jsSpreadTargets = array("samples/key-admin", "samples/yIndexedDB", "skel/cordova");
  foreach($jsSpreadTargets as $targetFolder) {
    echo "ystorage and yloader -> $targetFolder\n";
    copyFile("app-src/js/min/ystorage.min.js",          "$targetFolder/js", false);
    copyFile("skel/webApp/js/yloader.min.js",           "$targetFolder/js", false);
    copyFile("app-src/js/min/ystorage.js",          "$targetFolder/js", false);
    copyFile("skel/webApp/js/yloader.js",           "$targetFolder/js", false);

  }

?>
