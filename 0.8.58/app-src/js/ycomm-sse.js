  /********************************************************************
  * app-src/js/ycomm-sse.js
  * YeAPF 0.8.58-6 built on 2017-05-29 15:54 (-3 DST)
  * Copyright (C) 2004-2017 Esteban Daniel Dortta - dortta@yahoo.com
  * 2017-05-25 09:15:08 (-3 DST)
  ********************************************************************/
  var ycommSSEBase = function (workgroup, user, dataLocation, pollTimeout, preferredGateway) {
    var that = {
      
      /* pollTimeout must be between 1 and 60 seconds */
      pollTimeout: Math.min(60000, Math.max(typeof pollTimeout=='number'?pollTimeout:1000, 1000)),
      prefGateway: (preferredGateway || 'SSE').toUpperCase(),

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
                console.log("SSE: data: "+JSON.stringify(data));
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
            console.log("SSE: User is alive");
            setTimeout(that.userAlive, that.userAliveInterval); 
          }
        };
        
        var _userOffline = function(e) {
          console.log("SSE: User logged out");
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
              if (data && data[0] && data[0].ok) {
                that.w                 = workgroup;
                that.sse_session_id    = data[0].sse_session_id;
                that.userAliveInterval = Math.min(60000, data[0].userAliveInterval * 1000);
                callback();
              }
            }
          );
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
        console.log("SSE: polling for messages. pollTimeout: {0}ms".format(that.pollTimeout));
      },

      guardianTimeout: function (e) {
        /* if SSE.PHP don't answer up to guardian timeout, use poll version */
        clearTimeout(that.evtGuardian);
        that.evtSource.close();
        that.evtSource = undefined;
        that.startPolling();
      },

      close: function(e) {
        if (!that.closing) {
          that.closing=true;
          console.log("SSE: CLOSE");
          that.state=-1;
          that.pollEnabled = false;
          that.dispatchEvent('close');
          if (that.evtSource)
            that.evtSource.close();
          that.closing=false;
        }
      },

      open: function (e) {
        console.log("SSE: OPEN");
      },

      error: function(e) {
        console.error("SSE: ERROR using SSE");
      },

      message: function (e) {
        /* as connected, clear guardian timeout */
        clearTimeout(that.evtGuardian);
        console.log("SSE: MESSAGE");
        if (that.state===0) {
          that.state=1;
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
          that.dispatchEvent("ready", {"gateway": "SSE"});
          console.log("SSE: userAliveInterval: {0}ms".format(that.userAliveInterval));
          /* the first UAI happens in half of the planned time */
          setTimeout(that.userAlive, that.userAliveInterval / 2);
        }
        if (typeof that.onmessage=="function") {
          that.onmessage(e.data);
        }
      },

      init: function() {
        that.state = -1;
        if ((typeof dataLocation=="undefined") || (dataLocation === null)) {
          /* default data location is current location/sse.pph */
          that._dataLocation_ = (
            function() {
              var a = that.getLocation();
              var b=a.lastIndexOf('/');
              return a.substr(0,b+1)+'sse.php';
            }
          )();
        } else {
          that._dataLocation_=dataLocation;
        }

        if (that._dataLocation_.substr(0,5)=="file:") {
          console.error("SSE: '"+that._dataLocation_+"' is not a correct data location");
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

          that.attachUser(
            function() {
              that.state=0;
              /* first try to use EventSource() */
              if ((that.prefGateway=='SSE') && (typeof window.EventSource == "function")) {
                that.evtSource = new EventSource(that._dataLocation_+"?si="+md5(that.sse_session_id));
                that.evtSource.addEventListener("open",    that.open,    false);
                that.evtSource.addEventListener("error",   that.error,   false);
                that.evtSource.addEventListener("message", that.message, false);
                that.evtSource.addEventListener("close",   that.close,   false);

                that.evtGuardian = setTimeout(that.guardianTimeout, 1500);
              } else {
                that.startPolling();
              }
            }
          );
        }

        return that;
      }
    };

    return that.init();
  };