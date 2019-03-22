/*********************************************
 * app-src/js/ycfgdb.js
 * YeAPF 0.8.62-2 built on 2019-03-22 10:21 (-3 DST)
 * Copyright (C) 2004-2019 Esteban Daniel Dortta - dortta@yahoo.com
 * 2018-05-30 11:21:04 (-3 DST)
 * First Version (C) 2014 - esteban daniel dortta - dortta@yahoo.com
**********************************************/
//# sourceURL=app-src/js/ycfgdb.js

var cfgDBbase = function () {
  var that = {};

  that.getConnParams = function () {
    var ret = [];

    ret['server'] = ystorage.getItem('server');
    ret['user'] = ystorage.getItem('user');
    ret['password'] = ystorage.getItem('password');
    ret['token'] = ystorage.getItem('token');
    return ret;
  }

  that.setConnParams = function (aParams) {
    ystorage.setItem('server'  , aParams['server']);
    ystorage.setItem('user'    , aParams['user']);
    ystorage.setItem('password', aParams['password']);
    ystorage.setItem('token'   , aParams['token']);
  }

  return that;
}

var cfgDB = cfgDBbase();
