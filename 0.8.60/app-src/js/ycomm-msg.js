/*********************************************
 * app-src/js/ycomm-msg.js
 * YeAPF 0.8.60-119 built on 2018-06-08 05:44 (-3 DST)
 * Copyright (C) 2004-2018 Esteban Daniel Dortta - dortta@yahoo.com
 * 2018-05-30 11:21:04 (-3 DST)
 * First Version (C) 2014 - esteban daniel dortta - dortta@yahoo.com
 * These routines were written in order to help interprocess process messages
 * but as an remote process messages implementation.
 * Or, IPC over RPC as you like.
 * In Windows(TM) and Linux you would send a message to an application
 * meanwhile, with YeAPF you will send messages to connected users.
 * As this was not inteded to send chat messages, is correct to
 * send messages to and only to connected users.
 **********************************************/
//# sourceURL=app-src/js/ycomm-msg.js

var ycommMsgBase = function() {
  var that = {
    messagePeekerTimer: null,
    messageStack: [],
    msgProcs: [],
    _dbgFlag_noMessageProcessorPresent: false,
    msgCount: 0,
    serverOfflineFlag: null
  };

  that.grantMsgProc = function(aInterval) {
    /* caso venha sem parámtros, calcular um tempo prudente de no máximo 20 segs
     * Isso acontece quando o servidor devolveu uma resposta errada
     * e queremos que o sistema de mensagens continue em operação. */
    if ((aInterval === undefined) || (aInterval <= 0))
      aInterval = Math.min(20000, messagePeekerInterval * 2);

    if (that.messagePeekerTimer === undefined) {
      if (that.msgCount === 0)
        _dumpy(4, 1, "Configuring receivers interval to " + aInterval + 'ms');
      that.messagePeekerTimer = setTimeout(ycomm.msg.peek, aInterval);
    } else
      _dumpy(4, 1, "Receivers interval already defined");
  };

  that.feedBack = function() {
    if (dRowCount > 0) {
      that.msgCount++;
      for (var j in xData) {
        if (!isNaN(parseInt(j))) {
          var aux = xData[j];

          that.messageStack.push(new Array(aux['sourceUserId'],
            aux['message'],
            aux['wParam'],
            aux['lParam']));
        }
      }

      if (that.messageStack.length > 0) {
        if (that.msgProcs.length == 0) {
          if (!that._dbgFlag_noMessageProcessorPresent)
            if (jsDumpEnabled)
              window.alert("Messages arriving at  '" + _CurrentFileName + "'  but there is not\na registered message processor in order to receive it.\nUse _registerMsgProc() to register it");
          that._dbgFlag_noMessageProcessorPresent = true;
        } else {
          while (that.messageStack.length > 0) {
            var oldLen = that.messageStack.length;
            for (var i = 0; i < that.msgProcs.length; i++) {
              // _dumpy(4,1,"Calling: "+that.msgProcs[i]);
              var auxCallFunction = '<script>' + that.msgProcs[i] + '();</' + 'script>';
              auxCallFunction.evalScripts();
            }
            if (oldLen == that.messageStack.length)
              that.messageStack.shift();
          }
        }
      }

    }
    that.grantMsgProc(messagePeekerInterval);
  };

  that.peek = function() {
    clearTimeout(that.messagePeekerTimer);
    that.messagePeekerTimer = null;

    var ts = new Date();
    var auxParameters = 's=y_msg&u=' + u + '&a=peekMessage&formID=' + formID + '&ts=' +
      ts.getTime() + '&callBackFunction=ycomm.msg.feedBack&messagePeekerInterval=' + messagePeekerInterval;
    var aux = new Ajax.Request(
      'query.php', {
        method: 'get',
        asynchronous: true,
        parameters: auxParameters,
        onComplete: function(transport) {
          if (transport.status == 200)
            _QUERY_RETURN(transport);
          else {
            _dumpy(4, 1, "*** XMLHttpRequest call failure");
            setTimeout(that.notifyServerOffline, 500);
          }
        }
      }
    );
  };

  that.postMessage = function(aTargetUserID, aMessage, aWParam, aLParam, aBroadcastCondition) {
    var ts = new Date();
    if (aBroadcastCondition != undefined)
      var aux = '&targetUser=*&broadcastCondition="' + aBroadcastCondition + '"';
    else
      var aux = '&broadcastCondition=&targetUser=' + aTargetUserID;

    var auxParameters = 's=y_msg&u=' + u + '&a=postMessage' + aux + '&formID=' + formID +
      '&message=' + aMessage + '&wParam=' + aWParam + '&lParam=' + aLParam +
      '&ts=' + ts.getTime() + '&callBackFunction=ycomm.msg.feedBack';
    var aux = new Ajax.Request(
      'query.php', {
        method: 'get',
        asynchronous: false,
        parameters: auxParameters,
        onComplete: _QUERY_RETURN
      }
    );
  };

  that.cleanMsgQueue = function() {
    that.msgProcs.length = 0;
  };

  that.notifyServerOnline = function() {
    if ((that.serverOfflineFlag==null) || (that.serverOfflineFlag > 0)) {
      that.serverOfflineFlag = 0;
      var notificationArea = y$('notificationArea');
      if (notificationArea)
        notificationArea.style.display = 'none';

      if (typeof _notifyServerOnline =='function')
        setTimeout(_notifyServerOnline,500);        
    }
  };

  that.notifyServerOffline = function() {
    that.serverOfflineFlag=(that.serverOfflineFlag || 0)+1;
    
    var notificationArea = y$('notificationArea');
    if (!notificationArea) {
      notificationArea = document.createElement('div');
      notificationArea.id = 'notificationArea';
      notificationArea.setOpacity(90);
      document.body.appendChild(notificationArea);
      if (!getStyleRuleValue('.notificationArea')) {
        notificationArea.style.zIndex = 1000;
        notificationArea.style.position = 'absolute';
        notificationArea.style.left = '0px';
        notificationArea.style.top = '0px';
        notificationArea.style.border = '1px #900 solid';
        notificationArea.style.backgroundColor = '#fefefe';
      } else {
        notificationArea.className = 'notificationArea';
      }

      notificationArea.innerHTML = "<div style='padding: 32px'><big><b>Server Offline</b></big><hr>Your server has become offline or is misspelling answers when requested.<br>Wait a few minutes and try again later, or wait while YeAPF try again by itself</div>&nbsp;<br><img src='http://yeapf.com/images/yeapf-logo.png' style='width: 128px'>";
    }

    notificationArea.style.width = document.body.clientWidth + 'px';
    notificationArea.style.height = document.body.clientHeight + 'px';
    notificationArea.style.display = '';
  
    if (typeof _notifyServerOffline =='function')
      setTimeout(_notifyServerOffline,500);

    that.grantMsgProc();
  };

  that.registerMsgProc = function(aFunctionName) {
    var canAdd = true;
    _dumpy(4, 1, "Registering message receiver: " + aFunctionName);
    for (var i = 0; i < that.msgProcs.length; i++)
      if (that.msgProcs[i] == aFunctionName)
        canAdd = false;

    if (canAdd)
      that.msgProcs[that.msgProcs.length] = aFunctionName;

    that.grantMsgProc(messagePeekerInterval);
  };

  that.stopMsgProc = function() {
    clearTimeout(that.messagePeekerTimer);
  }

  return that;
}

addOnLoadManager(
  function()
  {
    if (typeof messagePeekerInterval=="undefined") {
      /*
       *  Si existe la bandera flags/debug.javascript, entonces, el tiempo de latencia es mayor
       *  para permitir poder depurar los eventos con calma
       */
      if (typeof jsDumpEnabled == "undefined")
        jsDumpEnabled = 0;

      if (jsDumpEnabled==1)
        messagePeekerInterval=15000;
      else
        messagePeekerInterval=750;
    }

  }
);

ycomm.msg = ycommMsgBase();
