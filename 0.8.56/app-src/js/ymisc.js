/*********************************************
 * app-src/js/ymisc.js
 * YeAPF 0.8.56-87 built on 2017-04-03 17:56 (-3 DST)
 * Copyright (C) 2004-2017 Esteban Daniel Dortta - dortta@yahoo.com
 * 2017-04-03 17:55:46 (-3 DST)
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
    if (aElementId>'') {
      if (aElementId.substr(0,1)=='#')
        aElementId = aElementId.substr(1);
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

function produceWaitMsg(msg) {
  var feedbackCSS='<style type="text/css"><!--.yWarnBanner {  font-family: Georgia, "Times New Roman", Times, serif;  font-size: 16px;  font-style: normal; font-variant: normal; font-weight: normal;  text-transform: none; margin: 16px; padding: 8px; background-color: #DFEEF2;  border: 1px dotted #387589; line-height: 24px;}--></style>';
  var feedbackText = '<div class=yWarnBanner><img src="images/waitIcon.gif" height=18px>&nbsp;{0}&nbsp;</div>';
  var aux=feedbackCSS + feedbackText.format(msg);
  return aux;
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
    s2 = obj.contentWindow.document.body.scrollHeight + 40 - objMargin.height;
    bestSize=Math.max(s1, s2);
    obj.style.height = bestSize + 'px';

    s1 = screen.width;
    s2 = obj.contentWindow.document.body.scrollWidth + 40 - objMargin.width;
    bestSize=Math.max(s1, s2);
    obj.style.width = bestSize + 'px';

    onResize = obj.getAttribute('onResize');
    if (onResize) {
      eval(onResize);
    }
  };
}


/*
 * HTMLElement prototype extensions
 */
var _expectedType;
if ((isOnMobile()) && (parseInt(getAndroidVersion(), 10)<3)) {
  /* gingerbread uses an object instead of function */
  _expectedType="object";
} else
  _expectedType="function";
_dump("ExpectedType="+_expectedType);
_dump("typeof HTMLElement = "+typeof HTMLElement);

if (typeof HTMLElement==_expectedType) {
  HTMLElement.prototype.hasClass = function (aClassName) {
    var ret = false;
    if (this.className) {
      var aClasses = this.className.split(' ');
      for(var i = 0; i<aClasses.length; i++) {
        if (aClasses[i] == aClassName)
          ret = true;
      }
    }
    return ret;
  };

  HTMLElement.prototype.deleteClass = function(aClassName) {
    var aNewClasses='';
    var aClasses=this.className.split(' ');
    for(var i = 0; i<aClasses.length; i++) {
      if (aClasses[i] != aClassName) {
        if (aNewClasses>'')
          aNewClasses = aNewClasses+' ';
        aNewClasses = aNewClasses + aClasses[i];
      }
    }
    this.className=aNewClasses;
    return this;
  };

  HTMLElement.prototype.removeClass = HTMLElement.prototype.deleteClass;

  HTMLElement.prototype.addClass = function(aClassName) {
    var aClassExists=false;
    var aClasses=this.className.split(' ');
    for(var i = 0; i<aClasses.length; i++) {
      if (aClasses[i] == aClassName) {
        aClassExists = true;
      }
    }
    if (!aClassExists)
      this.className = aClassName+ ' ' + this.className;
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
    aFormat='yyyy-mm-dd hh:mm:ss';
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
    for(i=0; i<sortedInfo.length && i<auxDateInfo.length; i++)
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
  var h=Math.floor(aMinutes / 60);
  var m=pad(aMinutes % 60,2);
  return h+':'+m;
}

/* unix timestamp to day of week (0=sunday) */
function timestamp2dayOfWeek(aTimestamp) {
  var aux=new Date();
  aux.setTime(aTimestamp*1000);
  return aux.getDay();
}

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
  var d=new Date();
  d.setFullYear(aDate['y']);
  d.setMonth(aDate['m']-1);
  d.setDate(aDate['d']);
  d.setHours(aDate['H']);
  d.setMinutes(aDate['M']);

  return d;
}

/* ISO8601 -> french date dd/mm/yyyy */
function UDate2Date(aUDate, aFormat) {
  if (typeof aFormat==='undefined')
    aFormat="d/m/y";
  var ret='';
  var aDate=extractDateValues(aUDate,'yyyymmddHHMMSS');
  if (aDate)
    ret='';
    for(var i=0; i<aFormat.length; i++)
      if (/^[d,m,y]+$/.test(aFormat[i]))
        ret+=aDate[aFormat[i]];
      else
        ret+=aFormat[i];
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

    auxLine = auxLine.replace(/\&\#91\;/g, '<');
    auxLine = auxLine.replace(/\&\#93\;/g, '>');
    auxLine = auxLine.replace(/\[/g, '<');
    auxLine = auxLine.replace(/\]/g, '>');
  } else if (typeof auxLine=='number') {
      auxLine = auxLine.toString();
  } else
    auxLine = '';
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
  asLatitude = asLatitude || true;
  var positive = Math.sign(dec) > 0,
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
  that = { };
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
  if (color.substr(0,1)=='#')
    color=color.substr(1);


  var r=hex2dec(color.substr(0,2));
  var g=hex2dec(color.substr(2,2));
  var b=hex2dec(color.substr(4,2));

  return [r, g, b];
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


/*
 * The original source code was picked from
 * http://www.openjs.com/scripts/xml_parser/
 * without copyright notes.
 *
 * The job here was to package the function inside a functional
 * object oriented model
 */

var xml2array = function (xmlDoc) {
  var key;
  var that = {};
  that.not_whitespace = new RegExp(/[^\s]/);
  that.parent_count=null;

  //Process the xml data
  that.xml2array = function(xmlDoc,parent_count) {
    var arr, temp_arr, temp, parent = "";
    parent_count = parent_count || {};

    var attribute_inside = 0; /*:CONFIG: Value - 1 or 0
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
        arr = that.xml2array(xmlDoc.childNodes[0],parent_count); //:RECURSION:
      } else { //If there is more than one childNodes, go thru them one by one and get their results.
        if (!arr)
          arr=[];

        var index = 0;

        for(var i=0; i<xmlDoc.childNodes.length; i++) {//Go thru all the child nodes.
          temp = that.xml2array(xmlDoc.childNodes[i],parent_count); //:RECURSION:
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
                        arr['attribute_' + lastKey + '_' + nname] = xmlDoc.childNodes[i].attributes[j].value;
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

  window.addEventListener("load", function() {    
    for(var i=0; i<_onLoadMethods.length; i++)
      if (_onLoadMethods.hasOwnProperty(i))
        if (_onLoadMethods[i]!==undefined)
          _onLoadMethods[i]();
    if (!isOnMobile()) {
      var event = new Event('deviceready');
      document.dispatchEvent(event);      
    }
  }, false);


  var addEvent = function(elem, eventName, eventHandler) {
    if (typeof elem == 'string') elem=y$(elem);
    if ((elem === null) || (typeof elem === 'undefined')) return;

    var i;

    if (elem.length) {
      for(i=0; i<elem.length; i++)
        addEvent(elem[i], eventName, eventHandler);
    } else {
      var eventList=eventName.split(" "), aEventName;
      for(i=0; i<eventList.length; i++) {
        aEventName=eventList[i];
        if ( elem.addEventListener ) {
          elem.addEventListener( aEventName, eventHandler, false );
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
