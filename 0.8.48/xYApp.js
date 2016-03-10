
/*
 * xYApp.js
 * YeAPF 0.8.48-6 built on 2016-03-09 09:16 (-3 DST)
 * Copyright (C) 2004-2016 Esteban Daniel Dortta - dortta@yahoo.com
 * 2016-01-23 22:00:19 (-3 DST)
 *
 * Yet Another PHP Framework
 * Written by Esteban Daniel Dortta in order to help common PHP/JS programmer
 *
 * This file is the startup code on client side
 * It's sole propose is to define globals variables and library keeping track of the user context
*/


  if (u==undefined) {
    var u='#(u)';
    var sa='#(s).#(a)';
    console.log('YeAPF 0.8.48-6 built on 2016-03-09 09:16 (-3 DST) '+sa);
    /*
     * formID está vazio até a primeira chamada a PeekMessage
     */
    var formID='';
    var isDataset=#int(isDataset);
    var isMenu=#int(isMenu);
    var isMessageProcessor=#int(isMessageProcessor);
    var isTablet=#int(isTablet);
    var iPhone=#int(iPhone);
    var iPad=#int(iPad);
    var iMaemo=#int(iMaemo);
    var iAndroid=#int(iAndroid);
    var jsDumpEnabled=#int(jsDumpEnabled);
    var aux='#(sysDate)';
    var sysDate=new Array();
    var yeapfVersion='0.8.48';
    sysDate['year']=aux.substr(0,4);
    sysDate['month']=aux.substr(4,2);
    sysDate['day']=aux.substr(6,2);

    var _CurrentFileName='#(_CurrentFileName)';
    var messagePeekerInterval = Math.max(1250,#int(messagePeekerInterval));
    var messagePeekerTimer;

    var _buildFormPath='#bestName(buildForm.php,2)';
    var _btnClose="#bestName('btn-close.gif')";

    var _requiredFields="#(_requiredFields)";
    var lastError="#nl(lastError)";
    var lastAction="#nl(lastAction)";
    if (lastError>'')
      lastError="**************\n"+lastError+"\n**************";


    var _notLoadedLibraries='';

    function loadLibrary(jsFileName, libFileName)
    {
      if (jsFileName>'') {
        try {
          document.write('<s'+ 'cript type="text/javascript" src="'+ jsFileName +'"></s'+ 'cript>');
          console.log(libFileName+' loaded');
        } catch(e) {
          var _script = document.createElement('script');
          _script.type='text/javascript';
          _script.src=jsFileName;
          document.getElementsByTagName('head')[0].appendChild(script);
          console.log(libFileName+' added');
        }
      } else {
        console.log(libFileName+' could not be loaded');
        _notLoadedLibraries+='\n\t';
        _notLoadedLibraries+=libFileName;
      }
    }

    loadLibrary("#versionedName('prototype.js')", 'prototype.js');
    loadLibrary("#versionedName('scriptaculous.js')", 'scriptaculous.js');
    loadLibrary("#versionedName('parser.js')", 'parser.js');
    loadLibrary("#versionedName('raphael.js')", 'raphael.js');
    loadLibrary("#versionedName('grafico.base.js')", 'grafico.base.js');
    loadLibrary("#versionedName('grafico.line.js')", 'grafico.line.js');

    loadLibrary("#versionedName('yeapf.js')", 'yeapf.js');
    /*
     * preparação para o jeito em que o 0.9 fará a carga
     */
    loadLibrary("#versionedName('ymisc.js')", 'ymisc.js');
    loadLibrary("#versionedName('ydebug.js')", 'ydebug.js');
    loadLibrary("#versionedName('ycfgdb.js')", 'ycfgdb.js');
    loadLibrary("#versionedName('ydragdrop.js')", 'ydragdrop.js');
    loadLibrary("#versionedName('ytabnav.js')", 'ytabnav.js');
    loadLibrary("#versionedName('ycomm.js')", 'ycomm.js');
    loadLibrary("#versionedName('ycomm-ajax.js')", 'ycomm-ajax.js');
    loadLibrary("#versionedName('ycomm-rest.js')", 'ycomm-rest.js');
    loadLibrary("#versionedName('ycomm-dom.js')", 'ycomm-dom.js');
    loadLibrary("#versionedName('ycalendar.js')", 'ycalendar.js');
    loadLibrary("#versionedName('ydyntable.js')", 'ydyntable.js');
    /*
     * continuação do jeito anterior ao 0.9
     */

    loadLibrary("#versionedName('xForms.js')", 'xForms.js');
    loadLibrary("#versionedName('interface.js')", 'interface.js');
    loadLibrary("#versionedName('xColors.js')", 'xColors.js');

    loadLibrary("#versionedName('xml2array.js')", 'xml2array.js');
    loadLibrary("#versionedName('json_sans_eval.js')", 'json_sans_eval.js');

    if (isDataset)
      loadLibrary("#versionedName('yDataset.js')", 'yDataset.js');

    if (isMenu) {
      loadLibrary('#versionedName("tree.js")', 'tree.js');
      loadLibrary('#versionedName("tree_tpl.js")', 'tree_tpl.js');
    }

    // loadLibrary("#versionedName('freedom.js')");

    if (_notLoadedLibraries>'')
      window.alert('YeAPF loader fails\nAt least one library could not be loaded\n'+_notLoadedLibraries);

    // dif com o timestamp do servidor;
    var auxTS=new Date();
    var diffTimeStamp=Math.floor(auxTS.getTime() / 1000) - #(sysTimeStamp);

    // as rotinas ainda não estão carregadas
    // e não podemos usar um evento onload
    // então adiamos o processamento de mensagens até um tempo que achamos prudente, 2segundos
    if (isMessageProcessor) {
      setTimeout('_grantMsgProc(250)',2000);
      // messagePeekerTimer = setTimeout('_peekMessages()',2000);
    }
  }

