/*********************************************
 * app-src/js/ycomm.js
 * YeAPF 0.8.63-54 built on 2019-07-02 19:38 (-3 DST)
 * Copyright (C) 2004-2019 Esteban Daniel Dortta - dortta@yahoo.com
 * 2019-05-24 18:30:00 (-3 DST)
 * First Version (C) 2010 - esteban daniel dortta - dortta@yahoo.com
**********************************************/
//# sourceURL=app-src/js/ycomm.js

  function processError(xError)
  {
    var errNo=xError.errNo;
    var errMsg=xError.errMsg;
    var errDetail=xError.errDetail;
    if (typeof errDetail != 'string') {
      var d1=array2text(errDetail['sys.stack'],false);
      if (d1 !== undefined)
        d1='\n==[stack]===================================\n'+d1;
      var d2=errDetail['sys.sqlTrace'];
      if (d2 !== undefined)
        d2='\n==[sql]===================================\n'+d2;
      var d3=errDetail['sys.sqlError'];
      errDetail=d3+d2+d1;
    }

    return 'Err #'+errNo+'\n-------- '+errMsg+'\n-------- '+errDetail;
  }


  var yRestTimeControl = function (initialRestTime) {
    var that = { };

    that.setRestTime = function (aValue) {
      that._restTime = Math.min( Math.max(125, aValue), 240 * 60 * 1000);
    };

    that.adjustRestTime = function (t1) {
      var t2, tDif, interleaveDif, aux;
      t2 = (new Date()).getTime();
      tDif = t2 - t1;
      aux = that._restTime - (that._restTime - tDif) / 2;
      that.setRestTime(aux);
    };

    that.init = function () {
      that.setRestTime(initialRestTime);
      Object.defineProperty(
        that,
        "restTime",
        {
          get: function () { return that._restTime; },
          set: that.setRestTime
        }
      );
      return that;
    };

    return that.init();
  };

  var ycommBase = function () {
    var that = {};

/*http://www.blooberry.com/indexdot/html/topics/urlencoding.htm*/

    that.urlCodification = {
        '%20' : ' ',
        '%21' : '!',
        '%2A' : '*',
        '%27' : "'",
        '%28' : '(',
        '%29' : ')',
        '%3B' : ';',
        '%3A' : ':',
        '%40' : '@',
        '%26' : '&',
        '%3D' : '=',
        '%2B' : '+',
        '%24' : '$',
        /* '%25' : '%', cannot be at list as it corrupts the process */
        '%2C' : ',',
        '%2F' : '/',
        '%3F' : '?',
        '%23' : '#',
        '%5B' : '[' ,
        '%5D' : ']' ,
        '%22' : '"',
        '%27' : "'"};

    that._AsyncMode=true;

    that._dummyWaitIconControl = function () {};

    // sets async mode.  defaults to true
    that.setAsyncMode = function (aAsyncMode) {
        if (aAsyncMode === undefined)
          aAsyncMode=true;

        _AsyncMode=aAsyncMode;
    };

    that.xq_urlEncode = function(aURL, aQuoted) {
      if (typeof aQuoted=='undefined')
        aQuoted=true;
      if ((typeof aURL=='string') && (aURL>'')) {
        /* '%' need to be changed first */
        aURL=aURL.replace(/%/g,'%25');
        /* ',' must be escaped */
        aURL=aURL.replace(/,/g,'\\,');
        for(var n in that.urlCodification)
          if (that.urlCodification.hasOwnProperty(n)) {
            var re = new RegExp(escapeRegExp(that.urlCodification[n]), 'g');
            aURL = aURL.replace(re, n);
          }

        if (!((aURL.substring(0,1)=="'") || (aURL.substring(0,1)=='"')))
          if (!isNumber(aURL))
            if (aQuoted)
              aURL='"'+aURL+'"';
      }
      return aURL;
    };

    that.urlJsonAsParams  = function(jsonParams) {
      var fieldName='';
      var fieldValue='';
      var auxFieldValue='';
      for(var jNdx in jsonParams) {
        if (jsonParams.hasOwnProperty(jNdx)) {
          if (fieldName>'') {
            fieldName+=',';
            fieldValue+=',';
          }

          fieldName += jNdx;
          auxFieldValue = jsonParams[jNdx];
          if ("boolean"==typeof auxFieldValue) {
            auxFieldValue=auxFieldValue?"YES":"NO";
          } else {
            if ("object"==typeof auxFieldValue) {
              auxFieldValue=JSON.stringify(auxFieldValue);
            }
          }
          auxFieldValue = maskHTML(that.xq_urlEncode(auxFieldValue, false));
          fieldValue += auxFieldValue;
        }
      }
      fieldName='('+fieldName+')';
      fieldValue='('+fieldValue+')';
      return [fieldName, fieldValue];
    };

    that.buildCommonURL = function (s, a, jsonParams, u) {

      if (typeof jsonParams == 'undefined')
        jsonParams = {};

      var jsonAsParams=that.urlJsonAsParams(jsonParams);
      var fieldName=jsonAsParams[0];
      var fieldValue=jsonAsParams[1];

      if (u===undefined)
        u='';

      var aURL="s={0}&a={1}&u={2}&fieldName={3}&fieldValue={4}".format(s, a, u || '', fieldName, fieldValue);

      var ts=(new Date()).getTime();
      aURL+='&ts='+ts;
      aURL+='&_rap_'+ts+'=1';
      // aURL=aURL.replace('%','%25');
      return aURL;
    };

    that.setWaitIconControl = function (aFunction) {
      that.waitIconControl = aFunction || that._dummyWaitIconControl;
    };

    that.pinger = {
        canPing: false,
        pingerWatchdog: null,
        pingCount: 0,
        pingTimeout: 15 * 1000,
        pingInterleave: 1500,
        onSuccess: null,
        onError: null,

        pong : function(aStatus, aError, aData) {
          if (that.pinger.pingerWatchdog) clearTimeout(that.pinger.pingerWatchdog);
          _dumpy(4,1,"ping answer loc:{0} rem:{1}".format(that.pinger.pingCount, aData.pingCount || 0));
          if (that.pinger.pingCount<=aData.pingCount) {
            that.pinger.pingCount=0;
          }
          _dumpy(4,1,"ping answer received {0}/{1}".format(that.pinger.pingCount, aData.pingCount));
          if (that.pinger.onSuccess !== null)
            that.pinger.onSuccess();
          if (that.pinger.canPing)
            that.pinger.pingerWatchdog = setTimeout(that.pinger.ping, that.pinger.pingInterleave);
        },
        /*
         * após um tempo de 60 segundos (pingTimeout)
         * sem resposta, ele cai nesta função e
         * volta a tentar em 1/2 pingInterleave
         */
        notAnswer: function () {
          if (that.pinger.pingerWatchdog) clearTimeout(that.pinger.pingerWatchdog);
          _dumpy(4,1,"ping timeout");
          if (that.pinger.onError !== null)
            that.pinger.onError();
          else
            _dumpy(4,1,"ping without 'onError' event");
          // sayStatusBar("Servidor não localizado "+that.pinger.pingCount+'...<br>Tentando novamente');
          if (that.pinger.canPing) {
            _dumpy(4,1,"ping Scheduling a new call for {0}ms".format(that.pinger.pingInterleave / 2))
            that.pinger.pingerWatchdog=setTimeout(that.pinger.ping, that.pinger.pingInterleave / 2);
          }
        },

        /*
         * tenta localizar o servidor.  manda um numero.
         * ele retorna o mesmo numero mais um timestamp
         */
        ping: function (aOnSuccess, aOnError, keepPinging) {
          if (that.pinger.pingerWatchdog) clearTimeout(that.pinger.pingerWatchdog);
          _dumpy(4,1,"ping being called");
          that.pinger.canPing = keepPinging || false;
          that.pinger.onSuccess = aOnSuccess  || that.pinger.onSuccess;
          that.pinger.onError = aOnError || that.pinger.onError;

          that.pinger.pingCount++;
          ycomm.crave('yeapf','ping',{ "pingCount": that.pinger.pingCount },'ycomm.pinger.pong');
          that.pinger.pingerWatchdog=setTimeout(that.pinger.notAnswer, that.pinger.pingTimeout);
        },

        stopPing: function () {
          if (that.pinger.pingerWatchdog) clearTimeout(that.pinger.pingerWatchdog);
          _dumpy(4,1, "ping stopping");
          that.pinger.canPing = false;
        }

    };

    that.init = function() {
      that._comm_timeout = 120000;  /* defaults to 120seconds */
      that._whatchdog_interleave = 250;

      Object.defineProperty(
        that,
        "timeout",
        {
          get:  function () { return that._comm_timeout; },
          set:  function (newTimeout) { 
                  newTimeout = parseInt(newTimeout || 0); 
                  /* it only accepts values between 125ms and 5minutes */ 
                  that._comm_timeout = Math.min(5*60*60*1000, Math.max(125, newTimeout));
                  _dumpy(4,0,"ping Adjusting call timeout to {0}ms".format(that._comm_timeout));
                }
        }
      );

      Object.defineProperty(
        that,
        "wd_interval",
        {
          get: function() { return that._whatchdog_interleave; },
          set: function (newInterval) {
                newInterval = parseInt(newInterval || 0);
                /* only accepts values between 100ms and 3/4 of timeout */
                that._whatchdog_interleave = Math.min((that.timeout*3/4), Math.max(100, newInterval));
                _dumpy(4,0,"ping Adjusting watchdog interleave to {0}ms".format(that._whatchdog_interleave));
          }
        }
      );


      that.setWaitIconControl();

      return that;
    }

    return that.init();
  };

  var ycomm = ycommBase();

