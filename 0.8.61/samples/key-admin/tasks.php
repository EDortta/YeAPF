<?php
/*
    samples/key-admin/tasks.php
    YeAPF 0.8.61-153 built on 2018-11-30 07:00 (-2 DST)
    Copyright (C) 2004-2018 Esteban Daniel Dortta - dortta@yahoo.com
    2018-11-30 07:00:56 (-2 DST)
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