/*********************************************
 * app-src/js/yapp.js
 * YeAPF 0.8.56-100 built on 2017-05-05 10:47 (-3 DST)
 * Copyright (C) 2004-2017 Esteban Daniel Dortta - dortta@yahoo.com
 * 2016-01-23 22:00:19 (-3 DST)
 * First Version (C) 2015 - esteban daniel dortta - dortta@yahoo.com
 **********************************************************************/

var objYeapfApp = function (aAnimationClassName) {
  var that = {};

  that.sessionList = [];

  that.showWaitIcon = function () {
    if (y$('waitIcon')) {
      y$('waitIcon').deleteClass('animated050');
      y$('waitIcon').deleteClass('fadeOut');
      y$('waitIcon').style.display='block';
    }
  };

  that.hideWaitIcon = function () {
    if (y$('waitIcon')) {
      y$('waitIcon').addClass('animated050');
      y$('waitIcon').addClass('fadeOut');
      //y$('waitIcon').style.display='none';
    }
  };

  /* this function is used by ycomm in order to show/hide the waiticon */
  that.waitIconControl = function (display) {
    if (display!=undefined) {
      if (display)
        that.showWaitIcon();
      else
        that.hideWaitIcon();
    }
  };

  that.setClassForClass = function(aClassTarget, aClassToBeApplied) {
    if ((typeof aClassToBeApplied=='string') && (typeof aClassTarget=='string')) {
      var ClassNames=aClassToBeApplied.split(' ');
      var allTabs=document.getElementsByClassName(aClassTarget);
      for(var i=0; i<allTabs.length; i++) {
        for(var k=0; k<ClassNames.length; k++)
          allTabs[i].addClass(ClassNames[k]);
      }
    }
  }

  that.setTabAnimation = function(aAnimationClassSet) {
    that.setClassForClass('tnTab', aAnimationClassSet);
  }

  that.setContainerAnimation = function(aAnimationClassSet) {
    that.setClassForClass('tnContainer', aAnimationClassSet);
  }

  that.init = function(aAnimationClassName) {
    that.setContainerAnimation(aAnimationClassName);
    return that;
  };

  that.n = function(aTabName) {
    var firstTab = mTabNav.getFirstChildTab(aTabName);
    if (firstTab)
      mTabNav.showTab(firstTab.id);
  }

  return that.init(aAnimationClassName);
}

/* Each section usually has a main tab, an associated event, a table, a form and a view */
var objSection = function () {
  var that = {};
  /* required elements */
  that.cfg = {
    prefixes: ['vw', 'vwTbl', 'vwFrm', 'frm', 'tbl']
  };

  /* offset to load table */
  that.polling = {
    xq_start:0,
    _eof_: false,
    _readyToLoad_: false
  };

  that.openNewDataForm = function () {
    ycomm.dom.cleanForm(that.cfg.mainDataForm);
    mTabNav.showTab(that.cfg.dataFormTab);
  };

  that.closeDataForm = function () {
    mTabNav.showTab(that.cfg.tableTab);
  };

  that.fillDataTable = function (aTableName, aData, aCleanTable) {
    _dump("WARNING: You need to overwrite '{0}.fillDataTable()' function".format(that.objS));
  }

  that.loadTable = function () {
    if (typeof me=='object') {
      var myself=that;
      ycomm.invoke(
        that.objS,
        'loadTable',
        {
          'mainID': me.mainID,
          'xq_start': that.polling.xq_start
        },
        function(status, error, data, userMsg, dataContext) {

          /* perare for next load */
          var rowCount = (dataContext && dataContext.rowCount)?dataContext.rowCount:0;
          var requestedRows = (dataContext && dataContext.requestedRows)?dataContext.requestedRows:0;

          myself.polling.xq_start      += rowCount;
          myself.polling._eof_         = rowCount < requestedRows;
          myself.polling._readyToLoad_ = true;

          /* fill browse table */
          if (true == that.fillDataTable(
                   that.cfg.mainTable,
                   data,
                   myself.polling.xq_start==rowCount ) ) {
            if (!myself.polling._eof_)
              setTimeout( myself.loadTable, 250 );
          }
        }
      );
    }
  };

  that.reloadTable = function () {
    that.polling._readyToLoad_=false;
    that.polling.xq_start=0;
    that.loadTable();
  }

  that.saveDataForm = function () {
    if (typeof me=='object') {
      /* pego os aData do formulario */
      var formulario=ycomm.dom.getFormElements(that.cfg.mainDataForm);
      formulario['mainID']=me.mainID;

      /* chamo meu servidor para salvar os aData */
      ycomm.invoke(
        that.objS,
        'saveDataForm',
        formulario,
        function(status, error, data) {
          that.loadTable();
          that.closeDataForm();
        }
      );
    }
  };

  that.editTableItem = function(aRowId) {
    var id = ycomm.dom.getTableInplaceData(that.cfg.mainTable, aRowId, 'id');
    _dump("Editando '{0}.{1}'".format(that.objS, id));
    that.openNewDataForm();
    ycomm.invoke(
      that.objS,
      'editTableItem',
      {
        'id': id
      },
      function (status, error, data) {
        ycomm.dom.fillElement(that.cfg.mainDataForm, data);
      }
    );
  };

  that.addEvent = function(aElementId, aEventName, aEventHandler) {
    var elemento=y$(aElementId);
    if (elemento) {
      elemento.addEventListener(
        aEventName,
        aEventHandler,
        false
      );
      return true;
    } else
      return false;
  }

  that.addButton = function(aButtonId, aEventHandler) {
    if (!that.addEvent(aButtonId, "click", aEventHandler))
      _dump("Button '{0}.{1}' not defined".format(that.objS,aButtonId));
  };

  that.init = function(aSubject, aLoadTableOnStartup) {
    if (typeof aLoadTableOnStartup=='undefined')
      aLoadTableOnStartup=true;
    /* parametro 's' no tripe do YeAPF */
    that.objS = aSubject;

    var errorList='', elementId;
    for (var i=0; i<that.cfg.prefixes.length; i++) {
      elementId=that.cfg.prefixes[i]+aSubject.ucFirst();
      if (y$(elementId)==null) {
        if (errorList>'')
          errorList+="\n";
        errorList+="\tElement '{0}' not found".format(elementId);
      }
    }

    if (errorList=='') {

      that.cfg.mainTab =        that.cfg.prefixes[0]+aSubject.ucFirst();
      that.cfg.tableTab =       that.cfg.prefixes[1]+aSubject.ucFirst();
      that.cfg.dataFormTab =    that.cfg.prefixes[2]+aSubject.ucFirst();
      that.cfg.mainDataForm =   that.cfg.prefixes[3]+aSubject.ucFirst();
      that.cfg.mainTable =      that.cfg.prefixes[4]+aSubject.ucFirst();

      var aux=ycomm.dom.getFormElements(that.cfg.mainDataForm);
      if (typeof aux.id == 'undefined')
        _dump("WARNING: There is no 'id' hidden field in '{0}'".format(that.cfg.mainDataForm));

      if (typeof me=='object')
        me.sessionList[me.sessionList.length]=that;

      if (aLoadTableOnStartup)
        that.loadTable();

    } else
      _dump("Object '{0}' cannot be initialized.\n{1}".format(that.objS, errorList));
    return that;
  };

  return that;
};


addOnLoadManager(
  function() {
    /* initialize app as quickly as DOM become ready */
    me = objYeapfApp();
    mTabNav.init();
    ycomm.setWaitIconControl(me.waitIconControl);
  }
);
