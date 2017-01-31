/*********************************************
 * app-src/js/yloader.js
 * YeAPF 0.8.54-1 built on 2017-01-31 11:51 (-2 DST)
 * Copyright (C) 2004-2017 Esteban Daniel Dortta - dortta@yahoo.com
 * 2017-01-11 15:48:34 (-2 DST)
 * First Version (C) 2014 - esteban daniel dortta - dortta@yahoo.com
 * Purpose:  Detect on which device this app is running
 *           and load wormhole.js library if it is mobile.
 *           Load each library component dinamically
 **********************************************/


/* http://stackoverflow.com/questions/3326650/console-is-undefined-error-for-internet-explorer */
if (typeof console === 'undefined')
  var console = (window.console = window.console || {});

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

if (typeof yloader=='undefined') {
  var yloader = function () {
    var that = {};

    (function () {
      var scripts= document.getElementsByTagName('script');
      var path= scripts[scripts.length-1].src.split('?')[0];
      var mydir= path.split('/').slice(0, -1).join('/')+'/';
      that.selfLocation = mydir;
      console.log("Loading from "+mydir);
    })();

    that.isMobile = (typeof window.orientation !== 'undefined');
    that.isChromeExtension = ((window.chrome && chrome.runtime && chrome.runtime.id) || (that.selfLocation.substr(0,17)=='chrome-extension:'));

    that.loadLibrary = function (jsFileName) {

      var libFileName;
      if (jsFileName>'') {
        if (that.selfLocation.substr(0,17)!='chrome-extension:')
          jsFileName = that.selfLocation+'/'+jsFileName+'?v=0.8.54';
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
        var auxName = jsFileName.split('/');
        if (auxName.length>0)
          libFileName = auxName[auxName.length-1];

        var loadMethod = that.isChromeExtension?1:0;
        if (loadMethod==0) {
          try {
            if (jsFileName.indexOf('.js')>0)
              document.write('<script type="text/javascript" src="'+ jsFileName +'"></s'+ 'cript>');
            else if (jsFileName.indexOf('.css')>0)
              document.write('<link rel="stylesheet" href="'+ jsFileName +'" type="text/css" media="screen" title="no title" charset="utf-8">');
            else
              loadMethod = 2;

            if (loadMethod==0)
              console.log(libFileName+' loaded');
          } catch(e) {
            loadMethod=1;
          }
        }
        if (loadMethod==1) {
          var _script = document.createElement('script');
          _script.type=(jsFileName.indexOf('.js')>0)?'text/javascript':(jsFileName.indexOf('.css')>0)?'text/css':'text/text';
          _script.src=jsFileName;
          var _header=document.getElementsByTagName('head');
          _header[_header.length-1].appendChild(_script);
          console.log(libFileName+' added');
        }

      }
    };

    if (that.isMobile) {
      /*
      that.loadLibrary('wormhole.js');
      document.addEventListener(
        "backbutton",
        function() { mosync.app.exit(); },
        true);
      */

    }

    that.libList = [
      'yopcontext.js',
      'ymisc.js',     'ydebug.js',     'yanalise.js',   'ycfgdb.js',
      'ydragdrop.js', 'ytabnav.js',
      'ycomm.js',     'ycomm-ajax.js', 'ycomm-rest.js', 'ycomm-dom.js', 
      'ycomm-msg.js', 'ycomm-sse.js', 
      'ycalendar.js', 'ydyntable.js'
    ];

    for(var i=0; i<that.libList.length; i++)
      that.loadLibrary(that.libList[i]);

    window.addEventListener("load", function() {
      var elem = (document.compatMode === "CSS1Compat") ?
          document.documentElement :
          document.body;

      var appScreen=document.getElementById('screen');
      if (appScreen)
        appScreen.style.width = elem.clientWidth + 'px';

    }, false);

    return that;
  };

  yloader();
}
