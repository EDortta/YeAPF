<?php
/*
    tools/install-script.php
    YeAPF 0.8.49-12 built on 2016-06-03 16:28 (-3 DST)
    Copyright (C) 2004-2016 Esteban Daniel Dortta - dortta@yahoo.com
    2016-06-03 16:25:51 (-3 DST)
*/

  function installFile($entry)
  {
    global $yeapfPath, $chmodScript, $targetPath;

    if ((file_exists($entry)) && (!is_dir($entry))) {
      echo "Copying $entry\n";
      if (file_exists("$targetPath/$entry"))
        if (!unlink("$targetPath/$entry"))
          die("** Error trying to remove '$entry' from $targetPath\nDo you have enough rights?\n");

      $aux=join('',file($entry));
      $aux=str_replace('%_YEAPF_PATH_%',$yeapfPath, $aux);
      $aux=str_replace('/usr/bin/php',"$targetPath/php", $aux);

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

  $targetPath=$onWinOS?"C:\Windows":"/usr/bin";
  $targetTemp=sys_get_temp_dir();

  $chmodScript='';
  if ($argv[1]=='Cygwin') {
    $targetPath="/cygdrive/c/Windows";
    $targetTemp="/cygdrive/c/Windows/Temp";

    $php = $argv[2];
    if (!file_exists('/usr/bin/php')) {
      echo "Creating PHP alias\n";
      $aux = join('',file('cygwinPHP.sh'));
      $aux = str_replace('%PHP%', $php, $aux);
      $f=fopen("/usr/bin/php","w");
      if ($f) {
        fwrite($f, $aux);
        fclose($f);
        $chmodScript.="chmod +x /usr/bin/php\n";
      }
    }
  }  

  $yeapfPath=dirname(getcwd());

  echo "YeAPF base folder = '$yeapfPath'\n";
  if (file_exists("$targetTemp/chmod-YeAPF-tools.sh"))
    unlink("$targetTemp/chmod-YeAPF-tools.sh");

  $d=dir(getcwd());
  while ($entry=$d->read()) {
    if (substr($entry,0,1)=='y') {
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
  shell_exec("$scriptName");
  echo "\nRun as root:\n$scriptName\n";
?>
