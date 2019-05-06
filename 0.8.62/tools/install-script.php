<?php
/*
    tools/install-script.php
    YeAPF 0.8.62-96 built on 2019-05-06 18:30 (-3 DST)
    Copyright (C) 2004-2019 Esteban Daniel Dortta - dortta@yahoo.com
    2019-05-06 11:43:31 (-3 DST)
*/

  function installFile($entry)
  {
    global $yeapfPath, $chmodScript, $targetPath, $phpPath;

    if ((file_exists($entry)) && (!is_dir($entry))) {
      echo "Copying $entry\n";
      if (file_exists("$targetPath/$entry"))
        if (!unlink("$targetPath/$entry"))
          die("** Error trying to remove '$entry' from $targetPath\nDo you have enough rights?\n");

      $aux=join('',file($entry));
      $aux=str_replace('%_YEAPF_PATH_%',$yeapfPath, $aux);
      $aux=str_replace('/usr/bin/php',"$phpPath", $aux);

      $f=fopen("$targetPath/$entry","w");
      if ($f) {
        fwrite($f,$aux);
        fclose($f);

        if ($chmodScript>'')
          $chmodScript.="\n";
        $aux="chmod +x $targetPath/$entry";
        $chmodScript.=$aux;
      }
    }
  }

  $onWinOS=(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
  $onDarwinOS=(strtoupper(substr(PHP_OS, 0, 6)) === 'DARWIN');

  $phpPath = $argv[2];
  $targetTemp=$argv[3];
  $targetPath=dirname($phpPath);

  echo "TEMP at $targetTemp\n";
  echo "Windows? = ".($onWinOS?'TRUE':'FALSE')."\n";
  echo "Mac? = ".($onDarwinOS?'TRUE':'FALSE')."\n";

  $chmodScript='';
  if ($argv[1]=='CYGWIN') {
    $targetPath="c:/cygwin64/bin/";
  } else {
    $targetPath=($onWinOS?"C:\\Windows":($onDarwinOS?"/usr/local/bin":"/usr/bin"));
  }

  $yeapfPath=dirname(getcwd());

  echo "YeAPF base folder = '$yeapfPath'\n";
  if (file_exists("$targetTemp/chmod-YeAPF-tools.sh"))
    unlink("$targetTemp/chmod-YeAPF-tools.sh");

  $d=dir(getcwd());
  while ($entry=$d->read()) {
    if ((substr($entry,0,1)=='y') || (substr($entry,0,4)=='adb-')) {
      installFile($entry);
    }
  }
  $d->close();

  chdir("yDoc");
  installFile('ydocimport');
  chdir("..");

  $scriptName="$targetTemp/chmod-YeAPF-tools.sh";
  $f=fopen($scriptName,"w");
  fwrite($f,$chmodScript);
  fclose($f);

  $ret=shell_exec("chmod +x $scriptName");
  shell_exec("bash $scriptName");
  echo "\nRun as root:\n$scriptName\n";
?>
