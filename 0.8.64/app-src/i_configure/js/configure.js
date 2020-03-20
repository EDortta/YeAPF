var configAppObj = function () {
  var that={};

  that.comm=function(a, callback, params) {
    params=params || {};

    ycomm.invoke(
      "config",
      a,
      params,
      function(status, error, data) {
        if (typeof callback == 'function') 
          callback(data);
      }
    );
  };

  that._lastMenu=null;

  that.openMenu = function(e) {
    e = e.target;
    var menuId=e.id, tabId='vw'+menuId.substr(4);
    if (that._lastMenu)
      that._lastMenu.deleteClass('active');
    that._lastMenu=y$(menuId).parentNode;
    mTabNav.showTab(tabId);
    that._lastMenu.addClass('active');
  };

  that.refreshAppRegistry = function () {
    that.comm(
      'refreshAppRegistry',       
      function(dados) {
        if (dados && dados[0]) {
          y$('appCfg_appRegistry').value=dados[0].appRegistry;
        }
      }
    );
  };

  that.init=function() {
    that.comm('ping');
    addEvent("menuAppConfig",         "click", that.openMenu);
    addEvent("menuSecurityConfig",    "click", that.openMenu);
    addEvent("menuLogs",              "click", that.openMenu);
    addEvent("menuAbout",             "click", that.openMenu);

    addEvent("btnRefreshAppRegistry", "click", that.refreshAppRegistry)
    return that;
  };

  return that.init();
}

var configApp;
addOnLoadManager(
  function() {
    configApp = configAppObj();
  }
)