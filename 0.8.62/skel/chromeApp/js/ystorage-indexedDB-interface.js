/*
aDBName is a string that identifies the database in local indexedDB
aDBVersion is an integer that identifies the database version
aTablesDefinition is an array of json.
   Each element of this array must contain:
     name (the name of the set of documents or a tableName in SQL notation)
     keyPath (the name of the key field or the PK in SQL notation)
     autoIncrement (a boolean that indicates if indexedDB will or will not generate the 'keyPath' field)
*/

window._WIDBI_AUTO = -1;
window._WIDBI_AS_WORKER = 1;
window._WIDBI_AS_IFRAME = 2;
window._WIDBI_AS_INLINE = 3;

var yIndexedDBInterfaceObj = function(aDBName, aDBVersion, aTablesDefinition, aWorkerStyle) {
  aWorkerStyle = str2int(aWorkerStyle);
  if (aWorkerStyle<=0) {
    var webKitVersion = yloader.webKitVersion.split('.')[0];
    if (webKitVersion<537)
      aWorkerStyle=_WIDBI_AS_IFRAME;
    else
      aWorkerStyle=_WIDBI_AS_WORKER;
  }

  var that = {};
  var wIndexedDB,
      callerIdBase = newIdentifier(),
      callBackFunctionList = [],
      lastStatus = null,
      postMessageSecondParameter;

  function _msgReceiver(e) {
    _dumpy(128,1,"yIndexedDBInterfaceObj _msgReceiver "+e.data.cmd);
    switch(e.data.cmd) {

      case'initialization':
        _dumpy(128,1,'initialization status: '+e.data.information.status);

        if (e.data.information.status!=lastStatus) {
          lastStatus = e.data.information.status;
          if (typeof that.onstatuschange == "function")
            setTimeout(
              function() {
                that.onstatuschange(lastStatus);
              },
              125);
        }
      break;

      case 'callback':
        var auxInfo = JSON.parse(e.data.information.cbr);
        var callbackIdentifiers = ['callbackId', 'callbackOnItem', 'callbackOnComplete'];
        for (var i=0; i<callbackIdentifiers.length; i++) {
          var auxCallbackId = auxInfo[callbackIdentifiers[i]];

          if (typeof callBackFunctionList[auxCallbackId] == "object") {
            callBackFunctionList[auxCallbackId].callback(auxInfo);
            if ((callBackFunctionList[auxCallbackId].destroy) || (auxInfo._removeThis)) {
              if ("string" == typeof callBackFunctionList[auxCallbackId].sibling) {
                delete callBackFunctionList[callBackFunctionList[auxCallbackId].sibling];
              }
              delete callBackFunctionList[auxCallbackId];
            }
          }

        }

        break;

      case 'log':
        _dumpy(128,1,e.data.information.message);
      break;
    }
  }

  function newCallerId(callback, autoDestroy, sibling) {
    var callbackId = null;
    autoDestroy = (typeof autoDestroy=="undefined")?true:autoDestroy;
    sibling = sibling || false;

    if (typeof callback=="function") {
      var entry = {};
      entry = {
        'callback': callback,
        'destroy':  autoDestroy,
        'sibling':  sibling
      }
      callbackId = callerIdBase+generateSmallSessionUniqueId();
      callBackFunctionList[callbackId] = entry;
    }
    return callbackId;
  }

  function voidCallBack(e) {
    _dumpy(128,1,'voidCallBack() ', e);
  }

  var tableObject = function(tableName) {
    var tableThat = {};

    tableThat.count =function (callback) {
      // callback = callback || voidCallBack;

      wIndexedDB.postMessage({
        'cmd':       'count',
        'callbackId':  newCallerId(callback, true),
        'tableName': tableThat.tableName
      }, postMessageSecondParameter)
    };

    tableThat.setItem =function (keyValue, data, callback) {
      // callback = callback || voidCallBack;

      wIndexedDB.postMessage({
        'cmd':       'setItem',
        'callbackId':  newCallerId(callback, true),
        'tableName': tableThat.tableName,
        'data': data,
        'keyValue': keyValue
      }, postMessageSecondParameter)
    };

    tableThat.getItem =function (keyValue, callback) {
      // callback = callback || voidCallBack;

      wIndexedDB.postMessage({
        'cmd':       'getItem',
        'callbackId':  newCallerId(callback, true),
        'tableName': tableThat.tableName,
        'keyValue': keyValue
      }, postMessageSecondParameter)
    };

    tableThat.insertData = function (dataArray, callback, aFieldsToPreserve ) {
      var onItemCallbackId = newCallerId(callback, false);
      wIndexedDB.postMessage(
      {
        'cmd': 'insertData',
        'tableName': tableThat.tableName,
        'callbackId': onItemCallbackId,
        'dataArray': dataArray.slice(),
        'fieldsToPreserve': aFieldsToPreserve
      }, postMessageSecondParameter);
    };

    tableThat.filter = function(onitem, oncomplete, condition, haltOnFirst) {
      var onItemCallbackId = newCallerId(onitem, false);
      wIndexedDB.postMessage(
      {
        'cmd': 'filter',
        'tableName': tableThat.tableName,
        'callbackOnItem': onItemCallbackId,
        'callbackOnComplete': newCallerId(oncomplete, true, onItemCallbackId),
        'condition': condition,
        'haltOnFirst': haltOnFirst
      }, postMessageSecondParameter);
    };

    tableThat.removeItem =function (keyValue, callback) {
      // callback = callback || voidCallBack;

      wIndexedDB.postMessage({
        'cmd':       'removeItem',
        'callbackId':  newCallerId(callback, true),
        'tableName': tableThat.tableName,
        'keyValue': keyValue
      }, postMessageSecondParameter)
    };

    tableThat.removeData =function (arrayOfKeys, callback) {
      // callback = callback || voidCallBack;

      wIndexedDB.postMessage({
        'cmd':       'removeData',
        'callbackId':  newCallerId(callback, false),
        'tableName': tableThat.tableName,
        'arrayOfKeys': arrayOfKeys
      }, postMessageSecondParameter)
    };



    tableThat.init = function() {
      tableThat.tableName = tableName;
      return tableThat;
    }

    return tableThat.init();
  };

  var initializeDatabase = function() {
    wIndexedDB.postMessage({
        'cmd': 'init',
        'dbTag': aDBName,
        'dbVersion': aDBVersion,
        'objectStoreDescription': aTablesDefinition
    }, postMessageSecondParameter);

    for(var i=0; i<aTablesDefinition.length; i++) {
      if (typeof that[aTablesDefinition[i].name] == "undefined") {
        that[aTablesDefinition[i].name] = tableObject(aTablesDefinition[i].name);
      }
    }
  };


  that.init = function() {
    getScriptName = function() {
      var scripts = document.getElementsByTagName('script');
      var sNdx = 0;
      var scriptName='';
      while(sNdx<scripts.length) {
        var auxScriptName=scripts[sNdx].src;
        if (auxScriptName.indexOf("ystorage-indexedDB-interface")>=0)
          scriptName=auxScriptName;
        sNdx++;
      }
      return scriptName;
    };

    var currentScriptName = getScriptName();
    var is_minified = currentScriptName.indexOf(".min.js")>-1;
    var slaveName = (is_minified?"ystorage-indexedDB-slave.min.js":"ystorage-indexedDB-slave.js");

    if (aWorkerStyle==_WIDBI_AS_WORKER) {
      wIndexedDB = new Worker("js/{0}".format(slaveName));
      postMessageSecondParameter=[];
      wIndexedDB.addEventListener("message", _msgReceiver);
      initializeDatabase();
    } else if (aWorkerStyle == _WIDBI_AS_IFRAME) {
      var iframe = document.createElement('iframe');
      var html = "<script src='js/yloader.js'></script>\n<script src='js/{0}'></script>".format(slaveName);
      iframe.frameborder=0;
      iframe.height=0;
      iframe.width=0;
      iframe.style="width:0;height:0;border:0; border:none;display: none;";

      document.body.appendChild(iframe);

      iframe.contentWindow.document.open();
      iframe.contentWindow.document.write(html);
      iframe.contentWindow.document.close();

      iframe.name="ifc_indexedDB";

      wIndexedDB = iframe.contentWindow;

      window.addEventListener("message", _msgReceiver, false);
      postMessageSecondParameter="*";

      iframe.onload = function() {
        initializeDatabase();
      };
    }

    return that;
  }

  return that.init();
}