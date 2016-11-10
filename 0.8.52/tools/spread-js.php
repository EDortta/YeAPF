<?php
  /*
    tools/spread-js.php
    YeAPF 0.8.52-1 built on 2016-11-10 13:41 (-2 DST)
    Copyright (C) 2004-2016 Esteban Daniel Dortta - dortta@yahoo.com
    2016-11-10 13:27:48 (-2 DST)

    This script will distribute monolite version of yloader.js
    among different application skeletons

   */

  /* void database connection */
  $dbConnect = 'no';
  /* load yeapf directly */
  require_once "includes/yeapf.functions.php";

  function copyFile($srcFileName, $tgtFolder, $addHeader = false)
  {
    if (file_exists($srcFileName)) {
      $auxFile = _file($srcFileName);
      if ($addHeader) {
        $auxFile = "/* YeAPF 0.8.52-1 built on 2016-11-10 13:41 (-2 DST) Copyright (C) 2004-2016 Esteban Daniel Dortta - dortta@yahoo.com */\n".$auxFile;
      }
      $tgtFileName=basename($srcFileName);
      if (file_put_contents("$tgtFolder/$tgtFileName", $auxFile))
        echo "\t$srcFileName: OK\n";
      else
        echo "\t$srcFileName: Error Writting '$tgtFolder'\n";
    } else {
      if (file_exists("$tgtFolder/$srcFileName"))
        if (!unlink("$tgtFolder/$srcFileName"))
          echo "Error file '$tgtFolder/$srcFileName' cannot be deleted\n";
    }
  }

  function copyMobileFiles($targetFolder)
  {
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
        die("Error: file cannot be written\n");
    echo "OK\n";
    return true;
  }

  function verifyDirExists($dirName)
  {
    echo "Granting '$dirName' ... ";
    echo (grantDir($dirName)?'Ok':'Error');
    echo "\n";
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
    $yeapf_minJS = "/* YeAPF 0.8.52-1 built on 2016-11-10 13:41 (-2 DST) Copyright (C) 2004-2016 Esteban Daniel Dortta - dortta@yahoo.com */\n".$yeapf_minJS;
  }

  verifyDirExists("skel/chromeApp/js");
  verifyDirExists("skel/MoSyncApp/LocalFiles/js");
  verifyDirExists("skel/webApp/js");
  verifyDirExists("templates/bootstrap3/js");
  verifyDirExists("skel/webSocket");
  verifyDirExists("skel/workbench");
  verifyDirExists("skel/workbench/www");
  
  /* CONFIGURE.PHP */
  copy("skel/webApp/configure.php", "skel/webSocket/configure.php");
  copy("skel/webApp/configure.php", "skel/workbench/configure.php");
  copy("skel/webApp/configure.php", "skel/workbench/www/configure.php");

  /* yloader.js and yloader.min.js */
  $yloaderTarget = array(
    "app-src/i_configure/js/yloader.min.js",
    "skel/chromeApp/js/yloader.js",
    "skel/chromeApp/js/yloader.min.js",
    "skel/webApp/js/yloader.js",
    "skel/webApp/js/yloader.min.js"
    "templates/bootstrap3/js/yloader.js",
    "templates/bootstrap3/js/yloader.min.js",
    "skel/MoSyncApp/LocalFiles/js/yloader.js",
    "skel/MoSyncApp/LocalFiles/js/yloader.min.js",
    "skel/chromeApp/js/yloader.js",
    "skel/chromeApp/js/yloader.min.js"
  );

  foreach($tgt in $yloaderTarget) {
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

  copyYeapfAppFiles("templates/bootstrap3/js");

  copyMobileFiles("skel/chromeApp/js");
  copyMobileFiles("skel/MoSyncApp/LocalFiles/js");

  copyFile("app-src/js/ycomm-worker.js",              "skel/webApp/js");
  copyFile("app-src/js/min/ycomm-worker.min.js",      "skel/webApp/js", false);
  copyFile("app-src/js/min/ystorage.min.js",          "skel/webApp/js", false);

?>
