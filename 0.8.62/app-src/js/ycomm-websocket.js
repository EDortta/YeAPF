/********************************************************************
 * app-src/js/ycomm-websocket.js
 * YeAPF 0.8.62-162 built on 2019-05-16 10:57 (-3 DST)
 * Copyright (C) 2004-2019 Esteban Daniel Dortta - dortta@yahoo.com
 * 2019-05-16 09:33:30 (-3 DST)
 ********************************************************************/

var ycommWebSocketClientObj = function(webSocketServerURL, uname, deviceId) {
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
  var that = {},
    socket, heartbeatGuardian;
  var localU = (typeof window.u == 'undefined') ? null : u;
  var host = null;
  var functionNameSeed = "F" + (guid().replace(/-/g, '').toLowerCase().substr(8, 12)) + "_";
  var functionCounter = 1000;
  var __eventHandler = [];

  uname = uname || localU || guid();

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
        var unattended = true;
        if (msg.data) {
          try {
            var data;
            if ("string" == typeof msg.data)
              data = JSON.parse(msg.data);
            else
              data = msg.data;

            if (data.callbackId) {
              if ("undefined" != typeof rootSystem[data.callbackId]) {
                rootSystem[data.callbackId](200, data.error, data.data, data.userMsg, data.dataContext, data.geometry);
              }
            }

            var subjectEvent = ((data.parameters || [])["s"] || "unknown");
            var singleEvent = ((data.parameters || [])["s"] || "unknown") + "." + ((data.parameters || [])["a"] || "unknown");
            console.log(subjectEvent, " or ", singleEvent);
            if ("function" == typeof __eventHandler[subjectEvent]) {
              __eventHandler[subjectEvent]((data.parameters || [])["a"], data.data);
              unattended = false;
            }
            if ("function" == typeof __eventHandler[singleEvent]) {
              __eventHandler[singleEvent](data.data);
              unattended = false;
            }

            if ("function" == typeof that.onmessage) {
              that.onmessage(data.data);
              unattended = false;
            }

          } catch (e) {
            console.error("Error processing message: " + msg.data);
          }

        } else {
          if ("function" == typeof that.onmessage) {
            that.onmessage(msg);
            unattended = false;
          }
        }
        if (unattended)
          log("UNATTENDED MESSAGE: " + msg.data);

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
    setTimeout(configureSocket, 1500);
  };

  var rootSystem = window || self;

  /* garbage collector */
  var _cleanUp = function(callbackFunctionName) {
    console.log("deleting " + callbackFunctionName);
    delete rootSystem[callbackFunctionName];
  };

  that.setEventHandler = function(handlerFunction, s, a) {
    if ("string" == typeof s) {
      if ("string" == typeof a) {
        if ("undefined" == typeof handlerFunction)
          delete __eventHandler[s + "." + a];
        else
          __eventHandler[s + "." + a] = handlerFunction;
      } else {
        if ("undefined" == typeof handlerFunction)
          delete __eventHandler[s];
        else
          __eventHandler[s] = handlerFunction;
      }
    }
  };

  /*
    besides xq_start and xq_requestedRows now you have:
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

    Remember that single character variables belongs to YeAPF by default. Meanwhile, as paramenters
    are encapsulated, you can use it even when not recommended.

    Anyway, you can define your own sensitive trigger or monitors or signals that will make
    YeAPF to send the message to an specific target or group of targets.
    In such case, all your triggers starts with '_' and are textual variables evaluated as trimmed
    and insensitive case ones.
    For example, you can define '_plate' as your trigger/monitor/signal/filter and
    set a specific value on each application of the environment. The message will be delivered
    only for those applications that has the searched value in the '_plate' published var.
    In this example, xq_target will be '_plate:120x452'
    In order to achive that, you can send a message to underlying yeapf message subsystem
    declaring your intention to listen for a monitor/signal/trigger/filter or whatever
    you would like to name it as this:
      s=y_msg&a=setTrigger&triggerName=_plate
    or more specifically for a value (that is more useful)
      s=y_msg&a=setTrigger&triggerName=_plate&triggerValue=120x452

    So, in that case, you will receive all messages that are targeted to _plate:120x452

    You can unset a trigger too:
      s=y_msg&a=unsetTrigger&triggerName=_plate
  */
  that.yank = function(s, a, jsonParams, aCallbackFunction) {
    aCallbackFunction = aCallbackFunction || function() {};
    jsonParams = jsonParams || {};

    var mySequence = ++functionCounter;
    var callbackFunctionName = functionNameSeed + mySequence;
    /* callback control... for garbage collect */
    ycomm._CBControl[callbackFunctionName] = { ready: false };

    rootSystem[callbackFunctionName] = function(status, error, data, userMsg, context, geometry) {
      aCallbackFunction(status, error, data, userMsg, context, geometry);
      _dumpy(4, 1, callbackFunctionName);
      _cleanUp(callbackFunctionName);
    };

    jsonParams['xq_bypass'] = ("boolean" == typeof jsonParams['xq_bypass']) ? jsonParams['xq_bypass'] : false;
    jsonParams['xq_bypass'] = (jsonParams['xq_bypass'] === true) ||
      (jsonParams['xq_bypass'] || "NO").toUpperCase() == "YES";

    var jsonAsParams = ycomm.urlJsonAsParams(jsonParams);
    var fieldName = jsonAsParams[0];
    var fieldValue = jsonAsParams[1];
    var yeapf_message = {
      "s": s,
      "a": a,
      "u": localU,
      "uname": uname,
      "fieldName": fieldName,
      "fieldValue": fieldValue,
      "callbackId": callbackFunctionName
    };
    socket.send(JSON.stringify(yeapf_message));
    setTimeout(function() { _cleanUp(callbackFunctionName); }, 15000);
  };

  var _cleanup = function(event) {
    // event.preventDefault();
    socket.onclose=function() {};
    socket.close();
    // event.returnValue = '';
  };

  /* private initializer */
  var _initialize = function() {
    webSocketServerURL = webSocketServerURL || "";
    if (webSocketServerURL > "") {
      host = webSocketServerURL;
      _deviceId = deviceId || guid();

      setTimeout(configureSocket, 1500);

      if ("function" == typeof rootSystem.addEventListener) {
        rootSystem.addEventListener('unload', _cleanup);
      }

      return that;
    } else {
      console.alert("You need to indicate the webService URL in order to use ycommWebSocketClientObj()");
      return dummy;
    }
  };

  return _initialize();
};