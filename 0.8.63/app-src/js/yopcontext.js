/***********************************************************************
 * app-src/js/yopcontext.js
 * YeAPF 0.8.63-104 built on 2019-07-10 19:52 (-3 DST)
 * Copyright (C) 2004-2019 Esteban Daniel Dortta - dortta@yahoo.com
 * 2018-10-08 12:43:10 (-3 DST)
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

function IsSafari() {
  var is_safari = navigator.userAgent.toLowerCase().indexOf('safari/') > -1;
  return is_safari;

}

function getAndroidVersion(ua) {
    ua = (ua || navigator.userAgent).toLowerCase(); 
    var match = ua.match(/android\s([0-9\.]*)/);
    return match ? match[1] : false;
};

function isOnMobile() {
  var ret=false;
  if (typeof mosync != 'undefined') {
    ret = mosync.isAndroid || mosync.isIOS || mosync.isWindowsPhone;
  } else
    ret=/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
  return ret;
};

