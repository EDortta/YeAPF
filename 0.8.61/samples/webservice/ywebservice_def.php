<?php
  $yWebService->configureWSDL("$webServiceName", "urn:$webServiceName");

  /*************************************************************
    Data Type Definition
  **************************************************************/
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
