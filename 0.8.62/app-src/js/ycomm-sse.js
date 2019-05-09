  /********************************************************************
  * app-src/js/ycomm-sse.js
  * YeAPF 0.8.62-100 built on 2019-05-09 19:34 (-3 DST)
  * Copyright (C) 2004-2019 Esteban Daniel Dortta - dortta@yahoo.com
  * 2019-04-18 20:32:49 (-3 DST)
  ********************************************************************/
  var ycommSSEBase = function (workgroup, user, dataLocation, pollTimeout, preferredGateway, dbgSSEDiv) {
    window.SSE_UNINITIALIZED  =-1;
    window.SSE_CLOSING        = 0;
    window.SSE_NONOPERATIONAL = 1;
    window.SSE_OPERATIONAL    = 2;
    var that = {

      /* pollTimeout must be between 60 and 900 seconds */
      pollTimeout: Math.min(900000, Math.max(typeof pollTimeout=='number'?pollTimeout:60000, 60000)),
      prefGateway: (preferredGateway || 'SSE').toUpperCase(),
      dbgDiv: y$(dbgSSEDiv),
      debugEnabled: false,

      setDebug: function(newValue) {
        newValue=newValue || false;
        that.debugEnabled = newValue;
      },

      debug: function() {
        var d=(new Date()),
            line,
            dbgClass,
            isError=false,
            isWarning=false;
        if (that.debugEnabled) {
          line=pad(d.getHours(),2)+':'+pad(d.getMinutes(),2)+':'+pad(d.getSeconds(),2)+' SSE: ';

          for (var i=0; i < arguments.length; i++) {
            line+=arguments[i].trim()+" ";
            if (arguments[i].toUpperCase().indexOf('ERROR')>=0)
              isError=true;
            if (arguments[i].toUpperCase().indexOf('STATUS')>=0)
              isWarning=true;
            if (arguments[i].toUpperCase().indexOf('WARN')>=0)
              isWarning=true;
          }
          if (isError) {
            console.error(line);
            dbgClass='label-danger';
          } else if (isWarning) {
            console.warn(line);
            dbgClass='label-warning';
          } else {
            console.log(line);
            dbgClass='';
          }
          if ('undefined' != typeof that.dbgDiv) {
            var aux="<div class='label {0}' style='display: inline-block'>{1}</div><br>".format(dbgClass, line);
            var currentText = that.dbgDiv.innerHTML.split("<br>");
            if (currentText.length>100) {
              currentText.splice(0,100-currentText.length);
              that.dbgDiv.innerHTML=currentText.join("<br>");
            }
            that.dbgDiv.innerHTML+=aux;
          }
        }
      },

      getLocation: function() {
        return (typeof document=='object' && document.location && document.location.href)?document.location.href:'';
      },

      getFolder: function(location) {
        var b=location.lastIndexOf('/');
        return location.substr(0,b+1);
      },

      rpc: function(a, params) {
        params = params || {};
        if (typeof that.sse_session_id!="undefined")
          params.sse_session_id = that.sse_session_id;

        var p = new Promise(
          function(resolve, reject) {
            that.debug("OUT: "+a);
            that.rpcMethod(
              "_sse", a, params,
              function(status, error, data) {
                if (status==200) {
                  resolve(data);
                } else {
                  reject(status);
                }
              },
              false
            );
          }
        );
        return p;
      },

      poll: function () {
        if (that.pollEnabled) {
          that.rpc("peekMessage").then( function(data) {
              if (data) {
                that.debug("IN: data: "+JSON.stringify(data));
                var eventName;
                for(var i=0; i<data.length; i++) {
                  if (!that.dispatchEvent(data[i].event, { data: data[i].data } )) {
                    that.message({
                      'data' : data[i].data
                    });
                  }
                }
              }
              setTimeout(that.poll, that.pollTimeout);
            });
        }
      },

      userAlive: function () {
        clearTimeout(that._userAliveScheduler);

        var _userAlive = function(data) {
          var toClose=false;
          if (data) {
            var info = data[0] || data;
            toClose = (info.event || '').toUpperCase() == 'CLOSE';
          }
          if (toClose) {
            _userOffline();
          } else {
            that.debug("IN: User is alive");
            if (that.state!=SSE_OPERATIONAL) {
              that.debug("Unexpected SSE state: "+that.state);
            }
            that.scheduleUserAlive(that.userAliveInterval);
          }
        };

        var _userOffline = function(e) {
          that.debug("STATUS: User logged out");
          that.close(e);
        };

        if (that.state==SSE_OPERATIONAL) {
          var p = that.rpc("userAlive");
          p.then(_userAlive).catch(_userOffline);
        } else {
          that.scheduleUserAlive(Math.max(100,that.userAliveInterval/100));
        }
      },

      scheduleUserAlive: function (timeout_ms) {
        timeout_ms = timeout_ms || that.userAliveInterval;
        clearTimeout(that._userAliveScheduler);
        console.log("%c scheduling UserAlive indicator for {0}ms".format(timeout_ms),'background: #222; color: #429BDA');
        that._userAliveScheduler = setTimeout(that.userAlive, timeout_ms);
      },

      attachUser: function (callback) {
        that.rpc(
          "attachUser",
          {
            "w": workgroup,
            "user": that.user
          }).then(function(data) {
            that.debug("IN: attach info");
            if (data) {
              var info = data[0] || data;
              if (info.ok) {
                that.w                 = workgroup;
                that.sse_session_id    = info.sse_session_id;
                /* cfgSSEUserAliveInterleave -> userAliveInterval
                   it comes in seconds
                   but as js works in ms, we translate it
                   The allowed interleave is between 61 and 900 seconds */
                that.userAliveInterval = Math.min(900, Math.max(61,info.userAliveInterval)) * 1000;
                that.debug("set userAliveInterval value to {0}ms ".format(that.userAliveInterval));
                callback();
              }
            }
          });
      },

      sendPing: function(callback) {
        if (that.state==SSE_OPERATIONAL) {
          that.rpc(
            "ping",
            {w: workgroup, user: that.user}
          ).then(callback);
        }
      },

      addEventListener: function (eventName, func) {
        /* save the event in the internal list */
        if (typeof that.events == "undefined") {
          that.events={};
        }
        if ("undefined" == typeof that.events[eventName])
          that.events[eventName] = [];
        that.events[eventName].push([that.state, func]);

        if (that.state==SSE_OPERATIONAL) {
          if (!that.pollEnabled)
            that.evtSource.addEventListener(eventName, func);
          if (trim(eventName.toLowerCase()) == "ready") {
            that.dispatchEvent("ready");
          }
        }
        return that;
      },

      dispatchEvent: function (eventName, params) {
        var ret=false;
        if ((that.state==SSE_OPERATIONAL) || (eventName=="onerror")) {
          if (typeof that.events !== "undefined") {
            for(var implementations=0; implementations<(that.events[eventName] || []).length; implementations++) {
              var eventDef = that.events[eventName][implementations];
              eventDef[1](params);
              ret |= true;
            }
          }

          eventName1=eventName;
          eventName2="on_"+eventName;
          if (typeof that[eventName1] == "function") {
            that[eventName1](params);
            ret |= true;
          } else if (typeof that[eventName2] == "function") {
            that[eventName2](params);
            ret |= true;
          } else
            ret |= false;
        }
        return ret;
      },

      startPolling : function() {
        clearTimeout(that.evtGuardian);
        that.pollEnabled=true;
        that.state=SSE_OPERATIONAL;
        that.dispatchEvent("ready", {"gateway": "Polling"});
        setTimeout(that.poll, 125);
        that.debug("STATUS: polling for messages. pollTimeout: {0}ms".format(that.pollTimeout));
      },

      guardianTimeout: function (e) {
        that.debug("Guardian Timeout! Let's use polling mode");
        /* if SSE.PHP don't answer up to guardian timeout, use poll version */
        clearTimeout(that.evtGuardian);
        that.__destroy__();
        that.startPolling();
      },

      __destroy__: function() {
          that.closing=true;
          clearTimeout(that.evtGuardian);
          that.debug('DESTROYING');
          that.state=SSE_UNINITIALIZED;
          that.evtSource=null;
          that.GUID=null;
      },

      close: function(e) {
        clearTimeout(that.evtGuardian);
        if (!that.closing) {
          that.closing=true;
          that.state=SSE_CLOSING;
          that.debug("STATUS: CLOSE");
          that.rpc(
            "detachUser",
            {
              w: workgroup,
              user: that.user
            }).then(function() {
              that.state=SSE_UNINITIALIZED;
              that.pollEnabled = false;
              that.__destroy__();
              that.closing=false;
            }).catch(function() {
              that.closing=false; setTimeout(that.close, 1500);
            });
        }
      },

      closeEvent: function(e) {
        clearTimeout(that.evtGuardian);
        console.log("%c close: {0}".format(e.data || "NULL"),"color: #8787FF");
        that.__destroy__();
      },

      errorEvent: function(e) {
        clearTimeout(that.evtGuardian);
        console.log("%c error: {0}".format(e.data || "NULL"),"color: #8787FF");
        that.debug("ERROR: while using SSE");
        that.dispatchEvent('onerror');
        that.state=SSE_NONOPERATIONAL;
        setTimeout(that.startup, 2500);
      },

      openEvent: function (e) {
        clearTimeout(that.evtGuardian);
        console.log("%c open: {0}".format(e.data || "NULL"),"color: #8787FF");
        that.debug("STATUS: OPEN");
        /* the first UAI happens in 1/100th after OPEN */
        that.scheduleUserAlive(that.userAliveInterval/100);
        that.dispatchEvent('onopen');

        if ((ycomm.msg) && (typeof ycomm.msg.notifyServerOnline =='function'))
          ycomm.msg.notifyServerOnline();
      },

      messageEvent: function (e) {
        /* as connected, clear guardian timeout */
        clearTimeout(that.evtGuardian);
        console.log("%c message: {0}".format(e.data || "NULL"),"color: #8787FF");
        if ((!e) || (e.target.readyState==2)) {
          that.close();
        } else {
          that.debug("MESSAGE");
          if (that.state>SSE_CLOSING) {
            /* firs message just synchro the message table */
            that.state=SSE_OPERATIONAL;
            that.debug("userAliveInterval: {0}ms".format(that.userAliveInterval));

            for(var eventName in that.events) {
              if (that.events.hasOwnProperty(eventName)) {
                for(var implementations=0; implementations<that.events[eventName].length; implementations++) {
                  var eventDef = that.events[eventName][implementations];
                  if (eventDef[0]<1) {
                    eventDef[0]=1;
                    that.evtSource.addEventListener(eventName, eventDef[1]);
                  }
                }
              }
            }
            that.dispatchEvent("onready", {"gateway": "SSE"});
          }

          if (that.state>SSE_UNINITIALIZED) {
            if (typeof that.onmessage=="function") {
              that.onmessage(e.data);
            }
          }
        }
      },

      pingEvent: function(e) {
        console.log("%c ping: {0}".format(e.data || "NULL"),"color: #8787FF");
      },

      startup: function(gateway) {
        gateway = (gateway || that.prefGateway || 'SSE').toUpperCase();
        console.log("%c startup: {0}".format(gateway),"color: #8787FF");
        that.prefGateway = gateway;
        if (that.evtGuardian) {
          clearTimeout(that.evtGuardian);
        }
        if ((typeof that.GUID != "string") || (that.state<=SSE_NONOPERATIONAL)) {

          if (typeof that.GUID != "string")
            that.GUID=generateUUID();

          that.attachUser(
            function() {
              that.state=SSE_NONOPERATIONAL;
              /* first try to use EventSource() */
              if ((that.prefGateway=='SSE') && (typeof window.EventSource == "function")) {
                that.debug("INFO: Attaching events");
                if (!that.evtSource) {
                  that.debug("INFO: All new evtSource");
                  that.evtSource = new EventSource(that._dataLocation_+"?si="+md5(that.sse_session_id));
                  that.evtSource.onerror=that.errorEvent;
                } else {
                  that.debug("INFO: Reusing component");
                }

                that.evtSource.onopen    = that.openEvent;
                that.evtSource.onclose   = that.closeEvent;
                that.evtSource.onmessage = that.messageEvent;
                that.addEventListener("ping",  that.pingEvent, false);
                that.addEventListener("reset", that.resetEvent, false);

                that.debug("INFO: Configuring guardianTimeout to {0}ms".format(that.userAliveInterval * 1.5));
                that.evtGuardian = setTimeout(that.guardianTimeout, that.userAliveInterval * 1.5);
                that.scheduleUserAlive(that.userAliveInterval/100);
              } else {
                that.startPolling();
              }
            }
          );
        } else {
          that.debugEnabled=true;
          that.debug("ERROR: SSE object already initialized");
        }
      },

      resetEvent: function() {
        that.close();
        that.__destroy__();
        that.state=SSE_NONOPERATIONAL;
        setTimeout(that.startup, 3500);
      },

      init: function() {
        that.state=SSE_UNINITIALIZED;
        if ((typeof dataLocation=="undefined") || (dataLocation === null)) {
          /* default data location is current location/sse.php */
          that._dataLocation_ = (
            function() {
              var a = that.getLocation();
              var b = a.lastIndexOf('/');
              return a.substr(0,b+1)+'sse.php';
            }
          )();
        } else {
          that._dataLocation_=dataLocation;
        }

        if (that._dataLocation_.substr(0,5)=="file:") {
          that.debug("ERROR: '"+that._dataLocation_+"' is not a correct data location");
        } else {
          /* create user id */
          that.user = ((user !==null) && (typeof user != "undefined"))?user:generateUUID();

          /* check same-source */
          var l1=that.getFolder(that.getLocation()),
              l2=that.getFolder(that._dataLocation_);

          /* chose better way to communicate with server */
          if (l1==l2) {
            that.rpcMethod = ycomm.invoke;
          } else {
            that.rpcMethod = ycomm.crave;
          }

          that.startup();

        }

        return that;
      }
    };

    var __state=-2;
    var __getState = function() {
      return __state;
    };
    var __setState = function(newState) {
      if (newState != __state) {
        console.log("%c SSE state changed from {0} to {1}".format(__state, newState), 'background: #222; color: #bada55');
        __state = newState;
      }
    };

    Object.defineProperty(that, "state", {
      'configurable': false,
      'enumerable': false,
      'get': __getState,
      'set': __setState
    });

    return that.init();
  };
