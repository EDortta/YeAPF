<?php
/*
    samples/webservice/ywebservice_def.php
    YeAPF 0.8.64-7 built on 2020-03-20 13:04 (-3 DST)
    Copyright (C) 2004-2020 Esteban Daniel Dortta - dortta@yahoo.com - MIT License
    2018-08-02 22:38:27 (-3 DST)

    YeAPF/samples
    webservice sample
    (C) 2008-2018 Esteban Daniel Dortta
*/
  global $webServiceName;

  $yWebService->configureWSDL("$webServiceName", "urn:$webServiceName");

  /*************************************************************
    Data Type Definition
  **************************************************************/
  $yWebService->register("ping",
      array("counter" => "xsd:integer"),
      array("return"  => "xsd:integer"),
      "urn:$webServiceName",
      "urn:$webServiceName#getStatus",
      "rpc",
      "encoded",
      "Ping the service $webServiceName. Returns 0 or server datetime"
  );

  $yWebService->register("getStatus",        // method name
      array(),                              // input parameters
      array("return" => "xsd:integer"),     // output parameters
      "urn:$webServiceName",                     // namespace
      "urn:$webServiceName#getStatus",           // soapaction
      "rpc",                                // style
      "encoded",                            // use
      "Get $webServiceName Status"                // documentation
  );

?>
