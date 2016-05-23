<?php
  /*
    tools/spread-js.php
    YeAPF 0.8.49-1 built on 2016-05-23 14:38 (-3 DST)
    Copyright (C) 2004-2016 Esteban Daniel Dortta - dortta@yahoo.com
    2016-03-18 13:26:05 (-3 DST)

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
        $auxFile = "/* YeAPF 0.8.49-1 built on 2016-05-23 14:38 (-3 DST) Copyright (C) 2004-2016 Esteban Daniel Dortta - dortta@yahoo.com */\n".$auxFile;
      }
      if (file_put_contents("$tgtFolder/$srcFileName", $auxFile))
        echo "\t$srcFileName: OK\n";
      else
        die ("\t$srcFileName: Error Writting\n");
    } else {
      if (file_exists("$tgtFolder/$srcFileName"))
        if (!unlink("$tgtFolder/$srcFileName"))
          die("Error file '$tgtFolder/$srcFileName' cannot be deleted\n");
    }
  }

  function copyMobileFiles($targetFolder)
  {
    copyFile("app-src/js/ysandboxifc.js",       $targetFolder);
    copyFile("app-src/js/ystorage.js",          $targetFolder);
    copyFile("app-src/js/yifc.js",              $targetFolder);
    copyFile("app-src/js/ycomm-worker.js",      $targetFolder);

    copyFile("app-src/js/ysandboxifc.min.js",   $targetFolder, true);
    copyFile("app-src/js/ystorage.min.js",      $targetFolder, true);
    copyFile("app-src/js/yifc.min.js",          $targetFolder, true);
    copyFile("app-src/js/ycomm-worker.min.js",  $targetFolder, true);
  }

  function copyYeapfAppFiles($targetFolder)
  {
    copyFile("app-src/js/yapp.min.js",      $targetFolder, true);
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
    $yeapf_minJS = "/* YeAPF 0.8.49-1 built on 2016-05-23 14:38 (-3 DST) Copyright (C) 2004-2016 Esteban Daniel Dortta - dortta@yahoo.com */\n".$yeapf_minJS;
  }

  verifyDirExists("skel/chromeApp/js");
  verifyDirExists("skel/MoSyncApp/LocalFiles/js");
  verifyDirExists("skel/webApp/js");
  verifyDirExists("templates/bootstrap3/js");
  verifyDirExists("skel/webSocket");
  copy("skel/webApp/configure.php", "skel/webSocket/configure.php");

  putVersion("skel/chromeApp/js/yloader.js", $yeapfJS);
  putVersion("skel/chromeApp/js/yloader.min.js", $yeapf_minJS);

  putVersion("skel/webApp/js/yloader.js", $yeapfJS);
  putVersion("skel/webApp/js/yloader.min.js", $yeapf_minJS);

  if (putVersion("templates/bootstrap3/js/yloader.min.js", $yeapf_minJS)) {
    putVersion("templates/bootstrap3/js/yloader.js", $yeapfJS);
    copyYeapfAppFiles("templates/bootstrap3/js");
  }
  putVersion("skel/MoSyncApp/LocalFiles/js/yloader.js", $yeapfJS);
  putVersion("skel/MoSyncApp/LocalFiles/js/yloader.min.js", $yeapf_minJS);

  if (putVersion("skel/chromeApp/js/yloader.min.js", $yeapf_minJS)) {
    putVersion("skel/chromeApp/js/yloader.js", $yeapfJS);
    echo "OK\n";
    copyMobileFiles("skel/chromeApp/js");

    if (putVersion("skel/webApp/js/yloader.min.js", $yeapf_minJS)) {
      putVersion("skel/webApp/js/yloader.js", $yeapfJS);
      copyFile("app-src/js/ycomm-worker.js",          "skel/webApp/js");
      copyFile("app-src/js/ycomm-worker.min.js",      "skel/webApp/js", false);

      echo "OK\n";
    } else {
      die("Error!\n");
    }

    if (putVersion("skel/MoSyncApp/LocalFiles/js/yloader.min.js", $yeapf_minJS)) {
      putVersion("skel/MoSyncApp/LocalFiles/js/yloader.js", $yeapfJS);
      echo "OK\n";

      copyMobileFiles("skel/MoSyncApp/LocalFiles/js");

    } else {
      die("Error!\n");
    }
  } else
    die("Error!\n");
?>
