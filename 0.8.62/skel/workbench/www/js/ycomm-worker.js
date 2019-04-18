/*********************************************
 * skel/workbench/www/js/ycomm-worker.js
 * YeAPF 0.8.62-70 built on 2019-04-18 20:33 (-3 DST)
 * Copyright (C) 2004-2019 Esteban Daniel Dortta - dortta@yahoo.com
 * 2019-04-18 20:33:12 (-3 DST)
 * First Version (C) 2014 - esteban daniel dortta - dortta@yahoo.com
**********************************************/

importScripts('yloader.js');

yCommWorker = function(e) {
  var data = e.data;
  switch(data.cmd) {
    case 'start' :
      {
        var callbackFunctionBackup = data.invoker.callbackFunction;

        /* invoker function cannot be filled
         * On worker, ycomm-ajax will always return a text (not xml) */
        data.invoker.callbackFunction = function(responseText) {
          var invokerImg = data.invoker;
          invokerImg.callbackFunction = callbackFunctionBackup;
          var dataPack = {
            cmd:'response',
            invoker: data.invoker,
            text: responseText
          };
          self.postMessage(dataPack);
        };

        if (typeof data.invoker.limits=='object')
          data.invoker.limits._start_ = 0;

        ycomm.invoke(
          data.invoker
        );
      }
  }
}

self.addEventListener('message', yCommWorker, false);
