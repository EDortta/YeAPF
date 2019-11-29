<?php
/*
    skel/webApp/templates/skeleton/tasks.php
    YeAPF 0.8.63-242 built on 2019-11-29 09:22 (-2 DST)
    Copyright (C) 2004-2019 Esteban Daniel Dortta - dortta@yahoo.com - MIT License
    2019-11-29 09:22:24 (-2 DST)
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