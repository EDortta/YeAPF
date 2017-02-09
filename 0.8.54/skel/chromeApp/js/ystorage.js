/*********************************************
 * skel/chromeApp/js/ystorage.js
 * YeAPF 0.8.54-26 built on 2017-02-09 09:06 (-2 DST)
 * Copyright (C) 2004-2017 Esteban Daniel Dortta - dortta@yahoo.com
 * 2017-02-09 09:06:46 (-2 DST)
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
        oStorage = {};
      Object.defineProperty(oStorage, "getItem", {
        value: function(sKey) {
          return sKey ? this[sKey] : null; },
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
      this.get = function() {
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
      this.configurable = false;
      this.enumerable = true;
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

      that.setItem = function(id, jData) {
        id = String(id);

        jData._id = jData._id || generateUUID();
        jData._ts_update = (new Date()).getTime() / 1000;

        if (jData._linked) {
          for(var i=0; i<jData._linked.length; i++) {
            delete jData[jData._linked[i]];
          }
          delete jData._linked;
        }

        for(var k in jData) {
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
      that.setBinItem = function(id, bData) {
        that.setItem(id, that.toBinString(bData));
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

      that.filter = function(onitem, oncomplete, condition) {
        if (typeof onitem == "function") {
          condition = condition || true;
          var ylex = yLexObj(condition);
          ylex.parse();
          var i, lista = that.getList(),
            item, canCall;
          for (i = 0; i < lista.length; i++) {
            item = that.getItem(lista[i]);
            canCall = ylex.solve(item);
            if (canCall) {
              onitem(item);
            }
          }
        }
        if (typeof oncomplete == "function")
          oncomplete();
      };

      that.each = function(onitem, oncomplete, condition) {
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
            if (canCall)
              onitem(item);
          }
        }
        if (typeof oncomplete == "function")
          oncomplete();
      };

      that.insertData = function(data) {
        for(var i in data) {
          if (data.hasOwnProperty(i)) {
            that.setItem(data[i][that._keyName_], data[i]);
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

      that.fillList = function(data, idFieldName, clean) {
        idFieldName = idFieldName || 'id';
        clean = clean || false;

        if (clean)
          that.cleanList();


        for (var i = 0; i < data.length; i++) {
          if (data[i][idFieldName]) {
            that.setItem(data[i][idFieldName], data);
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
    window.yInfoObj = function(restServer, aDBName, aKeyName, aDataTemplate) {
      var that = {};

      that.cfg = {
        db: ySingleDb(aDBName, aKeyName),
        garbage: ySingleDb(aDBName + "_garbage", aKeyName),
        dbName: aDBName,
        keyName: aKeyName,
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

      that.setItem = function(itemNdx, itemJData) {
        that.cfg.dataModified++;
        return that.cfg.db.setItem(itemNdx, itemJData);
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

      that.filter = function(onitem, oncomplete, condition) {
        return that.cfg.db.filter(onitem, oncomplete, condition);
      };

      that.each = function(onitem, oncomplete, condition) {
        return that.cfg.db.each(onitem, oncomplete, condition);
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

      that.insertData = function(data) {
        return that.cfg.db.insertData(data);
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

      that._retrieveFromServer = function() {
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

              setTimeout(that._retrieveFromServer, that.cfg.interleave.restTime);
            } else {
              if (typeof that.cfg.oncomplete == 'function')
                that.cfg.oncomplete(that.cfg.dbName);
              that.busy = false;
            }
          }
        );
      };

      that.retrieveFromServer = function(aonrecordcount, aoncomplete, aonprogress) {
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
                  that._retrieveFromServer();
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

      that.sendToServer = function(aoncomplete, aonprogress) {
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
              console.log("Erro {0} ao tentar acessar o servidor: {1}".format(status, error.message));
            }
          );

          return true;
        } else {
          console.warn("yInfoObj busy");
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

      return that.init(aDataTemplate);
    };
  }
})();
