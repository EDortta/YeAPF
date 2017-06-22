<?php
/*
    skel/service/tasks.php
    YeAPF 0.8.58-62 built on 2017-06-22 17:24 (-3 DST)
    Copyright (C) 2004-2017 Esteban Daniel Dortta - dortta@yahoo.com
    2017-06-22 17:24:55 (-3 DST)
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