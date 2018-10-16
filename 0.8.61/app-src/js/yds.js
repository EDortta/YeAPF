/*********************************************
 * app-src/js/yds.js
 * YeAPF 0.8.61-105 built on 2018-10-16 08:01 (-3 DST)
 * Copyright (C) 2004-2018 Esteban Daniel Dortta - dortta@yahoo.com
 * 2018-09-04 06:33:17 (-3 DST)
 * First Version (C) 2010 - esteban daniel dortta - dortta@yahoo.com
**********************************************/
//# sourceURL=app-src/js/yds.js

/*
 * Set of functions with the duty of keep dataset coherent and accesible by other parts of your webApplication
 * It is mandatory to exist only one dataset manager per webapplication/session in order to achive better performance
 * To include the dataset routines into your webapp, call yeapf.getAppDataset when building frames at startup
 */

  var _navImagesName = new Array('first', 'rewind', 'priorPage', 'refresh', 'nextPage', 'fastforward', 'last', 'ruler');
  var _dsCache = new Array();
  var _questContext = new Array();

  function __dsGrantDS(dsName, dsParentName)
  {
    _dump(dsName, dsParentName);
    var ret=false;
    if (_dsCache[dsName] == undefined) {
      _dsCache[dsName] = new Array();
      _dsCache[dsName]['data']=new Array();
      _dsCache[dsName]['columns']=new Array();
      _dsCache[dsName]['groupTitles']=new Array();
      _dsCache[dsName]['groupTitlesValues']=new Array();
      _dsCache[dsName]['parent'] = dsParentName;
      _dsCache[dsName]['questing'] = 0;

      ret = true;
    } else
      ret = _dsCache[dsName]['frameOwner'] == undefined;

    return ret;
  }

  function __dsGetDSTree(dsName)
  {
    _dump(dsName);
    var auxDSName = dsName;
    var auxDSMap = dsName;
    while (_dsCache[auxDSName]['parent'] != undefined) {
      auxDSName = _dsCache[auxDSName]['parent'];
      auxDSMap = auxDSName + '/' + auxDSMap;
    }

    return (auxDSMap);
  }

  function __dsBuildBasicURL(dsName, sqlID)
  {
    _dump(dsName, sqlID);
    _dsCache[dsName]['sqlID']=sqlID;
    _dsCache[dsName]['URL']='query.php?u='+u+'&s=dataset&sqlID='+sqlID+'&dsName='+__dsGetDSTree(dsName);
  }

  function __dsOpen(dsName, sqlID, frameOwner)
  {
    _dump(dsName, sqlID, frameOwner);
    if (__dsGrantDS(dsName)) {
      if (frameOwner==undefined)
        frameOwner = $frameOwner(dsName,'object');
      _dsCache[dsName]['recordBlockSize']=20;
      _dsCache[dsName]['rowsPerPage']=20;
      _dsCache[dsName]['pageCount']=0;
      _dsCache[dsName]['frameOwner']=frameOwner;
    }

    __dsBuildBasicURL(dsName, sqlID);

    _dsCache[dsName]['curPage'] = 0;
    _dsCache[dsName]['rowStart'] = 0;
    _dsCache[dsName]['recordCount'] = -1;
    _dsCache[dsName]['loading'] = false;
    _dsCache[dsName]['questing'] = 0;
    _dsCache[dsName]['firstPageShowed'] = false;

    var auxDiv=_dsCache[dsName]['frameOwner'].document.getElementById(dsName);

    if (auxDiv)
      while (auxDiv.hasChildNodes()) {
          auxDiv.removeChild(auxDiv.lastChild);
      }

    var auxNavigator = _dsCache[dsName]['frameOwner'].document.getElementById(dsName+'_navigator');

    if (auxNavigator == undefined) {
      auxNavigator = _dsCache[dsName]['frameOwner'].document.createElement('div');
      auxNavigator.id=dsName+'_navigator';
      auxNavigator.style.display='none';

      auxDiv.appendChild(auxNavigator);
    }

    var auxTable = _dsCache[dsName]['frameOwner'].document.getElementById(dsName+'_table');
    if (auxTable == undefined) {
      auxTable = _dsCache[dsName]['frameOwner'].document.createElement('table');
      auxTable.style.cellSpacing=1;
      auxTable.style.cellPadding=4;
      auxTable.id = dsName+'_table';
      auxDiv.appendChild(auxTable);
    }

    _dsCache[dsName]['table']=auxTable;
    _dsCache[dsName]['navButtons']=auxNavigator;

    if (_dsCache[dsName]['frameOwner']==undefined)
      alert(dsName+' DIV is not defined');

    var purgeOnLoad = false;
    if (_dsCache[dsName]['navigator']) {
      purgeOnLoad = (_dsCache[dsName]['navigator']['purgeOnLoad'] + '').toUpperCase();
      purgeOnLoad = (purgeOnLoad=='TRUE');
      if (purgeOnLoad)
        __dsCleanQueries(dsName);
    }
    // The very first load comes filled from .php YeAPF script
    __dsLoad(dsName, 0);

  }

  function __dsGrantImage(dsName, imgObj, imgSrc)
  {
    _dump(dsName, imgObj, imgSrc);
    imgObj.img = _dsCache[dsName]['frameOwner'].document.getElementById(imgObj.imgID);
    if (imgObj.img == undefined) {
      imgObj.img = _dsCache[dsName]['frameOwner'].document.createElement('img');
      imgObj.img.id = imgObj.imgID;
      imgObj.img.border='0';
      imgObj.newImg = true;
    } else
      imgObj.newImg = false;

    imgObj.img.onerror = function (e) {
      e.target.style.display='none';
      e.target.style.width='0px';
      e.target.style.height='0px';
    }

    imgObj.img.src = imgSrc;

    return imgObj.img;
  }

  function __dsShowNavigator(dsName)
  {
    _dump(dsName);
    if (_dsCache[dsName]['navButtons']!=undefined) {
      var navButtons = _dsCache[dsName]['navButtons'];

      var buttonImagesBase = _dsCache[dsName]['navigator']['buttonImagesBase'];
      if ((buttonImagesBase != undefined) && (_dsCache[dsName]['navigator']['rowsPerPage']>0)) {
        var ext = buttonImagesBase.substr(buttonImagesBase.lastIndexOf('.') + 1);
        var fileName = buttonImagesBase.substr(0,buttonImagesBase.lastIndexOf('.'));

        for(var a=0; a<_navImagesName.length; a++) {
          var imgObj = Object();
          imgObj.imgID = dsName+'_navButton_'+_navImagesName[a];
          __dsGrantImage(dsName, imgObj, fileName + '_' + _navImagesName[a] + '.'+ ext);
          if (imgObj.newImg) {
            if (_navImagesName[a]!='ruler') {
              var aLink = _dsCache[dsName]['frameOwner'].document.createElement('a');
              var href = document.createAttribute('href');
              aLink.setAttribute('href', "javascript:dsFrame.__dsNavigate('"+dsName+"', '"+_navImagesName[a]+"')");
            } else {
              var aLink = _dsCache[dsName]['frameOwner'].document.createElement('span');
              aLink.style.paddingLeft='16px';
              aLink.style.paddingRight='16px';
            }
            aLink.appendChild(imgObj.img);
            navButtons.appendChild(aLink);

            navButtons[dsName+'_navButton_'+_navImagesName[a]] = imgObj.img;
          }
          // imgObj.img.src=fileName + '_' + _navImagesName[a] + '.'+ ext;

        }

        var rulerObj = Object();
        rulerObj.imgID = dsName+'_navButton_rulerBall';
        __dsGrantImage(dsName, rulerObj, fileName + '_ruler-ball.'+ ext);
        if (rulerObj.newImg) {
          // var aDiv = _dsCache[dsName]['frameOwner'].document.createElement('div');
          var aDiv = rulerObj.img;
          // aDiv.appendChild(rulerObj.img);
          aDiv.style.position='absolute';
          aDiv.style.zIndex='100';
          aDiv.style.top='0px';
          aDiv.style.left='0px';
          aDiv.style.display='none';
          navButtons.appendChild(aDiv);
        }
      }
    }
  }

  function __dsIsColumnVisible(dsName, columnName)
  {
    _dump(dsName, columnName);
    var cVisible = str2bool(_dsCache[dsName]['columns'][columnName]['visible'], true);

    return cVisible;
  }

  function __dsVisibleColumnsCount(dsName)
  {
    _dump(dsName);
    var visibleColumns = 0;
    for (var aColName in _dsCache[dsName]['columns']) {
      var cVisible = __dsIsColumnVisible(dsName,aColName);

      if (cVisible)
        visibleColumns++;
    }

    return visibleColumns;
  }

  /* __dsShowTableColumns ( dsName [, table])
   * Write the columns title in the indicated table
   * If table is not indicated, it uses the default table of the
   * indicated dataset (dsName)
   */

  function __dsShowTableColumns(dsName, table)
  {
    _dump(dsName, table);

    if (table==undefined)
      var table = _dsCache[dsName]['table'];

    while (table.rows.length<=0)
      table.insertRow(table.rows.length);

    var row = table.rows[0];
    var colNdx = 0;

    for (var a in _dsCache[dsName]['columns']) {
      if (__dsIsColumnVisible(dsName,a)) {
        if (colNdx >=  row.cells.length) {
          row.insertCell(colNdx);
          /*
           * row.style.backgroundColor = '#3B6288';
           * row.style.color = 'White';
           */
        }
        var cell = row.cells[colNdx];
        var cWidth = _dsCache[dsName]['columns'][a]['width'];
        var cQuest = _dsCache[dsName]['columns'][a]['quest'];
        var cTitle = onlyDefinedValue(_dsCache[dsName]['columns'][a]['title']);

        if (cWidth != undefined)
          cell.width = cWidth;

        cell.className='title';
        cell.innerHTML=cTitle;
        _dsCache[dsName]['columns'][a]['isTitleReady'] = false;

        colNdx++;
      }
    }

  }

  function __dsURL(dsName, a)
  {
    _dump(dsName, a);
    var rbSize = onlyDefinedValue(_dsCache[dsName]['recordBlockSize']);
    if (rbSize<1) {
      rbSize=1;
      _dsCache[dsName]['recordBlockSize']=rbSize;
    }
    return _dsCache[dsName]['URL']+'&a='+a+'&recordBlockSize='+onlyDefinedValue( _dsCache[dsName]['recordBlockSize']);
  }

  function __dsGetDataGeometry(dsName, purgeCache)
  {
    if (purgeCache==undefined)
      var purgeCache=false;
    purgeCache = +purgeCache;

    _dump(dsName, purgeCache);

    new Ajax.Request(__dsURL(dsName, 'getDataGeometry') + '&purgeCache='+purgeCache,
                     {
                       asynchronous: 'false',
                       encoding: 'iso-8859-1',
                       method: 'get',
                       onSuccess: function(transport) {
                          _dump(transport.status);

                          var aGeometry = new Object();
                          processQueryReturn(transport, undefined, undefined, aGeometry);

                          _dsCache[dsName]['columns'] = jsonParse(aGeometry.value['columns']);
                          _dsCache[dsName]['navigator'] = jsonParse(aGeometry.value['navigator']);

                          __dsCleanQueries(dsName);

                          __dsBuildBasicURL(dsName, aGeometry.value['sqlID']);

                          for (a in _dsCache[dsName]['columns']) {
                            var aGroupTitle = str2bool(_dsCache[dsName]['columns'][a]['groupTitle']);
                            if (aGroupTitle) {
                              var n = _dsCache[dsName]['groupTitles'].length;
                              _dsCache[dsName]['groupTitles'][n] = aGroupTitle;
                            }
                          }

                          for (a in _dsCache[dsName]['navigator'])
                            if (_dsCache[dsName][a] != undefined)
                              _dsCache[dsName][a]=_dsCache[dsName]['navigator'][a];

                          __dsShowNavigator(dsName);
                          __dsShowTableColumns(dsName);

                          _dsCache[dsName]['recordCount']=dRowCount;
                          if (_dsCache[dsName]['rowsPerPage']<=0)
                            _dsCache[dsName]['pageCount']=1;
                          else
                            _dsCache[dsName]['pageCount'] = dRowCount / _dsCache[dsName]['rowsPerPage'];

                          if (_dsCache[dsName]['data'].length != dRowCount)
                            __dsLoad(dsName);
                          else {
                            var oTable = _dsCache[dsName]['table'];
                            while (oTable.rows.length>1)
                              oTable.deleteRow(1);
                          }

                         }
                     })
  }

  function __dsFillDataCache(dsName, aData, rNdx, aQueryField, aQueryValue)
  {
    _dump(dsName, aData, rNdx, aQueryField, aQueryValue);

    var rowNdx;
    for (var j in aData.value) {
      if (rNdx != undefined)
        rowNdx=rNdx;
      else
        rowNdx = aData.value[j]['rowid'];

      if (aData.value[j]['rowid'] != undefined) {
        if (_dsCache[dsName]['data'][rowNdx] == undefined) {
          _dsCache[dsName]['data'][rowNdx] = new Array();
          _dsCache[dsName]['data'][rowNdx]['visible']=false;
        }
        if (aQueryField!=undefined)
          _dsCache[dsName]['data'][rowNdx][aQueryField] = aQueryValue;
        for (var a in aData.value[j])
          if (a != 'rowid')
            _dsCache[dsName]['data'][rowNdx][a] = onlyDefinedValue(aData.value[j][a]);

        if (rNdx!=undefined)
          rNdx++;
      }
    }
  }

  function __dsPurge(dsName)
  {
    _dump(dsName);

    _dsCache[dsName]['recordCount']=-1;
    _dsCache[dsName]['data'].length=0;
    _dsCache[dsName]['pageCount']=0;
    _dsCache[dsName]['rowStart']=0;
    _dsCache[dsName]['curPage']=0;
    _dsCache[dsName]['index'] = new Array();
  }

  function __dsCleanQueries(dsName)
  {
    _dump(dsName);
    for(var i in _dsCache)
      if (_dsCache[i]['parent'] == dsName)
        __dsPurge(i);
  }

  function __refresh(dsName)
  {
    _dump(dsName);

    __dsCleanQueries(dsName);
    __dsLoad(dsName, true);
  }

  function __dsRefresh(dsName)
  {
    _dump(dsName);

    if (dsName==undefined) {
      for(var i in _dsCache)
        if (_dsCache[i])
          if (_dsCache[i]['parent']==undefined)
            __dsRefresh(i);
    } else {
      __refresh(dsName);
    }
  }

  function __dsLoad(dsName, purgeCache)
  {
    if (purgeCache==undefined)
      purgeCache=false;
    purgeCache = +purgeCache;

    _dump(dsName, purgeCache);

    if (purgeCache)
      __dsPurge(dsName);


    if (!_dsCache[dsName]['loading']) {
      _dsCache[dsName]['frameOwner'].showWaitIcon();
      _dsCache[dsName]['loading'] = true;

      if ( _dsCache[dsName]['recordCount'] < 0 ) {
        _dsCache[dsName]['data'].length=0;
        __dsGetDataGeometry(dsName, purgeCache);
      } else {
        if (_dsCache[dsName]['recordCount'] > _dsCache[dsName]['data'].length)
          _dsCache[dsName]['AJAX'] = new Ajax.Request(
              __dsURL(dsName, 'getDataBlock')+
                '&recordBlockStart='+_dsCache[dsName]['data'].length +
                '&purgeCache=0',
              {
                 asynchronous: 'true',
                 method: 'get',
                 encoding: 'iso-8859-1',
                 onSuccess: function(transport) {
                          _dump(transport.status);

                          var aDataContext = new Object();
                          var aData = new Object();
                          processQueryReturn(transport, aDataContext, aData);

                          var fFirstTime = ((_dsCache[dsName]['data']==undefined) || (_dsCache[dsName]['data'].length==0));

                          __dsFillDataCache(dsName, aData);


                          var fFirstPageReady = false;

                          fFirstPageReady=(_dsCache[dsName]['recordCount'] < _dsCache[dsName]['rowsPerPage']);
                          if ((_dsCache[dsName]['data'].length >= _dsCache[dsName]['rowsPerPage']) && (_dsCache[dsName]['rowsPerPage']>0))  fFirstPageReady=true;

                          if ((_dsCache[dsName]['data'].length >= _dsCache[dsName]['recordCount']) && (_dsCache[dsName]['rowsPerPage']<=0)) fFirstPageReady=true;

                          fFirstPageReady = fFirstPageReady && (!_dsCache[dsName]['firstPageShowed']);


                          if (fFirstPageReady) {
                            _dsCache[dsName]['firstPageShowed']=true;
                            __dsShowInfo(dsName);
                          }

                          if ((_dsCache[dsName]['recordCount'] > _dsCache[dsName]['data'].length) && (dRowCount>0))
                            __dsLoad(dsName);
                          _dsCache[dsName]['frameOwner'].hideWaitIcon();
                 }
              });
      }
      _dsCache[dsName]['loading'] = false;
      _dsCache[dsName]['frameOwner'].hideWaitIcon();
    }
  }

  var mutexCellContent = 0;

  function __dsMountCellContent(dsName, aCell, aQueryField, aQueryValue, aColumns, aHTML, aStack)
  {
    _dump(dsName, aCell, aQueryField, aQueryValue, '...');

    var ret='';
    if (aHTML==undefined) {
      var aHTML={ header: '', body: '%(columns)', footer: ''};
    } else
      if (typeof aHTML=="string") {
        var aux=aHTML;
        aHTML={ header: '', body: aux, footer: ''};
      }

    // aguardar enquanto outro processamento igual a este finaliza
    while (mutexCellContent>0) ;

    if (mutexCellContent == 0) {
      // incrementar o indicador de bloqueio
      mutexCellContent++;
      // rodar só se estou sozinho neste pedaço

      try {
        if (mutexCellContent==1) {
          aCell.innerHTML='';

          var rNdx = _dsCache[dsName]['index'][aQueryValue];

          var stackLevel=0;
          var stack = new Array();

          if (aStack != undefined) {
            stack[stackLevel] = new Array();
            for (var columnName in aStack)
              stack[stackLevel][columnName] = aStack[columnName];
            stackLevel++;
          }

          if (rNdx!=undefined) {
            var rNdxMin=rNdx, rNdxMax=rNdx-1;

            while ((rNdxMin>0) && (_dsCache[dsName]['data'][rNdxMin-1][aQueryField]==aQueryValue))
              rNdxMin--;

            while ((rNdxMax<_dsCache[dsName]['data'].length-1) && (_dsCache[dsName]['data'][rNdxMax+1][aQueryField]==aQueryValue))
              rNdxMax++;

            for (var ndx=rNdxMin; ndx <= rNdxMax; ndx++) {
              stack[stackLevel] = new Array();
              for (var columnName in _dsCache[dsName]['data'][ndx]) {
                // columnName = _dsCache[dsName]['columns'][cNdx];
                if (typeof _dsCache[dsName]['data'][ndx][columnName]!='function')
                  stack[stackLevel][columnName] = onlyDefinedValue(_dsCache[dsName]['data'][ndx][columnName]);
              }
              stackLevel++;
            }
          }

          if (aHTML.header!=undefined)
            ret = yAnalise(aHTML.header, stack);


          var visibleColumns = __dsVisibleColumnsCount(dsName);
          var colsHTML='';
          if (visibleColumns>0) {

            var auxTableID = 't.'+aCell.id;
            var auxTable = _dsCache[dsName]['frameOwner'].document.getElementById(auxTableID);
            if (auxTable == undefined) {
              var auxTable = _dsCache[dsName]['frameOwner'].document.createElement('table');
              auxTable.id = auxTableID;
              _dsCache[dsName]['table'] = auxTable;
            } else {
              while (auxTable.rows.length>0)
                auxTable.deleteRow(0);
            }

            if (rNdx != undefined) {
              for (var ndx=rNdxMin; ndx <= rNdxMax; ndx++) {
                var row = auxTable.insertRow(-1);

                __dsFillTableRow(dsName, row, _dsCache[dsName]['data'][ndx]);
              }
            }

            // aCell.appendChild(auxTable);
            // ,        html: "%ibdate(DATA_INTERNACAO) <b>%(tipo)</b>"

            colsHTML += '<span  class="innerTable"><table style="background-color: '+aCell.parentNode.style.backgroundColor+'">' + auxTable.innerHTML + '</table></span>';
          }

          stack[stackLevel-1]['columns'] = colsHTML;
          if (aHTML.body!=undefined)
            ret += yAnalise(aHTML.body, stack);

          if (aHTML.footer!=undefined)
            ret += yAnalise(aHTML.footer, stack);

          aCell.innerHTML = ret;
        }
      } finally {
        // decrementar o contador de bloqueio
        mutexCellContent--;
      }
    }
    return ret;
  }


  function __dsQuest(dsName, aQuest, aDataLine, aCell)
  {
    _dump(dsName, aQuest);
    ret=false;

    if (_dsCache[dsName]['questing']<=0) {
      try {
        _dsCache[dsName]['questing']++;

        // puede ser encodeURIComponent() en lugar de escape()

        var aWhereClause = escape(yAnalise(aQuest['whereClause'], aDataLine));
        var aQueryField = yAnalise(aQuest['queryField'], aDataLine);
        var aQueryValue = yAnalise(aQuest['queryValue'], aDataLine);
        var aQueryExtendedValue = aQueryValue;
        var aQueryExtendedField = aQueryField;
        if (aWhereClause>'') {
          aQueryExtendedValue += '.and.' + aWhereClause;
          aQueryExtendedField += ';' + aWhereClause;
        }

        var aTableName = yAnalise(aQuest['table'], aDataLine);
        var aGroupBy = yAnalise(aQuest['groupBy'], aDataLine);
        var aOrderBy = yAnalise(aQuest['orderBy'], aDataLine);
        var aTableJoin = escape(yAnalise(aQuest['tableJoin'], aDataLine));

        var isTitleReady = _dsCache[dsName]['columns'][aQuest['parentColName']]['isTitleReady'];
        if (!isTitleReady) {
          var mainTable = _dsCache[dsName]['table'];
          var titleCell = mainTable.rows[0].cells[aCell.cellIndex];
          var auxColTitleTable = _dsCache[dsName]['frameOwner'].document.createElement('table');
          auxColTitleTable.insertRow(-1);
          var titleRow = auxColTitleTable.rows[0];
          /*
           * titleRow.style.backgroundColor = '#3B6288';
           * titleRow.style.color = 'White';
           */

          // titleCell.innerHTML='';
          titleCell.appendChild(auxColTitleTable);
        }

        var aColumns = aQuest['columns'];
        var aColumnList = '';
        var aColumnListID = '';
        if (aGroupBy=='')
          aColumnList = aQueryField;
        var aCellTitleNdx = 0;
        var auxColName;
        for(var aCol in aColumns) {
          if (aCol.substring(0,6)!='query_') {
            if (aColumnList>'')  aColumnList+=',';
            if (aColumnListID>'')  aColumnListID+='_';
            if (aCol.substring(0,5)=='calc_')
              auxColName = "'' as "+aCol;
            else {
              if (aColumns[aCol]['fieldName'])
                auxColName = aColumns[aCol]['fieldName'];
              else
                auxColName = aCol;
            }
            aColumnList += auxColName;
            aColumnListID += aCol;
          }
          if (!isTitleReady) {
            if (onlyDefinedValue(aQuest['columns'][aCol]['title'])>'') {
              titleRow.insertCell(aCellTitleNdx);
              var aCellTitle = titleRow.cells[aCellTitleNdx];
              // aCellTitle.className='titleRow';
              if (aQuest['columns'][aCol] != undefined) {
                if (aQuest['columns'][aCol]['width'] != undefined)
                  aCellTitle.width = aQuest['columns'][aCol]['width'];

                aCellTitle.innerHTML = onlyDefinedValue(aQuest['columns'][aCol]['title']);
                aCellTitle.className='subtitle';
                aCellTitle.style.borderStyle='none';
              }

              aCellTitleNdx++;
            }
          }
        }

        if (!isTitleReady) {
          if (titleRow.cells.length==0)
            titleCell.removeChild(auxColTitleTable);
        }

        _dsCache[dsName]['columns'][aQuest['parentColName']]['isTitleReady']=true;

        var aDSName = aQuest['dsName'];
        if (aDSName==undefined)
          aDSName = aTableName+'.'+aColumnListID;

        if (__dsGrantDS(aDSName, dsName)) {
          _dsCache[aDSName]['URL']='query.php?u=' + u + '&s=dataset&dsContext=' + dsName + '&dsName=' + __dsGetDSTree(aDSName) + '&queryField=' + aQueryField + '&tableName=' + aTableName + '&columnList=' + aColumnList + '&orderBy=' + aOrderBy + '&groupBy=' + aGroupBy + '&tableJoin=' + aTableJoin;
          _dsCache[aDSName]['index'] = new Array();
          _dsCache[aDSName]['frameOwner'] = _dsCache[dsName]['frameOwner'];
          _dsCache[aDSName]['rowsPerPage']=200;
          _dsCache[aDSName]['curPage']=0;
          _dsCache[aDSName]['rowStart']=0;
          _dsCache[aDSName]['columns'] = aQuest['columns'];
          _dsCache[aDSName]['data'].length = 0;
        }

        if (_dsCache[aDSName]['questing']==0) {

          if (_dsCache[aDSName]['index'][aQueryExtendedValue] == undefined) {

            _dsCache[aDSName]['questing']++;
            var aAJAX = new Ajax.Request(
                            __dsURL(aDSName, 'getQuestValue') + '&queryValue='+aQueryValue + '&whereClause=' + aWhereClause + '&orderBy=' + aOrderBy + '&groupBy=' + aGroupBy + '&tableJoin=' + aTableJoin,
                            {
                              asynchronous: (aCell!=undefined),
                              method: 'get',
                              encoding: 'iso-8859-1',
                              onSuccess: function(transport) {
                                _dump(transport.status);
                                var aDataContext = new Object();
                                var aData = new Object();
                                processQueryReturn(transport, aDataContext, aData);

                                var rNdx = _dsCache[aDSName]['data'].length;
                                _dsCache[aDSName]['index'][aQueryExtendedValue] = rNdx;
                                _dsCache[aDSName]['data'][rNdx] = new Array();

                                /*
                                for (var j in aData.value) {
                                  if (j != 'rowid')
                                    _dsCache[aDSName]['data'][rNdx][j] = onlyDefinedValue(aData.value[j]);
                                }
                                */

                                __dsFillDataCache(aDSName, aData, rNdx, aQueryExtendedField, aQueryExtendedValue);

                                retCell = __dsMountCellContent(aDSName, aCell, aQueryExtendedField, aQueryExtendedValue, aColumns, aQuest['html'], aDataLine);

                              },
                              onComplete: function() {
                                _dsCache[aDSName]['questing']--;
                                ret = true;
                              }
                            }
                        );
          } else {
            retCell = __dsMountCellContent(aDSName, aCell, aQueryExtendedField, aQueryExtendedValue, aColumns, aQuest['html'], aDataLine);
            ret = true;
          }
        }

      } finally {
        _dsCache[dsName]['questing']--;
      }
    } else
      alert('Warning\nCircular querying');
    return ret;
  }

  function __dsGetColumnValue(aDataLine, aColName)
  {
    _dump(aDataLine, aColName);

    var aValue = '';
    if (aDataLine) {
      if (aDataLine[aColName] != undefined)
        var aValue = aDataLine[aColName];
      else {
        aColName=aColName.toUpperCase();
        if (aDataLine[aColName] != undefined)
          var aValue = onlyDefinedValue(aDataLine[aColName]);
      }
    }

    return aValue;
  }

  function _doQuest(qcNdx)
  {
    _dump(qcNdx);
    var questDone;

    questDone=__dsQuest(_questContext[qcNdx]['dsName'],
                        _questContext[qcNdx]['aQuest'],
                        _questContext[qcNdx]['aDataLine'],
                        _questContext[qcNdx]['cell']);
    if (questDone)
      _questContext[qcNdx]['usage']=1;
    else
      setTimeout("_doQuest("+qcNdx+")",200);
  }

  function _getQuestFreeSlot()
  {
    _dump();
    var freeSlot=-1;
    for (var aSlot=0; aSlot<_questContext.length; aSlot++)
      if (_questContext['inUse']<=1)
        freeSlot=aSlot;

    if (freeSlot<0) {
      freeSlot=_questContext.length;
      _questContext[freeSlot]=new Array();
    }
    _questContext[freeSlot]['usage']=2;
    return freeSlot;
  }

  function __dsFillTableRow(dsName, aRow, aDataLine)
  {
    _dump(dsName, aRow, aDataLine);

    var colNdx = 0;
    for (var aCol=0; aCol<_dsCache[dsName]['groupTitles'].length; aCol++) {
      var aColName = _dsCache[dsName]['groupTitles'][aCol];
      var aValue = __dsGetColumnValue(aDataLine, aColName);
      var aLastValue = _dsCache[dsName]['groupTitlesValues'][aColName];

      if (aLastValue!=aValue) {
        _dsCache[dsName]['groupTitlesValues'][aColName] = aValue;

        if (groupRow == undefined)
          var groupRow = aRow.offsetParent.insertRow(aRow.rowIndex);

        groupRow.style.backgroundColor = 'white';
        groupRow.style.color = '';
        groupRow.id = dsName+'.groupTitle.'+groupRow.rowIndex;

        if (groupCell==undefined)
          var groupCell = groupRow.insertCell(-1);

        groupCell.id = dsName+'.groupTitle.'+groupRow.rowIndex+'.'+colNdx;
        groupCell.colSpan = __dsVisibleColumnsCount(dsName);
        groupCell.innerHTML="<big><b>&nbsp;"+aValue+'</b></big>';
        colNdx++;
      }
    }

    colNdx=0;

    for (var aColName in _dsCache[dsName]['columns']) {

      var cVisible = __dsIsColumnVisible(dsName, aColName);

      if (cVisible) {

        var aValue = __dsGetColumnValue(aDataLine, aColName);

        var cType = _dsCache[dsName]['columns'][aColName]['type'];
        var cLabel = _dsCache[dsName]['columns'][aColName]['label'];
        var cCondLabel = _dsCache[dsName]['columns'][aColName]['condLabel'];
        var cAlign = _dsCache[dsName]['columns'][aColName]['align'];
        var cWidth = _dsCache[dsName]['columns'][aColName]['width'];
        var cHTML = _dsCache[dsName]['columns'][aColName]['html'];
        var aQuest = _dsCache[dsName]['columns'][aColName]['quest'];

        if (colNdx >=  aRow.cells.length) {
          var cell = aRow.insertCell(colNdx);
          cell.id = dsName+'.'+colNdx+'.'+aRow.rowIndex;
        }

        var cell = aRow.cells[colNdx];

        if (cWidth != undefined)
          cell.width = cWidth;

        if (aQuest != undefined) {
          var waitImage = _dsCache[dsName]['navigator']['waitImage'];
          if (waitImage != undefined)
            cell.innerHTML = "<img src="+waitImage+" style='max-height:16px'>";
          else
            cell.innerHTML='Carregando...';
            aQuest['parentColName'] = aColName;

          var qcNdx = _getQuestFreeSlot();
          _questContext[qcNdx]['usage']=2;
          _questContext[qcNdx]['dsName']=dsName;
          _questContext[qcNdx]['aQuest']=aQuest;
          _questContext[qcNdx]['aDataLine']=aDataLine;
          _questContext[qcNdx]['cell']=cell;

          setTimeout("_doQuest("+qcNdx+")",50);
        }

        if (cType>'') {
          if (cType != 'query')
            aValue = yAnalise('%'+cType+'('+aColName+')', aDataLine);
          else
            aValue = yAnalise('%('+aValue+')', aDataLine);
        } else
          aValue = yAnalise(aValue, aDataLine);

        if (cHTML != undefined) {
          var aValue = yAnalise(cHTML, aDataLine);
        }

        if (cAlign != undefined)
          cell.align = cAlign;

        if (cLabel>'')
          aValue = yAnalise(cLabel+' '+aValue, aDataLine);
        if (cCondLabel>'')
          if (aValue>'')
            aValue = yAnalise(cCondLabel+' '+aValue, aDataLine);

        if (aQuest == undefined)
          cell.innerHTML = aValue;

        cell.vAlign='top';

        colNdx++;

      }
    }
  }


  function __dsShowInfo(dsName)
  {
    _dump(dsName);

    // reposicionamiento del puntero en la regla
    var rulerBallIMG = _dsCache[dsName]['frameOwner'].document.getElementById(dsName+'_navButton_rulerBall');
    var rulerIMG = _dsCache[dsName]['frameOwner'].document.getElementById(dsName+'_navButton_ruler');
    if (rulerBallIMG) {
      var offY;
      offY = Math.abs(rulerIMG.offsetHeight - rulerBallIMG.offsetHeight) / 2;
      rulerBallIMG.style.top = (rulerIMG.offsetTop + offY)+'px';
      rulerBallIMG.style.display = 'block';
      // calculámos el centro del puntero
      var w = rulerIMG.offsetWidth;
      var x = Math.min(Math.round(_dsCache[dsName]['curPage'] * w / _dsCache[dsName]['pageCount'] ), w);
      x -= rulerBallIMG.offsetWidth / 2;
      x += rulerIMG.offsetLeft;
      rulerBallIMG.style.left = x + 'px';
    }

    // cálculo de los títulos

    _dsCache[dsName]['groupTitlesValues']=new Array();
    var navButtons = _dsCache[dsName]['navButtons'];
    var navButtonsVisible = navButtons.style.display == 'block';

    var rowsPerPage = parseInt(_dsCache[dsName]['rowsPerPage']);
    if (rowsPerPage<=0) {
      rowsPerPage = _dsCache[dsName]['data'].length;
      navButtonsVisible = false;
    } else
      navButtonsVisible = true;
    var rowOffset = _dsCache[dsName]['curPage'] * rowsPerPage;
    var rowStart = _dsCache[dsName]['rowStart'];
    rowStart = Math.max(rowOffset, rowStart);
    rowStart = Math.min(rowStart, rowOffset + rowsPerPage - 1);
    var rowEnd = Math.min(rowOffset + rowsPerPage, _dsCache[dsName]['data'].length );

    // alert('rowStart: '+rowStart+'\nrowEnd: '+rowEnd+'\n'+_dsCache[dsName]['curPage']+'/'+_dsCache[dsName]['pageCount']);

    var table = _dsCache[dsName]['table'];

    for (var r=1; r<=Math.min(rowsPerPage, table.rows.length); r++) {
      var row = table.rows[r];
      if (row) {
        row.style.backgroundColor = '#E5E5E5';
        row.style.color = '#EEEEEE';
        if (row.id.indexOf('groupTitle')>0)
          table.deleteRow(row.rowIndex);
      }
    }

    // limpieza del contenído de la tabla

    for (var r=rowEnd - rowStart ; r < rowsPerPage; r++) {
      var rNdx = rowEnd - rowStart + 1;
      if (rNdx < table.rows.length)
        table.deleteRow(rNdx);
    }

    // llenado de la tabla

    for (var r=rowStart; r < rowEnd ; r++) {
      var aDataLine = _dsCache[dsName]['data'][r];

      var rowNdx = r - rowStart + 1;

      while (table.rows.length<= rowNdx)
        table.insertRow(table.rows.length);


      var row = table.rows[rowNdx];
      row.style.backgroundColor = rowColorSpec.suggestRowColor(r - rowStart);
      row.style.color = '';

      __dsFillTableRow(dsName, row, aDataLine);

      _dsCache[dsName]['rowStart'] = r;

    }

    // alert(rowEnd - rowStart + '\n' + rowsPerPage );

    navButtons.style.display = navButtonsVisible ? 'block' : 'none';
    navButtons.style.zIndex=10;
    _dump("Botões: "+navButtons.style.display);
    _dump(getX(navButtons));
    if (rulerBallIMG)
      rulerBallIMG.style.display = navButtons.style.display;
  }

  function __dsNavigate(dsName, navAction)
  {
    _dump(dsName, navAction);
    switch(navAction)
    {
      case 'first':
        _dsCache[dsName]['curPage']=0;
        _dsCache[dsName]['rowStart'] = _dsCache[dsName]['curPage'] * _dsCache[dsName]['rowsPerPage'];
        __dsShowInfo(dsName);
        break;

      case 'rewind':
        _dsCache[dsName]['curPage'] = Math.min(0,_dsCache[dsName]['curPage'] - 5);
        _dsCache[dsName]['rowStart'] = _dsCache[dsName]['curPage'] * _dsCache[dsName]['rowsPerPage'];
        __dsShowInfo(dsName);
        break;

      case 'priorPage':
        if (_dsCache[dsName]['curPage'] > 0 )
          _dsCache[dsName]['curPage']--;
        _dsCache[dsName]['rowStart'] = _dsCache[dsName]['curPage'] * _dsCache[dsName]['rowsPerPage'];
        __dsShowInfo(dsName);
        break;

      case 'nextPage':
        if (_dsCache[dsName]['curPage'] < Math.round(_dsCache[dsName]['pageCount']) )
          _dsCache[dsName]['curPage']++;
        _dsCache[dsName]['rowStart'] = _dsCache[dsName]['curPage'] * _dsCache[dsName]['rowsPerPage'];
        __dsShowInfo(dsName);
        break;

      case 'fastforward':
        _dsCache[dsName]['curPage'] = Math.max(Math.round(_dsCache[dsName]['pageCount']),_dsCache[dsName]['curPage'] + 5);
        _dsCache[dsName]['rowStart'] = _dsCache[dsName]['curPage'] * _dsCache[dsName]['rowsPerPage'];
        __dsShowInfo(dsName);
        break;

      case 'last':
        _dsCache[dsName]['curPage']=Math.round(_dsCache[dsName]['pageCount']);
        _dsCache[dsName]['rowStart'] = _dsCache[dsName]['curPage'] * _dsCache[dsName]['rowsPerPage'];
        __dsShowInfo(dsName);
        break;

      case 'refresh':
        __refresh(dsName);
        break;

      case 'ruler':
        var rulerIMG = y$(dsName+'_navButton_ruler');
        if (rulerIMG != undefined) {
          var x = curX - getX(rulerIMG);
          var w = rulerIMG.offsetWidth;
          var page = Math.round((x * Math.round(_dsCache[dsName]['pageCount']) / w));

          _dsCache[dsName]['curPage']=page;
          _dsCache[dsName]['rowStart'] = _dsCache[dsName]['curPage'] * _dsCache[dsName]['rowsPerPage'];
          __dsShowInfo(dsName);
        }
        break;
    }

  }

  var _dsMsgTickCount=0;

  function dsMsgProc()
  {
    var msg=messageStack.shift();

    var sourceUserId=msg[0];
    var aMessage=msg[1];
    var wParam=msg[2];
    var lParam=msg[3];

    if (aMessage!='systemTick')
      if ((_dsMsgTickCount++ % 150)==0)
      _dump('aMessage: '+aMessage+' wParam: '+wParam+' tickCount: '+_dsMsgTickCount);
    switch(aMessage)
    {
      case 'refresh':
        if (_dsCache[wParam] != undefined)
          __refresh(wParam);
        break;

      case 'alert':
        alert(lParam);
        break;

      case '_dump':
        _dump(lParam);
        break;
    }


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

        _registerMsgProc('dsMsgProc');
      }
    );
  }

