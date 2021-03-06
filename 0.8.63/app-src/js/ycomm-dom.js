    /*********************************************
     * app-src/js/ycomm-dom.js
     * YeAPF 0.8.63-259 built on 2020-02-26 11:44 (-3 DST)
     * Copyright (C) 2004-2020 Esteban Daniel Dortta - dortta@yahoo.com - MIT License
     * 2020-02-26 11:39:25 (-3 DST)
     * First Version (C) 2014 - esteban daniel dortta - dortta@yahoo.com
     **********************************************/
    //# sourceURL=app-src/js/ycomm-dom.js

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
    ycomm.dom.fillElement = function(aElementID, xData, aLineSpec,
      aFlags) {
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
        first_time = typeof ycomm.dom._elem_templates[aElementID] ==
        "undefined";

      /* grants filledEvent exists */
      if ("undefined" == typeof window._evtFilled) {
        window._evtFilled = window.createDOMEvent("filled");
      }

      if (aElement) {

        idFieldName = aLineSpec.idFieldName || aElement.getAttribute(
          "data-id-fieldname") || 'id';
        aElement.setAttribute("data-id-fieldname", idFieldName);
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
              if (typeof xDataItem[aLineSpec.inplaceData[c]] !==
                "undefined") {
                var colName = aLineSpec.inplaceData[c];
                opt.setAttribute("data-" + colName, (xDataItem[
                  colName] || ''));
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
              aNewRow.style.backgroundColor = rowColorSpec.suggestRowColor(
                rowGroup);
          }
          if (xDataItem[idFieldName]) {
            if (y$(xDataItem[idFieldName])) {
              auxIdSequence = 0;
              while (y$(xDataItem[idFieldName] + '_' +
                  auxIdSequence))
                auxIdSequence++;
              aNewRow.id = xDataItem[idFieldName] + '_' +
                auxIdSequence;
            } else
              aNewRow.id = xDataItem[idFieldName];
          }

          saveInplaceData(aNewRow, xDataItem);

          if ((aLineSpec.onClick) || (aLineSpec.onSelect)) {
            aNewRow.addEventListener('click', ((aLineSpec.onClick) ||
              (aLineSpec.onSelect)), false);
          }
        };

        var addCell = function(colName) {
          if (colName != idFieldName) {
            var newCell = newRow.insertCell(cNdx),
              xDataItem = getDataFromXData(xData[j]),
              aNewCellValue = colName !== null ? unmaskHTML((
                xDataItem[colName] || '')) : unmaskHTML(xDataItem);

            if ((aLineSpec.columns) && (aLineSpec.columns[colName])) {
              if (aLineSpec.columns[colName].align)
                newCell.style.textAlign = aLineSpec.columns[colName]
                .align;
              if (aLineSpec.columns[colName].type) {
                aNewCellValue = yAnalise('%' + aLineSpec.columns[
                  colName].type + '(' + aNewCellValue + ')');
              }
            }

            if (!canCreateRow) {
              newCell.addClass('warning');
              /* newCell.style.borderLeft = 'solid 1px red'; */
            }

            newCell.innerHTML = aNewCellValue.length === 0 ?
              '&nbsp;' : aNewCellValue;
            newCell.style.verticalAlign = 'top';
            newCell.id = aElementID + '_' + cNdx + '_' + oTable.rows
              .length;
            newCell.setAttribute('colName', colName);
            if (typeof aLineSpec.onNewItem == 'function')
              aLineSpec.onNewItem(aElementID, newCell, xDataItem);
            cNdx = cNdx + 1;
          }
        };

        var oTable, auxHTML, j, c, cNdx, i, newCell, internalRowId =
          (new Date()).getTime() - 1447265735470,
          xDataItem;


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
            if (typeof(aLineSpec.columns || aLineSpec.rows ||
                aLineSpec.html) == "undefined") {
              ycomm.dom._elem_templates[aElementID] = {};
              if (oTable.rows.length > 0) {
                ycomm.dom._elem_templates[aElementID].rows = [];
                for (i = 0; i < oTable.rows.length; i++)
                  ycomm.dom._elem_templates[aElementID].rows[i] =
                  trim(oTable.rows[i].innerHTML + "").replace(
                    /\ \s+/g, '');
              }
            } else {
              ycomm.dom._elem_templates[aElementID] = {};
              ycomm.dom._elem_templates[aElementID].columns =
                aLineSpec.columns;
              ycomm.dom._elem_templates[aElementID].rows = aLineSpec.rows;
              ycomm.dom._elem_templates[aElementID].html = aLineSpec.html;
            }
          }
          mergeObject(ycomm.dom._elem_templates[aElementID],
            aLineSpec, true);

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
                    ((canCreateRow) && (i < oTable.rows.length)); i++
                  ) {
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
              xDataItem.rowid = ((!aFlags.insertAtTop) && (typeof newRow
                  .rowIndex !== "undefined")) ? newRow.rowIndex :
                internalRowId + '';
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
                  newCell.innerHTML = yAnalise(aLineSpec.html,
                    xDataItem);
                  newCell.style.verticalAlign = 'top';
                  newCell.id = aElementID + '_' + cNdx + '_' + oTable
                    .rows.length;
                  if (typeof aLineSpec.onNewItem == 'function')
                    aLineSpec.onNewItem(aElementID, newCell,
                      xDataItem);

                } else if (typeof aLineSpec.rows != 'undefined') {
                  var firstRow = true;
                  for (r = 0; r < aLineSpec.rows.length; r++) {
                    if (!firstRow) {
                      newRow = oTable.insertRow(oTable.rows.length);
                      setNewRowAttributes(newRow);
                    }
                    newRow.innerHTML = yAnalise(aLineSpec.rows[r],
                      xDataItem);
                    if (!canCreateRow) {
                      if (aFlags.deleteRows)
                        for (c = 0; c < newRow.cells.length; c++)
                          newRow.cells[c].style.borderLeft =
                          'solid 1px red';
                    }
                    if (typeof aLineSpec.onNewItem == 'function')
                      aLineSpec.onNewItem(aElementID, newRow,
                        xDataItem);
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
            if (typeof(aLineSpec.columns || aLineSpec.rows ||
                aLineSpec.html) == "undefined") {
              ycomm.dom._elem_templates[aElementID] = {};
              if (aElement.children.length > 0) {
                ycomm.dom._elem_templates[aElementID].rows = [];
                for (i = 0; i < aElement.children.length; i++)
                  ycomm.dom._elem_templates[aElementID].rows[i] =
                  trim(aElement.children[i].innerHTML + "").replace(
                    /\ \s+/g, '');
              }
            } else {
              ycomm.dom._elem_templates[aElementID] = {};
              ycomm.dom._elem_templates[aElementID].columns =
                aLineSpec.columns;
              ycomm.dom._elem_templates[aElementID].rows = aLineSpec.rows;
              ycomm.dom._elem_templates[aElementID].html = aLineSpec.html;
            }
          }
          mergeObject(ycomm.dom._elem_templates[aElementID],
            aLineSpec, true);

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
                  innerText = innerText + yAnalise(aLineSpec.rows[r],
                    xDataItem) + "";
                }
                asHTML = true;
              } else if (typeof aLineSpec.html == 'string') {
                innerText = innerText + yAnalise(aLineSpec.html,
                  xDataItem) + "";
                asHTML = true;
              } else {
                for (colName in xDataItem) {
                  if (innerText === '') {
                    if ((xDataItem.hasOwnProperty(colName)) &&
                      (colName != idFieldName) &&
                      (colName != 'rowid') &&
                      (colName != '_elementid_')) {
                      innerText = innerText + (xDataItem[colName] ||
                        '');
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
          if (first_time) {
            if (typeof(aLineSpec.columns || aLineSpec.rows ||
                aLineSpec.html) == "undefined") {
              ycomm.dom._elem_templates[aElementID] = {};
              if (oListBox.options.length > 0) {
                ycomm.dom._elem_templates[aElementID].rows = [];
                for (i = 0; i < oListBox.options.length; i++)
                  ycomm.dom._elem_templates[aElementID].rows[i] =
                  trim(oListBox.options[i].innerHTML + "").replace(
                    /\ \s+/g, '');
              }
            } else {
              ycomm.dom._elem_templates[aElementID] = {};
              ycomm.dom._elem_templates[aElementID].columns =
                aLineSpec.columns;
              ycomm.dom._elem_templates[aElementID].rows = aLineSpec.rows;
              ycomm.dom._elem_templates[aElementID].html = aLineSpec.html;
            }
          }
          mergeObject(ycomm.dom._elem_templates[aElementID],
            aLineSpec, true);

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
                  _dumpy(2, 1,
                    "ERRO: yeapf-dom.js - string cell not implemented"
                  );
                } else {
                  for (colName in xDataItem) {
                    if ((xDataItem.hasOwnProperty(colName)) &&
                      (colName != idFieldName) &&
                      (colName != 'rowid') &&
                      (colName != '_elementid_')) {
                      newCell = document.createElement('listcell');
                      newCell.innerHTML = (xDataItem[colName] || '');
                      newCell.id = aElementID + '_' + cNdx + '_' +
                        cRow;
                      if (typeof aLineSpec.onNewItem == 'function')
                        aLineSpec.onNewItem(aElementID, newCell,
                          xDataItem);
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
                      aLineSpec.onNewItem(aElementID, newCell,
                        xDataItem);
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


        } else if ((aElement.nodeName == 'SELECT') || (aElement.nodeName ==
            'DATALIST')) {
          if (first_time) {
            if (typeof(aLineSpec.columns || aLineSpec.rows ||
                aLineSpec.html) == "undefined") {
              ycomm.dom._elem_templates[aElementID] = {};
              if (aElement.options.length > 1) {
                ycomm.dom._elem_templates[aElementID].rows = [];
                for (i = 0; i < aElement.options.length; i++)
                  ycomm.dom._elem_templates[aElementID].rows[i] =
                  trim(aElement.options[i].innerHTML + "").replace(
                    /\ \s+/g, '');
              } else {
                if (aElement.options.length == 1) {
                  ycomm.dom._elem_templates[aElementID].html = trim(
                    aElement.options[0].outerHTML + "").replace(
                    /\ \s+/g, '');
                }
              }
            } else {
              ycomm.dom._elem_templates[aElementID] = {};
              ycomm.dom._elem_templates[aElementID].columns =
                aLineSpec.columns;
              ycomm.dom._elem_templates[aElementID].rows = aLineSpec.rows;
              ycomm.dom._elem_templates[aElementID].html = aLineSpec.html;
            }
          }
          mergeObject(ycomm.dom._elem_templates[aElementID],
            aLineSpec, true);

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
              if (typeof aLineSpec.html !== 'undefined') {
                auxHTML = yAnalise(aLineSpec.html, xDataItem);
              } else if (typeof aLineSpec.columns != 'undefined') {
                var sep = aLineSpec.sep || '';
                if (isArray(aLineSpec.columns)) {
                  for (c = 0; c < aLineSpec.columns.length; c++) {
                    if (auxHTML > '')
                      auxHTML += sep;
                    auxHTML = auxHTML + (xDataItem[aLineSpec.columns[
                      c]] || '');
                  }
                } else {
                  if (typeof xDataItem == 'string') {
                    _dumpy(2, 1,
                      "ERRO: yeapf-dom.js - string cell not implemented"
                    );
                  } else {
                    for (colName in aLineSpec.columns) {
                      if (colName != idFieldName)
                        auxHTML = auxHTML + (xDataItem[colName] || '') +
                        sep;
                    }
                  }
                }
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

              var opt = document.createElement('option');
              if (typeof xDataItem[idFieldName] != 'undefined') {
                if (aElement.nodeName == 'DATALIST') {
                  opt.setAttribute('data-' + idFieldName, xDataItem[
                    idFieldName]);
                } else {
                  opt.value = xDataItem[idFieldName];
                }
              }
              if (typeof aLineSpec.html == 'undefined')
                opt.innerHTML = auxHTML;
              opt.id = aElementID + '_' + cNdx;
              saveInplaceData(opt, xDataItem);

              if (typeof aLineSpec.onNewItem == 'function')
                aLineSpec.onNewItem(aElementID, opt, xDataItem);
              aElement.appendChild(opt);
              if (typeof aLineSpec.html !== 'undefined')
                opt.outerHTML = auxHTML;
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
              var classes = aElements[elemNdx].className.toUpperCase();
              ret = ret || (classes.indexOf(name) >= 0);
            }
            return ret;
          };

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

              fieldPrefix = aLineSpec.elementPrefixName || aLineSpec.prefix ||
                aElement.getAttribute('data-prefix') || '';
              fieldPostfix = aLineSpec.elementPostixName || aLineSpec
                .postfix || aElement.getAttribute('data-postfix') ||
                '';
              for (i = 0; i < aElements.length; i++) {
                /* the less prioritary MASK comes from the html form */
                editMask = aElements[i].getAttribute('data-edit-mask') ||
                  aElements[i].getAttribute('editMask');
                storageMask = aElements[i].getAttribute(
                  'data-storage-mask') || aElements[i].getAttribute(
                  'storageMask');
                valueType = aElements[i].getAttribute(
                  'data-value-type') || aElements[i].getAttribute(
                  'valueType') || 'text';

                /* data comming from the server */
                fieldName = suggestKeyName(yData, aElements[i].name ||
                  aElements[i].id, fieldPrefix, fieldPostfix);

                /* column name defined by the programmer on client side */
                colName = (aLineSpec.columns && suggestKeyName(
                  aLineSpec.columns, aElements[i].name ||
                  aElements[i].id)) || null;

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
                        editMask = (aLineSpec.columns[colName].editMask) ||
                          editMask;
                        storageMask = (aLineSpec.columns[colName].storageMask) ||
                          storageMask;
                      }
                    }

                    if (valueType != 'text') {
                      if ((editMask > '') && (storageMask > '')) {
                        if (valueType.indexOf('date') >= 0) {
                          fieldValue = dateTransform(fieldValue,
                            storageMask, editMask) || '';
                        }
                      } else
                        fieldValue = yAnalise("%" + valueType + "(" +
                          fieldValue + ")");
                    }

                    switch (fieldType) {

                      case "tel":
                        aElements[i].value = ("" + fieldValue).asPhone();
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
                          aElements[i].checked = (aElements[i].value ===
                            fieldValue);
                        break;

                      case "select-one":
                      case "select-multi":
                        for (j = 0; j < aElements[i].options.length; j++)
                          if (aElements[i].options[j].value ==
                            fieldValue)
                            aElements[i].selectedIndex = j;
                        break;
                    }

                    if (classHasName(i, "cpf")) {
                      aElements[i].value = ("" + fieldValue).asCPF();
                    }

                    if (classHasName(i, "cnpj")) {
                      aElements[i].value = ("" + fieldValue).asCNPJ();
                    }

                    if (classHasName(i, "rg")) {
                      aElements[i].value = ("" + fieldValue).asRG();
                    }

                    if (classHasName(i, "cep")) {
                      aElements[i].value = ("" + fieldValue).asCEP();
                    }

                    if (typeof aLineSpec.onNewItem == 'function')
                      aLineSpec.onNewItem(aElementID, aElements[i],
                        yData);

                  }
                }

              }

              aElement.dispatchEvent(window._evtFilled);

            } else if (xData.length > 1)
            _dump(
              "There are more than one record returning from the server"
            );

        } else if (aElement.nodeName == 'DIV') {
          if (first_time) {
            if (typeof(aLineSpec.columns || aLineSpec.rows ||
                aLineSpec.html) == "undefined") {
              ycomm.dom._elem_templates[aElementID] = {};
              ycomm.dom._elem_templates[aElementID].html = aElement.innerHTML;
            } else {
              ycomm.dom._elem_templates[aElementID] = {};
              ycomm.dom._elem_templates[aElementID].columns =
                aLineSpec.columns;
              ycomm.dom._elem_templates[aElementID].rows = aLineSpec.rows;
              ycomm.dom._elem_templates[aElementID].html = aLineSpec.html;
            }
          }
          mergeObject(ycomm.dom._elem_templates[aElementID],
            aLineSpec, true);


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
                  auxHTML = auxHTML + yAnalise(aLineSpec.html,
                    xDataItem);
                } else {
                  for (colName in xDataItem) {
                    if (xDataItem.hasOwnProperty(colName)) {
                      auxHTML +=
                        '<div><div class=tnFieldName><b><small>{0}</small></b></div>{1}'
                        .format(colName, (xDataItem[colName] || ''));
                    }
                  }
                }
              }
            }
            aElement.innerHTML = auxHTML;

            aElement.dispatchEvent(window._evtFilled);

          }
        }
      } else {
        console.error("Element '{0}' not found".format(aElementID));
      }
    };

    /* @REVIEW 20190805
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
        return aRow.getAttribute('data-' + fieldName) || aRow.getAttribute(
          fieldName);
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
              if (aFieldListFilter.indexOf(allElements[i].name ||
                  allElements[i].id) < 0)
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

        fieldType = aElement.type ? aElement.type.toLowerCase() :
          aElement.nodeName ? aElement.nodeName.toLowerCase() :
          'UNKNOWN';
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
            case "email":
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
      t: [
        'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.',
        'Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui blandit praesent luptatum zzril delenit augue duis dolore te feugait nulla facilisi. Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat.',
        'Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui blandit praesent luptatum zzril delenit augue duis dolore te feugait nulla facilisi.',
        'Nam liber tempor cum soluta nobis eleifend option congue nihil imperdiet doming id quod mazim placerat facer possim assum. Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat.',
        'Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis.',
        'At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, At accusam aliquyam diam diam dolore dolores duo eirmod eos erat, et nonumy sed tempor et et invidunt justo labore Stet clita ea et gubergren, kasd magna no rebum. sanctus sea sed takimata ut vero voluptua. est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat. ',
        'Consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.'
      ],
      d: ['yahu.com', 'hotmayl.com', 'jmail.com', 'yahu.com.nh',
        'hotmayl.com.nh', 'jmail.com.nh'
      ],
      p: ['http://', 'https://', 'ws://', 'wss://', 'ftp://'],
      mn: ['James', 'John', 'Robert', 'Michael', 'William', 'David',
        'Richard', 'Charles'
      ],
      fn: ['Mary', 'Patricia', 'Linda', 'Barbara', 'Elizabeth',
        'Jennifer', 'Maria', 'Susan'
      ],
      sn: ['Smith', 'Jones', 'Taylor', 'Williams', 'Brown', 'Davies',
        'Evans', 'Wilson'
      ],
      ch: 'qwertyuiopasdfghjklzxcvbnmQAZWSXEDCRFVTGBYHNUJMIKOLP0123456789',
      n: '0123456789'
    };

    ycomm.dom.testFormWithJunk = function(aFormId) {

      var aElements = this.selectElements(aFormId),
        i, fieldType, fieldId, fieldValue, maxLength, classes;


      var genString = function(base, minLen, maxLen, sep) {
        sep = sep || '';
        var ret = '',
          n, j;
        maxLen = Math.floor((Math.random() * maxLen) + minLen);
        j = 0;
        while (j < maxLen) {
          n = Math.floor((Math.random() * base.length));
          ret += base[n] + sep;
          j++;
        }
        return ret;
      };

      var genNumber = function(min, max, leftPaddingLen) {
        leftPaddingLen = leftPaddingLen || 0;
        var ret = '' + Math.floor((Math.random() * (max - min) +
          min));
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
              fieldValue = genString(ycomm.dom._scratch.t, 1, 15 *
                maxLength, ' ');
              break;
            case "email":
              fieldValue = genString(ycomm.dom._scratch.mn, 2, 3) +
                "@" + genString(ycomm.dom._scratch.d, 1, 1);
              break;
            case "date":
              fieldValue = 1 * genNumber(-2208981600000,
                2556064800000);
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
              fieldValue = genString(ycomm.dom._scratch.p, 1, 1) +
                genString(ycomm.dom._scratch.d, 1, 1) + ".xyz";
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
              } else if (classHasName('name')) {
                fieldValue = genString(ycomm.dom._scratch.mn, 1, 2,
                  ' ') + " " + genString(ycomm.dom._scratch.sn, 1,
                  2, ' ');
              } else if (classHasName('zip')) {
                /* http://www.mapanet.eu/en/resources/Postal-Format.asp */
                fieldValue = genNumber(0, 99999, 5);
                fieldValue += '-' + genNumber(0, 9999, 4);
              } else {
                fieldValue = genString(ycomm.dom._scratch.t, 1,
                  maxLength);
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
        var fieldPrefix = aLineSpec.elementPrefixName || aLineSpec.prefix ||
          form.getAttribute('data-prefix') || '';
        var fieldPostfix = aLineSpec.elementPostixName || aLineSpec.postfix ||
          form.getAttribute('data-postfix') || '';
        for (var i = 0; i < aElements.length; i++) {
          if (aElements[i].getAttribute) {
            editMask = aElements[i].getAttribute('data-edit-mask') ||
              aElements[i].getAttribute('editMask');
            storageMask = aElements[i].getAttribute(
              'data-storage-mask') || aElements[i].getAttribute(
              'storageMask');
            valueType = aElements[i].getAttribute('data-value-type') ||
              aElements[i].getAttribute('valueType') || 'text';
          } else {
            editMask = '';
            storageMask = '';
            valueType = 'text';
          }
          canChangeRetValue = true;

          fieldType = aElements[i].type.toLowerCase();
          fieldName = aElements[i].name || aElements[i].id;

          if ((fieldName.substr(fieldName.length, -(fieldPostfix.length)) ==
              fieldPostfix) &&
            (fieldName.substr(0, fieldPrefix.length) == fieldPrefix)) {

            fieldName = fieldName.substr(fieldPrefix.length);
            fieldName = fieldName.substr(0, fieldName.length - (
              fieldPostfix.length));

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
                      fieldValue = dateTransform(fieldValue, editMask,
                        storageMask);
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
                  fieldValue = aElements[i].checked ? aElements[i].value :
                    '';
                  canChangeRetValue = (fieldValue !== '');
                  break;

                case "select-one":
                case "select-multi":
                  fieldValue = aElements[i].selectedIndex;
                  if (aElements[i].options[fieldValue]) {
                    var aux_idFieldName = aElements[i].parentNode.getAttribute(
                      "data-id-fieldname") || "id";
                    fieldValue = aElements[i].options[fieldValue].getAttribute(
                      aux_idFieldName) || aElements[i].options[
                      fieldValue].value;
                    delete aux_idFieldName;
                  }
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
                    fieldValue =
                    "aOnReady() not present in js call to getFormElements()";
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
    ycomm.dom.addFormElement = function(aForm, aTagName,
      aElementAttributes) {
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
      for (var lx = 0, ly = 0; oElement !== null; oElement = oElement
        .offsetParent) {
        lx += oElement.offsetLeft;
        ly += oElement.offsetTop;
      }
      return {
        x: lx,
        y: ly
      };
    };