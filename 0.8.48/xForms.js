/*
    xForms.js
    YeAPF 0.8.48-6 built on 2016-03-09 09:16 (-3 DST)
    Copyright (C) 2004-2016 Esteban Daniel Dortta - dortta@yahoo.com
    2016-01-23 22:00:19 (-3 DST)
*/
  /*
   *
   * requer prototype.js para funcionar
   */


  function getFieldValue(fieldName)
  {
    var field=null;
    if (fieldName>'') {
      field=document.getElementById(fieldName);
      if (field)
        field=field.value;
    }
    return field;
  }

  function do_submit(f,a,way,scriptName)
  {
    if (f>'') {
      o=document.getElementById(f);
      if (o) {
        if (o.subjectAction)
          o.subjectAction.value=a;
        // window.alert(way+' ('+f+')');
        if ((way=='submit_form') || (way==undefined)) {
          if (o.action=='')
            o.action=scriptName;
          document.forms[f].submit();
        } else {
          var elems = o.elements;
          if (elems) {
            _return_div_=getFieldValue('_return_div_');
            var genLocation = (_return_div_ == undefined);
            if (genLocation) {
              var sepChar='=';
              var itemSeparator='&';
            } else {
              var sepChar=':';
              var itemSeparator=',';
            }

            var params='';
            var canAdd=false;
            var auxValue='';
            var auxParam='';
            var reservedID=':u:s:a:';
            for(var i = 0; i < elems.length; i++) {
              canAdd=false;
              var valueToAdd=null;
              if ((elems[i].type=='checkbox') || (elems[i].type=='radio')) {
                if (elems[i].checked) {
                  valueToAdd=elems[i].value;
                  canAdd=true;
                }
              } else if ((elems[i].type=='password') || (elems[i].type=='hidden') || (elems[i].type=='text') || (elems[i].type=='select-one') || (elems[i].type=='textarea')) {
                valueToAdd=elems[i].value;
                canAdd=true;
              } else if (elems[i].type!='submit') {
                console.log("Don't know how to send and '"+elems[i].type+"' input type");
                canAdd=false;
              }

              if (canAdd) {
                auxValue=elems[i].value;
                var auxID;
                if (auxParam=elems[i].name>'')
                  auxID=elems[i].name;
                else
                  auxID=elems[i].id;

                if (reservedID.indexOf(':'+auxID+':')==-1) {
                  auxParam=auxID+sepChar+auxValue;

                  if ((auxValue.indexOf(',')>0) || (auxValue.indexOf('&')>0))
                    auxParam='"'+auxParam+'"';

                  if (params>'')
                    params+=itemSeparator;

                  params=params+auxParam;
                }

              }
            }
            _id_fieldname=getFieldValue('_id_fieldname');
            id=getFieldValue(_id_fieldname);
            s=getFieldValue('s');
            u=getFieldValue('u');
            if (scriptName>'')
              url=scriptName;
            else
              url=o.action;

            if (_return_div_==null) {
              document.location=url+'?'+qsArgs("s,a,u")+'&'+params;
            } else {
              buildForm(_return_div_,url,u,s,a,id,params);
            }

          } else
            window.alert("There is no elements in '"+f+"' form");

        }
      } else {
        alert('Formulário '+f+' não localizado');
      }
/*
     } else {
      alert("Falta indicar um formulario para enviar os dados");
      exit;
*/
    }
  }

  function do_close()
  {
    var id=new String('#campo(id)');
    var rnd=Math.random()*1000;
    var s=new String(window.opener.location);
    rnd=Math.round(rnd);

    var p=s.search('rnd=');
    if (p>0)
      s=s.slice(0,p-1);

    p=s.search('#');
    if (p>0)
      s=s.slice(0,p);

    s=s+'&rnd='+rnd;
    s=s+'#'+id;

    window.opener.location.replace(s);
    window.close();
  }

  // rotinas genericas para comunicar-se com o query.php
  function getX( oElement )
  {
    var iReturnValue = 0;
    while( oElement != null ) {
      iReturnValue += oElement.offsetLeft;
      oElement = oElement.offsetParent;
    }
    return iReturnValue;
  }

  function getY( oElement )
  {
    var iReturnValue = 0;
    while( oElement != null ) {
      iReturnValue += oElement.offsetTop;
      oElement = oElement.offsetParent;
    }
    return iReturnValue;
  }

  function maxWindow() {
      window.moveTo(0, 0);


      if (document.all) {
          top.window.resizeTo(screen.availWidth, screen.availHeight);
      }

      else if (document.layers || document.getElementById) {
          if (top.window.outerHeight < screen.availHeight || top.window.outerWidth < screen.availWidth) {
              top.window.outerHeight = screen.availHeight;
              top.window.outerWidth = screen.availWidth;
          }
      }
  }

  // DOM Objects

  function hideElement(aElementName)
  {
    if ($(aElementName))
      $(aElementName).style.display='none';
  }

  function showElement(aElementName)
  {
    if ($(aElementName))
      $(aElementName).style.display='block';
  }

  // chose an item into a select objet from a string
  function selectItem(aSelectObject, aValue)
  {
    for(var i=0; i<aSelectObject.options.length; i++)
      if (aSelectObject.options[i].value==aValue)
        aSelectObject.options[i].selected=true;
  }


  function grantElement(aId, aDOMName)
  {
    auxDiv=document.getElementById(aId);
    if (auxDiv==null) {
      auxDiv=document.createElement(aDOMName);
      auxDiv.id=aId;
      document.body.appendChild(auxDiv);
    }

    return auxDiv;
  }

  var waitIconVisible=0;

  function showWaitIcon(topLeftElement, bottomRightElement)
  {
    var asFullScreen=false;
    var waitIcon=document.getElementById('waitIcon');
    if (!waitIcon) {
      waitIcon=document.createElement('div');
      waitIcon.id='waitIcon';
      waitIcon.style.position='absolute';
      waitIcon.style.padding='24px';
      waitIcon.innerHTML="<div style='font-size:16px;color:black;height:46px'><div style='float:left;height:100%;margin-right:8px'><img src='images/waitIcon.gif' style='max-height:18px' border=0></div><div style='float:left;height:100%'><b>Aguarde...</b><br><small>Carregando informação</small></div></div>";
      waitIcon.style.background='#fafafa';
      waitIcon.style.display='none';
      if (document.body)
        document.body.appendChild(waitIcon);
    }
    if (waitIcon) {
      waitIcon.style.visibility='visible';
      if (bottomRightElement==undefined)
        bottomRightElement=topLeftElement;

      if ((topLeftElement>'') && (bottomRightElement>'')) {
        var tl=document.getElementById(topLeftElement);
        var br=document.getElementById(bottomRightElement);
        if ((tl) && (br)) {
          var x=getX(tl)-8;
          var y=getY(tl)-16;
          var w=Math.max(120, Math.max(getX(br)-x+br.offsetWidth, 240));
          var h=Math.max( 80, Math.max(getY(br)-y+br.offsetHeight, 80));

          waitIcon.style.left=x+'px';
          waitIcon.style.top=y+'px';
          waitIcon.style.width=w+'px';
          waitIcon.style.height=h+'px';
        } else
          asFullScreen=true;
      } else
        asFullScreen=true;

      if (asFullScreen) {
        with (waitIcon) {
          style.left='0px';
          style.top='0px';
          var aux=getClientSize()
          style.width=aux[0]+'px';
          style.height=aux[1]+'px';
        }
      }
      setOpacity(waitIcon,90);
      waitIcon.style.zIndex=100;
      waitIconVisible=Math.max(0,waitIconVisible+1);

      if (waitIconVisible>0)
        if (waitIcon)
          waitIcon.style.display='block';


    }
  }

  function hideWaitIcon(aForce)
  {
    if (aForce==undefined)
      aForce=false;
    var waitIcon=document.getElementById('waitIcon');
    if (waitIcon) {
      if (waitIconVisible>0)
        waitIconVisible--;
      if ((waitIconVisible==0) || (aForce))
        waitIcon.style.display='none';
    }
  }

  var queryRunning=false;
  var xData;
  var xDataContext;
  var xUserMsg;
  var dRowCount;
  var curQuery;

  function zeroPad(num,count)
  {
    var numZeropad = num + '';
    while(numZeropad.length < count)
      numZeropad = "0" + numZeropad;

    return numZeropad;
  }

  function cleanFormElements(aFormID)
  {
    var aForm=document.getElementById(aFormID);
    if (aForm) {
      for (var i=0; i<aForm.elements.length; i++)
      {
        var aElement=aForm.elements[i];
        if ((aElement.type=='text') || (aElement.type=='textarea') || (aElement.type=='select-one'))
          aElement.value='';
      }
    }
  }

  function disableFormElements(aFormID, aDisableState)
  {
    if (aDisableState==undefined)
      aDisableState=true;
    var aForm=document.getElementById(aFormID);
    if (aForm) {
      for (var i=0; i<aForm.elements.length; i++)
      {
        var aElement=aForm.elements[i];
        if ((aElement.type=='text') || (aElement.type=='textarea') || (aElement.type=='select-one') ||
            (aElement.type=='checkbox') || (aElement.type=='radio'))
          aElement.disabled=aDisableState;
      }
    }
  }

  function _QUERY_FILL_TABLE(tableName, onClickEvent, colStart, colCount, btnFlags, aFieldIdName, auxData)
  {
    if (auxData==undefined)
      auxData=xData;

    // window.alert('FillTable');
    var oTable = document.getElementById(tableName);
    if (oTable) {
      var aContainer=oTable.parentNode;
      if (aContainer) {
        var waitIcon=document.getElementById('waitIcon');
        aContainer.style.position='absolute';
        if (waitIcon) {
          aContainer.style.top=waitIcon.style.top;
          aContainer.style.left=waitIcon.style.left;
        }
        aContainer.style.display='block';
      }

      while(oTable.rows.length>1)
        oTable.deleteRow(oTable.rows.length-1);

      for (var j in auxData) {
        var newRow=addRow(tableName, auxData[j],'javascript:'+onClickEvent+'()', btnFlags, colStart, colCount);
        if (aFieldIdName!=undefined) {
          if (auxData[j][aFieldIdName]!=undefined) {
            newRow.id=auxData[j][aFieldIdName];
          }
        }
      }
    }
  }

  function array2text(aArray, widthKeys, sep)
  {
    if (aArray != undefined) {
      if (sep==undefined)
        sep='\n';
      ret = '';
      for (var k in aArray) {
        if (ret>'')
          ret+=sep;
        if (widthKeys)
          ret+=k+': ';
        if (aArray[k] instanceof Object) {
          // ret+='\n  ';
          ret+=array2text(aArray[k], widthKeys,',  ');
        } else
          ret+=aArray[k];
      }
    }
    if (ret.substr(ret.length-1)=='\n')
      ret=ret.subsrt(0,ret.length-1);
    return ret;
  }

  function processQueryReturn(r, aDataContext, aDataCache, aGeometry)
  {
    if (r.status==200) {
      if (r.responseXML!=null) {
        var xmlArray=xml2array(r.responseXML);
        var xError=xmlArray['error'];
        var xRoot=xmlArray['root'];
        if (xError==undefined) {
          if (xRoot['dataContext']!=undefined)
            xError=xRoot['dataContext']['error'];
        }
        var xCallBackFunction;

        if (xError) {
          var errNo=xError['errNo'];
          var errMsg=xError['errMsg'];
          var errDetail=xError['errDetail'];
          if (typeof errDetail != 'String') {
            var d1=array2text(errDetail['sys.stack'],false);
            if (d1 != undefined)
              d1='\n==[stack]===================================\n'+d1;
            var d2=errDetail['sys.sqlTrace'];
            if (d2 != undefined)
              d2='\n==[sql]===================================\n'+d2;
            var d3=errDetail['sys.sqlError'];
            errDetail=d3+d2+d1;
          }

          window.alert('Err #'+errNo+'\n-------- '+errMsg+'\n-------- '+errDetail);
        } else if (xRoot) {
            xDataContext=xmlArray['root']['dataContext'];
            xCallBackFunction=xmlArray['root']['callBackFunction'];

            xUserMsg='';

            if (xDataContext['userMsg'])
              xUserMsg=xDataContext['userMsg'];

            if (xDataContext['formID']!=undefined) {
              if (formID=='') {
                formID=xDataContext['formID'];
                // alert("FORMID: "+formID);
              }
            }

            dRowCount=parseInt(xDataContext['rowCount']);
            if (xRoot['data'])
              xData=xRoot['data']['row'];
            else
              xData=xRoot['row'];

            if (dRowCount==1) {
              xData=new Array(xData);
            }


            if (aGeometry != undefined)
              aGeometry.value = xRoot['data']['geometry'];

        }

        if (aDataContext != undefined)
          aDataContext.value = xDataContext;

        if (aDataCache != undefined)
          aDataCache.value = xData;

        return xCallBackFunction;
      } else
        return null;
    }
  }

  function _QUERY_RETURN(r)
  {
    _notifyServerOnline();

    queryRunning=false;
    var xCallBackFunction = processQueryReturn(r);
    // _dump(r.status+' - '+xCallBackFunction+'()');
    if (xCallBackFunction!='_MessageFeedBack_')
      hideWaitIcon();


    if (xCallBackFunction!='undefined') {
      var auxCallFunction="<script>"+xCallBackFunction+'();</'+'script>';
      auxCallFunction.evalScripts();
    }
  }

  function _BUILD_ARRAY_PARAMETER(aArray)
  {
    var aux='';
    if (aArray!=undefined) {
      for(var n=0; n<aArray.length; n++) {
        if (aux>'')
          aux+=',';
        aux+=aArray[n];
      }
    }
    return '('+aux+')';
  }

  function onlyDefinedValue(aValue)
  {
    return (aValue || '');
  }

  function packedArgs()
  {
    var ret='';

    for(var i=0; i<arguments.length; i++) {
      if (i>0)
        ret+=',';
      ret+=arguments[i];
    }
    return '('+ret+')';
  }

  function packedArgValues()
  {
    var ret='';

    for(var i=0; i<arguments.length; i++) {
      if (i>0)
        ret+=',';
      var aux=$(arguments[i]);
      if (aux)
        aux=aux.value;
      else
        aux='';
      ret+=aux;
    }
    return '('+ret+')';
  }

  function qsArgs()
  {
    var ret='';
    var args = new Array();
    var argNdx=0;
    for (var i=0; i<arguments.length; i++) {
      var aux = arguments[i].split(',');
      for(var n=0; n<aux.length; n++)
        args[argNdx++]=aux[n];
    }

    for(var i=0; i<args.length; i++) {
      if (i>0)
        ret+='&';
      ret+=args[i];
      ret+='=';
      var aValue = window[args[i]];
      if (aValue==undefined) {
        aValue=$(args[i]);
      }
      if (aValue)
        if (aValue.value)
          aValue=aValue.value;

      ret+=onlyDefinedValue(aValue);
    }
    return ret;
  }

  function yQueryEvent(queryS, queryA, callBackFunction, fieldName, fieldValue)
  {
    if (fieldValue==undefined)
      fieldValue='';
    if (fieldName==undefined)
      fieldName='';

    showWaitIcon();

    var ts=new Date();
    var auxParameters='s='+queryS+'&u='+u+'&a='+queryA+'&fieldName='+
                      fieldName+'&fieldValue='+fieldValue+'&ts='+
                      ts.getTime()+'&callBackFunction='+callBackFunction;
    // window.alert(auxParameters);
    if (!queryRunning) {
      queryRunning=true;

      curQuery=auxParameters;

      var aux=new Ajax.Request(
        'query.php',
        {
          method: 'get',
          asynchronous: false,
          parameters: auxParameters,
          onComplete: _QUERY_RETURN
        }
      );
    } else
      window.alert("Consulta em andamento, por favor aguarde\nImpossível fazer: query.php"+auxParameters+"\n\nA seguinte consulta está em andamento: "+curQuery);
  }

  /* Llama XMLHttpRequest.  provoca al query.php
   * En la ausencia de algunos parámetros el se comporta asi:
   * _DO(queryS, queryA)
   */
  function _QUERY_URL(aURL, aCallBackFunctionName)
  {

    if (!queryRunning) {
      queryRunning=true;
      showWaitIcon();

      var ts=new Date();

      aURL+='&ts='+ts.getTime()+'&callBackFunction='+aCallBackFunctionName;
      aURL=aURL.replace('%','%25');

      curQuery=aURL;
      var aux=new Ajax.Request(
        'query.php',
        {
          method: 'get',
          asynchronous: false,
          parameters: aURL,
          onComplete: _QUERY_RETURN
        }
      );
    } else
      window.alert("Consulta em andamento, por favor aguarde\nImpossível fazer: query.php"+auxParameters+"\n\nA seguinte consulta está em andamento: "+curQuery);
  }


  function _DO(queryS, queryA, fieldName, fieldValue, callBackFunctionName)
  {
    if (callBackFunctionName==undefined)
      callBackFunctionName='undefined';
    if (fieldValue==undefined)
      fieldValue='';
    if (fieldName==undefined)
      fieldName='';

    var auxParameters='s='+queryS+'&u='+u+'&a='+queryA+'&fieldName='+
                      fieldName+'&fieldValue='+fieldValue;
    _QUERY_URL(auxParameters, callBackFunctionName);
    /*
    if (!queryRunning) {
      queryRunning=true;

      curQuery=auxParameters;

      var aux=new Ajax.Request(
        'query.php',
        {
          method: 'get',
          asynchronous: false,
          parameters: auxParameters,
          onComplete: _QUERY_RETURN
        }
      );
    } else
      window.alert("Consulta em andamento, por favor aguarde\nImpossível fazer: query.php"+auxParameters+"\n\nA seguinte consulta está em andamento: "+curQuery);
    */
  }

  function _QUERY(queryS, queryA, fieldNameOrList, callBackFunctionName)
  {
    var fieldList=unparentesis(fieldNameOrList).split(',');
    var fieldValues='';
    for(var i=0; i<fieldList.length; i++) {
      if (i>0)
        fieldValues+=',';
      var aux=$(fieldList[i]);
      if (aux)
        aux=aux.value;
      else
        aux='';
      fieldValues+=aux;
    }

    // var fieldValue=document.getElementById(fieldNameOrList);

    if (fieldValues>'') {
      // fieldValue=fieldValue.value;
      fieldValues='('+fieldValues+')';
      _DO(queryS, queryA, fieldNameOrList, fieldValues, callBackFunctionName);
    } else {
      _DO(queryS, queryA, '*', '', callBackFunctionName);
    }
  }

  /*
   * These routines were written in order to help
   * interprocess process messages
   * In Windows(TM) and Linux you would send a message to an application
   * meanwhile, with YeAPF you will send messages to connected users.
   * As this was not inteded to send chat messages, is correct to
   * send messages to and only to connected users.
   *
   */

  var messageStack=new Array();
  var msgProcs=new Array();
  var _dbgFlag_noMessageProcessorPresent=false;
  var msgCount=0;
  var serverOfflineFlag=0;

  function _MessageFeedBack_()
  {
    if (dRowCount>0) {
      msgCount++;
      for (var j in xData) {
        if (!isNaN(parseInt(j))) {
          var aux=xData[j];

          messageStack.push(new Array(aux['sourceUserId'],
                                      aux['message'],
                                      aux['wParam'],
                                      aux['lParam'])
                           );
        }
      }

      if (messageStack.length>0) {
        if (msgProcs.length==0) {
          if (!_dbgFlag_noMessageProcessorPresent)
            if (jsDumpEnabled)
              window.alert("Messages arriving at  '"+_CurrentFileName+"'  but there is not\na registered message processor in order to receive it.\nUse _registerMsgProc() to register it");
          _dbgFlag_noMessageProcessorPresent=true;
        } else {
          while (messageStack.length>0) {
            var oldLen=messageStack.length;
            for (var i=0; i<msgProcs.length; i++) {
              // _dump("Calling: "+msgProcs[i]);
              var auxCallFunction='<script>'+msgProcs[i]+'();</'+'script>';
              auxCallFunction.evalScripts();
            }
            if (oldLen==messageStack.length)
              messageStack.shift();
          }
        }
      }

    }
    _grantMsgProc(messagePeekerInterval);
  }

  function _peekMessages()
  {
    clearTimeout(messagePeekerTimer);
    messagePeekerTimer=null;

    var ts=new Date();
    var auxParameters='s=y_msg&u='+u+'&a=peekMessage&formID='+formID+'&ts='+
                      ts.getTime()+'&callBackFunction=_MessageFeedBack_&messagePeekerInterval='+messagePeekerInterval;
    var aux=new Ajax.Request(
      'query.php',
      {
        method: 'get',
        asynchronous: true,
        parameters: auxParameters,
        onComplete: function (transport) {
          if (transport.status==200)
            _QUERY_RETURN(transport);
          else {
            _dump("*** XMLHttpRequest call failure");
            setTimeout('_notifyServerOffline()',500);
          }
        }
      }
    );
  }

  function _postMessage(aTargetUserID, aMessage, aWParam, aLParam, aBroadcastCondition)
  {
    var ts=new Date();
    if (aBroadcastCondition!=undefined)
      var aux='&targetUser=*&broadcastCondition="'+aBroadcastCondition+'"';
    else
      var aux='&broadcastCondition=&targetUser='+aTargetUserID;

    var auxParameters='s=y_msg&u='+u+'&a=postMessage'+aux+'&formID='+formID+
                      '&message='+aMessage+'&wParam='+aWParam+'&lParam='+aLParam+
                      '&ts='+ts.getTime()+'&callBackFunction=_MessageFeedBack_';
    var aux=new Ajax.Request(
      'query.php',
      {
        method: 'get',
        asynchronous: false,
        parameters: auxParameters,
        onComplete: _QUERY_RETURN
      }
    );
  }

  function _cleanMsgProc()
  {
    msgProcs.length=0;
  }

  function _notifyServerOnline()
  {
    if (serverOfflineFlag>0) {
      serverOfflineFlag = 0;
      var mainBody=__getMainBody();
      var isReady = (typeof mainBody.$ == 'function') && (mainBody.document.body != null);
      if (isReady) {
        var notificationArea = mainBody.$('notificationArea');
        if (notificationArea)
          notificationArea.style.display='none';
      }
    }
  }

  function _notifyServerOffline()
  {
    serverOfflineFlag++;
    var mainBody=__getMainBody();
    var isReady = (typeof mainBody.$ == 'function') && (mainBody.document.body != null);
    if (isReady) {
      var notificationArea = mainBody.$('notificationArea');
      if (!notificationArea) {
        notificationArea = mainBody.document.createElement('div');
        notificationArea.id='notificationArea';
        setOpacity(notificationArea,90);
        mainBody.document.body.appendChild(notificationArea);
        if (!existsCSS('notificationArea')) {
          notificationArea.style.zIndex=1000;
          notificationArea.style.position='absolute';
          notificationArea.style.left='0px';
          notificationArea.style.top='0px';
          notificationArea.style.border='1px #900 solid';
          notificationArea.style.backgroundColor='#fefefe';
        } else
          notificationArea.className='notificationArea';
      }

      notificationArea.style.width=mainBody.innerWidth+'px';
      notificationArea.style.height=mainBody.innerHeight+'px';
      notificationArea.style.display='block';

      notificationArea.innerHTML="<div style='padding: 32px'><big><b>Server Offline</b></big><hr>Your server has become offline or is mispeling answers when requested.<br>Wait a few minutes and try again later, or wait while YeAPF try again by itself</div>&nbsp;";
    }

    _grantMsgProc();
  }

  function _grantMsgProc(aInterval)
  {
    // caso venha sem parámtros, calcular um tempo prudente de no máximo 20 segs
    // Isso acontece quando o servidor devolveu uma resposta errada
    // e queremos que o sistema de mensagens continue em operação.
    if ((aInterval==undefined) || (aInterval<=0))
      aInterval = Math.min(20000,messagePeekerInterval * 2);

    if (messagePeekerTimer == undefined) {
      if (msgCount==0)
        _dump("Configuring receivers interval to "+aInterval+'ms');
      messagePeekerTimer = setTimeout('_peekMessages()', aInterval);
    } else
      _dump("Receivers interval already defined");
  }

  function _registerMsgProc(aFunctionName)
  {
    var canAdd=true;
    _dump("Registering message receiver: "+aFunctionName);
    for (var i=0; i<msgProcs.length; i++)
      if (msgProcs[i]==aFunctionName)
        canAdd=false;

    if (canAdd)
      msgProcs[msgProcs.length]=aFunctionName;

    _grantMsgProc(messagePeekerInterval || 5000);
  }

  function _stopMsgProc()
  {
    clearTimeout(messagePeekerTimer);
  }

  /*
   * window control function
   *
   */

  if ((typeof sheetSuported)=="undefined")
    var sheetSuported=false;


  function wc_wait(aURL, details)
  {
    var styleSheet=new String('<style type="text/css"><!--.aviso {  font-family: Georgia, "Times New Roman", Times, serif;  font-size: 16px;  font-style: normal; font-variant: normal; font-weight: normal;  text-transform: none; margin: 16px; padding: 8px; background-color: #DFEEF2;  border: 1px dotted #387589; line-height: 24px;}--></style>');
    details.document.open();
    details.document.write(styleSheet);
    details.document.write('<div class="aviso">Carregando...</div>');
    details.document.write('<p><div align=center>Clique <a href='+aURL+'>aqui</a> para tentar novamente<br>caso tenha esperado muito tempo.</div>');
    details.document.close();
    details.location=aURL;
    details.focus();
  }

  var _serverURL='';

  function wc_set_server(aServerName)
  {
    if (aServerName>'') {
      if (aServerName.indexOf(':')<0)
        _serverURL='http://'+aServerName;
      else
        _serverURL=aServerName;
    } else
      _serverURL='';
  }

  function wc_set_context(s,a,id)
  {
    var ret=_serverURL+'?u='+u;
    if (s!=undefined)
      ret = ret + '&s=' + s;
    if (a!=undefined)
      ret = ret + '&a=' + a;
    if (id!=undefined)
      ret = ret + '&id=' + id;
    return ret;
  }

  function wc_open_sb(aURL, aLeft, aTop, aWidth, aHeight, aTitle)
  {
    var params="left="+aLeft+",top="+aTop+",height="+aHeight+",width="+aWidth+",scrollbars=yes,titlebar=no";

    if (sheetSuported)
      aURL=aURL+'&sheets=1';
    else
      aURL=aURL+'&sheets=0';

    var details=window.open(aURL, '', params);
    //details.resizeTo(aWidth, aHeight);
    wc_wait(aURL, details);
    return details;
  }

  function wc_resize_frame(aFrame) {
    var ifrm = document.getElementById(aFrame);
    if(window.frames[aFrame] && window.frames[aFrame].document) {
      window.frames[aFrame].window.scroll(0,0);
      var body = window.frames[aFrame].document.body;
      if(body) {
        ifrm.style.height = (body.scrollHeight || body.offsetHeight) + 'px';
      }
    }
  }

  // this function is intended to be used with
  // addOnLoadManager()
  function wc_autoresize_iframe(aFrameName)
  {
    var ifrm = document.getElementById(aFrameName);
    ifrm.onload=function() {wc_resize_frame(aFrameName);};
    wc_resize_frame(aFrameName);
  }


  /*
   * this is not mine
   * but I don't remember where I picked it
   *
   */
  var highlightcolor="#ffffae"
  var ns6=document.getElementById&&!document.all
  var previous=''
  var previousColor=''
  var eventobj

  //Regular expression to highlight only form elements
  var intended=/INPUT|TEXTAREA|SELECT|OPTION/

  //Function to check whether element clicked is form element
  function checkel(which)
  {
    if (which.style&&intended.test(which.tagName)){
      if (ns6 && eventobj.nodeType==3)
        eventobj=eventobj.parentNode.parentNode
      return true
    }
    else
      return false
  }

  //Function to highlight form element
  function highlight(e)
  {
    /*
    eventobj=ns6? e.target : event.srcElement
    if (previous!='') {
      if (checkel(previous))
        previous.style.backgroundColor=previousColor
      previous=eventobj
      if (checkel(eventobj)) {
        previousColor=eventobj.style.backgroundColor
        eventobj.style.backgroundColor=highlightcolor
      }
    } else {
      if (checkel(eventobj)) {
        previousColor=eventobj.style.backgroundColor
        eventobj.style.backgroundColor=highlightcolor
      }
      previous=eventobj
    }

    if (e.type=='text')
      e.select();
    */
  }

  function nextField()
  {
    var xNewObj;

    if (previous.type!='textarea') {

      for (var f=0; f<document.forms.length; f++) {
        for (i=0; i<document.forms[f].elements.length; i++)
          if (document.forms[f].elements[i]==previous) {
            var j=(i + 1) % document.forms[f].elements.length;
            xNewObj=document.forms[f].elements[j];
            break;
          }
      }

      if (xNewObj!=undefined)
        xNewObj.focus();
    }
  }

  function capturekey(e)
  {
    var e=(typeof e!='undefined')?window.event:e;
    var key;
    if (e)
      key=e.keyCode;
    if (key==13) {
      nextField();
      void(0);
      e.returnValue=false;
    }

    var viewerExists = (
                        (typeof nav_viewerJump=='function') &&
                        (typeof nav_viewerSkip=='function') &&
                        (typeof nav_viewerClose=='function')
                        );
    if (viewerExists) {
      if (key==37) {
        void(0);
        nav_viewerSkip(-1,0);
      } else if (key==39) {
        void(0);
        nav_viewerSkip(+1,0);
      } else if (key==27) {
        void(0);
        nav_viewerClose();
      } else if (key==38) {
        void(0);
        nav_viewerSkip(0,-1);
      } else if (key==40) {
        void(0);
        nav_viewerSkip(0, +1);
      } else if (key==34) {
        void(0);
        nav_viewerJump(+1);
      } else if (key==33) {
        void(0);
        nav_viewerJump(-1);
      }
    }
    return false;
  }

  var _onLoadMethods = new Array;

  function addOnLoadManager(aFunc)
  {
    var i=_onLoadMethods.length;
    _onLoadMethods[i]=aFunc;
  }

  var $_priorOnLoad = window.onload;

  window.onload=function() {
    for(var i in _onLoadMethods)
      if (_onLoadMethods.hasOwnProperty(i))
        if (_onLoadMethods[i]!=undefined)
          _onLoadMethods[i]();
    if ($_priorOnLoad)
      $_priorOnLoad();
  }

  if (navigator.appName!= "Mozilla")
    document.onkeyup=capturekey;
  else
    document.addEventListener("keyup",capturekey,true);


  addOnLoadManager( function() {
    if ((typeof _requiredFields != 'undefined') && (_requiredFields>'')) {
      var aux = _requiredFields.split(',');
      for(var i=0; i<aux.length; i++) {
        var aElement = $(aux[i]);
        if (aElement) {
          aElement = aElement.parentElement;
          aElement.style.cssText='border-width: 2px; border-color: #FF7456; border-style: solid';
        }
      }
    }

    var auxLastClickFeedback='';
    if (typeof lastError!='undefined')
      auxLastClickFeedback+=onlyDefinedValue(lastError);
    if (typeof lastAction!='undefined')
      auxLastClickFeedback+=onlyDefinedValue(lastAction);

    if (auxLastClickFeedback>'')
      window.alert(auxLastClickFeedback);

  } );
