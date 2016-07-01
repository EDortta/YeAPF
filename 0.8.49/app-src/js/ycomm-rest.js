/*********************************************
 * app-src/js/ycomm-rest.js
 * YeAPF 0.8.49-58 built on 2016-07-01 17:03 (-3 DST)
 * Copyright (C) 2004-2016 Esteban Daniel Dortta - dortta@yahoo.com
 * 2016-07-01 17:03:01 (-3 DST)
 *
 * ycomm-rest.js is a set of prototyped functions
 * build in order to use REST protocol
 *
 *********************************************/
//# sourceURL=app-src/js/ycomm-rest.js

  ycomm.setDataLocation = function(dataLocation, deviceId) {
    this._dataLocation_=dataLocation;
    this._deviceId_=deviceId || guid();
  };

  ycomm.getDataLocation = function () {
    return this._dataLocation_;
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
    return this._load;
  };

  ycomm._removeJSONP = function (scriptSequence, callback) {
    var head = document.head;
    var scriptID = "rest_"+scriptSequence;
    var script = document.getElementById(scriptID);
    if ((head!==undefined) && (script!==undefined)) {
      head.removeChild(script);
      _dumpy(4,1,'Clean '+scriptID+' after call to '+callback+'()');
    } else
      _dumpy(4,1,'Script not found: '+scriptID+' adressed to '+callback+'()');
    _dumpy(4,1,ycomm.getStatus());
  };

  ycomm.rest_timeout = 3500;

  ycomm.bring =  function (url) {
    var head = document.head;
    var script = document.createElement("script");
    _dumpy(4,1,url);
    // extrair o scriptSequence e o callback para depuracao
    var scriptSequence=null;
    var callback=null;
    var aux = url.substr(url.indexOf('?')+1).split('&');
    for(var i in aux) {
      if (aux.hasOwnProperty(i)) {
        var v = aux[i].split('=');
        if (v[0]=='scriptSequence')
          scriptSequence=v[1];
        if (v[0]=='callback')
          callback=v[1];
      }
    }

    ycomm._maxScriptSequenceReceived = Math.max(ycomm._maxScriptSequenceReceived, scriptSequence);

    script.onload=function() {
      if (ycomm._load>0)
        ycomm._load--;
    };
    script.setAttribute("src", url);
    script.id='rest_'+scriptSequence;

    head.appendChild(script);

    setTimeout("ycomm._removeJSONP("+scriptSequence+",'"+callback+"');", ycomm.rest_timeout);

  };

  ycomm.crave = function (s, a, limits, callbackFunction, callbackId) {
    var localU = (typeof u == 'undefined')?'':u;
    if (typeof callbackId == 'undefined')
      callbackId = 0;
    /* sequence number for script garbage collect */
    ycomm._scriptSequence++;
    if (!this.getDataLocation())
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
          console.log(callbackFunctionName);
        };
      } else if (typeof callbackFunction=='string') {
        callbackFunctionName=callbackFunction;
      } else
        console.error("param callBackFunction need to be function or string");

      if (callbackFunctionName>'') {
        /* number of concurrent calls */
        ycomm._load++;

        var aURL=this.buildCommonURL(s || '', a || '', limits || {}, localU);
        aURL="{0}?{1}&callback={2}&callbackId={3}&scriptSequence={4}&deviceId={5}".format(this._dataLocation_, aURL, callbackFunctionName, callbackId, ycomm._scriptSequence,this._deviceId_);
        if (ycomm.getLoad()<=ycomm._maxDirectCall) {
          console.log(aURL);
          ycomm.bring(aURL);
        } else
          setTimeout("ycomm.bring('"+aURL+"');", 250 + (ycomm.getLoad() - ycomm._maxDirectCall) * 500);
      }

    }
  };

  ycomm.isIdle = function () {
    return (ycomm._maxScriptSequenceReceived == ycomm._scriptSequence);
  };

  ycomm.getStatus = function () {
    return "isIdle() = {0} getLoad() = {1}".format(this.isIdle(), this.getLoad());
  };


