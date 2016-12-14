app#(scriptName)Obj = function () {
  var dummy={ initialized: false, s: '#(scriptName)' };
  var that=typeof appBase=="object"?appBase(dummy):dummy;

  that.init = function () {
    if (!that.initialized) {
      that.initialized=true;
      /*************************************
       * Your initialization code goes here
       *************************************/

    }
    return that;
  }

  return that.init();
}

if (typeof appBase === "undefined")
  yloader.loadLibrary("appbase.js");


var app#(scriptName)=null;
addOnLoadManager(
  function() {
    app#(scriptName)=app#(scriptName)Obj();
  }
);