  /********************************************************************
  * app-src/js/ycomm-websocket.js
  * YeAPF 0.8.62-81 built on 2019-05-01 13:06 (-3 DST)
  * Copyright (C) 2004-2019 Esteban Daniel Dortta - dortta@yahoo.com
  * 2019-05-01 12:50:05 (-3 DST)
  ********************************************************************/

  ycommWebSocketClientObj = function (webSocketServerURL, u, deviceId) {
  /* just in case you forget to use webSocketServerURL */
  var dummy = {
    yank: function(s, a, limits, aCallbackFunction) {
      console.log("bad configured");
      if ("function" == typeof aCallbackFunction) {
        /* https://www.restapitutorial.com/httpstatuscodes.html */
        aCallbackFunction(501, "Bad configured", null, null, null);
      }
    }
  };

  /* implementation */
  var that = {};
  var uname = u || guid(), socket, heartbeatGuardian;
  var host = null;
  var functionNameSeed="F"+(guid().replace(/-/g, '').toLowerCase().substr(8,12))+"_";
  var functionCounter=1000;

  /* private functions */
  var formatDate = function(date) {
    var hours = date.getHours();
    var minutes = date.getMinutes();
    hours = hours < 10 ? '0' + hours : hours;
    minutes = minutes < 10 ? '0' + minutes : minutes;
    var strTime = hours + ':' + minutes;
    return date.getFullYear() + "/" + (date.getMonth() + 1) + "/" + date.getDate() + "  " + strTime;
  };

  var now = function() {
    var d = new Date();
    return formatDate(d);
  };

  var log = function(msg) {
    console.log("%c {0} {1}".format(now(), msg || ""), "color: ##5157FF");
  };

  var getRndInteger=function(min, max) {
    return Math.floor(Math.random() * (max - min)) + min;
  };

  var configureSocket = function() {
    if (heartbeatGuardian)
      clearInterval(heartbeatGuardian);
    try {
      socket = new WebSocket(host);
      log('WebSocket - status ' + socket.readyState);
      /* ON OPEN */
      socket.onopen = function(msg) {
        log("Welcome - status " + this.readyState);
        socket.send("uname:" + uname);
        heartbeatGuardian = setInterval(heartbeat, 30000);
        if ("function" == typeof that.onopen) {
          that.onopen(msg);
        }
      };
      /* ON MESSAGE */
      socket.onmessage = function(msg) {
        log("<b>" + msg.data + '</b>');
      };
      /* ON CLOSE */
      socket.onclose = function(msg) {
        if (heartbeatGuardian)
          clearInterval(heartbeatGuardian);
        log("Disconnected - status " + this.readyState);
        setTimeout(configureSocket, 1500);
        if ("function" == typeof that.onclose) {
          that.onclose(msg);
        }
      };
    } catch (ex) {
      log(ex);
    }
  };

  var heartbeat = function() {
    if (socket.readyState==1)
      socket.send(".");
    else
      that.reconnect();
  };

  that.quit = function() {
    if (socket != null) {
      log("Goodbye!");
      socket.close();
      socket = null;
    }
  };

  that.reconnect = function() {
    that.quit();
    configureSocket();
  };


  that.yank = function(s, a, jsonParams, aCallbackFunction) {
    aCallbackFunction = aCallbackFunction || function() {};

    var mySequence = ++functionCounter;
    var callbackFunctionName = functionNameSeed+mySequence;
    /* callback control... for garbage collect */
    ycomm._CBControl[callbackFunctionName]={ready: false};

    var rootSystem = window || self;

    rootSystem[callbackFunctionName]=function(status, error, data, userMsg, context, geometry) {
      aCallbackFunction(status, error, data, userMsg, context, geometry);
      _dumpy(4,1,callbackFunctionName);
    };

    var jsonAsParams=that.urlJsonAsParams(jsonParams);
    var fieldName=jsonAsParams[0];
    var fieldValue=jsonAsParams[1];
    var yeapf_message = {
      "s":          s,
      "a":          a,
      "fieldName":  fieldName,
      "fieldValue": fieldValue,
      "callbackId": callbackFunctionName
    };
    socket.send(JSON.stringify(yeapf_message));
  };

  /* private initializer */
  var _initialize = function() {
    webSocketServerURL = webSocketServerURL || "";
    if (webSocketServerURL>"") {
      host = webSocketServerURL;
      _deviceId = deviceId || guid();

      return that;
    } else {
      console.alert("You need to indicate the webService URL in order to use ycommWebSocketClientObj()");
      return dummy;
    }
  };
};