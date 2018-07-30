<?php
/*
    samples/webservice/ywebservice_imp.php
    YeAPF 0.8.61-26 built on 2018-07-30 19:34 (-3 DST)
    Copyright (C) 2004-2018 Esteban Daniel Dortta - dortta@yahoo.com
    2018-07-30 19:33:47 (-3 DST)

    YeAPF/samples
    webservice sample
    (C) 2008-2018 Esteban Daniel Dortta
*/
  global $appName;

  function getStatus()
  {
    logAction("getStatus()");
    if (true)
      $ret=1024;
    else
      $ret=0;
    logAction("getStatus() = $ret");
    return $ret;
  }

?>
