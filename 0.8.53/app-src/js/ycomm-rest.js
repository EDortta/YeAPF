/*********************************************
 * app-src/js/ycomm-rest.js
 * YeAPF 0.8.53-114 built on 2017-01-25 17:53 (-2 DST)
 * Copyright (C) 2004-2017 Esteban Daniel Dortta - dortta@yahoo.com
 * 2017-01-25 17:51:30 (-2 DST)
 *
 * ycomm-rest.js is a set of prototyped functions
 * build in order to use REST protocol
 *
 *********************************************/
//# sourceURL=app-src/js/ycomm-rest.js

  ycomm.setDataLocation = function(dataLocation, deviceId) {
    ycomm._dataLocation_=dataLocation;
    ycomm._deviceId_=deviceId || guid();
  };

  ycomm.getDataLocation = function () {
    return ycomm._dataLocation_;
  };

  ycomm._scriptSequence = 0;
  ycomm._maxScriptSequenceReceived = 0;
  ycomm._CBSeq = 1000;
  ycomm._CBControl = {};
  ycomm._load = 0;
  ycomm._queue = 0;
  ycomm._maxDirectCall = 10;

  ycomm._dataLocation_ = (
    function() {
      var a = (typeof document=='object' && document.location && document.location.href)?document.location.href:'';
      var b=a.lastIndexOf('/');
      return a.substr(0,b+1)+'rest.php';
    }
  )();

  ycomm.getLoad = function () {
    return ycomm._load;
  };  

  ycomm._removeJSONP = function (scriptSequence, callback) {
    var head = document.head;
    var scriptID = "rest_"+scriptSequence;
    var script = document.getElementById(scriptID);
    if ((head!==undefined) && (script!==undefined)) {
      clearTimeout(script._whatchdog_);
      if (typeof script.abort === "function")
        script.abort();
      head.removeChild(script);
      _dumpy(4,1,'Clean '+scriptID+' after call to '+callback+'()');

    } else
      _dumpy(4,1,'Script not found: '+scriptID+' adressed to '+callback+'()');
    _dumpy(4,1,ycomm.getStatus());
  };

  ycomm.bring =  function (url, displayWaitIcon) {
    var head = document.head;
    if (displayWaitIcon)
      ycomm.waitIconControl(true);
    var script = document.createElement("script");
    _dumpy(4,1,url);
    // extrair o scriptSequence e o callback para depuracao
    var scriptSequence=null;
    var callbackFunctionName=null;
    var aux = url.substr(url.indexOf('?')+1).split('&');
    for(var i in aux) {
      if (aux.hasOwnProperty(i)) {
        var v = aux[i].split('=');
        if (v[0]=='scriptSequence')
          scriptSequence=v[1];
        if (v[0]=='callback')
          callbackFunctionName=v[1];
      }
    }

    ycomm._maxScriptSequenceReceived = Math.max(ycomm._maxScriptSequenceReceived, scriptSequence);

    script.UUID = generateUUID();
    script.maxWaitCount=(ycomm.timeout / ycomm.wd_interval)+2;
    script.callbackFunctionName=callbackFunctionName;
    script.displayWaitIcon = displayWaitIcon;
    script.onload=function() {
      if (ycomm._load>0)
        ycomm._load--;
      this.abort=null;
      if (this.displayWaitIcon)
        ycomm.waitIconControl(false);
    };

    script.abort = function () {
        _dumpy(4,1,"Calling {0}(404);".format(callbackFunctionName));
        /* https://pt.wikipedia.org/wiki/Lista_de_códigos_de_status_HTTP#404_N.C3.A3o_encontrado */
        setTimeout("{0}(404,{message: 'Server do not respond ({1})'}, {})".format(callbackFunctionName, url), 100);
    };

    script.pool=function() {
      _dumpy(4,5,this.UUID+ " : "+this.maxWaitCount);
      this.maxWaitCount--;
      if (this.maxWaitCount>0) {
        this._whatchdog_=setTimeout(this.id+".pool()", ycomm.wd_interval);
      } else {
        if (typeof this.abort == "function")
          this.abort();
      }
    };

    script.setAttribute("src", url);
    script.id='rest_'+scriptSequence;

    try {
      _dumpy(4,2,"Creating {0} as {1}".format(script.UUID, script.src));
      head.appendChild(script);
      setTimeout(script.id+".pool()", ycomm.wd_interval);
    } catch(e) {
      _dump("Exception: {0}".format(e.message));
    }

    setTimeout("ycomm._removeJSONP("+scriptSequence+",'"+callbackFunctionName+"');", ycomm.timeout);

  };

  ycomm.crave = function (s, a, limits, callbackFunction, displayWaitIcon, callbackId) {
    var localU = (typeof u == 'undefined')?'':u;
    if ((typeof callbackId == 'undefined') || (callbackId === null))
      callbackId = 0;
    if ((typeof displayWaitIcon == 'undefined') || (displayWaitIcon === null))
      displayWaitIcon = true;

    ycomm.registerCall('crave', s, a);
    /* sequence number for script garbage collect */
    ycomm._scriptSequence++;
    if (!ycomm.getDataLocation())
      console.error("You need to define dataLocation before 'crave' it");
    else {
      var callbackFunctionName;

      if (typeof callbackFunction=='function') {
        /* the user has passed an annon function
         * CallBack sequencer */
        ycomm._CBSeq++;

        /* name for the callback function */
        callbackFunctionName="ycb"+ycomm._CBSeq;

        /* callback control... for garbage collect */
        ycomm._CBControl[callbackFunctionName]={ready: false};

        window[callbackFunctionName]=function(status, error, data, userMsg, context, geometry) {
          callbackFunction(status, error, data, userMsg, context, geometry);
          _dumpy(4,1,callbackFunctionName);
        };
      } else if (typeof callbackFunction=='string') {
        callbackFunctionName=callbackFunction;
      } else
        console.error("param callBackFunction need to be function or string");

      if (callbackFunctionName>'') {
        /* number of concurrent calls */
        ycomm._load++;

        var aURL=ycomm.buildCommonURL(s || '', a || '', limits || {}, localU);
        aURL="{0}?{1}&callback={2}&callbackId={3}&scriptSequence={4}&deviceId={5}".format(ycomm._dataLocation_, aURL, callbackFunctionName, callbackId, ycomm._scriptSequence,ycomm._deviceId_);
        if (ycomm.getLoad()<=ycomm._maxDirectCall) {
          _dumpy(4,1,aURL);
          ycomm.bring(aURL, displayWaitIcon);
        } else
          setTimeout("ycomm.bring('"+aURL+"');", (0.5 + abs(ycomm.getLoad() - ycomm._maxDirectCall)) * ycomm.wd_interval * 2);
      }

    }
  };

  ycomm.isIdle = function () {
    return (ycomm._maxScriptSequenceReceived == ycomm._scriptSequence);
  };

  ycomm.getStatus = function () {
    return "isIdle() = {0} getLoad() = {1}".format(ycomm.isIdle(), ycomm.getLoad());
  };
