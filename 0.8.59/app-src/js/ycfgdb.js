/*********************************************
 * app-src/js/ycfgdb.js
 * YeAPF 0.8.59-128 built on 2017-12-22 07:10 (-2 DST)
 * Copyright (C) 2004-2017 Esteban Daniel Dortta - dortta@yahoo.com
 * 2017-12-14 18:31:30 (-2 DST)
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
