/*********************************************
 * skel/chromeApp/js/ystorage.js
 * YeAPF 0.8.49-114 built on 2016-08-12 16:10 (-3 DST)
 * Copyright (C) 2004-2016 Esteban Daniel Dortta - dortta@yahoo.com
 * 2016-08-12 16:10:39 (-3 DST)
 * First Version (C) 2014 - esteban daniel dortta - dortta@yahoo.com
 *********************************************/
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
  console.log("Creating ySingleDb ... ");
  window.ySingleDb = function (dbTag) {
    var that={};
    that._list = null;

    that.toBinArray = function (str) {
      var l = (str || '').length,
              arr = new Uint8Array(l);
      for (var i=0; i<l; i++) arr[i] = str.charCodeAt(i);
      return arr;
    };

    that.toBinString = function (arr) {
        var uarr = new Uint8Array(arr);
        var strings = [], chunksize = 0xffff;
        // There is a maximum stack size. We cannot call String.fromCharCode with as many arguments as we want
        for (var i=0; i*chunksize < uarr.length; i++){
            strings.push(String.fromCharCode.apply(null, uarr.subarray(i*chunksize, (i+1)*chunksize)));
        }
        return strings.join('');
    };

    that.setItem = function(id, jData) {
      id=String(id);
      localStorage.setItem(that._dbTag_+"_item_"+id, JSON.stringify(jData));      
      if (that._list.indexOf(id)==-1) {
        that._list[that._list.length]=id;
        localStorage.setItem(that._dbTag_+"_list", JSON.stringify(that._list));
      }      
    };

    /* receives a Uint8Array */
    that.setBinItem = function(id, bData) {
      that.setItem(id, that.toBinString(bData));
    };

    that.getItem = function(id) {
      id=String(id);
      var ret=localStorage.getItem(that._dbTag_+"_item_"+id);
      ret=JSON.parse(ret || "{}");
      return ret;
    };

    that.getBinItem = function(id) {
      return that.toBinArray(that.getItem(id));
    };

    that.getList = function () {
      return that._list || [];
    };

    that.fillList = function(data, idFieldName, clean) {
      idFieldName=idFieldName || 'id';
      clean=clean || false;

      if (clean) 
        that.cleanList();
      

      for(var i=0; i<data.length; i++) {
        if (data[i][idFieldName]) {
          that.setItem(data[i][idFieldName], data);
        }
      }
    };

    that.removeItem = function(id) {
      localStorage.removeItem(that._dbTag_+"_item_"+id);      
      var ndx=that._list.indexOf(String(id));
      if (ndx>-1) {
        that._list.splice(ndx,1);
        localStorage.setItem(that._dbTag_+"_list", JSON.stringify(that._list));
      }
    };

    that.cleanList = function() {
      for(var i in that._list) {
        id=that._list[i];
        that.removeItem(id);
      }      
    };

    that.init = function(dbTag) {
      that._dbTag_=dbTag;
      console.log("ystorage: creating "+dbTag);

      that._list=localStorage.getItem(that._dbTag_+"_list");
      if (typeof that._list=="string")
        that._list=((JSON) && JSON.parse(that._list)) || [];
      that._list=that._list || [];

      return that;
    };

    return that.init(dbTag);
  };
  console.log("ystorage ready!");
}