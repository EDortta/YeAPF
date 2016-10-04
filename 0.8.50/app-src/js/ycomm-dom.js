/*********************************************
 * app-src/js/ycomm-dom.js
 * YeAPF 0.8.50-54 built on 2016-10-04 16:54 (-3 DST)
 * Copyright (C) 2004-2016 Esteban Daniel Dortta - dortta@yahoo.com
 * 2016-10-04 16:54:14 (-3 DST)
 * First Version (C) 2014 - esteban daniel dortta - dortta@yahoo.com
**********************************************/
//# sourceURL=app-src/js/ycomm-dom.js

ycomm.dom = {
  _elem_templates: []
};

ycomm.dom.fillInplaceData = function(aElement, aData) {
  for(var i in aData)
    if (aData.hasOwnProperty(i))
      aElement.setAttribute('data_'+i, aData[i]);
};

ycomm.dom.getInplaceData = function(aElement) {

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
 *
 *             -- events -- (READY)
 *             onNewItem(aElementID, aNewElement, aRowData)
 *             onNewRowReady(aElementID, aRow)
 *             onSelect(aElementID, id) ou onClick(aElementID, id)
 *             -- events -- (PLANNED)
 *             onBeforeItemAdd(aElementID, id, dataLine)
 *             onItemAdd(aElementID, id)
 *             onReady(aElementID)
 * aFlags - JSON
 *          deleteRows  (true by default)
 *          paintRows   (true by default) 
 *          insertAtTop (applies to TR. false by default)
 */
ycomm.dom.fillElement = function(aElementID, xData, aLineSpec, aFlags) {
  if ((aLineSpec === undefined) || (aLineSpec===null))
    aLineSpec = {};

  if (typeof aFlags=="boolean")
    aFlags={deleteRows: aFlags};

  aFlags=aFlags || {};

  if (typeof aFlags.deleteRows=='undefined')
    aFlags.deleteRows=true;
  if (typeof aFlags.paintRows=='undefined')
    aFlags.paintRows=true;

  var idFieldName, colName, newRow, canCreateRow,
      aElement = y$(aElementID),
      rowIdOffset=0,
      first_time = typeof ycomm.dom._elem_templates[aElementID] == "undefined";

  idFieldName = aLineSpec.idFieldName || 'id';

  var getDataFromXData = function(xDataItem) {
    /* this function extract the pouchdb data from xDataItem if exists. otherwise, return xDataItem */
    if ((xDataItem.doc) && (xDataItem.id) && (xDataItem.value))
      xDataItem=xDataItem.doc;
    return xDataItem;
  };

  var setNewRowAttributes = function (aNewRow) {
    var auxIdSequence,
        auxInplaceData,
        xDataItem=getDataFromXData(xData[j]);

    cNdx = 0;
    if (aNewRow.nodeName=='TR') {
      if (aFlags.paintRows)
        aNewRow.style.backgroundColor=rowColorSpec.suggestRowColor(rowGroup);
    }
    if (xDataItem[idFieldName]) {
      if (y$(xDataItem[idFieldName])) {
        auxIdSequence = 0;
        while (y$(xDataItem[idFieldName]+'_'+auxIdSequence))
          auxIdSequence++;
        aNewRow.id = xDataItem[idFieldName]+'_'+auxIdSequence;
      } else
        aNewRow.id = xDataItem[idFieldName];
    }

    if (typeof aLineSpec.inplaceData != 'undefined') {
      for(var i=0; i<aLineSpec.inplaceData.length; i++) {
        colName = aLineSpec.inplaceData[i];
        auxInplaceData = xDataItem[colName] || '';
        aNewRow.setAttribute('data_'+colName, auxInplaceData);
      }
    }

    if ((aLineSpec.onClick) || (aLineSpec.onSelect)) {
      aNewRow.addEventListener('click', ((aLineSpec.onClick) || (aLineSpec.onSelect)), false);
    }
  };

  var addCell = function(colName) {
    if (colName != idFieldName) {
      var newCell  = newRow.insertCell(cNdx),
          xDataItem=getDataFromXData(xData[j]),
          aNewCellValue = colName!==null?unmaskHTML(xDataItem[colName]):unmaskHTML(xDataItem);
      if ((aLineSpec.columns) && (aLineSpec.columns[colName])) {
        if (aLineSpec.columns[colName].align)
          newCell.style.textAlign=aLineSpec.columns[colName].align;
        if (aLineSpec.columns[colName].type) {
          aNewCellValue = yAnalise('%'+aLineSpec.columns[colName].type+'('+aNewCellValue+')');
        }
      }

      if (!canCreateRow) {
        newCell.addClass('warning');
        /* newCell.style.borderLeft = 'solid 1px red'; */
      }

      newCell.innerHTML = aNewCellValue.length===0?'&nbsp;':aNewCellValue;
      newCell.style.verticalAlign='top';
      newCell.id=aElementID+'_'+cNdx+'_'+oTable.rows.length;
      newCell.setAttribute('colName', colName);
      if (typeof aLineSpec.onNewItem == 'function')
        aLineSpec.onNewItem(aElementID, newCell, xDataItem);
      cNdx = cNdx + 1;
    }
  };

  var oTable, auxHTML, j, c, cNdx, i, newCell, internalRowId=(new Date()).getTime()-1447265735470, xDataItem;

  if (aElement) {
    if (aElement.nodeName=='TABLE') {
      if (aElement.getElementsByTagName('tbody').length>0)
        oTable = aElement.getElementsByTagName('tbody')[0];
      else
        oTable = aElement;
      if (oTable.getElementsByTagName('tbody').length>0)
        oTable = oTable.getElementsByTagName('tbody')[0];

      /* 1) if this is the first time, pull the template from the table itself
       * 2) the 'aLineSpec' has higher priority */
      if (first_time) {
        if (typeof (aLineSpec.columns || aLineSpec.rows || aLineSpec.html) == "undefined") {
          ycomm.dom._elem_templates[aElementID]={};
          if (oTable.rows.length>0) {
            ycomm.dom._elem_templates[aElementID].rows = [];
            for(i=0; i<oTable.rows.length; i++)
              ycomm.dom._elem_templates[aElementID].rows[i]=trim(oTable.rows[i].innerHTML+"").replace(/\ \s+/g,'');
          }
        } else {
          ycomm.dom._elem_templates[aElementID]={};
          ycomm.dom._elem_templates[aElementID].columns = aLineSpec.columns;
          ycomm.dom._elem_templates[aElementID].rows    = aLineSpec.rows;
          ycomm.dom._elem_templates[aElementID].html    = aLineSpec.html;
        }
      }
      mergeObject(ycomm.dom._elem_templates[aElementID], aLineSpec, true);

      if (aFlags.deleteRows) {
        while(oTable.rows.length>0)
          oTable.deleteRow(oTable.rows.length-1);
      } else {
        rowIdOffset=oTable.rows.length;
      }

      var rowGroup = oTable.rows.length % 2;
      cNdx = null;
      for (j in xData) {
        if (xData.hasOwnProperty(j)) {
          xDataItem=getDataFromXData(xData[j]);
          rowGroup++;

          canCreateRow = true;
          if (!aFlags.deleteRows) {
            if (xDataItem[idFieldName]) {
              for(i=0; ((canCreateRow) && (i<oTable.rows.length)); i++) {
                if (oTable.rows[i].id == xDataItem[idFieldName]) {
                  newRow = oTable.rows[i];
                  while (newRow.cells.length>0)
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
          xDataItem.rowid = ((!aFlags.insertAtTop) && (typeof newRow.rowIndex !== "undefined" ))?newRow.rowIndex:internalRowId + '';
          xDataItem._elementid_ = aElementID;

          setNewRowAttributes(newRow);

          /* default action when neither columns nor html are defined */
          if ((typeof aLineSpec.html == 'undefined') &&
              (typeof aLineSpec.rows == 'undefined') &&
              (typeof aLineSpec.columns == 'undefined')) {
            if (typeof xDataItem=='string') {
              addCell(null);
            } else {
              for(colName in xDataItem) {
                if ((xDataItem.hasOwnProperty(colName)) &&
                    (colName!=idFieldName) &&
                    (colName!='rowid') &&
                    (colName!='_elementid_')) {
                  addCell(colName);
                }
              }
            }

          } else {
            /* columns order are defined */
            if (typeof aLineSpec.columns != 'undefined') {

              if (isArray(aLineSpec.columns)) {
                for (c=0; c<aLineSpec.columns.length; c++) {
                  addCell(aLineSpec.columns[c]);
                }
              } else {
                for(c in aLineSpec.columns) {
                  if (aLineSpec.columns.hasOwnProperty(c))
                    addCell(c);
                }
              }

            } else if (typeof aLineSpec.html != 'undefined'){
              /* html parser is enabled */
              newCell  = newRow.insertCell(0);
              newCell.innerHTML = yAnalise(aLineSpec.html,xDataItem);
              newCell.style.verticalAlign='top';
              newCell.id=aElementID+'_'+cNdx+'_'+oTable.rows.length;
              if (typeof aLineSpec.onNewItem == 'function')
                aLineSpec.onNewItem(aElementID, newCell, xDataItem);

            } else if (typeof aLineSpec.rows != 'undefined') {
              var firstRow = true;
              for(r=0; r < aLineSpec.rows.length; r++) {
                if (!firstRow) {
                  newRow = oTable.insertRow(oTable.rows.length);
                  setNewRowAttributes(newRow);
                }
                newRow.innerHTML = yAnalise(aLineSpec.rows[r],xDataItem);
                if (!canCreateRow) {
                  for(c=0; c<newRow.cells.length; c++)
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

    } else if (aElement.nodeName=='UL') {
      var oUL = aElement;

      if (aFlags.deleteRows) {
        while (oUL.firstChild) {
          oUL.removeChild(oUL.firstChild);
        }
      }

      for (j in xData) {
        if (xData.hasOwnProperty(j)) {
          xDataItem=getDataFromXData(xData[j]);
          var entry = document.createElement('li');
          var innerText = '',
              asHTML=false;
          if (typeof aLineSpec.rows=='object') {
            for(r=0; r < aLineSpec.rows.length; r++) {
              innerText=innerText+yAnalise(aLineSpec.rows[r],xDataItem)+"";
            }
            asHTML=true;
          } else if (typeof aLineSpec.html=='string') {
            innerText=innerText+yAnalise(aLineSpec.html,xDataItem)+"";
            asHTML=true;
          } else {
            for(colName in xDataItem) {
              if (innerText==='') {
                if ((xDataItem.hasOwnProperty(colName)) &&
                    (colName!=idFieldName) &&
                    (colName!='rowid') &&
                    (colName!='_elementid_')) {
                      innerText=innerText+xDataItem[colName];
                    }
              }
            }

          }

          setNewRowAttributes(entry);
          if (asHTML)
            entry.innerHTML=innerText;
          else
            entry.appendChild(document.createTextNode(innerText));
          if (typeof aLineSpec.beforeElement=='string') {
            var item=y$(aLineSpec.beforeElement);
            oUL.insertBefore(entry, item);
          } else
            oUL.appendChild(entry);

          if (typeof aLineSpec.onNewItem == 'function')
            aLineSpec.onNewItem(aElementID, entry, xDataItem);
        }
      }

    } else if (aElement.nodeName=='LISTBOX') {
      var oListBox = aElement;
      if (aFlags.deleteRows) {
        while(oListBox.childElementCount>0)
          oListBox.childNodes[0].remove();
      }
      var cRow = 0;

      for (j in xData) {
        if (xData.hasOwnProperty(j)) {
          xDataItem=getDataFromXData(xData[j]);
          xDataItem._elementid_ = aElementID;
          newRow   = document.createElement('listitem');
          cNdx = 0;

          if (typeof aLineSpec.columns == 'undefined') {
            if (typeof xDataItem == 'string') {
              _dumpy(2,1,"ERRO: yeapf-dom.js - string cell not implemented");
            } else {
              for(colName in xDataItem) {
                if ((xDataItem.hasOwnProperty(colName)) &&
                    (colName!=idFieldName) &&
                    (colName!='rowid') &&
                    (colName!='_elementid_')) {
                  newCell  = document.createElement('listcell');
                  newCell.innerHTML = xDataItem[colName];
                  newCell.id=aElementID+'_'+cNdx+'_'+cRow;
                  if (typeof aLineSpec.onNewItem == 'function')
                    aLineSpec.onNewItem(aElementID, newCell, xDataItem);
                  cNdx = cNdx + 1;
                  newRow.appendChild(newCell);
                }
              }
            }
          } else {
            for(colName in aLineSpec.columns) {
              if (colName != idFieldName) {
                newCell  = document.createElement('listcell');
                newCell.innerHTML = xDataItem[colName];
                newCell.id=aElementID+'_'+cNdx+'_'+cRow;
                if (typeof aLineSpec.onNewItem == 'function')
                  aLineSpec.onNewItem(aElementID, newCell, xDataItem);
                cNdx = cNdx + 1;
                newRow.appendChild(newCell);
              }
            }
          }
          oListBox.appendChild(newRow);
          cRow++;
        }
      }

    } else if ((aElement.nodeName=='SELECT') || (aElement.nodeName=='DATALIST')) {

      /* Clean options */
      if (aFlags.deleteRows){
        while (aElement.options.length>0)
          aElement.options.remove(0);
      }
      cNdx = 0;
      /* data */
      for (j in xData) {

        if (xData.hasOwnProperty(j)) {
          xDataItem=getDataFromXData(xData[j]);
          xDataItem._elementid_ = aElementID;
          auxHTML = '';
          if (typeof aLineSpec.columns == 'undefined') {
            if (typeof xDataItem == 'string') {
              _dumpy(2,1,"ERRO: yeapf-dom.js - string cell not implemented");
            } else {
              for(colName in xDataItem) {
                if ((xDataItem.hasOwnProperty(colName)) &&
                    (colName!=idFieldName) &&
                    (colName!='rowid') &&
                    (colName!='_elementid_')) {
                  auxHTML = auxHTML + xDataItem[colName];
                }
              }
            }
          } else {
            if (isArray(aLineSpec.columns)) {
              for (c=0; c<aLineSpec.columns.length; c++) {
                auxHTML = auxHTML + xDataItem[aLineSpec.columns[c]] + ' ';
              }
            } else {
              if (typeof xDataItem == 'string') {
                _dumpy(2,1,"ERRO: yeapf-dom.js - string cell not implemented");
              } else {
                for(colName in aLineSpec.columns) {
                  if (colName != idFieldName)
                    auxHTML = auxHTML + xDataItem[colName];
                }
              }
            }
          }

          var opt =  document.createElement('option');
          if (typeof xDataItem[idFieldName] != 'undefined')
            opt.value = xDataItem[idFieldName];
          opt.innerHTML = auxHTML;
          opt.id=aElementID+'_'+cNdx;
          if (aLineSpec.inplaceData) {
            for(c=0;c<aLineSpec.inplaceData.length; c++) {
              if (typeof xDataItem[aLineSpec.inplaceData[c]] !== "undefined") {
                opt.setAttribute("data_"+aLineSpec.inplaceData[c], xDataItem[aLineSpec.inplaceData[c]]);
              }
            }
          }
          if (typeof aLineSpec.onNewItem == 'function')
            aLineSpec.onNewItem(aElementID, opt, xDataItem);
          aElement.appendChild(opt);
          cNdx++;
        }
      }

      if (aElement.onclick)
        aElement.onclick();
    } else if (aElement.nodeName=='FORM') {
      var fieldType,
          valueType,
          editMask,
          storageMask,
          fieldValue,
          fieldName,
          fieldPrefix, fieldPostfix,
          aElements;

      if (aFlags.deleteRows)
        aElements = this.cleanForm(aElementID);
      else
        aElements = this.selectElements(aElementID);

      if (xData)
        if ((typeof xData=='object') || (xData.length === 1)) {
          var yData=getDataFromXData(xData[0] || xData);

          fieldPrefix = aLineSpec.elementPrefixName || aLineSpec.prefix || '';
          fieldPostfix = aLineSpec.elementPostixName || aLineSpec.postfix || '';
          for (i=0; i < aElements.length; i++) {
            /* the less prioritary MASK comes from the html form */
            editMask = aElements[i].getAttribute('editMask');
            storageMask = aElements[i].getAttribute('storageMask');
            valueType = aElements[i].getAttribute('valueType') || 'text';

            /* data comming from the server */
            fieldName = suggestKeyName(yData, aElements[i].name || aElements[i].id, fieldPrefix, fieldPostfix);

            /* column name defined by the programmer on client side */
            colName = (aLineSpec.columns && suggestKeyName(aLineSpec.columns, aElements[i].name || aElements[i].id)) || null;

            if (typeof yData[fieldName] != 'undefined') {
              fieldValue = unmaskHTML(yData[fieldName]);
              fieldType = aElements[i].type.toLowerCase();

              /* only fill field if there is not column definition
               * or if the colName is defined */
              if ((!aLineSpec.columns) || (colName>'')) {
                /* if thete is a colName, pick type and mask from aLineSpec */
                if (colName>'') {
                  if (!isArray(aLineSpec.columns)) {
                    valueType = aLineSpec.columns[colName].type;
                    editMask = (aLineSpec.columns[colName].editMask) || editMask;
                    storageMask = (aLineSpec.columns[colName].storageMask) || storageMask;
                  }
                }

                if (valueType!='text') {
                  if ((editMask>'') && (storageMask>'')) {
                    if (valueType.indexOf('date')>=0) {
                      fieldValue = dateTransform(fieldValue, storageMask, editMask);
                    }
                  } else
                    fieldValue = yAnalise("%"+valueType+"("+fieldValue+")");
                }

                switch(fieldType) {

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
                  case "tel":
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

                    if (aElements[i].value==fieldValue)
                      aElements[i].checked = (aElements[i].value === fieldValue);
                    break;

                  case "select-one":
                  case "select-multi":
                    for(j=0; j < aElements[i].options.length; j++)
                      if (aElements[i].options[j].value==fieldValue)
                        aElements[i].selectedIndex = j;
                    break;
                }

                if (typeof aLineSpec.onNewItem == 'function')
                  aLineSpec.onNewItem(aElementID, aElements[i], yData);

              }
            }

          }
        } else if (xData.length > 1)
          _dump("There are more than one record returning from the server");
    } else if (aElement.nodeName=='DIV') {
      if (aFlags.deleteRows)
        aElement.innerHTML='';
      
      auxHTML=aElement.innerHTML; 

      if (xData) {
        for (j in xData) {
          if (xData.hasOwnProperty(j)) {
            xDataItem=getDataFromXData(xData[j]);
            if (aLineSpec.html) {
              auxHTML=auxHTML+yAnalise(aLineSpec.html, xDataItem);
            } else {
              for(colName in xDataItem) {
                if (xDataItem.hasOwnProperty(colName)) {
                  auxHTML+='<div><div class=tnFieldName><b><small>{0}</small></b></div>{1}'.format(colName, xDataItem[colName]);
                }
              }
            }
          }
        }
        aElement.innerHTML=auxHTML;
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

ycomm.dom.getTableRowId = function(tableId, aRowNo)  {
  var row = ycomm.dom.getRowByRowNo(tableId, aRowNo);

  return row?row.id:null;
};

ycomm.dom.highlightRow = function(tableId, aRowId, highlightClass) {
  highlightClass = highlightClass || '';
  var c;
  aRowId = typeof aRowId=='undefined'?-1:aRowId;
  var table = document.getElementById(tableId);
  if (table) {
    for(var i=0; i<table.rows.length; i++) {
      if (i==aRowId) {
        table.rows[i].addClass(highlightClass);
        for(c=0; c<table.rows[i].cells.length; c++)
          table.rows[i].cells[c].addClass(highlightClass);
      } else {
        table.rows[i].removeClass(highlightClass);
        for(c=0; c<table.rows[i].cells.length; c++)
          table.rows[i].cells[c].removeClass(highlightClass);
      }
    }
  }
};

ycomm.dom.getTableRowInplaceData = function(aRow, fieldName) {
  if (aRow)
    return aRow.getAttribute('data_'+fieldName);
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

ycomm.dom.selectElements = function (aElementId, aFieldListFilter) {
  var aElements = [], knownField, allElements, i, fieldType;

  var aForm = y$(aElementId);
  if (aForm) {
    allElements=aForm.getElementsByTagName('*');
    for (i=0; i<allElements.length; i++) {
      if (allElements[i].type) {
        fieldType = allElements[i].type.toLowerCase();
        knownFieldType = false;

        if (aFieldListFilter) {
          if (aFieldListFilter.indexOf(allElements[i].name || allElements[i].id)<0)
            fieldType='--AVOID--';
        }

        switch(fieldType) {

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

    fieldType = aElement.type?aElement.type.toLowerCase():aElement.nodeName?aElement.nodeName.toLowerCase():'UNKNOWN';
    fieldModified = false;
    if (reservedFields.indexOf(aElement.id)<0) {
      switch(fieldType) {

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
          fieldModified = (aElement.value>'');
          aElement.value = "";
          break;

        case "radio":
        case "checkbox":
          fieldModified = (aElement.checked !== false);
          aElement.checked = false;
          break;

        case "select-one":
        case "select-multi":
          fieldModified = (aElement.selectedIndex>-1);
          aElement.selectedIndex = -1;
          break;
        case "table":
          if (aElement.getElementsByTagName('tbody').length>0)
            aElement = aElement.getElementsByTagName('tbody')[0];
          while(aElement.rows.length>0)
            aElement.deleteRow(aElement.rows.length-1);
          break;
        case "ul":
          while (aElement.firstChild) {
            aElement.removeChild(aElement.firstChild);
          }

          break;
      }
    }
  } else
    _dumpy(2,1,"null element when calling cleanElement()");
};

ycomm.dom.cleanForm = function (aFormId, aFieldList) {
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
  for (i=0; i<aElements.length; i++) {
    ycomm.dom.cleanElement(aElements[i]);
  }
  return aElements;
};

ycomm.dom._scratch = {
  t:  ['Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.',
       'Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui blandit praesent luptatum zzril delenit augue duis dolore te feugait nulla facilisi. Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat.',
       'Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui blandit praesent luptatum zzril delenit augue duis dolore te feugait nulla facilisi.',
       'Nam liber tempor cum soluta nobis eleifend option congue nihil imperdiet doming id quod mazim placerat facer possim assum. Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat.',
       'Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis.',
       'At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, At accusam aliquyam diam diam dolore dolores duo eirmod eos erat, et nonumy sed tempor et et invidunt justo labore Stet clita ea et gubergren, kasd magna no rebum. sanctus sea sed takimata ut vero voluptua. est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat. ',
       'Consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.'],
  d:  [ 'yahu.com', 'hotmayl.com', 'jmail.com', 'yahu.com.nh', 'hotmayl.com.nh', 'jmail.com.nh'],
  p:  [ 'http://', 'https://', 'ws://', 'wss://', 'ftp://'],
  mn: [ 'James', 'John', 'Robert', 'Michael', 'William', 'David', 'Richard', 'Charles'],
  fn: [ 'Mary', 'Patricia', 'Linda', 'Barbara', 'Elizabeth', 'Jennifer', 'Maria', 'Susan'],
  sn: [ 'Smith', 'Jones', 'Taylor', 'Williams', 'Brown', 'Davies', 'Evans', 'Wilson'],
  ch: 'qwertyuiopasdfghjklzxcvbnmQAZWSXEDCRFVTGBYHNUJMIKOLP0123456789'
};

ycomm.dom.testFormWithJunk = function(aFormId) {

  var aElements = this.selectElements(aFormId), 
      i, fieldType, fieldId, fieldValue, maxLength, classes;


  var genString = function(base, minLen, maxLen) {
    var ret='', n;
    maxLen=Math.floor((Math.random() * maxLen) + minLen);
    while (ret.length<maxLen) {
      n=Math.floor((Math.random() * base.length));
      ret+=base[n];
    }    
    return ret;
  };

  var genNumber = function(min, max) {
    return Math.floor((Math.random() * max) + min);
  }

  var classHasName = function (name) {
    var ret=false;
    name=name.toUpperCase();
    foreach(var c=0; c<classes.length; c++)  {
      ret=ret || (classes[c].indexOf(name)>=0);
    }
  return ret;
  }

  for(i=0; i<aElements.length; i++) {
    fieldType  = aElements[i].type.toLowerCase();
    fieldId    = aElements[i].id;
    maxLength  = aElements[i].getAttribute("maxlength") || 100;
    classes    = aElements[i].classList;
    
    for (var n=0; n<classes.length; n++)
      classes[n]=classes[n].toUpperCase();

    fieldValue = '';
    if (fieldId) {
      switch(fieldType) {
        case "password":
          fieldValue=genString(ycomm.dom._scratch.ch,6,15);
          break;
        case "textarea":
          fieldValue=genString(ycomm.dom._scratch.t,1,15 * maxLength);
          break;
        case "email":
          fieldValue=genString(ycomm.dom._scratch.mn,1,2)+"@"+genString(ycomm.dom._scratch.d, 1, 1);
          break;
        case "date":
          fieldValue="{0}-{1}-{2}".format(genNumber(1900,2050), genNumber(1,12), genNumber(1,28));
          break;
          
        case "color":
        case "datetime":
        case "datetime-local":
        case "month":
          fieldValue=genNumber(1,12);
          break;
        case "number":
        case "range":
          fieldValue=genNumber(1,100);
          break;
        case "search":
        case "tel":
        case "time":
        case "week":
          fieldValue=genNumber(1,52);
          break;
        case "url":
          fieldValue=genString(ycomm.dom._scratch.p, 1, 1)+genString(ycomm.dom._scratch.d, 1, 1)+".xyz";
          break;     

        case "radio":
        case "checkbox":
          break;

        case "select-one":
        case "select-multi":
         break;

        case "hidden":
        case "text":
        default:
          if (classHasName('cpf')) {
            fieldValue=fieldValue.gerarCPF();
          } else if (classHasName('cnpj')) {
            fieldValue=fieldValue.gerarCNPJ();
          } else {
            fieldValue=genString(ycomm.dom._scratch.t,1,maxLength);
          }
          fieldValue=fieldValue.substr(0, maxLength);
          break;
      }

      y$(fieldId).value=fieldValue;
    }
  }
};

/*
 * get all the elements of the form and returns a JSON
 * https://developer.mozilla.org/en-US/docs/Web/Guide/HTML/Forms/Data_form_validation
 * http://www.the-art-of-web.com/html/html5-form-validation/
 */
ycomm.dom.getFormElements = function (aFormId, aLineSpec, aOnReady) {
  aLineSpec=aLineSpec || {};
  var fieldPrefix = aLineSpec.elementPrefixName || aLineSpec.prefix || '';
  var fieldPostfix = aLineSpec.elementPostixName || aLineSpec.postfix || '';

  var ret = {},
      aElements = this.selectElements(aFormId),
      fieldName, fieldType, fieldValue,
      editMask,
      storageMask,
      valueType,
      busyCount=0,
      canChangeRetValue;

  for (var i=0; i<aElements.length; i++) {
    if (aElements[i].getAttribute) {
      editMask = aElements[i].getAttribute('editMask') || '';
      storageMask = aElements[i].getAttribute('storageMask') || '';
      valueType = aElements[i].getAttribute('valueType') || 'text';
    } else {
      editMask='';
      storageMask='';
      valueType='text';
    }
    canChangeRetValue = true;

    fieldType = aElements[i].type.toLowerCase();
    fieldName = aElements[i].name || aElements[i].id;

    if ((fieldName.substr(fieldName.length, -(fieldPostfix.length)) == fieldPostfix) &&
        (fieldName.substr(0,fieldPrefix.length)==fieldPrefix)) {

      fieldName=fieldName.substr(fieldPrefix.length);
      fieldName=fieldName.substr(0,fieldName.length - (fieldPostfix.length));

      if (fieldName>'') {
        fieldValue = '';

        if ( (fieldType=='radio') ||
             (fieldType=='checkbox')  )  {
          canChangeRetValue = false;
          if (typeof ret[fieldName] == 'undefined')
            ret[fieldName]='';
        }

        switch(fieldType) {

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
          case "tel":
          case "time":
          case "url":
          case "week":
            fieldValue = aElements[i].value+"";
            if ((editMask>'') && (storageMask>'')) {
              if (valueType.indexOf('date')>=0) {
                fieldValue = dateTransform(fieldValue, editMask, storageMask);
                fieldValue = fieldValue?fieldValue+"":"";
              }
            }
            break;

          case "radio":
          case "checkbox":
            fieldValue = aElements[i].checked?aElements[i].value:'';
            canChangeRetValue=(fieldValue!=='');
            break;

          case "select-one":
          case "select-multi":
            fieldValue = aElements[i].selectedIndex;
            if (aElements[i].options[fieldValue])
              fieldValue = aElements[i].options[fieldValue].value;
            break;

          case "file":
            if (typeof aOnReady=='function') {
              /*
              http://stackoverflow.com/questions/12090996/waiting-for-a-file-to-load-onload-javascript
              http://stackoverflow.com/questions/6978156/get-base64-encode-file-data-from-input-form
              http://igstan.ro/posts/2009-01-11-ajax-file-upload-with-pure-javascript.html
              https://developer.tizen.org/dev-guide/web/2.3.0/org.tizen.mobile.web.appprogramming/html/tutorials/w3c_tutorial/comm_tutorial/upload_ajax.htm
              */
              var reader=new FileReader();
              busyCount++;
              reader._fieldName=fieldName;
              reader.addEventListener("load", function() {
                ret[this._fieldName]=this.result;
                busyCount--;
                if (busyCount<=0) {
                  aOnReady(ret);
                }
              });
              reader.readAsDataURL(aElements[i].files[0]); 
              canChangeRetValue=false;           
            } else
              fieldValue="aOnReady() not present in js call to getFormElements()";
            break;
        }
        if (typeof fieldValue=='string') {
          if (isNumber(fieldValue))
            fieldValue=fieldValue.toFloat();
          else { 
            if (fieldValue.indexOf(',')>=0)
              fieldValue = encodeURIComponent(fieldValue);
          }
        }

        if (canChangeRetValue)
          ret[fieldName] = fieldValue;
      }
    }
  }

  return ret;
};

/* add an element to an existent form */
ycomm.dom.addFormElement = function (aForm, aTagName, aElementAttributes) {
  var aNewElement = document.createElement(aTagName);
  for(var i in aElementAttributes)
    if (aElementAttributes.hasOwnProperty(i))
      aNewElement.setAttribute(i,aElementAttributes[i]);
  aForm.appendChild(aNewElement);
  return aNewElement;
};

ycomm.dom.URL2post = function (aURL, aTarget, aWindow) {
  if (aURL !== undefined) {
    setTimeout(function () {
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
      if (aURL.length==2) {
        aAction = aURL[0];
        aURL = aURL[1];
      } else
        aURL = aURL[0];
      /* get the parameters */
      aURL = aURL.split('&');

      /* create the temporary form */
      aWindow.auxForm = aWindow.document.createElement("form");
      aWindow.document.body.appendChild(aWindow.auxForm);
      aWindow.auxForm.setAttribute('method','post');
      aWindow.auxForm.setAttribute('action',aAction);
      aWindow.auxForm.setAttribute('target',aTarget);
      for(var i=0; i<aURL.length; i++) {
        var value = aURL[i].split('=');
        if (value.length==1)
          value[1]='';
        ycomm.dom.addFormElement(aWindow.auxForm, 'input', {
                                   'type': 'hidden',
                                   'id': value[0],
                                   'name': value[0],
                                   'value': value[1] });
      }
      aWindow.auxForm.submit();
    }, 1000);
  }
};

ycomm.dom.deleteFieldClass = function (aElementList, aClassName) {
    for(var i in aElementList)
      if (aElementList.hasOwnProperty(i)) {
        y$(i).deleteClass(aClassName);
      }
};

ycomm.dom.viewport = function () {
  var e = window,
      a = 'inner';
  while (e.parent != e)
    e = e.parent;
  if ( !( 'innerWidth' in window ) ) {
    a = 'client';
    e = document.documentElement || document.body;
  }
  return { width : e[ a+'Width' ] , height : e[ a+'Height' ] };
};

ycomm.dom.getLeft = function( oElement ) {
    var iReturnValue = 0;
    while( oElement!==null ) {
      iReturnValue += oElement.offsetLeft;
      oElement = oElement.offsetParent;
    }
    return iReturnValue;
};

ycomm.dom.getTop = function( oElement ) {
    var iReturnValue = 0;
    while( oElement !== null ) {
      iReturnValue += oElement.offsetTop;
      oElement = oElement.offsetParent;
    }
    return iReturnValue;
};
