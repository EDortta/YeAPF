<?php
/*
    skel/webApp/tasks.php
    YeAPF 0.8.60-24 built on 2018-05-15 18:13 (-3 DST)
    Copyright (C) 2004-2018 Esteban Daniel Dortta - dortta@yahoo.com
    2017-08-28 19:44:55 (-3 DST)
*/

  require_once "yeapf.php";

  /* you need to create a CROND entry calling this script as in the next example:
      http://example.com/tasks.php?s=yeapf&a=tick
  */
  if ($s=='yeapf') {
    switch($a) {
      case 'tick':
        require_once "yeapf_ticker.php";
        break;
    }
  }

?>