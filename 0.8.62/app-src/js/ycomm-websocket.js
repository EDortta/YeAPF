/********************************************************************
 * app-src/js/ycomm-websocket.js
 * YeAPF 0.8.62-96 built on 2019-05-06 18:30 (-3 DST)
 * Copyright (C) 2004-2019 Esteban Daniel Dortta - dortta@yahoo.com
 * 2019-05-06 12:15:56 (-3 DST)
 ********************************************************************/

var ycommWebSocketClientObj = function(webSocketServerURL, u, deviceId) {
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
  var uname = u || guid(),
    socket, heartbeatGuardian;
  var host = null;
  var functionNameSeed = "F" + (guid().replace(/-/g, '').toLowerCase().substr(8, 12)) + "_";
  var functionCounter = 1000;
  var __eventHandler = [];

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

  var getRndInteger = function(min, max) {
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
        try {
          var result=JSON.parse(msg.data);
          if (result.callbackId) {
            if ("undefined" != rootSystem[result.callbackId])
              rootSystem[result.callbackId](200, result.error, result.data, result.userMsg, result.dataContext, result.geometry);
            else {
              var subjectEvent = ((result.parameters || []) ["s"] || "unknown");
              var singleEvent =  ((result.parameters || []) ["s"] || "unknown")+"."+((result.parameters || []) ["a"] || "unknown") ;
              if ("function" == typeof __eventHandler[subjectEvent]) {
                __eventHandler[subjectEvent] ((result.parameters || []) ["a"], result);
              }
              if ("function" == typeof __eventHandler[singleEvent]) {
                __eventHandler[singleEvent] (result);
              }
            }
          }
        } catch(e) {

        }

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
    if (socket.readyState == 1)
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

  var rootSystem = window || self;

  /* garbage collector */
  var _cleanUp = function (callbackFunctionName) {
    console.log("deleting "+callbackFunctionName);
    delete rootSystem[callbackFunctionName];
  }

  that.setEventHandler = function(handlerFunction, s, a) {
    if ("string" == typeof s) {
      if ("string" == typeof a) {
        if ("undefined" == typeof handlerFunction)
          delete __eventHandler[s+"."+a];
        else
          __eventHandler[s+"."+a]=handlerFunction;
      } else {
        if ("undefined" == typeof handlerFunction)
          delete __eventHandler[s];
        else
          __eventHandler[s]=handlerFunction;
      }
    }
  };

  /*
    besides xq_start and xq_requestedRows now you have
    xq_target (string) that indicates who is the ultimate target of the message
    and xq_bypass (boolean defaults to false) that indicates if the server will be bypassed
    when answering a query. In such case (true), the query will be sent/broadcasted to the
    xq_target and is this(these) target(s) the responsible to answer the query.
    About the xq_target formation we need to say that:
      xq_target is a string with this structure: [*] | ([!][uname|ip|id|uname|u|w]<:>[value])
      1. '*' the message is a broadcast to all the connected clients.
      2. '!' symbol will negate the target
      3. 'uname' indicating an specific target. In such case it is a sendMessage.
      4. 'ip' all the clients connected under that ip will receive the message.
      5. 'id' the client with such 'id' will receive the message
      6. 'u' that indicates the global 'u' in the tripod (s, a, u) that represent a logged user
      7. 'w' that indicates the global 'w' variable meaning the 'group' to what the client belongs.
  */
  that.yank = function(s, a, jsonParams, aCallbackFunction) {
    aCallbackFunction = aCallbackFunction || function() {};

    var mySequence = ++functionCounter;
    var callbackFunctionName = functionNameSeed + mySequence;
    /* callback control... for garbage collect */
    ycomm._CBControl[callbackFunctionName] = { ready: false };

    rootSystem[callbackFunctionName] = function(status, error, data, userMsg, context, geometry) {
      aCallbackFunction(status, error, data, userMsg, context, geometry);
      _dumpy(4, 1, callbackFunctionName);
      _cleanUp(callbackFunctionName);
    };

    jsonParams['xq_bypass']=jsonParams['xq_bypass']?true:false;

    var jsonAsParams = ycomm.urlJsonAsParams(jsonParams);
    var fieldName = jsonAsParams[0];
    var fieldValue = jsonAsParams[1];
    var yeapf_message = {
      "s": s,
      "a": a,
      "fieldName": fieldName,
      "fieldValue": fieldValue,
      "callbackId": callbackFunctionName
    };
    socket.send(JSON.stringify(yeapf_message));
    setTimeout(function() { _cleanUp(callbackFunctionName);}, 15000);
  };

  /* private initializer */
  var _initialize = function() {
    webSocketServerURL = webSocketServerURL || "";
    if (webSocketServerURL > "") {
      host = webSocketServerURL;
      _deviceId = deviceId || guid();

      configureSocket();

      return that;
    } else {
      console.alert("You need to indicate the webService URL in order to use ycommWebSocketClientObj()");
      return dummy;
    }
  };

  return _initialize();
};
