/*********************************************
 * app-src/js/yloader-src.js
 * YeAPF 0.8.63-106 built on 2019-07-11 09:42 (-3 DST)
 * Copyright (C) 2004-2019 Esteban Daniel Dortta - dortta@yahoo.com - MIT License
 * 2019-05-01 13:04:27 (-3 DST)
 * First Version (C) 2014 - esteban daniel dortta - dortta@yahoo.com
 * Purpose:  Build a monolitic YeAPF script so
 *           it can be loaded at once
**********************************************/

if (typeof console === 'undefined')
  if (typeof window != 'undefined')
    var console = (window.console = window.console || {});
  else
    var console = {};

(
  function () {
    var methods = [
      'assert', 'clear', 'count', 'debug', 'dir', 'dirxml', 'error',
      'exception', 'group', 'groupCollapsed', 'groupEnd', 'info', 'log',
      'markTimeline', 'profile', 'profileEnd', 'table', 'time', 'timeEnd',
      'timeStamp', 'trace', 'warn'
    ], method;
    for (var i=0; i<methods.length; i++) {
      method = methods[i];
      if (!console[method]) console[method] = function () {};
    }
  }
)();

console.log("YeAPF 0.8.63-106 built on 2019-07-11 09:42 (-3 DST)");

#include('yopcontext.js')
#include('ydebug.js')

var yloaderBase = function () {
  var that = {};

  (function () {
    var mydir;
    if (typeof document=='object') {
      var scripts= document.getElementsByTagName('script');
      var path= scripts[scripts.length-1].src.split('?')[0];
      mydir= path.split('/').slice(0, -1).join('/')+'/';
    } else
      mydir='./';
    that.selfLocation = mydir;
    _dump("Loading from "+mydir);
  })();

  /* in order to work with node */
  var auxW = "undefined" ==typeof window?{}:window;
  var auxN = "undefined" ==typeof navigator?{userAgent:'nodejs'}:navigator;

  that.isNode = (typeof module !== 'undefined' && module.exports);
  that.isWorker = (typeof importScripts == 'function');
  that.isMobile = (!that.isWorker) && (typeof auxW.orientation !== 'undefined');
  that.isChromeExtension = (!that.isWorker) && ((auxW.chrome && chrome.runtime && chrome.runtime.id) || (that.selfLocation.substr(0,17)=='chrome-extension:'));
  that.isChromeSandbox = (!that.isWorker) && ((that.isChromeExtension) && !(chrome.storage));
  that.isWebkit = /(safari|chrome)/.test(auxN.userAgent.toLowerCase());
  that.webKitVersion = (
    function() {
      var ret="530.00";
      var agentIds = auxN.userAgent.match(/([A-Za-z]*)\/([0-9A-Za-z\.]*)/g);
      for(var i=0; i<(agentIds || []).length; i++) {
        if (agentIds[i].substr(0,11)=='AppleWebKit') {
          ret = agentIds[i].substr(12);
        }
      }
      return ret;
    }
  )();

  that.operatingSystem = (
    function() {
      var agentIds = auxN.userAgent.match(/(\([A-z\ 0-9\.\;\,\-\/\:]*)\)/g) || [];
      var OSes=(agentIds[0] || '').replace(/[^a-zA-Z0-9\ \.\;]+/g, "").split(";") || ["Unknown", "Unknown"];
      for(var i=0; i<(OSes || []).length; i++) {
        OSes[i]=trim(OSes[i] || "Unknown");
      }
      return OSes;
    }
  )();

  that.loadLibrary = function (jsFileName, elementId, onload) {

    var libFileName;
    if (jsFileName>'') {
      if (that.selfLocation.substr(0,17)!='chrome-extension:')
        jsFileName = that.selfLocation+'/'+jsFileName+'?v=0.8.63';
      else {
        // chrome.runtime.id
        var auxChromeExtension = that.selfLocation.split('/');
        var auxLocation = '';
        for (var i=3; i<auxChromeExtension.length; i++) {
          if (auxChromeExtension[i]>'') {
            if (auxLocation>'')
              auxLocation+='/';
            auxLocation+=auxChromeExtension[i];
          }
        }
        jsFileName = auxLocation+'/'+jsFileName+'?v=0.8.34';
      }
      jsFileName = jsFileName.replace(/\/\//g,'\/');
      jsFileName = jsFileName.replace('http:/','http://');
      jsFileName = jsFileName.replace('https:/','https://');
      jsFileName = jsFileName.replace('file:/','file://');
      var auxName = jsFileName.split('/');
      if (auxName.length>0)
        libFileName = auxName[auxName.length-1];

      if (typeof importScripts == 'function')
        importScripts(jsFileName);
      else {
        var head  = document.getElementsByTagName('head')[0];
        if (jsFileName.indexOf('.css')<0) {
          var _script    =  document.createElement('script');
          _script.type   = (jsFileName.indexOf('.js')>0)?'text/javascript':'text/text';
          _script.onload = onload;
          _script.src    = jsFileName;
          head.appendChild(_script);
        } else {
          var _link    = document.createElement('link');
          _link.id     = elementId || libFileName;
          _link.rel    = 'stylesheet';
          _link.type   = 'text/css';
          _link.media  = 'all';
          _link.onload = onload;
          _link.href   = jsFileName;
          head.appendChild(_link);
        }
      }
      _dump(libFileName+' added');

    }
  };

  /*
  if (that.isMobile) {
    that.loadLibrary('wormhole.js');
    document.addEventListener(
      "backbutton",
      function() { mosync.app.exit(); },
      true);

  }
  */

  if (!that.isWorker) {
    if (!that.isNode) {
      window.addEventListener("load", function() {
        var elem = (document.compatMode === "CSS1Compat") ?
            document.documentElement :
            document.body;

        var appScreen=document.getElementById('screen');
        if (appScreen)
          appScreen.style.width = elem.clientWidth + 'px';

      });      
    }
  }

  return that;
};
_dump("R1");
var yloader=yloaderBase();
_dump("R2");

#include('ymisc.js')
_dump("ymisc");
#include('yanalise.js')
_dump("yanalise");
#include('ycfgdb.js')
_dump("ycfgdb");
#include('ydragdrop.js')
_dump("ydragdrop");
#include('ytabnav.js')
_dump("ytabnav");
#include('ycomm.js')
_dump("ycomm");
#include('ycomm-ajax.js')
_dump("ycomm-ajax");
#include('ycomm-rest.js')
_dump("ycomm-rest");
#include('ycomm-dom.js')
_dump("ycomm-dom");
#include('ycomm-msg.js')
_dump("ycomm-msg");
#include('ycomm-sse.js')
_dump("ycomm-sse");
#include('ycomm-websocket.js')
_dump("ycomm-websocket");
#include('ycalendar.js')
_dump("ycalendar");
#include('ydyntable.js')
_dump("ydyntable");
#include('yinterface.js')
_dump("yinterface");

