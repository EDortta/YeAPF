<?php
/*
    skel/webApp/tasks.php
    YeAPF 0.8.63-131 built on 2019-07-24 09:00 (-3 DST)
    Copyright (C) 2004-2019 Esteban Daniel Dortta - dortta@yahoo.com - MIT License
    2019-07-24 09:00:47 (-3 DST)
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