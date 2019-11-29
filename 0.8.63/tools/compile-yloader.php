<?php
  /*
    tools/compile-yloader.php
    YeAPF 0.8.63-242 built on 2019-11-29 09:22 (-2 DST)
    Copyright (C) 2004-2019 Esteban Daniel Dortta - dortta@yahoo.com - MIT License
    2019-09-09 08:27:18 (-2 DST)

    This script will produce monolite version of yloader.js
    to be used with different applications.

   */

  function myErrorHandler($errno, $errstr, $errfile, $errline)
  {
      if (!(error_reporting() & $errno)) {
          // This error code is not included in error_reporting, so let it fall
          // through to the standard PHP error handler
          return false;
      }

      switch ($errno) {
      case E_USER_ERROR:
          echo "<b>My ERROR</b> [$errno] $errstr<br />\n";
          echo "  Fatal error on line $errline in file $errfile";
          echo ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
          echo "Aborting...<br />\n";
          exit(1);
          break;

      case E_USER_WARNING:
          echo "<b>My WARNING</b> [$errno] $errstr<br />\n";
          break;

      case E_USER_NOTICE:
          echo "<b>My NOTICE</b> [$errno] $errstr<br />\n";
          break;

      default:
          echo "Unknown error type: [$errno] $errstr<br />\n";
          break;
      }
      $args=func_get_args();
      print_r($args);
      die();

      /* Don't execute PHP internal error handler */
      return true;
  }

  $old_error_handler = set_error_handler("myErrorHandler");


  /* void database connection */
  $dbConnect = 'no';
  /* void loading includes.lst in app folders */
  $cfgAvoidIncludesLst = 'yes';
  /* load yeapf directly */
  ini_set('display_startup_errors', 1);
  ini_set('display_errors', 1);
  error_reporting(-1);

  (@include_once("includes/yeapf.functions.php")) || die("yeapf.functions not found");

  /* comment lines to be deleted from each .js file */
  $headers = array(
               "* tools/compile-yloader.php",
               "* YeAPF 0.8.63-242 built on 2019-11-29 09:22 (-2 DST)",
               "* Copyright (C) 2004-2019 Esteban Daniel Dortta - dortta@yahoo.com - MIT License",
               "* 2019-09-09 08:27:18 (-2 DST)"
             );

  /* yloader source code */
  chdir("app-src/js");
  $yeapfJS = _file("yloader-src.js");
  chdir("../../");


  /* minimal clean-up */
  $yeapfJS = str_replace("\r\n","\n", $yeapfJS);
  $yeapfJS = str_replace("\n\n","\n", $yeapfJS);

  /* just keep first comment lines */
  foreach($headers as $h) {
    $p1=strpos($yeapfJS, $h, 365);
    while (!($p1===FALSE)) {
      $p2 = $p1 + strlen($h);
      while (($p1>0) && (substr($yeapfJS,$p1,1)!="\n"))
        $p1--;
      $yeapfJS = substr($yeapfJS,0,$p1).substr($yeapfJS,$p2);
      $p1=strpos($yeapfJS, $h, $p2);
    }
  }


  /* eliminate sourceMappingURL pragma
   * //# sourceURL=
   * //@ sourceURL=
   */
  $_sourceURL=array("//# sourceURL=", "//@ sourceURL=");
  foreach($_sourceURL as $sU) {
    $p2=0;
    do {
      $p1=strpos($yeapfJS, $sU,$p2);
      if (!($p1===false)) {
        $p2=strpos($yeapfJS, "\n", $p1);
        $yeapfJS=substr($yeapfJS,0,$p1).substr($yeapfJS,$p2);
      }
    } while ($p1);
  }


  $yeapfJS = str_replace("\t", "  ",$yeapfJS);
  while (strpos($yeapfJS," \n")===true)
    $yeapfJS = str_replace(" \n", "\n", $yeapfJS);
  $yeapfJS = str_replace("\n\n", "\n",$yeapfJS);

  /* save yloader.js to temp dir */
  $temp = sys_get_temp_dir().'/yloader-'.dechex(date('U'));
  file_put_contents($temp, $yeapfJS);


  if ($isCYGWIN)
    $temp=str_replace("\\", "\\\\", $temp);
  echo $temp;
?>
