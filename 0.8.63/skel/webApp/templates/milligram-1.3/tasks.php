<?php
/*
    skel/webApp/templates/milligram-1.3/tasks.php
    YeAPF 0.8.63-259 built on 2020-02-26 11:44 (-3 DST)
    Copyright (C) 2004-2020 Esteban Daniel Dortta - dortta@yahoo.com - MIT License
    2020-02-26 11:44:34 (-3 DST)
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