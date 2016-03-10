/*
    yeapf.js
    YeAPF 0.8.48-6 built on 2016-03-09 09:16 (-3 DST)
    Copyright (C) 2004-2016 Esteban Daniel Dortta - dortta@yahoo.com
    2016-01-23 22:00:19 (-3 DST)
*/

  // window.alert('YeAPF loaded\n#(u).#(s).#(a)\n#(_httpReferer_)');
  var curX=0;
  var curY=0;
  var openY=0;
  var openX=0;
  var xDisplacement=0;
  var yDisplacement=0;
  var xAbsPos=0;
  var yAbsPos=0;
  var fWidth=200;
  var fHeight=200;
  var fFirstPosition=true;
  var fTitle='';

  if (document.layers) document.captureEvents(Event.MOUSEMOVE);

  document.onmousemove=mtrack;

  function getCurrentTimeStamp()
  {
    var aux = new Date();
    return Math.floor(aux.getTime() / 1000);
  }

  function sleep(numberMillis){
    var sysTimeStamp = new Date();
    var exitTime = sysTimeStamp.getTime() + numberMillis;
    while (true){
      sysTimeStamp = new Date();
      if (sysTimeStamp.getTime() > exitTime)
        break;
    }
  };

  function mtrack(e) {
    if (!e)
      var e = window.event;

    if (e.pageX || e.pageY)   {
      curX = e.pageX;
      curY = e.pageY;
    }
    else if (e.clientX || e.clientY)  {
      curX = e.clientX + document.body.scrollLeft
        + document.documentElement.scrollLeft;
      curY = e.clientY + document.body.scrollTop
        + document.documentElement.scrollTop;
    }
  }

  var xmlhttp=null;
  var curTagName='';
  var curTag=null;
  var curForm='';
  var curReturnFunction='';

  var clientWidth=800;
  var clientHeight=600;

  getClientSize();


  function getClientSize() {
    var myWidth = 0, myHeight = 0;
    if( typeof( window.innerWidth ) == 'number' ) {
      //Non-IE
      myWidth = window.innerWidth;
      myHeight = window.innerHeight;
    } else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) {
      //IE 6+ in 'standards compliant mode'
      myWidth = document.documentElement.clientWidth;
      myHeight = document.documentElement.clientHeight;
    } else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) {
      //IE 4 compatible
      myWidth = document.body.clientWidth;
      myHeight = document.body.clientHeight;
    }
    clientWidth=myWidth;
    clientHeight=myHeight;
    // alert(clientWidth, clientHeight);
    return [clientWidth, clientHeight];
  }

  function getScrollXY()
  {
    // http://www.howtocreate.co.uk/tutorials/javascript/browserwindow
    var scrOfX = 0, scrOfY = 0;
    if( typeof( window.pageYOffset ) == 'number' ) {
      //Netscape compliant
      scrOfY = window.pageYOffset;
      scrOfX = window.pageXOffset;
    } else if( document.body && ( document.body.scrollLeft || document.body.scrollTop ) ) {
      //DOM compliant
      scrOfY = document.body.scrollTop;
      scrOfX = document.body.scrollLeft;
    } else if( document.documentElement && ( document.documentElement.scrollLeft || document.documentElement.scrollTop ) ) {
      //IE6 standards compliant mode
      scrOfY = document.documentElement.scrollTop;
      scrOfX = document.documentElement.scrollLeft;
    }
    return [ scrOfX, scrOfY ];
  }


  function setAbsoluteFormPosition(x,y)
  {
    xAbsPos=x;
    yAbsPos=y;
  }

  function setRelativeFormPosition(x,y)
  {
    xDisplacement=x;
    yDisplacement=y;
  }

  function setFormSize(aWidth, aHeight)
  {
    fWidth=aWidth;
    fHeight=aHeight;
  }

  function setFormTitle(aTitle)
  {
    fTitle=aTitle;
  }

  function adjustZ(aDivName, aZValue)
  {
    var theDiv=document.getElementById(aDivName);
    theDiv.style.zindex=aZValue;
  }

  function existsCSS(selectorName)
  {
    selectorName = selectorName.toLowerCase();
    if (!document.styleSheets) return false;

    var ret=false;

    for (var y=0; y<document.styleSheets.length; y++) {
      var theRules = new Array();
      if (document.styleSheets[y].cssRules)
        theRules=document.styleSheets[y].cssRules;
      else if (document.styleSheets[y].rules)
        theRules=document.styleSheets[y].rules;

      for (var i=0; i<theRules.length; i++) {
        var auxSelectorName=theRules[i].selectorText;
        if (auxSelectorName==selectorName)
          ret=true;
      }
    }
    return ret;
  }

  function changeCSS(selectorName, propertyName, value)
  {
    selectorName = selectorName.toLowerCase();
    if (!document.styleSheets) return;

    for (var y=0; y<document.styleSheets.length; y++) {
      var theRules = new Array();
      if (document.styleSheets[y].cssRules)
        theRules=document.styleSheets[y].cssRules;
      else if (document.styleSheets[y].rules)
        theRules=document.styleSheets[y].rules;

      for (var i=0; i<theRules.length; i++) {
        var auxSelectorName=theRules[i].selectorText;
        if (auxSelectorName==selectorName)
          theRules[i].style.setProperty(propertyName, value);
      }
    }
  }

  var feedbackCSS=new String('<style type="text/css"><!--.aviso {  font-family: Georgia, "Times New Roman", Times, serif;  font-size: 16px;  font-style: normal; font-variant: normal; font-weight: normal;  text-transform: none; margin: 16px; padding: 8px; background-color: #DFEEF2;  border: 1px dotted #387589; line-height: 24px;}--></style>');

  function produceWaitMsg(msg)
  {
    var aux=feedbackCSS + '<div class=aviso><img src="images/waitIcon.gif" height=18px>&nbsp;'+msg+'</div>';
    return aux;
  }

  function _feedback_message(msg)
  {
    if (curTag) {
      curTag.style.zIndex=1;
      curTag.innerHTML=produceWaitMsg(msg);
      if (fFirstPosition) {
        curTag.style.left=Math.max(0,openX-128);
        curTag.style.top=Math.max(0,openY-32);
        curTag.style.height='64px';
        curTag.style.width='256px';
      }
      curTag.style.borderWidth=1;
      curTag.style.borderStyle='solid';
      curTag.style.borderColor='000000';
      curTag.style.overflow='';

      curTag.style.display='block';
    }
  }

  // ==== rotinas para Ajax
  function formArrive()
  {
    if (xmlhttp.readyState==4) {
      if (xmlhttp.status==200) {
        if (xmlhttp.overrideMimeType)
          xmlhttp.overrideMimeType('text/xml; charset=ISO-8859-1');

        curTag.style.zIndex=1;
        auxHTML="<div style='color: white; background-color:#005555; padding: 4px'><table width=100%><tr><td><font color=#ffffff><b>"+fTitle+"</b></font></td><td width=18><a href=javascript:closeForm('"+curTagName+"')><img src='"+_btnClose+"' border=0 height=18px></a></td></tr></table></div>";
        curTag.innerHTML=auxHTML+' '+xmlhttp.responseText;

        var auxX=openX;
        var auxY=openY;
        if (xAbsPos<=0)
          auxX=openX-fWidth / 2;
        if (auxX<8)
          auxX=8;

        var maxX;
        if (document.documentElement.clientWidth)
          maxX=document.documentElement.clientWidth-parseInt(curTag.style.width)-20;
        else
          maxX=document.documentElement.offsetWidth-parseInt(curTag.style.width)-20;
        if (maxX<8)
          maxX=8;
        while ((auxX>0) && (auxX>maxX))
          auxX--;

        if (yAbsPos<=0)
          auxY=openY-16;
        if (auxY<8)
          auxY=8;

        var maxY;
        if (document.documentElement.clientHeight)
          maxY=document.documentElement.clientHeight-parseInt(curTag.style.height)-20;
        else
          maxY=document.documentElement.offsetHeight-parseInt(curTag.style.height)-20;
        if (maxY<8)
          maxY=8;
        while ((auxY>0) && (auxY>maxY))
          auxY--;

        curTag.style.position='absolute';
        curTag.style.zindex=1;
        curTag.style.borderWidth=1;
        curTag.style.borderStyle='solid';
        curTag.style.borderColor='000000';
        curTag.style.overflow='scroll';

        if (fFirstPosition) {
          curTag.style.left=auxX;
          curTag.style.top=auxY;
        }
        fFirstPosition=false;

        curTag.style.width=fWidth+'px';
        curTag.style.height=(parseInt(fHeight)+18)+'px';

        curTag.style.display='block';


        auxID=document.getElementById('bottomTable');
        if ((auxID) && (auxID.style.top))
          curTag.style.height=(parseInt(auxID.style.top)+24)+'px';
      } else
        alert("Não foi possivel carregar o formulario "+curForm)
      curTagName='';
    }
  }
  // http://ajaxpatterns.org/On-Demand_Javascript
  function scriptArrive()
  {
    if (xmlhttp.readyState==4) {
      if (xmlhttp.status==200) {
        if (xmlhttp.overrideMimeType)
          xmlhttp.overrideMimeType('text/xml; charset=ISO-8859-1');
        if (file_exists('../imagens/btn-close.gif'))
          _btnClose='../imagens/btn-close.gif';
        else
          _btnClose='imagens/btn-close.gif';

          auxHTML="<span  id=bottomTable><table><tr><td><img src='"+_btnClose+"' border=0></td><td><a href=javascript:closeForm('"+curTagName+"')>Fechar</a></td></tr></table></span>"
        curTag.innerHTML=xmlhttp.responseText+'<br>'+auxHTML;
        curTag.style.display='block';
        curTag.style.top=openY-16;
        auxX=openX-16;
        while ((auxX>0) && (auxX>window.innerWidth-curTag.style.width))
          auxX--;
        curTag.style.left=auxX;
        auxID=document.getElementById('bottomTable');
        curTag.style.height=auxID.style.top;
      } else
        alert("Não foi possivel carregar o formulario "+curForm)
      curTagName='';
    }
  }

  function valueArrive()
  {
    if (xmlhttp.readyState==4) {
      if (xmlhttp.status==200) {
        if (xmlhttp.overrideMimeType)
          xmlhttp.overrideMimeType('text/xml; charset=ISO-8859-1');
        auxHTML=""
        curTag.innerHTML=xmlhttp.responseText;
    if (curReturnFunction!=null)
      curReturnFunction(xmlhttp.responseText);
      } else
        alert("Não foi possivel carregar o formulario "+curForm)
    }
  }

  function getAnswer(returnType,returnFunction,tag,url,u,s,a,id)
  {
    while (curTagName>'')
     sleep(1);
    curTagName=tag;
    curTag=document.getElementById(tag);
    curForm=url;

    openX=curX;
    openY=curY;

    xmlhttp=null;
    if (window.XMLHttpRequest)
      xmlhttp=new XMLHttpRequest()
    else if (window.ActiveXObject)
      xmlhttp=new ActiveXObject("Microsoft.XMLHTTP")

    if (xmlhttp!=null) {
      urlArgs='';

      urlArgs = tag+','+u+','+s+','+a+','+id;

      for (var i=6; i<arguments.length; i++) {
        urlArgs += ',';
        urlArgs += arguments[i];
      }

      url=_buildFormPath+'?'+url+','+urlArgs;

      curForm=url;

    if (returnType=='text')
        xmlhttp.onreadystatechange=formArrive;
    else if (returnType=='script')
        xmlhttp.onreadystatechange=scriptArrive;
    else if (returnType=='value')
        xmlhttp.onreadystatechange=valueArrive;
    if (returnFunction>'')
      curReturnFunction=returnFunction;
    else
      curReturnFunction=null;
      xmlhttp.open("GET",url,true);
      xmlhttp.setRequestHeader("Content-Type", "text/plain;charset=ISO-8859-1");
      xmlhttp.send(null)
    } else
      alert("Este navegador não oferece suporte para XMLHTTP.")
  }

  function buildForm(tag,url,u,s,a,id)
  {
    // window.alert(url+' '+u+' '+s+' '+a);
    while (curTagName>'')
     sleep(1);

    if (tag.substring(0,7)=='parent.') {
      tag=tag.substring(7);
      var  auxTag=parent.document.getElementById(tag);
    } else
      var  auxTag=document.getElementById(tag);

    if (!auxTag) {
      window.alert("DIV de retórno "+tag+" não localizado");
    } else {
      curTagName=tag;
      curTag=auxTag;
      curForm=url;

      if (fFirstPosition) {
        if (xAbsPos>0)
          openX=xAbsPos;
        else
          openX=curX+xDisplacement;

        if (yAbsPos>0)
          openY=yAbsPos;
        else
          openY=curY+yDisplacement;
      }

      _feedback_message('Aguarde...');


      xmlhttp=null;
      if (window.XMLHttpRequest)
        xmlhttp=new XMLHttpRequest()
      else if (window.ActiveXObject)
        xmlhttp=new ActiveXObject("Microsoft.XMLHTTP")

      if (xmlhttp!=null) {
        urlArgs='';

        if ((s=='') || (u=='') || (a==''))
          window.alert("Your app is trying to use buildForm in the old-fashioned way.\nUse xYApp.js insted");

        urlArgs = tag+','+u+','+s+','+a+','+id;

        for (var i=6; i<arguments.length; i++) {
          urlArgs += ',';
          urlArgs += arguments[i];
        }

        url=_buildFormPath+'?'+url+','+urlArgs;

        // window.alert(tag+'\n'+url+'\n'+u+'\n'+s+'\n'+a+'\n'+id+'\n'+xAbsPos+', '+yAbsPos+'\n'+openX+', '+openY);

        curForm=url;

        xmlhttp.onreadystatechange=formArrive;
        xmlhttp.open("GET",url,true)
        xmlhttp.send(null)
      } else
        alert("Este navegador não oferece suporte para XMLHTTP.")
    }
  }

  function requestFormData(tag,url,u,s,a,id)
  {
    while (curTagName>'')
     sleep(1);
    curTagName=tag;
    curTag=document.getElementById(tag);
    /*
    if (!curTag)
      window.alert(tag+' não localizado');
    */
    curForm=url;

    if (xAbsPos>0)
      openX=xAbsPos;
    else
      openX=curX+xDisplacement;

    if (yAbsPos>0)
      openY=yAbsPos;
    else
      openY=curY+yDisplacement;

    // _feedback_message('Aguarde...');

    xmlhttp=null;
    if (window.XMLHttpRequest)
      xmlhttp=new XMLHttpRequest()
    else if (window.ActiveXObject)
      xmlhttp=new ActiveXObject("Microsoft.XMLHTTP")

    if (xmlhttp!=null) {
      urlArgs='';

      urlArgs = tag+','+u+','+s+','+a+','+id;

      for (var i=6; i<arguments.length; i++) {
        urlArgs += ',';
        urlArgs += arguments[i];
      }

      url=_buildFormPath+'?'+url+','+urlArgs;

      // window.alert(tag+'\n'+url+'\n'+u+'\n'+s+'\n'+a+'\n'+id+'\n'+xAbsPos+', '+yAbsPos+'\n'+openX+', '+openY);

      curForm=url;

      xmlhttp.onreadystatechange=dataArrive;
      xmlhttp.open("GET",url,true)
      xmlhttp.send(null)
    } else
      alert("Este navegador não oferece suporte para XMLHTTP.")
  }

  function buildScript(tag,url,u,s,a,id)
  {
    while (curTagName>'')
     sleep(1);
    curTagName=tag;
    curTag=document.getElementById(tag);
    curForm=url;

    openX=curX+xDisplacement;
    openY=curY+yDisplacement;

    _feedback_message('Aguarde...');

    xmlhttp=null;
    if (window.XMLHttpRequest)
      xmlhttp=new XMLHttpRequest()
    else if (window.ActiveXObject)
      xmlhttp=new ActiveXObject("Microsoft.XMLHTTP")

    if (xmlhttp!=null) {
      urlArgs='';

      urlArgs = tag+','+u+','+s+','+a+','+id;

      for (var i=6; i<arguments.length; i++) {
        urlArgs += ',';
        urlArgs += arguments[i];
      }

      url=_buildFormPath+'?'+url+','+urlArgs;
      curForm=url;

      xmlhttp.onreadystatechange=scriptArrive
      xmlhttp.open("GET",url,true)
      xmlhttp.send(null)
    } else
      alert("Este navegador não oferece suporte para XMLHTTP.")
  }

  function __X__getValue(returnFunction,tag,url,u,s,a,id)
  {
  }

  function requestData(tag,url,u,s,a,id)
  {
    xmlhttp=null;
    if (window.XMLHttpRequest)
      xmlhttp=new XMLHttpRequest()
    else if (window.ActiveXObject)
      xmlhttp=new ActiveXObject("Microsoft.XMLHTTP")

    if (xmlhttp!=null) {
      urlArgs='';

      urlArgs = tag+','+u+','+s+','+a+','+id;

      for (var i=6; i<arguments.length; i++) {
        urlArgs += ',';
        urlArgs += arguments[i];
      }

      url=_buildFormPath+'?'+url+','+urlArgs;
      // url='buildForm.php?'+url+','+urlArgs;

      // window.alert(url);

      // window.alert('calling...'+tag+'\n'+url+'\n'+u+'\n'+s+'\n'+a+'\n'+id+'\n'+xAbsPos+', '+yAbsPos+'\n'+openX+', '+openY);

      curForm=url;

      xmlhttp.onreadystatechange=dataArrive
      xmlhttp.open("GET",url,true)
      xmlhttp.send(null)
    } else
      alert("Este navegador não oferece suporte para XMLHTTP.")
  }

  function dataArrive()
  {
    if (xmlhttp.readyState==4) {
      if (xmlhttp.status==200) {
        // window.alert(xmlhttp.responseText);
        if (xmlhttp.overrideMimeType)
          xmlhttp.overrideMimeType('text/xml; charset=ISO-8859-1');

        var xmlArray=xml2array(xmlhttp.responseXML);

        var xError=xmlArray['error'];
        var xRoot=xmlArray['root'];
        var xDataContext=xmlArray['root']['dataContext'];

        if (xError) {
          var errNo=xError['errNo'];
          var errMsg=xError['errMsg'];
          var errDetail=xError['errDetail'];
          window.alert('Err #'+errNo+'\n'+errMsg+'\n'+errDetail);
        } else if (xRoot) {
          var dNavScript=xDataContext['navScript'];
          var dTableID=xDataContext['tableID'];
          var dNavigatorID=xDataContext['navigatorID'];
          var dProgressBarID=xDataContext['progressBarID'];
          var dSqlID=xDataContext['sqlID'];
          var dRequestedRows=xDataContext['requestedRows'];
          var dFirstRow=xDataContext['firstRow'];
          var dRowCount=xDataContext['rowCount'];


          var xTarget=xRoot['target'];
          if (xRoot['data'])
            var xData=xRoot['data']['row'];

          var nRecCount=0;
          if (xData) {
            for (var j in xData) {
              addRow(xTarget, xData[j]);
              nRecCount++;
            }
            // window.alert(nRecCount+'>0?\n'+dNavigatorID+'=undefined?\n'+dRequestedRows+'='+dRowCount+'?');

            if (nRecCount>0) {
              if (dNavigatorID==undefined) {
                var dNavigatorID='';
                if (dRequestedRows==dRowCount) {
                  requestData(dNavigatorID,dNavScript,u,'dataRequest','getData',
                              dSqlID,dTableID,dNavigatorID,dProgressBarID,dRequestedRows,parseInt(dFirstRow)+parseInt(dRowCount));
                }
              }
            }
          }
        } else {
          var aux='';
          for(var j in xmlArray)
            aux=aux+xmlArray[j]+'\n';
          window.alert(aux);
        }

      } else
        alert("Não foi possivel carregar o formulario "+curForm)
    }
  }

  // Genera Purpouse Routines

  var undefinedValuesCount=0;
  var undefinedValuesList='';

  function onlyDefinedValue(aValue, valueName)
  {
    if (valueName==undefined)
      valueName='';
    if (aValue==undefined) {
      aValue='';
      if (valueName>'') {
        undefinedValuesCount++;
        if (undefinedValuesList>'')
          undefinedValuesList+=', ';
        undefinedValuesList += valueName;
      }
    }
    return aValue;
  }

  function nonZeroValue(aValue)
  {
    if ((aValue==undefined) || (aValue=='0')) {
      aValue='-';
      undefinedValuesCount++;
    }
    return aValue;
  }



  // ==== rotinas para transformação de dados

  // valores decimais expressados em string para um valor double
  // aceita só um separador, ou ',' ou '.'

  // ==== YeAPF - Javascript implementation

  function yAnalise(aLine, aStack)
  {
    if (aLine!=undefined) {

      aLine = unmaskHTML(aLine);

      var yPattern = /%[+(\w)]|[]\(/gi;
      var yFunctions = ',int,integer,intz,intn,decimal,ibdate,tsdate,tstime,date,time,words,image,nl2br';
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
            aValue = UDate2Date(aValue);
            break;
          case 'time':
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


  // ==== rotinas para gerenciar cores

  function suggestRowColor(curRow)
  {
    if ((curRow % 2)==0)
      ret='#F2F0F0';
    else
      ret='#BFBFBF';

    return ret;
  }

  function decomposeColor(color)
  {
    if (color.substr(0,1)=='#')
      color=color.substr(1);


    var r=hex2dec(color.substr(0,2));
    var g=hex2dec(color.substr(2,2));
    var b=hex2dec(color.substr(4,2));

    return [r, g, b];
  }

  function complementaryColor(color)
  {
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

  function grayColor(color)
  {
    var xDiv=32;

    var dc = decomposeColor(color);

    var r=Math.floor(dc[0] / xDiv) * xDiv;
    var g=Math.floor(dc[1] / xDiv) * xDiv;
    var b=Math.floor(dc[2] / xDiv) * xDiv;

    var gray=(r+g+b) / 3;

    var gray=dec2hex(gray);

    var res=gray+gray+gray;

    return res;
  }

  // ==== rotinas para gerenciar tabelas

  function cleanTable(tableId,aHeaderRowCount)
  {
    if (aHeaderRowCount==undefined)
      aHeaderRowCount=1;
    var tbl = document.getElementById(tableId);

    if (tbl)
      while(tbl.rows.length>aHeaderRowCount)
        tbl.deleteRow(tbl.rows.length-1);
  }

   function addRow(tablename, arr, aHRef, btnFlags, colStart, colCount, aOnSelect)
   {
     if (btnFlags==undefined)
       btnFlags=0;

     if (colStart==undefined)
       colStart=0;

     if (colCount==undefined)
       colCount=-1;

     var tbl = document.getElementById(tablename);
     var lastRow;
     var row;
     if (tbl.tBodies.length>0) {
       lastRow = tbl.tBodies[0].rows.length;
       row = tbl.tBodies[0].insertRow(lastRow);
     } else {
       lastRow = tbl.rows.length;
       row = tbl.insertRow(lastRow);
     }


     var r=0;
     var c=0;
     var curRow=lastRow-1;
     if (curRow<0)
       curRow=0;
     var MyTableName="'"+tablename+"'";
     var cellAlign;

     if ((btnFlags & 1)==1) {
       var cell=row.insertCell(r++);
       var cbName='cb_'+curRow;
       var checkbox = document.createElement('input');
       checkbox.type='checkbox';
       checkbox.name=cbName;
       checkbox.id=cbName;
       checkbox.onclick=aOnSelect;
       cell.appendChild(checkbox);

       // cell.innerHTML="<input type=checkbox name='"+cbName+"' id='"+cbName+"'>";
       cell.style.background=suggestRowColor(curRow);
     }

      for (var j in arr) {
        if ((j!='rowid') && (arr.hasOwnProperty(j))) {
          if (c>=colStart) {
            var iHTML='';
            if ((colCount<0) || (c<colStart+colCount)) {
              var cell = row.insertCell(r++);
              cellAlign=isNumber(arr[j])?'right':'left';
              if ((typeof(arr[j])=='string')||(typeof(arr[j])=='number')) {
                if (aHRef) {

                  var auxHRef='';
                  var isJS = (aHRef.substr(0,10)=='javascript') || ((aHRef.indexOf('.')<0) && (aHRef.indexOf('?')<0)) || (aHRef.indexOf('(')>=0);
                  if (isJS) {
                    if (aHRef.substr(0,10)!='javascript')
                      aHRef='javascript:'+aHRef;
                    if (aHRef.indexOf('(')>=0)
                      auxHRef=aHRef.substring(0,aHRef.indexOf('(')+1) + MyTableName +','+ curRow+ ')';
                    else
                      auxHRef=aHRef+'(' + MyTableName +','+ curRow+ ')';
                  } else {
                    if (aHRef.indexOf('?')<0)
                      auxHRef=aHRef+wc_set_context('yapf','choseTableRow',tablename+':'+curRow);

                    auxHRef=aHRef+"&tableName="+tablename+"&curRow="+curRow;
                  }

                  // iHTML='<a href="javascript:'+aHRef+'('+MyTableName+','+curRow+')">';
                  iHTML='<a href="'+auxHRef+'">';
                  iHTML=iHTML+arr[j];
                  iHTML=iHTML+'</a>';
                } else
                  iHTML=arr[j];
              } else {
                cellAlign='right';
                for (var k in arr[j]) {
                  if (arr[j].hasOwnProperty(k)) {
                    if (iHTML>'')
                      iHTML=iHTML+'<br>';
                    iHTML=iHTML+arr[j][k];
                    if (!isNumber(arr[j][k]))
                      cellAlign='left';
                  }
                }
              }

              cell.setAttribute('align', cellAlign);
              cell.innerHTML = iHTML;
              cell.style.background=suggestRowColor(curRow);
            }

          }
          c++;
        } else
          curRow++;
      }
      return row;
   }

   function getValue(arr, ndx)
   {
     var myNdx=0;
     var value='';
     for (var j in arr) {
       if (j!='rowid') {
         if (ndx==myNdx) {
           value=arr[j];
           break;
         }
         myNdx++;
       }
     }
     return value;
   }

   function getTableRowNdx(tableId, id)
   {
     var ret=-1;
     var table = document.getElementById(tableId);
     for(var r in table.rows) {
       if (table.rows.hasOwnProperty(r)) {
         if (table.rows[r].id==id)
           ret=r;
       }
     }
     return ret;
   }

   function getTableRowId(tableId, y)
   {
      var table = document.getElementById(tableId);
      var row = table.rows[y];
      return row.id;
   }

   function getTableCellText(tableId, x, y)
   {
      var table = document.getElementById(tableId);
      var row = table.rows[y];
      var cell = row.cells[x];

      // window.alert(row.cells[x].textContent);
      if(document.all)
        var cellText=cell.innerText ;
      else
        var cellText=cell.textContent;

      return cellText;
   }

  function _do_closeForm(tagObj)
  {
    /*
    inc=2;
    lh=parseInt(tagObj.style.height)+inc;
    lw=parseInt(tagObj.style.width)+inc;
    while ((parseInt(tagObj.style.height)!=lh) && (parseInt(tagObj.style.width)!=lw)) {
      lh=parseInt(tagObj.style.height);
      lw=parseInt(tagObj.style.width);
      tagObj.style.height=parseInt(tagObj.style.height)-inc;
      tagObj.style.width=parseInt(tagObj.style.width)-inc;
      inc+=2;
    }
    */
    tagObj.style.display='none';
  }

  function closeForm(tag)
  {
    tagObj=document.getElementById(tag);
    if (tagObj)
      _do_closeForm(tagObj);
  }

  function closeParentForm(tag)
  {
    tagObj=parent.document.getElementById(tag);
    if (tagObj)
      _do_closeForm(tagObj);
  }


  // ==== rotinas para gerenciar frames
  /*
   * definimos o macro $frame() que devolve o frame associado com o path indicado
   *
   * por exemplo
   *   $('/') -> body principal
   */

  function $frame(frameName)
  {
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

  function __searchFrameByName(aRootFrame, frameName)
  {
    var aFrame=null;
    if (aRootFrame.frames) {
      for (var n=0; (aFrame==null) && (n<aRootFrame.frames.length); n++)
        if (aRootFrame.frames[n].name==frameName)
          aFrame=aRootFrame.frames[n];
        else
          aFrame=__searchFrameByName(aRootFrame.frames[n], frameName)
    }

    return aFrame;
  }

  function $frameByName(frameName)
  {
    return __searchFrameByName(top, frameName);
  }

  function __searchFunctionInFrame(aName, aType, f)
  {
    var ret=undefined;
    var aux="typeof f."+aName+"=='"+aType+"'";

    /*
    if (aType=='object')
      alert("typeof f."+aName+'=='+aType+'?\n'+eval(aux)+'\n'+eval("typeof f."+aName)+'\n'+f.name);
    */

    if (eval(aux)) {
      ret=f;
    } else {
      var n=0;
      if (f.frames)
        while ((n<f.frames.length) && (ret==undefined))
          ret = __searchFunctionInFrame(aName, aType, f.frames[n++]);
    }

    return ret;
  }

  function $frameOwner(aName, aType)
  {
    // alert("looking for "+aName+" as "+aType);

    var f=$frame('/');

    return __searchFunctionInFrame(aName, aType, f);
  }

  var _ymainBody;

  function __getMainBody()
  {
    if (_ymainBody==undefined) {
      var mainBody = $frame('/mainBody');
      if (!mainBody)
        mainBody=window;

      _ymainBody=mainBody;
    }
    return _ymainBody
  }

  function __debugMouseOver(e)
  {
    var mainBody=__getMainBody();
    var debug = mainBody.$('debug');
    setOpacity(debug,10);
  }

  function __debugMouseOut(e)
  {
    var mainBody=__getMainBody();
    var debug = mainBody.$('debug');
    setOpacity(debug,60);
  }

  /*
   * bridge:  webApp frame to dataset frame
   */

  var dsFrame = $frameOwner('__dsOpen', 'function');
  var dsEventDelayCount = 0;
  var dsEventContext = new Array();

  function dsOpen(dsName, sqlID, frameOwner)
  {
    if (dsName==undefined) {
      dsName=dsEventContext['dsName'];
      sqlID=dsEventContext['sqlID'];
      frameOwner=dsEventContext['frameOwner'];
    }
    _dump(dsName, sqlID);
    if (!dsFrame)
      dsFrame = $frameOwner('__dsOpen', 'function');

    if (dsFrame) {
      if (typeof dsFrame.__dsOpen == 'function')
        dsFrame.__dsOpen(dsName, sqlID, frameOwner);
    } else {
      dsEventContext['dsName']=dsName;
      dsEventContext['sqlID']=sqlID;
      dsEventContext['frameOwner']=frameOwner;

      dsEventDelayCount++;
      if (dsEventDelayCount==11) {
        alert("Delaying dataset load event more than 10 times\nContact your system administrator");
        dsEventDelayCount=0;
      }
      setTimeout("dsOpen()", 750);
    }
  }

  function dsRefresh(dsName)
  {
    dsFrame.__dsRefresh(dsName);
  }


  /**************************************************
   * ask routines
   * Purpose: Ask a set of values to the user and call
   * a url on submit button.
  ***************************************************/
  var aFloatDiv=false;
  var aFloatForm=false;

  function keyEvent(e, btnOk, btnCancel)
  {
    if (!e)
      var e = window.event;

    var key;
    if (window.event)
      key = window.event.keyCode; //IE
    else
      key = e.which; //firefox

    if (key == 13)
      $(btnOk).click();
    else if ((key == 0) || (key == 27))
      $(btnCancel).click();

    return (key != 13);
  }

  function disableEnterKey( e )
  {
    if (!e)
      var e = window.event;

     var key;
     if (window.event)
      key = window.event.keyCode; //IE
     else
      key = e.which; //firefox

     return (key != 13);
  }

  function askValue(aFrameName, aURL, aFieldList)
  {
    var aDocument=$frame(aFrameName).document;

    if (aFloatDiv==false) {
      aFloatDiv=aDocument.createElement('div');
      aFloatDiv.id='askFloatDiv';
      aDocument.body.appendChild(aFloatDiv);
      aFloatDiv.className='ask';
      with (aFloatDiv.style) {
        position='absolute';
        zIndex=100;
      }
    }
    if (aDocument.getElementById('askFloatDiv')==undefined)
      aDocument.body.appendChild(aFloatDiv);

    if (aFloatForm==false) {
      aFloatForm=aDocument.createElement('div');
      aFloatForm.id='askFloatForm';
      aFloatForm.className='askForm';
      with (aFloatForm.style) {
        position='absolute';
        zIndex=100;
      }
    }
    if (aDocument.getElementById('askFloatForm')==undefined)
      aDocument.body.appendChild(aFloatForm);

    aFloatDiv.style.width=aDocument.body.scrollWidth+'px';
    aFloatDiv.style.height=aDocument.body.scrollHeight+'px';
    aFloatDiv.innerHTML="";
    aFloatDiv.style.display='block';

    var _position={x: curX, y: curY};
    var _onChangesFuncName='';

    with (aFloatForm) {
      var firstElement, aValue;
      var inHTML ="<form onsubmit='return=false;' id=fAskValue>";
      for(var k in aFieldList) {
        if (aFieldList.hasOwnProperty(k)) {
          if (k=='_position') {
            _position = aFieldList[k].value;
            _onChangesFuncName = aFieldList[k].onChangesFuncName;
          }
        }
      }

      if (_onChangesFuncName)
        _onChangesFuncName=" onchange='javascript:"+_onChangesFuncName+"(this);'";

      for(var k in aFieldList) {
        if (aFieldList.hasOwnProperty(k)) {
          if (firstElement==undefined)
            if (aFieldList[k]['type']!='hidden')
              firstElement=aFieldList[k]['name'];
          aValue=aFieldList[k]['value'];

          if (aFieldList[k]['title']!=undefined)
            if (aFieldList[k]['type']!='hidden')
              inHTML+="  <div class=fieldTitle>"+aFieldList[k]['title']+"</div>";
          if (aFieldList[k]['type']=='text')
            inHTML+="  <div class=fieldValue><textarea id="+aFieldList[k]['name']+" name="+aFieldList[k]['name']+" rows=4 cols="+aFieldList[k]['width']+_onChangesFuncName+">"+aValue+"</textarea></div>";
          else if (aFieldList[k]['type']=='hidden')
            inHTML+="  <input type=hidden id="+aFieldList[k]['name']+" name="+aFieldList[k]['name']+" value='"+aValue+"'>";
          else
            inHTML+="  <div class=fieldValue><input type=text id="+aFieldList[k]['name']+" name="+aFieldList[k]['name']+" onkeydown='return keyEvent(event, btnAskSalvar, btnAskCancelar)' value='"+aValue+"'"+_onChangesFuncName+"></div>";
        }
      }
      inHTML+="<div style='align:right'>";
      inHTML+="<input type=button id='btnAskCancelar' value='Cancelar' onclick=askClose()>";
      inHTML+="<input type=button id='btnAskSalvar' value='Lançar' onclick=askSubmit('"+aFrameName+"','"+aURL+"')>";
      inHTML+="</div>";
      inHTML+="</form>";
      innerHTML = inHTML;
    }

    aFloatForm.style.left=_position.x-40+'px';
    aFloatForm.style.top=_position.y-20+'px';
    aFloatForm.style.display='block';
    if ((firstElement) && ($(firstElement))) {
      $(firstElement).focus();
      $(firstElement).select();
    }
  }

  function askClose()
  {
    $('askFloatForm').style.display='none';
    $('askFloatDiv').style.display='none';
  }

  function askSubmit(aFrameName, aURL)
  {
    var botoes=$('fAskValue').elements;
    var isJS = (aURL.substr(0,10)=='javascript') || ((aURL.indexOf('.')==0) && (aURL.indexOf('?')==0));
    var jsParams = '';
    for(var b in botoes) {
      if (onlyDefinedValue(botoes[b].id)>'') {
        if (isJS) {
          if (jsParams>'')
            jsParams+=', ';
          jsParams+=botoes[b].id+':"'+onlyDefinedValue(botoes[b].value)+'"';
        } else
          aURL+='&'+botoes[b].id+'='+onlyDefinedValue(botoes[b].value);
      }
    }

    askClose();

    if (isJS) {
      jsParams='{ '+jsParams+' }';
      aURL=aURL.substr(11);
      if (aURL.substr(aURL.length-2,2)=='()')
        aURL=aURL.substr(0,aURL.length-1)+jsParams+');';
      else
        aURL=aURL.substr(0,aURL.length-1)+', '+jsParams+');';
      aURL='<script>'+aURL+'<'+'/script>';
      aURL.evalScripts();
    } else
      $frame(aFrameName).document.location=aURL;

    return false;
  }

  /**************************************************************
   * WebSocket support routines
   **************************************************************/

  function webSocketEnabled()
  {
    return (window.WebSocket || window.MozWebSocket);
  }
