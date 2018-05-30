/*********************************************
 * skel/chromeApp/js/ystorage.js
 * YeAPF 0.8.60-72 built on 2018-05-30 19:50 (-3 DST)
 * Copyright (C) 2004-2018 Esteban Daniel Dortta - dortta@yahoo.com
 * 2018-05-30 19:50:50 (-3 DST)
 * First Version (C) 2014 - esteban daniel dortta - dortta@yahoo.com
 * yServerWatcherObj and yInfoObj introduced in 2016-08-22 0.8.50-0
 *********************************************/

 /*
   __utma
   __utmc
   __utmz
   https://davidwalsh.name/detecting-online
 */
(function () {
  "use strict";
  if (!window.ystorage) {
    window.ystorage = (function() {

      var aKeys = [],
        oStorage = {},
        that = {};
      Object.defineProperty(oStorage, "getItem", {
        value: function(sKey) {
          return sKey ? that[sKey] : null; },
        writable: false,
        configurable: false,
        enumerable: false
      });
      Object.defineProperty(oStorage, "key", {
        value: function(nKeyId) {
          return aKeys[nKeyId]; },
        writable: false,
        configurable: false,
        enumerable: false
      });
      Object.defineProperty(oStorage, "setItem", {
        value: function(sKey, sValue) {
          if (!sKey) {
            return; }
          document.cookie = escape(sKey) + "=" + escape(sValue) + "; expires=Tue, 19 Jan 2038 03:14:07 GMT; path=/";
        },
        writable: false,
        configurable: false,
        enumerable: false
      });
      Object.defineProperty(oStorage, "length", {
        get: function() {
          return aKeys.length; },
        configurable: false,
        enumerable: false
      });
      Object.defineProperty(oStorage, "removeItem", {
        value: function(sKey) {
          if (!sKey) {
            return; }
          document.cookie = escape(sKey) + "=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/";
        },
        writable: false,
        configurable: false,
        enumerable: false
      });
      that.get = function() {
        var iThisIndx;
        for (var sKey in oStorage) {
          iThisIndx = aKeys.indexOf(sKey);
          if (iThisIndx === -1) { oStorage.setItem(sKey, oStorage[sKey]); } else { aKeys.splice(iThisIndx, 1); }
          delete oStorage[sKey];
        }
        for (; aKeys.length > 0; aKeys.splice(0, 1)) {
          oStorage.removeItem(aKeys[0]);
        }
        for (var aCouple, iKey, nIdx = 0, aCouples = document.cookie.split(/\s*;\s*/); nIdx < aCouples.length; nIdx++) {
          aCouple = aCouples[nIdx].split(/\s*=\s*/);
          if (aCouple.length > 1) {
            oStorage[iKey = unescape(aCouple[0])] = unescape(aCouple[1]);
            aKeys.push(iKey);
          }
        }
        return oStorage;
      };
      that.configurable = false;
      that.enumerable = true;
      return that;
    })();
  }

  if (!window.ySingleDb) {
    console.log("Creating ySingleDb ... ");
    window.ySingleDb = function(dbTag, aKeyName) {
      var that = {};
      that._list = null;

      that.toBinArray = function(str) {
        var l = (str || '').length,
          arr = new Uint8Array(l);
        for (var i = 0; i < l; i++) arr[i] = str.charCodeAt(i);
        return arr;
      };

      that.toBinString = function(arr) {
        var uarr = new Uint8Array(arr);
        var strings = [],
          chunksize = 0xffff;
        // There is a maximum stack size. We cannot call String.fromCharCode with as many arguments as we want
        for (var i = 0; i * chunksize < uarr.length; i++) {
          strings.push(String.fromCharCode.apply(null, uarr.subarray(i * chunksize, (i + 1) * chunksize)));
        }
        return strings.join('');
      };

      that.setItem = function(id, jData, aFieldsToPreserve) {
        var i, k, oldData;
        id = String(id);

        jData._id = jData._id || generateUUID();
        jData._ts_update = (new Date()).getTime() / 1000;

        if (isArray(aFieldsToPreserve)) {
          oldData=that.getItem(id);
          if (!isEmpty(oldData)) {
            for(i=0; i<aFieldsToPreserve.length; i++) {
              if (typeof oldData[aFieldsToPreserve[i]] !== 'undefined') {
                jData[aFieldsToPreserve[i]]=oldData[aFieldsToPreserve[i]];
              }
            }
          }
        }

        if (jData._linked) {
          for(i=0; i<jData._linked.length; i++) {
            delete jData[jData._linked[i]];
          }
          delete jData._linked;
        }

        for(k in jData) {
          if (jData.hasOwnProperty(k)) {
            if (k.substr(0,1)!='_') {
              if (isArray(jData[k]))
                jData[k]=JSON.stringify(jData[k]);
            }
          }
        }

        localStorage.setItem(that._dbTag_ + "_item_" + id, JSON.stringify(jData));
        if (that._list.indexOf(id) == -1) {
          that._list[that._list.length] = id;
          localStorage.setItem(that._dbTag_ + "_list", JSON.stringify(that._list));
        }
      };

      /* receives a Uint8Array */
      that.setBinItem = function(id, bData, aFieldsToPreserve) {
        that.setItem(id, that.toBinString(bData), aFieldsToPreserve);
      };

      that.getItem = function(id) {
        id = String(id);
        var ret = localStorage.getItem(that._dbTag_ + "_item_" + id);
        ret = JSON.parse(ret || "{}");
        if (!isEmpty(ret)) {
          if (isArray(that._siblingDB)) {
            var linkedData;
            ret._linked=[];

            for(var i = 0; i<that._siblingDB.length; i++) {
              linkedData = that._siblingDB[i].db.getItem(ret[that._siblingDB[i].linkage]);
              for(var n in linkedData) {
                ret._linked=[];
                if (linkedData.hasOwnProperty(n)) {
                  if (typeof ret[n] == 'undefined') {
                    ret[n] = linkedData[n];
                    ret._linked.push(n);
                  }
                }
              }
            }

          }
        }
        return ret;
      };

      that.getBinItem = function(id) {
        return that.toBinArray(that.getItem(id));
      };

      that.getList = function() {
        return that._list || [];
      };

      that.linkTo = function(aSiblingDB, aLinkageField) {
        if (!(aSiblingDB && aSiblingDB.getItem)) {
          aLinkageField=null;
          aSiblingDB=null;
        } else {
          if (typeof aLinkageField == 'undefined')
            aSiblingDB=null;
        }

        if (aSiblingDB) {
          var aux = {
            db: aSiblingDB,
            linkage: aLinkageField || null
          };
          that._siblingDB = that._siblingDB || [];
          that._siblingDB.push(aux);
        }
      };

      that.filter = function(onitem, oncomplete, condition, haltOnFirst) {
        haltOnFirst = false || haltOnFirst;
        if (typeof onitem == "function") {
          condition = condition || true;
          var allValues=false, ylex;
          if (true === condition)
            allValues=true;
          else {
            ylex = yLexObj(condition);
            ylex.parse();
          }
          var i, lista = that.getList(),
              item, canCall;
          for (i = 0; i < lista.length; i++) {
            item = that.getItem(lista[i]);
            canCall = allValues || ylex.solve(item);
            if (canCall) {
              onitem(item);
              if (haltOnFirst)
                i=lista.length+1;
            }
          }
        }
        if (typeof oncomplete == "function")
          oncomplete();
      };

      that.each = function(onitem, oncomplete, condition, haltOnFirst) {
        haltOnFirst = false || haltOnFirst;
        var conditionSatisfied = function(value, needed) {
          needed = String(needed).split(" ");
          var satisfied = true,
            n, p;
          for (n in needed) {
            p = trim(needed[n]);
            if (isNumber(p) && isNumber(value)) {
              satisfied = satisfied && (parseFloat(p) === parseFloat(value));
            } else
              satisfied = satisfied && (""+value).toUpperCase().indexOf(p.toUpperCase()) >= 0;
          }
          return satisfied;
        };

        var i, lista = that.getList(),
          item, canCall;
        for (i = 0; i < lista.length; i++) {
          if (typeof onitem == "function") {
            item = that.getItem(lista[i]);
            /* se nao ha condicao, repassar tudo */
            canCall = typeof condition == "undefined";

            if (!canCall) {
              if (typeof condition == "string") {
                /* caso a condicao seja um string, entao se refere apenas aa chave */
                canCall = conditionSatisfied(item[that.cfg.keyName], condition);
              } else if (typeof condition == "object") {
                canCall = true;
                for (var j in condition) {
                  if (condition.hasOwnProperty(j)) {
                    if (typeof item[j] !== "undefined")
                      canCall = canCall && (conditionSatisfied(item[j], condition[j]));
                    else
                      canCall = false;
                  }
                }
              }
            }
            if (canCall) {
              onitem(item);
              if (haltOnFirst)
                i = lista.length+1;
            }
          }
        }
        if (typeof oncomplete == "function")
          oncomplete();
      };

      that.insertData = function(data, aFieldsToPreserve) {
        for(var i in data) {
          if (data.hasOwnProperty(i)) {
            that.setItem(data[i][that._keyName_], data[i], aFieldsToPreserve);
          }

        }
      };

      that.count = function(condition) {
        var cc = 0;
        that.each(function() { cc++; }, null, condition);
        return cc;
      };

      that.extractData = function(oncomplete, condition) {
        var data = [];
        var onItem = function(item) {
          data[data.length] = item;
        };
        var atEnd = function() {
          oncomplete(data);
        };
        that.each(onItem, atEnd, condition);
      };

      that.fillList = function(data, idFieldName, clean, aFieldsToPreserve) {
        idFieldName = idFieldName || 'id';
        clean = clean || false;

        if (clean)
          that.cleanList();


        for (var i = 0; i < data.length; i++) {
          if (data[i][idFieldName]) {
            that.setItem(data[i][idFieldName], data, aFieldsToPreserve);
          }
        }
      };

      that.removeItem = function(id, propagate) {
        propagate = typeof propagate !== "undefined"?propagate:true;
        if (propagate) {
          if (typeof that.onremove == "function") {
            that.onremove(that.getItem(id));
          }
        }
        localStorage.removeItem(that._dbTag_ + "_item_" + id);
        var ndx = that._list.indexOf(String(id));
        if (ndx > -1) {
          that._list.splice(ndx, 1);
          localStorage.setItem(that._dbTag_ + "_list", JSON.stringify(that._list));
        }
      };

      /* removes a set of items that satisfies a condition */
      that.removeItems = function(condition, propagate) {
        propagate = typeof propagate !== "undefined"?propagate:true;

        that.extractData(
          function(list) {
            for(var i in list) {
              if (list.hasOwnProperty(i)) {
                that.removeItem(list[i][that._keyName_], propagate);
              }
            }
          },
          condition
        );
      };

      /* removes an item without propagate it deletion */
      that.eliminateItem = function(id) {
        that.removeItem(id, false);
      };

      that.eliminateItems = function(condition) {
        that.removeItems(condition, false);
      };

      that.cleanList = function(propagate) {
        propagate = typeof propagate !== "undefined"?propagate:true;
        var _id = [],
            n=0;
        for (var i in that._list) {
          _id[n++] = that._list[i];
        }
        for (n=0; n<_id.length; n++) {
          that.removeItem(_id[n], propagate);
        }
      };

      /* removes all the items in the list without propagating deletion */
      that.blankList = function() {
        that.cleanList(false);
      };

      that.init = function(dbTag, aKeyName) {
        that._dbTag_ = dbTag;
        that._keyName_ = aKeyName || '_id';

        console.log("ystorage: creating " + dbTag);

        that._list = localStorage.getItem(that._dbTag_ + "_list");
        if (typeof that._list == "string")
          that._list = ((JSON) && JSON.parse(that._list)) || [];
        that._list = that._list || [];

        return that;
      };

      return that.init(dbTag, aKeyName);
    };
    console.log("ystorage ready!");
  }

  if (!window.yIndexedDB) {
    if (!window.indexedDB)
      window.indexedDB = window.mozIndexedDB || window.webkitIndexedDB || window.msIndexedDB;
    window.IDBTransaction = window.IDBTransaction || window.webkitIDBTransaction || window.msIDBTransaction;
    window.IDBKeyRange = window.IDBKeyRange || window.webkitIDBKeyRange || window.msIDBKeyRange;

    window.yIndexedDB = function (dbTag, aKeyName, onready_callback) {
      var that={};

      that._newTransaction = function(mode, callback, txCallback) {
        mode=mode || "readonly";

        txCallback = txCallback ||
                     function(errCode) {
                      if(errCode!==0) {
                        if (canCheck) {
                          canCheck=false;
                          if ("function" == typeof callback)
                            callback(520, errCode, [{}]);
                        }
                      }
                    };

        var tx = that._db.transaction([that._dbTag_], "readwrite"),
            waiting = true, error,
            canCheck = true;

        tx.oncomplete = function () {
          error=0;
          waiting = false;
          if ("function" == typeof txCallback)
            txCallback(error);
        };

        tx.onerror = function() {
          error=1;
          waiting = false;
          if ("function" == typeof txCallback)
            txCallback(error);
        };

        var objectStore = tx.objectStore(that._dbTag_);
        return objectStore;
      };

      that.count = function (callback) {
        var objectStore = that._newTransaction("readonly");
        var request = objectStore.count();
        request.onsuccess = function() {
          if ("function" == typeof callback) {
            callback(200, 0, [request.result]);
          }
        };
      };

      that.setItem =function (keyValue, data, callback) {
        var ret=false;
        if (3==that.status) {
          data._id = data._id || generateUUID();
          data._ts_update = (new Date()).getTime() / 1000;

          var objectStore = that._newTransaction("readwrite", callback),
              request = objectStore.put(data);
          request.onsuccess = function() {
            if ("function" == typeof callback)
              callback(200, 0, [request.result]);
          };
          ret=true;
        }
        return ret;
      };

      that.getItem =function (keyValue, callback) {
        var ret=false;
        if (3==that.status) {
          var objectStore = that._newTransaction("readonly", callback),
              request=objectStore.get(keyValue);
          request.onsuccess = function () {
            if ("function" == typeof callback)
              callback(200, 0, [request.result]);
          };
          ret=true;
        }
        return ret;
      };

      that.insertData = function(dataArray, aFieldsToPreserve, callback) {
        var ret=false;
        if (3==that.status) {
          var objectStore = that._newTransaction("readwrite", callback),
              completed = 0, errorCount = 0;

          for (var n=0; (dataArray && n<dataArray.length); n++) {
            var data = dataArray[n], request;
            data._id = data._id || generateUUID();
            data._ts_update = (new Date()).getTime() / 1000;
            request=objectStore.put(data);
            request.onsuccess = function (e) {
              var ret = e.target;
              completed++;
              if (completed + errorCount>=dataArray.length) {
                if ("function" == typeof callback)
                  callback(200, { errorCount: errorCount }, [ret.result]);
              }
            };

            request.onerror = function(e) {
              var ret = e.target;
              errorCount++;
              if (completed + errorCount>=dataArray.length) {
                if ("function" == typeof callback)
                  callback(200, { errorCount: errorCount }, [{}]);
              }
            };
          }
          ret=true;
        }
        return ret;
      };

      that.filter = function(onitem, oncomplete, condition, haltOnFirst) {
        if (3==that.status) {
          haltOnFirst = false || haltOnFirst;

          var _filter_end = function(errCode) {
            if (errCode===0)
              if (typeof oncomplete == "function")
                oncomplete();
          };

          if (typeof onitem == "function") {
            condition = condition || true;
            var allValues=false;
            if (true===condition) {
              allValues=true;
            } else {
              var ylex = yLexObj(condition);
              ylex.parse();
            }

            var objectStore = that._newTransaction("readonly", undefined, _filter_end);
            var request = objectStore.openCursor();
            request.onsuccess = function (e) {
              var canCall, cursor = e.target.result;
              if (cursor) {
                canCall = allValues || ylex.solve(cursor.value);
                if (canCall) {
                  onitem(cursor.value);
                }
                if (!haltOnFirst)
                  cursor.continue();
              }
            };

            request.onerror = function() {

            };
          } else {
            _filter_end(0);
          }
        }
      };

      that.slice = function(onitem, oncomplete, first, last, haltOnFirst) {
        if (3==that.status) {
          haltOnFirst = false || haltOnFirst;

          var _filter_end = function(errCode) {
            if (errCode===0)
              if (typeof oncomplete == "function")
                oncomplete();
          };

          if (typeof onitem == "function") {

            var objectStore = that._newTransaction("readonly", undefined, _filter_end);
            var request = objectStore.openCursor();
            var p=0, count = 0;
            first = Math.max(0, first);
            request.onsuccess = function (e) {
              var cursor = e.target.result, jumpThis=false;
              if (cursor) {
                if (p<first) {
                  p=first;
                  console.log("Advancing to {0}".format(first));
                  if (first>0) {
                    cursor.advance(first);
                    jumpThis=true;
                  }
                } else {
                  p++;
                  if (p<=last) {
                    if ("function" === typeof onitem) {
                      if (!jumpThis)
                        onitem(cursor.value);
                    }

                    if (!haltOnFirst) {
                      if (p<last) {
                        cursor.continue();
                      }
                    }
                  }
                }
              }
            };

            request.onerror = function() {

            };
          } else {
            _filter_end(0);
          }
        }
      };

      that.removeItem = function(keyValue, callback) {
        var ret=false;
        if (3==that.status) {
          var objectStore = that._newTransaction("readonly", callback),
              request=objectStore.delete(keyValue);
          request.onsuccess = function () {
            if ("function" == typeof callback)
              callback(200, 0, request.result);
          };
          ret=true;
        }
        return ret;
      };

      that.onDBRequestError = function(event) {
        that._status = -1;
        console.error("yIndexedDB error trying to open '{0}'".format(dbTag));
      };

      that.onDBRequestSuccess = function(event) {
        that._status = 3;
        that._db = event.target.result;
        if ("function" == typeof onready_callback)
          onready_callback();
      };

      that.onDBUpgradeNeeded = function(event) {
        that._status = 2;
        that._db = event.target.result;
        that._objectStore = that._db.createObjectStore(that._dbTag_, {keyPath: that._keyName_[0]});
        for (var n=1; n<that._keyName_.length; n++) {
          that._objectStore.createIndex(that._keyName_[n]+'_ndx', that._keyName_[n], {unique: false});
        }
        that._status = 3;
      };

      that.init = function(dbTag, aKeyName) {
        that._dbTag_ = dbTag;
        /* prepare index key names */
        aKeyName = aKeyName || '_id';
        if (!isArray(aKeyName)) {
          var sep=(aKeyName.indexOf(",")>=0)?',':';';
          aKeyName = aKeyName.split(sep);
        }
        that._keyName_ = aKeyName.slice();
        if (that._keyName_.length===0)
          that._keyName_[0]='_id';

        /* status */
        that._status = 0;
        Object.defineProperty(
          that,
          'status',
          {
            get: function() { return that._status; },
            configurable: false,
            enumerable: false
          }
        );

        if (window.indexedDB) {
          that._status = 1;
          /* request an indexedDB instance */
          that._DBOpenRequest = window.indexedDB.open(dbTag, 1);
          that._DBOpenRequest.onerror         = that.onDBRequestError;
          that._DBOpenRequest.onsuccess       = that.onDBRequestSuccess;
          that._DBOpenRequest.onupgradeneeded = that.onDBUpgradeNeeded;
        }

        return that;
      };

      return that.init(dbTag, aKeyName);
    };
  }

  /*
  yStorage -> ySingleDb + ycomm.crave  = infoObj
  */

  if (!window.yServerWatcherObj) {
    window.yServerWatcherObj = function(server) {
      var that = {};

      that.serverReady = function(onSuccess, onError) {
        var localTS1 = new Date().getTime();
        ycomm.crave(
          "sync",
          "ping",
          null,
          function(status, error, data) {
            if (status == 200) {
              if (parseInt(data.pong || 0) > 0) {
                var localTS2 = new Date().getTime(),
                  wastedTime = localTS2 - localTS1;
                console.log("Server ready...");
                console.log("Wasted time: {0}ms".format(wastedTime));

                ycomm.wd_interval = wastedTime * 4;

                if (typeof onSuccess == "function")
                  onSuccess();
              }
            } else {
              if (typeof onError == "function") {
                onError(status, error);
              }
            }
          }
        );
      };

      that.init = function() {
        ycomm.setDataLocation(server);
        that.serverReady(function() { console.log("Server ready!"); });
        return that;
      };

      return that.init();
    };
  }

  if (!window.yInfoObj) {
    /* prior to 0.8.55 the parameter sequence was: (restServer, aDBName, aKeyName, aDataTemplate)
       on 0.8.55 it expected (aDBSpec, aServerSpec)
           aDBSpec { dbName: string,
                     keyName: string,
                     dataTemplate: string }

           aServerSpec { restServer: string,
                         projectId: string,
                         deviceId: string,
                         deviceKey: string }
     */
    window.yInfoObj = function(aDBSpec, aServerSpec) {
      var that = {};

      if ((typeof aDBSpec == "object") && (typeof aDBSpec.dbName=="string")) {

        that.cfg = {
          db: ySingleDb(aDBSpec.dbName, aDBSpec.keyName),
          garbage: ySingleDb(aDBSpec.dbName + "_garbage", aDBSpec.keyName),
          dbName: aDBSpec.dbName,
          keyName: aDBSpec.keyName,
          dataModified: 0,
          onrecordcount: null,
          onprogress: null,
          oncomplete: null
        };

        that.onremove = function(item) {
          item._ts_deletion = (new Date()).getTime() / 1000;
          that.cfg.garbage.setItem(item._id, item);
          that.cfg.dataModified++;
        };

        that.isbusy = function() {
          return that.busy;
        };

        that.getItem = function(itemNdx) {
          return that.cfg.db.getItem(itemNdx);
        };

        that.setItem = function(itemNdx, itemJData, aFieldsToPreserve) {
          that.cfg.dataModified++;
          return that.cfg.db.setItem(itemNdx, itemJData, aFieldsToPreserve);
        };

        that.removeItem = function(itemNdx) {
          return that.cfg.db.removeItem(itemNdx);
        };

        that.removeItems = function(condition) {
          return that.cfg.db.removeItems(condition);
        };

        that.eliminateItem = function(itemNdx) {
          return that.cfg.db.eliminateItem(itemNdx);
        };

        that.eliminateItems = function(condition) {
          return that.cfg.db.eliminateItems(condition);
        };

        that.cleanList = function() {
          return that.cfg.db.cleanList();
        };

        that.blankList = function() {
          return that.cfg.db.blankList();
        };

        that.linkTo = function(aSiblingDB, aLinkageField) {
          return that.cfg.db.linkTo(aSiblingDB, aLinkageField);
        };

        that.filter = function(onitem, oncomplete, condition, haltOnFirst) {
          return that.cfg.db.filter(onitem, oncomplete, condition, haltOnFirst);
        };

        that.each = function(onitem, oncomplete, condition, haltOnFirst) {
          return that.cfg.db.each(onitem, oncomplete, condition, haltOnFirst);
        };

        that.paint = that.each;

        that.count = function(condition) {
          return that.cfg.db.count(condition);
        };

        that.extractData = function(oncomplete, condition) {
          var data = [];
          var onItem = function(item) {
            data[data.length] = item;
          };
          var atEnd = function() {
            oncomplete(data);
          };
          that.each(onItem, atEnd, condition);
        };

        that.insertData = function(data, aFieldsToPreserve) {
          return that.cfg.db.insertData(data, aFieldsToPreserve);
        };

        that.cleanCondition = function() {
          that.cfg.condition = {};
        };

        that.setCondition = function(aCondition) {
          if (typeof aCondition == 'string') {
            var tokens = aCondition.match(/\S+/g),
              n, myCondition = '';
            for (n = 0; n < tokens.length; n++) {
              if (!isNumber(tokens[n])) {
                if (!isOperator(tokens[n]))
                  tokens[n] = "%(" + tokens[n] + ")";
              }
              myCondition += tokens[n] + ' ';
            }
            that.cfg.condition = { _statement_: myCondition };

          } else
            that.cfg.condition = aCondition || {};
        };

        that.retrieveFromServer = function(aonrecordcount, aoncomplete, aonprogress) {
          var _retrieveFromServer = function() {
            var condition = {
                xq_start: that.cfg.xq_start,
                xq_collectionName: that.cfg.dbName
              },
              t1 = (new Date()).getTime();
            mergeObject(that.cfg.condition, condition);

            ycomm.crave(
              "sync",
              "getDocumentInSequence", /* @TO-DO*/
              condition,
              function(status, error, data) {
                if (data.length > 0) {
                  that.cfg.interleave.adjustRestTime(t1);

                  for (var i = 0; i < data.length; i++) {
                    that.setItem(data[i][that.cfg.keyName], data[i]);
                    if (typeof that.cfg.onprogress == 'function')
                      that.cfg.onprogress(that.cfg.dbName, data[i], that.cfg.xq_start + i);
                  }
                  that.cfg.xq_start += data.length;

                  setTimeout(_retrieveFromServer, that.cfg.interleave.restTime);
                } else {
                  if (typeof that.cfg.oncomplete == 'function')
                    that.cfg.oncomplete(that.cfg.dbName);
                  that.busy = false;
                }
              }
            );
          };

          if (!that.busy) {
            that.busy = true;
            that.cfg.onrecordcount = aonrecordcount || null;
            that.cfg.oncomplete = aoncomplete || null;
            that.cfg.onprogress = aonprogress || null;

            that.server.serverReady(
              function() {
                ycomm.crave(
                  "sync",
                  "getRecordCount", /* @TO-DO*/
                  that.cfg.condition,
                  function(satus, error, data) {
                    data = data || [];
                    data[0] = data[0] || {};
                    if (typeof that.cfg.onrecordcount == 'function')
                      that.cfg.onrecordcount(that.cfg.dbName, parseInt(data[0].CC || 0));
                    that.cfg.xq_start = 0;
                    _retrieveFromServer();
                  }
                );

              },
              function(status, error) {
                console.log("Erro {0} ao tentar acessar o servidor: {1}".format(status, error.message));
              }
            );
            return true;
          } else {
            console.warn("yInfoObj busy");
            return false;
          }
        };

        that.templatedData = function(data) {
          var ret;
          if (!that.cfg.dataTemplateEmpty) {
            ret = {};
            for (var i in that.cfg.dataTemplate) {
              if (that.cfg.dataTemplate.hasOwnProperty(i))
                ret[i] = data[i];
            }
          } else
            ret = data;
          return ret;
        };

        that.sendToServer = function(aoncomplete, aonprogress, aonerror) {
          aonerror = aonerror || console.warn;

          if (!that.busy) {
            that.busy = true;
            that.cfg.onrecordcount = null;
            that.cfg.oncomplete = aoncomplete || null;
            that.cfg.onprogress = aonprogress || null;

            var recordCount = 0;

            that.server.serverReady(
              function() {
                that.each(
                  function(d) {
                    var callContext = that.templatedData(d);

                    d._ts_upload = (new Date(d)).getTime() / 1000;

                    mergeObject(that.cfg.condition, callContext);
                    ycomm.crave(
                      "sync",
                      "setDocument", /* @TO-DO*/
                      callContext,
                      function(status, error, data) {
                        if (status == 200) {
                          if (typeof that.cfg.onprogress == 'function') {
                            that.cfg.onprogress(that.cfg.dbName, data[0], ++recordCount);
                          }
                        } else {
                          aonerror(status+': '+error);
                        }
                      }
                    );
                  },
                  function() {
                    if (typeof that.cfg.oncomplete == 'function')
                      that.cfg.oncomplete(that.cfg.dbName);
                    that.busy = false;
                  },
                  that.cfg.condition
                );

              },
              function(status, error) {
                aonerror("Erro {0} ao tentar acessar o servidor: {1}".format(status, error.message));
              }
            );

            return true;
          } else {
            aonerror("yInfoObj busy");
            return false;
          }
        };

        that.init = function(aDataTemplate) {
          /* interleave time */
          that.cfg.interleave = yRestTimeControl(500);

          /* data template */
          if (typeof aDataTemplate === "undefined") {
            that.cfg.dataTemplateEmpty = true;
          } else {
            that.cfg.dataTemplateEmpty = false;
          }
          that.cfg.dataTemplate = aDataTemplate || {};

          /* garbage colector */
          that.cfg.db.onremove = that.onremove;

          /* data server */
          that.server = yServerWatcherObj(restServer);

          /* initial condition status */
          that.cleanCondition();

          return that;
        };

        return that.init(aDBSpec.dataTemplate);
      } else {
        var _msg =  "Since 0.8.55 the parameters or yInfoObj() has changed to (aDBSpec: {}, aServerSpec: {})";
        console.error(_msg);
        alert(_msg);
      }
    };
  }
})();
