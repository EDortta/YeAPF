app#(scriptName)Obj = function () {
  var dummy={ initialized: false, s: '#(scriptName)', rf: ycomm.invoke };
  var that=typeof appBase=="function"?appBase(dummy):dummy;

  that.init = function () {
    if (!that.initialized) {
      that.initialized=1;
      if (typeof appBase !='function')
        console.warn("Are you sure you want to leave 'appbase.js' behind?")
      /*************************************
       * Your initialization code goes here
       *************************************/

      that.initialized=2;
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