<?php
/*
    tools/install-script.php
    YeAPF 0.8.49-10 built on 2016-06-03 13:09 (-3 DST)
    Copyright (C) 2004-2016 Esteban Daniel Dortta - dortta@yahoo.com
    2014-08-18 17:02:33 (-3 DST)
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

  $targetPath=($argv[1]=='Cygwin')?"/cygdrive/c/Windows":(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')?"C:\Windows":"/usr/bin";
  $targetTemp=($argv[1]=='Cygwin')?"/cygdrive/c/Windows/Temp":(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')?"C:\Windows\Temp":"/tmp";

  $chmodScript='';
  if ($argv[1]=='Cygwin') {
    $php = $_ENV['_'];
    if (!file_exists('/usr/bin/php')) {
      $aux = join('',file('cygwinPHP.sh'));
      $aux = str_replace('%PHP%', $php, $aux);
      $f=fopen("/usr/bin/php","w");
      if ($f) {
        fwrite($f, $aux);
        fclose($f);
        $chmodScript.="chmod +x /usr/bin/php\n";
      }
    }
    die("OK");
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
