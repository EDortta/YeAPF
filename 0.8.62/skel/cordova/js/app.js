var appBase = function () {
  that= {};

  that.whatchdog = null;
  that.initialized = false;
  that.desktopApp = null;

  that.initialize = function () {
    document.addEventListener('deviceready', that.onDeviceReady, false);
    that.whatchdog = setTimeout(function() { app.onDeviceReady(true) }, 6000);
    return that;
  }

  that.onDeviceReady = function (aDesktop) {
    aDesktop = aDesktop===true;
    clearTimeout(app.whatchdog);
    if (!app.initialized) {
      app.initialized = true;
      mTabNav.init();
      app.desktopApp=aDesktop;
      if (aDesktop) {
      } else {
        document.addEventListener('backbutton', app.onBackButton, false);
      }

      mTabNav.showTab('vwMonitor');
    }
  }

  that.queryMonitor = function() {

  }

  return that;

};

var mainApp=appBase().initialize();
