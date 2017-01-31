/*********************************************
 * app-src/js/ycfgdb.js
 * YeAPF 0.8.54-1 built on 2017-01-31 11:51 (-2 DST)
 * Copyright (C) 2004-2017 Esteban Daniel Dortta - dortta@yahoo.com
 * 2016-01-23 22:00:19 (-2 DST)
 * First Version (C) 2014 - esteban daniel dortta - dortta@yahoo.com
**********************************************/
//# sourceURL=app-src/js/ycfgdb.js

var cfgDBbase = function () {
  that = {};

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
