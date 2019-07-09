<?php
/*
    skel/webApp/templates/bs4/tasks.php
    YeAPF 0.8.63-92 built on 2019-07-09 12:00 (-3 DST)
    Copyright (C) 2004-2019 Esteban Daniel Dortta - dortta@yahoo.com
    2019-07-09 12:00:16 (-3 DST)
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