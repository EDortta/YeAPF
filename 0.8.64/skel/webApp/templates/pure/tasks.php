<?php
/*
    skel/webApp/templates/pure/tasks.php
    YeAPF 0.8.64-7 built on 2020-03-20 13:04 (-3 DST)
    Copyright (C) 2004-2020 Esteban Daniel Dortta - dortta@yahoo.com - MIT License
    2020-03-20 13:04:36 (-3 DST)
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