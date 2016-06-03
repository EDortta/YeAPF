/*********************************************
 * app-src/js/yanalise.js
 * YeAPF 0.8.49-10 built on 2016-06-03 13:09 (-3 DST)
 * Copyright (C) 2004-2016 Esteban Daniel Dortta - dortta@yahoo.com
 * 2016-05-18 19:09:30 (-3 DST)
 * First Version (C) 2014 - esteban daniel dortta - dortta@yahoo.com
**********************************************/
//# sourceURL=app-src/js/yanalise.js

// ==== YeAPF - Javascript implementation

function yAnalise(aLine, aStack)
{
  if (aLine!=undefined) {

    aLine = unmaskHTML(aLine);

    var yPattern = /%[+(\w)]|[]\(/gi;
    var yFunctions = ',int,integer,intz,intn,decimal,ibdate,tsdate,tstime,date,time,words,image,nl2br,quoted,singleQuoted,condLabel';
    var p;
    var aValue='';

    while ((p = aLine.search(yPattern)) >=0 ) {
      var p1 = aLine.slice(p).search(/\(/);
      var c1 = aLine.slice(p + p1 + 1, p + p1 + 2);
      if ((c1=='"') || (c1=="'")) {
        var c2;
        var p3 = p + p1 + 1 ;
        do {
          p3++;
          c2 = aLine.slice(p3, p3 + 1);
        } while ((c2!=c1) && (p3<aLine.length));
        var p2 = p3 + aLine.slice(p3).search(/\)/) - p;
      } else
        var p2 = aLine.slice(p).search(/\)/);

      var funcName = aLine.slice(p+1, p+p1);
      var funcParams = aLine.slice(p + p1 + 1, p + p2);
      var parametros = funcParams;
      funcParams = funcParams.split(',');
      for (var n=0; n<funcParams.length; n++)
        funcParams[n] = yAnalise(funcParams[n], aStack);

      aValue = undefined;
      var fParamU = funcParams[0].toUpperCase();
      var fParamN = funcParams[0];
      if (aStack!=undefined) {
        // can come a stack or a simple unidimensional array
        if (aStack[0]==undefined) {
          if (aStack[fParamU])
            aValue = yAnalise(aStack[fParamU], aStack);
          else
            aValue = yAnalise(aStack[fParamN], aStack);
        } else {
          for(var sNdx=aStack.length -1 ; (sNdx>=0) && (aValue==undefined); sNdx--)
            if (aStack[sNdx][fParamU] != undefined)
              aValue = yAnalise(aStack[sNdx][fParamU], aStack);
            else if (aStack[sNdx][fParamN] != undefined)
              aValue = yAnalise(aStack[sNdx][fParamN], aStack);
        }
      } else {
        if (eval('typeof '+fParamN)=='string')
          aValue=eval(fParamN);
        else
          aValue=yAnalise(fParamN);
      }

      if (aValue==undefined)
          aValue = '';
      funcParams[0] = aValue;

      switch (funcName)
      {
        case 'integer':
        case 'int':
        case 'intz':
        case 'intn':
          aValue = str2int(aValue);
          if (aValue==0) {
            if (funcName=='intz')
              aValue='-';
            else if (funcName=='intn')
              aValue='';
          }
          break;
        case 'decimal':
          var aDecimals = Math.max(0,parseInt(funcParams[1]));
          aValue = parseFloat(aValue);
          aValue = aValue.toFixed(aDecimals);
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
        case 'nl2br':
          aValue = aValue.replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + '<br>' + '$2');
          break;
        case 'words':
          var auxValue = aValue.split(' ');

          var aStart = Math.max(0,str2int(funcParams[1]));
          var aCount = Math.max(auxValue.length - 1 ,str2int(funcParams[2]));
          var aWrap  = Math.max(0,str2int(funcParams[3]));

          aValue='';
          for (var n=aStart; n<aStart+aCount; n++) {
            var tmpValue = onlyDefinedValue(auxValue[n]);
            if (tmpValue>'')
              aValue+=' '+tmpValue;
          }

          if (aWrap>0)
            aValue = wordwrap(aValue, aWrap, '<br>', true);

          break;
        case 'quoted':
          aValue = ('"'+aValue).trim()+'"';
          break;
        case 'singleQuoted':
          aValue = ("'"+aValue).trim()+"'";
          break;
        case 'condLabel':

          break;
        default:
          if (funcName>'') {
            if (eval('typeof '+funcName) == 'function') {
              var parametros='';
              for (var n=0; n<funcParams.length; n++) {
                if (parametros>'')
                  parametros += ','

                parametros += "'"+funcParams[n]+"'";
              }

              var chamada = '<script>'+funcName+'('+parametros+');</'+'script>';
              aValue = chamada.evalScripts();
            }
          }
          break;
      }

      aLine = aLine.slice(0,p) + aValue + aLine.slice(p + p2 + 1);
    }
  } else
    aLine='';

  return aLine;
}
