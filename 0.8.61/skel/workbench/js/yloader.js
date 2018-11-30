/*********************************************
  * skel/workbench/js/yloader.js
  * YeAPF 0.8.61-153 built on 2018-11-30 07:00 (-2 DST)
  * Copyright (C) 2004-2018 Esteban Daniel Dortta - dortta@yahoo.com
  * 2018-11-30 07:00:56 (-2 DST)
  * First Version (C) 2014 - esteban daniel dortta - dortta@yahoo.com
  * Purpose:  Build a monolitic YeAPF script so
  *           it can be loaded at once
 **********************************************/
 if (typeof console === 'undefined')
   if (typeof window != 'undefined')
     var console = (window.console = window.console || {});
   else
     var console = {};
 (
   function () {
     var methods = [
       'assert', 'clear', 'count', 'debug', 'dir', 'dirxml', 'error',
       'exception', 'group', 'groupCollapsed', 'groupEnd', 'info', 'log',
       'markTimeline', 'profile', 'profileEnd', 'table', 'time', 'timeEnd',
       'timeStamp', 'trace', 'warn'
     ], method;
     for (var i=0; i<methods.length; i++) {
       method = methods[i];
       if (!console[method]) console[method] = function () {};
     }
   }
 )();
 console.log("YeAPF 0.8.61-153 built on 2018-11-30 07:00 (-2 DST)");
 /* START yopcontext.js */
     /***********************************************************************
      * First Version (C) 2014 - esteban daniel dortta - dortta@yahoo.com
      *
      * This is a set of function that helps to recognize operational context
      **********************************************************************/
     
     
     function getInternetExplorerVersion() {
     /* http://msdn.microsoft.com/en-us/library/ms537509(v=vs.85).aspx
      * Returns the version of Internet Explorer or a -1
      * (indicating the use of another browser).
      */
     
       var rv = -1; // Return value assumes failure.
       if (navigator.appName == 'Microsoft Internet Explorer')
       {
         var ua = navigator.userAgent;
         var re  = new RegExp("MSIE ([0-9]{1,}[\.0-9]{0,})");
         if (re.exec(ua) != null)
           rv = parseFloat( RegExp.$1 );
       }
       return rv;
     }
     
     function isInternetExplorer() {
       return (getInternetExplorerVersion() >= 0);
     };
     
     function IsSafari() {
       var is_safari = navigator.userAgent.toLowerCase().indexOf('safari/') > -1;
       return is_safari;
     
     }
     
     function getAndroidVersion(ua) {
         ua = (ua || navigator.userAgent).toLowerCase(); 
         var match = ua.match(/android\s([0-9\.]*)/);
         return match ? match[1] : false;
     };
     
     function isOnMobile() {
       var ret=false;
       if (typeof mosync != 'undefined') {
         ret = mosync.isAndroid || mosync.isIOS || mosync.isWindowsPhone;
       } else
         ret=/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
       return ret;
     };
     
     
 /* END yopcontext.js */
 /* START ydebug.js */
     /*********************************************
      * First Version (C) 2014 - esteban daniel dortta - dortta@yahoo.com
     **********************************************/
     
     
     ydbg = function() {
       var that = {};
       that.logLevel = 0;
       that.logFlag = 0;
       return that;
     };
     
     function __dump__(aLineConsole) {
       if (typeof mosync == "object")
         mosync.rlog(aLineConsole);
       else
         console.log(aLineConsole);
     
     }
     
     // This functions can be used with mosync or browser
     // and replaces console.log() / mosync.rlog()
     function _dump() {
       var aLine = '',
           aLineConsole,
           aAnArgument;
       for (var i=0; i<arguments.length; i++) {
         if (aLine>'')
           aLine+=', ';
         aAnArgument = arguments[i];
         if (typeof aAnArgument=="object")
           aAnArgument = aAnArgument.toString();
     
         aLine += aAnArgument;
       }
       aLineConsole = /* arguments.callee.caller.name+': '+*/aLine;
     
       __dump__(aLine);
       /* OBSOLETO 2016-02-24
       if ((typeof jsDumpEnabled != "undefined") && (jsDumpEnabled)) {
         aLine = '<b>'+arguments.callee.caller.name+'</b> <small>'+aLine+'</small>';
     
         var mainBody=__getMainBody();
         var isReady = (typeof mainBody.$ == 'function') && (mainBody.document.body != null);
         if (isReady) {
           var debug = mainBody.y$('debug');
           if (!debug) {
             debug = mainBody.document.createElement('div');
             debug.id='debug';
             debug.className='debug';
             setOpacity(debug,60);
             debug.onmouseover = __debugMouseOver;
             debug.onmouseout = __debugMouseOut;
             mainBody.document.body.appendChild(debug);
           }
           if (debug.innerHTML=='')
             debug.innerHTML = '<b>YeAPF!</b>';
           else {
             var auxText = debug.innerHTML;
             auxText = auxText.split('<br>');
             auxText = auxText.splice(Math.max(0,auxText.length-50));
             auxText = auxText.join('<br>');
             debug.innerHTML = auxText;
           }
           debug.innerHTML += '<br>'+aLine;
           debug.scrollTop = debug.scrollHeight;
         } else {
           aLine = aLine.replace('<small>','\n    ');
           aLine = aLine.replace('</small>','\n');
           aLine = aLine.replace('<b>','');
           aLine = aLine.replace('</b>','');
           alert(aLine);
         }
       }
       */
     }
     
     function _setLogFlagLevel(aLogFlag, aLevel) {
       ydbg.logFlag=aLogFlag;
       ydbg.logLevel=aLevel;
     }
     
     function _dumpy(logFlag, logLevel) {
       if (ydbg.logFlag & logFlag) {
         if (ydbg.logLevel>=logLevel) {
           var aLine = '', aAnArgument, ts, d=new Date();
           ts=d.getHours()+':'+d.getMinutes()+':'+d.getSeconds()+' ';
           for (var i=2; i<arguments.length; i++) {
             if (aLine>'')
               aLine+=', ';
             aAnArgument = arguments[i];
             if (typeof aAnArgument=="object")
               aAnArgument = aAnArgument.toString();
             aLine += aAnArgument;
           }
           __dump__(ts+aLine);
         }
       }
     
     }
     
 /* END ydebug.js */
 var yloaderBase = function () {
   var that = {};
   (function () {
     var mydir;
     if (typeof document=='object') {
       var scripts= document.getElementsByTagName('script');
       var path= scripts[scripts.length-1].src.split('?')[0];
       mydir= path.split('/').slice(0, -1).join('/')+'/';
     } else
       mydir='./';
     that.selfLocation = mydir;
     _dump("Loading from "+mydir);
   })();
   that.isWorker = (typeof importScripts == 'function');
   that.isMobile = (!that.isWorker) && (typeof window.orientation !== 'undefined');
   that.isChromeExtension = (!that.isWorker) && ((window.chrome && chrome.runtime && chrome.runtime.id) || (that.selfLocation.substr(0,17)=='chrome-extension:'));
   that.isChromeSandbox = (!that.isWorker) && ((that.isChromeExtension) && !(chrome.storage));
   that.isWebkit = /(safari|chrome)/.test(navigator.userAgent.toLowerCase());
   that.webKitVersion = (
     function() {
       var ret="530.00";
       var agentIds = navigator.userAgent.match(/([A-Za-z]*)\/([0-9A-Za-z\.]*)/g);
       for(var i=0; i<agentIds.length; i++) {
         if (agentIds[i].substr(0,11)=='AppleWebKit') {
           ret = agentIds[i].substr(12);
         }
       }
       return ret;
     }
   )();
   that.operatingSystem = (
     function() {
       var agentIds = navigator.userAgent.match(/(\([A-z\ 0-9\.\;\,\-\/\:]*)\)/g);
       var OSes=(agentIds[0] || '').replace(/[^a-zA-Z0-9\ \.\;]+/g, "").split(";") || ["Unknown", "Unknown"];
       for(var i=0; i<OSes.length; i++) {
         OSes[i]=trim(OSes[i] || "Unknown");
       }
       return OSes;
     }
   )();
   that.loadLibrary = function (jsFileName, elementId, onload) {
     var libFileName;
     if (jsFileName>'') {
       if (that.selfLocation.substr(0,17)!='chrome-extension:')
         jsFileName = that.selfLocation+'/'+jsFileName+'?v=0.8.61';
       else {
         // chrome.runtime.id
         var auxChromeExtension = that.selfLocation.split('/');
         var auxLocation = '';
         for (var i=3; i<auxChromeExtension.length; i++) {
           if (auxChromeExtension[i]>'') {
             if (auxLocation>'')
               auxLocation+='/';
             auxLocation+=auxChromeExtension[i];
           }
         }
         jsFileName = auxLocation+'/'+jsFileName+'?v=0.8.34';
       }
       jsFileName = jsFileName.replace(/\/\//g,'\/');
       jsFileName = jsFileName.replace('http:/','http://');
       jsFileName = jsFileName.replace('https:/','https://');
       var auxName = jsFileName.split('/');
       if (auxName.length>0)
         libFileName = auxName[auxName.length-1];
       if (typeof importScripts == 'function')
         importScripts(jsFileName);
       else {
         var head  = document.getElementsByTagName('head')[0];
         if (jsFileName.indexOf('.css')<0) {
           var _script    =  document.createElement('script');
           _script.type   = (jsFileName.indexOf('.js')>0)?'text/javascript':'text/text';
           _script.onload = onload;
           _script.src    = jsFileName;
           head.appendChild(_script);
         } else {
           var _link    = document.createElement('link');
           _link.id     = elementId || libFileName;
           _link.rel    = 'stylesheet';
           _link.type   = 'text/css';
           _link.media  = 'all';
           _link.onload = onload;
           _link.href   = jsFileName;
           head.appendChild(_link);
         }
       }
       _dump(libFileName+' added');
     }
   };
   /*
   if (that.isMobile) {
     that.loadLibrary('wormhole.js');
     document.addEventListener(
       "backbutton",
       function() { mosync.app.exit(); },
       true);
   }
   */
   if (!that.isWorker) {
     window.addEventListener("load", function() {
       var elem = (document.compatMode === "CSS1Compat") ?
           document.documentElement :
           document.body;
       var appScreen=document.getElementById('screen');
       if (appScreen)
         appScreen.style.width = elem.clientWidth + 'px';
     });
   }
   return that;
 };
 _dump("R1");
 var yloader=yloaderBase();
 _dump("R2");
 /* START ymisc.js */
     /*********************************************
      * First Version (C) 2014 - esteban daniel dortta - dortta@yahoo.com
      *
      * Many of the prototypes extensions are based
      * on Douglas Crockford's JavaScript: The Good Parts
     **********************************************/
     
     
     /*
     /*
      * dom shortcut 'y$()'
      */
     
     ( function () {
       y$ = function (aElementId, aTagName, aIndex) {
         var ret=undefined, auxRet;
         if (("string" == typeof aElementId) && (aElementId>'')) {
           if (aElementId.substr(0,1)=='#') {
             if (aElementId.indexOf(' ')>0) {
               ret=document.querySelector(aElementId);
             } else {
               aElementId = aElementId.substr(1);
             }
           }
           if (!ret)
             ret = document.getElementById(aElementId);
      
           if (!ret) {
             ret = document.getElementsByName(aElementId);
             if (ret.length) {
               if (ret.length==1)
                 ret=ret[0];
               if (ret.length===0)
                 ret=undefined;
             } else {
               ret=undefined
             }
           }
           if (!ret) {
             /* search by classes */
             var c, className, classes = aElementId.split(' '), classesReturn = undefined, first=true;
             for(c=0; c<classes.length; c++) {
               className=trim(classes[c]);
               if (className.substr(0,1)=='.')
                 className = className.substr(1);
               auxRet = getElementsByClassName(document, '*', className);
               if (auxRet.length>0) {
                 if (first) {
                   classesReturn = auxRet;
                 } else {
                   first=false;
                   classesReturn = array_intersect(classesReturn || [], auxRet);
                 }
               }
             }
             ret=classesReturn;
           } else {
             if (typeof aTagName !== 'undefined') {
               aIndex = 0+aIndex;
               if (ret.getElementsByTagName) {
                 var innerElements=ret.getElementsByTagName(aTagName);
                 if (innerElements.length>0)
                   ret=innerElements[aIndex];
               }
             }
           }
         }
         return ret;
       };
     })();
     
     if (typeof $ =='undefined') $ = y$;
     
     
     if (typeof window == "object") {
       window.createDOMEvent = function(eventName) {
         var ret=null;
         if (isOnMobile()) {
           ret = document.createEvent('Event');
           ret.initEvent(eventName, true, true);
         } else {
           ret = new Event(eventName);
         }
         return ret;
       }
     }
     
     
     /*
      * $frame()
      * given a frame name by path it returns dom frame
      * i.e. if there is a frame named innerF inside an frame called mainFrame
      * that belongs to body, you can call $frame('/mainFrame/innerF')
      * But you can call it by reference
      * i.e. you is inside a frame called whateverF and you don't know
      * who si it main frame, you can call the main frame by $frame('../')
      * If you doesn't know the path, you can use $frameByName()
      * If you doesn't know the path nor the name, but at least a function name
      * you can use $frameOwner()
      */
     function $frame(frameName) {
       if (frameName.substr(0,2)=='./')
         frameName=frameName.substr(2);
     
       var rootFrame;
       if (frameName.substr(0,3)=='../') {
         rootFrame=parent;
         frameName=frameName.substr(3);
       } else if (frameName=='/') {
         frameName='';
         // rootFrame = this;
         rootFrame = top;
       } else if (frameName.substr(0,1)=='/') {
         rootFrame = top;
         frameName=frameName.substr(1);
       } else
         rootFrame=self;
     
       if (frameName>'') {
         var list=frameName.split('/');
     
         for(var n=0; n<list.length; n++)
           rootFrame=rootFrame.frames[list[n]];
       }
       return rootFrame;
     }
     
     function $frameByName(frameName) {
       _searchFrameByName_ = function(aRootFrame, frameName)
       {
         var aFrame=null;
         if (aRootFrame.frames) {
           for (var n=0; (aFrame===null) && (n<aRootFrame.frames.length); n++)
             if (aRootFrame.frames[n].name==frameName)
               aFrame=aRootFrame.frames[n];
             else
               aFrame=_searchFrameByName_(aRootFrame.frames[n], frameName);
         }
     
         return aFrame;
       };
     
       return _searchFrameByName_(top, frameName);
     }
     
     function $frameOwner(aName, aType) {
       _searchFunctionInFrame_ = function (aName, aType, f)
       {
         var ret;
         var aux="typeof f."+aName+"=='"+aType+"'";
     
         if (eval(aux)) {
           ret=f;
         } else {
           var n=0;
           if (f.frames)
             while ((n<f.frames.length) && (ret===undefined))
               ret = _searchFunctionInFrame_(aName, aType, f.frames[n++]);
         }
     
         return ret;
       };
     
       // alert("looking for "+aName+" as "+aType);
     
       var f=$frame('/');
     
       return _searchFunctionInFrame_(aName, aType, f);
     }
     
     function isSSL() {
       return (window.location.href.indexOf("https://")===0);
     }
     
     function urlValue(paramName) {
       var params = document.location.search.split('&'), ret=null, i, p;
       if (params.length>0)
         params[0]=params[0].split('?')[1];
       for(i=0; i<params.length; i++) {
         p=params[i].split('=');
         if (p[0]==paramName) {
           ret=p[1];
           break;
         }
       }
       return ret;
     }
     
     function produceWaitMsg(msg) {
       var feedbackCSS='<style type="text/css"><!--.yWarnBanner {  font-family: Georgia, "Times New Roman", Times, serif;  font-size: 16px;  font-style: normal; font-variant: normal; font-weight: normal;  text-transform: none; margin: 16px; padding: 8px; background-color: #DFEEF2;  border: 1px dotted #387589; line-height: 24px;}--></style>';
       var feedbackText = '<div class=yWarnBanner><img src="images/waitIcon.gif" height=18px>&nbsp;{0}&nbsp;</div>';
       var aux=feedbackCSS + feedbackText.format(msg);
       return aux;
     }
     
     function countCheckedElements(aClassOrElementName) {
       var elems = y$(aClassOrElementName);
       var ret = 0;
       if (elems) {
         if (!isArray(elems))
           elems = [ elems ];
         for(var i=0; i<elems.length; i++) {
           if (elems[i].checked)
             ret++;
         }
       }
       return ret;
     }
     
     /**********************************
      *  Base64 encode / decode
      *  http://www.webtoolkit.info/
      **********************************/
     
     var Base64 = {
     
         // private property
         _keyStr : "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",
     
         // public method for encoding
         encode : function (input) {
           var output = "";
           var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
           var i = 0;
     
           input = Base64._utf8_encode(input);
     
           while (i < input.length) {
     
             chr1 = input.charCodeAt(i++);
             chr2 = input.charCodeAt(i++);
             chr3 = input.charCodeAt(i++);
     
             enc1 = chr1 >> 2;
             enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
             enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
             enc4 = chr3 & 63;
     
             if (isNaN(chr2)) {
               enc3 = enc4 = 64;
             } else if (isNaN(chr3)) {
               enc4 = 64;
             }
     
             output = output +
             this._keyStr.charAt(enc1) + this._keyStr.charAt(enc2) +
             this._keyStr.charAt(enc3) + this._keyStr.charAt(enc4);
     
           }
     
           return output;
         },
     
         // public method for decoding
         decode : function (input) {
           var output = "";
           var chr1, chr2, chr3;
           var enc1, enc2, enc3, enc4;
           var i = 0;
     
           input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");
     
           while (i < input.length) {
     
             enc1 = this._keyStr.indexOf(input.charAt(i++));
             enc2 = this._keyStr.indexOf(input.charAt(i++));
             enc3 = this._keyStr.indexOf(input.charAt(i++));
             enc4 = this._keyStr.indexOf(input.charAt(i++));
     
             chr1 = (enc1 << 2) | (enc2 >> 4);
             chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
             chr3 = ((enc3 & 3) << 6) | enc4;
     
             output = output + String.fromCharCode(chr1);
     
             if (enc3 != 64) {
               output = output + String.fromCharCode(chr2);
             }
             if (enc4 != 64) {
               output = output + String.fromCharCode(chr3);
             }
     
           }
     
           output = Base64._utf8_decode(output);
     
           return output;
     
         },
     
         // private method for UTF-8 encoding
         _utf8_encode : function (string) {
           string = string.replace(/\r\n/g,"\n");
           var utftext = "";
     
           for (var n = 0; n < string.length; n++) {
     
             var c = string.charCodeAt(n);
     
             if (c < 128) {
               utftext += String.fromCharCode(c);
             }
             else if((c > 127) && (c < 2048)) {
               utftext += String.fromCharCode((c >> 6) | 192);
               utftext += String.fromCharCode((c & 63) | 128);
             }
             else {
               utftext += String.fromCharCode((c >> 12) | 224);
               utftext += String.fromCharCode(((c >> 6) & 63) | 128);
               utftext += String.fromCharCode((c & 63) | 128);
             }
     
           }
     
           return utftext;
         },
     
         // private method for UTF-8 decoding
         _utf8_decode : function (utftext) {
           var string = "";
           var i = 0;
           var c = c1 = c2 = 0;
     
           while ( i < utftext.length ) {
     
             c = utftext.charCodeAt(i);
     
             if (c < 128) {
               string += String.fromCharCode(c);
               i++;
             }
             else if((c > 191) && (c < 224)) {
               c2 = utftext.charCodeAt(i+1);
               string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
               i += 2;
             }
             else {
               c2 = utftext.charCodeAt(i+1);
               c3 = utftext.charCodeAt(i+2);
               string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
               i += 3;
             }
     
           }
     
           return string;
         }
     
     }
     
     /*
      * http://snipplr.com/view/1853/get-elements-by-attribute/
      */
     function getElementsByAttribute(oRootElem, strTagName, strAttributeName, strAttributeValue){
       console.log("getElementsByAttribute()");
       var arrElements = oRootElem.getElementsByTagName(strTagName);
       var arrReturnElements = [];
       var oAttributeValue = (typeof strAttributeValue != "undefined")? new RegExp("(^|\\s)" + strAttributeValue + "(\\s|$)", "i") : null;
       var oCurrent;
       var oAttribute;
       for(var i=0; i<arrElements.length; i++){
         oCurrent = arrElements[i];
         oAttribute = oCurrent.getAttribute && oCurrent.getAttribute(strAttributeName);
         if(typeof oAttribute == "string" && oAttribute.length > 0){
           if (typeof strAttributeValue == "undefined" || (oAttributeValue && oAttributeValue.test(oAttribute))) {
               arrReturnElements.push(oCurrent);
           }
         }
       }
       return arrReturnElements;
     }
     
     if (typeof getElementsByClassName=="undefined") {
       console.log("Using own 'getElementsByClassName()' function");
       function getElementsByClassName(oRootElem, strTagName, aClassName) {
         var arrElements = oRootElem.getElementsByTagName(strTagName);
         var arrReturnElements = [];
         var oCurrent;
         for(var i=0; i<arrElements.length; i++) {
           oCurrent = arrElements[i];
           if ((oCurrent) && (typeof oCurrent.hasClass == 'function'))
             if (oCurrent.hasClass(aClassName))
               arrReturnElements.push(oCurrent);
         }
         if (arrReturnElements===null)
           arrReturnElements=document.getElementsByClassName(aClassName);
         return arrReturnElements;
       }
     }
     
     function getStyleRuleValue(className, styleItemName) {
       /* original from http://stackoverflow.com/questions/6338217/get-a-css-value-with-javascript */
       className=className || '';
     
       for (var i = 0; i < document.styleSheets.length; i++) {
           var mysheet = document.styleSheets[i];
           var myrules = mysheet.cssRules ? mysheet.cssRules : mysheet.rules;
           for (var j = 0; j < myrules.length; j++) {
               if (myrules[j].selectorText && myrules[j].selectorText.toLowerCase() === className) {
                 if (typeof styleItemName=="string")
                   return myrules[j].style[styleItemName];
                 else
                   return myrules[j].style;
               }
           }
     
       }
     };
     
     function setStyleRuleValue(className, styleItemName, value) {
       /* original from http://stackoverflow.com/questions/6338217/get-a-css-value-with-javascript */
       className=className || '';
     
       for (var i = 0; i < document.styleSheets.length; i++) {
           var mysheet = document.styleSheets[i];
           var myrules = mysheet.cssRules ? mysheet.cssRules : mysheet.rules;
           for (var j = 0; j < myrules.length; j++) {
               if (myrules[j].selectorText && myrules[j].selectorText.toLowerCase() === className) {
                 if (typeof styleItemName=="string")
                   myrules[j].style[styleItemName] = value;
                 else
                   myrules[j].style = value;
               }
           }
     
       }
     };
     
     var getClientSize = function () {
       var auxDE = (document && document.documentElement)?document.documentElement:{clientWidth:800, clientHeight: 600};
       var auxW = (window)?window:{innerWidth: 800, innerHeight: 600};
       var w = Math.max(auxDE.clientWidth, auxW.innerWidth || 0);
       var h = Math.max(auxDE.clientHeight, auxW.innerHeight || 0);
       return [w, h];
     };
     
     if (typeof resizeIframe == 'undefined') {
       var resizeIframe = function (obj, objMargin) {
         objMargin = objMargin || {};
     
         objMargin.width = objMargin.width || 0;
         objMargin.height = objMargin.height || 0;
     
         var s1, s2, bestSize, onResize;
     
         s1 = screen.height;
         s2 = (obj.contentWindow || obj.contentDocument || obj).document.body.scrollHeight + 40 - objMargin.height;
         bestSize=Math.max(s1, s2);
         obj.style.height = bestSize + 'px';
     
         s1 = screen.width;
         s2 = (obj.contentWindow || obj.contentDocument || obj).document.body.scrollWidth + 40 - objMargin.width;
         bestSize=Math.max(s1, s2);
         obj.style.width = bestSize + 'px';
     
         onResize = obj.getAttribute('onResize');
         if (onResize) {
           eval(onResize);
         }
       };
     }
     
     if ("undefined" !== typeof Element) {
     
       if (!Element.prototype.matches)
           Element.prototype.matches = Element.prototype.msMatchesSelector || 
                                       Element.prototype.webkitMatchesSelector;
     
       if (!Element.prototype.closest)
           Element.prototype.closest = function(s) {
               var el = this;
               if (!document.documentElement.contains(el)) return null;
               do {
                   if (el.matches(s)) return el;
                   el = el.parentElement || el.parentNode;
               } while (el !== null); 
               return null;
           };
     
     }
     /*
      * HTMLElement prototype extensions
      */
     if ((typeof HTMLElement=="object") || (typeof HTMLElement=="function")) {
       HTMLElement.prototype.hasClass = function (aClassName) {
         var ret = false;
         if (this.className) {
           var asterisk = aClassName.indexOf('*');
           var aClasses = this.className.split(' ');
           for(var i = 0; i<aClasses.length; i++) {
             if (asterisk>=0) {
               if (aClasses[i].substr(0,asterisk)==aClassName.substr(0,asterisk))
                 ret=true;
             } else {
               if (aClasses[i] == aClassName)
                 ret = true;          
             }
           }
         }
         return ret;
       };
     
       HTMLElement.prototype.setOpacity = function(value) {
         value = Math.max(0, Math.min(value,100));
         this.style.opacity = value/100;
         this.style.filter = "alpha(opacity={0})".format(value);
       };
     
       HTMLElement.prototype.deleteClass = function(aClassName) {
         var aNewClasses='';
         var aClasses=this.className.split(' ');
         var aParamClasses = (aClassName || '').split(' ');
         for(var i = 0; i<aClasses.length; i++) {
           if (aParamClasses.indexOf(aClasses[i])<0) {
             aNewClasses = aNewClasses + ' ' + aClasses[i];
           }
         }
         this.className=trim(aNewClasses);
         return this;
       };
     
       HTMLElement.prototype.removeClass = HTMLElement.prototype.deleteClass;
     
       HTMLElement.prototype.addClass = function(aClassName) {
         var aClassModified = false;
         var aNewClasses=this.className;
         var aClasses=this.className.split(' ');
         var aParamClasses = (aClassName || '').split(' ');
         for(var i = 0; i<aParamClasses.length; i++) {
           if (aClasses.indexOf(aParamClasses[i])<0) {
             aNewClasses = aNewClasses + ' ' + aParamClasses[i];
             aClassModified = true;
           }
         }
         if (aClassModified)
           this.className = trim(aNewClasses);
         return this;
       };
     
       HTMLElement.prototype.siblings = function() {
         var buildChildrenList = function(aNode, aExceptionNode) {
           var ret = [];
           while (aNode) {
             if ((aNode != aExceptionNode) && (aNode.nodeType==1)) {
               ret.push(aNode);
             }
             aNode = aNode.nextSibling;
           }
           return ret;
         };
         return buildChildrenList(this.parentNode.firstChild, this);
       };
       if (!HTMLElement.prototype.getAttribute)
         HTMLElement.prototype.getAttribute = function (attributeName) {
           var ret='';
           for(var i=0; i<this.attributes.length; i++)
             if (this.attributes[i].name == attributeName)
               ret = attributes[i].value;
           return ret;
         };
     
       if (!HTMLElement.prototype.block)
         HTMLElement.prototype.block = function () {
           this.setAttribute('blocked','blocked');
           return this;
         };
     
       if (!HTMLElement.prototype.unblock)
         HTMLElement.prototype.unblock = function () {
           this.removeAttribute('blocked');
           return this;
         };
     
     
       if (!HTMLElement.prototype.isBlocked)
         HTMLElement.prototype.isBlocked = function () {
           var hasBlock = this.getAttribute('blocked');
           return ( (typeof hasBlock == 'string') &&
                    (hasBlock.toLowerCase()=='blocked'));
         };
     
       if (!HTMLElement.prototype.lock)
         HTMLElement.prototype.lock = function () {
           if (!this.isBlocked())
             this.readOnly = true;
           return this;
         };
     
       if (!HTMLElement.prototype.unlock)
         HTMLElement.prototype.unlock = function () {
           if (!this.isBlocked())
             this.readOnly = false;
           return this;
         };
     
       if (!HTMLElement.prototype.selected) {
         HTMLElement.prototype.selected = function () {
           var ret=this;
           if (typeof this.list == 'object') {
             var v1=trim(this.value), op=this.list.options;
             for(var i in op) {
               if (op.hasOwnProperty(i)) {
                 if (trim(op[i].innerHTML)==v1) {
                   ret=op[i];
                   break;
                 }
               }
             }
           }
           return ret;
         }
       }
     
     
     }
     
     if (!Array.prototype.forEach) {
       Array.prototype.forEach = function(fun /*, thisArg */)
       {
         "use strict";
     
         if (this === void 0 || this === null)
           throw new TypeError();
     
         var t = Object(this);
         var len = t.length >>> 0;
         if (typeof fun !== "function")
           throw new TypeError();
     
         var thisArg = arguments.length >= 2 ? arguments[1] : void 0;
         for (var i = 0; i < len; i++)
         {
           if (i in t)
             fun.call(thisArg, t[i], i, t);
         }
       };
     }
     
     if (!Array.prototype.unique) {
       Array.prototype.unique = function() {
         var a=this;
         return  a.filter(function(item, pos) {
             return a.indexOf(item) == pos;
         })
       }
     }
     
     /* Object extensions */
     var mergeObject = function (srcObj, trgObj, overwriteIfExists) {
       if (overwriteIfExists===undefined)
         overwriteIfExists=false;
       trgObj=trgObj || {};
       for (var i in srcObj)
         if (srcObj.hasOwnProperty(i)) {
           if ((undefined === trgObj[i]) || (overwriteIfExists))
             trgObj[i] = srcObj[i];
         }
     };
     
     
     function isPropertySupported(property)
     {
       return property in document.body.style;
     };
     
     function isEmpty(obj) {
         for(var prop in obj) {
             if(obj.hasOwnProperty(prop))
                 return false;
         }
         return true;
     }
     
     if (!String.prototype.asPhone) {
       String.prototype.asPhone = function() {
         var aux=this.replace(/\D+/g, ''), i;
         return aux.replace(/(\d{2,3})(\d{3})(\d{3})/, '$1-$2-$3');
       }
     }
     
     if (!String.prototype.abbreviate) {
       String.prototype.abbreviate=function(maxLength, prioritizeFirstName)
       {
         var addLastName = function() {
           if (p2>'') {
             if (name.length+lastname.length+p2.length+(name.length>0?1:0+lastname.length>0?1:0)<maxLength) {
               lastname=trim(p2+' '+lastname);
               changes++;
             } else if (name.length+lastname.length+(name.length>0?1:0+lastname.length>0?1:0+1)<maxLength) {
               lastname=trim(p2.substr(0,1)+'.'+lastname);
               changes++;
             }
           }
         };
     
         var addName = function() {
           if (p1>'') {
             if (name.length+lastname.length+p1.length+(name.length>0?1:0+lastname.length>0?1:0)<maxLength) {
               name=trim(name+' '+p1);
               changes++;
             } else {
               if (name.length+lastname.length+(name.length>0?1:0+lastname.length>0?1:0+1)<maxLength) {
                 name=trim(name+' '+p1.substr(0,1)+'.');
                 changes++;
               }
             }
           }
         };
     
         prioritizeFirstName = prioritizeFirstName || false;
         maxLength=str2int(maxLength);
         if (this.indexOf(' ')>0) {
           var ni,li,piece=this.toString(), name='', lastname='', p1, p2, changes;
           while (name.length+lastname.length+1<=maxLength) {
             piece=trim(piece);
             ni=piece.indexOf(' ');
             if (ni<0)
               ni=piece.length;
             li=piece.lastIndexOf(' ');
             p1=trim(piece.substr(0,ni));
             p2='';
     
             if (ni<li) {
               p2=trim(piece.substr(li));
               piece=trim(piece.substr(0,li));
             }
     
             piece=trim(piece.substr(ni));
             changes=0;
     
             if (prioritizeFirstName)
               addName();
             else
               addLastName();
     
             if (p1!=p2) {
               if (prioritizeFirstName)
                 addLastName();
               else
                 addName();
             }
             if (changes===0)
               break;
           }
           return trim(name+' '+lastname);
         } else
           return this.substr(0,maxLength);
       };
     }
     
     if (!String.prototype.ucFirst) {
       String.prototype.ucFirst=function()
       {
           return this.charAt(0).toUpperCase() + this.slice(1);
       };
     }
     
     if (!String.prototype.lcFirst) {
       String.prototype.lcFirst=function()
       {
           return this.charAt(0).toLowerCase() + this.slice(1);
       };
     }
     
     if (!String.prototype.stripTags) {
       String.prototype.stripTags=function() {
         return (this || '').replace(/<(?:.|\n)*?>/gm, '');
       }
     }
     
     if (!String.prototype.repeat) {
       String.prototype.repeat= function(n, aChar){
           n= n || 1;
           aChar=aChar||this;
           return Array(n+1).join(aChar);
       };
     }
     /* returns a quoted string if it is not a number
      * or a parsed float otherwise */
     if (!String.prototype.quoteString) {
       String.prototype.quoteString=function(emptyAsNull)
       {
         if (emptyAsNull===undefined)
           emptyAsNull=false;
         var aux=this.valueOf();
         if (!isNumber(aux)) {
           if ((emptyAsNull) && (aux===''))
             aux = null;
           else {
             aux = this.replace(/\"/g, "\\\"");
             aux = '"'+aux+'"';
           }
         } else
           aux=parseFloat(aux);
         return aux;
       };
     }
     
     if (!String.prototype.quote) {
       String.prototype.quote=function()
       {
         var aux = this.replace(/\"/g, "\\\"");
         return '"'+aux+'"';
       };
     }
     
     if (!String.prototype.unquote) {
       String.prototype.unquote = function() {
         var firstChar='', lastChar='';
         if (this.length>1) {
           firstChar = this.substr(0,1);
           lastChar = this.substr(this.length-1,1);
           if (firstChar == lastChar) {
             if ((lastChar == '"') || (lastChar == "'"))
               return this.substr(1,this.length-2);
             else
               return this.toString()+'';
           } else if (((firstChar=='(') && (lastChar==')')) ||
                      ((firstChar=='[') && (lastChar==']')) ||
                      ((firstChar=='{') && (lastChar=='}'))) {
             return this.substr(1,this.length-2);
           } else
             return this.toString()+'';
         } else
           return this.toString()+'';
       };
     }
     
     if (!String.prototype.format) {
       String.prototype.format = function() {
         var args = arguments;
         return this.replace(/{(\d+)}/g, function(match, number) {
           return typeof args[number] != 'undefined' ? args[number] : match
           ;
         });
       };
     }
     
     if (!String.prototype.isPIS) {
       String.prototype.isPIS = function() {
           var p, n, s, c = this,
               mult = [3,2,9,8,7,6,5,4,3,2],
               ret=false;
           if((c = c.replace(/[^\d]/g,"").split("")).length == 11) {
             p=c[0];
             n=1;
             while ((n<c.length) && (c[n]==p))
               n++;
             if (n<c.length) {
               var total = 0, m1, m2, d;
               for(n=0; n<10; n++) {
                 total += (c[n]*mult[n]);
               }
               resto = 11 - total % 11;
               resto = resto == 10 || (resto == 11?0:resto);
               digito = c[10];
               ret = resto == digito;
             }
           }
           return ret;
       }
     }
     
     if (!String.prototype.isCNS) {
       String.prototype.isCNS = function () {
         var ret=false, c=this.replace(/[^\d]/g,""), s=0;
         if ((c.match("[1-2]\\d{10}00[0-1]\\d")) || (c.match("[7-9]\\d{14}"))) {
           c=c.split('');
           for(var i=0; i<c.length; i++) {
             s+=Math.floor(c[i],10) * (15-i);
           }
           ret =( s % 11 )==0;
         }
         return ret;
     }}
     
     if (!String.prototype.isCPF) {
       //+ Carlos R. L. Rodrigues
       //@ http://jsfromhell.com/string/is-cpf [rev. #1]
     
       String.prototype.isCPF = function(){
           var s, c = this;
           if((c = c.replace(/[^\d]/g,"").split("")).length != 11) return false;
           if(new RegExp("^" + c[0] + "{11}$").test(c.join(""))) return false;
           for(s = 10, n = 0, i = 0; s >= 2; n += c[i++] * s--){}
           if(c[9] != (((n %= 11) < 2) ? 0 : 11 - n)) return false;
           for(s = 11, n = 0, i = 0; s >= 2; n += c[i++] * s--){}
           if(c[10] != (((n %= 11) < 2) ? 0 : 11 - n)) return false;
           return true;
       };
     
     
       function _mod_(a,b){return Math.round(a-(Math.floor(a/b)*b));}
       //+ Johny W.Alves
       //@ http://www.johnywalves.com.br/artigos/js-gerador-cnpj-cpf/
       String.prototype.gerarCNPJ = function() {
         var n1 = Math.round(Math.random()*9);
         var n2 = Math.round(Math.random()*9);
         var n3 = Math.round(Math.random()*9);
         var n4 = Math.round(Math.random()*9);
         var n5 = Math.round(Math.random()*9);
         var n6 = Math.round(Math.random()*9);
         var n7 = Math.round(Math.random()*9);
         var n8 = Math.round(Math.random()*9);
         var n9 = 0;
         var n10 = 0;
         var n11 = 0;
         var n12 = 1;
     
         var aux = n1 * 5 + n2 * 4 + n3 * 3 + n4 * 2 + n5 * 9 + n6 * 8 + n7 * 7 + n8 * 6 + n9 * 5 + n10 * 4 + n11 * 3 + n12 * 2;
         aux = _mod_(aux, 11);
         var nv1 = aux < 2 ? 0 : 11 - aux;
     
         aux = n1 * 6 + n2 * 5 + n3 * 4 + n4 * 3 + n5 * 2 + n6 * 9 + n7 * 8 + n8 * 7 + n9 * 6 + n10 * 5 + n11 * 4 + n12 * 3 + nv1 * 2;
         aux = _mod_(aux, 11);
         var nv2 = aux < 2 ? 0 : 11 - aux;
     
         return ""+n1+n2+"."+n3+n4+n5+"."+n6+n7+n8+"/"+n9+n10+n11+n12+"-"+nv1+nv2;
       };
     
       //+ Johny W.Alves
       //@ http://www.johnywalves.com.br/artigos/js-gerador-cnpj-cpf/
       String.prototype.gerarCPF = function() {
         var n1 = Math.round(Math.random()*9);
         var n2 = Math.round(Math.random()*9);
         var n3 = Math.round(Math.random()*9);
         var n4 = Math.round(Math.random()*9);
         var n5 = Math.round(Math.random()*9);
         var n6 = Math.round(Math.random()*9);
         var n7 = Math.round(Math.random()*9);
         var n8 = Math.round(Math.random()*9);
         var n9 = Math.round(Math.random()*9);
     
         var aux = n1 * 10 + n2 * 9 + n3 * 8 + n4 * 7 + n5 * 6 + n6 * 5 + n7 * 4 + n8 * 3 + n9 * 2;
         aux = _mod_(aux, 11);
         var nv1 = aux < 2 ? 0 : 11 - aux;
     
         aux = n1 * 11 + n2 * 10 + n3 * 9 + n4 * 8 + n5 * 7 + n6 * 6 + n7 * 5 + n8 * 4 + n9 * 3 + nv1 * 2;
         aux = _mod_(aux, 11);
         var nv2 = aux < 2 ? 0 : 11 - aux;
     
         return ""+n1+n2+n3+"."+n4+n5+n6+"."+n7+n8+n9+"-"+nv1+nv2;
       };
     
       String.prototype.asCPF = function() {
         return this.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/g,"\$1.\$2.\$3\-\$4");
       }
     
       String.prototype.asCNPJ = function() {
         return this.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/g,"\$1.\$2.\$3\/\$4\-\$5");
       }
     
       String.prototype.asCEP = function() {
         return this.replace(/(\d{2})(\d{3})(\d{3})/g,"\$1\$2-\$3");
       }
     
       String.prototype.asPhone = function(agruparDeTresEmTres) {
         agruparDeTresEmTres = agruparDeTresEmTres || false;
         var number=""+this.replace(/\D/g,"");
         while (number.substr(0,1)=="0")
           number=number.substr(1);
         /* telefone sem nono digito */
         if (number.length==8)
           return number.replace(/(\d{4})(\d{4})/g,"\$1-\$2");
         else if (number.length==9) {
           if (agruparDeTresEmTres)
             return number.replace(/(\d{3})(\d{3})(\d{3})/g,"\$1-\$2-\$3");
           else
             return number.replace(/(\d{1})(\d{4})(\d{4})/g,"\$1 \$2-\$3");
         }
         else if (number.length==10)
           return number.replace(/(\d{2})(\d{4})(\d{4})/g,"(\$1) \$2-\$3");
         else if (number.length==11) {
           if (agruparDeTresEmTres)
             return number.replace(/(\d{2})(\d{3})(\d{3})(\d{3})/g,"(\$1) \$2 \$3\ \$4");
           else
             return number.replace(/(\d{2})(\d{1})(\d{4})(\d{4})/g,"(\$1) \$2 \$3-\$4");
         } else if (number.length==12) {
           return number.replace(/(\d{2})(\d{2})(\d{4})(\d{4})/g,"+$1 (\$2) \$3-\$4");
         } else if (number.length==13) {
           if (agruparDeTresEmTres)
             return number.replace(/(\d{2})(\d{2})(\d{3})(\d{3})(\d{3})/g,"+$1 (\$2) \$3-\$4-\$5");
           else
             return number.replace(/(\d{2})(\d{2})(\d{1})(\d{4})(\d{4})/g,"+$1 (\$2) \$3 \$4-\$5");
         }
       }
     
       String.prototype.asRG = function() {
         var number=""+this.replace(/[^a-zA-Z0-9]+/g, "");
         if (number.length==8)
           return number.replace(/(\w{1})(\d{4})(\d{2})(\d{1})$/,"$1.$2.$3-$4");
         else
           return number.replace(/(\d{2})(\d{3})(\d{3})(\d{1})$/,"$1.$2.$3-$4");
       }
     }
     
     if (!String.prototype.isEmail) {
       String.prototype.isEmail = function() {
         return isEmail(this);
       };
     }
     
     if (!String.prototype.isCNPJ) {
       //+ Carlos R. L. Rodrigues
       //@ http://jsfromhell.com/string/is-cnpj [rev. #1]
     
       String.prototype.isCNPJ = function(){
           var i, b = [6,5,4,3,2,9,8,7,6,5,4,3,2], c = this;
           if((c = c.replace(/[^\d]/g,"").split("")).length != 14) return false;
           for(i = 0, n = 0; i < 12; n += c[i] * b[++i]){}
           if(c[12] != (((n %= 11) < 2) ? 0 : 11 - n)) return false;
           for(i = 0, n = 0; i <= 12; n += c[i] * b[i++]){}
           if(c[13] != (((n %= 11) < 2) ? 0 : 11 - n)) return false;
           return true;
       };
     }
     
     if (!String.prototype.toFloat) {
       String.prototype.toFloat = function () {
         n=this;
         // n=n.replace(":", "");
         if (n.match(/^-?((\d*[,.]){1,4})?\d*$/)) {
           var p=n.indexOf('.'),
               c=n.indexOf(',');
           if (p<c) {
             n=n.replace(".", "");
           }
           n=n.replace(",", ".");
           return parseFloat(n);
         } else {
           return NaN;
         }
       };
     }
     
     Function.prototype.method = function (name, func) {
       this.prototype[name] = func;
       return this;
     };
     
     Function.method('inherits', function (Parent) {
       this.prototype = new Parent( );
       return this;
     });
     
     if (typeof Object.create !== 'function') {
         Object.create = function (o) {
             var F = function () {};
             F.prototype = o;
             return new F();
         };
     }
     
     /*
     if (typeof Object.prototype.copyObj !== 'function') {
       Object.prototype.copyObj = function() {
         var JThis;
         try {
           JThis=JSON.stringify(this);
         } catch(e) {
           JThis={};
         }
         return (JSON.parse(JThis));
       };
     }
     */
     
     if (!Array.prototype.indexOf) {
       Array.prototype.indexOf = function(elt /*, from*/) {
         var len = this.length >>> 0;
     
         var from = Number(arguments[1]) || 0;
         from = (from < 0) ?
                Math.ceil(from) :
                Math.floor(from);
         if (from < 0)
           from += len;
     
         for (; from < len; from++) {
           if (from in this &&
               this[from] === elt)
             return from;
         }
         return -1;
       };
     }
     
     var forceStringValue = function(aObjArr, aIndex) {
       return ((aObjArr[aIndex] || "")+"").unquote();
     }
     
     array_intersect = function(a, b) {
         var t=b;
         if (b.length > a.length) { b = a; a = t };
         return a.filter(function (e) {
             if (b.indexOf(e) !== -1) return true;
         });
     }
     
     
     /* as the array keys could be used with data coming from
      * interbase (UPPERCASE) postgresql (lowercase most of the time)
      * or mysql (mixed case when configured properly), we need
      * to let the programmer use which one he wants in the data model
      * while keep the array untoched.
      * Not only that, the field names on client side can be prefixed and/or
      * postfixed, so we need to chose the more adequated
      * This function guess which one is the best */
      var suggestKeyName = function (aObj, aKeyName, fieldPrefix, fieldPostfix) {
         var ret = null;
         if (aKeyName) {
           var aColList;
           if (!isArray(aObj))
             aColList = aObj;
           else {
             aColList = {};
             for(var a=0; a<aObj.length; a++) {
               aColName=aObj[a];
               aColList[aColName]=aColName;
             }
           }
     
           var UKey = aKeyName.toUpperCase();
           for(var i in aColList) {
             if (aColList.hasOwnProperty(i))
               if (i.toUpperCase()==UKey)
                 ret = i;
           }
     
           if (fieldPrefix || fieldPostfix) {
             if (ret===null) {
               fieldPrefix = fieldPrefix || '';
               fieldPostfix = fieldPostfix || '';
               if ((UKey.substr(0,fieldPrefix.length))==fieldPrefix.toUpperCase()) {
                 aKeyName=aKeyName.substr(fieldPrefix.length);
                 ret=suggestKeyName(aColList, aKeyName);
               }
     
               if (ret===null) {
                 if (UKey.substr(UKey.length - fieldPostfix.length) == fieldPostfix.toUpperCase()) {
                   aKeyName = aKeyName.substr(0, aKeyName.length - fieldPostfix.length);
                   ret=suggestKeyName(aColList, aKeyName);
                 }
               }
             }
           }
         }
         return ret;
      };
     
     /* date extensions */
     if (typeof Date.prototype.getFirstDayOfWeek == 'undefined') {
       Date.prototype.getFirstDayOfWeek = function(weekStart) {
         /* weekStart - By default it is sunday (0)
          */
         weekStart = (weekStart || 0);
         var date = (new Date(this.getTime()));
         date.setHours(0,0,0,0);
         while (date.getDay()!=weekStart) {
           date.setDate(date.getDate()-1);
           date.setHours(0,0,0,0);
         }
         return date;
       }
     }
     
     if (typeof Date.
     prototype.monthFirstDOW == 'undefined') {
       Date.prototype.monthFirstDOW = function(aDate) {
         var auxDate = new Date((aDate || this).getTime());
         auxDate.setDate(1);
         return auxDate.getDay();
       };
     }
     
     if (typeof Date.prototype.monthLastDay == 'undefined') {
       Date.prototype.monthLastDay = function(aDate) {
         var auxDate = new Date((aDate || this).getTime());
         return new Date(auxDate.getYear(), auxDate.getMonth()+1, 0).getDate();
       };
     }
     
     if (typeof Date.prototype.monthLastDOW == 'undefined') {
       Date.prototype.monthLastDOW = function(aDate) {
         var auxDate = new Date((aDate || this).getTime());
         auxDate.setDate(this.monthLastDay(auxDate));
         return auxDate.getDay();
       };
     }
     
     if (typeof Date.prototype.nextMonth == 'undefined')
       Date.prototype.nextMonth = function (aDate) {
         var auxDate = new Date((aDate || this).getTime());
         var thisMonth = auxDate.getMonth();
         auxDate.setMonth(thisMonth+1);
         if(auxDate.getMonth() != thisMonth+1 && auxDate.getMonth() !== 0)
           auxDate.setDate(0);
         return auxDate;
       };
     
     if (typeof Date.prototype.prevMonth == 'undefined')
       Date.prototype.prevMonth = function (aDate) {
         var auxDate = new Date((aDate || this).getTime());
         var thisMonth = auxDate.getMonth();
         auxDate.setMonth(thisMonth-1);
         if(auxDate.getMonth() != thisMonth-1 && (auxDate.getMonth() != 11 || (thisMonth == 11 && auxDate.getDate() == 1)))
           auxDate.setDate(0);
         return auxDate;
       };
     
     if (typeof Date.prototype.incMonth == 'undefined')
       Date.prototype.incMonth = function (aInc)
       {
         var thisMonth = this.getMonth();
         this.setMonth(thisMonth + aInc);
         if(this.getMonth() != thisMonth + aInc && (this.getMonth() != 11 || (thisMonth == 11 && this.getDate() == 1)))
           this.setDate(0);
       };
     
     if (typeof Date.prototype.incDay == 'undefined')
       Date.prototype.incDay = function () {
         this.setDate(this.getDate()+1);
       };
     
     if (typeof Date.prototype.decDay == 'undefined')
       Date.prototype.decDay = function () {
         this.setDate(this.getDate()-1);
       };
     
     if (typeof Date.prototype.incWeek == 'undefined')
       Date.prototype.incWeek = function () {
         this.setDate(this.getDate()+7);
       };
     
     if (typeof Date.prototype.decWeek == 'undefined')
       Date.prototype.decWeek = function () {
         this.setDate(this.getDate()-7);
       };
     
     if (typeof Date.prototype.daysInMonth == 'undefined')
       Date.prototype.daysInMonth = function(iMonth, iYear) {
         if (!iYear)
           iYear = this.getFullYear();
         if (!iMonth)
           iMonth = this.getMonth() + 1;
     
         return 32 - new Date(parseInt(iYear), parseInt(iMonth)-1, 32).getDate();
       };
     
     /* french style is dd/mm/yyyy */
     if (typeof Date.prototype.toFrenchString == 'undefined')
       Date.prototype.toFrenchString = function () {
         return '' + this.getDate() + '/' +
                     (this.getMonth()+1) + '/' +
                     this.getFullYear();
       };
     
     /* UDate is like ISO8601 but with no separations and without milliseconds */
     if (typeof Date.prototype.toUDate == 'undefined')
       Date.prototype.toUDate = function () {
     
         return '' + pad(this.getFullYear(),4) +
                     pad(this.getMonth()+1, 2) +
                     pad(this.getDate(), 2) +
                     pad(this.getHours(), 2) +
                     pad(this.getMinutes(), 2) +
                     pad(this.getSeconds(), 2);
       };
     
     
     if (typeof Date.prototype.toISOString =='undefined' ) {
     
         Date.prototype.toISOString = function() {
           return this.getUTCFullYear() +
               '-' + pad( this.getUTCMonth() + 1, 2) +
               '-' + pad( this.getUTCDate(), 2) +
               'T' + pad( this.getUTCHours(), 2 ) +
               ':' + pad( this.getUTCMinutes(), 2 ) +
               ':' + pad( this.getUTCSeconds(), 2 ) +
               '.' + String( (this.getUTCMilliseconds()/1000).toFixed(3) ).slice( 2, 5 ) +
               'Z';
         };
     
     }
     
     if (typeof Date.prototype.frenchFormat == 'undefined')
       Date.prototype.frenchFormat = function () {
         return this.getDate()+'/'+(this.getMonth()+1)+'/'+this.getFullYear();
       };
     
     /*
      * aStrDate: string
      * aFormat: string where 'y' means year, 'm' month, 'd' day,
      *                       'H' hour, 'M': minutes and 'S' seconds
      *
      * It can understand yyyy-mm-dd HH:MM:SS or yy/m/dd H:MM:SS from
      * the same format string and diferent dates
      */
     var extractDateValues = function (aStrDate, aFormat, aDateMap) {
     
       var getDateSplitter = function(aStr) {
         var splitter1 = (aStr.match(/\//g)||[]).length;
         var splitter2 = (aStr.match(/\-/g)||[]).length;
     
         return ((splitter1>splitter2)?'/':((splitter2>0)?'-':''));
       };
     
       var dateSplitter = getDateSplitter(aFormat);
       var dateSplitterInUse = getDateSplitter(aStrDate);
     
       var i, dtSequence = null;
       if (dateSplitter>'') {
         dtSequence = [];
         var dtFormat = aFormat.split(dateSplitter);
         for(i=0; i<dtFormat.length; i++)
           dtSequence[dtFormat[i].substr(0,1)]=i;
     
         var aux=aStrDate.split(dateSplitter);
         while (aux.length<dtFormat.length) {
           aStrDate=aStrDate+dateSplitter+'01';
           aux[aux.length]=0;
         }
       }
     
       var getElementValue = function (aElementTag) {
         var p = aFormat.indexOf(aElementTag);
         var l = 0;
         if (p>=0) {
           var elementValue;
           while ((p+l<aFormat.length) && (aFormat.substr(p+l,1)==aElementTag))
             l++;
     
           if (((aElementTag.match(/[y,m,d]/g) || []).length>0) && (dtSequence!==null)) {
             elementValue = aStrDate.split(dateSplitter)[dtSequence[aElementTag]].split(' ')[0];
           } else
             elementValue = str2int(aStrDate.substr(p,l));
           return [ p, elementValue, aElementTag, l ];
         } else
           return [ null, null, aElementTag ];
       };
     
       var parseDate = function() {
         return aStrDate.match(/\b[\d]+\b/g);
       };
     
       var getReturn = function(aDateArray) {
         var ret = [ ];
         for(var i=0; i<aDateArray.length; i++) {
           var auxValue=aDateArray[i][1];
           if (auxValue !== null) {
             auxValue=auxValue.toString();
             if (auxValue.length==1)
               auxValue=pad(auxValue,2);
             else if (auxValue.length==3)
               auxValue=pad(auxValue,4);
           }
           ret[aDateArray[i][2]]=auxValue;
         }
         return ret;
       };
     
       var ret;
     
       if (aFormat === undefined)
         aFormat='yyyy-mm-ddThh:mm:ss';
       if (aStrDate === '') {
         ret=[];
         ret['y']='';
         ret['d']='';
         ret['m']='';
         ret['H']='';
         ret['M']='';
         ret['S']='';
         return ret;
       }
     
       if (aDateMap === undefined)
         aDateMap = {};
     
       aDateMap.elems = [ getElementValue('y'),
                          getElementValue('m'),
                          getElementValue('d'),
                          getElementValue('H'),
                          getElementValue('M'),
                          getElementValue('S')
                        ];
     
       /* first we try with fixed position analisis
        * we test the minimum approach: month/day */
       if ( (dateSplitterInUse==dateSplitter) &&
            (((aDateMap.elems[1][1]>0) && (aDateMap.elems[1][1]<13)) &&
             ((aDateMap.elems[2][1]>=1) && (aDateMap.elems[2][1]<=31)))) {
         ret = getReturn(aDateMap.elems);
       } else {
         /* secondly we try with relative position analisis
          * so we have in sortedInfo the field as it comes
          * from the user */
         var sortedInfo = aDateMap.elems;
         sortedInfo.sort(function(a,b) {
             if (a[0]===b[0])
               return 0;
             else if ((a[0]<b[0]) || (b[0]===null))
               return -1;
             else if ((a[0]>b[0]) || (a[0]===null))
               return 1;
           });
         /* we extract the date elements */
         var auxDateInfo = parseDate();
         for(i=0; i<sortedInfo.length && i<(auxDateInfo||[]).length; i++)
           sortedInfo[i][1] = auxDateInfo[i];
         if (sortedInfo[0][1] * sortedInfo[1][1] * sortedInfo[2][1] > 0 )
           ret = getReturn(sortedInfo);
         else {
           ret=null;
         }
       }
       return ret;
     };
     
     var array2date = function(aDate) {
       return pad(aDate['d'],2)+'-'+pad(aDate['m'],2)+'-'+aDate['y'];
     };
     
     /* hh:mm (string) -> minutes (integer) */
     function time2minutes(aTime) {
       if ((aTime===undefined) || (aTime=='NaN'))
         aTime=0;
       var h=0;
       var m=0;
       if (aTime>'') {
         aTime=""+aTime+" ";
         var p=aTime.indexOf('h');
         if (p<0)
           p=aTime.indexOf(':');
         if (p>=0) {
           h=aTime.substring(0,p);
           m=parseInt(aTime.substring(p+1));
           if (isNaN(m))
             m=0;
         } else {
           h=0;
           m=parseInt(aTime);
         }
         aTime=h*60+m;
       }
     
       if (aTime<0)
         aTime=0;
     
       return aTime;
     }
     
     /* minutes (integer) -> hh:mm (string) */
     function minutes2time(aMinutes) {
       var h=pad(Math.floor(aMinutes / 60),2);
       var m=pad(aMinutes % 60,2);
       return h+':'+m;
     }
     
     /* unix timestamp to day of week (0=sunday) */
     function timestamp2dayOfWeek(aTimestamp) {
       var aux=new Date();
       aux.setTime(aTimestamp*1000);
       return aux.getDay();
     }
     
     Date.prototype.getWeekNumber = function(){
       var d = new Date(Date.UTC(this.getFullYear(), this.getMonth(), this.getDate()));
       var dayNum = d.getUTCDay() || 7;
       d.setUTCDate(d.getUTCDate() + 4 - dayNum);
       var yearStart = new Date(Date.UTC(d.getUTCFullYear(),0,1));
       return Math.ceil((((d - yearStart) / 86400000) + 1)/7)
     };
     
     /* http://stackoverflow.com/questions/11887934/how-to-check-if-the-dst-daylight-saving-time-is-in-effect-and-if-it-is-whats */
     Date.prototype.stdTimezoneOffset = function() {
         var jan = new Date(this.getFullYear(), 0, 1);
         var jul = new Date(this.getFullYear(), 6, 1);
         return Math.max(jan.getTimezoneOffset(), jul.getTimezoneOffset());
     }
     
     Date.prototype.dst = function() {
         return this.getTimezoneOffset() < this.stdTimezoneOffset();
     }
     
     function TimezoneDetect() {
       /*
        * http://www.michaelapproved.com/articles/timezone-detect-and-ignore-daylight-saving-time-dst/
        */
       var dtDate = new Date('1/1/' + (new Date()).getUTCFullYear());
       var intOffset = 10000; //set initial offset high so it is adjusted on the first attempt
       var intMonth;
       var intHoursUtc;
       var intHours;
       var intDaysMultiplyBy;
     
       //go through each month to find the lowest offset to account for DST
       for (intMonth=0;intMonth < 12;intMonth++){
         //go to the next month
         dtDate.setUTCMonth(dtDate.getUTCMonth() + 1);
     
         //To ignore daylight saving time look for the lowest offset.
         //Since, during DST, the clock moves forward, it'll be a bigger number.
         if (intOffset > (dtDate.getTimezoneOffset() * (-1))){
             intOffset = (dtDate.getTimezoneOffset() * (-1));
         }
       }
     
       return intOffset;
     }
     
     
     
     /* unix timestamp -> dd/mm/yyyy */
     function timestamp2date(aTimestamp) {
       if ((!isNaN(aTimestamp)) && (aTimestamp>'')) {
         var aux=new Date();
         aux.setTime(aTimestamp*1000 + (-TimezoneDetect() - aux.getTimezoneOffset()) * 60 * 1000);
         return pad(aux.getDate(),2)+'/'+pad(aux.getMonth()+1,2)+'/'+pad(aux.getFullYear(),4);
       } else
         return '';
     }
     
     /* unix timestamp -> hh:mm */
     function timestamp2time(aTimestamp, seconds) {
       var ret;
       if (aTimestamp===undefined)
         ret='';
       else if ((aTimestamp==='') || (isNaN(aTimestamp)))
         ret='';
       else {
         if (seconds===undefined)
           seconds=false;
         var aux=new Date();
         aux.setTime(aTimestamp*1000);
     
         ret=pad(aux.getHours(),2)+':'+pad(aux.getMinutes(),2);
         if (seconds)
           ret = ret+':'+pad(aux.getSeconds(),2);
       }
       return ret;
     }
     
     
     /* dd/mm/yyyy hh:mm:ss -> yyyymmddhhmmss */
     function FDate2UDate(a) {
       a=a || (new Date("1/1/1900")).toFrenchString();
       if (a.indexOf('/')>0)
         a=a.split('/');
       else
         a=a.split('-');
       var h=a[2] || '';
       h=h.split(' ');
       a[2]=h[0];
       h=h[1];
       if (h===undefined)
         h='00:00:00';
       h=h.split(':');
       if (h[1]===undefined)
         h[1]=0;
       if (h[2]===undefined)
         h[2]=0;
       return pad(a[2],4)+'-'+pad(a[1],2)+'-'+pad(a[0],2)+' '+pad(h[0],2)+':'+pad(h[1],2)+':'+pad(h[2],2);
     }
     
     /* ISO8601 -> javascript date object */
     function UDate2JSDate(aUDate) {
       var aDate=extractDateValues(aUDate,'yyyymmddHHMMSS');
       var d=new Date(aDate['y'], aDate['m']-1, aDate['d'], aDate['H'], aDate['M'], aDate['S']);
     
       return d;
     }
     
     /* ISO8601 -> french date dd/mm/yyyy */
     function UDate2Date(aUDate, aFormat) {
       if (typeof aFormat==='undefined')
         aFormat="d/m/y";
       var ret='';
       var aDate=extractDateValues(aUDate,'yyyymmddHHMMSS');
       if (!(aDate === null)) { 
         for(var i=0; i<aFormat.length; i++)
           if (/^[d,m,y]+$/.test(aFormat[i]))
             ret+=aDate[aFormat[i]];
           else
             ret+=aFormat[i];
       }
       if (ret=='//')
         ret='';
       return ret;
     }
     
     /* ISO8601 -> french time hh:mm:ss */
     function UDate2Time(aUDate, aFormat) {
       if (typeof aFormat==='undefined')
         aFormat="H:M:S";
       var ret='';
       var aDate=extractDateValues(aUDate,'yyyymmddHHMMSS');
       if (aDate) {
         ret='';
         for(var i=0; i<aFormat.length; i++)
           if (/^[H,M,S]+$/.test(aFormat[i]))
             ret+=aDate[aFormat[i]];
           else
             ret+=aFormat[i];
       }
       if (ret=='::')
         ret='';
       return ret;
     }
     
     /* interbase (english) date mmddyyyy -> french date dd-mm-yyyy */
     function IBDate2Date(aIBDate) {
       var ret='';
       var aDate=extractDateValues(aIBDate,'mmddyyyyHHMMSS');
       if (aDate)
         ret =  aDate['d']+'-'+aDate['m']+'-'+aDate['y'];
       return ret;
     }
     
     // french date dd-mm-yyyy -> english date  mm-dd-yyyy
     function date2IBDate(aFDate) {
       var ret='';
       var aDate=extractDateValues(aFDate,'ddmmyyyyHHMMSS');
       if (aDate)
         ret =  pad(aDate['m'],2)+'-'+pad(aDate['d'],2)+'-'+aDate['y'];
       return ret;
     }
     
     // french date dd-mm-yyyy -> ISO8601 date  yyyy-mm-dd
     function date2UDate(aFDate) {
       var ret='';
       var aDate=extractDateValues(aFDate,'ddmmyyyyHHMMSS');
       if (aDate)
         ret =  pad(aDate['y'],4)+'-'+pad(aDate['m'],2)+'-'+pad(aDate['d'],2);
       return ret;
     }
     
     function IBDate2timestamp(a) {
       a = IBDate2Date(a);
       a = date2timestamp(a);
       return a;
     }
     
     function timestamp2IBDate(a) {
       a = timestamp2date(a);
       a = date2IBDate(a);
       return a;
     }
     
     var dateTransform = function (aStrDate, srcFormat, destFormat) {
       if (aStrDate) {
         var tmpDate = extractDateValues(aStrDate, srcFormat);
         if (tmpDate) {
           var auxMap={};
           var emptyDate = extractDateValues("111111111111", destFormat, auxMap);
           var ret=destFormat;
     
           for(var i=0; i<auxMap.elems.length; i++) {
             /* e is a shortcut to the array map */
             var e = auxMap.elems[i];
             if (e[0] !== null) {
               /* pos 2 is the date index (y,m,d,H,M,S)
                * pos 3 is the target length */
               var value = pad(tmpDate[e[2]],e[3]);
     
               /* pos 0 is the start of the date element
                * we expect to have enough space in date return */
               while (ret.length < e[0] + e[3])
                 ret = ret+' ';
               ret=ret.substr(0,e[0]) + value + ret.substr(e[0]+e[3], ret.length);
             }
           }
     
         }
         return ret;
       } else
         return null;
     };
     
     var isValidDate=function(aFrenchDate) {
       var ok=true, d;
       if ("string"==typeof aFrenchDate)  {
         aFrenchDate = dateTransform(aFrenchDate, "dd/mm/yyyy", "yyyy-mm-ddT12:59:59");
       }
     
       try {
         d = new Date(aFrenchDate);
       } catch(err) {
         ok=false;
       }
     
       if (ok) {
         if (!isNaN(d.getTime())) {
           var f=dateTransform(d.getFullYear()+"/"+(d.getMonth()+1)+"/"+d.getDate(), "yyyy/mm/dd", "yyyy-mm-ddT12:59:59");
           ok=f==aFrenchDate;
         } else
           ok=false;
       }
     
       return ok;
     };
     
     var dateInRange = function (aFrenchDate, aFrenchMinDate, aFrenchMaxDate) {
       /* determina se uma data em formato frances (dd/mm/yyyy) estÃ¡ no escopo indicado
          Na ausencia de um dos parÃ¡metros, ele assume hoje para aquele que falta
          Se faltam os dois, a Ãºnica data vÃ¡lida Ã© hoje */
       var ret=false;
       if (isValidDate(aFrenchDate)) {
         aFrenchMinDate = aFrenchMinDate || (new Date()).toFrenchString();
         aFrenchMaxDate = aFrenchMaxDate || (new Date()).toFrenchString();
         if (isValidDate(aFrenchMinDate)) {
           if (isValidDate(aFrenchMaxDate)) {
             aFrenchDate    = dateTransform(aFrenchDate,    "dd/mm/yyyy", "yyyy-mm-dd");
             aFrenchMinDate = dateTransform(aFrenchMinDate, "dd/mm/yyyy", "yyyy-mm-dd");
             aFrenchMaxDate = dateTransform(aFrenchMaxDate, "dd/mm/yyyy", "yyyy-mm-dd");
             ret = ((aFrenchDate>=aFrenchMinDate) && (aFrenchDate<=aFrenchMaxDate));        
           }
         }
       }
       return ret;
     };
     
     /* discover type of things */
     function isInfinity(aValue) {
       if (aValue!==undefined)
         return  (aValue.POSITIVE_INFINITY || aValue.NEGATIVE_INFINITY  || aValue=='Infinity');
       else
         return true;
     }
     
     function isNumber(n) {
       if (typeof n === 'string') {
         var f=n.toFloat();
         if (!isNaN(f))
           n=f;
       }
       return !isNaN(parseFloat(n)) && isFinite(n);
     }
     
     function isOperator(n) {
       var ret=false;
       if (typeof n == 'string') {
         ret = ((n=='<') || (n=='>') || (n=='!') || (n=='!==') || (n=='!=') ||
                (n=='>') || (n=='<=') || (n=='>=') || (n=='=='));
       }
       return ret;
     }
     
     var isArray = function (value) {
       /* by Douglas Crockford */
       return value &&
       typeof value === 'object' &&
       typeof value.length === 'number' &&
       typeof value.splice === 'function' &&
       !(value.propertyIsEnumerable('length'));
     };
     
     /* regexp functions */
     function isEmail(email) {
       var aux=(email && email.unquote()) || '';
       var re = /^(([^\*<>()[\]\\.,;:\s@\"]+(\.[^\*<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
       return re.test(aux);
     }
     
     /* miscellaneous functions */
     function pad(number, length) {
       var str = '' + number;
       while (str.length < length)
         str = '0' + str;
       return str;
     }
     
     function unmaskHTML(auxLine) {
       if (typeof auxLine == 'string') {
         if (auxLine.length>0) {
           var c=auxLine.substr(0,1);
           if ((c=='"') || (c=="'")) {
             var z=auxLine.substr(auxLine.length-1);
             if (c==z)
               auxLine=auxLine.substr(1,auxLine.length-2);
           }
         }
         while (auxLine.indexOf('!!')>=0)
           auxLine = auxLine.replace('!!', '&');
     
         auxLine = auxLine.replace(/\[/g, '<');
         auxLine = auxLine.replace(/\]/g, '>');
     
         auxLine = auxLine.replace(/\&\#91\;/g, '[');
         auxLine = auxLine.replace(/\&\#93\;/g, ']');
       } else if (typeof auxLine=='number') {
           auxLine = auxLine.toString();
       } else if (typeof auxLine == 'object') {
         for(var aux in auxLine) {
           if (auxLine.hasOwnProperty(aux)) {
             auxLine[aux]=unmaskHTML(auxLine[aux]);
           }
         }
       } else {
         auxLine = '';
       }
       return auxLine;
     }
     
     function escapeRegExp(string) {
         return string.replace(/([.*+?^=!:${}()|\[\]\/\\])/g, "\\$1");
     }
     
     function maskHTML(auxLine) {
       auxLine = auxLine || '';
       if (typeof auxLine == 'string') {
         while (auxLine.indexOf('<')>=0)
           auxLine = auxLine.replace(/\</,'[');
         while (auxLine.indexOf('>')>=0)
           auxLine = auxLine.replace(/\>/,']');
         while (auxLine.indexOf('&')>=0)
           auxLine = auxLine.replace('&','!!');
       }
       return auxLine;
     }
     
     function trim(str) {
       if (typeof str=="string")
         return str.replace(/^\s+|\s+$/g,"")
       else
         return "";
     }
     
     if (!String.prototype.trim) {
       String.prototype.trim = function() {
         return this.replace(/^\s+|\s+$/g,"");
       }
     }
     
     function unparentesis(v) {
       if (v.length>1) {
         if ((v.substring(0,1)=='(') || (v.substring(0,1)=='[') || (v.substring(0,1)=='{'))
           v=v.substring(1,v.length-1);
       }
       return (v);
     }
     
     function wordwrap( str, width, brk, cut ) {
         brk = brk || '\n';
         width = width || 75;
         cut = cut || false;
     
         if (!str) { return str; }
     
         var regex = '.{1,' +width+ '}(\\s|$)' + (cut ? '|.{' +width+ '}|.+$' : '|\\S+?(\\s|$)');
     
         return str.match( RegExp(regex, 'g') ).join( brk );
     }
     
     function nl2br(aString) {
       var ret='';
     
       if (aString!==undefined) {
         ret = aString.replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + '<br>' + '$2');
       }
     
       return ret;
     }
     
     function dec2deg(dec, asLatitude) {
       asLatitude = "undefined" == typeof asLatitude?true:asLatitude;
       if (sign(dec)!=0) {
         var positive = sign(dec) > 0,
             gpsdeg, r, gpsmin,
             D, M, S, suffix;
     
         dec=Math.abs(dec);
         gpsdeg = parseInt(dec),
         r = dec - (gpsdeg * 1.0);
         gpsmin = r * 60.0;
         r = gpsmin - (parseInt(gpsmin)*1.0);
         D = gpsdeg;
         M = parseInt(gpsmin);
         S = parseInt(r*60.0);
     
         if (asLatitude) {
           suffix=positive?'N':'S';
         } else {
           suffix=positive?'E':'W';
         }
         return D+"&deg; "+M+"' "+S+"'' "+suffix;
       } else {
         return 'NULL';
       }
     }
     
     function deg2dec(deg) {
       var suffix = deg.replace(/[^SNEW]+/g, '');
       var ret, d = deg.replace(/[\W_]+/g," ").split(' ');
       for(var i=0; i<2; i++)
         d[i]=str2int(d[i]||0);
       ret = d[0] + d[1]/60 + d[2]/3600;
       if ((suffix=='S') || (suffix=='W'))
         ret = ret * -1;
       return ret;
     }
     
     function str2double(aStr) {
       if (typeof aStr === 'undefined')
         aStr = '0';
     
       aStr=""+aStr;
     
       var a="";
       if ((aStr.indexOf(',')>0) && (aStr.indexOf('.')>0))
         a=aStr.replace('.','');
       else
         a=aStr;
       a=a.replace(',','.');
     
       if (a==='')
         a='0.00';
     
       a=parseFloat(a);
       if (isNaN(a))
         a=0;
       var ret = parseFloat(a);
       ret = parseFloat(ret);
       return ret;
     }
     
     function str2int(value) {
       var n = parseInt(value);
       return n === null || isNaN(n) ? 0 : n;
     }
     
     function str2bool(aStr, aDefaultValue) {
       if (aDefaultValue===undefined)
         aDefaultValue = false;
     
       if (aStr===undefined)
         aStr = aDefaultValue;
       else
         aStr = aStr.toUpperCase()=='TRUE';
     
       return aStr;
     }
     
     function bool2str(aBool) {
       return aBool ? 'TRUE' : 'FALSE';
     }
     
     function sign(aValue) {
       aValue = str2int(aValue);
       if (aValue==0) 
         return 0;
       else if (aValue<0)
         return -1;
       else
         return 1;
     }
     
     function dec2hex(d) {
       return d.toString(16);
     }
     
     function hex2dec(h) {
         return parseInt(h,16);
     }
     
     /*
      * interface routines
      * in version 0.9.0 will be moved to yinterface.js
      */
     
     var rowColorSpecBase = function () {
       var that = { };
       that.cfgColors = [ '#F2F0F0', '#CFCFCF'] ;
     
       that.suggestRowColor=function (curRow) {
         return this.cfgColors[(curRow % 2)];
       };
     
       that.setRowColors = function (c1, c2) {
         this.cfgColors[0]=c1 || this.cfgColors[0];
         this.cfgColors[1]=c2 || this.cfgColors[1];
       };
       return that;
     };
     
     var rowColorSpec = rowColorSpecBase();
     
     function decomposeColor(color) {
       if (color.substr(0,4)=='rgb(') {
         var aux=color.replace(/[^\d,]/g, '').split(','), ret=[], n;
         for (n=0; n<aux.length; n++)
           ret[n]=str2int(aux[n]);
         return ret;
       } else {
         if (color.substr(0,1)=='#')
           color=color.substr(1);
     
     
         var r=hex2dec(color.substr(0,2));
         var g=hex2dec(color.substr(2,2));
         var b=hex2dec(color.substr(4,2));
     
         return [r, g, b];
       }
     }
     
     function complementaryColor(color) {
       var xDiv = 32;
       var xLimite = 250;
       var xDivContraste = 3;
     
       var dc = decomposeColor(color);
       for (var n=0; n<3; n++) {
         dc[n] = Math.floor(dc[n] / xDivContraste);
         dc[n] = Math.floor(dc[n] / xDiv) * xDiv;
         if (xLimite>0)
           dc[n] = xLimite - Math.min(xLimite, dc[n]);
       }
     
       var res=dec2hex(dc[0])+dec2hex(dc[1])+dec2hex(dc[2]);
     
       return '#'+res;
     }
     
     function grayColor(color) {
       var xDiv=32;
     
       var dc = decomposeColor(color);
     
       var r=Math.floor(dc[0] / xDiv) * xDiv;
       var g=Math.floor(dc[1] / xDiv) * xDiv;
       var b=Math.floor(dc[2] / xDiv) * xDiv;
     
       var gray=(r+g+b) / 3;
     
           gray=dec2hex(gray);
     
       var res=gray+gray+gray;
     
       return res;
     }
     
     function rgb2hex (rgb) {
       var res;
       if (typeof rgb.b ==='undefined') {
         res=pad(dec2hex(rgb[0]),2)+pad(dec2hex(rgb[1]),2)+pad(dec2hex(rgb[2]),2);
       } else {
         res=pad(dec2hex(rgb.r),2)+pad(dec2hex(rgb.g),2)+pad(dec2hex(rgb.b),2);
       }
       return res;
     }
     
     function pickColorFromGradient(firstColor, lastColor, weight) {
         var w1 = Math.max(0,Math.min(weight, 100))/100,
             w2 = 1 - w1,
             color1 = decomposeColor(firstColor), 
             color2 = decomposeColor(lastColor);
     
         var rgb = [Math.round(color1[0] * w1 + color2[0] * w2),
             Math.round(color1[1] * w1 + color2[1] * w2),
             Math.round(color1[2] * w1 + color2[2] * w2)];
         return rgb2hex(rgb);
     }
     
     function brighterColor(color, percent){
         percent=percent || 50;
         color=decomposeColor(color);
     
         var r = parseInt(color[0]),
             g = parseInt(color[1]),
             b = parseInt(color[2]);
     
         return '#' +
            ((0|(1<<8) + r + (256 - r) * percent / 100).toString(16)).substr(1) +
            ((0|(1<<8) + g + (256 - g) * percent / 100).toString(16)).substr(1) +
            ((0|(1<<8) + b + (256 - b) * percent / 100).toString(16)).substr(1);
     }
     
     
     var hsmColorBase = function() {
       function min3(a,b,c) {
           return (a<b)?((a<c)?a:c):((b<c)?b:c);
       }
     
       function max3(a,b,c) {
           return (a>b)?((a>c)?a:c):((b>c)?b:c);
       }
     
       var that = {};
     
       that.HueShift = function(h,s) {
           h+=s; while (h>=360.0) h-=360.0; while (h<0.0) h+=360.0; return h;
       };
     
       /* original source: http://color.twysted.net/  and  http://colormatch.dk/ */
     
       that.RGB2HSV = function(rgb) {
           var hsv = {};
           var max;
           if (typeof rgb.r =='undefined') {
             var aux = { r: rgb[0], g: rgb[1], b: rgb[2] };
             rgb=aux;
           }
           max=max3(rgb.r,rgb.g,rgb.b);
           var dif=max-min3(rgb.r,rgb.g,rgb.b);
           hsv.saturation=(max===0.0)?0:(100*dif/max);
           if (hsv.saturation===0) hsv.hue=0;
           else if (rgb.r==max) hsv.hue=60.0*(rgb.g-rgb.b)/dif;
           else if (rgb.g==max) hsv.hue=120.0+60.0*(rgb.b-rgb.r)/dif;
           else if (rgb.b==max) hsv.hue=240.0+60.0*(rgb.r-rgb.g)/dif;
           if (hsv.hue<0.0) hsv.hue+=360.0;
           hsv.value=Math.round(max*100/255);
           hsv.hue=Math.round(hsv.hue);
           hsv.saturation=Math.round(hsv.saturation);
           return hsv;
       };
     
       that.HSV2RGB = function(hsv, positionalRGB) {
           positionalRGB = positionalRGB || true;
     
           var aux={}, rgb={};
           if (hsv.saturation==0) {
               aux.r=aux.g=aux.b=Math.round(hsv.value*2.55);
           } else {
               hsv.hue/=60;
               hsv.saturation/=100;
               hsv.value/=100;
               var i=Math.floor(hsv.hue);
               var f=hsv.hue-i;
               var p=hsv.value*(1-hsv.saturation);
               var q=hsv.value*(1-hsv.saturation*f);
               var t=hsv.value*(1-hsv.saturation*(1-f));
               switch(i) {
               case 0: aux.r=hsv.value; aux.g=t; aux.b=p; break;
               case 1: aux.r=q; aux.g=hsv.value; aux.b=p; break;
               case 2: aux.r=p; aux.g=hsv.value; aux.b=t; break;
               case 3: aux.r=p; aux.g=q; aux.b=hsv.value; break;
               case 4: aux.r=t; aux.g=p; aux.b=hsv.value; break;
               default: aux.r=hsv.value; aux.g=p; aux.b=q;
               }
               aux.r=Math.round(aux.r*255);
               aux.g=Math.round(aux.g*255);
               aux.b=Math.round(aux.b*255);
           }
     
           if (positionalRGB) {
             rgb[0]=aux.r;
             rgb[1]=aux.g;
             rgb[2]=aux.b;
           } else {
             rgb = aux;
           }
           return rgb;
       };
     
       return that;
     }
     
     var hsmColor=hsmColorBase();
     
     /*
      * The original source code was picked from
      * http://www.openjs.com/scripts/xml_parser/
      * without copyright notes.
      *
      * The job here was to package the function inside a functional
      * object oriented model
      */
     
     var xml2array = function (xmlDoc, attribute_inside) {
       var key;
       var that = {};
       that.not_whitespace = new RegExp(/[^\s]/);
       that.parent_count=null;
       attribute_inside = attribute_inside || 0;
     
       //Process the xml data
       that.xml2array = function(xmlDoc,parent_count,attribute_inside) {
         var arr, temp_arr, temp, parent = "";
         parent_count = parent_count || {};
     
         attribute_inside = attribute_inside || 0; /*:CONFIG: Value - 1 or 0
         * If 1, Value and Attribute will be shown inside the tag - like this...
         * For the XML string...
         * <guid isPermaLink="true">http://www.bin-co.com/</guid>
         * The resulting array will be...
         * array['guid']['value'] = "http://www.bin-co.com/";
         * array['guid']['attribute_isPermaLink'] = "true";
         *
         * If 0, the value will be inside the tag but the attribute will be outside - like this...
         * For the same XML String the resulting array will be...
         * array['guid'] = "http://www.bin-co.com/";
         * array['attribute_guid_isPermaLink'] = "true";
         */
     
         if(xmlDoc.nodeName && xmlDoc.nodeName.charAt(0) != "#") {
           if(xmlDoc.childNodes.length > 1) { //If its a parent
             arr = {};
             parent = xmlDoc.nodeName;
     
           }
         }
         var value = xmlDoc.nodeValue;
         if(xmlDoc.parentNode && xmlDoc.parentNode.nodeName && value) {
           if(that.not_whitespace.test(value)) {//If its a child
             arr = {};
             arr[xmlDoc.parentNode.nodeName] = value;
           }
         }
     
         if(xmlDoc.childNodes.length) {
           if(xmlDoc.childNodes.length == 1) { //Just one item in this tag.
             arr = that.xml2array(xmlDoc.childNodes[0],parent_count, attribute_inside); //:RECURSION:
           } else { //If there is more than one childNodes, go thru them one by one and get their results.
             if (!arr)
               arr=[];
     
             var index = 0;
     
             for(var i=0; i<xmlDoc.childNodes.length; i++) {//Go thru all the child nodes.
               temp = that.xml2array(xmlDoc.childNodes[i],parent_count, attribute_inside); //:RECURSION:
               if(temp) {
                 var assoc = false;
                 var arr_count = 0;
                 var lastKey = null;
                 for(key in temp) {
                   if (temp.hasOwnProperty(key)) {
                     lastKey = key;
                     if(isNaN(key)) assoc = true;
                     arr_count++;
                     if(arr_count>2) break;//We just need to know wether it is a single value array or not
                   }
                 }
     
                 if(assoc && arr_count == 1) {
                   if(arr[lastKey]) {  //If another element exists with the same tag name before,
                           //    put it in a numeric array.
                     //Find out how many time this parent made its appearance
                     if(!parent_count || !parent_count[lastKey]) {
                       parent_count[lastKey] = 0;
     
                       temp_arr = arr[lastKey];
                       arr[lastKey] = {};
                       arr[lastKey][0] = temp_arr;
                     }
                     parent_count[lastKey]++;
                     arr[lastKey][parent_count[lastKey]] = temp[lastKey]; //Members of of a numeric array
                   } else {
                     parent_count[lastKey] = 0;
                     arr[lastKey] = temp[lastKey];
                     if(xmlDoc.childNodes[i].attributes && xmlDoc.childNodes[i].attributes.length) {
                       for(var j=0; j<xmlDoc.childNodes[i].attributes.length; j++) {
                         var nname = xmlDoc.childNodes[i].attributes[j].nodeName;
                         if(nname) {
                           /* Value and Attribute inside the tag */
                           if(attribute_inside) {
                             temp_arr = arr[lastKey];
                             arr[lastKey] = {};
                             arr[lastKey]['value'] = temp_arr;
                             arr[lastKey]['attribute_'+nname] = xmlDoc.childNodes[i].attributes[j].nodeValue;
                           } else {
                             /* Value in the tag and Attribute otside the tag(in parent) */
                             // 20180305 arr['attribute_' + lastKey + '_' + nname] = xmlDoc.childNodes[i].attributes[j].value;
                             arr[nname] = xmlDoc.childNodes[i].attributes[j].value;
                           }
                         }
                       } //End of 'for(var j=0; j<xmlDoc. ...'
                     } //End of 'if(xmlDoc.childNodes[i] ...'
                   }
                 } else {
                   arr[index] = temp;
                   index++;
                 }
               } //End of 'if(temp) {'
               temp=undefined;
             } //End of 'for(var i=0; i<xmlDoc. ...'
           }
         }
     
         if(parent && arr) {
           temp = arr;
           arr = {};
     
           arr[parent] = temp;
         }
         return arr;
       };
     
       return that.xml2array(xmlDoc);
     };
     
     
     /*====================================================================
      * HASH routines
      * http://phpjs.org/functions/
      *====================================================================*/
     
     var utf8_decode = function(str_data) {
       //  discuss at: http://phpjs.org/functions/utf8_decode/
       // original by: Webtoolkit.info (http://www.webtoolkit.info/)
       //    input by: Aman Gupta
       //    input by: Brett Zamir (http://brett-zamir.me)
       // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
       // improved by: Norman "zEh" Fuchs
       // bugfixed by: hitwork
       // bugfixed by: Onno Marsman
       // bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
       // bugfixed by: kirilloid
       //   example 1: utf8_decode('Kevin van Zonneveld');
       //   returns 1: 'Kevin van Zonneveld'
     
       var tmp_arr = [],
         i = 0,
         ac = 0,
         c1 = 0,
         c2 = 0,
         c3 = 0,
         c4 = 0;
     
       str_data += '';
     
       while (i < str_data.length) {
         c1 = str_data.charCodeAt(i);
         if (c1 <= 191) {
           tmp_arr[ac++] = String.fromCharCode(c1);
           i++;
         } else if (c1 <= 223) {
           c2 = str_data.charCodeAt(i + 1);
           tmp_arr[ac++] = String.fromCharCode(((c1 & 31) << 6) | (c2 & 63));
           i += 2;
         } else if (c1 <= 239) {
           // http://en.wikipedia.org/wiki/UTF-8#Codepage_layout
           c2 = str_data.charCodeAt(i + 1);
           c3 = str_data.charCodeAt(i + 2);
           tmp_arr[ac++] = String.fromCharCode(((c1 & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
           i += 3;
         } else {
           c2 = str_data.charCodeAt(i + 1);
           c3 = str_data.charCodeAt(i + 2);
           c4 = str_data.charCodeAt(i + 3);
           c1 = ((c1 & 7) << 18) | ((c2 & 63) << 12) | ((c3 & 63) << 6) | (c4 & 63);
           c1 -= 0x10000;
           tmp_arr[ac++] = String.fromCharCode(0xD800 | ((c1 >> 10) & 0x3FF));
           tmp_arr[ac++] = String.fromCharCode(0xDC00 | (c1 & 0x3FF));
           i += 4;
         }
       }
     
       return tmp_arr.join('');
     };
     
     var utf8_encode = function (argString) {
       //  discuss at: http://phpjs.org/functions/utf8_encode/
       // original by: Webtoolkit.info (http://www.webtoolkit.info/)
       // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
       // improved by: sowberry
       // improved by: Jack
       // improved by: Yves Sucaet
       // improved by: kirilloid
       // bugfixed by: Onno Marsman
       // bugfixed by: Onno Marsman
       // bugfixed by: Ulrich
       // bugfixed by: Rafal Kukawski
       // bugfixed by: kirilloid
       //   example 1: utf8_encode('Kevin van Zonneveld');
       //   returns 1: 'Kevin van Zonneveld'
     
       if (argString === null || typeof argString === 'undefined') {
         return '';
       }
     
       var string = (argString + ''); // .replace(/\r\n/g, "\n").replace(/\r/g, "\n");
       var utftext = '',
         start, end, stringl = 0;
     
       start = end = 0;
       stringl = string.length;
       for (var n = 0; n < stringl; n++) {
         var c1 = string.charCodeAt(n);
         var enc = null;
     
         if (c1 < 128) {
           end++;
         } else if (c1 > 127 && c1 < 2048) {
           enc = String.fromCharCode(
             (c1 >> 6) | 192, (c1 & 63) | 128
           );
         } else if ((c1 & 0xF800) != 0xD800) {
           enc = String.fromCharCode(
             (c1 >> 12) | 224, ((c1 >> 6) & 63) | 128, (c1 & 63) | 128
           );
         } else { // surrogate pairs
           if ((c1 & 0xFC00) != 0xD800) {
             throw new RangeError('Unmatched trail surrogate at ' + n);
           }
           var c2 = string.charCodeAt(++n);
           if ((c2 & 0xFC00) != 0xDC00) {
             throw new RangeError('Unmatched lead surrogate at ' + (n - 1));
           }
           c1 = ((c1 & 0x3FF) << 10) + (c2 & 0x3FF) + 0x10000;
           enc = String.fromCharCode(
             (c1 >> 18) | 240, ((c1 >> 12) & 63) | 128, ((c1 >> 6) & 63) | 128, (c1 & 63) | 128
           );
         }
         if (enc !== null) {
           if (end > start) {
             utftext += string.slice(start, end);
           }
           utftext += enc;
           start = end = n + 1;
         }
       }
     
       if (end > start) {
         utftext += string.slice(start, stringl);
       }
     
       return utftext;
     };
     
     function utf8_to_ascii(str) {
      var out = "", i, l = str.length, u;
      for (i=0; i<l; i++) {
       if (str.charCodeAt(i) < 0x80) {
        out += str.charAt(i);
       } else {
         u = "" + str.charCodeAt(i).toString(16);
         out += "\\u" + (u.length === 2 ? "00" + u : u.length === 3 ? "0" + u : u);
       }
      }
      return out;
     }
     
     function replaceDiacritics(str){
     
       var diacritics = [
         {char: 'A', base: /[\300-\306]/g},
         {char: 'a', base: /[\340-\346]/g},
         {char: 'E', base: /[\310-\313]/g},
         {char: 'e', base: /[\350-\353]/g},
         {char: 'I', base: /[\314-\317]/g},
         {char: 'i', base: /[\354-\357]/g},
         {char: 'O', base: /[\322-\330]/g},
         {char: 'o', base: /[\362-\370]/g},
         {char: 'U', base: /[\331-\334]/g},
         {char: 'u', base: /[\371-\374]/g},
         {char: 'N', base: /[\321]/g},
         {char: 'n', base: /[\361]/g},
         {char: 'C', base: /[\307]/g},
         {char: 'c', base: /[\347]/g}
       ];
     
       diacritics.forEach(function(letter){
         str = str.replace(letter.base, letter.char);
       });
     
       return str;
     };
     
     /*=====================================================================
      * http://stackoverflow.com/questions/105034/create-guid-uuid-in-javascript
      *=====================================================================*/
     var generateUUID = function () {
         var d = new Date().getTime();
         if(window.performance && typeof window.performance.now === "function"){
             d += performance.now(); //use high-precision timer if available
         }
         var uuid = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
             var r = (d + Math.random()*16)%16 | 0;
             d = Math.floor(d/16);
             return (c=='x' ? r : (r&0x3|0x8)).toString(16);
         });
         return uuid;
     }
     
     function guid() {
       function s4() {
         return Math.floor((1 + Math.random()) * 0x10000)
           .toString(16)
           .substring(1);
       }
       return s4() + s4() + '-4' + s4().substr(0,3) + '-' + s4() + '-' +
         s4() + '-' + s4() + s4() + s4();
     }
     
     function newIdentifier () {
       function randomIntFromInterval(min, max) {
         return Math.floor(Math.random() * (max - min + 1) + min);
       }
       nro = (new Date()).getTime();
     
       var ret = '';
       nro = trim(" " + nro);
       nro = nro + "" + randomIntFromInterval(11, 99);
       nro = nro + "" + randomIntFromInterval(11, 99);
       nro = nro + "" + randomIntFromInterval(11, 99);
       for (var i = 0; i < Math.ceil(nro.length / 2); i++) {
         var p = parseInt(nro.substr(i * 2, 2));
         var x = dec2hex(p);
         ret += pad(x, 2);
         if ((i + 1) % 3 == 0)
           ret += '-';
       }
       return ret;
     };
     
     var generateSmallSessionUniqueId = (function() {
       var nextIndex = [0,0,0];
       var chars = '8i7u6y5t4r3e2w1q9o0p'.split('');
       var num = chars.length;
     
       return function() {
         var a = nextIndex[0];
         var b = nextIndex[1];
         var c = nextIndex[2];
         var id = chars[a] + chars[b] + chars[c];
     
         a = ++a % num;
     
         if (!a) {
           b = ++b % num;
     
           if (!b) {
             c = ++c % num;
           }
         }
         nextIndex = [a, b, c];
         return id;
       }
     }());
     
     var md5=function (str) {
       //  discuss at: http://phpjs.org/functions/md5/
       // original by: Webtoolkit.info (http://www.webtoolkit.info/)
       // improved by: Michael White (http://getsprink.com)
       // improved by: Jack
       // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
       //    input by: Brett Zamir (http://brett-zamir.me)
       // bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
       //  depends on: utf8_encode
       //   example 1: md5('Kevin van Zonneveld');
       //   returns 1: '6e658d4bfcb59cc13f96c14450ac40b9'
     
       var xl;
     
       var rotateLeft = function(lValue, iShiftBits) {
         return (lValue << iShiftBits) | (lValue >>> (32 - iShiftBits));
       };
     
       var addUnsigned = function(lX, lY) {
         var lX4, lY4, lX8, lY8, lResult;
         lX8 = (lX & 0x80000000);
         lY8 = (lY & 0x80000000);
         lX4 = (lX & 0x40000000);
         lY4 = (lY & 0x40000000);
         lResult = (lX & 0x3FFFFFFF) + (lY & 0x3FFFFFFF);
         if (lX4 & lY4) {
           return (lResult ^ 0x80000000 ^ lX8 ^ lY8);
         }
         if (lX4 | lY4) {
           if (lResult & 0x40000000) {
             return (lResult ^ 0xC0000000 ^ lX8 ^ lY8);
           } else {
             return (lResult ^ 0x40000000 ^ lX8 ^ lY8);
           }
         } else {
           return (lResult ^ lX8 ^ lY8);
         }
       };
     
       var _F = function(x, y, z) {
         return (x & y) | ((~x) & z);
       };
       var _G = function(x, y, z) {
         return (x & z) | (y & (~z));
       };
       var _H = function(x, y, z) {
         return (x ^ y ^ z);
       };
       var _I = function(x, y, z) {
         return (y ^ (x | (~z)));
       };
     
       var _FF = function(a, b, c, d, x, s, ac) {
         a = addUnsigned(a, addUnsigned(addUnsigned(_F(b, c, d), x), ac));
         return addUnsigned(rotateLeft(a, s), b);
       };
     
       var _GG = function(a, b, c, d, x, s, ac) {
         a = addUnsigned(a, addUnsigned(addUnsigned(_G(b, c, d), x), ac));
         return addUnsigned(rotateLeft(a, s), b);
       };
     
       var _HH = function(a, b, c, d, x, s, ac) {
         a = addUnsigned(a, addUnsigned(addUnsigned(_H(b, c, d), x), ac));
         return addUnsigned(rotateLeft(a, s), b);
       };
     
       var _II = function(a, b, c, d, x, s, ac) {
         a = addUnsigned(a, addUnsigned(addUnsigned(_I(b, c, d), x), ac));
         return addUnsigned(rotateLeft(a, s), b);
       };
     
       var convertToWordArray = function(str) {
         var lWordCount;
         var lMessageLength = str.length;
         var lNumberOfWords_temp1 = lMessageLength + 8;
         var lNumberOfWords_temp2 = (lNumberOfWords_temp1 - (lNumberOfWords_temp1 % 64)) / 64;
         var lNumberOfWords = (lNumberOfWords_temp2 + 1) * 16;
         var lWordArray = new Array(lNumberOfWords - 1);
         var lBytePosition = 0;
         var lByteCount = 0;
         while (lByteCount < lMessageLength) {
           lWordCount = (lByteCount - (lByteCount % 4)) / 4;
           lBytePosition = (lByteCount % 4) * 8;
           lWordArray[lWordCount] = (lWordArray[lWordCount] | (str.charCodeAt(lByteCount) << lBytePosition));
           lByteCount++;
         }
         lWordCount = (lByteCount - (lByteCount % 4)) / 4;
         lBytePosition = (lByteCount % 4) * 8;
         lWordArray[lWordCount] = lWordArray[lWordCount] | (0x80 << lBytePosition);
         lWordArray[lNumberOfWords - 2] = lMessageLength << 3;
         lWordArray[lNumberOfWords - 1] = lMessageLength >>> 29;
         return lWordArray;
       };
     
       var wordToHex = function(lValue) {
         var wordToHexValue = '',
           wordToHexValue_temp = '',
           lByte, lCount;
         for (lCount = 0; lCount <= 3; lCount++) {
           lByte = (lValue >>> (lCount * 8)) & 255;
           wordToHexValue_temp = '0' + lByte.toString(16);
           wordToHexValue = wordToHexValue + wordToHexValue_temp.substr(wordToHexValue_temp.length - 2, 2);
         }
         return wordToHexValue;
       };
     
       var x = [],
         k, AA, BB, CC, DD, a, b, c, d, S11 = 7,
         S12 = 12,
         S13 = 17,
         S14 = 22,
         S21 = 5,
         S22 = 9,
         S23 = 14,
         S24 = 20,
         S31 = 4,
         S32 = 11,
         S33 = 16,
         S34 = 23,
         S41 = 6,
         S42 = 10,
         S43 = 15,
         S44 = 21;
     
       str = this.utf8_encode(str);
       x = convertToWordArray(str);
       a = 0x67452301;
       b = 0xEFCDAB89;
       c = 0x98BADCFE;
       d = 0x10325476;
     
       xl = x.length;
       for (k = 0; k < xl; k += 16) {
         AA = a;
         BB = b;
         CC = c;
         DD = d;
         a = _FF(a, b, c, d, x[k + 0], S11, 0xD76AA478);
         d = _FF(d, a, b, c, x[k + 1], S12, 0xE8C7B756);
         c = _FF(c, d, a, b, x[k + 2], S13, 0x242070DB);
         b = _FF(b, c, d, a, x[k + 3], S14, 0xC1BDCEEE);
         a = _FF(a, b, c, d, x[k + 4], S11, 0xF57C0FAF);
         d = _FF(d, a, b, c, x[k + 5], S12, 0x4787C62A);
         c = _FF(c, d, a, b, x[k + 6], S13, 0xA8304613);
         b = _FF(b, c, d, a, x[k + 7], S14, 0xFD469501);
         a = _FF(a, b, c, d, x[k + 8], S11, 0x698098D8);
         d = _FF(d, a, b, c, x[k + 9], S12, 0x8B44F7AF);
         c = _FF(c, d, a, b, x[k + 10], S13, 0xFFFF5BB1);
         b = _FF(b, c, d, a, x[k + 11], S14, 0x895CD7BE);
         a = _FF(a, b, c, d, x[k + 12], S11, 0x6B901122);
         d = _FF(d, a, b, c, x[k + 13], S12, 0xFD987193);
         c = _FF(c, d, a, b, x[k + 14], S13, 0xA679438E);
         b = _FF(b, c, d, a, x[k + 15], S14, 0x49B40821);
         a = _GG(a, b, c, d, x[k + 1], S21, 0xF61E2562);
         d = _GG(d, a, b, c, x[k + 6], S22, 0xC040B340);
         c = _GG(c, d, a, b, x[k + 11], S23, 0x265E5A51);
         b = _GG(b, c, d, a, x[k + 0], S24, 0xE9B6C7AA);
         a = _GG(a, b, c, d, x[k + 5], S21, 0xD62F105D);
         d = _GG(d, a, b, c, x[k + 10], S22, 0x2441453);
         c = _GG(c, d, a, b, x[k + 15], S23, 0xD8A1E681);
         b = _GG(b, c, d, a, x[k + 4], S24, 0xE7D3FBC8);
         a = _GG(a, b, c, d, x[k + 9], S21, 0x21E1CDE6);
         d = _GG(d, a, b, c, x[k + 14], S22, 0xC33707D6);
         c = _GG(c, d, a, b, x[k + 3], S23, 0xF4D50D87);
         b = _GG(b, c, d, a, x[k + 8], S24, 0x455A14ED);
         a = _GG(a, b, c, d, x[k + 13], S21, 0xA9E3E905);
         d = _GG(d, a, b, c, x[k + 2], S22, 0xFCEFA3F8);
         c = _GG(c, d, a, b, x[k + 7], S23, 0x676F02D9);
         b = _GG(b, c, d, a, x[k + 12], S24, 0x8D2A4C8A);
         a = _HH(a, b, c, d, x[k + 5], S31, 0xFFFA3942);
         d = _HH(d, a, b, c, x[k + 8], S32, 0x8771F681);
         c = _HH(c, d, a, b, x[k + 11], S33, 0x6D9D6122);
         b = _HH(b, c, d, a, x[k + 14], S34, 0xFDE5380C);
         a = _HH(a, b, c, d, x[k + 1], S31, 0xA4BEEA44);
         d = _HH(d, a, b, c, x[k + 4], S32, 0x4BDECFA9);
         c = _HH(c, d, a, b, x[k + 7], S33, 0xF6BB4B60);
         b = _HH(b, c, d, a, x[k + 10], S34, 0xBEBFBC70);
         a = _HH(a, b, c, d, x[k + 13], S31, 0x289B7EC6);
         d = _HH(d, a, b, c, x[k + 0], S32, 0xEAA127FA);
         c = _HH(c, d, a, b, x[k + 3], S33, 0xD4EF3085);
         b = _HH(b, c, d, a, x[k + 6], S34, 0x4881D05);
         a = _HH(a, b, c, d, x[k + 9], S31, 0xD9D4D039);
         d = _HH(d, a, b, c, x[k + 12], S32, 0xE6DB99E5);
         c = _HH(c, d, a, b, x[k + 15], S33, 0x1FA27CF8);
         b = _HH(b, c, d, a, x[k + 2], S34, 0xC4AC5665);
         a = _II(a, b, c, d, x[k + 0], S41, 0xF4292244);
         d = _II(d, a, b, c, x[k + 7], S42, 0x432AFF97);
         c = _II(c, d, a, b, x[k + 14], S43, 0xAB9423A7);
         b = _II(b, c, d, a, x[k + 5], S44, 0xFC93A039);
         a = _II(a, b, c, d, x[k + 12], S41, 0x655B59C3);
         d = _II(d, a, b, c, x[k + 3], S42, 0x8F0CCC92);
         c = _II(c, d, a, b, x[k + 10], S43, 0xFFEFF47D);
         b = _II(b, c, d, a, x[k + 1], S44, 0x85845DD1);
         a = _II(a, b, c, d, x[k + 8], S41, 0x6FA87E4F);
         d = _II(d, a, b, c, x[k + 15], S42, 0xFE2CE6E0);
         c = _II(c, d, a, b, x[k + 6], S43, 0xA3014314);
         b = _II(b, c, d, a, x[k + 13], S44, 0x4E0811A1);
         a = _II(a, b, c, d, x[k + 4], S41, 0xF7537E82);
         d = _II(d, a, b, c, x[k + 11], S42, 0xBD3AF235);
         c = _II(c, d, a, b, x[k + 2], S43, 0x2AD7D2BB);
         b = _II(b, c, d, a, x[k + 9], S44, 0xEB86D391);
         a = addUnsigned(a, AA);
         b = addUnsigned(b, BB);
         c = addUnsigned(c, CC);
         d = addUnsigned(d, DD);
       }
     
       var temp = wordToHex(a) + wordToHex(b) + wordToHex(c) + wordToHex(d);
     
       return temp.toLowerCase();
     };
     
     /*==================================================
      * USER INTERFACE ROUTINES
      *==================================================*/
     
     if ((typeof window=='object') && (typeof _onLoadMethods == 'undefined')) {
       var _onLoadMethods = [];
     
       window.addOnLoadManager=function(aFunc)
       {
         var i=_onLoadMethods.length;
         _onLoadMethods[i]=aFunc;
       };
     
       document.addEventListener(
           "DOMContentLoaded",
           function(event) {
             if (mTabNav) mTabNav.init();
           }
       );
     
       __startup = function() {
         for(var i=0; i<_onLoadMethods.length; i++)
           if (_onLoadMethods.hasOwnProperty(i))
             if (_onLoadMethods[i]!==undefined)
               _onLoadMethods[i]();
       }
     
       if (typeof cordova=='object') {
         document.addEventListener(
           'deviceready',
           __startup
           , false);
       } else {
         window.addEventListener("load", function() {
           __startup();
           if (!isOnMobile()) {
             var event = new Event('deviceready');
             document.dispatchEvent(event);
           }
         }, false);
       }
     
     
       var addEvent = function(elem, eventName, eventHandler) {
         if (typeof elem == 'string') elem=y$(elem);
         if ((elem === null) || (typeof elem === 'undefined')) return;
     
         var i;
     
         if ((elem.nodeName!='SELECT') && ((isArray(elem)) || ((typeof elem=="object") && (typeof elem.length=="number")))) {
           for(i=0; i<elem.length; i++)
             addEvent(elem[i], eventName, eventHandler);
         } else {
           var eventList=eventName.split(" "), aEventName;
           for(i=0; i<eventList.length; i++) {
             aEventName=eventList[i];
             if ( elem.addEventListener ) {
               elem.addEventListener( aEventName, eventHandler, aEventName.toUpperCase()=="change" );
             } else if ( elem.attachEvent ) {
               elem.attachEvent( "on" + aEventName, eventHandler );
             } else {
               elem["on"+aEventName]=eventHandler;
             }
           }
         }
     
       };
     
       var removeEvent = function(elem, eventName, eventHandler) {
         if (typeof elem == 'string') elem=y$(elem);
         if ((elem === null) || (typeof elem === 'undefined')) return;
     
         var i;
     
         if (isArray(elem)) {
           for(i=0; i<elem.length; i++)
             removeEvent(elem[i], eventHandler);
         } else {
           var eventList=eventName.split(" "), aEventName;
           for(i=0; i<eventList.length; i++) {
             aEventName=eventList[i];
             if ( elem.addEventListener ) {
               elem.removeEventListener( aEventName, eventHandler, false );
             } else if ( elem.detachEvent ) {
               elem.detachEvent( "on" + aEventName, eventHandler );
             } else {
               elem["on"+aEventName]=null;
             }
           }
         }
     
       }
     
     }
     
 /* END ymisc.js */
 _dump("ymisc");
 /* START yanalise.js */
     /*********************************************
      * First Version (C) 2014 - esteban daniel dortta - dortta@yahoo.com
      * yLexObj introduced in 2016-08-22 0.8.50-0
     **********************************************/
     
     // ==== YeAPF - Javascript implementation
     function yAnalise(aLine, aStack, aObject) {
       "use strict";
       if (aLine !== undefined) {
     
         aLine = unmaskHTML(aLine);
         if (true === window.__allowInsecureJSCalls__) {
           aObject = aObject || window;
         } else {
           aObject = aObject || {};
         }
     
         /* var yPattern = /%[+(\w)]|[]\(/gi; */
         var yPattern = /\%(|\w+)\(/gi;
         var yFunctions = ',int,integer,intz,intn,decimal,ibdate,tsdate,tstime,tsdatetime,date,time,lat2deg,lon2deg,words,image,nl2br,quoted,singleQuoted,condLabel,checked,rg,cpf,cnpj,phone,cep,brDocto';
         var p, p1, p2, c1, c2, p3;
         var aValue = '';
     
         while ((typeof aLine == 'string') && (p = aLine.search(yPattern)) >= 0) {
           p1 = aLine.slice(p).search(/\(/);
           if (p1 >= 0) {
             c1 = aLine.slice(p + p1 + 1, p + p1 + 2);
             if ((c1 == '"') || (c1 == "'")) {
               p3 = p + p1 + 1;
               do {
                 p3++;
                 c2 = aLine.slice(p3, p3 + 1);
               } while ((c2 != c1) && (p3 < aLine.length));
               p2 = p3 + aLine.slice(p3).search(/\)/) - p;
             } else
               p2 = aLine.slice(p).search(/\)/);
     
             var funcName = aLine.slice(p + 1, p + p1);
             var funcParams = aLine.slice(p + p1 + 1, p + p2);
             var parametros = funcParams, n=null;
             funcParams = funcParams.split(',');
             for (n = 0; n < funcParams.length; n++) {
               funcParams[n]=(funcParams[n] || '').trim();
               funcParams[n] = yAnalise(funcParams[n], aStack, aObject);
             }
     
             aValue = undefined;
             var fParamU = funcParams[0].toUpperCase();
             var fParamN = funcParams[0];
             if (aStack !== undefined) {
               // can come a stack or a simple unidimensional array
               if (aStack[0] == undefined) {
                 if (aStack[fParamU])
                   aValue = yAnalise(aStack[fParamU], aStack, aObject);
                 else
                   aValue = yAnalise(aStack[fParamN], aStack, aObject);
               } else {
                 for (var sNdx = aStack.length - 1;
                   (sNdx >= 0) && (aValue == undefined); sNdx--)
                   if (aStack[sNdx][fParamU] != undefined)
                     aValue = yAnalise(aStack[sNdx][fParamU], aStack, aObject);
                   else if (aStack[sNdx][fParamN] != undefined)
                   aValue = yAnalise(aStack[sNdx][fParamN], aStack, aObject);
               }
             } else {
               if ("" == fParamN) {
                 aValue = "";
               } else {
                 if ((fParamN.substr(0, 1) != '.') && (eval('typeof ' + fParamN) == 'string'))
                   aValue = eval(fParamN);
                 else
                   aValue = yAnalise(fParamN, null, aObject);
               }
             }
     
             if (aValue == undefined)
               aValue = '';
             funcParams[0] = aValue;
     
             switch (funcName) {
               case 'integer':
               case 'int':
               case 'intz':
               case 'intn':
                 aValue = str2int(aValue);
                 if (aValue == 0) {
                   if (funcName == 'intz')
                     aValue = '-';
                   else if (funcName == 'intn')
                     aValue = '';
                 }
                 break;
               case 'decimal':
                 var aDecimals = Math.max(0, parseInt(funcParams[1]));
                 aValue = str2double(aValue);
                 aValue = aValue.toFixed(aDecimals);
                 break;
               case 'phone':
                 aValue = (aValue || '').asPhone();
                 break;
               case 'lon2deg':
                 aValue = dec2deg(aValue, false);
                 break;
               case 'lat2deg':
                 aValue = dec2deg(aValue, true);
                 break;
               case 'deg2dec':
                 aValue = deg2dec(aValue, false);
                 break;
               case 'ibdate':
                 aValue = IBDate2Date(aValue);
                 break;
               case 'tsdate':
                 aValue = timestamp2date(aValue);
                 break;
               case 'tstime':
                 aValue = timestamp2time(aValue);
                 break;
               case 'tsdatetime':
                 aValue = timestamp2date(aValue)+' '+timestamp2time(aValue);
                 break;
               case 'date':
                 if (funcParams[1])
                   aValue = UDate2Date(aValue, funcParams[1]);
                 else
                   aValue = UDate2Date(aValue);
                 break;
               case 'time':
                 if (funcParams[1])
                   aValue = UDate2Time(aValue, funcParams[1]);
                 else
                   aValue = UDate2Time(aValue);
                 break;
     
               case 'rg':
                 aValue = (''+(aValue || '')).asRG();
                 break;
     
               case 'cpf':
                 aValue = (''+(aValue || '')).asCPF();
                 break;
     
               case 'cnpj':
                 aValue = (''+(aValue || '')).asCNPJ();
                 break;
     
               case 'brDocto':
                 var aux=(''+(aValue || ''));
                 if (aux.isCNPJ())
                   aValue = aux.asCNPJ();
                 else if (aux.isCPF())
                   aValue = aux.asCPF();
                 else
                   aValue = aux.asRG();
                 break;
     
               case 'phone':
                 aValue = (''+(aValue || '')).asPhone(funcParams[1]);
                 break;
     
               case 'cep':
                 aValue = (''+(aValue || '')).asCEP();
                 break;
     
               case 'nl2br':
                 aValue = aValue.replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + '<br>' + '$2');
                 break;
     
               case 'abbreviate':
                 aValue = aValue.abbreviate(funcParams[1] || 20, funcParams[2] || false);
                 break;
     
               case 'words':
                 var auxValue = aValue.split(' ');
     
                 var aStart = Math.max(0, str2int(funcParams[1]));
                 var aCount = Math.max(auxValue.length - 1, str2int(funcParams[2]));
                 var aWrap = Math.max(0, str2int(funcParams[3]));
     
                 aValue = '';
                 for (n = aStart; n < aStart + aCount; n++) {
                   var tmpValue = onlyDefinedValue(auxValue[n]);
                   if (tmpValue > '')
                     aValue += ' ' + tmpValue;
                 }
     
                 if (aWrap > 0)
                   aValue = wordwrap(aValue, aWrap, '<br>', true);
     
                 break;
               case 'quoted':
                 aValue = ('"' + aValue).trim() + '"';
                 break;
               case 'singleQuoted':
                 aValue = ("'" + aValue).trim() + "'";
                 break;
               case 'condLabel':
     
                 break;
               case 'checked':
                 aValue = countCheckedElements(aValue);
                 break;
     
               default:
                 if (funcName > '') {
                   if ('function' == typeof aObject[funcName]) {
                     aValue = aObject[funcName].apply(null, funcParams);
                   } else {
                     if ('object' == typeof aObject[funcName]) {
                       if ('function' == typeof aObject[funcName][aValue]) {
                         aValue = aObject[funcName][aValue].apply(null, funcParams);
                       } else {
                         aValue = aObject[funcName];
                       }
                     } else {
                       if ('undefined' !== typeof aObject[funcName]) {
                         aValue = aObject[funcName];
                       }
                     }
                   }
                   /*
                   if (eval('typeof '+funcName) == 'function') {
                     var parametros='';
                     for (var n=0; n<funcParams.length; n++) {
                       if (parametros>'')
                         parametros += ','
     
                       parametros += "'"+funcParams[n]+"'";
                     }
     
                     var chamada = '<script>'+funcName+'('+parametros+');</'+'script>';
                     aValue = chamada.evalScripts();
                   }*/
                 }
                 break;
             }
     
             aLine = aLine.slice(0, p) + aValue + aLine.slice(p + p2 + 1);
           } else {
             console.error("HALTING yAnalise as entering in loop");
             break;
           }
     
         }
     
         /* disabled until we can recognize quickly if is an HTML code */
     
         /*
         var needEval=false;
         var ops=['<', '>', '==', '!=', '<=', '>='];
         for(var i=0; i<ops.length; i++) {
           needEval|=(aLine.indexOf(ops[i])>=0);
         }
        
         if ((needEval) && (false)) {
           try{
             aLine=eval(aLine);
           } catch(err) {
     
           }
         }
         */
     
       } else
         aLine = '';
     
       return aLine;
     }
     
     /* YeAPF lexical analyser and parser  - Javascript implementation */
     var yLexObj = function(aString, toDebug) {
       "use strict";
       var that = {};
     
       that._debug = (toDebug || false) === true;
     
       that.optable = {
         '!': 'EXCLAMATION',
         // '"': 'DOUBLE_QUOTE',
         '#': 'NUMBER_SIGN',
         '$': 'DOLLAR',
         '%': 'MODULUS',
         '^': 'POWER',
         '&': 'AMPERSAND',
         '(': 'L_PAREN',
         ')': 'R_PAREN',
         '*': 'MULTIPLICATION',
         '+': 'ADDITION',
         ',': 'COMMA',
         '-': 'SUBSTRACTION',
         '.': 'PERIOD',
         '/': 'DIVISION',
         ':': 'COLON',
         ';': 'SEMICOLON',
         '<': 'LESS_THAN',
         '=': 'EQUALS',
         '>': 'GREATER_THAN',
         '?': 'QUESTION',
         '[': 'L_BRACKET',
         '\\': 'BACKSLASH',
         ']': 'R_BRACKET',
         '{': 'L_BRACE',
         '|': 'PIPE',
         '}': 'R_BRACE',
         '~': 'TILDE',
         '++': 'INCREMENT',
         '--': 'DECREMENT',
         '==': 'EQUAL2',
         '!=': 'NOT_EQUAL2',
         '>=': 'GREATER_EQUALS2',
         '<=': 'LESS_EQUALS2'
       };
     
       that.opprecedence = {
         'LIKE': 6,
         '<': 6,
         '>': 6,
         '<=': 6,
         '>=': 6,
         '==': 6,
     
         '^': 4,
         '/': 4,
         '*': 4,
         'AND': 4,
     
         'OR': 3,
     
         '+': 2,
         '-': 2,
     
         '(': 1,
       };
     
       that._ALPHA = 1;
       that._ALPHA_NUM = 2;
       that._NEW_LINE = 4;
       that._DIGIT = 8;
       that._QUOTE = 16;
     
       that.voidToken = {
         type: null,
         token: null,
         token_string: null,
         pos: null
       };
     
       that.error = function() {
         var ret = {};
         mergeObject(that.voidToken, ret);
         ret.type = 'ERROR';
         ret.pos = that.pos;
         return ret;
       };
     
     
       that.oneChar = function(offset) {
         offset = offset || 0;
         return that.buf.charAt(that.pos + offset);
       };
     
       that._isnewline = function(c) {
         c = that.oneChar();
         return (c === '\r' || c === '\n') ? that._NEW_LINE : 0;
       };
     
       that._isdigit = function(c) {
         return (c >= '0' && c <= '9') ? that._DIGIT : 0;
       };
     
       that._isalpha = function(c) {
         return ((c >= 'a' && c <= 'z') ||
           (c >= 'A' && c <= 'Z') ||
           (c === '_') || (c === '$')) ? that._ALPHA : 0;
       };
     
       that._isalphanum = function(c) {
         return that._isdigit(c) | that._isalpha(c);
       };
     
       that._isquote = function(c) {
         return ((c == "'") || (c == '"')) ? that._QUOTE : 0;
       };
     
       that._whatis = function(c) {
         return that._isalpha(c) | that._isdigit(c) | that._isquote(c);
       };
     
       that._process_quote = function() {
         var quote = that.oneChar(),
           lq_pos = that.buf.indexOf(quote, that.pos + 1),
           ret = that.error();
         if (lq_pos > that.pos) {
           ret = {
             type: 'LITERAL',
             token: that.buf.substring(that.pos + 1, lq_pos),
             pos: that.pos
           };
           ret.token_string = ret.token;
           that.pos = lq_pos + 1;
         }
         return ret;
       };
     
       that._process_identifier = function() {
         var lq_pos = 1,
           ret = that.error();
         while ((that.pos + lq_pos < that.buf.length) &&
           (that._isalpha(that.oneChar(lq_pos))))
           lq_pos++;
         ret = {
           type: 'IDENTIFIER',
           token: that.buf.substring(that.pos, that.pos + lq_pos),
           pos: that.pos
         };
         ret.token_string = ret.token;
         that.pos = that.pos + lq_pos;
         return ret;
       };
     
       that._process_number = function() {
         var lq_pos = 1,
           ret = that.error();
         while ((that.pos + lq_pos < that.buf.length) &&
           (that._isdigit(that.oneChar(lq_pos))))
           lq_pos++;
         ret = {
           type: 'NUMBER',
           token: that.buf.substring(that.pos, that.pos + lq_pos),
           pos: that.pos
         };
         ret.token_string = ret.token;
         that.pos = that.pos + lq_pos;
         return ret;
       };
     
       that.getToken = function() {
         var c,
           ret = that.error(),
           sep = ' \t\r\n';
     
         /* jump to next valid oneChar */
         while (that.pos < that.buf.length) {
           c = that.oneChar();
           if (sep.indexOf(c) > -1) {
             that.pos++;
           } else {
             break;
           }
         }
     
         /* if still into string */
         if (that.pos < that.buf.length) {
           var canProcessOp = true;
           if (c == '/') {
             if (that.oneChar(1) == '*') {
     
             } else if (that.oneChar(1) == '/') {
     
             }
           }
     
           if (canProcessOp) {
             var op = that.optable[c];
             if (op === undefined) {
               switch (that._whatis(c)) {
                 case (that._ALPHA):
                   ret = that._process_identifier();
                   var auxToken = String(ret.token_string).toUpperCase();
                   if ((auxToken == 'AND') || (auxToken == 'OR') || (auxToken == 'LIKE')) {
                     ret.token_string = auxToken;
                     ret.type = 'OPERATOR';
                   }
                   break;
                 case (that._DIGIT):
                   ret = that._process_number();
                   break;
                 case (that._QUOTE):
                   ret = that._process_quote();
                   break;
               }
             } else {
               var _type = 'OPERATOR',
                 _token_string = c,
                 c1 = that.oneChar(1),
                 r1 = that.voidToken,
                 _pos = that.pos;
               if (that.optable[c + c1]) {
                 c = c + c1;
                 op = that.optable[c];
                 _token_string = c;
               } else if ('-+'.indexOf(c) >= 0) {
                 var ptt = that.priorToken.type;
                 if ((ptt === null) || ((ptt == 'OPERATOR') && (that.priorToken.token == "L_PAREN"))) {
                   var c1t = that._whatis(c1);
                   if (c1t == that._DIGIT) {
                     r1 = that._process_number();
                   } else if (c1t == that._ALPHA)
                     r1 = that._process_identifier();
                 }
               }
     
               ret = {
                 type: r1.type || _type,
                 token: r1.token || op,
                 pos: _pos,
                 token_string: r1.token_string || _token_string
               };
               that.pos += c.length;
             }
           }
         } else {
           ret.type = 'EOF';
           ret.token = null;
         }
         that.priorToken = ret;
         return ret;
       };
     
       that.tokenTypeIs = function(token, expectedTypes) {
         expectedTypes = ',' + expectedTypes + ',';
         return (expectedTypes.indexOf(',' + token.type + ',') >= 0);
       };
     
       that.getExpectedToken = function(expectedTypes) {
         var priorPos = that.pos;
         var token = that.getToken();
         if (that.tokenTypeIs(token, expectedTypes)) {
           return token;
         } else {
           that.pos = priorPos;
           return false;
         }
       };
     
       that._analiseText = function() {
         var token, lastSym = that.voidToken,
           pct, ppt, itemSolved;
         do {
           itemSolved = false;
           token = that.getToken();
           if (token && token.type != "EOF") {
             if (that.symStack.length > 0)
               lastSym = that.symStack[that.symStack.length - 1];
             lastSym = lastSym || that.voidToken;
     
             if (token.token_string == '(') {
               that.symStack.push(token);
             } else if (token.token_string == ')') {
               lastSym = that.symStack.pop();
               while ((lastSym) && (lastSym.token_string != '(')) {
                 that.postFixStack.push(lastSym);
                 lastSym = that.symStack.pop();
               }
             } else if (token.type == 'OPERATOR') {
               var canTransfer = true;
               while (canTransfer) {
                 canTransfer = false;
                 if (that.symStack.length > 0) {
                   lastSym = that.symStack[that.symStack.length - 1];
                   lastSym = lastSym || that.voidToken;
     
                   pct = that.opprecedence[token.token_string] || 99;
                   ppt = that.opprecedence[lastSym.token_string] || 10;
                   if (ppt > pct) {
                     canTransfer = true;
                     lastSym = that.symStack.pop();
                     that.postFixStack.push(lastSym);
                   }
                 }
               }
               that.symStack.push(token);
     
             } else {
               that.postFixStack.push(token);
             }
     
           }
     
         } while (!((token.type == 'ERROR') || (token.type == 'EOF')));
     
         do {
           lastSym = that.symStack.pop();
           if ((lastSym) && (lastSym.type != 'EOF'))
             that.postFixStack.push(lastSym);
         } while ((lastSym) && (lastSym.type != 'EOF'));
     
         if (that._debug) {
           _dumpy(32,2,"postFixStack:");
           that.showStack(that.postFixStack);
           _dumpy(32,2,"symStack:");
           that.showStack(that.symStack);
         }
       };
     
       that.solve = function(data) {
         var i, stack = [],
           token, aux, canPush, op1, op2, ret, noErrors = true,
           errorMessage='';
         data = data || {};
         data['true'] = true;
         data['false'] = false;
         for (i = 0;
           (i < that.postFixStack.length) && (noErrors); i++) {
           canPush = false;
     
           token = that.postFixStack[i];
           if (token) {
             if ((token.type == 'NUMBER') || (token.type == 'LITERAL')) {
               aux = token.token_string;
               if (!isNumber(aux))
                 aux = String(aux).toUpperCase();
               canPush = true;
             }
             if (token.type == 'IDENTIFIER') {
               aux = data[token.token_string];
               if (typeof aux == 'undefined') {
                 errorMessage = "'" + token.token_string + "' is not defined on data";
                 _dumpy(32,1,errorMessage);
                 aux = false;
                 canPush = true;
               } else {
                 if (typeof aux == "string")
                   aux = String(aux).toUpperCase();
                 canPush = true;
               }
             }
     
             if (canPush) {
               stack.push(aux);
             } else {
               op2 = stack.pop();
               op1 = stack.pop();
               ret = null;
               switch (("" + token.token_string).toUpperCase()) {
                 case '+':
                   ret = str2double(op1) + str2double(op2);
                   break;
                 case '-':
                   ret = str2double(op1) - str2double(op2);
                   break;
                 case '*':
                   ret = str2double(op1) * str2double(op2);
                   break;
                 case '/':
                   ret = str2double(op1) / str2double(op2);
                   break;
                 case '^':
                   ret = Math.pow(str2double(op1), str2double(op2));
                   break;
                 case '>':
                   ret = op1 > op2;
                   break;
                 case '<':
                   ret = op1 < op2;
                   break;
                 case '>=':
                   ret = op1 >= op2;
                   break;
                 case '<=':
                   ret = op1 <= op2;
                   break;
                 case '<>':
                 case '!=':
                   ret = op1 != op2;
                   break;
                 case '==':
                   ret = op1 == op2;
                   break;
                 case 'AND':
                 case '&&':
                   ret = op1 && op2;
                   break;
                 case 'OR':
                 case '||':
                   ret = op1 || op2;
                   break;
     
                 case 'LIKE':
                   op1 = String(op1).toUpperCase();
                   op2 = String(op2).toUpperCase();
                   var cleanFilter = op2.replace(/\%/g, '');
                   if (op2.substr(0, 1) != '%') {
                     if (op2.substr(op2.length - 1) != '%') {
                       /* bla */
                       ret = op1 == op2;
                     } else {
                       /* bla% */
                       ret = op1.substr(0, cleanFilter.length) == cleanFilter;
                     }
                   } else {
                     if (op2.substr(op2.length - 1, 1) == '%') {
                       /* %bla% */
                       ret = op1.indexOf(cleanFilter) >= 0;
                     } else {
                       /* %bla */
                       ret = op1.substr(op1.length - cleanFilter.length) == cleanFilter;
                     }
                   }
                   break;
                 default:
                   errorMessage = "'" + token.token_string + "' is not a recognized operator";
                   console.error(errorMessage);
                   throw new Error();
               }
     
     
               if (that._debug)
                 _dumpy(32,2,"{0} = {1} {2} {3}".format(ret, op1, token.token_string, op2));
     
     
               if (ret !== null)
                 stack.push(ret);
             }
           }
         }
         ret = stack.pop();
     
         if (that._debug) _dumpy(32,2,JSON.stringify(ret));
     
         return ret;
       };
     
       that.showStack = function(s) {
         var stackString = "\t";
         for (var i = 0; i < s.length; i++)
           stackString += (s[i].token_string) + ' ';
         _dumpy(32,2,stackString);
       };
     
       that.parse = function() {
         that.reset();
         that._analiseText();
         return that.stack;
       };
     
       that.reset = function() {
         that.pos = 0;
         that.symStack = [];
         that.postFixStack = [];
         that.priorToken = that.voidToken;
         return that;
       };
     
       that.init = function(aString) {
         that.buf = aString || that.buf || "";
         that.parse();
         return that;
       };
     
       return that.init(aString);
     };
 /* END yanalise.js */
 _dump("yanalise");
 /* START ycfgdb.js */
     /*********************************************
      * First Version (C) 2014 - esteban daniel dortta - dortta@yahoo.com
     **********************************************/
     
     
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
     
 /* END ycfgdb.js */
 _dump("ycfgdb");
 /* START ydragdrop.js */
     /********************************************************************
      *
      * Drag and Drop functions were modified from luke beuer ideas at
      * http://luke.breuer.com/tutorial/javascript-drag-and-drop-tutorial.aspx
      * Available cursors can be catched here:
      * http://help.dottoro.com/ljbknbcd.php
      ********************************************************************/
     
     
     var ydragdropBase = function() {
       var that = { };
     
       that.info = {
         startX:  0,
         startY:  0,
         offsetX: 0,
         offsetY: 0,
         dragElement: null,
         overElement: null,
         oldZIndex: 0,
         lastHighLight: null
       };
     
       that.highlight = function(e) {
     
         if (that.info.lastHighLight!=null)
           that.info.lastHighLight.deleteClass('highlight');
     
         that.info.lastHighLight = e;
     
     
         if (e) {
           that.info.lastHighLight.addClass('highlight');
         }
       }
     
       that.getTarget = function (e) {
         if (!e)
           var e = window.event;
         // IE uses srcElement, others use target
         if (e.target) return e.target;
         else if (e.srcElement) return e.srcElement
         else return window.event;
       }
     
       that.onMouseDown = function(e) {
         // IE is retarded and doesn't pass the event object
         if (e == null)
           e = window.event;
     
     
         var target = that.getTarget(e);
     
         if (target.className == 'caption')
           target = target.parentNode;
         if (target.className == 'title')
           target = target.parentNode;
     
         /*
          * _dumpy(2,1,target.getAttribute('draggable') == 'yes' ? 'draggable element clicked' : 'NON-draggable element clicked');
          */
     
         // for IE, left click == 1
         // for Firefox, left click == 0
         if ((e.button == 1 && window.event != null || e.button == 0) && target.getAttribute('draggable') == 'yes') {
     
           document.body.style.cursor = "move";
     
           // grab the mouse position
           that.info.startX = e.clientX;
           that.info.startY = e.clientY;
           // grab the clicked element's position
           that.info.offsetX = str2int(target.style.left);
           that.info.offsetY = str2int(target.style.top);
           // bring the clicked element to the front while it is being dragged
           that.info.oldZIndex = target.style.zIndex;
     
           /*
           OBSOLETE-2018-11-20
           var maxZ = 0;
           var divList = document.querySelectorAll('[draggable="yes"]');
           for (var i = 0; i < divList.length; i++) {
             var aDiv = divList[i];
             if (parseInt(aDiv.style.zIndex) > maxZ)
               maxZ = parseInt(aDiv.style.zIndex);
           }
     
           for (var i = 0; i < divList.length; i++) {
             var aDiv = divList[i];
             aDiv.style.zIndex = Math.max(0, parseInt(aDiv.style.zIndex) - 1);
           }
           target.style.zIndex = maxZ + 2;
           */
     
           // we need to access the element in OnMouseMove
           that.info.dragElement = target;
           // cancel out any text selections
           document.body.focus();
           // prevent text selection in IE
           document.onselectstart = function () {
             return false;
           };
           // prevent IE from trying to drag an image
           target.ondragstart = function () {
             return false;
           };
           // prevent text selection (except IE)
           return false;
         }
     
       };
     
       that.onMouseMove = function(e) {
         if (!e)
           var e = window.event;
         var x = e.clientX,
             y = e.clientY,
             overElement = document.elementFromPoint(x, y);
     
     
         if (e == null)
           var e = window.event;
     
         if (that.info.dragElement != null) {
     
           that.info.overElement = overElement;
     
           // this is the actual "drag code"
           that.info.dragElement.style.left = (that.info.offsetX + x - that.info.startX) + 'px';
           that.info.dragElement.style.top = (that.info.offsetY + y - that.info.startY) + 'px';
           /*
            * _dumpy(2,1,'(' + that.info.dragElement.style.left + ', ' + that.info.dragElement.style.top + that.info.dragElement.style.zIndex + ')');
            */
     
           var canDo = true;
     
           if (overElement) {
             canDo = overElement.getAttribute('droppable')=='yes';
             if (typeof overElement.ondragover == 'function') {
               canDo = overElement.ondragover(that.info.dragElement);
             }
             if (canDo)
               that.highlight(overElement);
             else
               that.highlight(null);
           }
     
           if (canDo)
             document.body.style.cursor = "crosshair";
           else
             document.body.style.cursor = "move";
         } else {
           if (overElement) {
     
             if (document.body) {
               if (overElement.getAttribute('draggable') == 'yes') {
                 document.body.style.cursor = "pointer";
               } else
                 document.body.style.cursor = "default";
             }
           }
         }
       }
     
       that.onMouseUp = function(e) {
         if (!e)
           var e = window.event;
         if (that.info.dragElement != null) {
           document.body.style.cursor = "default";
           /*
           OBSOLETE-2018-11-20
           that.info.dragElement.style.zIndex = Math.max(0,parseInt(that.info.dragElement.style.zIndex)-1);
           */
     
           // we're done with these events until the next OnMouseDown
           document.onselectstart = null;
           that.info.dragElement.ondragstart = null;
     
           that.highlight(null);
     
           var aux = that.info.dragElement;
           // this is how we know we're not dragging
           that.info.dragElement = null;
           if (that.info.overElement) {
             _dumpy(2,1,'mouse up over'+that.info.overElement.id);
     
             var canDo = that.info.overElement.getAttribute('droppable') == 'yes';
             if (canDo) {
               if (typeof that.info.overElement.ondragover == 'function') {
                 canDo = that.info.overElement.ondragover(aux);
               }
               if (typeof that.info.overElement.ondrop == 'function') {
                 if (canDo)
                   that.info.overElement.ondrop(aux);
               }
             }        
           }
     
         }
     
       };
     
     
       if (typeof document=='object') {
         document.onmousedown = that.onMouseDown;
         document.onmouseup = that.onMouseUp;
         document.onmousemove = that.onMouseMove;
       }
       return that;
     }
     
     var ydragdrop = ydragdropBase();
     
 /* END ydragdrop.js */
 _dump("ydragdrop");
 /* START ytabnav.js */
     /*********************************************
      * First Version (C) 2012 - esteban daniel dortta - dortta@yahoo.com
      * Purpose: to control multiple tabs in a only-one-page application
      *          this is specially useful when building web mobile applications
     **********************************************/
     
     var tabNavBase = function () {
       var that = {};
     
       if ("undefined" !== typeof createDOMEvent) {
         that.tabchangeEvent = createDOMEvent('tabchange');
         that.tabblurEvent   = createDOMEvent('tabblur');
         that.tabfocusEvent  = createDOMEvent('tabfocus');
         that.tabshowEvent   = createDOMEvent('tabshow');
       }
     
       /*
       OBSOLETE 2018-01-24
       if (isOnMobile()) {
         _dump("Loading mobile tabs");
         that.tabchangeEvent = document.createEvent('Event');
         that.tabchangeEvent.initEvent('tabchange', true, true);
     
         that.tabblurEvent = document.createEvent('Event');
         that.tabblurEvent.initEvent('tabblur', true, true);
     
         that.tabfocusEvent = document.createEvent('Event');
         that.tabfocusEvent.initEvent('tabfocus', true, true);
     
         that.tabshowEvent = document.createEvent('Event');
         that.tabshowEvent.initEvent('tabshow', true, true);
       } else {
         if ((typeof Event=='function') || (typeof Event=='object')) {
           _dump("Loading desktop tabs");
           that.tabchangeEvent = new Event('tabchange');
           that.tabblurEvent = new Event('tabblur');
           that.tabfocusEvent = new Event('tabfocus');
           that.tabshowEvent = new Event('tabshow');
         } else
           _dump("Tabs are not supported");
       }
       */
     
       that.currentTabNdx = -1;
       that.currentContainerNdx = -1;
       that.containerList = [];
       that.tabList = [];
     
       that.storage = null;
     
       that.initialized = -1;
       that.lock = {
         returnTabId: null,
         locked: false
       };
     
       that.isContainer = function (aDiv) {
         var ret = false;
         if (aDiv) {
           ret = aDiv.hasClass('tnContainer');
         }
         return ret;
       };
     
       that.isTab = function (aDiv) {
         var ret = false;
         if (aDiv) {
           ret = aDiv.hasClass('tnTab');
         }
         return ret;
       };
     
       that.getContainer = function (aContainerNdx) {
         return that.containerList[aContainerNdx];
       }
     
       that.getCurrentContainer = function () {
         return that.getContainer(that.currentContainerNdx);
       }
     
       that.getContainerById = function (aContainerId) {
         var ret=null;
         for(var i=0; i < that.containerList.length; i++) {
           if (that.containerList[i].element.id==aContainerId)
             ret = that.containerList[i];
         }
     
         return ret;
       }
     
       that.getTabContainer = function (aTabId) {
         var ret=null;
         if (aTabId) {
           for(var i=0; (i < that.containerList.length) && (ret==null); i++) {
             for(var n=0; (n < that.containerList[i].childs.length) && (ret==null); n++) {
               if (typeof aTabId=='string') {
                 if (that.containerList[i].childs[n].id == aTabId) {
                   ret = that.containerList[i];
                 }
               } else {
                 if (that.containerList[i].childs[n] == aTabId) {
                   ret = that.containerList[i];
                 }
               }
             }
           }
         }
         return ret;
       }
     
       that.getFirstTabInContainer = function(aContainer) {
         var ret=null, myContainer;
         if (typeof aContainer == 'string')
           aContainer=y$(aContainer);
     
         if (that.isTab(aContainer))
           myContainer=that.getTabContainer(aContainer);
         else {
           var containerNdx=that.getContainerNdx(aContainer);
           if (containerNdx>=0)
             myContainer=that.containerList[containerNdx];
         }
     
         if (myContainer)
           ret=myContainer.childs[0];
     
         return ret;
       }
     
       that.getFirstChildTab = function(aTab) {
         var ret=null, myContainer;
         if (typeof aTab == 'string')
           aTab=y$(aTab);
     
         if (that.isTab(aTab)) {
           var aContainerList=aTab.getElementsByClassName('tnContainer');
           if (aContainerList.hasOwnProperty('0')) {
             var containerNdx=that.getContainerNdx(aContainerList[0]);
             if (containerNdx>=0)
               ret=that.containerList[containerNdx].childs[0];
           } else
             ret=aTab;
     
         }
         return ret;
       }
     
       that.getContainerFromParam = function (aContainer, aTabId) {
         if (aContainer==undefined) {
           if (aTabId==undefined)
             aContainer = that.getCurrentContainer();
           else
             aContainer = that.getTabContainer(aTabId);
     
         } else if (typeof aContainer == 'string') {
           aContainer = that.getContainerById(aContainer);
         } else if (isNumber(aContainer)) {
           aContainer = that.getContainer(aContainer);
         } else if (typeof aContainer != 'object') {
           _dump("getContainerFromParam() parameter is not null, valid string, object nor a number");
           aContainer = null;
         }
         return aContainer;
       }
     
       that.getCurrentTabNdx = function(aContainer) {
         var ret = -1;
         aContainer = that.getContainerFromParam(aContainer);
         if (aContainer) {
           ret = aContainer.currentTabNdx;
         }
     
         return ret;
       }
     
       that.setCurrentContainer = function (aNewContainerNdx) {
         if (that.initialized < 0)
           that.init();
         that.currentContainerNdx = aNewContainerNdx % that.containerList.length;
       }
     
       that.getContainerNdx = function (aTab) {
         var ret=-1;
         for (var i=0; i<that.containerList.length; i++) {
           if (that.containerList[i].element==aTab)
             ret=i;
         }
         return ret;
       }
     
       that.addContainer = function (aContainer) {
         if (that.initialized < 0)
           that.init();
         if (aContainer) {
           var ndx = that.getContainerNdx(aContainer);
           if (ndx<0) {
             ndx = that.containerList.length;
             that.containerList[ndx] = {
               childs: [],
               element: aContainer,
               currentTabNdx: -1
             }
           } else {
             that.containerList[ndx].childs = [];
           }
     
           var auxTabList=aContainer.getElementsByClassName('tnTab');
           for(var i in auxTabList)
             if (auxTabList.hasOwnProperty(i)) {
               if (typeof auxTabList[i]=='object') {
                 var l=that.containerList[ndx].childs.length;
                 that.containerList[ndx].childs[l]=auxTabList[i];
                 that.hideTab(auxTabList[i]);
               }
             }
     
           that.currentContainerNdx = ndx;
     
         }
       };
     
       that.addTab = function (aTab) {
         if (that.initialized < 0)
           that.init();
         if (aTab) {
           var aux = that.getCurrentContainer().childs;
           if (aux.indexOf(aTab)<0)
             aux[aux.length] = aTab;
         }
       };
     
       that.init = function (aDivContainer) {
         if (that.initialized < 0) {
           _dump("Initializing tabs");
     
           that.initialized = 0;
     
           var allContainers = y$('tnContainer'),
               firstTab = null, aDiv = null,
               i = 0;
           if (allContainers) {
             for (i=0; i<allContainers.length; i++) {
               aDiv=allContainers[i];
               that.addContainer(aDiv);
             }
           } else
             _dump("ERROR: No containers defined. Use 'tnContainer' class on a DIV");
     
           var allTabs = y$('tnTab');
           if (allTabs) {
             for(var i=0; i<allTabs.length; i++)
               that.hideTab(allTabs[i]);
           } else
             _dump("ERROR: No tabs defined. Use 'tnTab' class on a DIV");
     
           if (that.containerList.length>0) {
             firstTab=that.containerList[0].childs[0];
             that.displayTab(that.getFirstChildTab(firstTab));
           }
     
           that.currentContainerNdx = 0;
           that.initialized = 1;
         }
         if (ycomm)
           ycomm.setWaitIconControl(that.waitIconControl);
         return that;
       };
     
       that.currentTabId = function() {
         return that._currentTab || '';
       };
     
       that.currentTab = function () {
         return y$(that.currentTabId());
         /*
         var theContainer = that.getCurrentContainer();
         if (theContainer.currentTabNdx>-1) {
           return theContainer.childs[theContainer.currentTabNdx];
         } else
           return null;
         */
       };
     
       that.createTab = function (aDivContainer, aNewTabId) {
         var aDiv = null;
         if (y$(aNewTabId)==undefined) {
           aDiv = document.createElement('div');
           aDiv.className='tnTab';
           aDiv.style.display='none';
           aDiv.id=aNewTabId;
     
           aDivContainer.appendChild(aDiv);
     
           /* criar sob containerList */
     
           that.addTab(aDiv);
           that.hideTab(aDiv);
     
         }
         return aDiv;
       }
     
       that.delTab = function (aTab) {
       };
     
       that.displayTab = function (aTab, aContainer) {
         if (!that.changingView) {
           that.changingView=true;
           var styleDisplay='';
           try {
             if (!that.locked()) {
               if (aTab) {
                 if (that.initialized < 0)
                   that.init();
                 _dumpy(64,1,"displayTab "+aTab.id);
                 var canChange = true,
                     i = 0;
                 canChange = aTab.dispatchEvent(that.tabchangeEvent) || canChange;
                 /*
                 if (that.ontabchange != undefined)
                   canChange = that.ontabchange(aTab);
                 */
                 if (canChange) {
                   var theContainer = that.getContainerFromParam(aContainer);
                   if (theContainer) {
     
                     _dumpy(64,1,"canchange");
                     var aNdx = -1;
                     var freeze = false;
     
                     for(i = 0; i < theContainer.childs.length; i++) {
                       if (theContainer.childs[i] != aTab)
                         freeze |= !(that.hideTab(theContainer.childs[i], aTab, theContainer));
                       else
                         aNdx = i;
                     }
                     _dumpy(64,1,"readytochange "+!freeze);
                     if (!freeze) {
                       that.setCurrentContainer(that.getContainerNdx(theContainer));
                       theContainer.currentTabNdx = aNdx;
                       that._currentTab=aTab.id;
                       aTab.dispatchEvent(that.tabfocusEvent);
                       /*
                       if (that.ontabfocus != undefined)
                         that.ontabfocus(aTab);
                       */
                       aTab.style.display = styleDisplay;
                       var auxNode=aTab;
                       while ((auxNode) && (auxNode!=document.body)) {
                         auxNode.style.display = styleDisplay;
                         auxNode=auxNode.parentNode;
                       }
                       var elems=aTab.getElementsByTagName('*');;
                       i=0;
                       while (i<elems.length) {
                         if ((elems[i].type=='checkbox') || (elems[i].type=='radio') || (elems[i].type=='password') || (elems[i].type=='hidden') || (elems[i].type=='text') || (elems[i].type=='select-one') || (elems[i].type=='textarea')) {
                           elems[i].focus();
                           break;
                         }
                         i++;
                       }
     
                       setTimeout(function(){window.dispatchEvent(that.tabshowEvent);}, 125);
     
                     } else {
                       _dumpy(64,1,"freeze");
                     }
                   }
                 }
                 _dumpy(64,1,"return");
               }
             }
           } finally {
             that.changingView=false;
           }
         }
       };
     
       that.showWaitIcon = function () {
         if (y$('waitIcon')) {
           if (isPropertySupported('opacity')) {
             y$('waitIcon').style.opacity='.99';
           }
           y$('waitIcon').style.display='';
         }
       };
     
       that.hideWaitIcon = function () {
         if (y$('waitIcon')) {
           if (isPropertySupported('opacity')) {
             y$('waitIcon').style.opacity=0;
           }
           y$('waitIcon').style.display='none';
         }
       };
     
       that.waitIconControl = function (display) {
         if (display!=undefined) {
           if (display)
             that.showWaitIcon();
           else
             that.hideWaitIcon();
         }
       }
     
       that.isInnerTab = function(aTabToBeShowed, aCurrentTab) {
         var ret = false;
         if (aTabToBeShowed) {
           var aTab = aTabToBeShowed;
           while ( (aTab) && (aTab.parent != aTab) ) {
             if (aCurrentTab == aTab)
               ret = true;
             aTab = aTab.parentNode;
           }
         }
         return ret;
       }
     
       that.hideTab = function (aTab, aTabToBeShowed, aContainer) {
         if (!that.locked()) {
           _dumpy(64,1,"hideTab "+aTab.id);
           var ret = true;
           var theContainer = that.getContainerFromParam(aContainer);
           if (theContainer) {
             if (theContainer.childs.indexOf(aTab) == theContainer.currentTabNdx) {
               ret = aTab.dispatchEvent(that.tabblurEvent) || ret;
               /*
               if (that.ontabblur != undefined)
                 ret = that.ontabblur(aTab, aTabToBeShowed);
               */
               if (ret)
                 theContainer.currentTabNdx = -1;
             } else
               ret = true;
           }
           if (ret) {
             if (typeof aTab=='object')
               if (!that.isInnerTab(aTabToBeShowed, aTab))
                 aTab.style.display = 'none';
           }
           return ret;
         }
       };
     
       that.showNext =  function (aContainer) {
         aContainer = that.getContainerFromParam(aContainer);
         if (aContainer) {
           var currentTabNdx = aContainer.currentTabNdx;
           if (currentTabNdx<aContainer.childs.length-1)
             that.displayTab(aContainer.childs[currentTabNdx+1], aContainer);
           else
             that.displayTab(aContainer.childs[0], aContainer);
         }
       };
     
       that.showPrior = function (aContainer) {
         aContainer = that.getContainerFromParam(aContainer);
         if (aContainer) {
           var currentTabNdx = aContainer.currentTabNdx;
           if (currentTabNdx>0)
             that.displayTab(aContainer.childs[currentTabNdx-1], aContainer);
           else
             that.displayTab(aContainer.childs[aContainer.childs.length-1], aContainer);
         }
       };
     
       that.getCurrentTabId = function (aContainer) {
         var ret = null;
         aContainer = that.getContainerFromParam(aContainer);
         if (aContainer) {
           var currentTabNdx = aContainer.currentTabNdx;
           if (currentTabNdx>-1)
             ret = aContainer.childs[currentTabNdx].id;
         }
         return ret;
       }
     
       that.showTab = function (aTabId, aLockTabAfterShow, aContainer) {
         if (!that.locked()) {
           var theContainer = that.getContainerFromParam(aContainer, aTabId);
           if (aTabId == undefined) {
             aTabId = theContainer.childs[0].id;
           }
           if (aLockTabAfterShow==undefined)
             aLockTabAfterShow=false;
     
           var aTab = document.getElementById(aTabId);
           var priorTabId = '';
           if (aTab) {
             if (aLockTabAfterShow) {
               priorTabId = that.getCurrentTabId(theContainer);
             }
             that.displayTab(aTab, theContainer);
             if (aLockTabAfterShow)
               that.lockTab(aTabId, priorTabId);
           } else
             alert(aTabId+" not found");
         }
       };
     
       that.locked = function () {
         return that.lock.locked;
       }
     
       that.releaseLockedTabs = function () {
         for(var i=0; i<that.getCurrentContainer().childs.length; i++) {
           if (that.getCurrentContainer().childs[i].locked)
             that.getCurrentContainer().childs[i].locked=false;
         }
         that.lock.locked=false;
         that.lock.returnTabId=null;
       }
     
       that.lockTab = function (aTabId, aReturnTabId) {
         if (that.locked())
           that.releaseLockedTabs();
         if (y$(aTabId)) {
           that.lock.locked = true;
           y$(aTabId).locked = true;
           that.lock.returnTabId = y$(aReturnTabId)?aReturnTabId:null;
         }
       }
     
       that.unlockTab = function (aTabId) {
         if (that.locked()) {
           if (y$(aTabId)) {
             if (y$(aTabId).locked) {
               var nextTabId = that.lock.returnTabId;
               that.releaseLockedTabs();
               if (nextTabId!=null)
                 that.showTab(nextTabId);
             }
           }
         }
       }
       return that;
     }
     
     var mTabNav = tabNavBase();
     
     
     
 /* END ytabnav.js */
 _dump("ytabnav");
 /* START ycomm.js */
     /*********************************************
      * First Version (C) 2010 - esteban daniel dortta - dortta@yahoo.com
     **********************************************/
     
     
       function processError(xError)
       {
         var errNo=xError.errNo;
         var errMsg=xError.errMsg;
         var errDetail=xError.errDetail;
         if (typeof errDetail != 'string') {
           var d1=array2text(errDetail['sys.stack'],false);
           if (d1 !== undefined)
             d1='\n==[stack]===================================\n'+d1;
           var d2=errDetail['sys.sqlTrace'];
           if (d2 !== undefined)
             d2='\n==[sql]===================================\n'+d2;
           var d3=errDetail['sys.sqlError'];
           errDetail=d3+d2+d1;
         }
     
         return 'Err #'+errNo+'\n-------- '+errMsg+'\n-------- '+errDetail;
       }
     
     
       var yRestTimeControl = function (initialRestTime) {
         var that = { };
     
         that.setRestTime = function (aValue) {
           that._restTime = Math.min( Math.max(125, aValue), 240 * 60 * 1000);
         };
     
         that.adjustRestTime = function (t1) {
           var t2, tDif, interleaveDif, aux;
           t2 = (new Date()).getTime();
           tDif = t2 - t1;
           aux = that._restTime - (that._restTime - tDif) / 2;
           that.setRestTime(aux);
         };
     
         that.init = function () {
           that.setRestTime(initialRestTime);
           Object.defineProperty(
             that,
             "restTime",
             {
               get: function () { return that._restTime; },
               set: that.setRestTime
             }
           );
           return that;
         };
     
         return that.init();
       };
     
       var ycommBase = function () {
         var that = {};
     
     /*http://www.blooberry.com/indexdot/html/topics/urlencoding.htm*/
     
         that.urlCodification = {
             '%20' : ' ',
             '%21' : '!',
             '%2A' : '*',
             '%27' : "'",
             '%28' : '(',
             '%29' : ')',
             '%3B' : ';',
             '%3A' : ':',
             '%40' : '@',
             '%26' : '&',
             '%3D' : '=',
             '%2B' : '+',
             '%24' : '$',
             /* '%25' : '%', cannot be at list as it corrupts the process */
             '%2C' : ',',
             '%2F' : '/',
             '%3F' : '?',
             '%23' : '#',
             '%5B' : '[' ,
             '%5D' : ']' ,
             '%22' : '"',
             '%27' : "'"};
     
         that._AsyncMode=true;
     
         that._dummyWaitIconControl = function () {};
     
         // sets async mode.  defaults to true
         that.setAsyncMode = function (aAsyncMode) {
             if (aAsyncMode === undefined)
               aAsyncMode=true;
     
             _AsyncMode=aAsyncMode;
         };
     
         that.xq_urlEncode = function(aURL, aQuoted) {
           if (typeof aQuoted=='undefined')
             aQuoted=true;
           if ((typeof aURL=='string') && (aURL>'')) {
             /* '%' need to be changed first */
             aURL=aURL.replace(/%/g,'%25');
             /* ',' must be escaped */
             aURL=aURL.replace(/,/g,'\\,');
             for(var n in that.urlCodification)
               if (that.urlCodification.hasOwnProperty(n)) {
                 var re = new RegExp(escapeRegExp(that.urlCodification[n]), 'g');
                 aURL = aURL.replace(re, n);
               }
     
             if (!((aURL.substring(0,1)=="'") || (aURL.substring(0,1)=='"')))
               if (!isNumber(aURL))
                 if (aQuoted)
                   aURL='"'+aURL+'"';
           }
           return aURL;
         };
     
         that.urlJsonAsParams  = function(jsonParams) {
           var fieldName='';
           var fieldValue='';
           var auxFieldValue='';
           for(var jNdx in jsonParams) {
             if (jsonParams.hasOwnProperty(jNdx)) {
               if (fieldName>'') {
                 fieldName+=',';
                 fieldValue+=',';
               }
     
               fieldName += jNdx;
               auxFieldValue = maskHTML(that.xq_urlEncode(jsonParams[jNdx], false));
               fieldValue += auxFieldValue;
             }
           }
           fieldName='('+fieldName+')';
           fieldValue='('+fieldValue+')';
           return [fieldName, fieldValue];
         };
     
         that.buildCommonURL = function (s, a, jsonParams, u) {
     
           if (typeof jsonParams == 'undefined')
             jsonParams = {};
     
           var jsonAsParams=that.urlJsonAsParams(jsonParams);
           var fieldName=jsonAsParams[0];
           var fieldValue=jsonAsParams[1];
     
           if (u===undefined)
             u='';
     
           var aURL="s={0}&a={1}&u={2}&fieldName={3}&fieldValue={4}".format(s, a, u || '', fieldName, fieldValue);
     
           var ts=(new Date()).getTime();
           aURL+='&ts='+ts;
           aURL+='&_rap_'+ts+'=1';
           // aURL=aURL.replace('%','%25');
           return aURL;
         };
     
         that.setWaitIconControl = function (aFunction) {
           that.waitIconControl = aFunction || that._dummyWaitIconControl;
         };
     
         that.pinger = {
             canPing: false,
             pingerWatchdog: null,
             pingCount: 0,
             pingTimeout: 15 * 1000,
             pingInterleave: 1500,
             onSuccess: null,
             onError: null,
     
             pong : function(aStatus, aError, aData) {
               if (that.pinger.pingerWatchdog) clearTimeout(that.pinger.pingerWatchdog);
               _dumpy(4,1,"ping answer loc:{0} rem:{1}".format(that.pinger.pingCount, aData.pingCount || 0));
               if (that.pinger.pingCount<=aData.pingCount) {
                 that.pinger.pingCount=0;
               }
               _dumpy(4,1,"ping answer received {0}/{1}".format(that.pinger.pingCount, aData.pingCount));
               if (that.pinger.onSuccess !== null)
                 that.pinger.onSuccess();
               if (that.pinger.canPing)
                 that.pinger.pingerWatchdog = setTimeout(that.pinger.ping, that.pinger.pingInterleave);
             },
             /*
              * apÃ³s um tempo de 60 segundos (pingTimeout)
              * sem resposta, ele cai nesta funÃ§Ã£o e
              * volta a tentar em 1/2 pingInterleave
              */
             notAnswer: function () {
               if (that.pinger.pingerWatchdog) clearTimeout(that.pinger.pingerWatchdog);
               _dumpy(4,1,"ping timeout");
               if (that.pinger.onError !== null)
                 that.pinger.onError();
               else
                 _dumpy(4,1,"ping without 'onError' event");
               // sayStatusBar("Servidor nÃ£o localizado "+that.pinger.pingCount+'...<br>Tentando novamente');
               if (that.pinger.canPing) {
                 _dumpy(4,1,"ping Scheduling a new call for {0}ms".format(that.pinger.pingInterleave / 2))
                 that.pinger.pingerWatchdog=setTimeout(that.pinger.ping, that.pinger.pingInterleave / 2);
               }
             },
     
             /*
              * tenta localizar o servidor.  manda um numero.
              * ele retorna o mesmo numero mais um timestamp
              */
             ping: function (aOnSuccess, aOnError, keepPinging) {
               if (that.pinger.pingerWatchdog) clearTimeout(that.pinger.pingerWatchdog);
               _dumpy(4,1,"ping being called");
               that.pinger.canPing = keepPinging || false;
               that.pinger.onSuccess = aOnSuccess  || that.pinger.onSuccess;
               that.pinger.onError = aOnError || that.pinger.onError;
     
               that.pinger.pingCount++;
               ycomm.crave('yeapf','ping',{ "pingCount": that.pinger.pingCount },'ycomm.pinger.pong');
               that.pinger.pingerWatchdog=setTimeout(that.pinger.notAnswer, that.pinger.pingTimeout);
             },
     
             stopPing: function () {
               if (that.pinger.pingerWatchdog) clearTimeout(that.pinger.pingerWatchdog);
               _dumpy(4,1, "ping stopping");
               that.pinger.canPing = false;
             }
     
         };
     
         that.init = function() {
           that._comm_timeout = 120000;  /* defaults to 120seconds */
           that._whatchdog_interleave = 250;
     
           Object.defineProperty(
             that,
             "timeout",
             {
               get:  function () { return that._comm_timeout; },
               set:  function (newTimeout) { 
                       newTimeout = parseInt(newTimeout || 0); 
                       /* it only accepts values between 125ms and 5minutes */ 
                       that._comm_timeout = Math.min(5*60*60*1000, Math.max(125, newTimeout));
                       _dumpy(4,0,"ping Adjusting call timeout to {0}ms".format(that._comm_timeout));
                     }
             }
           );
     
           Object.defineProperty(
             that,
             "wd_interval",
             {
               get: function() { return that._whatchdog_interleave; },
               set: function (newInterval) {
                     newInterval = parseInt(newInterval || 0);
                     /* only accepts values between 100ms and 3/4 of timeout */
                     that._whatchdog_interleave = Math.min((that.timeout*3/4), Math.max(100, newInterval));
                     _dumpy(4,0,"ping Adjusting watchdog interleave to {0}ms".format(that._whatchdog_interleave));
               }
             }
           );
     
     
           that.setWaitIconControl();
     
           return that;
         }
     
         return that.init();
       };
     
       var ycomm = ycommBase();
     
     
 /* END ycomm.js */
 _dump("ycomm");
 /* START ycomm-ajax.js */
       /********************************************************************
        *
        * Com o advento do WebSocket, precisamos de novas formas para
        * provocar o servidor.
        * Este primeiro passo pretende melhorar o Ajax
        * Depois, virÃ£o funÃ§Ãµes genericas
        * Caso esteja usando prototype, ele usarÃ¡ o mesmo, se nÃ£o se virarÃ¡
        * para criar uma interface
        *
        * verificar ServerSentEvents
        * 2013-08-31
        * https://developer.mozilla.org/en-US/docs/Server-sent_events/Using_server-sent_events
        *
        * requires ycomm.js to be loaded
        * the callback function will recive: (status, xError, xData, xUserMsg, xDataContext, xGeometry)
        ********************************************************************/
     
       if (typeof xAjax=='undefined') {
         console.log("Using own xAjax() implementation");
         /*
          * 1) implementar um xAjax simples
          * 2) depois deixar de depender do prototype (107K)
          */
         var xAjax = function() {
           var that = {};
     
           if (typeof XMLHttpRequest !== 'undefined') {// code for IE7+, Firefox, Chrome, Opera, Safari
             that.xmlhttp=new XMLHttpRequest();
           } else { // code for IE6, IE5
             that.xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
           }
     
           that.Request=function (script, options) {
             // recognized options:
             // method, asynchronous, parameters, onComplete
             that.xmlhttp.onreadystatechange = function() {
     
               if (that.xmlhttp.readyState>0) {
                 if (typeof options.onProgress != 'undefined') {
                   options.onProgress(that.xmlhttp);
                 }
               }
     
               if (that.xmlhttp.readyState==4) {
                 if (typeof options.onComplete != 'undefined') {
                   options.onComplete(that.xmlhttp);
                 }
               }
             };
     
             that.xmlhttp.ontimeout = function() {
             };
     
             if (yloader.isWorker) {
               options.asynchronous = false;
             } else {
               if (options.multipart)
                 options.asynchronous = true;
             }
     
             if ((options.method || 'POST').toUpperCase()=='POST') {
               that.xmlhttp.open((options.method || 'POST'), script, options.asynchronous);
               that.xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded;charset=utf-8");
               that.xmlhttp.send(options.parameters);
             } else {
               var sep;
               if (script.indexOf('?')!==-1)
                 sep='&';
               else
                 sep='?';
               that.xmlhttp.open(options.method, script+sep+options.parameters, options.asynchronous);
               that.xmlhttp.send();
             }
           };
           return that;
         };
       }
     
       ycomm.scriptName = yloader.isWorker?'../query.php':'query.php';
     
       ycomm.defaultMethod = 'post';
       ycomm.canReceiveMessages = true;
     
       /* receive the xml envelope and split it in parts in order
        * to feed ycomm-dom functions */
       ycomm.explodeData = function(xmlDoc) {
         var xmlArray = xml2array(xmlDoc);
     
         var xCallBackFunction,
             xData, retData,
             xRoot = xmlArray['root'] || {},
             xDataContext = xRoot['dataContext'] || {},
     
             xError = xRoot['error'] ||
                      xDataContext['error'] ||
                      xDataContext['lastError'],
     
             xCallBackFunction = xmlArray['root']['callBackFunction'],
             xGeometry = null,
             xUserMsg = xDataContext['userMsg'],
             xSysMsg = xDataContext['sysMsg'];
             xStack = xDataContext['stack'];
     
         if (xStack) {
           var requestedS = xDataContext['s'];
           var requestedA = xDataContext['a'];
           var requestedV = xDataContext['v'];
           console.log("/------- {0}.{1}.v{2} ---".format(requestedS, requestedA, requestedV));
           for(var i in xStack) {
             console.log("| STACK: %c"+xStack[i],"color: #FF4D48");
           }
           console.log("\------- {0}.{1}.v{2} ---".format(requestedS, requestedA, requestedV));
         }
     
         /* sysMsg has higher priority, so it is processed prior to user application
          * sysMsg.msg = ( logoff, alert )
          * 'logoff' message block all sucessive requests and redirect main URL to 'body.php?s=yeapf&a=logoff'
          */
     
         if (xSysMsg) {
           if (xSysMsg.msg) {
             if (xSysMsg.msg=='logoff') {
               ycomm.canReceiveMessages = false;
               /* If I'm on a windows, I need to close the opener */
               var wOpener=window, wAux;
               while (wOpener.opener) {
                 wAux=wOpener;
                 wOpener=wOpener.opener;
                 wAux.close();
               }
     
               while (wOpener.parent != wOpener)
                 wOpener = wOpener.parent;
     
               if (xSysMsg.banner) {
                 _dumpy(4,1,xSysMsg.banner);
                 alert(xSysMsg.banner);
               }
               wOpener.document.location='body.php?s=yeapf&a=logoff';
             }
           }
         }
     
         /* only continue if the user is logged */
         if (ycomm.canReceiveMessages) {
           if (xDataContext) {
             var i;
             if (xDataContext.requiredFields) {
               var reqFields = xDataContext.requiredFields.split(',');
               for(i = 0; i<reqFields.length; i++) {
                 fieldName=reqFields[i];
                 if (y$(fieldName))
                   y$(fieldName).addClass('fieldWarning');
               }
             }
     
             if (xDataContext.formError) {
               var auxFormError = '';
               for(i in xDataContext.formError)
                 if (xDataContext.formError.hasOwnProperty(i)) {
                   if (auxFormError>'')
                     auxFormError+="\n";
                   auxFormError = auxFormError+xDataContext.formError[i];
                 }
               alert(auxFormError);
             }
           }
     
     
           if (xRoot) {
     
             if (xDataContext['formID']!=undefined) {
               if (formID=='') {
                 formID=xDataContext['formID'];
                 // alert("FORMID: "+formID);
               }
             }
     
             xDataContext['firstRow'] = parseInt(xDataContext['firstRow']);
             xDataContext['rowCount'] = parseInt(xDataContext['rowCount']);
             xDataContext['requestedRows'] = parseInt(xDataContext['requestedRows']);
     
             var auxRowCount = xDataContext['rowCount'];
     
             if (xRoot['data'])
               xData=xRoot['data']['row'];
             else
               xData=xRoot['row'];
     
             if (auxRowCount==1) {
               xData=new Array(xData);
             }
     
     
             if (xData) {
               retData = [];
               for(var n in xData)
                 if (xData.hasOwnProperty(n)) {
                   retData[n] = {};
                   for(var j in xData[n])
                     if (xData[n].hasOwnProperty(j))
                       retData[n][j]=unmaskHTML(xData[n][j]);
                 }
             }
     
             if (xRoot['data']!==undefined)
               xGeometry = xRoot['data']['geometry'];
     
           }
         } /* end of (ycomm.canReceiveMessages==true) */
     
     
         var ret = {
           data: retData,
           geometry: xGeometry,
           dataContext: xDataContext,
           error: xError,
           userMsg: xUserMsg
         };
     
         return ret;
     
       };
     
       ycomm.text2data = function (aResponseText) {
         var ret={};
     
         if (typeof DOMParser == 'function')  {
           var parser = new DOMParser();
           var xmlDoc = parser.parseFromString(aResponseText, "application/xml");
           ret=ycomm.explodeData(xmlDoc);
         }
         return ret;
       };
     
       ycomm.registerCall = function(via, s, a) {
         if ((ydbg) && ((ydbg.logFlag & 8)>0)) {
           if (typeof _ycomm_stat == 'undefined') {
             window._ycomm_stat = [];
           }
     
           if (typeof _ycomm_stat[via] == 'undefined')
             _ycomm_stat[via] = [];
     
           if (typeof _ycomm_stat[via][s] == 'undefined')
             _ycomm_stat[via][s] = [];
     
           if (typeof _ycomm_stat[via][s][a] == 'undefined')
             _ycomm_stat[via][s][a] = {count:0};
     
           _ycomm_stat[via][s][a].count++;
           _dumpy(4,2,"via: {0} s: {1} a: {2} count: {3}".format(via, s, a, _ycomm_stat[via][s][a].count));
         }
       }
     
       ycomm.dataLength = function (data) {
         var cc=0;
         if (data) {
           for (var i in data) { 
             if (data.hasOwnProperty(i)) 
               cc++; 
           };
         }
         return cc;
       };
     
       /*
        * https://developer.mozilla.org/en-US/docs/Web/API/FormData
        * https://developer.mozilla.org/en-US/docs/Web/Guide/Using_FormData_Objects
        * https://developer.mozilla.org/en-US/docs/Web/API/FileReader#readAsArrayBuffer%28%29
        */
     
       ycomm.invoke = function(s, a, limits, callbackFunction, displayWaitIcon) {
           if (typeof displayWaitIcon=='undefined')
             displayWaitIcon = true;
           /* if the first parameter is an object, then
            * all the others parameters are expected to be into that object */
           if (typeof s =='object') {
             var auxObj = s;
             s = auxObj.s;
             a = auxObj.a;
             limits  = auxObj.limits;
             callbackFunction = auxObj.callbackFunction;
           }
           var localU = (typeof u == 'undefined')?'':u;
           if (displayWaitIcon)
             ycomm.waitIconControl(true);
     
           var aURL=ycomm.buildCommonURL(s || '', a || '', limits || {}, localU);
           var promiseRet = new Promise(
             function(resolve, reject) {
               if (typeof xAjax!='undefined') {
     
                 ycomm.registerCall('invoke', s, a);
     
                 var aux=xAjax();
                 aux.Request(
                   ycomm.scriptName,
                   {
                     method: ycomm.defaultMethod,
                     asynchronous: !yloader.isWorker,
                     parameters: aURL,
                     onTimeout: function() {
                       console.log('XMLHttpRequest timeout');
                       if (displayWaitIcon)
                         ycomm.waitIconControl(false);
                       callbackFunction(404, {}, [{}], null, null, null);
                       reject({status: 404, message: "Timeout"});
                     },
                     onComplete: function(r) {
                         var retData = {
                           data: null,
                           geometry: null,
                           dataContext: null,
                           error: null,
                           userMsg: null
                         },
                         xmlDoc=null;
     
                         if (r.status==200) {
                           if ((ycomm.msg) && (typeof ycomm.msg.notifyServerOnline =='function'))
                             ycomm.msg.notifyServerOnline();
     
     
                           if (r.responseXML) {
                             xmlDoc = r.responseXML;
                           } else {
                             if (typeof DOMparser == 'function')  {
                               var parser = new DOMParser();
                               xmlDoc = parser.parseFromString(r.responseText, "application/xml");
                             }
                           }
     
                           if (xmlDoc!==null)
                             retData = ycomm.explodeData(xmlDoc);
     
                           resolve({status: 200, return: retData});
     
                         } else {
                           console.log(r.statusText);
                           if ((ycomm.msg) && (typeof ycomm.msg.notifyServerOffline =='function'))
                             ycomm.msg.notifyServerOffline();
                           reject({ status: r.status });
                         }
     
                         ycomm.waitIconControl(false);
     
                         if (retData.error) {
                           if (typeof retData.error == "string")
                             console.error(retData.error);
                           else {
                             for(var k in retData.error) {
                               if (retData.error.hasOwnProperty(k))
                                 console.error(retData.error[k]);
                             }
                           }
                         }
     
                         if (typeof callbackFunction=='function') {
                           if (yloader.isWorker)
                             callbackFunction(r.responseText);
                           else
                             callbackFunction(r.status, retData.error, retData.data, retData.userMsg, retData.dataContext, retData.geometry);                    
                         }
     
                       }
                   }
                 );
               } else {
                 console.log("Not ready to call "+aURL);
                 console.log("prototype library not loaded");
                 reject({ status: -1, message: 'xAjax not found'});
               }
             }
           );
           return promiseRet;
         };
     
     
 /* END ycomm-ajax.js */
 _dump("ycomm-ajax");
 /* START ycomm-rest.js */
     /*********************************************
      *
      * ycomm-rest.js is a set of prototyped functions
      * build in order to use REST protocol
      *
      *********************************************/
     
     
       ycomm.setDataLocation = function(dataLocation, deviceId) {
         ycomm._dataLocation_=dataLocation;
         ycomm._deviceId_=deviceId || guid();
       };
     
       ycomm.getDataLocation = function () {
         return ycomm._dataLocation_;
       };
     
       ycomm._scriptSequence = 0;
       ycomm._maxScriptSequenceReceived = 0;
       ycomm._CBSeq = 1000;
       ycomm._CBControl = {};
       ycomm._load = 0;
       ycomm._queue = 0;
       ycomm._maxDirectCall = 10;
     
       ycomm._dataLocation_ = (
         function() {
           var a = (typeof document=='object' && document.location && document.location.href)?document.location.href:'';
           var b=a.lastIndexOf('/');
           return a.substr(0,b+1)+'rest.php';
         }
       )();
     
       ycomm.getLoad = function () {
         return ycomm._load;
       };  
     
       ycomm._removeJSONP = function (scriptSequence, callback) {
         var head = document.head;
         var scriptID = "rest_"+scriptSequence;
         var script = document.getElementById(scriptID);
         if ((head!==undefined) && (script!==undefined)) {
           clearTimeout(script._whatchdog_);
           if (typeof script.abort === "function")
             script.abort();
           head.removeChild(script);
           _dumpy(4,1,'Clean '+scriptID+' after call to '+callback+'()');
     
         } else
           _dumpy(4,1,'Script not found: '+scriptID+' adressed to '+callback+'()');
         _dumpy(4,1,ycomm.getStatus());
       };
     
       ycomm.bring =  function (url, displayWaitIcon) {
         var head;
         //head = document.head;
         head=document.getElementsByTagName("head")[0];
         if (displayWaitIcon)
           ycomm.waitIconControl(true);
         var script = document.createElement("script");
         _dumpy(4,1,url);
         // extrair o scriptSequence e o callback para depuracao
         var scriptSequence=null;
         var callbackFunctionName=null;
         var aux = url.substr(url.indexOf('?')+1).split('&');
         for(var i in aux) {
           if (aux.hasOwnProperty(i)) {
             var v = aux[i].split('=');
             if (v[0]=='scriptSequence')
               scriptSequence=v[1];
             if (v[0]=='callback')
               callbackFunctionName=v[1];
           }
         }
     
         ycomm._maxScriptSequenceReceived = Math.max(ycomm._maxScriptSequenceReceived, scriptSequence);
     
         script.UUID = generateUUID();
         script.maxWaitCount=(ycomm.timeout / ycomm.wd_interval)+2;
         script.callbackFunctionName=callbackFunctionName;
         script.displayWaitIcon = displayWaitIcon;
         script.type = "text/javascript";
     
         script.onload=function() {
           if (ycomm._load>0)
             ycomm._load--;
           this.abort=null;
           if (this.displayWaitIcon)
             ycomm.waitIconControl(false);
         };
     
         script.abort = function () {
             if (ycomm._load>0)
               ycomm._load--;
             _dumpy(4,1,"Calling {0}(404);".format(callbackFunctionName));
             /* https://pt.wikipedia.org/wiki/Lista_de_cÃ³digos_de_status_HTTP#404_N.C3.A3o_encontrado */
             setTimeout("{0}(404,{message: 'Server do not respond ({1})'}, {})".format(callbackFunctionName, url), 100);
         };
     
         script.pool=function() {
           _dumpy(4,5,this.UUID+ " : "+this.maxWaitCount);
           this.maxWaitCount--;
           if (this.maxWaitCount>0) {
             this._whatchdog_=setTimeout(this.id+".pool()", ycomm.wd_interval);
           } else {
             if (typeof this.abort == "function")
               this.abort();
           }
         };
     
         script.id='rest_'+scriptSequence;
         script.setAttribute("src", url);
     
         try {
           _dumpy(4,2,"Creating {0} as {1}".format(script.UUID, script.src));
           head.appendChild(script);
           setTimeout(script.id+".pool()", ycomm.wd_interval);
         } catch(e) {
           _dump("Exception: {0}".format(e.message));
         }
     
         setTimeout("ycomm._removeJSONP("+scriptSequence+",'"+callbackFunctionName+"');", ycomm.timeout);
     
       };
     
       ycomm.crave = function (s, a, limits, callbackFunction, displayWaitIcon, callbackId) {
         var localU = (typeof u == 'undefined')?'':u;
         if ((typeof callbackId == 'undefined') || (callbackId === null))
           callbackId = 0;
         if ((typeof displayWaitIcon == 'undefined') || (displayWaitIcon === null))
           displayWaitIcon = true;
     
         ycomm.registerCall('crave', s, a);
         /* sequence number for script garbage collect */
         ycomm._scriptSequence++;
         if (!ycomm.getDataLocation())
           console.error("You need to define dataLocation before 'crave' it");
         else {
           var callbackFunctionName;
     
           if (typeof callbackFunction=='function') {
             /* the user has passed an annon function
              * CallBack sequencer */
             ycomm._CBSeq++;
     
             /* name for the callback function */
             callbackFunctionName="ycb"+ycomm._CBSeq;
     
             /* callback control... for garbage collect */
             ycomm._CBControl[callbackFunctionName]={ready: false};
     
             var rootSystem = window || self;
     
             rootSystem[callbackFunctionName]=function(status, error, data, userMsg, context, geometry) {
               callbackFunction(status, error, data, userMsg, context, geometry);
               _dumpy(4,1,callbackFunctionName);
             };
           } else if (typeof callbackFunction=='string') {
             callbackFunctionName=callbackFunction;
           } else
             console.error("param callBackFunction need to be function or string");
     
           if (callbackFunctionName>'') {
             /* number of concurrent calls */
             ycomm._load++;
     
             var aURL=ycomm.buildCommonURL(s || '', a || '', limits || {}, localU);
             aURL="{0}?{1}&callback={2}&callbackId={3}&scriptSequence={4}&deviceId={5}".format(ycomm._dataLocation_, aURL, callbackFunctionName, callbackId, ycomm._scriptSequence,ycomm._deviceId_);
             if (ycomm.getLoad()<=ycomm._maxDirectCall) {
               ycomm.bring(aURL, displayWaitIcon);
             } else
               setTimeout("ycomm.bring('"+aURL+"');", (0.5 + Math.abs(ycomm.getLoad() - ycomm._maxDirectCall)) * ycomm.wd_interval * 2);
           }
     
         }
       };
     
       ycomm.isIdle = function () {
         return (ycomm._maxScriptSequenceReceived == ycomm._scriptSequence);
       };
     
       ycomm.getStatus = function () {
         return "isIdle() = {0} getLoad() = {1}".format(ycomm.isIdle(), ycomm.getLoad());
       };
     
 /* END ycomm-rest.js */
 _dump("ycomm-rest");
 /* START ycomm-dom.js */
     /*********************************************
      * First Version (C) 2014 - esteban daniel dortta - dortta@yahoo.com
      **********************************************/
     
     
     ycomm.dom = {
       _elem_templates: []
     };
     
     ycomm.dom.fillInplaceData = function(aElement, aData) {
       for (var i in aData)
         if (aData.hasOwnProperty(i)) {
           if (i.substr(0, 5) == 'data-') {
             aElement.setAttribute(i, aData[i]);
           } else {
             aElement.setAttribute('data-' + i, aData[i]);
           }
         }
     };
     
     ycomm.dom.getInplaceData = function(aElement) {
       var attr = aElement.attributes,
         ret = {},
         a, name;
       for (var i in attr) {
         if (attr.hasOwnProperty(i)) {
           a = attr[i];
           name = a.nodeName;
     
           if (((name || '').substr(0, 5)) == 'data-') {
             ret[name] = a.nodeValue;
           }
         }
       }
       return ret;
     };
     
     /*
      * aElementID - ID do elemento (SELECT ou TABLE)
      * xData - vetor bidimensional associativo que vem do ajax/rest/...
      * aLineSpec - formatacao de cada linha em JSON conforme a seguinte descricao
      *             -- vars --
      *             idFieldName        - string containing ID field name on xData
      *             columns: {         - json describing each column as next:
      *                    columnName: {    - maybe an existing column in xdata or a new alias just for show
      *                        title        - column title
      *                        width        - string. prefer px
      *                        visible      - boolean
      *                        html         - optional html format string that can use %() functions (yAnalise() function)
      *                        align        - left, center, right
      *                        type         - (int,integer,intz,intn,decimal,ibdate,tsdate,tstime,date,time)
      *                        editMask     - 'dd/mm/yyy HH:MM:SS', '#,99', '#.###,##'
      *                        storageMask  - 'yyyymmddHHMMSS', '#.99'
      *                    }
      *                }
      *             rows: []           - string array with complete "<td>%(fieldName)</td>..." definition
      *             html               - string using postprocess yeapf tags as in prior html
      *             inplaceData: []    - string array with the columns that need to be placed
      *                                  inside de TR definition.  ie. the id is the user code
      *                                  and the inplaceData are the name and email.
      *             elementPrefixName OR
                    prefix             - string to be added before each target (element) form fields
      *             elementPostfixName OR
                    postfix            - string to be added after each target (element) form fields
      *
      *             beforeElement      - string with LI element id indicating new elements will be added before it
      *             sep                - sring separator (o be used in DATALIST and SELECT)
      *
      *             -- events -- (READY)
      *             onBeforeNewItem(aElementID, dataLine)
      *             onNewItem(aElementID, aNewElement, aRowData)
      *             onNewRowReady(aElementID, aRow)
      *             onSelect(aElementID, id) ou onClick(aElementID, id)
      *             -- events -- (PLANNED)
      *             onItemAdd(aElementID, id)
      *             onReady(aElementID)
      * aFlags - JSON
      *          deleteRows  (true by default)
      *          paintRows   (false by default) 
      *          insertAtTop (applies to TR. false by default)
      *          unlearn      (undefined by default)
      */
     ycomm.dom.fillElement = function(aElementID, xData, aLineSpec, aFlags) {
       if ((aLineSpec === undefined) || (aLineSpec === null))
         aLineSpec = {};
     
       if (typeof aFlags == "boolean")
         aFlags = { deleteRows: aFlags };
     
       aFlags = aFlags || {};
     
       if (typeof aFlags.deleteRows == 'undefined')
         aFlags.deleteRows = true;
       if (typeof aFlags.paintRows == 'undefined')
         aFlags.paintRows = true;
       if (typeof aFlags.insertAtTop == 'undefined')
         aFlags.insertAtTop = false;
     
       var idFieldName, colName, newRow, canCreateRow,
         aElement = y$(aElementID),
         rowIdOffset = 0,
         first_time = typeof ycomm.dom._elem_templates[aElementID] == "undefined";
     
       idFieldName = aLineSpec.idFieldName || 'id';
       if (typeof aFlags.unlearn == "boolean")
         first_time = aFlags.unlearn;
     
       var getDataFromXData = function(xDataItem) {
         /* this function extract the pouchdb data from xDataItem if exists. otherwise, return xDataItem */
         if ((xDataItem.doc) && (xDataItem.id) && (xDataItem.value))
           xDataItem = xDataItem.doc;
         return xDataItem;
       };
     
       var saveInplaceData = function(opt, xDataItem) {
         if (typeof aLineSpec.inplaceData != 'undefined') {
           for (var c = 0; c < aLineSpec.inplaceData.length; c++) {
             if (typeof xDataItem[aLineSpec.inplaceData[c]] !== "undefined") {
               var colName = aLineSpec.inplaceData[c];
               opt.setAttribute("data-" + colName, (xDataItem[colName] || ''));
             }
           }
         }
       };
     
       var setNewRowAttributes = function(aNewRow) {
         var auxIdSequence,
           auxInplaceData,
           xDataItem = getDataFromXData(xData[j]);
     
         cNdx = 0;
         if (aNewRow.nodeName == 'TR') {
           if (aFlags.paintRows)
             aNewRow.style.backgroundColor = rowColorSpec.suggestRowColor(rowGroup);
         }
         if (xDataItem[idFieldName]) {
           if (y$(xDataItem[idFieldName])) {
             auxIdSequence = 0;
             while (y$(xDataItem[idFieldName] + '_' + auxIdSequence))
               auxIdSequence++;
             aNewRow.id = xDataItem[idFieldName] + '_' + auxIdSequence;
           } else
             aNewRow.id = xDataItem[idFieldName];
         }
     
         saveInplaceData(aNewRow, xDataItem);
     
         if ((aLineSpec.onClick) || (aLineSpec.onSelect)) {
           aNewRow.addEventListener('click', ((aLineSpec.onClick) || (aLineSpec.onSelect)), false);
         }
       };
     
       var addCell = function(colName) {
         if (colName != idFieldName) {
           var newCell = newRow.insertCell(cNdx),
             xDataItem = getDataFromXData(xData[j]),
             aNewCellValue = colName !== null ? unmaskHTML((xDataItem[colName] || '')) : unmaskHTML(xDataItem);
     
           if ((aLineSpec.columns) && (aLineSpec.columns[colName])) {
             if (aLineSpec.columns[colName].align)
               newCell.style.textAlign = aLineSpec.columns[colName].align;
             if (aLineSpec.columns[colName].type) {
               aNewCellValue = yAnalise('%' + aLineSpec.columns[colName].type + '(' + aNewCellValue + ')');
             }
           }
     
           if (!canCreateRow) {
             newCell.addClass('warning');
             /* newCell.style.borderLeft = 'solid 1px red'; */
           }
     
           newCell.innerHTML = aNewCellValue.length === 0 ? '&nbsp;' : aNewCellValue;
           newCell.style.verticalAlign = 'top';
           newCell.id = aElementID + '_' + cNdx + '_' + oTable.rows.length;
           newCell.setAttribute('colName', colName);
           if (typeof aLineSpec.onNewItem == 'function')
             aLineSpec.onNewItem(aElementID, newCell, xDataItem);
           cNdx = cNdx + 1;
         }
       };
     
       var oTable, auxHTML, j, c, cNdx, i, newCell, internalRowId = (new Date()).getTime() - 1447265735470,
         xDataItem;
     
       /* grants filledEvent exists */
       if ("undefined" == typeof window._evtFilled) {
         window._evtFilled = window.createDOMEvent("filled");
       }
     
       if (aElement) {
         if (aElement.nodeName == 'TABLE') {
           if (aElement.getElementsByTagName('tbody').length > 0)
             oTable = aElement.getElementsByTagName('tbody')[0];
           else
             oTable = aElement;
           if (oTable.getElementsByTagName('tbody').length > 0)
             oTable = oTable.getElementsByTagName('tbody')[0];
     
           /* 1) if this is the first time, pull the template from the table itself
            * 2) the 'aLineSpec' has higher priority */
           if (first_time) {
             if (typeof(aLineSpec.columns || aLineSpec.rows || aLineSpec.html) == "undefined") {
               ycomm.dom._elem_templates[aElementID] = {};
               if (oTable.rows.length > 0) {
                 ycomm.dom._elem_templates[aElementID].rows = [];
                 for (i = 0; i < oTable.rows.length; i++)
                   ycomm.dom._elem_templates[aElementID].rows[i] = trim(oTable.rows[i].innerHTML + "").replace(/\ \s+/g, '');
               }
             } else {
               ycomm.dom._elem_templates[aElementID] = {};
               ycomm.dom._elem_templates[aElementID].columns = aLineSpec.columns;
               ycomm.dom._elem_templates[aElementID].rows = aLineSpec.rows;
               ycomm.dom._elem_templates[aElementID].html = aLineSpec.html;
             }
           }
           mergeObject(ycomm.dom._elem_templates[aElementID], aLineSpec, true);
     
           if (aFlags.deleteRows === true) {
             while (oTable.rows.length > 0)
               oTable.deleteRow(oTable.rows.length - 1);
           } else {
             rowIdOffset = oTable.rows.length;
           }
     
           var rowGroup = oTable.rows.length % 2;
           cNdx = null;
           for (j in xData) {
             if (xData.hasOwnProperty(j)) {
               xDataItem = getDataFromXData(xData[j]);
               rowGroup++;
     
               canCreateRow = true;
               if (!aFlags.deleteRows) {
                 if (xDataItem[idFieldName]) {
                   for (i = 0;
                     ((canCreateRow) && (i < oTable.rows.length)); i++) {
                     if (oTable.rows[i].id == xDataItem[idFieldName]) {
                       newRow = oTable.rows[i];
                       while (newRow.cells.length > 0)
                         newRow.deleteCell(0);
                       canCreateRow = false;
                       xDataItem.rowid = i;
                     }
                   }
                 }
               }
     
               if (canCreateRow) {
                 if (aFlags.insertAtTop)
                   newRow = oTable.insertRow(0);
                 else
                   newRow = oTable.insertRow(oTable.rows.length);
               }
     
               // xDataItem['rowid'] = parseInt(xDataItem['rowid']) + rowIdOffset + '';
               internalRowId++;
               xDataItem.rowid = ((!aFlags.insertAtTop) && (typeof newRow.rowIndex !== "undefined")) ? newRow.rowIndex : internalRowId + '';
               xDataItem._elementid_ = aElementID;
     
               setNewRowAttributes(newRow);
     
               if (typeof aLineSpec.onBeforeNewItem == 'function') {
                 aLineSpec.onBeforeNewItem(aElementID, xDataItem);
               }
     
               /* default action when neither columns nor html are defined */
               if ((typeof aLineSpec.html == 'undefined') &&
                 (typeof aLineSpec.rows == 'undefined') &&
                 (typeof aLineSpec.columns == 'undefined')) {
                 if (typeof xDataItem == 'string') {
                   addCell(null);
                 } else {
                   for (colName in xDataItem) {
                     if ((xDataItem.hasOwnProperty(colName)) &&
                       (colName != idFieldName) &&
                       (colName != 'rowid') &&
                       (colName != '_elementid_')) {
                       addCell(colName);
                     }
                   }
                 }
     
               } else {
                 /* columns order are defined */
                 if (typeof aLineSpec.columns != 'undefined') {
     
                   if (isArray(aLineSpec.columns)) {
                     for (c = 0; c < aLineSpec.columns.length; c++) {
                       addCell(aLineSpec.columns[c]);
                     }
                   } else {
                     for (c in aLineSpec.columns) {
                       if (aLineSpec.columns.hasOwnProperty(c))
                         addCell(c);
                     }
                   }
     
                 } else if (typeof aLineSpec.html != 'undefined') {
                   /* html parser is enabled */
                   newCell = newRow.insertCell(0);
                   newCell.innerHTML = yAnalise(aLineSpec.html, xDataItem);
                   newCell.style.verticalAlign = 'top';
                   newCell.id = aElementID + '_' + cNdx + '_' + oTable.rows.length;
                   if (typeof aLineSpec.onNewItem == 'function')
                     aLineSpec.onNewItem(aElementID, newCell, xDataItem);
     
                 } else if (typeof aLineSpec.rows != 'undefined') {
                   var firstRow = true;
                   for (r = 0; r < aLineSpec.rows.length; r++) {
                     if (!firstRow) {
                       newRow = oTable.insertRow(oTable.rows.length);
                       setNewRowAttributes(newRow);
                     }
                     newRow.innerHTML = yAnalise(aLineSpec.rows[r], xDataItem);
                     if (!canCreateRow) {
                       if (aFlags.deleteRows)
                         for (c = 0; c < newRow.cells.length; c++)
                           newRow.cells[c].style.borderLeft = 'solid 1px red';
                     }
                     if (typeof aLineSpec.onNewItem == 'function')
                       aLineSpec.onNewItem(aElementID, newRow, xDataItem);
                     firstRow = false;
                   }
                 }
     
               }
     
               if (typeof aLineSpec.onNewRowReady == 'function') {
                 aLineSpec.onNewRowReady(aElementID, newRow);
               }
     
             }
           }
     
           aElement.dispatchEvent(window._evtFilled);
     
     
         } else if (aElement.nodeName == 'UL') {
           var oUL = aElement;
     
           if (first_time) {
             if (typeof(aLineSpec.columns || aLineSpec.rows || aLineSpec.html) == "undefined") {
               ycomm.dom._elem_templates[aElementID] = {};
               if (aElement.children.length > 0) {
                 ycomm.dom._elem_templates[aElementID].rows = [];
                 for (i = 0; i < aElement.children.length; i++)
                   ycomm.dom._elem_templates[aElementID].rows[i] = trim(aElement.children[i].innerHTML + "").replace(/\ \s+/g, '');
               }
             } else {
               ycomm.dom._elem_templates[aElementID] = {};
               ycomm.dom._elem_templates[aElementID].columns = aLineSpec.columns;
               ycomm.dom._elem_templates[aElementID].rows = aLineSpec.rows;
               ycomm.dom._elem_templates[aElementID].html = aLineSpec.html;
             }
           }
           mergeObject(ycomm.dom._elem_templates[aElementID], aLineSpec, true);
     
           if (aFlags.deleteRows) {
             while (oUL.firstChild) {
               oUL.removeChild(oUL.firstChild);
             }
           }
     
           for (j in xData) {
             if (xData.hasOwnProperty(j)) {
               xDataItem = getDataFromXData(xData[j]);
               if (typeof aLineSpec.onBeforeNewItem == 'function') {
                 aLineSpec.onBeforeNewItem(aElementID, xDataItem);
               }
     
               var entry = document.createElement('li');
               saveInplaceData(entry, xDataItem);
     
               var innerText = '',
                 asHTML = false;
               if (typeof aLineSpec.rows == 'object') {
                 for (r = 0; r < aLineSpec.rows.length; r++) {
                   innerText = innerText + yAnalise(aLineSpec.rows[r], xDataItem) + "";
                 }
                 asHTML = true;
               } else if (typeof aLineSpec.html == 'string') {
                 innerText = innerText + yAnalise(aLineSpec.html, xDataItem) + "";
                 asHTML = true;
               } else {
                 for (colName in xDataItem) {
                   if (innerText === '') {
                     if ((xDataItem.hasOwnProperty(colName)) &&
                       (colName != idFieldName) &&
                       (colName != 'rowid') &&
                       (colName != '_elementid_')) {
                       innerText = innerText + (xDataItem[colName] || '');
                     }
                   }
                 }
     
               }
     
               setNewRowAttributes(entry);
               if (asHTML)
                 entry.innerHTML = innerText;
               else
                 entry.appendChild(document.createTextNode(innerText));
               if (typeof aLineSpec.beforeElement == 'string') {
                 var item = y$(aLineSpec.beforeElement);
                 oUL.insertBefore(entry, item);
               } else
                 oUL.appendChild(entry);
     
               if (typeof aLineSpec.onNewItem == 'function')
                 aLineSpec.onNewItem(aElementID, entry, xDataItem);
             }
           }
     
           aElement.dispatchEvent(window._evtFilled);
     
         } else if (aElement.nodeName == 'LISTBOX') {
           var oListBox = aElement;
           if (aFlags.deleteRows) {
             while (oListBox.childElementCount > 0)
               oListBox.childNodes[0].remove();
           }
           var cRow = 0;
     
           for (j in xData) {
             if (xData.hasOwnProperty(j)) {
               xDataItem = getDataFromXData(xData[j]);
               xDataItem._elementid_ = aElementID;
               if (typeof aLineSpec.onBeforeNewItem == 'function') {
                 aLineSpec.onBeforeNewItem(aElementID, xDataItem);
               }
     
               newRow = document.createElement('listitem');
               cNdx = 0;
     
               if (typeof aLineSpec.columns == 'undefined') {
                 if (typeof xDataItem == 'string') {
                   _dumpy(2, 1, "ERRO: yeapf-dom.js - string cell not implemented");
                 } else {
                   for (colName in xDataItem) {
                     if ((xDataItem.hasOwnProperty(colName)) &&
                       (colName != idFieldName) &&
                       (colName != 'rowid') &&
                       (colName != '_elementid_')) {
                       newCell = document.createElement('listcell');
                       newCell.innerHTML = (xDataItem[colName] || '');
                       newCell.id = aElementID + '_' + cNdx + '_' + cRow;
                       if (typeof aLineSpec.onNewItem == 'function')
                         aLineSpec.onNewItem(aElementID, newCell, xDataItem);
                       cNdx = cNdx + 1;
                       newRow.appendChild(newCell);
                     }
                   }
                 }
               } else {
                 for (colName in aLineSpec.columns) {
                   if (colName != idFieldName) {
                     newCell = document.createElement('listcell');
                     newCell.innerHTML = (xDataItem[colName] || '');
                     newCell.id = aElementID + '_' + cNdx + '_' + cRow;
                     if (typeof aLineSpec.onNewItem == 'function')
                       aLineSpec.onNewItem(aElementID, newCell, xDataItem);
                     cNdx = cNdx + 1;
                     newRow.appendChild(newCell);
                   }
                 }
               }
               saveInplaceData(newRow, xDataItem);
               oListBox.appendChild(newRow);
               cRow++;
             }
           }
     
           aElement.dispatchEvent(window._evtFilled);
     
     
         } else if ((aElement.nodeName == 'SELECT') || (aElement.nodeName == 'DATALIST')) {
     
           /* Clean options */
           if (aFlags.deleteRows) {
             while (aElement.options.length > 0) {
               aElement.removeChild(aElement.options[0]);
               /*
                 aElement.options.remove(0);
                */
             }
           }
           cNdx = 0;
           /* data */
           for (j in xData) {
     
             if (xData.hasOwnProperty(j)) {
               xDataItem = getDataFromXData(xData[j]);
               xDataItem._elementid_ = aElementID;
               if (typeof aLineSpec.onBeforeNewItem == 'function') {
                 aLineSpec.onBeforeNewItem(aElementID, xDataItem);
               }
     
               auxHTML = '';
               if (typeof aLineSpec.columns == 'undefined') {
                 if (typeof xDataItem == 'string') {
                   _dumpy(2, 1, "ERRO: yeapf-dom.js - string cell not implemented");
                 } else {
                   for (colName in xDataItem) {
                     if ((xDataItem.hasOwnProperty(colName)) &&
                       (colName != idFieldName) &&
                       (colName != 'rowid') &&
                       (colName != '_elementid_')) {
                       auxHTML = auxHTML + (xDataItem[colName] || '');
                     }
                   }
                 }
               } else {
                 var sep = aLineSpec.sep || '';
                 if (isArray(aLineSpec.columns)) {
                   for (c = 0; c < aLineSpec.columns.length; c++) {
                     if (auxHTML > '')
                       auxHTML += sep;
                     auxHTML = auxHTML + (xDataItem[aLineSpec.columns[c]] || '');
                   }
                 } else {
                   if (typeof xDataItem == 'string') {
                     _dumpy(2, 1, "ERRO: yeapf-dom.js - string cell not implemented");
                   } else {
                     for (colName in aLineSpec.columns) {
                       if (colName != idFieldName)
                         auxHTML = auxHTML + (xDataItem[colName] || '') + sep;
                     }
                   }
                 }
               }
     
               var opt = document.createElement('option');
               if (typeof xDataItem[idFieldName] != 'undefined') {
                 if (aElement.nodeName == 'DATALIST') {
                   opt.setAttribute('data-' + idFieldName, xDataItem[idFieldName]);
                 } else {
                   opt.value = xDataItem[idFieldName];
                 }
               }
               opt.innerHTML = auxHTML;
               opt.id = aElementID + '_' + cNdx;
               saveInplaceData(opt, xDataItem);
     
               if (typeof aLineSpec.onNewItem == 'function')
                 aLineSpec.onNewItem(aElementID, opt, xDataItem);
               aElement.appendChild(opt);
               cNdx++;
             }
           }
     
           aElement.dispatchEvent(window._evtFilled);
     
           if (aElement.onclick)
             aElement.onclick();
     
         } else if (aElement.nodeName == 'FORM') {
           var fieldType,
             valueType,
             editMask,
             storageMask,
             fieldValue,
             fieldName,
             fieldPrefix, fieldPostfix,
             aElements;
     
     
           var classHasName = function(elemNdx, name) {
             var ret = false;
             if (aElements[elemNdx]) {
                 name = name.toUpperCase();
                 var classes=aElements[elemNdx].className.toUpperCase();
                 ret = ret || (classes.indexOf(name) >= 0);
             }
             return ret;
           }
     
           if (aFlags.deleteRows)
             aElements = this.cleanForm(aElementID);
           else
             aElements = this.selectElements(aElementID);
     
           if (xData)
             if ((typeof xData == 'object') || (xData.length === 1)) {
               var yData = getDataFromXData(xData[0] || xData);
               saveInplaceData(aElement, yData);
               if (typeof aLineSpec.onBeforeNewItem == 'function') {
                 aLineSpec.onBeforeNewItem(aElementID, yData);
               }
     
               fieldPrefix = aLineSpec.elementPrefixName || aLineSpec.prefix || aElement.getAttribute('data-prefix') || '';
               fieldPostfix = aLineSpec.elementPostixName || aLineSpec.postfix || aElement.getAttribute('data-postfix') || '';
               for (i = 0; i < aElements.length; i++) {
                 /* the less prioritary MASK comes from the html form */
                 editMask = aElements[i].getAttribute('data-edit-mask') || aElements[i].getAttribute('editMask');
                 storageMask = aElements[i].getAttribute('data-storage-mask') || aElements[i].getAttribute('storageMask');
                 valueType = aElements[i].getAttribute('data-value-type') || aElements[i].getAttribute('valueType') || 'text';
     
                 /* data comming from the server */
                 fieldName = suggestKeyName(yData, aElements[i].name || aElements[i].id, fieldPrefix, fieldPostfix);
     
                 /* column name defined by the programmer on client side */
                 colName = (aLineSpec.columns && suggestKeyName(aLineSpec.columns, aElements[i].name || aElements[i].id)) || null;
     
                 if (typeof yData[fieldName] != 'undefined') {
                   fieldValue = unmaskHTML(yData[fieldName]);
                   fieldType = aElements[i].type.toLowerCase();
     
                   /* only fill field if there is not column definition
                    * or if the colName is defined */
                   if ((!aLineSpec.columns) || (colName > '')) {
                     /* if thete is a colName, pick type and mask from aLineSpec */
                     if (colName > '') {
                       if (!isArray(aLineSpec.columns)) {
                         valueType = aLineSpec.columns[colName].type;
                         editMask = (aLineSpec.columns[colName].editMask) || editMask;
                         storageMask = (aLineSpec.columns[colName].storageMask) || storageMask;
                       }
                     }
     
                     if (valueType != 'text') {
                       if ((editMask > '') && (storageMask > '')) {
                         if (valueType.indexOf('date') >= 0) {
                           fieldValue = dateTransform(fieldValue, storageMask, editMask) || '';
                         }
                       } else
                         fieldValue = yAnalise("%" + valueType + "(" + fieldValue + ")");
                     }
     
                     switch (fieldType) {
     
                       case "tel":
                         aElements[i].value = (""+fieldValue).asPhone();
                         break;
     
                       case "text":
                       case "password":
                       case "textarea":
                       case "email":
                       case "hidden":
                       case "color":
                       case "date":
                       case "datetime":
                       case "datetime-local":
                       case "month":
                       case "number":
                       case "range":
                       case "search":
                       case "time":
                       case "url":
                       case "week":
                         aElements[i].value = fieldValue;
                         break;
     
                       case "radio":
                       case "checkbox":
                         /*
                         var options=document.getElementsByName(fieldName);
                         for (var j=0; j<options.length; j++)
                           if (options[j].value==fieldValue)
                             options[j].checked=true;
                         */
     
                         if (aElements[i].value == fieldValue)
                           aElements[i].checked = (aElements[i].value === fieldValue);
                         break;
     
                       case "select-one":
                       case "select-multi":
                         for (j = 0; j < aElements[i].options.length; j++)
                           if (aElements[i].options[j].value == fieldValue)
                             aElements[i].selectedIndex = j;
                         break;
                     }
     
                     if (classHasName(i,"cpf")) {
                       aElements[i].value = (""+fieldValue).asCPF();
                     }
     
                     if (classHasName(i,"cnpj")) {
                       aElements[i].value = (""+fieldValue).asCNPJ();
                     }
     
                     if (classHasName(i,"rg")) {
                       aElements[i].value = (""+fieldValue).asRG();
                     }
     
                     if (classHasName(i,"cep")) {
                       aElements[i].value = (""+fieldValue).asCEP();
                     }
     
                     if (typeof aLineSpec.onNewItem == 'function')
                       aLineSpec.onNewItem(aElementID, aElements[i], yData);
     
                   }
                 }
     
               }
     
               aElement.dispatchEvent(window._evtFilled);
     
             } else if (xData.length > 1)
             _dump("There are more than one record returning from the server");
     
         } else if (aElement.nodeName == 'DIV') {
           if (first_time) {
             if (typeof(aLineSpec.columns || aLineSpec.rows || aLineSpec.html) == "undefined") {
               ycomm.dom._elem_templates[aElementID] = {};
               ycomm.dom._elem_templates[aElementID].html = aElement.innerHTML;
             } else {
               ycomm.dom._elem_templates[aElementID] = {};
               ycomm.dom._elem_templates[aElementID].columns = aLineSpec.columns;
               ycomm.dom._elem_templates[aElementID].rows = aLineSpec.rows;
               ycomm.dom._elem_templates[aElementID].html = aLineSpec.html;
             }
           }
           mergeObject(ycomm.dom._elem_templates[aElementID], aLineSpec, true);
     
     
           if (aFlags.deleteRows)
             aElement.innerHTML = '';
     
           auxHTML = aElement.innerHTML;
     
           if (xData) {
             for (j in xData) {
               if (xData.hasOwnProperty(j)) {
                 xDataItem = getDataFromXData(xData[j]);
                 saveInplaceData(aElement, xDataItem);
                 if (typeof aLineSpec.onBeforeNewItem == 'function') {
                   aLineSpec.onBeforeNewItem(aElementID, xDataItem);
                 }
     
                 if (aLineSpec.html) {
                   auxHTML = auxHTML + yAnalise(aLineSpec.html, xDataItem);
                 } else {
                   for (colName in xDataItem) {
                     if (xDataItem.hasOwnProperty(colName)) {
                       auxHTML += '<div><div class=tnFieldName><b><small>{0}</small></b></div>{1}'.format(colName, (xDataItem[colName] || ''));
                     }
                   }
                 }
               }
             }
             aElement.innerHTML = auxHTML;
     
             aElement.dispatchEvent(window._evtFilled);
     
           }
         }
       }
     };
     
     /*
      * search for the first container from the element
      * The container could be: a table row, a select option, a listbox item
      * i.e. if the container is a row, aContainerID is the table which
      * contains the ROW and the aElement is a button into the row
      */
     ycomm.dom.getRowId = function(aElement, aContainerID) {
       if (aElement) {
         while ((aElement) && (aElement.parentNode)) {
           aElement = aElement.parentNode;
         }
       }
     };
     
     ycomm.dom.getRowByRowNo = function(tableId, aRowNo) {
       var table = document.getElementById(tableId);
       if (table) {
         var row = table.rows[aRowNo];
         return row;
       } else
         return null;
     };
     
     ycomm.dom.getTableRowId = function(tableId, aRowNo) {
       var row = ycomm.dom.getRowByRowNo(tableId, aRowNo);
     
       return row ? row.id : null;
     };
     
     ycomm.dom.highlightRow = function(tableId, aRowId, highlightClass) {
       highlightClass = highlightClass || '';
       var c;
       aRowId = typeof aRowId == 'undefined' ? -1 : aRowId;
       var table = document.getElementById(tableId);
       if (table) {
         for (var i = 0; i < table.rows.length; i++) {
           if (i == aRowId) {
             table.rows[i].addClass(highlightClass);
             for (c = 0; c < table.rows[i].cells.length; c++)
               table.rows[i].cells[c].addClass(highlightClass);
           } else {
             table.rows[i].removeClass(highlightClass);
             for (c = 0; c < table.rows[i].cells.length; c++)
               table.rows[i].cells[c].removeClass(highlightClass);
           }
         }
       }
     };
     
     ycomm.dom.getTableRowInplaceData = function(aRow, fieldName) {
       if (aRow)
         return aRow.getAttribute('data-' + fieldName);
       else
         return null;
     };
     
     ycomm.dom.getTableInplaceData = function(tableId, y, fieldName) {
       var table = document.getElementById(tableId);
       if (table) {
         var row = table.rows[y];
         return ycomm.dom.getTableRowInplaceData(row, fieldName);
       } else
         return null;
     };
     
     ycomm.dom.deleteElement = function(aElementId) {
       var aElement = y$(aElementId);
       if (aElement)
         aElement.parentNode.removeChild(aElement);
     };
     
     ycomm.dom.selectElements = function(aElementId, aFieldListFilter) {
       var aElements = [],
         knownField, allElements, i, fieldType;
     
       var aForm = y$(aElementId);
       if (aForm) {
         allElements = aForm.getElementsByTagName('*');
         for (i = 0; i < allElements.length; i++) {
           if (allElements[i].type) {
             fieldType = allElements[i].type.toLowerCase();
             knownFieldType = false;
     
             if (aFieldListFilter) {
               if (aFieldListFilter.indexOf(allElements[i].name || allElements[i].id) < 0)
                 fieldType = '--AVOID--';
             }
     
             switch (fieldType) {
     
               case "text":
               case "password":
               case "textarea":
               case "hidden":
               case "email":
               case "radio":
               case "checkbox":
               case "select-one":
               case "select-multi":
               case "file":
                 knownFieldType = true;
                 break;
     
               case "color":
               case "date":
               case "datetime":
               case "datetime-local":
               case "month":
               case "number":
               case "range":
               case "search":
               case "tel":
               case "time":
               case "url":
               case "week":
                 knownFieldType = true;
                 break;
             }
     
             if (knownFieldType)
               aElements[aElements.length] = allElements[i];
           }
         }
       }
       return aElements;
     };
     
     ycomm.dom.cleanElement = function(aElement) {
       if (typeof aElement == 'string')
         aElement = y$(aElement);
       if (aElement) {
         var reservedFields = ['__cmd5p__'],
           fieldModified,
           fieldType, aux;
     
         fieldType = aElement.type ? aElement.type.toLowerCase() : aElement.nodeName ? aElement.nodeName.toLowerCase() : 'UNKNOWN';
         fieldModified = false;
         if (reservedFields.indexOf(aElement.id) < 0) {
           switch (fieldType) {
     
             case "text":
             case "password":
             case "textarea":
             case "hidden":
             case "color":
             case "date":
             case "datetime":
             case "datetime-local":
             case "month":
             case "number":
             case "range":
             case "search":
             case "tel":
             case "time":
             case "url":
             case "week":
               fieldModified = (aElement.value > '');
               aElement.value = "";
               break;
     
             case "radio":
             case "checkbox":
               fieldModified = (aElement.checked !== false);
               aElement.checked = false;
               break;
     
             case "select-one":
             case "select-multi":
               fieldModified = (aElement.selectedIndex > -1);
               aElement.selectedIndex = -1;
               break;
             case "table":
               if (aElement.getElementsByTagName('tbody').length > 0)
                 aElement = aElement.getElementsByTagName('tbody')[0];
               while (aElement.rows.length > 0)
                 aElement.deleteRow(aElement.rows.length - 1);
               break;
             case "ul":
               while (aElement.firstChild) {
                 aElement.removeChild(aElement.firstChild);
               }
     
               break;
           }
         }
       } else
         _dumpy(2, 1, "null element when calling cleanElement()");
     };
     
     ycomm.dom.cleanForm = function(aFormId, aFieldList) {
       /*
         <button>
         <datalist>
         <fieldset>
         <form>
         <input>
         <keygen>
         <label>
         <legend>
         <meter>
         <optgroup>
         <option>
         <output>
         <progress>
         <select>
         <textarea>
       */
       var i, aElements;
     
       aElements = this.selectElements(aFormId, aFieldList);
       for (i = 0; i < aElements.length; i++) {
         ycomm.dom.cleanElement(aElements[i]);
       }
       return aElements;
     };
     
     ycomm.dom._scratch = {
       t: ['Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.',
         'Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui blandit praesent luptatum zzril delenit augue duis dolore te feugait nulla facilisi. Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat.',
         'Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui blandit praesent luptatum zzril delenit augue duis dolore te feugait nulla facilisi.',
         'Nam liber tempor cum soluta nobis eleifend option congue nihil imperdiet doming id quod mazim placerat facer possim assum. Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat.',
         'Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis.',
         'At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, At accusam aliquyam diam diam dolore dolores duo eirmod eos erat, et nonumy sed tempor et et invidunt justo labore Stet clita ea et gubergren, kasd magna no rebum. sanctus sea sed takimata ut vero voluptua. est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat. ',
         'Consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.'
       ],
       d: ['yahu.com', 'hotmayl.com', 'jmail.com', 'yahu.com.nh', 'hotmayl.com.nh', 'jmail.com.nh'],
       p: ['http://', 'https://', 'ws://', 'wss://', 'ftp://'],
       mn: ['James', 'John', 'Robert', 'Michael', 'William', 'David', 'Richard', 'Charles'],
       fn: ['Mary', 'Patricia', 'Linda', 'Barbara', 'Elizabeth', 'Jennifer', 'Maria', 'Susan'],
       sn: ['Smith', 'Jones', 'Taylor', 'Williams', 'Brown', 'Davies', 'Evans', 'Wilson'],
       ch: 'qwertyuiopasdfghjklzxcvbnmQAZWSXEDCRFVTGBYHNUJMIKOLP0123456789',
       n: '0123456789'
     };
     
     ycomm.dom.testFormWithJunk = function(aFormId) {
     
       var aElements = this.selectElements(aFormId),
         i, fieldType, fieldId, fieldValue, maxLength, classes;
     
     
       var genString = function(base, minLen, maxLen) {
         var ret = '',
           n, j;
         maxLen = Math.floor((Math.random() * maxLen) + minLen);
         j = 0;
         while (j < maxLen) {
           n = Math.floor((Math.random() * base.length));
           ret += base[n];
           j++;
         }
         return ret;
       };
     
       var genNumber = function(min, max, leftPaddingLen) {
         leftPaddingLen = leftPaddingLen || 0;
         var ret = '' + Math.floor((Math.random() * (max - min) + min));
         while (('' + ret).length < leftPaddingLen)
           ret = '0' + ret;
         return ret;
       };
     
       var classHasName = function(name) {
         var ret = false;
         name = name.toUpperCase();
         for (var c = 0; c < lClasses.length; c++) {
           ret = ret || (lClasses[c].indexOf(name) >= 0);
         }
         return ret;
       };
     
       for (i = 0; i < aElements.length; i++) {
         fieldType = aElements[i].type.toLowerCase();
         fieldId = aElements[i].id;
         maxLength = aElements[i].getAttribute("maxlength") || 100;
         lClasses = aElements[i].className.split(" ");
     
         for (var n = 0; n < lClasses.length; n++)
           lClasses[n] = lClasses[n].toUpperCase();
     
         fieldValue = '';
         if (fieldId) {
           switch (fieldType) {
             case "password":
               fieldValue = genString(ycomm.dom._scratch.ch, 6, 15);
               break;
             case "textarea":
               fieldValue = genString(ycomm.dom._scratch.t, 1, 15 * maxLength);
               break;
             case "email":
               fieldValue = genString(ycomm.dom._scratch.mn, 2, 3) + "@" + genString(ycomm.dom._scratch.d, 1, 1);
               break;
             case "date":
               fieldValue = 1 * genNumber(-2208981600000, 2556064800000);
               fieldValue = new Date(fieldValue);
               fieldValue = fieldValue.toISOString().substr(0, 10);
               break;
     
             case "color":
             case "datetime":
             case "datetime-local":
             case "month":
               fieldValue = 1 * genNumber(1, 12);
               break;
             case "number":
             case "range":
               fieldValue = 1 * genNumber(1, 100);
               break;
             case "tel":
               fieldValue = 1 * genNumber(10, 52);
               for (var aux = 0; aux < 3; aux++)
                 fieldValue += ' ' + genNumber(100, 999);
               break;
             case "search":
             case "time":
             case "week":
               fieldValue = 1 * genNumber(1, 52);
               break;
             case "url":
               fieldValue = genString(ycomm.dom._scratch.p, 1, 1) + genString(ycomm.dom._scratch.d, 1, 1) + ".xyz";
               break;
     
             case "radio":
             case "checkbox":
               break;
     
             case "select-one":
             case "select-multi":
               break;
     
             case "hidden":
               fielValue = "";
               break;
     
             default:
               if (classHasName('password')) {
                 fieldValue = genString(ycomm.dom._scratch.ch, 6, 15);
               } else if (classHasName('cpf')) {
                 fieldValue = fieldValue.gerarCPF();
               } else if (classHasName('cnpj')) {
                 fieldValue = fieldValue.gerarCNPJ();
               } else if (classHasName('ie')) {
                 fieldValue = genString(ycomm.dom._scratch.n, 6, 12);
               } else if (classHasName('cep')) {
                 /* http://www.mapanet.eu/en/resources/Postal-Format.asp */
                 fieldValue = genNumber(10, 99);
                 fieldValue += '.' + genNumber(0, 999, 3);
                 fieldValue += '-' + genNumber(0, 999, 3);
               } else if (classHasName('zip')) {
                 /* http://www.mapanet.eu/en/resources/Postal-Format.asp */
                 fieldValue = genNumber(0, 99999, 5);
                 fieldValue += '-' + genNumber(0, 9999, 4);
               } else {
                 fieldValue = genString(ycomm.dom._scratch.t, 1, maxLength);
               }
               fieldValue = fieldValue.substr(0, maxLength);
               break;
           }
     
           y$(fieldId).value = fieldValue;
         }
       }
     };
     
     /*
      * get all the elements of the form and returns a JSON
      * https://developer.mozilla.org/en-US/docs/Web/Guide/HTML/Forms/data-form_validation
      * http://www.the-art-of-web.com/html/html5-form-validation/
      */
     ycomm.dom.getFormElements = function(aFormId, aLineSpec, aOnReady) {
       aLineSpec = aLineSpec || {};
     
       var ret = {},
         aElements = this.selectElements(aFormId),
         fieldName, fieldType, fieldValue,
         editMask,
         storageMask,
         valueType,
         busyCount = 0,
         canChangeRetValue, form = y$(aFormId);
     
       if (form) {
         var fieldPrefix = aLineSpec.elementPrefixName || aLineSpec.prefix || form.getAttribute('data-prefix') || '';
         var fieldPostfix = aLineSpec.elementPostixName || aLineSpec.postfix || form.getAttribute('data-postfix') || '';
         for (var i = 0; i < aElements.length; i++) {
           if (aElements[i].getAttribute) {
             editMask = aElements[i].getAttribute('data-edit-mask') || aElements[i].getAttribute('editMask');
             storageMask = aElements[i].getAttribute('data-storage-mask') || aElements[i].getAttribute('storageMask');
             valueType = aElements[i].getAttribute('data-value-type') || aElements[i].getAttribute('valueType') || 'text';
           } else {
             editMask = '';
             storageMask = '';
             valueType = 'text';
           }
           canChangeRetValue = true;
     
           fieldType = aElements[i].type.toLowerCase();
           fieldName = aElements[i].name || aElements[i].id;
     
           if ((fieldName.substr(fieldName.length, -(fieldPostfix.length)) == fieldPostfix) &&
             (fieldName.substr(0, fieldPrefix.length) == fieldPrefix)) {
     
             fieldName = fieldName.substr(fieldPrefix.length);
             fieldName = fieldName.substr(0, fieldName.length - (fieldPostfix.length));
     
             if (fieldName > '') {
               fieldValue = '';
     
               if ((fieldType == 'radio') ||
                 (fieldType == 'checkbox')) {
                 canChangeRetValue = false;
                 if (typeof ret[fieldName] == 'undefined')
                   ret[fieldName] = '';
               }
     
               switch (fieldType) {
     
                 case "text":
                 case "password":
                 case "textarea":
                 case "email":
                 case "hidden":
                 case "color":
                 case "date":
                 case "datetime":
                 case "datetime-local":
                 case "month":
                 case "search":
                 case "tel":
                 case "time":
                 case "url":
                 case "week":
                   fieldValue = aElements[i].value + "";
                   if ((editMask > '') && (storageMask > '')) {
                     if (valueType.indexOf('date') >= 0) {
                       fieldValue = dateTransform(fieldValue, editMask, storageMask);
                       fieldValue = fieldValue ? fieldValue + "" : "";
                     }
                   }
                   break;
                 case "number":
                 case "range":
                   fieldValue = aElements[i].value;
                   if (isNumber(fieldValue))
                     fieldValue = fieldValue.toFloat();
                   break;
     
                 case "radio":
                 case "checkbox":
                   fieldValue = aElements[i].checked ? aElements[i].value : '';
                   canChangeRetValue = (fieldValue !== '');
                   break;
     
                 case "select-one":
                 case "select-multi":
                   fieldValue = aElements[i].selectedIndex;
                   if (aElements[i].options[fieldValue])
                     fieldValue = aElements[i].options[fieldValue].value;
                   break;
     
                 case "file":
                   if (typeof aOnReady == 'function') {
                     /*
                     http://stackoverflow.com/questions/12090996/waiting-for-a-file-to-load-onload-javascript
                     http://stackoverflow.com/questions/6978156/get-base64-encode-file-data-from-input-form
                     http://igstan.ro/posts/2009-01-11-ajax-file-upload-with-pure-javascript.html
                     https://developer.tizen.org/dev-guide/web/2.3.0/org.tizen.mobile.web.appprogramming/html/tutorials/w3c_tutorial/comm_tutorial/upload_ajax.htm
                     */
                     var reader = new FileReader();
                     busyCount++;
                     reader._fieldName = fieldName;
                     reader.addEventListener("load", function() {
                       ret[this._fieldName] = this.result;
                       busyCount--;
                       if (busyCount <= 0) {
                         aOnReady(ret);
                       }
                     });
                     reader.readAsDataURL(aElements[i].files[0]);
                     canChangeRetValue = false;
                   } else
                     fieldValue = "aOnReady() not present in js call to getFormElements()";
                   break;
               }
               if (typeof fieldValue == 'string') {
                 if (fieldValue.indexOf(',') >= 0)
                   fieldValue = encodeURIComponent(fieldValue);
               }
     
               if (canChangeRetValue)
                 ret[fieldName] = fieldValue;
             }
           }
         }
       }
     
       return ret;
     };
     
     /* add an element to an existent form */
     ycomm.dom.addFormElement = function(aForm, aTagName, aElementAttributes) {
       var aNewElement = document.createElement(aTagName);
       for (var i in aElementAttributes)
         if (aElementAttributes.hasOwnProperty(i))
           aNewElement.setAttribute(i, aElementAttributes[i]);
       aForm.appendChild(aNewElement);
       return aNewElement;
     };
     
     ycomm.dom.URL2post = function(aURL, aTarget, aWindow) {
       if (aURL !== undefined) {
         setTimeout(function() {
           /* if no target was defined, use _self */
           if (aTarget === undefined)
             aTarget = '_self';
     
           /* if no window was defined, use current */
           if (aWindow === undefined)
             aWindow = window;
     
           /* default action is 'body.php' */
           var aAction = 'body.php';
           /* get the method */
           aURL = aURL.split('?');
           if (aURL.length == 2) {
             aAction = aURL[0];
             aURL = aURL[1];
           } else
             aURL = aURL[0];
           /* get the parameters */
           aURL = aURL.split('&');
     
           /* create the temporary form */
           aWindow.auxForm = aWindow.document.createElement("form");
           aWindow.document.body.appendChild(aWindow.auxForm);
           aWindow.auxForm.setAttribute('method', 'post');
           aWindow.auxForm.setAttribute('action', aAction);
           aWindow.auxForm.setAttribute('target', aTarget);
           for (var i = 0; i < aURL.length; i++) {
             var value = aURL[i].split('=');
             if (value.length == 1)
               value[1] = '';
             ycomm.dom.addFormElement(aWindow.auxForm, 'input', {
               'type': 'hidden',
               'id': value[0],
               'name': value[0],
               'value': value[1]
             });
           }
           aWindow.auxForm.submit();
         }, 1000);
       }
     };
     
     ycomm.dom.deleteFieldClass = function(aElementList, aClassName) {
       for (var i in aElementList)
         if (aElementList.hasOwnProperty(i)) {
           y$(i).deleteClass(aClassName);
         }
     };
     
     ycomm.dom.viewport = function() {
       var e = window,
         a = 'inner';
       while (e.parent != e)
         e = e.parent;
       if (!('innerWidth' in window)) {
         a = 'client';
         e = document.documentElement || document.body;
       }
       return { width: e[a + 'Width'], height: e[a + 'Height'] };
     };
     
     /* getX() */
     ycomm.dom.getLeft = function(oElement) {
       var iReturnValue = 0;
       while (oElement) {
         iReturnValue += oElement.offsetLeft;
         oElement = oElement.offsetParent;
       }
       return iReturnValue;
     };
     
     /* getY() */
     ycomm.dom.getTop = function(oElement) {
       var iReturnValue = 0;
       while (oElement) {
         iReturnValue += oElement.offsetTop;
         oElement = oElement.offsetParent;
       }
       return iReturnValue;
     };
     
     ycomm.dom.getPos = function(oElement) {
       for (var lx = 0, ly = 0; oElement !== null; oElement = oElement.offsetParent) {
         lx += oElement.offsetLeft;
         ly += oElement.offsetTop;
       }
       return {
         x: lx,
         y: ly
       };
     };
     
 /* END ycomm-dom.js */
 _dump("ycomm-dom");
 /* START ycomm-msg.js */
     /*********************************************
      * First Version (C) 2014 - esteban daniel dortta - dortta@yahoo.com
      * These routines were written in order to help interprocess process messages
      * but as an remote process messages implementation.
      * Or, IPC over RPC as you like.
      * In Windows(TM) and Linux you would send a message to an application
      * meanwhile, with YeAPF you will send messages to connected users.
      * As this was not inteded to send chat messages, is correct to
      * send messages to and only to connected users.
      **********************************************/
     
     
     var ycommMsgBase = function() {
       var that = {
         messagePeekerTimer: null,
         messageStack: [],
         msgProcs: [],
         _dbgFlag_noMessageProcessorPresent: false,
         msgCount: 0,
         serverOfflineFlag: null
       };
     
       that.grantMsgProc = function(aInterval) {
         /* caso venha sem parÃ¡mtros, calcular um tempo prudente de no mÃ¡ximo 20 segs
          * Isso acontece quando o servidor devolveu uma resposta errada
          * e queremos que o sistema de mensagens continue em operaÃ§Ã£o. */
         if ((aInterval === undefined) || (aInterval <= 0))
           aInterval = Math.min(20000, messagePeekerInterval * 2);
     
         if (that.messagePeekerTimer === undefined) {
           if (that.msgCount === 0)
             _dumpy(16, 1, "Configuring receivers interval to " + aInterval + 'ms');
           that.messagePeekerTimer = setTimeout(ycomm.msg.peek, aInterval);
         } else
           _dumpy(16, 1, "Receivers interval already defined");
       };
     
       that.feedBack = function() {
         if (dRowCount > 0) {
           that.msgCount++;
           for (var j in xData) {
             if (!isNaN(parseInt(j))) {
               var aux = xData[j];
     
               that.messageStack.push(new Array(aux['sourceUserId'],
                 aux['message'],
                 aux['wParam'],
                 aux['lParam']));
             }
           }
     
           if (that.messageStack.length > 0) {
             if (that.msgProcs.length == 0) {
               if (!that._dbgFlag_noMessageProcessorPresent)
                 if (jsDumpEnabled)
                   window.alert("Messages arriving at  '" + _CurrentFileName + "'  but there is not\na registered message processor in order to receive it.\nUse _registerMsgProc() to register it");
               that._dbgFlag_noMessageProcessorPresent = true;
             } else {
               while (that.messageStack.length > 0) {
                 var oldLen = that.messageStack.length;
                 for (var i = 0; i < that.msgProcs.length; i++) {
                   // _dumpy(16,1,"Calling: "+that.msgProcs[i]);
                   var auxCallFunction = '<script>' + that.msgProcs[i] + '();</' + 'script>';
                   auxCallFunction.evalScripts();
                 }
                 if (oldLen == that.messageStack.length)
                   that.messageStack.shift();
               }
             }
           }
     
         }
         that.grantMsgProc(messagePeekerInterval);
       };
     
       that.peek = function() {
         clearTimeout(that.messagePeekerTimer);
         that.messagePeekerTimer = null;
     
         var ts = new Date();
         var auxParameters = 's=y_msg&u=' + u + '&a=peekMessage&formID=' + formID + '&ts=' +
           ts.getTime() + '&callBackFunction=ycomm.msg.feedBack&messagePeekerInterval=' + messagePeekerInterval;
         var aux = new Ajax.Request(
           'query.php', {
             method: 'get',
             asynchronous: true,
             parameters: auxParameters,
             onComplete: function(transport) {
               if (transport.status == 200)
                 _QUERY_RETURN(transport);
               else {
                 _dumpy(16, 1, "*** XMLHttpRequest call failure");
                 setTimeout(that.notifyServerOffline, 500);
               }
             }
           }
         );
       };
     
       that.postMessage = function(aTargetUserID, aMessage, aWParam, aLParam, aBroadcastCondition) {
         var ts = new Date();
         if (aBroadcastCondition != undefined)
           var aux = '&targetUser=*&broadcastCondition="' + aBroadcastCondition + '"';
         else
           var aux = '&broadcastCondition=&targetUser=' + aTargetUserID;
     
         var auxParameters = 's=y_msg&u=' + u + '&a=postMessage' + aux + '&formID=' + formID +
           '&message=' + aMessage + '&wParam=' + aWParam + '&lParam=' + aLParam +
           '&ts=' + ts.getTime() + '&callBackFunction=ycomm.msg.feedBack';
         var aux = new Ajax.Request(
           'query.php', {
             method: 'get',
             asynchronous: false,
             parameters: auxParameters,
             onComplete: _QUERY_RETURN
           }
         );
       };
     
       that.cleanMsgQueue = function() {
         that.msgProcs.length = 0;
       };
     
       that.notifyServerOnline = function() {
         if ((that.serverOfflineFlag==null) || (that.serverOfflineFlag > 0)) {
           that.serverOfflineFlag = 0;
           var notificationArea = y$('notificationArea');
           if (notificationArea)
             notificationArea.style.display = 'none';
     
           if (typeof _notifyServerOnline =='function')
             setTimeout(_notifyServerOnline,500);        
         }
       };
     
       that.notifyServerOffline = function() {
         that.serverOfflineFlag=(that.serverOfflineFlag || 0)+1;
         
         var notificationArea = y$('notificationArea');
         if (!notificationArea) {
           notificationArea = document.createElement('div');
           notificationArea.id = 'notificationArea';
           notificationArea.setOpacity(90);
           document.body.appendChild(notificationArea);
           if (!getStyleRuleValue('.notificationArea')) {
             notificationArea.style.zIndex = 1000;
             notificationArea.style.position = 'absolute';
             notificationArea.style.left = '0px';
             notificationArea.style.top = '0px';
             notificationArea.style.border = '1px #900 solid';
             notificationArea.style.backgroundColor = '#fefefe';
           } else {
             notificationArea.className = 'notificationArea';
           }
     
           notificationArea.innerHTML = "<div style='padding: 32px'><big><b>Server Offline</b></big><hr>Your server has become offline or is misspelling answers when requested.<br>Wait a few minutes and try again later, or wait while YeAPF try again by itself</div>&nbsp;<br><img src='http://yeapf.com/images/yeapf-logo.png' style='width: 128px'>";
         }
     
         notificationArea.style.width = document.body.clientWidth + 'px';
         notificationArea.style.height = document.body.clientHeight + 'px';
         notificationArea.style.display = '';
       
         if (typeof _notifyServerOffline =='function')
           setTimeout(_notifyServerOffline,500);
     
         that.grantMsgProc();
       };
     
       that.registerMsgProc = function(aFunctionName) {
         var canAdd = true;
         _dumpy(16, 1, "Registering message receiver: " + aFunctionName);
         for (var i = 0; i < that.msgProcs.length; i++)
           if (that.msgProcs[i] == aFunctionName)
             canAdd = false;
     
         if (canAdd)
           that.msgProcs[that.msgProcs.length] = aFunctionName;
     
         that.grantMsgProc(messagePeekerInterval);
       };
     
       that.stopMsgProc = function() {
         clearTimeout(that.messagePeekerTimer);
       }
     
       return that;
     }
     
     if ("function" == typeof addOnLoadManager) {
       addOnLoadManager(
         function()
         {
           if (typeof messagePeekerInterval=="undefined") {
             /*
              *  Si existe la bandera flags/debug.javascript, entonces, el tiempo de latencia es mayor
              *  para permitir poder depurar los eventos con calma
              */
             if (typeof jsDumpEnabled == "undefined")
               jsDumpEnabled = 0;
     
             if (jsDumpEnabled==1)
               messagePeekerInterval=15000;
             else
               messagePeekerInterval=750;
           }
     
         }
       );
     }
     
     ycomm.msg = ycommMsgBase();
     
 /* END ycomm-msg.js */
 _dump("ycomm-msg");
 /* START ycomm-sse.js */
       /********************************************************************
       ********************************************************************/
       var ycommSSEBase = function (workgroup, user, dataLocation, pollTimeout, preferredGateway, dbgSSEDiv) {
         var that = {
     
           /* pollTimeout must be between 1 and 60 seconds */
           pollTimeout: Math.min(60000, Math.max(typeof pollTimeout=='number'?pollTimeout:1000, 1000)),
           prefGateway: (preferredGateway || 'SSE').toUpperCase(),
           dbgDiv: y$(dbgSSEDiv),
           debugEnabled: false,
     
           debug: function() {
             var d=(new Date()),
                 line, 
                 dbgClass,
                 isError=false,
                 isWarning=false;
             if (that.debugEnabled) {
               line=pad(d.getHours(),2)+':'+pad(d.getMinutes(),2)+':'+pad(d.getSeconds(),2)+' SSE: ';
     
               for (var i=0; i < arguments.length; i++) {
                 line+=arguments[i].trim()+" ";
                 if (arguments[i].toUpperCase().indexOf('ERROR')>=0)
                   isError=true;
                 if (arguments[i].toUpperCase().indexOf('STATUS')>=0)
                   isWarning=true;
                 if (arguments[i].toUpperCase().indexOf('WARN')>=0)
                   isWarning=true;
               }
               if (isError) {
                 console.error(line);
                 dbgClass='label-danger';
               } else if (isWarning) {
                 console.warn(line);
                 dbgClass='label-warning';
               } else {
                 console.log(line);
                 dbgClass='';
               }
               if ('undefined' != typeof that.dbgDiv) {
                 that.dbgDiv.innerHTML+="<div class='label {0}' style='display: inline-block'>{1}</div>".format(dbgClass, line);
               }          
             }
           },
     
           getLocation: function() {
             return (typeof document=='object' && document.location && document.location.href)?document.location.href:'';
           },
     
           getFolder: function(location) {
             var b=location.lastIndexOf('/');
             return location.substr(0,b+1);
           },
     
           rpc: function(a, params) {
             params = params || {};
             if (typeof that.sse_session_id!="undefined")
               params.sse_session_id = that.sse_session_id;
     
             var p = new Promise(
               function(resolve, reject) {
                 that.debug("OUT: "+a);
                 that.rpcMethod(
                   "_sse", a, params,
                   function(status, error, data) {
                     if (status==200) {
                       resolve(data);
                     } else {
                       reject(status);
                     }
                   },
                   false
                 );
               }
             );
             return p;
           },
     
           poll: function () {
             if (that.pollEnabled) {
               that.rpc("peekMessage").then( function(data) {
                   if (data) {
                     that.debug("IN: data: "+JSON.stringify(data));
                     var eventName;
                     for(var i=0; i<data.length; i++) {
                       if (!that.dispatchEvent(data[i].event, { data: data[i].data } )) {
                         that.message({
                           'data' : data[i].data
                         });
                       }
                     }
                   }
                   setTimeout(that.poll, that.pollTimeout);
                 });
             }
           },
     
           userAlive: function () {
             clearTimeout(that._userAliveScheduler);
     
             var _userAlive = function(data) {
               var toClose=false;
               if (data && data[0]) {
                 toClose = (data[0].event || '').toUpperCase() == 'CLOSE';
               }
               if (toClose) {
                 _userOffline();
               } else {
                 that.debug("IN: User is alive");
                 if (that.state==1) {
                   setTimeout(that.userAlive, that.userAliveInterval*3/4);
                 } else {
                   that.debug("Unexpected SSE state: "+that.state);
                 }
               }
             };
     
             var _userOffline = function(e) {
               that.debug("STATUS: User logged out");
               that.close(e);
             };
     
             if (that.state==1) {
               var p = that.rpc("userAlive");
               p.then(_userAlive).catch(_userOffline);
             }
           },
     
           scheduleUserAlive: function (timeout_ms) {
             timeout_ms = timeout_ms || that.userAliveInterval;
             clearTimeout(that._userAliveScheduler);
             that._userAliveScheduler = setTimeout(that.userAlive, timeout_ms);
           },
     
           attachUser: function (callback) {
             that.rpc(
               "attachUser",
               {
                 "w": workgroup,
                 "user": that.user
               }).then(function(data) {
                 that.debug("IN: attach info");
                 if (data && data[0] && data[0].ok) {
                   that.w                 = workgroup;
                   that.sse_session_id    = data[0].sse_session_id;
                   that.userAliveInterval = Math.min(30000, Math.max(5000,data[0].userAliveInterval * 1000));
                   that.debug("userAliveInterval: "+that.userAliveInterval);
                   callback();
                 }
               });
           },
     
           sendPing: function(callback) {
             if (that.state==1) {
               that.rpc(
                 "ping",
                 {w: workgroup, user: that.user}
               ).then(callback);          
             }
           },
     
           addEventListener: function (eventName, func) {
             /* save the event in the internal list */
             if (typeof that.events == "undefined") {
               that.events={};
             }
             if ("undefined" == typeof that.events[eventName])
               that.events[eventName] = [];
             that.events[eventName].push([that.state, func]);
     
             if (that.state==1) {
               if (!that.pollEnabled)
                 that.evtSource.addEventListener(eventName, func);
               if (trim(eventName.toLowerCase()) == "ready") {
                 that.dispatchEvent("ready");
               }
             }
             return that;
           },
     
           dispatchEvent: function (eventName, params) {
             var ret=false;
             if (that.state==1) {
               if (typeof that.events !== "undefined") {
                 for(var implementations=0; implementations<(that.events[eventName] || []).length; implementations++) {
                   var eventDef = that.events[eventName][implementations];
                   eventDef[1](params);
                   ret |= true;
                 }
               }
     
               eventName1=eventName;
               eventName2="on_"+eventName;
               if (typeof that[eventName1] == "function") {
                 that[eventName1](params);
                 ret |= true;
               } else if (typeof that[eventName2] == "function") {
                 that[eventName2](params);
                 ret |= true;
               } else
                 ret |= false;
             }
             return ret;
           },
     
           startPolling : function() {
             clearTimeout(that.evtGuardian);
             that.pollEnabled=true;
             that.state=1;
             that.dispatchEvent("ready", {"gateway": "Polling"});
             setTimeout(that.poll, 125);
             that.debug("STATUS: polling for messages. pollTimeout: {0}ms".format(that.pollTimeout));
           },
     
           guardianTimeout: function (e) {
             that.debug("Guardian Timeout! Let's use polling mode");
             /* if SSE.PHP don't answer up to guardian timeout, use poll version */
             clearTimeout(that.evtGuardian);
             that.__destroy__();
             that.startPolling();
           },
     
           __destroy__: function() {
               that.closing=true;
               clearTimeout(that.evtGuardian);
               that.debug('DESTROYING');
               that.state=-1;
               that.evtSource=null;
               that.GUID=null;
           },
     
           close: function(e) {
             clearTimeout(that.evtGuardian);
             if (!that.closing) {
               that.closing=true;
               that.state=0;
               that.debug("STATUS: CLOSE");
               that.rpc(
                 "detachUser",
                 {
                   w: workgroup,
                   user: that.user
                 }).then(function() {
                   that.state=-1;
                   that.pollEnabled = false;
                   that.__destroy__();
                   that.closing=false;              
                 }).catch(function() {
                   that.closing=false; setTimeout(that.close, 1500);
                 });
             }
           },
     
           closeEvent: function(e) {
             clearTimeout(that.evtGuardian);
             that.__destroy__();
           },
     
           error: function(e) {
             clearTimeout(that.evtGuardian);
             that.debug("ERROR: while using SSE");
             that.dispatchEvent('onerror');
           },
     
           openEvent: function (e) {
             clearTimeout(that.evtGuardian);
             that.debug("STATUS: OPEN");
             /* the first UAI happens in 1/100th after OPEN */
             that.scheduleUserAlive(that.userAliveInterval/100);
             that.dispatchEvent('onopen');
             
             if ((ycomm.msg) && (typeof ycomm.msg.notifyServerOnline =='function'))
               ycomm.msg.notifyServerOnline();
           },
     
           message: function (e) {
             /* as connected, clear guardian timeout */
             clearTimeout(that.evtGuardian);
             if ((!e) || (e.target.readyState==2)) {
               that.close();
             } else {
               that.debug("MESSAGE");
               if (that.state===0) {
                 /* firs message just synchro the message table */
                 that.state=1;
                 that.debug("userAliveInterval: {0}ms".format(that.userAliveInterval));
     
                 for(var eventName in that.events) {
                   if (that.events.hasOwnProperty(eventName)) {
                     for(var implementations=0; implementations<that.events[eventName].length; implementations++) {
                       var eventDef = that.events[eventName][implementations];
                       if (eventDef[0]<1) {
                         eventDef[0]=1;
                         that.evtSource.addEventListener(eventName, eventDef[1]);
                       }
                     }
                   }
                 }
                 that.dispatchEvent("onready", {"gateway": "SSE"});
               }
     
               if (that.state>0) { 
                 if (typeof that.onmessage=="function") {
                   that.onmessage(e.data);
                 }
               }
             }
           },
     
           startup: function(gateway) {
             gateway = (gateway || that.prefGateway || 'SSE').toUpperCase();
             that.prefGateway = gateway;
             if (that.evtGuardian) {
               clearTimeout(that.evtGuardian);
             }
             if (typeof that.GUID != "string") {
               that.GUID=generateUUID();
               that.attachUser(
                 function() {
                   that.state=0;
                   /* first try to use EventSource() */
                   if ((that.prefGateway=='SSE') && (typeof window.EventSource == "function")) {
                     that.debug("INFO: Attaching events");
                     if (!that.evtSource) {
                       that.debug("INFO: All new evtSource");
                       that.evtSource = new EventSource(that._dataLocation_+"?si="+md5(that.sse_session_id));
                       that.evtSource.addEventListener("error",   that.error,   false);
                     } else {
                       that.debug("INFO: Reusing component");
                     }
     
                     that.evtSource.onopen    = that.openEvent;
                     that.evtSource.onclose   = that.closeEvent;
                     that.evtSource.onmessage = that.message;
                     
                     that.debug("INFO: Configuring guardianTimeout");
                     that.evtGuardian = setTimeout(that.guardianTimeout, 30000);
                   } else {
                     that.startPolling();
                   }
                 }
               );
             } else {
               that.debugEnabled=true;
               that.debug("ERROR: SSE object already initialized");
             }
           },
     
           reset: function() {
             that.close();
             that.__destroy__();
             setTimeout(that.startup, 3500);
           },
     
           init: function() {
             that.state = -1;
             if ((typeof dataLocation=="undefined") || (dataLocation === null)) {
               /* default data location is current location/sse.php */
               that._dataLocation_ = (
                 function() {
                   var a = that.getLocation();
                   var b = a.lastIndexOf('/');
                   return a.substr(0,b+1)+'sse.php';
                 }
               )();
             } else {
               that._dataLocation_=dataLocation;
             }
     
             if (that._dataLocation_.substr(0,5)=="file:") {
               that.debug("ERROR: '"+that._dataLocation_+"' is not a correct data location");
             } else {
               /* create user id */
               that.user = ((user !==null) && (typeof user != "undefined"))?user:generateUUID();
     
               /* check same-source */
               var l1=that.getFolder(that.getLocation()),
                   l2=that.getFolder(that._dataLocation_);
     
               /* chose better way to communicate with server */
               if (l1==l2) {
                 that.rpcMethod = ycomm.invoke;
               } else {
                 that.rpcMethod = ycomm.crave;
               }
     
               that.startup();
     
             }
     
             return that;
           }
         };
     
         return that.init();
       };
     
 /* END ycomm-sse.js */
 _dump("ycomm-sse");
 /* START ycalendar.js */
     /*********************************************
      * First Version (C) August 2013 - esteban daniel dortta - dortta@yahoo.com
     **********************************************/
     
     
     
       /*
        * cfg elements
        *   view: 0 || 1 || 2
        *   orientation: 0 (landscape) || 1 (portrait)
        *   date: Date()
        *   dateScope { first: YYMMDDhhmmss, last: YYMMDDhhmmss}
        *   dayEntryDivision: 20 (minutes)
        *   cellSize { width: integer(px), height: integer (px) }
        *   divContainerName: string
        */
     
       var yCalendar = function (cfg) {
         var that = { };
     
         that.cfg = cfg || { };
         /*
           0-month
           1-week
           2-day
         */
         that.cfg.view = +(that.cfg.view || 0);
         /*
          *  0 - landscape
          *  1 - portrait
          */
         that.cfg.orientation = +(that.cfg.orientation || 0);
     
         /*
          * highlight date (or today as default)
          */
         that.cfg.date = (that.cfg.date || new Date());
     
         /*
          * common division for day visualization (minutes)
          */
         that.cfg.dayEntryDivision = that.cfg.dayEntryDivision || 20;
     
         /*
          * cell size in pixels
          */
         if (that.cfg.cellSize) {
           that.cfg.cellSize.width = that.cfg.cellSize.width || null;
           that.cfg.cellSize.height = that.cfg.cellSize.height || null;
         } else
           that.cfg.cellSize = { width: null , height: null};
     
         /*
          * div container where to place the calendar
          */
         that.cfg.divContainerName = that.cfg.divContainerName || '';
     
         /*
          * callback function to be called on different moments
          */
         that.cfg.callback = that.cfg.callback || null;
     
         that.context = { };
         that.context.dateScope = { first: '', last: '' };
         that.context.nCols = 0;
         that.context.nRows = 0;
     
         /*
          * configuration functions
          */
     
         /*
          * set the container (div) to place the calendar
          */
         that.setDivContainerName = function(aDivName) {
           that.cfg.divContainerName = aDivName;
           return that;
         }
     
         /*
          * set cell size (width x height) in pixels
          */
         that.setCellSize = function(aCellWidth, aCellHeight) {
           that.cfg.cellSize.width = that.cfg.cellSize.width || aCellWidth;
           that.cfg.cellSize.height = that.cfg.cellSize.height || aCellHeight;
           return that;
         }
     
         that.setView = function(aView) {
           that.cfg.view = +(aView) % 3;
           return that;
         }
     
         that.setCallback = function(aCallback) {
           that.cfg.callback = aCallback;
           return that;
         }
     
         that.setDate = function(aDate) {
           that.cfg.date = aDate || that.cfg.date;
           return that;
         }
     
         that.getDate = function() {
           return that.cfg.date;
         }
         /*
          * set calendar orientation (0-landscape 1-portrait)
          */
         that.setOrientation = function(aOrientation) {
           that.cfg.orientation = (+(aOrientation) % 2);
           return that;
         }
     
         /*
           style that will be used
             calBand,
             calDayLCell, calWeekLCell, calMonthLCell
             calDayPCell, calWeekPCell, calMonthPCell
             calEmptyDayCel, calEmptyWeekCell, calEmptyMonthCell
         */
         that.draw = function(aCaller) {
           var orientationTag = ['L', 'P'];
           var theDiv = y$(that.cfg.divContainerName);
           if (theDiv) {
             try {
               /* status = 0.  DOM BEING CREATED */
               that.cfg.status = 0;
               if (that.cfg.callback != null)
                 that.cfg.callback(that, 'DOMLocked', theDiv);
     
               var aTag = null,
                   aCellID = null,
                   aTagClass = null,
                   aCellContent = null,
                   aAuxTag = null,
                   aDiv = null,
                   aSpan = null,
                   aText = null;
     
               /* month ans week views increments in day chunks */
               if (that.cfg.view<2)
                 var inc = 24 * 60 * 60 * 1000;
               else
                 var inc = that.cfg.dayEntryDivision * 60 * 1000;
     
               var colNumber = 0, rowNumber=0;
               /*
                * create a class base name to be used with all the elements
                * that is: calDay followed by L(landscape) or P (portrait)
                */
               var classBaseName = 'calDay'+orientationTag[that.cfg.view % 2];
     
               /* remove all children nodes */
               while (theDiv.hasChildNodes()) {
                 theDiv.removeChild(theDiv.lastChild);
               }
     
               /* create the calendar table */
               that.context.oCalTable = document.createElement('table');
               that.context.oCalTable.cellPadding=0;
               that.context.oCalTable.cellSpacing=0;
     
               var oTR = that.context.oCalTable.insertRow(-1);
               var oTD = oTR.insertCell();
               oTD.className = 'calBand';
               var openRow = true;
     
               var emptyCellCount = 0;
               var extraStyle = { };
     
               if (that.cfg.cellSize.height != null)
                 extraStyle.height = parseInt(that.cfg.cellSize.height) + 'px';
               if (that.cfg.cellSize.width != null)
                 extraStyle.width = parseInt(that.cfg.cellSize.width) + 'px';
     
               var d1 = that.context.dateScope.first;
               var d2 = that.context.dateScope.last;
     
               d1.setHours(12);
               d2.setHours(12);
     
               var createEmptyCell = function() {
                   /* create an unique ID for the empty day */
                   aCellID = that.cfg.divContainerName+"_empty_"+emptyCellCount;
     
                   /* create an empty day */
                   var aDiv = document.createElement('div');
                   mergeObject(extraStyle, aDiv.style);
                   aDiv.id = aCellID;
                   aDiv.className = classBaseName+"Cell "+classBaseName+"EmptyCell";
                   oTD.appendChild(aDiv);
     
                   if (that.cfg.orientation==0)
                     colNumber++;
                   emptyCellCount++;
     
                   /* call callback function */
                   if (that.cfg.callback!= null)
                     that.cfg.callback(that, 'getEmptyDayContent', aDiv);
               }
     
               var createFilledCell = function (aTagType) {
                   if (aTagType==0) {
                     aCellID = that.cfg.divContainerName+'_day_'+d.toUDate().substring(0,8);
                     aTag = d.getDate();
                   } else {
                     aTag = d.getHours()+':'+d.getMinutes();
                     aCellID = that.cfg.divContainerName+'_day_'+d.toUDate().substring(0, 12);
                   }
               }
     
               var d = new Date(d1);
               var interactions = (d2 - d) / inc + 1;
     
               if (that.cfg.view === 0) {
                 if (that.cfg.orientation==0) {
                   for(n = 0; n < d1.getDay(); n++) {
                     createEmptyCell();
                   }
                 } else {
                   d.setDate( d.getDate() - d1.getDay() );
                   var dOffset = [];
                   var dAux = new Date(d);
                   for(n = 0; n < that.context.nRows; n++) {
                     dOffset[n] = new Date(dAux);
                     dAux.setDate(dAux.getDate()+1);
                   }
                 }
               }
     
               while (interactions>0) {
                 if (!openRow) {
                   oTR = that.context.oCalTable.insertRow(-1);
                   oTD = oTR.insertCell();
                   oTD.className = 'calBand';
     
                   openRow = true;
                 }
     
                 aTag = '';
     
                 if (that.cfg.orientation==1) {
                   if ((d<d1) || (d>d2))
                     createEmptyCell();
                   else {
                     if ((that.cfg.view === 0) || (that.cfg.view === 1)) {
                       createFilledCell(0);
                     } else if (that.cfg.view === 2){
                       createFilledCell(1);
                     } else
                     _dumpy(8,1,"Not implemented");
     
                   }
     
                 } else if (that.cfg.orientation==0) {
     
                   if ((that.cfg.view === 0) || (that.cfg.view === 1)) {
     
                     createFilledCell(0);
     
                   } else if (that.cfg.view === 2) {
     
                     createFilledCell(1);
     
                   } else
                     _dumpy(8,1,"Not implemented");
                 }
     
                 if (aTag>'') {
     
                   aTagClass = classBaseName+'Cell';
                   if (d.getDay() === 0)
                     aTagClass += ' '+classBaseName+'FreeCell';
     
                   if (d.getDate()==that.cfg.date.getDate())
                     aTagClass += ' '+classBaseName+'Highlight';
     
                   /* create a day container */
                   aDiv = document.createElement('div');
                   mergeObject(extraStyle, aDiv.style);
                   aDiv.id = aCellID;
                   aDiv.className = aTagClass;
                   mergeObject(extraStyle, aDiv.style);
                   aDiv.date = d;
     
                   /* create a day tag */
                   aSpan = document.createElement('span');
                   aSpan.id = aCellID+"_tag";
                   aSpan.className = 'calTag';
                   if (that.cfg.callback!= null) {
                     aAuxTag = that.cfg.callback(that, 'getTagContent', aSpan) || '';
                     if (aAuxTag>'')
                       aTag = aAuxTag;
                   }
                   aSpan.innerHTML = aTag;
                   aDiv.appendChild(aSpan);
                   if (that.cfg.callback!= null) {
                     aText = that.cfg.callback(that, 'getCellContent', aDiv) || '';
                     if (aText>'') {
                       aDiv.innerHTML += aText;
                     }
                   }
                   oTD.appendChild(aDiv);
     
                 }
     
                 if (that.cfg.orientation==1) {
                   d.setTime(d.getTime()+inc * that.context.nRows);
                 } else
                   d.setTime(d.getTime()+inc);
     
                 colNumber++;
                 if(colNumber>=that.context.nCols) {
                   colNumber = 0;
                   rowNumber++;
                   openRow = false;
                   if (that.cfg.orientation==1)
                     d=dOffset[rowNumber];
                 }
     
                 interactions--;
               }
     
               if (openRow) {
                 while (colNumber<that.context.nCols) {
                   createEmptyCell();
                 }
                 colNumber = 0;
                 openRow = false;
               }
     
               theDiv.appendChild(that.context.oCalTable);
     
             } catch(err) {
               _dumpy(8,1,'ERROR: '+err.message);
             }
             // status = 1.  DOM READY
             that.cfg.status = 1;
             if (that.cfg.callback!= null)
               that.cfg.callback(that, 'DOMReleased', theDiv);
           }
           return that;
         }
     
         that.build = function(aDate, aView, aOrientation) {
     
           that.cfg.orientation = aOrientation || that.cfg.orientation;
           that.cfg.view = aView || that.cfg.view;
           that.cfg.date = aDate || that.cfg.date;
     
           var theDiv = y$(that.cfg.divContainerName);
           if (theDiv) {
             var d1 = new Date(that.cfg.date),
                 d2 = null,
                 secondsPerDay = 24 * 60 * 60 * 1000,
                 nCols = 0,
                 nRows = 0;
     
             switch (that.cfg.view) {
               case 0:
                 // month view.
                 nCols = 7;
                 nRows = 5;
     
                 // Recalculate dateScope
                 d1.setDate(1);
     
                 var d2 = new Date(d1);
                 d2.setDate(d1.daysInMonth());
     
                 break;
     
               case 1:
                 // week view.
                 nCols = 1;
                 nRows = 7;
     
                 // Recalculate dateScope
                 var d1 = new Date(that.cfg.date);
                 while (d1.getDay()>0)
                   d1.setTime(d1.getTime()-secondsPerDay)
     
                 var d2 = new Date(d1);
                 d2.setTime(d1.getTime()+secondsPerDay * 6);
     
                 break;
     
               case 2:
                 // day view
                 nCols = 1;
                 nRows = Math.round(24 * 60 / that.cfg.dayEntryDivision);
     
                 // Recalculate dateScope
                 d1.setHours(6);   // <--- Need more config there
                 d1.setMinutes(0); // <--- Need more config there
     
                 var d2 = new Date(d1);
                 d2.setHours(21);  // <--- Need more config there
                 d2.setMinutes(60-that.cfg.dayEntryDivision);
     
                 break;
     
               default:
                 _dumpy(8,1,"Not implemented");
             }
     
             that.context.dateScope.first = d1;
             that.context.dateScope.last = d2;
     
     
             if (that.cfg.orientation === 1) {
               that.context.nCols = nRows;
               that.context.nRows = nCols;
             } else {
               that.context.nCols = nCols;
               that.context.nRows = nRows;
             }
     
             that.draw(that);
     
             _dumpy(8,1,"Build calendar on "+that.cfg.date.toUDate()+" View: "+that.cfg.view+" Orientation: "+that.cfg.orientation+" cols: "+nCols+" rows: "+nRows);
           } else
             _dumpy(8,1,"ERROR: "+that.cfg.divContainerName+" not found on that page");
           return that;
         }
     
         that.each = function(aFunction) {
           if (typeof aFunction == 'function') {
             if (that.context.oCalTable) {
               var idSeed = that.cfg.divContainerName+"_day_";
               var processElement = function (aTagSpec) {
                 var elements = that.context.oCalTable.getElementsByTagName(aTagSpec);
                 for (var i=0; i<elements.length; i++)
                   if (elements[i].id.substr(0,idSeed.length)==idSeed)
                     aFunction(elements[i]);
               }
               processElement('div');
               processElement('span');
             }
           }
           return that;
         };
         return that;
       };
     
 /* END ycalendar.js */
 _dump("ycalendar");
 /* START ydyntable.js */
     /*********************************************
      * First Version (C) 2009 - esteban daniel dortta - dortta@yahoo.com
     **********************************************/
     
     
     
     function _dynCheckChilds(aStack, aField)
     {
       var childOpenCondition=aField.childOpenCondition;
       if (childOpenCondition>'') {
         var aResult = Parser.evaluate(childOpenCondition, aStack);
         var childsContainer=y$(aField.name+'_childs');
         if (childsContainer) {
           if (aResult)
             childsContainer.style.display='block';
           else
             childsContainer.style.display='none';
         }
       }
     }
     
     function dynCheckChids(aFieldList, e)
     {
       if (e==undefined)
         var e = window.event || arguments.callee.caller.arguments[0];
       if (e.target)
         e = e.target;
     
       // temporary stack to call Parser
       var aStack=new Array();
       for(var f in aFieldList)
         if ((aFieldList.hasOwnProperty(f)) && (y$(f)))
           aStack[f]=y$(f).value;
     
       // check if it is entering from a form field
       if (aFieldList[e.id])
         _dynCheckChilds(aStack, aFieldList[e.id]);
       else {
         for(var f in aFieldList)
           if (aFieldList.hasOwnProperty(f))
             _dynCheckChilds(aStack, aFieldList[f]);
       }
     }
     
     function _dynConfigOnChange(aElement, onChange)
     {
       if (aElement) {
         if (aElement.onchange != __cbOnChange__) {
           aElement.dynOnChange=aElement.onchange;
           if (onChange==undefined)
             aElement.onchange=__dynOnChange__;
           else
             aElement.onchange=onChange;
         }
       }
     }
     
     function dynConfigOnchange(aElementList, onChange)
     {
       if (typeof(aElementList) != 'object')
         var aList = aElementList.split(',');
       else
         var aList=aElementList;
       if (aList.length>0) {
         for(var i=0; i<aList.length; i++)
           _dynConfigOnChange(y$(aList[i]), onChange);
       } else
         for(var i in aList)
           if (aList.hasOwnProperty(i))
             _dynConfigOnChange(y$(i), onChange);
     }
     
     /*
      * Sometimes you need to attach some fields display to a checkbox situation
      * This function set onChange event of all the aElementSet
      */
     function dynConfigCheckBoxChilds(aSaveOnChange, aElementSet)
     {
       if (aElementSet==undefined)
         var aElementSet = document.getElementsByTagName('input');
     
       if (aSaveOnChange==undefined)
         var aSaveOnChange=false;
     
       var auxID;
       var ccDependents;
       var allElements=document.getElementsByTagName('*');
       for(var i=0; i<aElementSet.length; i++)
       {
         var canModifyEvents=false;
         if (aElementSet[i].type=='checkbox') {
           auxID = aElementSet[i].id+'.';
           ccDependents=0;
           for (var n=i+1; n<allElements.length; n++) {
             var aID=allElements[n].id;
             if (typeof aID == "string")
               if (aID.substr(0,auxID.length)==auxID) {
                 dynSetElementDisplay(allElements[n].id, aElementSet[i].id, aElementSet[i].value);
                 // aElementSet[n].style.visibility=aElementSet[i].checked?'visible':'hidden';
                 ccDependents++;
               }
           }
           canModifyEvents=true;
         } else if (aElementSet[i].type=='text') {
           canModifyEvents=true;
         }
     
         if (canModifyEvents) {
           if (aElementSet[i].onchange != __cbOnChange__) {
             aElementSet[i].dynOnChange=aElementSet[i].onchange;
             aElementSet[i].onchange=__cbOnChange__;
             aElementSet[i].dynSaveOnChange=aSaveOnChange;
           }
         }
       }
     }
     
     function dynTableEnumerateCellElements(aTable, aFunction, aFirstRow)
     {
       if (aFirstRow==undefined)
         aFirstRow=2;
     
       for(var r=aFirstRow; r<aTable.rows.length; r++)
         for (var c=0; c<aTable.rows[r].cells.length; c++) {
           aCell=aTable.rows[r].cells[c];
           var cellElements = aCell.getElementsByTagName('*');
           for(var e in cellElements)
             if (cellElements.hasOwnProperty(e))
               aFunction(cellElements[e]);
         }
     }
     
     
     function dynRenumberElements(e, aTable, aElementsSeed)
     {
       var aLines= new Array();
       var aSequence=0;
     
       dynTableEnumerateCellElements(aTable, function (aElement) {
         if (typeof aElement.id != 'undefined') {
           var idInfo=aElement.id.split('.');
           if (aElementsSeed.indexOf(idInfo[0])>0) {
             var aNdx=str2int(idInfo[1]);
             if (aLines.indexOf(aNdx)<0)
               aLines[aSequence++]=aNdx;
           }
         }
       }, 1);
     
       for(var i in aLines)
         if (aLines.hasOwnProperty(i)) {
           var aNdx=aLines[i];
           for(var n in aElementsSeed)
             if (aElementsSeed.hasOwnProperty(n)) {
               document.getElementById(aElementsSeed[n]+'.'+zeroPad(aNdx,2)).id = aElementsSeed[n]+'.'+zeroPad(i,2);
             }
         }
     }
     
     function _dynCleanTableRow(e, aTable, aRowNdx)
     {
       for(var n=0; n<aTable.rows[aRowNdx].childNodes.length; n++)
         dynCleanChilds(e, aTable.rows[aRowNdx].childNodes[n], true);
     }
     
     function dynCleanChilds(e, aDOMObject, aCleanAll, aChangeVisibility)
     {
       if (aDOMObject instanceof Text)
         aDOMObject = aDOMObject.nextSibling;
       if (aDOMObject!=undefined) {
         if (aCleanAll==undefined)
           aCleanAll=false;
         if (aChangeVisibility==undefined)
           aChangeVisibility=true;
     
         var auxID;
         if (!((e==undefined) ||(e==null)))
           auxID = e.id+'.';
         else
           auxID = '*.';
     
         var allElements=aDOMObject.getElementsByTagName('*');
         for (var i=0; i<allElements.length; i++)
         {
           var aID=allElements[i].id;
           if (typeof aID == 'string')
             if ((aID.substr(0,auxID.length)==auxID) || (aCleanAll) || (auxID=='*.')) {
               if ((aChangeVisibility) && (auxID!='*.'))
                 dynSetElementDisplay(allElements[i].id, e.id, e.value);
     
               if ((auxID=='*.') || (!e.checked)) {
                 // limpar conteÃºdo dos campos dependentes
                 // caso esteja ocultando
                 if (allElements[i].type=='checkbox')
                   allElements[i].checked=false;
                 if (allElements[i].type=='radio')
                   allElements[i].checked=false;
                 if (allElements[i].type=='text')
                   allElements[i].value='';
                 if (allElements[i].type=='number')
                   allElements[i].value='';
     
                 if (allElements[i].rows != undefined) {
                   // keeps the first two lines of the table and eliminates the rest
                   // the first contains the titles
                   // the second contains the fields template and buttons
                   while (allElements[i].rows.length>2) {
                     _dynCleanTableRow(e, allElements[i],2);
                     allElements[i].deleteRow(2);
                   }
     
                   // clean the contents of the second row
                   if (allElements[i].rows.length>1)
                     for(var n=0; n<allElements[i].rows[1].childNodes.length; n++)
                       dynCleanChilds(e, allElements[i].rows[1].childNodes[n], true, false);
                 } else if (allElements[i].cells != undefined) {
                   for(var c=0; c<allElements[i].cells.length; c++)
                     dynCleanChilds(e, allElements[i].cells[c], true, aChangeVisibility);
                 } else
                   if (allElements[i].type!=undefined)
                     __cbOnChange__(allElements[i]);
               }
             }
         }
       }
     }
     
     function dynTableDelRow(e, aHeaderRowCount)
     {
       if (aHeaderRowCount==undefined)
         aHeaderRowCount=2;
       while ((e!=undefined) && (!(e instanceof HTMLTableRowElement)))
         e=e.parentNode;
       if (e) {
         var table = e.parentNode;
         if (table.rows.length>aHeaderRowCount) {
           _dynCleanTableRow(e, table, e.rowIndex);
           table.deleteRow(e.rowIndex);
         } else  {
           _dynCleanTableRow(e, table, e.rowIndex);
           /*
           for (var c=0; c<e.cells.length; c++) {
             var aCell=e.cells[c];
             for (var i=0; i<aCell.childNodes.length; i++) {
               var el=aCell.childNodes[i];
               el.value=null;
             }
           }
           */
         }
       } else
         alert("Your button is outside a table");
     }
     
     function dynTableDelAllRows(aTable)
     {
       while(aTable.rows.length>0)
         dynTableDelRow(aTable.rows[0],0);
     }
     
     function _dynExplodeTag(aTag)
     {
       var sequence = aTag.match(/\d+$/)[0];
       var name = aTag.substr(0,aTag.length-sequence.length);
       return [name, sequence];
     }
     
     function dynTableCloneRow(e, onchange)
     {
       while ((e!=undefined) && (!(e instanceof HTMLTableRowElement)))
         e=e.parentNode;
       if (e) {
         var table = e.parentNode;
         var obj = e.cloneNode(true);
         // Find a special object inside cells like INPUT elements
         // so we can pick the name and use it as base for next name
         // A name or id is composed of 'name' followed by 'sequence'
         // for example: 'product01' is a valid tag
         for (var c=0; c<obj.cells.length; c++) {
           var aCell=obj.cells[c];
           for (var i=0; i<aCell.childNodes.length; i++) {
             var el=aCell.childNodes[i];
             if (el.id>'') {
               var elID = _dynExplodeTag(el.id);
               var curSeq=elID[1];
               curSeq=0;
               while (y$(elID[0]+zeroPad(curSeq,2)))
                 curSeq++;
               el.id=elID[0]+zeroPad(curSeq,2);
               el.name=el.id;
               el.onchange=onchange;
             }
             el.value=null;
           }
         }
         var newRow=table.insertBefore(obj,e.nextSibling);
         return newRow;
       } else {
         alert("Your button is outside a table");
         return null;
       }
     }
     
     function dynTableCloneLastRow(aTableId)
     {
       var oTable=y$(aTableId);
       if (oTable.rows.length>0) {
         var r=oTable.rows[oTable.rows.length-1];
         dynTableCloneRow(r);
       }
     }
     
     function dynSetElementDisplay(aElementID, aMasterFieldName, aMasterFieldValue)
     {
       var element=y$(aElementID);
       if (element) {
         var field=y$(aMasterFieldName);
         if (field) {
           var theFieldValue;
           if (field.type=='radio') {
             var auxRadio=document.getElementsByName(aMasterFieldName);
             // if not master value defined, we assume the last one of the series is the desired trigger value
             if (aMasterFieldValue==undefined)
               if (auxRadio.length>0)
                 aMasterFieldValue=auxRadio[auxRadio.length-1].value;
     
             for(var i=0; i<auxRadio.length; i++)
               if (auxRadio[i].checked)
                 theFieldValue=auxRadio[i].value;
           } else if (field.type=='checkbox') {
             if (field.checked)
               theFieldValue=field.value;
           } else
             theFieldValue=field.value;
     
           if (aMasterFieldValue==undefined)
             aMasterFieldValue=theFieldValue;
     
     
           if (aMasterFieldValue==theFieldValue)
             var aDisplay='';
           else
             var aDisplay='none';
     
           if (element.type=='table') {
             for (var r=0; r<element.rows.length; r++)
               element.rows[r].style.display=aDisplay;
           } else
             element.style.display=aDisplay;
         }
       }
     }
     
     function dynSetDisplay(aElementSet, aDisplayStyle)
     {
       for(var i=0; i<aElementSet.length; i++) {
         aElementSet[i].style.display=aDisplayStyle;
         _dumpy(2,1,aElementSet[i].id, aElementSet[i].style.display);
       }
     }
     
     function dynSetVisibility(aElementSet, aVisibility)
     {
       for(var i=0; i<aElementSet.length; i++) {
         aElementSet[i].style.visibility=aVisibility;
         _dumpy(2,1,aElementSet[i].id, aElementSet[i].style.visibility);
       }
     }
     
     function dynRemoveElements(aElementSet)
     {
       for(var i=aElementSet.length-1; i>=0; i--) {
         var pai = aElementSet[i].parentNode;
         pai.removeChild(aElementSet[i]);
       }
     }
     
     function dynTablePrint(aTable, aWidth, aHeight)
     {
     }
     
     function calcGridAddItem(aCalcGrid, aFieldValues, aNewFieldName, aTitle, aFieldList, aCalcExpr, aResultCellPostfix, aForce, aUnits, aDecimalPlaces)
     {
       if (aForce==undefined)
         aForce=false;
     
       if (aUnits==undefined)
         aUnits='';
     
       if (aDecimalPlaces==undefined)
         aDecimalPlaces='2';
     
       var canAdd = true;
       var aFields = aFieldList.split(',');
       if (!aForce) {
         for(var i in aFields)
           if (aFields.hasOwnProperty(i))
             if (aFieldValues[aFields[i]]==undefined)
               canAdd = false;
       }
       if (canAdd) {
         if (aCalcGrid[aNewFieldName]==undefined) {
           aCalcGrid[aNewFieldName]=new Array();
           aCalcGrid[aNewFieldName]['title']=aTitle;
           aCalcGrid[aNewFieldName]['fieldList']=aFieldList;
           aCalcGrid[aNewFieldName]['calcExpr']=aCalcExpr;
           aCalcGrid[aNewFieldName]['resultCellPostfix']=aResultCellPostfix;
           aCalcGrid[aNewFieldName]['units']=aUnits;
           aCalcGrid[aNewFieldName]['decimalPlaces']=aDecimalPlaces;
         } else
           console.log("Field '"+aNewFieldName+"' already exists in calcGrid");
       } else
         console.log("Some fields does not exists in ("+aFieldValues+") list");
     }
     
     var _cg_rules = new Array();
     
     function calcGridSetRules(aCalcGrid, aFieldList, aOnColumns)
     {
       if (_cg_rules[aCalcGrid.id]==undefined)
         _cg_rules[aCalcGrid.id] = new Array();
       _cg_rules[aCalcGrid.id]['rules']=aFieldList;
       _cg_rules[aCalcGrid.id]['onColumns']=aOnColumns;
     }
     
     function calcGridSetCellsGuides(aCalcGrid, aColSet, aRowSet)
     {
       if (_cg_rules[aCalcGrid.id]==undefined)
         _cg_rules[aCalcGrid.id] = new Array();
       _cg_rules[aCalcGrid.id]['area']=new Array();
       _cg_rules[aCalcGrid.id]['area']['colSet']=aColSet;
       _cg_rules[aCalcGrid.id]['area']['rowSet']=aRowSet;
     }
     
     function calcGridGetColsGuide(aCalcGrid)
     {
       var ret=null;
       if (aCalcGrid!=undefined) {
         if (_cg_rules[aCalcGrid.id]!=undefined) {
           ret = _cg_rules[aCalcGrid.id]['area']['colSet']
         }
       }
     
       return ret;
     }
     
     function calcGridGetRowsGuide(aCalcGrid)
     {
       var ret=null;
       if (aCalcGrid!=undefined) {
         if (_cg_rules[aCalcGrid.id]!=undefined) {
           ret = _cg_rules[aCalcGrid.id]['area']['rowSet']
         }
       }
     
       return ret;
     }
     
     function calcGridEnumerateCells(aCalcGrid, aFunction, aSchema)
     {
       var ok=false;
       var aRules = _cg_rules[aCalcGrid.id];
       if (aRules) {
         var aColSet=aRules['area']['colSet'];
         var aRowSet=aRules['area']['rowSet'];
         for(var r in aRowSet) {
           if (aRowSet.hasOwnProperty(r)) {
             for(var c in aColSet)
               if (aColSet.hasOwnProperty(c)) {
                 var aCell=y$(r+'_'+aColSet[c].name);
                 if (aCell) {
                   if (aSchema!=undefined) {
                     ok=false;
                     if (aSchema.editable)
                       ok=(aColSet[c].editable && aRowSet[r].editable);
                     if (aSchema.name)
                       ok=ok || (aColSet[c].name==aSchema.name) || (aRowSet[r].name==aSchema.name);
                   } else
                     ok=true;
                   if (ok)
                     aFunction(aCell);
                 }
               }
           }
         }
       }
     }
     
     function calcGridCleanContent(aCalcGrid)
     {
       var aFields=new Array();
       aFields['value']='';
       aFields['calcGridSet']=aCalcGrid.id;
     
       calcGridEnumerateCells(aCalcGrid,
         function(a)
         {
           dynSetCellValue(a.id, aFields);
         }
       );
     }
     
     function calcGridCleanColumn(aCalcGrid, aColKey)
     {
       var aRules = _cg_rules[aCalcGrid.id];
       if (aRules) {
         var aColSet=aRules['area']['colSet'];
         var aRowSet=aRules['area']['rowSet'];
         var aCol = aColSet[aColKey];
         var aCell;
         if (aCol) {
           for(var r in aRowSet) {
             if (aRowSet.hasOwnProperty(r)) {
               aCell=y$(r+'_'+aCol.name);
               if (aCell)
                 dynSetCellValue(aCell.id,'');
             }
           }
         }
       }
     }
     
     
     function calcGridGetRules(aCalcGrid)
     {
       var aRules = _cg_rules[aCalcGrid.id];
       if (aRules)
         return aRules['rules'];
       else
         return {};
     }
     
     function calcGridGetAssociatedRule(aCalcGrid, aFieldName)
     {
       var ret=null;
       var aRules = calcGridGetRules(aCalcGrid);
       for(var f in aRules) {
         if (ret==null) {
           if (aRules.hasOwnProperty(f)) {
             if (aFieldName==f)
               ret=aRules[f];
           }
         }
       }
       return ret;
     }
     
     function calcGridGetRuleTitle(aCalcGrid, aFieldName)
     {
       var ret=null;
       var theRule=calcGridGetAssociatedRule(aCalcGrid, aFieldName);
       if (theRule!=null)
         ret=theRule['title'];
       return ret;
     }
     
     function calcGridGetNextFieldName(aCalcGrid, aFieldName)
     {
       var ret=null;
       var aRules = calcGridGetRules(aCalcGrid);
       var nextIsField=false;
       for(var f in aRules) {
         if (ret==null) {
           if (aRules.hasOwnProperty(f)) {
             if (nextIsField)
               ret=f;
             nextIsField=(f==aFieldName);
           }
         }
       }
       return ret;
     }
     
     function calcGridRecalc(aCalcGrid, aCellName)
     {
       var aRules=_cg_rules[aCalcGrid.id];
       if (aRules) {
         aRules = aRules['rules'];
         for(var r in aRules)
           if (aRules.hasOwnProperty(r)) {
             var rule=aRules[r];
             if (rule['fieldList']) {
               if (rule['fieldList'].indexOf(aCellName) >= 0 ) {
                 var aFieldList=rule['fieldList'].split(',');
                 var aStack=new Array();
                 for(var f in aFieldList)
                   if (aFieldList.hasOwnProperty(f)) {
                     aStack[aFieldList[f]]=y$(aFieldList[f]).innerHTML;
                   }
     
                 var aResultCellName;
                 if (rule['resultCellPrefix']>'')
                   aResultCellName=rule['resultCellPrefix']+'_'+r;
                 else if (rule['resultCellPostfix']>'')
                   aResultCellName=r+'_'+rule['resultCellPostfix'];
                 else
                   aResultCellName=r;
                 var aResult = Parser.evaluate(rule['calcExpr'], aStack);
                 var aZero=0.00;
                 aZero = aZero.toFixed(rule['decimalPlaces']);
                 aResult=aResult.toFixed(rule['decimalPlaces']);
                 aResult =  (isNaN(aResult)) ? aZero : (isInfinity(aResult) ? aZero : aResult+rule['units']);
                 y$(aResultCellName).innerHTML=aResult;
                 y$(aResultCellName).style.border='solid 1px #96CBFF';
               }
             }
           }
       }
     }
     
     function dynTableGetCellParentGrid(aCellId)
     {
       var aCell=y$(aCellId);
       var aParent=aCell.parentNode;
       while (aParent.tagName!='TABLE')
         aParent=aParent.parentNode;
     
       if (aParent.tagName=='TABLE')
         return aParent;
       else
         return null;
     }
     
     function dynTableCreate(aTableContainer, aTableID)
     {
       if (aTableContainer) {
         var fTable = document.createElement('table');
         fTable.id=aTableID;
         fTable.name=aTableID;
         aTableContainer.appendChild(fTable);
       } else
         console.log('Error: You cannot create a dynTable without a div to contain it');
       return fTable;
     }
     
     function dynTableSetRowTitles(aTable, aFirstRow, aFieldList, aGraphFunctionName)
     {
       for(var k in aFieldList)
         if ((aFieldList.hasOwnProperty(k)) && (k!='_context_')) {
           while (aTable.rows.length<aFirstRow) {
             var aRow=aTable.insertRow(aTable.rows.length);
             var aCell=aRow.insertCell(0);
           }
           var aRow = aTable.insertRow(aTable.rows.length);
           var aCell = aRow.insertCell(0);
           aCell.id=k;
           if (aFieldList[k].parent>'') {
             aCell.style.paddingLeft='18px';
             aCell.style.fontSize='80%';
           }
           var aTitle=aFieldList[k].title;
           if (undefined != aGraphFunctionName)
             if (aFieldList[k].graph)
               aTitle='<a href="javascript:'+aGraphFunctionName+'(\''+aTable.id+'\',\''+k+'\')">'+aTitle+'</a>';
           aCell.innerHTML=aTitle;
         }
     }
     
     function dynTableSetColTitles(aTable, aFirstCol, aSequence)
     {
       var aRow, aCell;
     
       var i=aFirstCol;
       for(var k in aSequence) {
         if (aSequence.hasOwnProperty(k)) {
           for(var r=0; r<aTable.rows.length; r++) {
             aRow=aTable.rows[r];
             while (aRow.cells.length<aFirstCol)
               aCell=aRow.insertCell(aRow.cells.length);
             aCell=aRow.insertCell(i);
             if (r==0)
               aCell.innerHTML=aSequence[k].title;
             aCell.id=aTable.rows[r].cells[0].id+'_'+aSequence[k].name;
             aCell.style.textAlign='center';
           }
           i++;
         }
       }
     }
     
     function dynTableSetColWidth(aTable, aWidth,aStart,aFinish)
     {
       if (aStart==undefined)
         var aStart=0;
       if (aFinish==undefined)
         var aFinish=aTable.rows[aTable.rows.length-1].cells.length;
       for(var c=aStart; c<=aFinish; c++)
         for (var r=0; r<aTable.rows.length; r++)
           if (aTable.rows[r].cells[c] != undefined)
             aTable.rows[r].cells[c].style.minWidth=aWidth+'px';
     }
     
     function dynTableSetRowHeight(aTable, aHeight, aStart, aFinish)
     {
       if (aStart==undefined)
         var aStart=0;
       if (aFinish==undefined)
         var aFinish=aTable.rows.length-1;
     
       for (var r=aStart; r<=aFinish; r++)
         aTable.rows[r].style.height=aHeight+'px';
     }
     
     function dynSetCellValue(aCellName, aCellValue)
     {
       if (y$(aCellName)) {
     
         var aCoordinates=aCellName.split('_');
         var aCalcGrid=dynTableGetCellParentGrid(aCellName);
         var aRule = calcGridGetAssociatedRule(aCalcGrid, aCoordinates[0]);
         if (aStack==undefined) {
           var aFieldList = calcGridGetRules(aCalcGrid);
           var aStack=new Array();
           for (var f in aFieldList)
             if ((f!=undefined) && (f!='_context_'))
               if (aFieldList.hasOwnProperty(f)) {
                 aStack[f]=y$(f+'_'+aCoordinates[1]).innerHTML;
               }
         }
     
         var canChangeCellValue=true;
     
         if (y$(aCellName).innerHTML!='') {
           if ((aRule.minVal!=undefined) && (aRule.minVal>'')) {
             var checkMinVal = str2int(aCellValue.value)+' >= '+aRule.minVal;
             var canChangeCellValue=Parser.evaluate(checkMinVal, aStack);
             console.log(checkMinVal+' = '+canChangeCellValue);
           }
           if (canChangeCellValue) {
             if ((aRule.maxVal!=undefined) && (aRule.maxVal!='')) {
               var checkMaxVal = aRule.maxVal+' >= '+str2int(aCellValue.value);
               var canChangeCellValue=Parser.evaluate(checkMaxVal, aStack);
               console.log(checkMaxVal+' = '+canChangeCellValue);
             }
           }
         }
     
         if (canChangeCellValue) {
           var aTotal=aCoordinates[0]+'_total';
           var priorValue=str2int(y$(aCellName).innerHTML);
           y$(aCellName).innerHTML=aCellValue.value;
     
           if (y$(aTotal)) {
             var vTotal=str2int(y$(aTotal).innerHTML);
             vTotal=vTotal-priorValue+str2int(aCellValue.value);
             y$(aTotal).innerHTML=vTotal;
             var calcGridList = aCellValue.calcGridSet.split(',');
             for (var aCG in calcGridList)
               if (calcGridList.hasOwnProperty(aCG)) {
                 if (calcGridList[aCG]>'') {
                   var auxCalcGrid=y$(calcGridList[aCG]);
                   calcGridRecalc(auxCalcGrid, aTotal);
                 }
               }
           }
           // table -> tr -> td
           //var aCalcGrid=y$(aCellName).parentNode.parentNode.parentNode;
           if (aRule!=undefined) {
             var notificationFormId=aRule.notificationFormId;
             if (notificationFormId>'') {
               var fl='fl_'+notificationFormId;
     
               var aFields = this[fl];
               if (aFields) {
                 aFields['_position']=new Array();
                 aFields['_position']['name']='_position';
                 aFields['_position']['type']='hidden';
                 aFields['_position']['value']={ 'x': 100, 'y': 0};
     
                 askValue('/','javascript:dynSaveForm()',aFields);
               }
             }
           }
     
           if (aCellValue.openNextField) {
             var nextField=calcGridGetNextFieldName(aCalcGrid, aCoordinates[0]);
             var nextCellName=nextField+'_'+aCoordinates[1];
             if (y$(nextCellName))
               y$(nextCellName).click();
           }
         } else {
           console.log("O Valor nÃ£o pode ser lanÃ§ado por nÃ£o cumprir condiÃ§Ãµes de existÃªncia");
           window.alert("O valor nÃ£o Ã© consistente.\nRevise valores do campo pai e o prÃ³prio valor lanÃ§ado\nTente novamente");
           y$(aCellName).click();
         }
       }
     }
     
     function showCellInfo(aElement, show, aEditableFlag)
     {
       var aMessage = '';
     
       if (aElement)
         aMessage = aElement.parentElement.firstChild.innerHTML+'<br><small>Dia: '+aElement.cellIndex+'</small>';
     
       if (!aEditableFlag)
         var aStyle='color:#aaa';
       else
         var aStyle='font-weight: 800; color:black';
     
       aMessage="<div style='"+aStyle+"'>"+aMessage+"</div>";
     
       var tip=document.getElementById('tipDiv');
       if (tip) {
         if (show) {
           tip.style.display='block';
           var aTargetX = getX(aElement) + aElement.offsetWidth;
           var aTargetY = getY(aElement) + aElement.offsetHeight;
           new Effect.Move('tipDiv', { x: aTargetX, y: aTargetY, mode: 'absolute', duration: .3});
         }
         tip.innerHTML = aMessage;
       }
     }
     
     function dynSetEditableCell(aTable, aColName, aRowName, aCalcGridSet, aEditable, aOnChangesFuncName)
     {
       var aCellName=aRowName+'_'+aColName;
       var aCell=y$(aCellName);
       if (aCell) {
         // aCell.contentEditable=aEditableFlag;
         aCell.onmouseover=function() {
            //alert(this.parentElement.firstChild.innerHTML + this.cellIndex);
            showCellInfo(this, true, aEditable);
         }
         aCell.onmouseout=function() {
            //alert(this.parentElement.firstChild.innerHTML + this.cellIndex);
            showCellInfo(this, false, aEditable);
         }
         if (aEditable) {
           aCell.onclick=function() {
             // var aRowName=this.id.split('_')[0];
             var aFieldTitle = calcGridGetRuleTitle(this.parentNode.parentNode.parentNode, aRowName);
             if (aFieldTitle==null)
               aFieldTitle='Valor';
     
             var aFields=new Array();
             aFields['valor']=new Array();
             aFields['valor']['title']=aFieldTitle+' / '+aColName;
             aFields['valor']['name']='value';
             aFields['valor']['type']='integer';
             aFields['valor']['width']='4';
             aFields['valor']['value']=aCell.innerHTML;
     
             aFields['calcGridSet']=new Array();
             aFields['calcGridSet']['name']='calcGridSet';
             aFields['calcGridSet']['type']='hidden';
             aFields['calcGridSet']['value']=aCalcGridSet;
     
             aFields['openNextField']=new Array();
             aFields['openNextField']['name']='openNextField';
             aFields['openNextField']['type']='hidden';
             aFields['openNextField']['value']=1;
     
             var x=getX(aCell) + parseInt(aCell.offsetWidth);
             var y=getY(aCell) + parseInt(aCell.offsetHeight);
     
             aFields['_position']=new Array();
             aFields['_position']['name']='_position';
             aFields['_position']['type']='hidden';
             aFields['_position']['value']={ 'x': x, 'y': y};
             aFields['_position']['onChangesFuncName']=aOnChangesFuncName;
     
             askValue('/','javascript:dynSetCellValue("'+aRowName+'_'+aColName+'")',aFields); ;
           };
         } else
           aCell.onclick=null;
       }
     }
     
     function dynSetEditableCells(aTable, aRowFields, aColFields, aOnChangesFuncName)
     {
       for(var r in aRowFields)
         if (aRowFields.hasOwnProperty(r))
           for(var c in aColFields) {
             if (aColFields.hasOwnProperty(c)) {
               dynSetEditableCell(aTable,
                                  aColFields[c].name, aRowFields[r].name,
                                  [aColFields[c]['calcGridAssoc'], aRowFields[r]['calcGridAssoc']],
                                  (aColFields[c].editable) && (aRowFields[r].editable),
                                  aOnChangesFuncName);
             }
         }
     }
     
     function dynSetClickableHeaders(aTable, aRowFields, aColFields, aOnClick)
     {
       for(var c in aColFields)
         if (aColFields.hasOwnProperty(c)) {
           var aCell=y$('_'+aColFields[c].name);
           if (aCell) {
             aCell.onclick=aOnClick;
           }
         }
     }
     
     function dynSetClickableRowHeaders(aTable, aRowFields, aColFields, aOnClick)
     {
       for(var r in aRowFields)
         if (aRowFields.hasOwnProperty(r)) {
           var aCell=y$(aRowFields[r].name);
           if (aCell) {
             aCell.onclick=aOnClick;
           }
         }
     }
     
     function sequenceSetValue(aSequence, aKey, aValue)
     {
       for (var n in aSequence)
         if (aSequence.hasOwnProperty(n)) {
           aSequence[n][aKey]=aValue;
         }
     }
     
     function sequenceAdd(aSequence, aValue)
     {
       /*
       var n=aSequence.length;
       aSequence[n]=new Array();
       aSequence[n]=aValue;
       */
       aSequence[aValue.name]=aValue;
     }
     
     function sequenceProducer(aFirstValue, aLastValue, aInc)
     {
       var ret=new Array();
       if (aInc>0) {
         for(var n=aFirstValue; n<=aLastValue; n+=aInc) {
           var aValue=new Array();
           aValue.title=n;
           aValue.name=n;
           sequenceAdd(ret, aValue);
         }
       } else if (aInc<0) {
     
       } else
         console.log("You cannot create a non increment sequence");
       return ret;
     }
     
     function fillTable(aTableId, aSQL, aFieldOrder, aLink, aFieldIdName, aOnData, aOnReady, aOnSelect, aFlags)
     {
       ycomm.invoke('yeapfDB',
                    'doSQL',
                    { 'sql': '"'+aSQL+'"' },
                  function(status, xError, xData) {
                     /*@20150330 hideWaitIcon();*/
                     console.log(status, aTableId);
     
                     var aFieldList = aFieldOrder.split(',');
                     var aRows   = '';
                     for(var i=0; i<aFieldList.length; i++)
                       aRows=aRows+'<td><a href="{1}">%({0})</a></td>'.format(aFieldList[i], aLink);
     
                     ycomm.dom.fillElement(
                       aTableId,
                       xData,
                       { 'onNewItem':   aOnData,
                         'rows':        [aRows],
                         'inplaceData': [aFieldIdName],
                         'onNewItem':   aOnData,
                         'onReady':     aOnReady }
                     );
                     /*
                     var oTable = y$(aTableId);
                     if (oTable) {
                       var aContainer=oTable.parentNode;
                       if (aContainer)
                         aContainer.style.display='block';
     
                       var aHeaderLines=1;
                       var aBtnFlags=0;
                       if (aFlags) {
                         if (aFlags.headerLines!=undefined)
                           aHeaderLines = parseInt(aFlags.headerLines);
                         if (aFlags.btnFlags!=undefined)
                           aBtnFlags=parseInt(aFlags.btnFlags);
                       }
     
                       while(oTable.rows.length>aHeaderLines)
                         oTable.deleteRow(oTable.rows.length-1);
                       console.log(aFieldOrder);
                       var aFieldList=aFieldOrder.split(',');
     
                       for (var j in xData) {
                         if (xData.hasOwnProperty(j)) {
                           var aux=new Array();
                           var ndx=0;
                           var rowId='';
                           for (var col in aFieldList) {
                             if (aFieldList.hasOwnProperty(col)) {
                               var aColData=xData[j][trim(aFieldList[col])] || '';
                               if (aOnData!=undefined)
                                 aColData=aOnData(aColData, trim(aFieldList[col]), aTableId, j);
                               aux[ndx++]=aColData;
                               if (trim(aFieldList[col])==aFieldIdName)
                                 rowId=aColData;
                             }
                           }
     
                           if (rowId=='') {
                             for(var col in xData[j])
                               if (xData[j].hasOwnProperty(col)) {
                                 if ((col==aFieldIdName) && (col>'')) {
                                   aColData=xData[j][col];
                                   if (aOnData!=undefined)
                                     aColData=aOnData(aColData, trim(col), aTableId, j);
                                   rowId=aColData;
                                 }
                               }
                           }
     
                           if (rowId=='')
                             rowId=aTableId+'_'+j;
     
                           addRow(aTableId, aux, aLink, aBtnFlags, 0, -1, aOnSelect).id=rowId;
                         }
                       }
                     }
                     if (aOnReady!=undefined)
                       aOnReady(aTableId);
                       */
                  });
     }
     
     function getCheckboxTable(aCheckboxID)
     {
       if (y$(aCheckboxID).parentElement)
         return y$(aCheckboxID).parentElement.parentElement
       else
         return null;
     }
     
     function getAllCheckboxInTable(aTable)
     {
       var ret={};
       var chk = aTable.getElementsByTagName('input');
       var len = chk.length;
       var n=0;
     
       for (var i = 0; i < len; i++) {
         if (chk[i].type === 'checkbox') {
             ret[n++]=chk[i];
         }
       }
       return ret;
     }
     
     function getFormSelectOptions(aArray, aFormName, aFormField, aOnReady)
     {
       aArray.length=0;
       ycomm.invoke('yeapfDB',
                  'getFormSelectOptions',
                  { 'formName': '"'+aFormName+'"',
                    'formField': '"'+aFormField+'"' },
                  function(status, xError, xData) {
                     /*@20150330 hideWaitIcon();*/
                     console.log(status, aFormName+'.'+aFormField);
                     for (var j in xData) {
                       if (xData.hasOwnProperty(j)) {
                         for(var k in xData[j])
                           if ((xData[j].hasOwnProperty(k)) && (k!='rowid'))
                             aArray[k]=xData[j][k];
                       }
                     }
                     if (aOnReady!=undefined)
                       aOnReady(aArray, aFormName, aFormField);
                  });
     }
     
     function $value(aID, aDefaultValue)
     {
       var a=y$(aID);
       if (a)
         return a.value;
       else
         return aDefaultValue;
     }
     
     function __saveFormInfo(e)
     {
       var s=$value('s','');
       var a=$value('a','');
       a = 'save'+a.ucFirst();
       var id=$value('id','');
     
       var v;
       if (e.type=='checkbox')
         v=e.checked?e.value:'';
       else if (e.type=='text')
         v=e.value;
     
       var jFieldName = e.id;
       jFieldName = jFieldName.replace('.','_');
     
       _dumpy(2,1,'u',u,'s',s,'a',a,'id',id,'eID',e.id,'v',v);
     
       _DO(s,a,'(id,'+jFieldName+')','('+id+','+v+')');
     }
     
     function __dynOnChange__(e)
     {
       if (e==undefined)
         var e = window.event || arguments.callee.caller.arguments[0];
       if (e.target)
         e = e.target;
     
       if (e.childOpenCondition)
         console.log(e.childOpenCondition);
     }
     
     function __cbOnChange__(e, saving)
     {
       // procurar elemento que chamou esta funÃ§Ã£o
       if (e==undefined)
         var e = window.event || arguments.callee.caller.arguments[0];
       if (e.target)
         e = e.target;
     
       if (saving==undefined)
         saving=e.dynSaveOnChange;
     
       _dumpy(2,1,'check ',e.id,e.checked);
     
       if (e.dynOnChange != undefined)
         e.dynOnChange();
       // salvar informaÃ§Ã£o
       if (saving)
         __saveFormInfo(e);
     /*
           for (var n=i+1; n<allElements.length; n++)
             if (allElements[n].id.substr(0,auxID.length)==auxID) {
               dynSetElementDisplay(allElements[n].id, aElementSet[i].id, aElementSet[i].value);
               // aElementSet[n].style.visibility=aElementSet[i].checked?'visible':'hidden';
               ccDependents++;
             }
      */
       // corrigir visualizaÃ§Ã£o dos elementos dependentes do clicado
       dynCleanChilds(e, document);
     
     }
     
 /* END ydyntable.js */
 _dump("ydyntable");
 /* START yinterface.js */
     /*
         skel/workbench/js/yloader.js
         YeAPF 0.8.61-153 built on 2018-11-30 07:00 (-2 DST)
         Copyright (C) 2004-2018 Esteban Daniel Dortta - dortta@yahoo.com
         2018-11-30 07:00:56 (-2 DST)
     */
     
     var yInterfaceObj = function() {
       var that = {};
     
       that.openTab = function(e) {
         var opcoes = y$('.op-menu');
         if (opcoes) {
           opcoes.forEach(function(i) {
             i.deleteClass('active');
           });
           /* pode ser um click ou um href */
           if (typeof e == "object") {
             /* se for um click, pego o destino */
             if (e.target)
               e = e.target;
             while ((e) && (e.nodeName != 'LI'))
               e = e.parentNode;
             e.addClass('active');
             var tab = e.getAttribute('data-tab');
             mTabNav.showTab(tab);
           } else if (typeof e == "string") {
             /* pode ser uma ancora */
             var i, href;
             for (i = 0; i < document.links.length; i++) {
               href = document.links[i].getAttribute('href');
               if (href == e) {
                 that.openTab(document.links[i]);
                 i = document.links.length;
               }
             }
           }
         }
       };
     
       that.getRowCount = function(subject, params) {
         var promiseReturn = new Promise(
           function(resolve, reject) {
             ycomm.invoke(
               subject,
               "getRowCount",
               params || {},
               function(s, e, d, m, c) {
                 if (200 == s) {
                   if ((d) && (d[0])) {
                     d[0].cc=d[0].cc || d[0].CC || 0;
                     resolve(d[0]);
                   } else {
                     reject("status: " + s);
                   }
                 } else {
                   reject("status: " + s);
                 }
               }
             );
           }
         );
         return promiseReturn;
       };
     
       that.mountPagesIndex = function(tableId, currentPage) {
         var table = y$(tableId),
           paginator, subject, pageCount, l, n, g, newLI, aTag, maxNumberOfBlocks, step, clientSize = getClientSize();
         if (table) {
           paginator = y$(table.getAttribute('data-paginator'));
     
           if (paginator) {
             currentPage = currentPage || table.getAttribute('data-currentPage');
             subject = table.getAttribute('data-subject');
             pageCount = str2int(table.getAttribute('data-pageCount'));
     
             currentPage = Math.min(pageCount || 1, Math.max(1, currentPage || 0));
             paginator.innerHTML = '';
     
             l = Math.max(1, currentPage - 2);
             maxNumberOfBlocks = Math.ceil(Math.max(clientSize[0], paginator.parentNode.offsetWidth) / 60); /* dois digitos 50px */
             g = Math.min(pageCount, l + 4);
             step = Math.max(1, Math.ceil(pageCount / (maxNumberOfBlocks - (g - l))));
     
             table.setAttribute('data-pageStep', step);
     
             for (n = 1; n < 1+pageCount;) {
               aTag = document.createElement('A');
               aTag.setAttribute('href', '#' + subject);
               aTag.setAttribute('class', 'page-' + subject);
               aTag.setAttribute('data-subject', subject);
               aTag.setAttribute('data-table', tableId);
               aTag.setAttribute('data-pageNo', n);
               aTag.innerHTML = n;
               newLI = document.createElement('LI');
               newLI.appendChild(aTag);
               if (n == currentPage)
                 newLI.addClass('active');
               paginator.appendChild(newLI);
               if (n < l) {
                 n = n + step;
                 if (n > l)
                   n = l;
               } else if (n > g) {
                 n = n + step;
               } else {
                 n++;
               }
             }
             addEvent(".page-" + subject, "click", that.gotoPage);
           }
         }
     
       };
     
       that.initializePagination = function(subject, tableId, paginatorId, pageLen, params) {
         tableId = tableId || "tbl-" + subject;
         paginatorId = paginatorId || 'pagination-' + subject;
         pageLen = Math.max(1, pageLen || 20);
     
         var paginator = y$(paginatorId),
           table = y$(tableId),
           nav = paginator.parentNode;
     
         if (table) {
           params = params || {};
           var tableParams = JSON.parse(table.getAttribute('data-params') || '{}');
           mergeObject(params, tableParams, true);
           tableParams.pageLen = pageLen;
           table.setAttribute('data-params', JSON.stringify(tableParams));
           table.setAttribute('data-subject', subject);
           table.setAttribute('data-paginator', paginatorId);
         }
     
         var promiseReturn = new Promise(
           function(resolve, reject) {
             if (paginator) {
               paginator.innerHTML = '';
               that.getRowCount(subject, tableParams).
               then(
                 function(d) {
                   var pageCount = Math.ceil(d.cc / pageLen);
                   table.setAttribute('data-pageCount', pageCount);
                   that.mountPagesIndex(tableId);
                   resolve();
                 }
               ).
               catch(
                 function(error) {
                   console.error("Error doing {0}.getRowCount".format(subject));
                   reject(error);
                 }
               );
             } else {
               reject("error: paginator not found");
             }
           }
         );
     
         return promiseReturn;
     
       };
     
       that.gotoPage = function(e) {
         e = e.target;
         var pageNo = e.getAttribute('data-pageNo'),
             tableId = e.getAttribute('data-table') ;
     
     
     
     
     
     
     
     
     
     
     
     
     
     
         that.loadTablePage(e.getAttribute('data-subject'), pageNo, tableId);
       };
     
       that.loadTablePage = function(subject, pageNo, tableId, params) {
         tableId = tableId || "tbl-" + subject;
         pageNo = (pageNo===-1)?pageNo:Math.max(1, pageNo || 0);
     
         var table = y$(tableId),
           tableParams = {},
           pageLen, pageCount, pageStep, 
           dataPaginator,n,
           promiseReturn;
     
         if (table) {
           pageStep      = table.getAttribute("data-pageStep") || 0;
           pageCount     = table.getAttribute("data-pageCount") || 0;
           dataPaginator = (y$(table.getAttribute("data-paginator")) || {children:[]}).children;
     
           ycomm.dom.fillElement(tableId,{});
     
           if (pageNo===-1)
             pageNo = pageCount;
     
           if (pageStep>1) {
             that.mountPagesIndex(tableId, pageNo);
           } else {
             for (n=0; n<dataPaginator.length; n++) {
               dataPaginator[n].deleteClass('active');
             }
             if (dataPaginator[pageNo-1])
               dataPaginator[pageNo-1].addClass('active');
           }
     
           tableParams = JSON.parse(table.getAttribute('data-params') || '{}');
           pageLen = tableParams.pageLen;
           if (!pageLen) {
             console.warn("Call initializePagination() first");
             that.initializePagination(subject, tableId);
           }
     
           // tableParams.currentPage=pageNo;
     
           pageLen = Math.max(1, pageLen || 20);
           /* curo os parÃ¡metros e misturo com os params que tenho */
           mergeObject(params || {}, tableParams, true);
     
           table.setAttribute('data-params', JSON.stringify(tableParams));
           table.setAttribute('data-subject', subject);
           table.setAttribute('data-currentPage', pageNo);
     
           promiseReturn = new Promise(
             function(resolve, reject) {
               /* esta funÃ§Ã£o serve para puxar apenas uma pÃ¡gina dos dados do banco para dentro de uma tabela
                  recomendamos seu uso para tabelas com 200 registros ou mais */
               var blockStart = 0,
                 blockSize = pageNo * pageLen,
                 blockReady = false;
     
               var __loadTable = function() {
                 mergeObject({
                     xq_start: (pageNo - 1) * pageLen + blockStart,
                     xq_requestedRows: pageLen
                   },
                   tableParams, true);
                 ycomm.invoke(
                   subject,
                   "loadTable",
                   tableParams,
                   function(status, error, data, userMsg, dataContext) {
                     if (status == 200) {
                       ycomm.dom.fillElement(tableId, data, {}, {
                         deleteRows: blockStart == 0
                       });
                       if ((dataContext) && ((dataContext.rowCount >= dataContext.requestedRows))) {
                         blockStart += dataContext.rowCount;
                         if ((dataContext.rowCount>0) && ((pageNo - 1) * pageLen + blockStart < blockSize)) {
                           setTimeout(__loadTable, 125);
                         } else
                           blockReady = true;
                       } else {
                         blockReady = true;
                       }
     
                       if (blockReady) {
                         var event = new Event('ready');
                         y$(tableId).dispatchEvent(event);
                         resolve(data);
                       }
                     } else {
                       reject("status: " + status);
                     }
                   }
                 );
               };
     
               if (table)
                 setTimeout(__loadTable, 125);
               else
                 reject("error: table cannot be find");
             }
           );
         } else {
           promiseReturn = new Promise(
             function(resolve, reject) {
               setTimeout(function() {
                 reject("Erro: 500");
               }, 125);
             }
           );
         }
     
         return promiseReturn;
       };
     
       that.loadTable = function(subject, tableId, params) {
         /* curo o nome da tabela */
         tableId = tableId || "tbl-" + subject;
     
         var table = y$(tableId),
           tableParams = {};
     
         if (table)
           tableParams = JSON.parse(table.getAttribute('data-params') || '{}');
     
         /* curo os parÃ¡metros e misturo com os params que tenho */
         mergeObject(params || {}, tableParams, true);
     
         table.setAttribute('data-params', JSON.stringify(tableParams));
         table.setAttribute('data-subject', subject);
     
         var promiseReturn = new Promise(
           function(resolve, reject) {
             /* esta funÃ§Ã£o serve para puxar TODOS os dados do banco para dentro de uma tabela
                entÃ£o use apenas com aquelas tabelas relativamente pequenas */
             var blockStart = 0;
     
             var __loadTable = function() {
               mergeObject({
                 xq_start: blockStart
               }, tableParams, true);
               ycomm.invoke(
                 subject,
                 "loadTable",
                 tableParams,
                 function(status, error, data, userMsg, dataContext) {
                   if (status == 200) {
                     ycomm.dom.fillElement(tableId, data, {}, {
                       deleteRows: blockStart == 0
                     });
                     if (dataContext.rowCount >= dataContext.requestedRows) {
                       blockStart += dataContext.rowCount;
                       setTimeout(__loadTable, 125);
                     } else {
                       var event = new Event('ready');
                       y$(tableId).dispatchEvent(event);
                       resolve(data);
                     }
                   } else {
                     reject("status: " + status);
                   }
                 }
               );
             };
     
             if (table)
               setTimeout(__loadTable, 125);
             else
               reject("error: table cannot be find");
           }
         );
         return promiseReturn;
       };
     
       that.pullAllData = function(subject, params) {
         params = params || {};
         var retPromise = new Promise(
           function(resolve, reject) {
             var blockStart = 0,
               allData = [];
             var __loadTable = function() {
               mergeObject({
                 xq_start: blockStart
               }, params, true);
               ycomm.invoke(
                 subject,
                 "loadTable",
                 params,
                 function(status, error, data, userMsg, dataContext) {
                   if (200 == status) {
                     allData = allData.concat(data);
     
                     if (dataContext.rowCount >= dataContext.requestedRows) {
                       blockStart += dataContext.rowCount;
                       setTimeout(__loadTable, 125);
                     } else {
                       resolve(allData);
                     }
     
                   } else {
                     reject("status: " + status);
                   }
                 }
               );
             };
             setTimeout(__loadTable, 125);
           }
         );
     
         return retPromise;
       };
     
       that.getButtonContext = function(e) {
         e = e.target;
         if (e.nodeName != 'BUTTON')
           e = e.parentNode;
         return y$(e.getAttribute('data-table'));
       };
     
       that.refreshTable = function(e) {
         var table = that.getButtonContext(e),
           table_id, table_params, table_subject, table_currentPage;
     
         if (table) {
           table_id = table.getAttribute('id');
           table_params = JSON.parse(table.getAttribute('data-params')) || {};
           table_subject = table.getAttribute('data-subject');
           table_currentPage = table.getAttribute('data-currentPage');
     
           var pageLen = table_params.pageLen;
           if (!pageLen) {
             that.loadTable(table_subject, table_id, table_params);
           } else {
             that.loadTablePage(table_subject, table_currentPage, table_id, table_params);
           }
         }
     
       };
     
       that.insertData = function(e) {
         var table = that.getButtonContext(e),
           table_subject, table_form, table_tab, table_params, i;
     
         if (table) {
           table_subject = table.getAttribute('data-subject');
           table_form = table.getAttribute('data-form');
           table_tab = table.getAttribute('data-tab');
           table_modal = table.getAttribute('data-modal');
     
           table_params = table.getAttribute('data-params');
     
           if (table_modal)
             $(table_modal).modal('show');
           if (table_tab)
             mTabNav.showTab(table_tab);
           if (table_form) {
             ycomm.dom.cleanForm(table_form);
             /* coloco os valores padrÃ£o que vem do params */
             ycomm.dom.fillElement(table_form, [JSON.parse(table_params)]);
     
             /* enumero os elementos para poder limpar sua condiÃ§Ã£o de erro */
             var form = ycomm.dom.getFormElements(table_form),
               cd;
             for (i in form) {
               if (form.hasOwnProperty(i)) {
                 cd = y$(i);
                 if (cd) {
                   cd = cd.closest('div');
                   cd.deleteClass('has-error');
                 }
               }
             }
           }
         }
       };
     
       that.getForm = function(manager, subject, formId, fieldPrefix, fieldPostfix, callback) {
         var requestNeeded = true;
     
         if (that.allowCacheForm) {
           if ("undefined" != typeof that._forms_cache) {
             if ("undefined" != typeof that._forms_cache[subject + '.' + formId]) {
               requestNeeded = false;
               that.buildFormFromJSON(formId, that._forms_cache[subject + '.' + formId], fieldPrefix, fieldPostfix).then(callback);
             }
           }
         }
     
         if (requestNeeded) {
           console.log("Requesting form '{0}'".format(formId));
           ycomm.invoke(
             subject,
             'getForm', {},
             function(s, e, d) {
               if (200 == s) {
                 if ((d) && (d[0])) {
                   console.log(formId+" arrivou ");
                   if ("undefined" == typeof that._forms_cache)
                     that._forms_cache = [];
                   that._forms_cache[subject + '.' + formId] = d[0];
                   that.buildFormFromJSON(manager, formId, d[0], fieldPrefix, fieldPostfix).then(callback);
                 } else
                   console.error("'{0}' nÃ£o estÃ¡ devolve resultado ao fazer 'getForm'".format(subject));
               }
             }
           );
         }
       };
     
       that.input_clean_associated_field = function(elem) {
         var form = elem.closest('FORM'),
           fieldPrefix = (form.getAttribute('data-prefix')) || '',
           fieldPostfix = (form.getAttribute('data-postfix')) || '',
           resultSpec = JSON.parse(elem.getAttribute('data-result-spec') || '[]'),
           auxField = null,
           auxFieldName = null;
     
         for (var n = 0; n < resultSpec.length; n++) {
           auxFieldName = fieldPrefix + resultSpec[n].to + fieldPostfix;
           if (auxFieldName != elem.id) {
             auxField = y$(auxFieldName);
             if (auxField) {
               auxField.value = '';
             }
           }
         }
     
       };
     
       that.input_input = function(e) {
         if (e.target)
           e = e.target;
     
         if (e) {
           var dataListReady = e.getAttribute('data-list-ready');
           if (dataListReady == 'true') {
             var value = trim(e.value).toUpperCase(),
               opts = e.list.childNodes,
               form = e.closest('FORM'),
               i, n, located = false,
               targetField, sourceField, sourceFieldValue,
               fieldPrefix = (form.getAttribute('data-prefix')) || '',
               fieldPostfix = (form.getAttribute('data-postfix')) || '',
               resultSpec = JSON.parse(e.getAttribute('data-result-spec') || '[]');
     
             var _fillResultData = function(opt) {
               located = true;
               for (n = 0; n < resultSpec.length; n++) {
                 targetField = y$(fieldPrefix + resultSpec[n].to + fieldPostfix);
                 if (targetField) {
                   sourceField = 'data-' + resultSpec[n].from;
                   sourceFieldValue = opt.getAttribute(sourceField);
                   targetField.value = sourceFieldValue;
                 }
               }
             };
     
             that.input_clean_associated_field(e);
     
             for (i = 0; i < opts.length; i++) {
               if (opts[i].value.toUpperCase() === value) {
                 _fillResultData(opts[i]);
               }
             }
     
             if (!located) {
               var lineSpec = JSON.parse(e.getAttribute('data-line-spec') || '{}');
               if (lineSpec.columns) {
                 var firstColumn = lineSpec.columns[0];
                 for (i = 0; i < opts.length; i++) {
                   if ((opts[i].getAttribute('data-' + firstColumn) || '').toUpperCase() === value) {
                     _fillResultData(opts[i]);
                   }
                 }
               }
             }
           }
         }
       };
     
       that.input_keyup = function(e) {
         var isBackspace = e.key == 'Backspace';
         e = e.target;
         if (e) {
           var subject = e.getAttribute("data-subject"),
             action = e.getAttribute("data-action"),
             dataList = e.getAttribute("list"),
             lineSpec = e.getAttribute("data-line-spec") || '{}',
             search = trim(e.value);
     
           if (lineSpec == '')
             lineSpec = '{}';
           lineSpec = JSON.parse(lineSpec);
     
           if ((!isBackspace) && (search.length == 3)) {
             e.setAttribute('data-list-ready', 'false');
     
             that.log("Calling {0}.{1} with '{2}'".format(subject, action, search));
             ycomm.invoke(
               subject,
               action, {
                 "search": search
               },
               function(s, err, d) {
                 if (200 == s) {
                   if (d && d[0]) {
                     ycomm.dom.fillElement(dataList, d, lineSpec);
                     e.setAttribute('data-list-ready', 'true');
                     that.input_input(e);
                   }
                 }
               }
             );
           } else {
             if (search.length < 3) {
               ycomm.dom.fillElement(dataList, {});
               that.input_clean_associated_field(e);
             }
           }
         }
       };
     
       that.log = function() {
         var args = (arguments.length === 1 ? [arguments[0]] : Array.apply(null, arguments));
         var __log = that.logFunction || (false)?console.log:null;
         if (__log) {
           var _logLine = "";
           for (var i = 0; i < args.length; i++)
             _logLine += args[i] + ' ';
           __log(_logLine);
         }
       };
     
       window.DOM_dispatchEvent = function(eventName, targetId) {
         var event = new Event(eventName);
         var target = y$(targetId);
         if (target) {
           if (isArray(target)) {
             for (var i = 0; i < target.length; i++) {
               target[i].dispatchEvent(event);
             }
           } else {
             target.dispatchEvent(event);
           }
         }
       };
     
       window.DOM_show = function(elementOrClass) {
         elementOrClass = (elementOrClass || '');
         if ("string" == typeof elementOrClass) {
           elementOrClass = elementOrClass.unquote();
           if (elementOrClass.indexOf(' ') >= 0) {
             elementOrClass = elementOrClass.split(' ');
             for (var el = 0; el < elementOrClass.length; el++) {
               DOM_show(elementOrClass(el));
             }
             elementOrClass = null;
           } else {
             elementOrClass = y$(elementOrClass);
           }
         }
     
         if (elementOrClass) {
           if ("number" == typeof elementOrClass.length) {
             for (var e = 0; e < elementOrClass.length; e++) {
               DOM_show(elementOrClass[e]);
             }
           } else {
             elementOrClass = elementOrClass.closest('div.form-group');
             if (elementOrClass) {
               elementOrClass.style.display = "";
             }
           }
         }
       };
     
       window.DOM_hide = function(elementOrClass) {
         elementOrClass = (elementOrClass || '');
         if ("string" == typeof elementOrClass) {
           elementOrClass = elementOrClass.unquote();
           if (elementOrClass.indexOf(' ') >= 0) {
             elementOrClass = elementOrClass.split(' ');
             for (var el = 0; el < elementOrClass.length; el++) {
               DOM_hide(elementOrClass(el));
             }
             elementOrClass = null;
           } else {
             elementOrClass = y$(elementOrClass);
           }
         }
     
         if (elementOrClass) {
           if ("number" == typeof elementOrClass.length) {
             for (var e = 0; e < elementOrClass.length; e++) {
               DOM_hide(elementOrClass[e]);
             }
           } else {
             elementOrClass = elementOrClass.closest('div.form-group');
             if (elementOrClass) {
               elementOrClass.style.display = "none";
             }
           }
         }
       };
     
       window.DOM_enable = function(elementOrClass) {
         elementOrClass = (elementOrClass || '');
         if ("string" == typeof elementOrClass) {
           elementOrClass = elementOrClass.unquote();
           if (elementOrClass.indexOf(' ') >= 0) {
             elementOrClass = elementOrClass.split(' ');
             for (var el = 0; el < elementOrClass.length; el++) {
               DOM_enable(elementOrClass(el));
             }
             elementOrClass = null;
           } else {
             elementOrClass = y$(elementOrClass);
           }
         }
     
         if (elementOrClass) {
           if ("number" == typeof elementOrClass.length) {
             for (var e = 0; e < elementOrClass.length; e++) {
               DOM_enable(elementOrClass[e]);
             }
           } else {
             elementOrClass.disabled = false;
           }
         }
       };
     
       window.DOM_disable = function(elementOrClass) {
         elementOrClass = (elementOrClass || '');
         if ("string" == typeof elementOrClass) {
           elementOrClass = elementOrClass.unquote();
           if (elementOrClass.indexOf(' ') >= 0) {
             elementOrClass = elementOrClass.split(' ');
             for (var el = 0; el < elementOrClass.length; el++) {
               DOM_disable(elementOrClass(el));
             }
             elementOrClass = null;
           } else {
             elementOrClass = y$(elementOrClass);
           }
         }
     
         if (elementOrClass) {
           if ("number" == typeof elementOrClass.length) {
             for (var e = 0; e < elementOrClass.length; e++) {
               DOM_disable(elementOrClass[e]);
             }
           } else {
             elementOrClass.disabled = true;
           }
         }
       };
     
       window.DOM_setValue = function(elementOrClass, value) {
         elementOrClass = (elementOrClass || '').unquote();
         value = trim(value).unquote();
     
         var _setValue = function(elem) {
           if (elem.nodeName == 'INPUT')
             if ((elem.type == "checkbox") || (elem.type == "radio")) {
               elem.checked = ((value || '').toUpperCase() == 'TRUE') || ((value || '').toUpperCase() == elem.value);
             } else {
               elem.value = value;
             }
         };
     
         var elem = y$(elementOrClass);
         if (elem) {
           if ("number" == typeof elem.length) {
             for (var i = 0; i < elem.length; i++) {
               _setValue(elem[i]);
             }
           } else {
             _setValue(elem);
           }
         }
       };
     
       window.DOM_addClass = function(elementOrClass, className) {
         var e = y$(elementOrClass);
         if (e) {
           className = className.split(' ');
           for (var n = 0; n < className.length; n++) {
             if (e.length) {
               for (var i = 0; i < e.length; i++) {
                 e[i].addClass(className[n]);
               }
             } else {
               e.addClass(className[n]);
             }
           }
         }
       };
     
       window.DOM_removeClass = function(elementOrClass, className) {
         var e = y$(elementOrClass);
         if (e) {
           className = className.split(' ');
           for (var n = 0; n < className.length; n++) {
             if (e.length) {
               for (var i = 0; i < e.length; i++) {
                 e[i].removeClass(className[n]);
               }
             } else {
               e.removeClass(className[n]);
             }
           }
         }
       };
     
       that.evaluateField = function(e, evalStr, trueArray, falseArray) {
         /* OUT OF CONTEXT!!!!! AS it is a click, 'that' does not exists */
         if (e)
           e = e.target;
         if (e) {
           var triggerFunctions = function(funList) {
             var funDef, objName, funName, funParams;
             for (var i = 0; funList && i < funList.length; i++) {
               funDef = funList[i].split('.');
               if (funDef.length == 1) {
                 funDef[1] = funDef[0];
                 funDef[0] = "window";
               }
               objName = funDef[0];
               if ("object" == typeof window[objName]) {
                 funDef[1] = funDef[1].split('(');
                 funName = funDef[1][0];
                 if ("function" == typeof window[objName][funName]) {
                   funParams = trim(funDef[1][1]);
                   funParams = (funParams).substr(0, funParams.length - 1);
                   funParams = funParams.split(',');
                   for (var n = 0; n < funParams.length; n++) {
                     funParams[n] = trim((funParams[n] || '')).unquote();
                   }
                   window[objName][funName].apply(null, funParams);
                 } else {
                   that.log("'{1}' is not a function in '{0}'".format(objName, funName));
                 }
               } else {
                 that.log("'{0}' does not exists or is not an object".format(objName));
               }
             }
           };
     
           var classes = e.className.split(' '),
             mySensitivitySpec;
           /* acrescento o id do prÃ³prio elemento caso exista */
           if ((e.id || '') > '')
             classes[classes.length] = e.id;
     
           for (var i = 0; i < classes.length; i++) {
             mySensitivitySpec = undefined;
     
             if ("undefined" !== typeof (yInterface._sensitiveFields[classes[i]])) {
               mySensitivitySpec = yInterface._sensitiveFields[classes[i]];
             }
             if ("undefined" !== typeof (yInterface._sensitiveFields["." + classes[i]])) {
               mySensitivitySpec = yInterface._sensitiveFields["." + classes[i]];
             }
     
             if (mySensitivitySpec) {
               var ret, lexRet;
               ret = yAnalise(mySensitivitySpec.eval, undefined, formDin);
               lexRet = (yLexObj(ret).solve()) === true;
               that.log("evaluate (" + mySensitivitySpec.eval, ") returns ", ret, " = ", lexRet);
               triggerFunctions(mySensitivitySpec[lexRet]);
             }
           }
         }
       };
     
       that.formFilled = function(e) {
         console.log("FORMFILLED "+e.target.id);
       };
     
       that.emptyForm = function() {
         return {
           getAttribute: function () {return '';},
           removeEventListener: function() {}
         };
       };
     
       that.buildFormFromJSON = function(formManager, formId, jForm, fieldPrefix, fieldPostfix) {
         var form = y$(formId) || that.emptyForm(),
           formInfoJ = JSON.parse( ( jForm ||{} ).form || "{}"),
           sourceCodeViewer=y$('sourceCodeViewer');
     
         if (sourceCodeViewer) {
           /* https://stackoverflow.com/questions/4810841/how-can-i-pretty-print-json-using-javascript*/
           sourceCodeViewer.innerHTML=(function() {
             var json = JSON.stringify(jForm.form, undefined, 2);
             json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(new RegExp("\n", 'g'),"*").replace('\\"','\"');
             return '<pre style="white-space: pre-wrap; word-break: keep-all;">'+json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function (match) {
                 var cls = 'number';
                 if (/^"/.test(match)) {
                     if (/:$/.test(match)) {
                         cls = 'key';
                     } else {
                         cls = 'string';
                     }
                 } else if (/true|false/.test(match)) {
                     cls = 'boolean';
                 } else if (/null/.test(match)) {
                     cls = 'null';
                 }
                 return '<span class="' + cls + '">' + match + '</span>';
             })+'</pre>';
           })();
         }
     
         fieldPrefix = fieldPrefix || form.getAttribute('data-prefix') || '';
         fieldPostfix = fieldPostfix || form.getAttribute('data-postfix') || '';
     
         that._sensitiveFields = [];
     
         var sequence = (new Date()).getTime(),
           eventList = [],
           apiUsageList = [];
     
         function createFields(jList, masterObject) {
     
     
           var html = '',
             n, e, i,
             groupingSpecs = '',
             groupingClass = '',
             userData = '',
             aux, elementId, auxNdx,
             paragraphModel           = "<div class='form-group {1}' id='{0}'><label>{2}</label><div {4} <!--USERDATA--> ><p>{3}</p></div></div>",
             inputModel               = "<div class='form-group {1}' id='grp_{0}'><label for='{0}'>{3}</label><input type='{2}' autocomplete='off' class='form-control {5}' id='{0}' {4} {6} <!--USERDATA--> /></div>",
             inputModelWithLeftAddon  = "<div class='form-group {1}' id='grp_{0}'><label for='{0}'>{3}</label><div class='input-group'><span class='input-group-addon'>{7}</span><input type='{2}' autocomplete='off' class='form-control {5}' id='{0}' {4} {6} <!--USERDATA--> /></div></div>",
             inputModelWithRightAddon = "<div class='form-group {1}' id='grp_{0}'><label for='{0}'>{3}</label><div class='input-group'><input type='{2}' autocomplete='off' class='form-control {5}' id='{0}' {4} {6} <!--USERDATA--> /><span class='input-group-addon'>{7}</span></div></div>",
             inputWithListModel       = "<div class='form-group {1}' id='grp_{0}'><label for='{0}'>{3}</label><input type='{2}' class='form-control {5}' id='{0}' {4} {11} list='{6}' data-subject='{7}' data-action='{8}' data-line-spec='{9}' data-result-spec='{10}' <!--USERDATA--> /><datalist id='{6}'></datalist></div>",
             hiddenInputModel         = "<input type=hidden id='{0}' {1} value='{2}' <!--USERDATA--> />",
             buttonModel              = "<button type='button' class='{1}' id='{0}' {4} {5} <!--USERDATA--> ><i class='{3}'></i>{2}</button>",
             selectModelWithQuery     = "<div class='form-group {1}' id='grp_{0}'><label for={0}>{2}</label><select id='{0}' class='form-control' {3} {4} <!--USERDATA--> ></select></div>",
             selectModelWithOptions   = "<div class='form-group {1}' id='grp_{0}'><label for={0}>{2}</label><select id='{0}' class='form-control' {3} {4} <!--USERDATA--> >{5}</select></div>",
             checkboxModel            = "<div class='form-group {1}' id='grp_{0}'><label>{4}</label><div class='checkbox'><label><input type='checkbox' value='{3}' id='{0}' {5} {6} <!--USERDATA--> >{2}</label></div></div>",
             radioModel               = "<div class='{1}' id='grp_{0}'><div class='radio'><label><input type='radio' name='{3}' id='{0}' value='{4}' {5} <!--USERDATA--> >{2}</label></div></div>",
             textareaModel            = "<div class='form-group {1}' id='grp_{0}'><label for='{0}'>{2}</label><textarea class='form-control' id='{0}' cols='{3}' rows='{4}' {5} {6} <!--USERDATA--> ></textarea></div>",
             formModel                = "<form role='form' id='{0}' data-prefix='{2}' class='{1}' {3} <!--USERDATA--> >{4}</form>";
     
           masterObject = masterObject || {};
           if ("string" == typeof masterObject) {
             aux = y$(masterObject);
             masterObject = {};
             if (aux) {
               masterObject.maxCount = aux.getAttribute('data-max-count');
               masterObject.class = aux.getAttribute('data-class');
               masterObject.itemCounter = aux.getAttribute('data-item-counter');
               masterObject.id = aux.id;
             }
           }
     
           if ("undefined" == typeof masterObject.maxCount)
             masterObject.maxCount = -1;
           if ("undefined" == typeof masterObject.itemCounter)
             masterObject.itemCounter = 1;
     
           var _loadSelectOptions = function(query, elemId, disabled) {
             var inicio_bloco = 0,
               start_time = (new Date()).getTime(),
               callCount = 0;
     
             function __loadSelectoptions() {
               var params = {
                 xq_start: inicio_bloco
               };
               mergeObject(query.params, params);
               ycomm.invoke(
                 query.subject,
                 query.action,
                 params,
                 function(status, error, data, mensagem, contexto) {
                   ycomm.dom.fillElement(elemId, data, {}, {
                     deleteRows: inicio_bloco == 0
                   });
                   if (200 == status) {
                     callCount++;
                     inicio_bloco += contexto.rowCount;
                     if (contexto.requestedRows == contexto.rowCount)
                       setTimeout(__loadSelectoptions, 125);
                     else {
                       if (disabled) {
                         var ops = y$(elemId).options;
                         for (var i = 0; i < ops.length; i++)
                           ops[i].disabled = 'disabled';
                       }
                       var end_time = (new Date()).getTime();
                       var wastedTime = (end_time - start_time) / 1000;
                       that.log("API {0}.{1} took {2} seconds and {4} call(s) to fill '{3}'".format(query.subject, query.action, wastedTime, elemId, callCount));
                     }
                     var elem = y$(elemId);
                     if (elem) {
                       elem.addEventListener('refresh', function(e) {
                         _loadSelectOptions(query, elemId, disabled);
                       });
                     }
                   }
                 }
               );
             }
     
             that.log("Requesting options for '{0}' using '{1}.{2}'".format(elemId, query.subject, query.action));
     
             __loadSelectoptions();
           };
     
           var addToEventList = function(elementId, eventName, eventHandler) {
             if ("undefined" == typeof eventList[elementId])
               eventList[elementId] = [];
             if ("undefined" == typeof eventList[elementId][eventName])
               eventList[elementId][eventName] = [];
             eventList[elementId][eventName][eventList[elementId][eventName].length] = eventHandler;
           };
     
           if ((masterObject.maxCount == -1) || (masterObject.itemCounter <= masterObject.maxCount)) {
             if (masterObject.controlField)
               jList['controlField' + sequence] = masterObject.controlField;
     
             for (i in jList) {
               if (jList.hasOwnProperty(i)) {
                 sequence++;
     
                 if (masterObject.id || '' > '') {
                   aux = (masterObject.itemCounter || 1);
     
                   if (jList[i].labels) {
                     auxNdx = Math.min(aux, jList[i].labels.length) - 1;
                     jList[i].label = jList[i].labels[auxNdx];
     
                     if (jList[i].suffixes) {
                       auxNdx = Math.min(auxNdx + 1, jList[i].suffixes.length) - 1;
                       aux = jList[i].suffixes[auxNdx] + aux;
                     }
     
                   }
                   aux = "" + aux;
     
                   elementId = fieldPrefix + (jList[i].id || i) + aux + fieldPostfix;
                 } else {
                   if ((jList[i].isControlButton || '').toUpperCase() == 'YES') {
                     elementId = fieldPrefix + (jList[i].id || i) + '' + sequence + fieldPostfix;
                   } else {
                     elementId = fieldPrefix + (jList[i].id || i) + fieldPostfix;
                   }
                 }
                 if (typeof jList[i].order == 'undefined')
                   jList[i].order = sequence;
                 groupingSpecs = '';
                 groupingClass = '';
                 if (jList[i].group) {
                   groupingSpecs += "class='{0}'".format(jList[i].group);
                   groupingClass += ' ' + jList[i].group;
                 }
     
                 if (jList[i].query) {
                   var apiName = jList[i].query.subject + '.' + jList[i].query.action;
                   if ("undefined" == typeof apiUsageList[apiName])
                     apiUsageList[apiName] = [];
                   if ("undefined" == typeof apiUsageList[apiName][elementId])
                     apiUsageList[apiName][elementId] = 0;
                   apiUsageList[apiName][elementId]++;
     
                 }
     
                 if (typeof jList[i].events !== 'undefined') {
                   for (n = 0; n < jList[i].events.length; n++) {
                     for (e in jList[i].events[n]) {
                       if (jList[i].events[n].hasOwnProperty(e)) {
                         addToEventList(elementId, e, jList[i].events[n][e]);
                       }
                     }
                   }
                 }
     
                 if (typeof jList[i].conditional !== 'undefined') {
                   aux = jList[i].conditional.sensitivity.split(' ');
                   for (var a = 0; a < aux.length; a++) {
                     addToEventList(aux[a], "changeOrBlur", that.evaluateField);
                     if ("undefined" == typeof that._sensitiveFields)
                       that._sensitiveFields = [];
                     if ("undefined" == typeof that._sensitiveFields[aux[a]]) {
                       that._sensitiveFields[aux[a]] = jList[i].conditional;
                     }
                   }
                 }
     
                 userData = '';
                 for (var key in jList[i]) {
                   if (key.substr(0, 5) == "data-") {
                     userData += "{0}='{1}' ".format(key, jList[i][key]);
                   }
                 }
     
                 if ((jList[i].domType || jList[i].type || '').toUpperCase() == "ROW") {
                   html += "<div class='row' id={0}>".format(jList[i].id || 'row_' + formId + '_' + sequence);
                   html += createFields(jList[i].fields);
                   html += "</div>";
     
                 } else if ((jList[i].domType || jList[i].type || '').toUpperCase() == "ARRAY") {
                   var arrayId = (jList[i].id || elementId) + "_" + formId;
                   var controlField = {
                     "class": "col-md-2",
                     "type": "column",
                     "fields": [{
                         "type": "button",
                         "id": "array_control_btn_add_",
                         "class": "btn btn-primary btn-xs",
                         "data-master-object-id": arrayId,
                         "isControlButton": "yes",
                         "data-action": "add",
                         "iconClass": 'glyphicon glyphicon-plus-sign'
                       },
                       {
                         "type": "button",
                         "id": "array_control_btn_remove_",
                         "class": "btn btn-danger btn-xs",
                         "data-master-object-id": arrayId,
                         "isControlButton": "yes",
                         "data-action": "remove",
                         "iconClass": 'glyphicon glyphicon-remove-sign'
                       }
                     ]
                   };
                   var arrayObject = {
                     maxCount: jList[i].maxCount,
                     class: jList[i].class,
                     itemCounter: 1,
                     id: arrayId
                   };
     
                   jList[i].fields._controlField = controlField;
     
                   that._repeatableFieldInfo[arrayId] = jList[i].fields;
     
                   html += "<div id={0} data-class='{1}' data-max-count='{2}' data-item-counter='2'>".format(arrayId, jList[i].class || 'col-md-12', jList[i].maxCount);
                   html += createFields(jList[i].fields, arrayObject);
                   html += "</div>";
     
                 } else if ((jList[i].domType || jList[i].type || '').toUpperCase() == "COLUMN") {
                   html += "<div class='{0}' id='{1}'>".format(jList[i].class || '', jList[i].id || 'div_' + formId + '_' + sequence);
                   if (jList[i].label)
                     html += "<h2>{0}</h2>".format(jList[i].label);
                   html += createFields(jList[i].fields);
                   html += "</div>";
     
                 } else if ((jList[i].domType || jList[i].type || '').toUpperCase() == "P") {
                   html += paragraphModel.format(
                     elementId,
                     jList[i].class || 'col-md-12',
                     jList[i].label || '',
                     jList[i].content || '',
                     groupingSpecs,
                     (jList[i].readOnly || '').toUpperCase() == 'YES' ? 'disabled=yes' : '');
     
                 } else if ((jList[i].domType || jList[i].type || '').toUpperCase() == "BUTTON") {
                   if ((jList[i].isControlButton || '').toUpperCase() == 'YES')
                     addToEventList(elementId, "click", controlButtonAction);
                   html += buttonModel.format(
                     elementId,
                     jList[i].class || 'col-md-12',
                     jList[i].label || '',
                     jList[i].iconClass || '',
                     groupingSpecs,
                     (jList[i].readOnly || '').toUpperCase() == 'YES' ? 'readonly' : '');
     
                 } else if ((jList[i].domType || jList[i].type || '').toUpperCase() == "FORM") {
                   html += formModel.format(
                     elementId,
                     jList[i].class || 'col-md-12',
                     jList[i].dataPrefix || '',
                     groupingSpecs,
                     '',
                     (jList[i].readOnly || '').toUpperCase() == 'YES' ? 'disabled=yes' : '');
     
                 } else if ((jList[i].domType || jList[i].type || '').toUpperCase() == "SELECT") {
                   /* o problema Ã© adiar as opÃ§oes do select */
                   if (jList[i].query) {
                     html += selectModelWithQuery.format(elementId, jList[i].class, jList[i].label || '', groupingSpecs, null);
                     for(var auxP in jList[i].query.params) {
                       if (jList[i].query.params.hasOwnProperty(auxP)) {
                         jList[i].query.params[auxP] = yAnalise(jList[i].query.params[auxP], undefined, formManager);
                       }
                     }
                     _loadSelectOptions(jList[i].query, elementId, (jList[i].readOnly || '').toUpperCase() == 'YES');
                   } else if (jList[i].options) {
                     var auxOptions = '';
                     for(var op in jList[i].options) {
                       if (jList[i].options.hasOwnProperty(op)) {
                         auxOptions+="<option value='{0}'>{1}</option>".format(jList[i].options[op].value, jList[i].options[op].label);
                       }
                     }
                     html += selectModelWithOptions.format(elementId, jList[i].class, jList[i].label || '', groupingSpecs, null, auxOptions);
                   }
     
                 } else if ((jList[i].domType || jList[i].type || '').toUpperCase() == "TEXTAREA") {
                   html += textareaModel.format(
                     elementId,
                     jList[i].class || 'col-md-12',
                     jList[i].label || '',
                     jList[i].cols || '80',
                     jList[i].rows || '4',
                     groupingSpecs,
                     (jList[i].readOnly || '').toUpperCase() == 'YES' ? 'disabled=yes' : '');
     
                 } else if ((jList[i].domType || jList[i].type || '').toUpperCase() == "CHECKBOX") {
                   /* CHECKBOXGROUP */
                   /*
                   if (jList[i].label)
                     html+="<div class='form-group col-md-12'><label>{0}</label></div>".format(jList[i].label);
                   */
                   html += checkboxModel.format(
                     elementId,
                     jList[i].class || 'col-md-12',
                     jList[i].description || '',
                     jList[i].value, jList[i].label || '',
                     groupingSpecs,
                     (jList[i].readOnly || '').toUpperCase() == 'YES' ? 'disabled=yes' : '');
     
                 } else if ((jList[i].domType || jList[i].type || '').toUpperCase() == "RADIO") {
     
                   html += "<div class='form-group {1} row'><label>{0}</label><div class='row'>".format(jList[i].label || '', jList[i].class || '');
                   for (n = 0; n < ((jList[i].options) || {
                       length: 0
                     }).length; n++) {
                     html += radioModel.format(
                       elementId + '_' + ('' + (jList[i].options[n].value || n)).replace(/[^0-9a-z]/gi, ''),
                       jList[i].options[n].class || jList[i].defaultOptionClass || 'col-md-12',
                       jList[i].options[n].label || '',
                       elementId,
                       jList[i].options[n].value,
                       groupingSpecs,
                       (jList[i].readOnly || '').toUpperCase() == 'YES' ? 'disabled=yes' : '');
                   }
                   html += "</div></div>";
     
                 } else if ((jList[i].domType || jList[i].type || '').toUpperCase() == "INPUT") {
                   if ((jList[i].hidden || '').toUpperCase() == 'YES') {
                     html += hiddenInputModel.format(elementId, groupingSpecs, jList[i].value || '');
                   } else {
                     if (jList[i].query) {
                       sequence++;
                       html += inputWithListModel.format(
                         elementId,
                         (jList[i].class || 'col-md-12'),
                         jList[i].inputMode || 'text',
                         jList[i].label || '',
                         (jList[i].readOnly || '').toUpperCase() == 'YES' ? 'readonly' : '',
                         groupingClass, /* groupingSpecs nÃ£o pode ir aqui */
                         elementId + "_datalist_" + sequence,
                         jList[i].query.subject,
                         jList[i].query.action,
                         JSON.stringify(jList[i].query.lineSpec || {}),
                         JSON.stringify(jList[i].resultSpec || {}));
                       addToEventList(elementId, "keyup", "interface.input_keyup");
                       addToEventList(elementId, "input", "interface.input_input");
                     } else {
                       if ("undefined" != typeof jList[i].rightAddon) {
                         html += inputModelWithRightAddon.format(
                           elementId,
                           (jList[i].class || 'col-md-12'),
                           jList[i].inputMode || 'text',
                           jList[i].label || '',
                           (jList[i].readOnly || '').toUpperCase() == 'YES' ? 'readonly' : '',
                           groupingClass, /* groupingSpecs nÃ£o pode ir aqui */
                           '',
                           jList[i].rightAddon);
                       } else if ("undefined" != typeof jList[i].leftAddon) {
                         html += inputModelWithLeftAddon.format(
                           elementId,
                           (jList[i].class || 'col-md-12'),
                           jList[i].inputMode || 'text',
                           jList[i].label || '',
                           (jList[i].readOnly || '').toUpperCase() == 'YES' ? 'readonly' : '',
                           groupingClass, /* groupingSpecs nÃ£o pode ir aqui */
                           '',
                           jList[i].leftAddon);
                       } else {
                         html += inputModel.format(
                           elementId,
                           (jList[i].class || 'col-md-12'),
                           jList[i].inputMode || 'text',
                           jList[i].label || '',
                           (jList[i].readOnly || '').toUpperCase() == 'YES' ? 'readonly' : '',
                           groupingClass, /* groupingSpecs nÃ£o pode ir aqui */
                           '');
                       }
                     }
                   }
                 } else {
                   that.log("'{0}' is not a DOM object. Treating it as HIDDEN".format(elementId));
                   html += hiddenInputModel.format(elementId, groupingSpecs, jList[i].value || '');
                 }
     
                 html = html.replace(new RegExp("<!--USERDATA-->", 'g'), userData);
     
               }
             }
     
             masterObject.itemCounter++;
             if (masterObject.id || '' > '') {
               html = "<div class={0}>{1}</div>".format(masterObject.class, html);
             }
             aux = y$(masterObject.id);
             if (aux) {
               aux.setAttribute('data-item-counter', masterObject.itemCounter);
             }
     
           }
     
           return html;
         }
     
         var controlButtonAction = function(e) {
           /* cuidado... ele estÃ¡ voltando aqui mas nem tudo estÃ¡ onde deveria, entÃ£o tudo tem que ser montado novamente */
           e = e.target;
           sequence = (new Date()).getTime();
           eventList = [];
           apiUsageList = [];
           var btn = e.closest('button');
           var btnAction = btn.getAttribute('data-action');
           var masterObjectId = btn.getAttribute('data-master-object-id');
           var masterObject = y$(masterObjectId);
           if (btnAction == 'add') {
             var html = createFields(that._repeatableFieldInfo[masterObjectId], masterObjectId);
             // masterObject.innerHTML+=html;
             masterObject.insertAdjacentHTML('beforeend', html);
             that.log("Complementing {0} at {1}".format(form.id, masterObjectId));
             setTimeout(function() {
               finalAdditionWork({
                 'eventList': eventList,
                 'apiUsage': apiUsageList
               });
             }, 125);
           } else if (btnAction == 'remove') {
             var container = btn.parentNode.parentNode;
             if (container)
               container.remove();
           }
         };
     
         var finalAdditionWork = function(formPrepared) {
           var elementId;
     
           that.log("API Usage");
           for (var apiUsageDetail in formPrepared.apiUsage) {
             if (formPrepared.apiUsage.hasOwnProperty(apiUsageDetail)) {
               that.log("&nbsp;&nbsp;" + apiUsageDetail);
               for (elementId in formPrepared.apiUsage[apiUsageDetail])
                 if (formPrepared.apiUsage[apiUsageDetail].hasOwnProperty(elementId)) {
                   that.log("&nbsp;&nbsp;&nbsp;&nbsp;used by '{0}'".format(elementId));
                 }
             }
           }
     
           var __addEvent = function(elementId, eventName, eventHandler)  {
             if (eventName=='changeOrBlur') {
               var auxElement = y$(elementId);
               if ("undefined" == typeof (auxElement ||{}).length) {
                 if ((auxElement.nodeName=='INPUT') && (!((auxElement.type=='radio') || (auxElement.type=='checkbox'))))
                   __addEvent(elementId, 'blur', eventHandler);
                 else
                   __addEvent(elementId, 'change', eventHandler);
               } else {
                 for(var el=0; el<(auxElement ||{}).length || 0; el++) {
                   __addEvent(auxElement[el].id, eventName, eventHandler);
                 }
               }
             } else {
               addEvent(elementId, eventName, eventHandler);
               if ((eventName == 'change')||(eventName == 'blur')) {
                 if ("undefined" == typeof dispatchedEvents["{0}.{1}".format(eventName, elementId)]) {
                   dispatchedEvents["{0}.{1}".format(eventName, elementId)] = true;
                   that.log("dispatching event {0} on{1}".format(elementId, eventName));
                   DOM_dispatchEvent(eventName, elementId);
                 }
               }
             }
           };
     
           that.log("DOM Events");
           var dispatchedEvents = [];
           for (elementId in formPrepared.eventList) {
             if (formPrepared.eventList.hasOwnProperty(elementId)) {
               that.log("&nbsp;&nbsp;For '{0}'".format(elementId));
               for (var eventName in formPrepared.eventList[elementId]) {
                 if (formPrepared.eventList[elementId].hasOwnProperty(eventName)) {
                   for (var n = 0; n < formPrepared.eventList[elementId][eventName].length; n++) {
                     var ev = formPrepared.eventList[elementId][eventName][n];
                     if ("function" == typeof ev) {
                       __addEvent(elementId, eventName, ev);
                     } else {
                       ev = ev.split('.');
                       var evtError = '';
                       if ("object" == typeof window[ev[0]]) {
                         if ("function" == typeof window[ev[0]][ev[1]]) {
                           __addEvent(elementId, eventName, window[ev[0]][ev[1]] );
                         } else {
                           evtError = "'{0}' not found in '{1}'".format(ev[1], ev[0]);
                         }
                       } else {
                         evtError = "'{0}' was not found or is not an object".format(ev[0]);
                       }
                       if (evtError > '')
                         evtError = "(Error: {0})".format(evtError);
                       that.log("&nbsp;&nbsp;&nbsp;&nbsp;{1} -> {2}.{3}() {4}".format(elementId, eventName, ev[0], ev[1], evtError));
                     }
                   }
                 }
               }
             }
           }
     
           if ("undefined" != typeof flatpickr) {
             flatpickr(".date-select-field",{dateFormat: "d/m/Y"});
           }
     
           if ("undefined" != typeof InputMask) {
             new InputMask().Initialize(document.querySelectorAll(".masked-date-field"),
             {
               mask: InputMaskDefaultMask.Date,
               placeHolder: "dd/mm/yyyy"
             });
             new InputMask().Initialize(document.querySelectorAll(".masked-time-field"),
             {
               mask: InputMaskDefaultMask.TimeShort,
               placeHolder: "hh:mm"
             });
     
           }
     
         };
     
         /* http://2ality.com/2013/06/triggering-events.html */
         /* http://192.168.11.62/bfb/ */
         var prepareFieldRet = new Promise(
           function(solve, reject) {
     
             solve({
               'html': "<div class='json-dyn-form'>"+createFields(formInfoJ)+'</div>',
     
               'eventList': eventList,
               'apiUsage': apiUsageList
             });
           }
     
         );
     
         var ret = new Promise(
           function(solve, reject) {
     
             prepareFieldRet.then(function(formPrepared) {
               form.removeEventListener("filled", that.formFilled);
     
               that.log("Filling {0}".format(form.id));
               form.innerHTML = formPrepared.html;
     
               finalAdditionWork(formPrepared);
     
               solve();
     
               form.addEventListener("filled", that.formFilled);
     
             }).catch(reject);
     
           }
         );
     
         return ret;
     
       };
     
       that.init = function() {
         that.requestFunction = ycomm.invoke;
     
         addEvent(".op-menu", "click", that.openTab);
         addEvent(".btn-atualizar", "click", that.refreshTable);
         addEvent(".btn-adicionar", "click", that.insertData);
     
         /* apenas para desenvolvimento. Em produÃ§Ã£o tem que estar TRUE */
         that.allowCacheForm = false;
         that._repeatableFieldInfo = [];
     
         var tables = y$('.table');
         if (tables)
           tables.forEach(function(table) {
             ycomm.dom.fillElement(table.id, {});
           });
     
         /* analiso a url para ver se o usuÃ¡rio quer ir para uma ancora */
         setTimeout(function() {
           that.openTab(window.location.hash);
         }, 512);
         return that;
       };
     
       return that.init();
     };
     
     if ("function" == typeof addOnLoadManager) {
       addOnLoadManager(
         function() {
           yInterface = yInterfaceObj();
         }
       );
     }
 /* END yinterface.js */
 _dump("yinterface");