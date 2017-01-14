  /********************************************************************
  * app-src/js/ycomm-sse.js
  * YeAPF 0.8.53-56 built on 2017-01-14 13:26 (-2 DST)
  * Copyright (C) 2004-2017 Esteban Daniel Dortta - dortta@yahoo.com
  * 2017-01-13 19:41:59 (-2 DST)
  ********************************************************************/
  var ycommSSEBase = function (workgroup, user, dataLocation) {
    var that = {

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
              }
            );
          }
        );
        return p;
      },

      poll: function () {
        if (that.pollEnabled) {
          that.rpc("peekMessage").then( function(data) {
              if (data) {
                var eventName;
                for(var i=0; i<data.length; i++) {
                  if (!that.dispatchEvent(data[i].event, { data: data[i].data } )) {
                    that.message({
                      'data' : data[i].data
                    });
                  }
                }
              }
              setTimeout(that.poll, 1000);
            });
        }
      },

      attachUser: function (callback) {
        that.rpc(
          "attachUser",
          {
            "w": workgroup,
            "user": that.user
          }).then(function(data) {
              if (data && data[0] && data[0].ok) {
                that.sse_session_id = data[0].sse_session_id;
                callback();
              }
            }
          );
      },

      addEventListener: function (eventName, func) {
        if (trim(eventName.toLowerCase()) != "ready") {
          /* save the event in the internal list */
          if (typeof that.events == "undefined") {
            that.events={};
          }
          if ("undefined" == typeof that.events[eventName])
            that.events[eventName] = [];
          that.events[eventName].push([that.state, func]);
        }

        /* add the manager */
        that["on_"+eventName] = func;

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
          console.log("CLOSE");
          that.state=-1;
          that.pollEnabled = false;
          that.dispatchEvent('close');
          if (that.evtSource)
            that.evtSource.close();
          that.closing=false;
        }
      },

      open: function (e) {
        console.log("OPEN");
      },

      error: function(e) {
        console.error("ERROR");
      },

      message: function (e) {
        /* as connected, clear guardian timeout */
        clearTimeout(that.evtGuardian);
        console.log("MESSAGE");
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
        }
        if (typeof that.onmessage=="function") {
          that.onmessage(e.data);
        }
      },

      init: function() {
        that.state = -1;
        if (typeof dataLocation=="undefined") {
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
          console.error("'"+that._dataLocation_+"' is not a correct data location");
        } else {
          /* create user id */
          that.user = typeof user != "undefined"?user:generateUUID();

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
              if (typeof window.EventSource == "function") {
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