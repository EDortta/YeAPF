<?php
/*
    skel/webApp/ydbg.php
    YeAPF 0.8.60-24 built on 2018-05-15 18:13 (-3 DST)
    Copyright (C) 2004-2018 Esteban Daniel Dortta - dortta@yahoo.com
    2017-08-28 19:44:55 (-3 DST)

    skel/webApp / index.php
    This file cannot be modified within skel/webApp
    folder, but it can be copied and changed outside it.
*/
  error_reporting(E_ALL | E_STRICT);
  ini_set('display_errors', TRUE);
  ini_set('display_startup_errors', TRUE);

  function _yErrorHandler_($errno, $errstr, $errfile, $errline)
  {
    if (!(error_reporting() & $errno)) {
        // This error code is not included in error_reporting
        return;
    }

    switch ($errno) {
    case E_USER_ERROR:
        echo "<b>My ERROR</b> [$errno] $errstr<br />\n";
        echo "  Fatal error on line $errline in file $errfile";
        echo ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
        echo "Aborting...<br />\n";
        exit(1);
        break;

    case E_USER_WARNING:
        echo "<b>My WARNING</b> [$errno] $errstr<br />\n&nbsp;&nbsp;$errfile:$errline<br />\n";
        break;

    case E_USER_NOTICE:
        echo "<b>My NOTICE</b> [$errno] $errstr<br />\n&nbsp;&nbsp;$errfile:$errline<br />\n";
        break;

    default:
        echo "Unknown error type: [$errno] $errstr<br />\n&nbsp;&nbsp;$errfile:$errline<br />\n";
        break;
    }

    /* Don't execute PHP internal error handler */
    return true;
  }

  set_error_handler("_yErrorHandler_");

  $script = getenv("QUERY_STRING");

  if (file_exists($script))
    include_once "$script";
  else
    die("FILE '$script' NOT FOUND");

?>
