<?php
  /*
    tools/compile-yloader.php
    YeAPF 0.8.60-119 built on 2018-06-08 05:44 (-3 DST)
    Copyright (C) 2004-2018 Esteban Daniel Dortta - dortta@yahoo.com
    2018-05-30 11:21:05 (-3 DST)

    This script will produce monolite version of yloader.js
    to be used with different applications.

   */

  /* void database connection */
  $dbConnect = 'no';
  /* void loading includes.lst in app folders */
  $cfgAvoidIncludesLst = 'yes';
  /* load yeapf directly */
  require_once "includes/yeapf.functions.php";

  /* comment lines to be deleted from each .js file */
  $headers = array(
               "* tools/compile-yloader.php",
               "* YeAPF 0.8.60-119 built on 2018-06-08 05:44 (-3 DST)",
               "* Copyright (C) 2004-2018 Esteban Daniel Dortta - dortta@yahoo.com",
               "* 2018-05-30 11:21:05 (-3 DST)"
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
