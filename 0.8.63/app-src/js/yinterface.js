/*
    app-src/js/yinterface.js
    YeAPF 0.8.63-242 built on 2019-11-29 09:22 (-2 DST)
    Copyright (C) 2004-2019 Esteban Daniel Dortta - dortta@yahoo.com - MIT License
    2019-09-09 08:27:18 (-2 DST)
*/

var yInterfaceObj = function() {
  var that = {};

  that._tableALineSpec = [];

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
        while ( (e) &&
                !((e.getAttribute('data-tab') !== null) ||
                (e.nodeName == 'LI')) )
          e = e.parentNode;
        if (!e.hasClass("disabled")) {
          e.addClass('active');
          var tab = e.getAttribute('data-tab');
          if ("string" == typeof tab)
            mTabNav.showTab(tab);
          else {
            console.warn((e.id || "unidentified") + " does not has data-tab attribute");
          }
        }
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
                d[0].cc = d[0].cc || d[0].CC || 0;
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

        for (n = 1; n < 1 + pageCount;) {
          aTag = document.createElement('A');
          aTag.setAttribute('href', '#' + subject);
          aTag.setAttribute('class', 'page-' + subject + " page-link");
          aTag.setAttribute('data-subject', subject);
          aTag.setAttribute('data-table', tableId);
          aTag.setAttribute('data-pageNo', n);
          aTag.innerHTML = n;
          newLI = document.createElement('LI');
          newLI.addClass('page-item');
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

  that.initializePagination = function(subject, tableId, paginatorId, pageLen, params, aLineSpec) {
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

      that._tableALineSpec[tableId] = aLineSpec;
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
        tableId = e.getAttribute('data-table'),
        aLineSpec = {};
    if (tableId)
      aLineSpec = that._tableALineSpec[tableId];
    that.loadTablePage(e.getAttribute('data-subject'), pageNo, tableId, null, aLineSpec);
  };

  that.loadTablePage = function(subject, pageNo, tableId, params, aLineSpec) {
    tableId = tableId || "tbl-" + subject;
    pageNo = (pageNo === -1) ? pageNo : Math.max(1, pageNo || 0);
    aLineSpec = aLineSpec || {};

    var table = y$(tableId),
      tableParams = {},
      pageLen, pageCount, pageStep,
      dataPaginator, paginatorId, n,
      promiseReturn;

    if (table) {
      pageStep = table.getAttribute("data-pageStep") || 0;
      pageCount = table.getAttribute("data-pageCount") || 0;
      paginatorId = table.getAttribute("data-paginator");
      dataPaginator = (y$(paginatorId) || { children: [] }).children;

      ycomm.dom.fillElement(tableId, {});

      if (pageNo === -1)
        pageNo = pageCount;

      if (pageStep > 1) {
        that.mountPagesIndex(tableId, pageNo);
      } else {
        for (n = 0; n < dataPaginator.length; n++) {
          dataPaginator[n].deleteClass('active');
        }
        if (dataPaginator[pageNo - 1])
          dataPaginator[pageNo - 1].addClass('active');
      }

      tableParams = JSON.parse(table.getAttribute('data-params') || '{}');
      pageLen = tableParams.pageLen;
      if (!pageLen) {
        console.warn("Call initializePagination() first");
        that.initializePagination(subject, tableId,  paginatorId, null, params, aLineSpec);
      }

      // tableParams.currentPage=pageNo;

      pageLen = Math.max(1, pageLen || 20);
      /* curo os parámetros e misturo com os params que tenho */
      mergeObject(params || {}, tableParams, true);

      table.setAttribute('data-params', JSON.stringify(tableParams));
      table.setAttribute('data-subject', subject);
      table.setAttribute('data-currentPage', pageNo);

      promiseReturn = new Promise(
        function(resolve, reject) {
          /* esta função serve para puxar apenas uma página dos dados do banco para dentro de uma tabela
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
                  var flags = {
                    deleteRows: blockStart == 0
                  };
                  ycomm.dom.fillElement(tableId, data, aLineSpec, flags);
                  if ((dataContext) && ((dataContext.rowCount >= dataContext.requestedRows))) {
                    blockStart += dataContext.rowCount;
                    if ((dataContext.rowCount > 0) && ((pageNo - 1) * pageLen + blockStart < blockSize)) {
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

  that.loadTable = function(subject, tableId, params, aLineSpec) {
    /* curo o nome da tabela */
    tableId = tableId || "tbl-" + subject;
    aLineSpec  = aLineSpec || {};

    var table = y$(tableId),
      tableParams = {};

    if (table)
      tableParams = JSON.parse(table.getAttribute('data-params') || '{}');

    /* curo os parámetros e misturo com os params que tenho */
    mergeObject(params || {}, tableParams, true);

    table.setAttribute('data-params', JSON.stringify(tableParams));
    table.setAttribute('data-subject', subject);

    var promiseReturn = new Promise(
      function(resolve, reject) {
        /* esta função serve para puxar TODOS os dados do banco para dentro de uma tabela
           então use apenas com aquelas tabelas relativamente pequenas */
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
                ycomm.dom.fillElement(tableId, data, aLineSpec, {
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
        /* coloco os valores padrão que vem do params */
        ycomm.dom.fillElement(table_form, [JSON.parse(table_params)]);

        /* enumero os elementos para poder limpar sua condição de erro */
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
              console.log(formId + " chegou ");
              if ("undefined" == typeof that._forms_cache)
                that._forms_cache = [];
              that._forms_cache[subject + '.' + formId] = d[0];
              that.buildFormFromJSON(manager, formId, d[0], fieldPrefix, fieldPostfix).then(callback);
            } else
              console.error("'{0}' não está devolve resultado ao fazer 'getForm'".format(subject));
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
    var __log = that.logFunction || (false) ? console.log : null;
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
      /* acrescento o id do próprio elemento caso exista */
      if ((e.id || '') > '')
        classes[classes.length] = e.id;

      for (var i = 0; i < classes.length; i++) {
        mySensitivitySpec = undefined;

        if ("undefined" !== typeof(yInterface._sensitiveFields[classes[i]])) {
          mySensitivitySpec = yInterface._sensitiveFields[classes[i]];
        }
        if ("undefined" !== typeof(yInterface._sensitiveFields["." + classes[i]])) {
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
    console.log("FORMFILLED " + e.target.id);
  };

  that.emptyForm = function() {
    return {
      getAttribute: function() { return ''; },
      removeEventListener: function() {}
    };
  };

  that.buildFormFromJSON = function(formManager, formId, jForm, fieldPrefix, fieldPostfix) {
    var form = y$(formId) || that.emptyForm(),
      formInfoJ = JSON.parse((jForm || {}).form || "{}"),
      sourceCodeViewer = y$('sourceCodeViewer');

    if (sourceCodeViewer) {
      /* https://stackoverflow.com/questions/4810841/how-can-i-pretty-print-json-using-javascript*/
      sourceCodeViewer.innerHTML = (function() {
        var json = JSON.stringify(jForm.form, undefined, 2);
        json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(new RegExp("\n", 'g'), "*").replace('\\"', '\"');
        return '<pre style="white-space: pre-wrap; word-break: keep-all;">' + json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function(match) {
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
        }) + '</pre>';
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
        paragraphModel = "<div class='form-group {1}' id='{0}'><label>{2}</label><div {4} <!--USERDATA--> ><p>{3}</p></div></div>",
        inputModel = "<div class='form-group {1}' id='grp_{0}'><label for='{0}'>{3}</label><input type='{2}' autocomplete='off' class='form-control {5}' id='{0}' {4} {6} <!--USERDATA--> /></div>",
        inputModelWithLeftAddon = "<div class='form-group {1}' id='grp_{0}'><label for='{0}'>{3}</label><div class='input-group'><span class='input-group-addon'>{7}</span><input type='{2}' autocomplete='off' class='form-control {5}' id='{0}' {4} {6} <!--USERDATA--> /></div></div>",
        inputModelWithRightAddon = "<div class='form-group {1}' id='grp_{0}'><label for='{0}'>{3}</label><div class='input-group'><input type='{2}' autocomplete='off' class='form-control {5}' id='{0}' {4} {6} <!--USERDATA--> /><span class='input-group-addon'>{7}</span></div></div>",
        inputWithListModel = "<div class='form-group {1}' id='grp_{0}'><label for='{0}'>{3}</label><input type='{2}' class='form-control {5}' id='{0}' {4} {11} list='{6}' data-subject='{7}' data-action='{8}' data-line-spec='{9}' data-result-spec='{10}' <!--USERDATA--> /><datalist id='{6}'></datalist></div>",
        hiddenInputModel = "<input type=hidden id='{0}' {1} value='{2}' <!--USERDATA--> />",
        buttonModel = "<button type='button' class='{1}' id='{0}' {4} {5} <!--USERDATA--> ><i class='{3}'></i>{2}</button>",
        selectModelWithQuery = "<div class='form-group {1}' id='grp_{0}'><label for={0}>{2}</label><select id='{0}' class='form-control' {3} {4} <!--USERDATA--> ></select></div>",
        selectModelWithOptions = "<div class='form-group {1}' id='grp_{0}'><label for={0}>{2}</label><select id='{0}' class='form-control' {3} {4} <!--USERDATA--> >{5}</select></div>",
        checkboxModel = "<div class='form-group {1}' id='grp_{0}'><label>{4}</label><div class='checkbox'><label><input type='checkbox' value='{3}' id='{0}' {5} {6} <!--USERDATA--> >{2}</label></div></div>",
        radioModel = "<div class='{1}' id='grp_{0}'><div class='radio'><label><input type='radio' name='{3}' id='{0}' value='{4}' {5} <!--USERDATA--> >{2}</label></div></div>",
        textareaModel = "<div class='form-group {1}' id='grp_{0}'><label for='{0}'>{2}</label><textarea class='form-control' id='{0}' cols='{3}' rows='{4}' {5} {6} <!--USERDATA--> ></textarea></div>",
        formModel = "<form role='form' id='{0}' data-prefix='{2}' class='{1}' {3} <!--USERDATA--> >{4}</form>";

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
              /* o problema é adiar as opçoes do select */
              if (jList[i].query) {
                html += selectModelWithQuery.format(elementId, jList[i].class, jList[i].label || '', groupingSpecs, null);
                for (var auxP in jList[i].query.params) {
                  if (jList[i].query.params.hasOwnProperty(auxP)) {
                    jList[i].query.params[auxP] = yAnalise(jList[i].query.params[auxP], undefined, formManager);
                  }
                }
                _loadSelectOptions(jList[i].query, elementId, (jList[i].readOnly || '').toUpperCase() == 'YES');
              } else if (jList[i].options) {
                var auxOptions = '';
                for (var op in jList[i].options) {
                  if (jList[i].options.hasOwnProperty(op)) {
                    auxOptions += "<option value='{0}'>{1}</option>".format(jList[i].options[op].value, jList[i].options[op].label);
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
                    groupingClass, /* groupingSpecs não pode ir aqui */
                    elementId + "_datalist_" + sequence,
                    jList[i].query.subject,
                    jList[i].query.action,
                    JSON.stringify(jList[i].query.lineSpec || {}),
                    JSON.stringify(jList[i].resultSpec || {}));
                  addToEventList(elementId, "keyup", "yInterface.input_keyup");
                  addToEventList(elementId, "input", "yInterface.input_input");
                } else {
                  if ("undefined" != typeof jList[i].rightAddon) {
                    html += inputModelWithRightAddon.format(
                      elementId,
                      (jList[i].class || 'col-md-12'),
                      jList[i].inputMode || 'text',
                      jList[i].label || '',
                      (jList[i].readOnly || '').toUpperCase() == 'YES' ? 'readonly' : '',
                      groupingClass, /* groupingSpecs não pode ir aqui */
                      '',
                      jList[i].rightAddon);
                  } else if ("undefined" != typeof jList[i].leftAddon) {
                    html += inputModelWithLeftAddon.format(
                      elementId,
                      (jList[i].class || 'col-md-12'),
                      jList[i].inputMode || 'text',
                      jList[i].label || '',
                      (jList[i].readOnly || '').toUpperCase() == 'YES' ? 'readonly' : '',
                      groupingClass, /* groupingSpecs não pode ir aqui */
                      '',
                      jList[i].leftAddon);
                  } else {
                    html += inputModel.format(
                      elementId,
                      (jList[i].class || 'col-md-12'),
                      jList[i].inputMode || 'text',
                      jList[i].label || '',
                      (jList[i].readOnly || '').toUpperCase() == 'YES' ? 'readonly' : '',
                      groupingClass, /* groupingSpecs não pode ir aqui */
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
      /* cuidado... ele está voltando aqui mas nem tudo está onde deveria, então tudo tem que ser montado novamente */
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

      var __addEvent = function(elementId, eventName, eventHandler) {
        if (eventName == 'changeOrBlur') {
          var auxElement = y$(elementId);
          if ("undefined" == typeof(auxElement || {}).length) {
            if ((auxElement.nodeName == 'INPUT') && (!((auxElement.type == 'radio') || (auxElement.type == 'checkbox'))))
              __addEvent(elementId, 'blur', eventHandler);
            else
              __addEvent(elementId, 'change', eventHandler);
          } else {
            for (var el = 0; el < (auxElement || {}).length || 0; el++) {
              __addEvent(auxElement[el].id, eventName, eventHandler);
            }
          }
        } else {
          addEvent(elementId, eventName, eventHandler);
          if ((eventName == 'change') || (eventName == 'blur')) {
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
                      __addEvent(elementId, eventName, window[ev[0]][ev[1]]);
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
        flatpickr(".date-select-field", { dateFormat: "d/m/Y" });
      }

      if ("undefined" != typeof InputMask) {
        new InputMask().Initialize(document.querySelectorAll(".masked-year-field"), {
          mask: InputMaskDefaultMask.DateMonthYear,
          placeHolder: "yyyy"
        });
        new InputMask().Initialize(document.querySelectorAll(".masked-month-year-field"), {
          mask: InputMaskDefaultMask.DateMonthYear,
          placeHolder: "mm/yyyy"
        });
        new InputMask().Initialize(document.querySelectorAll(".masked-date-field"), {
          mask: InputMaskDefaultMask.Date,
          placeHolder: "dd/mm/yyyy"
        });
        new InputMask().Initialize(document.querySelectorAll(".masked-time-field"), {
          mask: InputMaskDefaultMask.TimeShort,
          placeHolder: "hh:mm"
        });

      } else {
        console.warn("InputMask not found (https://www.cssscript.com/lightweight-pure-javascript-input-mask/)");
      }

    };

    /* http://2ality.com/2013/06/triggering-events.html */
    /* http://192.168.11.62/bfb/ */
    var prepareFieldRet = new Promise(
      function(solve, reject) {

        solve({
          'html': "<div class='json-dyn-form'>" + createFields(formInfoJ) + '</div>',

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

    addEvent(".btn-update", "click", that.refreshTable);
    addEvent(".btn-add", "click", that.insertData);

    /* apenas para desenvolvimento. Em produção tem que estar TRUE */
    that.allowCacheForm = false;
    that._repeatableFieldInfo = [];

    var tables = y$('.table');
    if (tables) {
      tables.forEach(function(table) {
        if ((table.getAttribute('data-dbtable') || 'no') == 'yes') {
          ycomm.dom.fillElement(table.id, {});
        }
      });
    }

    /* analiso a url para ver se o usuário quer ir para uma ancora */
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