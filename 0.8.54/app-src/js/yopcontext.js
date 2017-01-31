/***********************************************************************
 * app-src/js/yopcontext.js
 * YeAPF 0.8.54-1 built on 2017-01-31 11:51 (-2 DST)
 * Copyright (C) 2004-2017 Esteban Daniel Dortta - dortta@yahoo.com
 * 2016-06-23 12:11:10 (-2 DST)
 * First Version (C) 2014 - esteban daniel dortta - dortta@yahoo.com
 *
 * This is a set of function that helps to recognize operational context
 **********************************************************************/
//# sourceURL=app-src/js/yopcontext.js

function getInternetExplorerVersion() {
/* http://msdn.microsoft.com/en-us/library/ms537509(v=vs.85).aspx
 * Returns the version of Internet Explorer or a -1
 * (indicating the use of another browser).
 */

  var rv = -1; // Return value assumes failure.
  if (navigator.appName == 'Microsoft Internet Explorer')
  {
    var ua = navigator.userAgent;
    var re  = new RegExp("MSIE ([0-9]{1,}[\.0-9]{0,})");
    if (re.exec(ua) != null)
      rv = parseFloat( RegExp.$1 );
  }
  return rv;
}

function isInternetExplorer() {
  return (getInternetExplorerVersion() >= 0);
};

function getAndroidVersion(ua) {
    ua = (ua || navigator.userAgent).toLowerCase(); 
    var match = ua.match(/android\s([0-9\.]*)/);
    return match ? match[1] : false;
};
 
function isOnMobile() {
  var ret=false;
  _dump(navigator.userAgent);
  if (typeof mosync != 'undefined') {
    ret = mosync.isAndroid || mosync.isIOS || mosync.isWindowsPhone;
  } else
    ret=/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
  return ret;
};

