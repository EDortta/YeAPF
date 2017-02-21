/*********************************************
 * skel/MoSyncApp/LocalFiles/js/yifc.js
 * YeAPF 0.8.54-39 built on 2017-02-21 17:52 (-3 DST)
 * Copyright (C) 2004-2017 Esteban Daniel Dortta - dortta@yahoo.com
 * 2017-02-21 17:52:09 (-3 DST)
 * First Version (C) 2014 - esteban daniel dortta - dortta@yahoo.com
 * This object allows to communicate with ysandboxifc.js
 * and runs outside the sandbox.
 * IFC - Inter Frame Communication
 *********************************************/
//# sourceURL=skel/MoSyncApp/LocalFiles/js/yifc.js

var IFCObj = function () {
  var that = {};
  /* bind to this event in order to know if you have acces to
   * your restful interface. */
  that.statusChange = null;

  /* default sandbox frame */
  that.ySandboxFrame=null;

  that.findSandboxFrame = function() {
    if (frames && frames.ifc_frame) {
      _dumpy(1,1,"MAIN: looking for bridge from 'ifc_frame'");
      that.ySandboxFrame=frames.ifc_frame.contentWindow;
    } else {
      _dumpy(1,1,"MAIN: looking for bridge from 'ifc_frame'");
      that.ySandboxFrame=$frameByName('ifc_frame').contentWindow;
      if (typeof that.ySandboxFrame == "undefined") {
        if ((opener) && (opener.ifc_frame)) {
          _dumpy(1,1,"MAIN: looking for bridge from 'opener'");
          that.ySandboxFrame=opener.ifc_frame.contentWindow;
        }
      }
    }

    return that;
  };

  /* ifcMsgCounter is used to keep track of craving the sandbox
   * from the user application */
  that.ifcMsgCounter = 0;
  that.ifcMsgQueue = [];

  that.receiveMessage = function(event) {
    _dumpy(1,1,"MAIN: receiveMessage() from "+event.origin);
    /* only consider yeapf well structured messages */
    if (typeof event == 'object') {
      if ((event.data.s !== undefined) && (event.data.a !== undefined)) {
        var sa=event.data.s+'.'+event.data.a;
        _dumpy(1,1,"MAIN: {0} ({1})".format(sa,event.origin));
        switch(sa) {
          case 'yeapf.offline':
          case 'yeapf.online':
            if (typeof that.statusChange == 'function')
              that.statusChange(event.data.a);
            break;
          case 'yeapf.getConnParams':
            _dumpy(1,1,"MAIN: getConnParams");

            var data=null;
            that.getConnParams( function(result) {
              event.source.postMessage({s:'yeapf', a:'connParams', limits: {connParams: result}},"*");
            });

            break;
          case 'yeapf.dataPleaded':
            var queueEntry = that.ifcMsgQueue[event.data.callbackId];
            if (queueEntry) {
              if (typeof queueEntry.aCallbackFunction=='function')
                queueEntry.aCallbackFunction(200, 0, event.data.data);
              else if (typeof queueEntry.aCallbackFunction=='string') {
                var fnName = queueEntry.aCallbackFunction.split('.');
                var fn = null;
                if (fnName.length==1)
                  fn = window[queueEntry.aCallbackFunction];
                else {
                  var auxObj = window[fnName[0]];
                  fn = auxObj[fnName[1]];
                }
                if (typeof fn=='function')
                  fn(event.data.data);
              }
              delete that.ifcMsgQueue[event.data.callbackId];
            }
            break;
        }
      }
    }

  };

  that.getConnParams = function(callback) {
    if (chrome && chrome.storage)  {
      chrome.storage.local.get('connParams', function ( result )  {
        result = result.connParams;
        callback(result);
      });
    }
  };

  that.setConnParams = function (server, user, password, terminalID) {
    var myConnParams = {
          'server': server,
          'user': user,
          'password': password,
          'terminalID': terminalID
        };
    chrome.storage.local.set({'connParams': myConnParams}, function () {
          that.ySandboxFrame.postMessage({s:'yeapf', a:'connParams', limits: {connParams: myConnParams}},"*");
        });
  };

  that.plead = function (s, a, limits, aCallbackFunction) {
    _dumpy(1,1,"MAIN: preparing to plead ("+s+","+a+")");
    if ((typeof aCallbackFunction == 'function') || (typeof aCallbackFunction=='undefined') || (typeof aCallbackFunction=='string')) {
      if (typeof that.ySandboxFrame === "undefined") {
        _dump("MAIN: looking for 'ifc_frame' iframe");
        that.findSandboxFrame();
      }
      if (that.ySandboxFrame) {
        var aux = that.ifcMsgCounter++;
        var sbContext = {
          's': s,
          'a': a,
          'limits': limits,
          'queueId': aux
        };

        var sbMessage = {
          's': 'yeapf',
          'a': 'plead',
          'context': sbContext
        }

        that.ifcMsgQueue[aux] = {
          'aCallbackFunction': aCallbackFunction
        };

        _dumpy(1,1,"MAIN: pleading("+s+","+a+")");
        _dumpy(1,1,JSON.stringify(sbMessage));
        that.ySandboxFrame.postMessage(sbMessage, '*');
      } else
        _dumpy(1,1,"MAIN: you need a sandboxed iframe called ifc_frame");
    } else
      _dumpy(1,1,"MAIN: you need to define a callback function");
  };

  return that.findSandboxFrame();
};

var ifc;

window.addEventListener("load", function() {
  if (!ifc) ifc = IFCObj();
  window.addEventListener("message", ifc.receiveMessage, false);
});
