/*********************************************
 * skel/MoSyncApp/LocalFiles/js/ysandboxifc.js
 * YeAPF 0.8.56-90 built on 2017-04-21 11:44 (-3 DST)
 * Copyright (C) 2004-2017 Esteban Daniel Dortta - dortta@yahoo.com
 * 2017-04-21 11:44:29 (-3 DST)
 * First Version (C) 2014 - esteban daniel dortta - dortta@yahoo.com
 * By security reasons, sometimes you cannot acces
 * your restful interface from your webapp, so you need a bridge.
 * sandboxIFC (IFC means InterFrameCommunications) lets you
 * continue to use restful interface from a sandboxed iframe.
 * It counterpart is yifc.js
 *********************************************/
//# sourceURL=skel/MoSyncApp/LocalFiles/js/ysandboxifc.js

_setLogFlagLevel(1,2);

console.log("Sandbox stage2");
var sandboxIFCObj = function () {
  that = {};
  that.lastStatus=0;

  that.Connected = function() {
    if (that.lastStatus<=0) {
      console.log("ONLINE------------");
      that.lastStatus=1;
      parent.postMessage({s:'yeapf',a:'online'},'*');
    }
  };

  that.Offline = function () {
    if (that.lastStatus>=0) {
      console.log("OFFLINE------------");
      that.lastStatus=-1;
      parent.postMessage({s:'yeapf',a:'offline'},'*');
    }
  };

  that.getConnParams = function() {
    parent.postMessage({s:'yeapf', a:'getConnParams'}, '*');
  };

  that.receiveMessage = function(event) {
    _dumpy(1,1,"SANDBOX: receiveMessage() from "+event.origin+" ("+typeof event+")");
    if (typeof event == 'object') {
      _dumpy(1,2, JSON.stringify(event.data));
      var localData=event.data || {s: null, a:null};
      var sa=localData.s+'.'+localData.a;
      _dumpy(1,1,"SANDBOX: {0} ({1})".format(sa,event.origin));
      switch (sa) {
        case 'yeapf.connParams':
          _dumpy(1,1,"SANDBOX: connParams");
          var data=localData.limits.connParams;
          if (data === null)
            setTimeout(that.getConnParams, 2000);
          else {
            ycomm.pinger.stopPing();
            ycomm.setDataLocation(data.server+'/rest.php');
            ycomm.pinger.ping(that.Connected, that.Offline);
          }
          break;
        case 'yeapf.plead':
          _dumpy(1,1,"SANDBOX: plead");
          var toCrave = localData.context;
          ycomm.crave(toCrave.s, toCrave.a, toCrave.limits, 'sandboxIFC.dataPleaded', null, localData.queueId);
          break;
      }
    } else
      _dump("The event is not an object");
  };

  that.dataPleaded = function (status, error, aData, userMsg, context) {
    _dumpy(1,1,"SANDBOX: dataPleaded()");
    var msg = {
      's':          'yeapf',
      'a':          'dataPleaded',
      'callbackId': context.callbackId,
      'data':       aData
    };
    parent.postMessage(msg, '*');
  };

  return that;
};

console.log("Sandbox stage3");
var sandboxIFC = {};

window.addEventListener("load", function() {
  console.log("Sandbox stage4");
  sandboxIFC = sandboxIFCObj();
  console.log("Sandbox stage5");
  ycomm.pinger.pingInterleave = 10000;
  sandboxIFC.getConnParams();
  window.addEventListener("message", sandboxIFC.receiveMessage, false);
  console.log("Sandbox stage6");
});
