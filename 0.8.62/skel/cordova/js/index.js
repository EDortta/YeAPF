var mobAppObj = function() {
  var that={};


  that.onDeviceReady = function() {
    /* Your Events goes here */
  };

  that.init = function() {
    document.addEventListener('deviceready', that.onDeviceReady, false);
    return that;
  };

  return that.init();
}

addOnLoadManager(function() {window.app=mobAppObj()});