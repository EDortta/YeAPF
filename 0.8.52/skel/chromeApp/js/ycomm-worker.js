/*********************************************
 * skel/chromeApp/js/ycomm-worker.js
 * YeAPF 0.8.52-159 built on 2016-12-23 16:56 (-2 DST)
 * Copyright (C) 2004-2016 Esteban Daniel Dortta - dortta@yahoo.com
 * 2016-12-23 16:56:59 (-2 DST)
 * First Version (C) 2014 - esteban daniel dortta - dortta@yahoo.com
**********************************************/
//# sourceURL=skel/chromeApp/js/ycomm-worker.js

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
