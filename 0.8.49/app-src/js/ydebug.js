/*********************************************
 * app-src/js/ydebug.js
 * YeAPF 0.8.49-32 built on 2016-06-25 10:34 (-3 DST)
 * Copyright (C) 2004-2016 Esteban Daniel Dortta - dortta@yahoo.com
 * 2016-06-18 12:50:14 (-3 DST)
 * First Version (C) 2014 - esteban daniel dortta - dortta@yahoo.com
**********************************************/
//# sourceURL=app-src/js/ydebug.js

ydbg = function() {
  var that = {};
  that.logLevel = 0;
  that.logFlag = 0;
  return that;
};

function __dump__(aLineConsole) {
  if (typeof mosync == "object")
    mosync.rlog(aLineConsole);
  else
    console.log(aLineConsole);

}

// This functions can be used with mosync or browser
// and replaces console.log() / mosync.rlog()
function _dump() {
  var aLine = '',
      aLineConsole,
      aAnArgument;
  for (var i=0; i<arguments.length; i++) {
    if (aLine>'')
      aLine+=', ';
    aAnArgument = arguments[i];
    if (typeof aAnArgument=="object")
      aAnArgument = aAnArgument.toString();

    aLine += aAnArgument;
  }
  aLineConsole = /* arguments.callee.caller.name+': '+*/aLine;

  __dump__(aLine);
  /* OBSOLETO 2016-02-24
  if ((typeof jsDumpEnabled != "undefined") && (jsDumpEnabled)) {
    aLine = '<b>'+arguments.callee.caller.name+'</b> <small>'+aLine+'</small>';

    var mainBody=__getMainBody();
    var isReady = (typeof mainBody.$ == 'function') && (mainBody.document.body != null);
    if (isReady) {
      var debug = mainBody.y$('debug');
      if (!debug) {
        debug = mainBody.document.createElement('div');
        debug.id='debug';
        debug.className='debug';
        setOpacity(debug,60);
        debug.onmouseover = __debugMouseOver;
        debug.onmouseout = __debugMouseOut;
        mainBody.document.body.appendChild(debug);
      }
      if (debug.innerHTML=='')
        debug.innerHTML = '<b>YeAPF!</b>';
      else {
        var auxText = debug.innerHTML;
        auxText = auxText.split('<br>');
        auxText = auxText.splice(Math.max(0,auxText.length-50));
        auxText = auxText.join('<br>');
        debug.innerHTML = auxText;
      }
      debug.innerHTML += '<br>'+aLine;
      debug.scrollTop = debug.scrollHeight;
    } else {
      aLine = aLine.replace('<small>','\n    ');
      aLine = aLine.replace('</small>','\n');
      aLine = aLine.replace('<b>','');
      aLine = aLine.replace('</b>','');
      alert(aLine);
    }
  }
  */
}

function _setLogFlagLevel(aLogFlag, aLevel) {
  ydbg.logFlag=aLogFlag;
  ydbg.logLevel=aLevel;
}

function _dumpy(logFlag, logLevel) {
  if (ydbg.logFlag & logFlag) {
    if (ydbg.logLevel>=logLevel) {
      var aLine = '', aAnArgument;
      for (var i=2; i<arguments.length; i++) {
        if (aLine>'')
          aLine+=', ';
        aAnArgument = arguments[i];
        if (typeof aAnArgument=="object")
          aAnArgument = aAnArgument.toString();
        aLine += aAnArgument;
      }
      __dump__(aLine);
    }
  }

}
