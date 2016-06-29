/*********************************************
 * app-src/js/ystorage.js
 * YeAPF 0.8.49-42 built on 2016-06-29 07:25 (-3 DST)
 * Copyright (C) 2004-2016 Esteban Daniel Dortta - dortta@yahoo.com
 * 2016-06-29 07:25:20 (-3 DST)
 * First Version (C) 2014 - esteban daniel dortta - dortta@yahoo.com
 *********************************************/
//# sourceURL=app-src/js/ystorage.js

if (!window.ystorage) {
  Object.defineProperty(window, "ystorage", new (function () {
    var aKeys = [], oStorage = {};
    Object.defineProperty(oStorage, "getItem", {
      value: function (sKey) { return sKey ? this[sKey] : null; },
      writable: false,
      configurable: false,
      enumerable: false
    });
    Object.defineProperty(oStorage, "key", {
      value: function (nKeyId) { return aKeys[nKeyId]; },
      writable: false,
      configurable: false,
      enumerable: false
    });
    Object.defineProperty(oStorage, "setItem", {
      value: function (sKey, sValue) {
        if(!sKey) { return; }
        document.cookie = escape(sKey) + "=" + escape(sValue) + "; expires=Tue, 19 Jan 2038 03:14:07 GMT; path=/";
      },
      writable: false,
      configurable: false,
      enumerable: false
    });
    Object.defineProperty(oStorage, "length", {
      get: function () { return aKeys.length; },
      configurable: false,
      enumerable: false
    });
    Object.defineProperty(oStorage, "removeItem", {
      value: function (sKey) {
        if(!sKey) { return; }
        document.cookie = escape(sKey) + "=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/";
      },
      writable: false,
      configurable: false,
      enumerable: false
    });
    this.get = function () {
      var iThisIndx;
      for (var sKey in oStorage) {
        iThisIndx = aKeys.indexOf(sKey);
        if (iThisIndx === -1) { oStorage.setItem(sKey, oStorage[sKey]); }
        else { aKeys.splice(iThisIndx, 1); }
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
  })());
}

if (!window.ySingleDb) {
  console.log("Creating ySingleDb");
  window.ySingleDb = function (dbTag) {
    var that={};
    that.dbAux = window.localStorage;

    that.setItem = function(id, jData) {
      localStorage.setItem(that._dbTag_+"_item_"+id, JSON.stringify(jData));
      var lista=that.getList();
      if (lista.indexOf(id)==-1) {
        lista[lista.length]=String(id);
        localStorage.setItem(that._dbTag_+"_list", JSON.stringify(lista));
      }      
    };

    that.getItem = function(id) {
      var ret=localStorage.getItem(that._dbTag_+"_item_"+id);
      ret=JSON.parse(ret);
      return ret;
    };

    that.getList = function () {
      var lista=localStorage.getItem(that._dbTag_+"_list");
      if (typeof lista=="string")
        lista=((JSON) && JSON.parse(lista)) || [];
      lista=lista || [];
      return lista;
    };

    that.removeItem = function(id) {
      localStorage.removeItem(that._dbTag_+"_item_"+id);
      var lista=that.getList();
      var ndx=lista.indexOf(String(id));
      if (ndx>-1) {
        lista.splice(ndx,1);
        localStorage.setItem(that._dbTag_+"_list", JSON.stringify(lista));
      }
    };

    that.init = function(dbTag) {
      that._dbTag_=dbTag;
      return that;
    };

    return that.init(dbTag);
  };
}