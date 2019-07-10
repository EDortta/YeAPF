<?php
/*
    samples/webservice/ywebservice_imp.php
    YeAPF 0.8.63-104 built on 2019-07-10 19:52 (-3 DST)
    Copyright (C) 2004-2019 Esteban Daniel Dortta - dortta@yahoo.com
    2018-08-02 22:38:27 (-3 DST)

    YeAPF/samples
    webservice sample
    (C) 2008-2018 Esteban Daniel Dortta
*/
  global $webServiceName;

  function ping($counter) {
    global $webServiceName;

    $ret=0;

    /* THIS IS ONLY AN EXAMPLE
       In this sample, 'ping' only works if 'ping' is enabled
       you need to replace 'true' with your own avaliation code */
    if (true) {
      /* you can attend to SOAP events as in query and rest function */
      $soapFunctionName="soap_$webServiceName";

      if (function_exists("$soapFunctionName")) {
        /* you need to create an associative array with parameters in order to attend using soap_%(appName)() */
        $params = array('counter' => $counter);
        /* then, you call soap_%(appName)() with those parameters */
        $ret = $$soapFunctionName("ping", $params);
        /*
          of course, your soap_%(appName)() will answer with an SQL statement or an associative array
          because it uses em_%(appName)() to respond the query
          It's your responsability to make the correct transformation from that to function answer
         */
      }
      $ret = date('U');
    }
    return $ret;
  }

  function getStatus()
  {
    logAction("getStatus()");
    /* analise your local status and return the correct value */
    if (true)
      $ret=1024;
    else
      $ret=0;
    logAction("getStatus() = $ret");
    return $ret;
  }

?>
