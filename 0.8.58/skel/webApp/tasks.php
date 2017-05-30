<?php
/*
    skel/webApp/tasks.php
    YeAPF 0.8.58-13 built on 2017-05-30 11:50 (-3 DST)
    Copyright (C) 2004-2017 Esteban Daniel Dortta - dortta@yahoo.com
    2016-06-02 14:53:21 (-3 DST)
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