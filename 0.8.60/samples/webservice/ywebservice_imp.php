<?php
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
