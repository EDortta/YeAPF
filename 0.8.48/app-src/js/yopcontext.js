/***********************************************************************
 * app-src/js/yopcontext.js
 * YeAPF 0.8.48-103 built on 2016-05-24 18:54 (-3 DST)
 * Copyright (C) 2004-2016 Esteban Daniel Dortta - dortta@yahoo.com
 * 2016-01-23 22:00:19 (-3 DST)
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
}
 
function isOnMobile() {
  var ret=false;
  if (typeof mosync != 'undefined') {
    ret = mosync.isAndroid || mosync.isIOS || mosync.isWindowsPhone;
  }
  return ret;
}

