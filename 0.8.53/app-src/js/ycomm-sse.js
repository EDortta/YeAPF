  /********************************************************************
  * app-src/js/ycomm-sse.js
  * YeAPF 0.8.53-30 built on 2017-01-12 15:16 (-2 DST)
  * Copyright (C) 2004-2017 Esteban Daniel Dortta - dortta@yahoo.com
  * 2017-01-12 15:09:48 (-2 DST)
  ********************************************************************/
  var ycommSSEBase = function (w, dataLocation) {
    var that = {

      getLocation: function() {
        return (typeof document=='object' && document.location && document.location.href)?document.location.href:'';
      },

      getFolder: function(location) {
        var b=location.lastIndexOf('/');
        return location.substr(0,b+1);
      },

      poll: function () {
        that.rpc(
          "sse",
          "peekMessage",
          {
            "sse_session_id": that.sse_session_id
          },
          function(status, error, data) {
            if (data) {
              for(var i=0; i<data.length; i++) {
                that.message({
                  'event': data[i].event,
                  'data' : data[i].data
                });
              }
            }
            setTimeout(that.poll, 750);
          }
        );
      },

      startPolling: function () {
        /* check same-source */
        var l1=that.getFolder(that.getLocation()),
            l2=that.getFolder(that._dataLocation_);

        if (l1==l2) {
          that.rpc = ycomm.invoke;
        } else {
          that.rpc = ycomm.crave;
        }
        that.rpc(
          "sse",
          "attachUser",
          {
            "w": w,
            "u": typeof u != "undefined"?u:generateSmallSessionUniqueId()
          },
          function(status, error, data) {
            if (data && data[0] && data[0].ok) {
              that.sse_session_id = data[0].sse_session_id;
              that.poll();
            }
          }
        );
      },

      guardianTimeout: function (e) {
        /* if SSE.PHP don't answer up to guardian timeout, use poll version */
        clearTimeout(that.evtGuardian);
        that.evtSource.close();
        setTimeout(that.startPolling, 125);
      },

      open: function (e) {
        console.log(e);
      },

      error: function(e) {
        console.error(e);
      },

      message: function (e) {
        /* as connected, clear guardian timeout */
        clearTimeout(that.evtGuardian);
        if (typeof that.onmessage=="function") {
          that.onmessage(e.data);
        }
      },

      init: function() {
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
          /* first try to use EventSource() */
          if (typeof window.EventSource == "function") {
            that.evtSource = new EventSource(that._dataLocation_);
            that.evtGuardian = setTimeout(that.guardianTimeout, 2000);
            that.evtSource.addEventListener("open",    that.open,    false);
            that.evtSource.addEventListener("error",   that.error,   false);
            that.evtSource.addEventListener("message", that.message, false);
          } else {
            that.startPolling();
          }
        }

        return that;
      }
    };

    return that.init();
  };