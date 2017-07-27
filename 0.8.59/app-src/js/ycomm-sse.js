  /********************************************************************
  * app-src/js/ycomm-sse.js
  * YeAPF 0.8.59-9 built on 2017-07-27 17:40 (-3 DST)
  * Copyright (C) 2004-2017 Esteban Daniel Dortta - dortta@yahoo.com
  * 2017-07-21 10:35:13 (-3 DST)
  ********************************************************************/
  var ycommSSEBase = function (workgroup, user, dataLocation, pollTimeout, preferredGateway, dbgSSEDiv) {
    var that = {

      /* pollTimeout must be between 1 and 60 seconds */
      pollTimeout: Math.min(60000, Math.max(typeof pollTimeout=='number'?pollTimeout:1000, 1000)),
      prefGateway: (preferredGateway || 'SSE').toUpperCase(),
      dbgDiv: y$(dbgSSEDiv),

      debug: function() {
        var d=(new Date()),
            line, 
            dbgClass,
            isError=false,
            isWarning=false;
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
          that.dbgDiv.innerHTML+="<div class='label {0}' style='display: inline-block'>{1}</div>".format(dbgClass, line);
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
        var _userAlive = function(data) {
          var toClose=false;
          if (data && data[0]) {
            toClose = (data[0].event || '').toUpperCase() == 'CLOSE';
          }
          if (toClose) {
            _userOffline();
          } else {
            that.debug("IN: User is alive");
            if (that.state==1) {
              setTimeout(that.userAlive, that.userAliveInterval);
            } else {
              that.debug("Unexpected SSE state: "+that.state);
            }
          }
        };

        var _userOffline = function(e) {
          that.debug("STATUS: User logged out");
          that.close(e);
        };

        var p = that.rpc("userAlive");
        p.then(_userAlive).catch(_userOffline);
      },

      attachUser: function (callback) {
        that.rpc(
          "attachUser",
          {
            "w": workgroup,
            "user": that.user
          }).then(function(data) {
            that.debug("IN: attach info");
            if (data && data[0] && data[0].ok) {
              that.w                 = workgroup;
              that.sse_session_id    = data[0].sse_session_id;
              that.userAliveInterval = Math.min(30000, Math.max(5000,data[0].userAliveInterval * 1000));
              that.debug("userAliveInterval: "+that.userAliveInterval);
              callback();
            }
          });
      },

      addEventListener: function (eventName, func) {
        /* save the event in the internal list */
        if (typeof that.events == "undefined") {
          that.events={};
        }
        if ("undefined" == typeof that.events[eventName])
          that.events[eventName] = [];
        that.events[eventName].push([that.state, func]);

        if (that.state==1) {
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

        return ret;
      },

      startPolling : function() {
        that.pollEnabled=true;
        that.state=1;
        that.dispatchEvent("ready", {"gateway": "Polling"});
        setTimeout(that.poll, 125);
        that.debug("STATUS: polling for messages. pollTimeout: {0}ms".format(that.pollTimeout));
      },

      guardianTimeout: function (e) {
        that.debug("Guardian Timeout! Let's use polling mode");
        /* if SSE.PHP don't answer up to guardian timeout, use poll version */
        clearTimeout(that.evtGuardian);
        that.evtSource.close();
        that.evtSource = undefined;
        that.startPolling();
      },

      close: function(e) {
        if (!that.closing) {
          that.closing=true;
          that.debug("STATUS: CLOSE");
          that.state=-1;
          that.pollEnabled = false;
          that.dispatchEvent('onclose');
          if (that.evtSource)
            that.evtSource.close();
          that.closing=false;
        }
      },

      open: function (e) {
        clearTimeout(that.evtGuardian);
        that.debug("STATUS: OPEN");
        /* the first UAI happens in 50ms after OPEN */
        setTimeout(that.userAlive, that.userAliveInterval/100);
        that.dispatchEvent('onopen');
        
        if ((ycomm.msg) && (typeof ycomm.msg.notifyServerOnline =='function'))
          ycomm.msg.notifyServerOnline();
      },

      error: function(e) {
        that.debug("ERROR: while using SSE");
        that.dispatchEvent('onerror');
      },

      message: function (e) {
        /* as connected, clear guardian timeout */
        clearTimeout(that.evtGuardian);
        that.debug("MESSAGE");
        if (that.state===0) {
          that.state=1;
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
        if (typeof that.onmessage=="function") {
          that.onmessage(e.data);
        }
      },

      startup: function(gateway) {
        gateway = (gateway || that.prefGateway || 'SSE').toUpperCase();
        that.prefGateway = gateway;
        if (that.evtGuardian) {
          clearTimeout(that.evtGuardian);
        }
        that.attachUser(
          function() {
            that.state=0;
            /* first try to use EventSource() */
            if ((that.prefGateway=='SSE') && (typeof window.EventSource == "function")) {
              that.debug("INFO: Attaching events");
              if (!that.evtSource) {
                that.evtSource = new EventSource(that._dataLocation_+"?si="+md5(that.sse_session_id));
              }
              that.evtSource.onopen=that.open;
              that.evtSource.onclose=that.close;
              that.evtSource.onmessage=that.message;

              that.evtSource.addEventListener("error",   that.error,   false);
              
              that.debug("INFO: Configuring guardianTimeout");
              that.evtGuardian = setTimeout(that.guardianTimeout, 30000);
            } else {
              that.startPolling();
            }
          }
        );
      },

      init: function() {
        that.state = -1;
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

    return that.init();
  };
