<?php
/*
    samples/webservice/ywebservice_def.php
    YeAPF 0.8.61-26 built on 2018-07-30 19:34 (-3 DST)
    Copyright (C) 2004-2018 Esteban Daniel Dortta - dortta@yahoo.com
    2018-07-30 19:33:47 (-3 DST)

    YeAPF/samples
    webservice sample
    (C) 2008-2018 Esteban Daniel Dortta
*/
  global $appName;

  $yWebService->configureWSDL("$appName", "urn:$appName");

  /*************************************************************
    Data Type Definition
  **************************************************************/
  $yWebService->register("getStatus",        // method name
      array(),                              // input parameters
      array("return" => "xsd:integer"),     // output parameters
      "urn:$appName",                     // namespace
      "urn:$appName#getStatus",           // soapaction
      "rpc",                                // style
      "encoded",                            // use
      "Get $appName Status"                // documentation
  );

?>
