  /********************************************************************
   * app-src/js/ycomm-ajax.js
   * YeAPF 0.8.61-105 built on 2018-10-16 08:01 (-3 DST)
   * Copyright (C) 2004-2018 Esteban Daniel Dortta - dortta@yahoo.com
   * 2018-09-18 06:22:48 (-3 DST)
   *
   * Com o advento do WebSocket, precisamos de novas formas para
   * provocar o servidor.
   * Este primeiro passo pretende melhorar o Ajax
   * Depois, virão funções genericas
   * Caso esteja usando prototype, ele usará o mesmo, se não se virará
   * para criar uma interface
   *
   * verificar ServerSentEvents
   * 2013-08-31
   * https://developer.mozilla.org/en-US/docs/Server-sent_events/Using_server-sent_events
   *
   * requires ycomm.js to be loaded
   * the callback function will recive: (status, xError, xData, xUserMsg, xDataContext, xGeometry)
   ********************************************************************/

  if (typeof xAjax=='undefined') {
    console.log("Using own xAjax() implementation");
    /*
     * 1) implementar um xAjax simples
     * 2) depois deixar de depender do prototype (107K)
     */
    var xAjax = function() {
      var that = {};

      if (typeof XMLHttpRequest !== 'undefined') {// code for IE7+, Firefox, Chrome, Opera, Safari
        that.xmlhttp=new XMLHttpRequest();
      } else { // code for IE6, IE5
        that.xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
      }

      that.Request=function (script, options) {
        // recognized options:
        // method, asynchronous, parameters, onComplete
        that.xmlhttp.onreadystatechange = function() {

          if (that.xmlhttp.readyState>0) {
            if (typeof options.onProgress != 'undefined') {
              options.onProgress(that.xmlhttp);
            }
          }

          if (that.xmlhttp.readyState==4) {
            if (typeof options.onComplete != 'undefined') {
              options.onComplete(that.xmlhttp);
            }
          }
        };

        that.xmlhttp.ontimeout = function() {
        };

        if (yloader.isWorker) {
          options.asynchronous = false;
        } else {
          if (options.multipart)
            options.asynchronous = true;
        }

        if ((options.method || 'POST').toUpperCase()=='POST') {
          that.xmlhttp.open((options.method || 'POST'), script, options.asynchronous);
          that.xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded;charset=utf-8");
          that.xmlhttp.send(options.parameters);
        } else {
          var sep;
          if (script.indexOf('?')!==-1)
            sep='&';
          else
            sep='?';
          that.xmlhttp.open(options.method, script+sep+options.parameters, options.asynchronous);
          that.xmlhttp.send();
        }
      };
      return that;
    };
  }

  ycomm.scriptName = yloader.isWorker?'../query.php':'query.php';

  ycomm.defaultMethod = 'post';
  ycomm.canReceiveMessages = true;

  /* receive the xml envelope and split it in parts in order
   * to feed ycomm-dom functions */
  ycomm.explodeData = function(xmlDoc) {
    var xmlArray = xml2array(xmlDoc);

    var xCallBackFunction,
        xData, retData,
        xRoot = xmlArray['root'] || {},
        xDataContext = xRoot['dataContext'] || {},

        xError = xRoot['error'] ||
                 xDataContext['error'] ||
                 xDataContext['lastError'],

        xCallBackFunction = xmlArray['root']['callBackFunction'],
        xGeometry = null,
        xUserMsg = xDataContext['userMsg'],
        xSysMsg = xDataContext['sysMsg'];
        xStack = xDataContext['stack'];

    if (xStack) {
      var requestedS = xDataContext['s'];
      var requestedA = xDataContext['a'];
      var requestedV = xDataContext['v'];
      console.log("/------- {0}.{1}.v{2} ---".format(requestedS, requestedA, requestedV));
      for(var i in xStack) {
        console.log("| STACK: %c"+xStack[i],"color: #FF4D48");
      }
      console.log("\------- {0}.{1}.v{2} ---".format(requestedS, requestedA, requestedV));
    }

    /* sysMsg has higher priority, so it is processed prior to user application
     * sysMsg.msg = ( logoff, alert )
     * 'logoff' message block all sucessive requests and redirect main URL to 'body.php?s=yeapf&a=logoff'
     */

    if (xSysMsg) {
      if (xSysMsg.msg) {
        if (xSysMsg.msg=='logoff') {
          ycomm.canReceiveMessages = false;
          /* If I'm on a windows, I need to close the opener */
          var wOpener=window, wAux;
          while (wOpener.opener) {
            wAux=wOpener;
            wOpener=wOpener.opener;
            wAux.close();
          }

          while (wOpener.parent != wOpener)
            wOpener = wOpener.parent;

          if (xSysMsg.banner) {
            _dumpy(4,1,xSysMsg.banner);
            alert(xSysMsg.banner);
          }
          wOpener.document.location='body.php?s=yeapf&a=logoff';
        }
      }
    }

    /* only continue if the user is logged */
    if (ycomm.canReceiveMessages) {
      if (xDataContext) {
        var i;
        if (xDataContext.requiredFields) {
          var reqFields = xDataContext.requiredFields.split(',');
          for(i = 0; i<reqFields.length; i++) {
            fieldName=reqFields[i];
            if (y$(fieldName))
              y$(fieldName).addClass('fieldWarning');
          }
        }

        if (xDataContext.formError) {
          var auxFormError = '';
          for(i in xDataContext.formError)
            if (xDataContext.formError.hasOwnProperty(i)) {
              if (auxFormError>'')
                auxFormError+="\n";
              auxFormError = auxFormError+xDataContext.formError[i];
            }
          alert(auxFormError);
        }
      }


      if (xRoot) {

        if (xDataContext['formID']!=undefined) {
          if (formID=='') {
            formID=xDataContext['formID'];
            // alert("FORMID: "+formID);
          }
        }

        xDataContext['firstRow'] = parseInt(xDataContext['firstRow']);
        xDataContext['rowCount'] = parseInt(xDataContext['rowCount']);
        xDataContext['requestedRows'] = parseInt(xDataContext['requestedRows']);

        var auxRowCount = xDataContext['rowCount'];

        if (xRoot['data'])
          xData=xRoot['data']['row'];
        else
          xData=xRoot['row'];

        if (auxRowCount==1) {
          xData=new Array(xData);
        }


        if (xData) {
          retData = [];
          for(var n in xData)
            if (xData.hasOwnProperty(n)) {
              retData[n] = {};
              for(var j in xData[n])
                if (xData[n].hasOwnProperty(j))
                  retData[n][j]=unmaskHTML(xData[n][j]);
            }
        }

        if (xRoot['data']!==undefined)
          xGeometry = xRoot['data']['geometry'];

      }
    } /* end of (ycomm.canReceiveMessages==true) */


    var ret = {
      data: retData,
      geometry: xGeometry,
      dataContext: xDataContext,
      error: xError,
      userMsg: xUserMsg
    };

    return ret;

  };

  ycomm.text2data = function (aResponseText) {
    var ret={};

    if (typeof DOMParser == 'function')  {
      var parser = new DOMParser();
      var xmlDoc = parser.parseFromString(aResponseText, "application/xml");
      ret=ycomm.explodeData(xmlDoc);
    }
    return ret;
  };

  ycomm.registerCall = function(via, s, a) {
    if ((ydbg) && ((ydbg.logFlag & 8)>0)) {
      if (typeof _ycomm_stat == 'undefined') {
        window._ycomm_stat = [];
      }

      if (typeof _ycomm_stat[via] == 'undefined')
        _ycomm_stat[via] = [];

      if (typeof _ycomm_stat[via][s] == 'undefined')
        _ycomm_stat[via][s] = [];

      if (typeof _ycomm_stat[via][s][a] == 'undefined')
        _ycomm_stat[via][s][a] = {count:0};

      _ycomm_stat[via][s][a].count++;
      _dumpy(4,2,"via: {0} s: {1} a: {2} count: {3}".format(via, s, a, _ycomm_stat[via][s][a].count));
    }
  }

  ycomm.dataLength = function (data) {
    var cc=0;
    if (data) {
      for (var i in data) { 
        if (data.hasOwnProperty(i)) 
          cc++; 
      };
    }
    return cc;
  };

  /*
   * https://developer.mozilla.org/en-US/docs/Web/API/FormData
   * https://developer.mozilla.org/en-US/docs/Web/Guide/Using_FormData_Objects
   * https://developer.mozilla.org/en-US/docs/Web/API/FileReader#readAsArrayBuffer%28%29
   */

  ycomm.invoke = function(s, a, limits, callbackFunction, displayWaitIcon) {
      if (typeof displayWaitIcon=='undefined')
        displayWaitIcon = true;
      /* if the first parameter is an object, then
       * all the others parameters are expected to be into that object */
      if (typeof s =='object') {
        var auxObj = s;
        s = auxObj.s;
        a = auxObj.a;
        limits  = auxObj.limits;
        callbackFunction = auxObj.callbackFunction;
      }
      var localU = (typeof u == 'undefined')?'':u;
      if (displayWaitIcon)
        ycomm.waitIconControl(true);

      var aURL=ycomm.buildCommonURL(s || '', a || '', limits || {}, localU);
      var promiseRet = new Promise(
        function(resolve, reject) {
          if (typeof xAjax!='undefined') {

            ycomm.registerCall('invoke', s, a);

            var aux=xAjax();
            aux.Request(
              ycomm.scriptName,
              {
                method: ycomm.defaultMethod,
                asynchronous: !yloader.isWorker,
                parameters: aURL,
                onTimeout: function() {
                  console.log('XMLHttpRequest timeout');
                  if (displayWaitIcon)
                    ycomm.waitIconControl(false);
                  callbackFunction(404, {}, [{}], null, null, null);
                  reject({status: 404, message: "Timeout"});
                },
                onComplete: function(r) {
                    var retData = {
                      data: null,
                      geometry: null,
                      dataContext: null,
                      error: null,
                      userMsg: null
                    },
                    xmlDoc=null;

                    if (r.status==200) {
                      if ((ycomm.msg) && (typeof ycomm.msg.notifyServerOnline =='function'))
                        ycomm.msg.notifyServerOnline();


                      if (r.responseXML) {
                        xmlDoc = r.responseXML;
                      } else {
                        if (typeof DOMparser == 'function')  {
                          var parser = new DOMParser();
                          xmlDoc = parser.parseFromString(r.responseText, "application/xml");
                        }
                      }

                      if (xmlDoc!==null)
                        retData = ycomm.explodeData(xmlDoc);

                      resolve({status: 200, return: retData});

                    } else {
                      console.log(r.statusText);
                      if ((ycomm.msg) && (typeof ycomm.msg.notifyServerOffline =='function'))
                        ycomm.msg.notifyServerOffline();
                      reject({ status: r.status });
                    }

                    ycomm.waitIconControl(false);

                    if (retData.error) {
                      if (typeof retData.error == "string")
                        console.error(retData.error);
                      else {
                        for(var k in retData.error) {
                          if (retData.error.hasOwnProperty(k))
                            console.error(retData.error[k]);
                        }
                      }
                    }

                    if (typeof callbackFunction=='function') {
                      if (yloader.isWorker)
                        callbackFunction(r.responseText);
                      else
                        callbackFunction(r.status, retData.error, retData.data, retData.userMsg, retData.dataContext, retData.geometry);                    
                    }

                  }
              }
            );
          } else {
            console.log("Not ready to call "+aURL);
            console.log("prototype library not loaded");
            reject({ status: -1, message: 'xAjax not found'});
          }
        }
      );
      return promiseRet;
    };

