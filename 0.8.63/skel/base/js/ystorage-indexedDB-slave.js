/*jshint esversion: 6 */

if ("undefined" == typeof yloader) {
  if ("undefined" != typeof importScripts)
    try {
      importScripts("yloader.js");
    } catch(e) {
      try {
        importScripts("yloader.min.js");
      } catch(e) {
        log('Error loading yloader');
      }

    }
}

/*
importScripts("browser.js");
importScripts("indexeddbshim.js");
*/

var yIndexedDBSlaveObj = function() {
  var that = {},
    forceWebSQL = true && (typeof openDatabase == 'function'),
    webKitVersion = yloader.webKitVersion.split('.')[0],
    canUseIndexedDB = ((!forceWebSQL) && (webKitVersion >= 537));

  if (canUseIndexedDB) {
    indexedDB = typeof indexedDB != "undefined" ? indexedDB :
      typeof mozIndexedDB != "undefined" ? mozIndexedDB :
      typeof webkitIndexedDB != "undefined" ? webkitIndexedDB :
      typeof msIndexedDB != "undefined" ? msIndexedDB : {};

    IDBTransaction = typeof IDBTransaction != "undefined" ? IDBTransaction :
      typeof webkitIDBTransaction != "undefined" ? webkitIDBTransaction :
      typeof msIDBTransaction != "undefined" ? msIDBTransaction : { READ_WRITE: "readwrite" };

    IDBKeyRange = typeof IDBKeyRange != "undefined" ? IDBKeyRange :
      typeof webkitIDBKeyRange != "undefined" ? webkitIDBKeyRange :
      typeof msIDBKeyRange != "undefined" ? msIDBKeyRange : {};
  }

  var db;

  that.context = {
    status: typeof indexedDB.open !== "undefined" ? 0 : -1
  };

  that.sendInformation = function(cmd, information) {
    /* this send information to the parent */
    var data = {
      'cmd': (cmd || '').trim(),
      'information': information
    };

    if (yloader.isWorker) {
      self.postMessage(data);
    } else {
      if ("undefined" !== typeof parent)
        parent.postMessage(data, "*");
      else
        window.postMessage(data, "*");
    }
  };

  that._indicateStatusChanges = function() {
    that.sendInformation('initialization', {
      'status': that.status
    });
  };

  that._log = function(message) {
    that.sendInformation('log', {
      'message': message
    });
  };

  that._db_success = function() {
    that.status = 2;
  };

  that._db_failure = function() {
    that.status = -2;
  };

  that._genericCallBack = function(cbr) {
    /* tentativa de desacoplar */
    setTimeout(
      function() {
        that.sendInformation('callback', {
          'cbr': JSON.stringify(cbr)
        });
      },
      25
    );
  };

  that._getObjectDescriptorByTableName = function(tableName) {
    var ret = {};
    for (var i = 0; i < that.context.objectStoreDescription.length; i++) {
      if (that.context.objectStoreDescription[i].name == tableName)
        ret = that.context.objectStoreDescription[i];
    }
    return ret;
  };

  var prepareKeyValue = function(objectDescriptor, dataRecord, keyValue) {
    var keyPath = objectDescriptor.keyPath;
    if ("undefined" != typeof keyValue) {
      if ("string" == typeof keyPath) {
        dataRecord[keyPath] = keyValue;
      } else {
        if (isArray(keyPath)) {
          if (!isArray(keyValue)) {
            if (keyValue.indexOf(";")>=0) {
              keyValue=keyValue.split(";");
            } else if (keyValue.indexOf(",")>=0) {
              keyValue=keyValue.split(",");
            } else if (keyValue.indexOf(".")>=0) {
              keyValue=keyValue.split(".");
            }
          }
          for(var k=0; k<keyValue.length; k++) {
            dataRecord[keyPath[k]] = keyValue[k];
          }
        }
      }
    }

    return [ dataRecord, keyValue];
  };

  var createWhereClause = function(objectDescriptor, mandatoryFields, keyValue, operator, fieldsToIgnore ) {
    fieldsToIgnore = fieldsToIgnore || [];
    operator = operator || 'or';
    var aux = prepareKeyValue(objectDescriptor, {}, keyValue);
    dataRecord = aux[0];
    keyValue   = aux[1];

    var mandatoryFieldsArray = mandatoryFields.split(',');
    var whereClause = '';
    if (!isArray(keyValue))
      keyValue=[keyValue];
    for (var i = 0; i < mandatoryFieldsArray.length; i++) {
      if (fieldsToIgnore.indexOf(mandatoryFieldsArray[i])<=0) {
        if (whereClause > '') {
          whereClause += ' {0} '.format(operator);
        }
        whereClause += mandatoryFieldsArray[i] + '=' + "'{0}'".format(keyValue[i] || '');
      }
    }

    return whereClause;
  };

  var grantDocumentId = function(objectDescriptor, dataRecord, keyValue) {

    var keyPath = objectDescriptor.keyPath;

    var auxKeyValue = prepareKeyValue(objectDescriptor, dataRecord, keyValue);
    dataRecord = auxKeyValue[0];
    keyValue   = auxKeyValue[1];

    if ("string" == typeof keyPath) {
      dataRecord._id = ("undefined" == typeof dataRecord[keyPath])?newIdentifier():dataRecord[keyPath];
    } else {
      var aux="";
      keyPath.forEach(
        function(e) {
          if (aux>'')
            aux+='.';
          aux+=("undefined" == typeof dataRecord[e])?newIdentifier():dataRecord[e];
        }
      );
      dataRecord._id=md5(aux);
    }
    if (("undefined" == typeof dataRecord._ts) || (dataRecord._ts<=0))
      dataRecord._ts = str2int(((new Date()).getTime() / 1000));
    return dataRecord._id;
  };

  that.count = function(tableName, callbackId) {
    var callback = that._genericCallBack;

    var myObjectDescriptor = that._getObjectDescriptorByTableName(tableName);

    var callbackRet = {
      dbTag: that.context.dbTag,
      name: myObjectDescriptor.name,
      tableName: tableName,
      action: 'count',
      callbackId: callbackId,
      data: null,
      result: undefined
    };

    if (canUseIndexedDB) {
      //https://developer.mozilla.org/en-US/docs/Web/API/IDBObjectStore/count
      var transaction = db.transaction([tableName], "readwrite");
      var objectStore = transaction.objectStore(tableName);
      var request = objectStore.count();

      request.onsuccess = function(event) {
        callbackRet.return = event.target.result;
        callback(callbackRet);
      };

      request.onerror = function(event) {
        callbackRet.return = false;
        callback(callbackRet);
      };

    } else {
      db.transaction(
        function(tx) {
          var sqlStatement = "select count(*) as cc from {0}".format(tableName);
          tx.executeSql(
            sqlStatement,
            [],
            function(sqlTransaction, sqlResultSet) {
              callbackRet.return = sqlResultSet.rows.item(0).cc;
              callback(callbackRet);
            },
            function(sqlTransaction, sqlError) {
              callbackRet.return = -1;
              callbackRet.errorMessage = sqlError.message;
              callback(callbackRet);
            });
        });
    }
  };

  that.setItem = function(tableName, callbackId, keyValue, data) {
    var callback = that._genericCallBack;

    var myObjectDescriptor = that._getObjectDescriptorByTableName(tableName);

    data = data || {};
    grantDocumentId(myObjectDescriptor, data, keyValue);

    var callbackRet = {
      dbTag: that.context.dbTag,
      name: myObjectDescriptor.name,
      _id: data._id,
      tableName: tableName,
      action: 'setItem',
      keyValue: keyValue,
      callbackId: callbackId,
      data: null,
      result: undefined
    };

    var _sqlError = function(sqlTransaction, sqlError) {
      callbackRet.return = false;
      callbackRet.errorMessage = sqlError.message;
      callback(callbackRet);
    };

    if (canUseIndexedDB) {
      var transaction = db.transaction([tableName], "readwrite");
      var request = transaction.objectStore(tableName);

      request.put(data);

      request.onsuccess = function(event) {
        callbackRet.return = true;
        callback(callbackRet);
      };

      request.onerror = function(event) {
        callbackRet.return = false;
        callback(callbackRet);
      };
    } else {
      db.transaction(
        function(tx) {
          getMandatoryFields(tableName,
            function(aTableName, mandatoryFields) {
              var mandatoryFieldsArray = mandatoryFields.split(',');
              var sqlFields = '',
                sqlValues = '',
                keyWhereStatement = '',
                sqlValuesArray = [];
              for (var i = 0; i < mandatoryFieldsArray.length; i++) {
                var fieldName = mandatoryFieldsArray[i].trim();

                if (sqlFields > '') {
                  sqlFields += ", ";
                  sqlValues += ", ";
                }
                sqlFields += fieldName;
                sqlValues += '?';
                if (fieldName == 'data')
                  sqlValuesArray[sqlValuesArray.length] = JSON.stringify(data);
                else {
                  if (keyWhereStatement>'')
                    keyWhereStatement+=' AND ';
                  keyWhereStatement+="{0}='{1}'".format(fieldName, data[fieldName]);
                  sqlValuesArray[sqlValuesArray.length] = data[fieldName];
                }
              }
              var sqlStatementD = "DELETE FROM {0} where {1}".format(aTableName, keyWhereStatement);
              var sqlStatementI = "INSERT INTO {0} ({1}) values({2})".format(aTableName, sqlFields, sqlValues);
              that._log(sqlStatementD);
              tx.executeSql(
                sqlStatementD,
                [],
                function (sqlTransaction, sqlResultSet) {
                  that._log(sqlStatementI);
                  tx.executeSql(
                    sqlStatementI,
                    sqlValuesArray,
                    function(sqlTransaction, sqlResultSet) {
                      callbackRet.return = true;
                      callback(callbackRet);
                    },
                    _sqlError);
                },
                _sqlError
              );
            }
          );
        }
      );
    }
  };

  that.getItem = function(tableName, callbackId, keyValue) {
    var callback = that._genericCallBack;

    var myObjectDescriptor = that._getObjectDescriptorByTableName(tableName);

    var callbackRet = {
      dbTag: that.context.dbTag,
      name: myObjectDescriptor.name,
      tableName: tableName,
      action: 'getItem',
      keyValue: keyValue,
      callbackId: callbackId,
      data: null,
      result: undefined
    };

    if (canUseIndexedDB) {
      var request = db
        .transaction([tableName], "readonly")
        .objectStore(tableName)
        .get(keyValue);

      request.onsuccess = function(event) {
        callbackRet.return = true;
        callbackRet.data = event.target.result;
        callback(callbackRet);
      };

      request.onerror = function(event) {
        callbackRet.return = false;
        callback(callbackRet);
      };
    } else {
      db.transaction(
        function(tx) {
          getMandatoryFields(tableName,
            function(aTableName, mandatoryFields) {
              /*
              var mandatoryFieldsArray = mandatoryFields.split(',');
              var whereClause = '';
              for (var i = 0; i < mandatoryFieldsArray.length; i++) {
                if (whereClause > '') {
                  whereClause += ' or ';
                }
                whereClause += mandatoryFieldsArray[i] + '=' + "'{0}'".format(keyValue);
              }*/
              var whereClause = createWhereClause(myObjectDescriptor, mandatoryFields, keyValue, 'or');

              var sqlStatement = "select * from {0} where {1}".format(aTableName, whereClause);

              tx.executeSql(
                sqlStatement, [],
                function(sqlTransaction, results) {
                  if (results.rows.length==0) {
                    callback(callbackRet);
                  } else {
                    for (var i = 0; i < results.rows.length; i++) {
                      callbackRet.data = {};
                      var auxData = results.rows.item(i).data || "";
                      auxData = JSON.parse(auxData);
                      mergeObject(auxData, callbackRet.data);
                      callbackRet.return = true;
                      callback(callbackRet);
                    }
                  }
                },
                function(sqlTransaction, sqlError) {
                  callbackRet.errorMessage = sqlError.message;
                  callbackRet.return = null;
                  callback(callbackRet);
                });
            }
          );
        }
      );
    }

  };

  that.insertData = function(tableName, dataArray, callbackId, aFieldsToPreserve) {
    var callback = that._genericCallBack;

    var myObjectDescriptor = that._getObjectDescriptorByTableName(tableName);

    var callbackRet = {
      dbTag: that.context.dbTag,
      name: myObjectDescriptor.name,
      tableName: tableName,
      action: 'insertData',
      callbackId: callbackId,
      data: null,
      result: undefined
    };

    var dataToInsert = 0, insertStatments = [];

    var _sqlResult = function(sqlTransaction, sqlResultSet) {
      dataToInsert--;
      _dumpy(128,2,tableName + " OK - dataToInsert " + dataToInsert);
      if (dataToInsert <= 0) {
        callbackRet.return = true;
        callback(callbackRet);
      }
    };

    var _sqlError = function(sqlTransaction, sqlError) {
      _dumpy(128,2,tableName + " ERR- dataToInsert " + dataToInsert);
      _dumpy(128,2,JSON.stringify(sqlError));
      dataToInsert--;
      if (dataToInsert <= 0) {
        callbackRet.return = false;
        callbackRet._removeThis = true;
        callback(callbackRet);
      }
    };

    var dNdx = 0,
      dataCount = dataArray.length;


    if (canUseIndexedDB) {

      var transaction = db.transaction([tableName], "readwrite");
      var itemStore = transaction.objectStore(tableName);

      var _insertData = function() {
        if (dNdx < dataCount) {
          dataToInsert--;
          grantDocumentId(myObjectDescriptor, dataArray[dNdx]);

          var request = itemStore.put(dataArray[dNdx]);

          request.onsuccess = _insertData;
          request.onerror = _sqlError;

          dNdx++;
        } else {
          _sqlResult(transaction, []);
        }
      };

      _insertData();

    } else {
      getMandatoryFields(tableName,
        function(aTableName, mandatoryFields) {
          db.transaction(
            function(tx) {

              var mandatoryFieldsArray = mandatoryFields.split(','),
                data = [],
                keyWhereStatement = '',
                keyValueArray = [],
                sqlFields = '',
                sqlValues = '',
                sqlValuesArray = [],
                fieldName,
                sqlStatementD, sqlStatementI,
                sqlStatement = '';

              dataToInsert = dataCount;
              if (dNdx>=dataCount) {
                _sqlResult(tx, []);
              } else {
                for (dNdx = 0; dNdx < dataCount; dNdx++) {
                  grantDocumentId(myObjectDescriptor, dataArray[dNdx]);

                  data = dataArray[dNdx];
                  sqlValuesArray = [];
                  data._id = data._id || newIdentifier();
                  data._ts = data._ts || str2int(((new Date()).getTime() / 1000));

                  sqlFields='';
                  sqlValues='';
                  keyWhereStatement='';

                  for (var i = 0; i < mandatoryFieldsArray.length; i++) {
                    fieldName = mandatoryFieldsArray[i].trim();

                    if (sqlFields > '') {
                      sqlFields += ", ";
                      sqlValues += ", ";
                    }
                    sqlFields += fieldName;
                    if (fieldName=='data') {
                      sqlValues += "'{0}'".format(JSON.stringify(data));
                    } else {
                      sqlValues += "'{0}'".format(data[fieldName]);
                    }

                    if (fieldName!=='data') {
                      if (keyWhereStatement > '') {
                        keyWhereStatement+=' AND ';
                      }
                      keyWhereStatement+="{0}='{1}'".format(fieldName, data[fieldName]);
                    }

                    if (fieldName == 'data')
                      sqlValuesArray[sqlValuesArray.length] = JSON.stringify(data);
                    else {
                      keyValueArray[keyValueArray.length] = data[fieldName];
                      sqlValuesArray[sqlValuesArray.length] = data[fieldName];
                    }


                  }

                  sqlStatementD = "DELETE FROM {0} where {1};".format(aTableName, keyWhereStatement);
                  sqlStatementI = "INSERT INTO {0} ({1}) values({2});".format(aTableName, sqlFields, sqlValues);

                  insertStatments[insertStatments.length]=sqlStatementI;

                  var sqlStatement = sqlStatementD+sqlStatementI;

                  that._log(aTableName + " " + dNdx + " " + sqlStatementD);
                  that._log(keyWhereStatement.toString());
                  tx.executeSql(
                    sqlStatementD,
                    [],
                    function(sqlTransaction, sqlResultSet) {
                      var auxSQL = insertStatments.shift();
                      tx.executeSql(auxSQL,[],_sqlResult,_sqlError);
                    },
                    _sqlError);
                }
              }

            }
          );
        }
      );
    }
  };

  that.filter = function(tableName, onitem, oncomplete, condition, haltOnFirst) {
    var callback = that._genericCallBack,
      canCall, breakQuery=false;

    var myObjectDescriptor = that._getObjectDescriptorByTableName(tableName);

    condition = condition || true;

    var callbackRet = {
      dbTag: that.context.dbTag,
      name: myObjectDescriptor.name,
      tableName: tableName,
      action: 'filter',
      condition: condition,
      data: null,
      result: undefined
    };

    var allValues = false;
    if (true === condition) {
      allValues = true;
    } else {
      var ylex = yLexObj(condition);
      ylex.parse();
    }
    if (canUseIndexedDB) {
      var request = db
        .transaction([tableName], "readonly")
        .objectStore(tableName)
        .openCursor();

      request.onsuccess = function(e) {
        var cursor = e.target.result;
        var aux = {};

        if (cursor) {
          canCall = (!breakQuery) && ( allValues || ylex.solve(cursor.value) );

          if (canCall) {
            mergeObject(callbackRet, aux);
            aux.data = cursor.value;
            aux.return = true;
            aux.callbackOnItem = onitem;
            callback(aux);
            if (haltOnFirst)
              breakQuery=true;
          }

          cursor.continue();
        } else {
          mergeObject(callbackRet, aux);
          aux.data = null;
          aux.return = true;
          aux._removeThis = true;
          aux.callbackOnComplete = oncomplete;
          callback(aux);

        }
      };
    } else {
      db.transaction(
        function(tx) {
          var sqlStatement = "select * from {0}".format(tableName);
          _dumpy(128,2,sqlStatement);
          tx.executeSql(
            sqlStatement, [],
            function(tx, results) {
              var aux;
              for (var i = 0; (breakQuery==false) && (i < results.rows.length); i++) {
                var value = results.rows.item(i).data;
                if (value == "undefined")
                  value = undefined;
                if (value == "null")
                  value = null;
                value = value || "{}";

                _dumpy(128,2,value);
                value = JSON.parse(value);
                _dumpy(128,2,value);

                canCall = allValues || ylex.solve(value);
                aux={};

                if (canCall) {
                  mergeObject(callbackRet, aux);
                  aux.data = {};
                  mergeObject(value, aux.data);
                  aux.callbackOnItem = onitem;
                  aux.return = true;
                  callback(aux);
                  if (haltOnFirst) {
                    breakQuery=true;
                  }
                }
              }

              aux={};

              mergeObject(callbackRet, aux);
              aux.data = null;
              aux.return = true;
              aux._removeThis = true;
              aux.callbackOnComplete = oncomplete;
              callback(aux);

            });
        }
      );
    }
  };

  that.slice = function(tableName, onitem, oncomplete, first, last, haltOnFirst) {
  };

  that.removeItem = function(tableName, callbackId, keyValue) {
    var callback = that._genericCallBack;

    var myObjectDescriptor = that._getObjectDescriptorByTableName(tableName);

    var data = {};
    grantDocumentId(myObjectDescriptor, data, keyValue);

    var callbackRet = {
      dbTag: that.context.dbTag,
      name: myObjectDescriptor.name,
      _id: data._id,
      tableName: tableName,
      action: 'removeItem',
      keyValue: keyValue,
      callbackId: callbackId,
      data: null,
      result: undefined
    };

    if (canUseIndexedDB) {
      var transaction = db.transaction([tableName], "readwrite");
      var request = transaction.objectStore(tableName);

      request.onsuccess = function(event) {
        callbackRet.return = true;
        callback(callbackRet);
      };

      request.onerror = function(event) {
        callbackRet.return = false;
        callback(callbackRet);
      };

      request.delete(keyValue);
    } else {
      db.transaction(
        function(tx) {
          getMandatoryFields(tableName,
            function(aTableName, mandatoryFields) {
              /*
              var mandatoryFieldsArray = mandatoryFields.split(',');
              var whereClause = '';
              for (var i = 0; i < mandatoryFieldsArray.length; i++) {
                if ((mandatoryFieldsArray[i]!='data') && (mandatoryFieldsArray[i]!='_id')) {
                  if (whereClause > '') {
                    whereClause += ' and ';
                  }
                  whereClause += mandatoryFieldsArray[i] + '=' + "'{0}'".format(keyValue);
                }
              }
              */
              var whereClause = createWhereClause(myObjectDescriptor, mandatoryFields, keyValue, 'and', [ '_id', 'data' ]);

              var sqlStatement = "delete from {0} where {1}".format(aTableName, whereClause);

              tx.executeSql(
                sqlStatement, [],
                function(sqlTransaction, results) {
                  callbackRet.return = true;
                  callbackRet.rowsAffected = results.rowsAffected;
                  callback(callbackRet);
                },
                function(sqlTransaction, sqlError) {
                  callbackRet.errorMessage = sqlError.message;
                  callbackRet.return = null;
                  callback(callbackRet);
                });
            }
          );
        }
      );
    }
  };

  that.removeData = function(tableName, callbackId, arrayOfKeys) {
    var callback = that._genericCallBack;

    var myObjectDescriptor = that._getObjectDescriptorByTableName(tableName);
/*
    var data = {};
    grantDocumentId(myObjectDescriptor, data, arrayOfKeys);
*/
    var callbackRet = {
      dbTag: that.context.dbTag,
      name: myObjectDescriptor.name,
      _id: null,
      tableName: tableName,
      action: 'removeData',
      keyValue: null,
      callbackId: callbackId,
      data: null,
      result: undefined
    };

    if (canUseIndexedDB) {

    } else {
      db.transaction(
        function(tx) {
          getMandatoryFields(tableName,
            function(aTableName, mandatoryFields) {
              if (isArray(arrayOfKeys)) {
                var sqlStatement='',
                    statementCounter = 0,
                    processFinished = false;;

                var _endOfProcess = function() {
                  if (!processFinished) {
                    processFinished=true;
                    var aux = {};
                    mergeObject(callbackRet, aux);
                    aux.return = true;
                    aux.rowsAffected = 0;
                    aux._removeThis = true;
                    callback(aux);
                  }
                }

                var _deleteItem = function (aTransaction) {
                  var aux = {};
                  mergeObject(callbackRet, aux);

                  var keyValue, canContinue=true, whereClause;

                  while (canContinue) {
                    if (arrayOfKeys.length>0) {
                      keyValue = arrayOfKeys.pop();


                      whereClause = createWhereClause(myObjectDescriptor, mandatoryFields, keyValue, 'and', [ '_id', 'data' ]);

                      // aux.keyValue = keyValue;
                      sqlStatement = "delete from {0} where {1};".format(aTableName, whereClause);
                      statementCounter++;
                      if ((statementCounter % 30 == 0) || (arrayOfKeys.length==0)) {
                        canContinue=false;
                      }

                      aTransaction.executeSql(
                        sqlStatement, [],
                        function(sqlTransaction, results) {
                          aux.return = true;
                          aux.rowsAffected = results.rowsAffected;
                          callback(aux);
                          if (arrayOfKeys.length>0)
                            _deleteItem(aTransaction);
                          else
                            _endOfProcess();
                        },
                        function(sqlTransaction, sqlError) {
                          aux.errorMessage = sqlError.message;
                          aux.return = null;
                          callback(aux);
                        });

                    } else {
                      canContinue=false;
                      _endOfProcess();
                    }
                  }

                };

                _deleteItem(tx);

              }
            }
          );
        }
      );
    }
  };

  that.cleanList = function(tableName, callbackId, propagate) {

  };

  that.blankList = function(tableName, callbackId) {
  };

  Object.defineProperty(that, "status", {
    get: function() {
      return that.context.status || 0;
    },
    set: function(aNewValue) {
      if (aNewValue != that.status) {
        that.context.status = aNewValue;
        that._indicateStatusChanges();
      }
    },
    enumerable: true
  });

  Object.defineProperty(that, "dbTag", {
    get: function() {
      return that.context.dbTag || '';
    },
    enumerable: true
  });

  Object.defineProperty(that, "dbVersion", {
    get: function() {
      return that.context.dbVersion || '1';
    },
    enumerable: true
  });

  that.receiveMessage = function(e) {
    var data = e.data;
    if (that.status < 2) {
      if (data.cmd == 'init') {
        init(data.dbTag, data.objectStoreDescription, data.dbVersion);
      }
    } else {
      switch (data.cmd) {
        case 'stop':
          that.status = -1;
          that.close();
          break;

        case 'count':
          that.count(data.tableName, data.callbackId, data.keyValue, data.data);
          break;

        case 'setItem':
          that.setItem(data.tableName, data.callbackId, data.keyValue, data.data);
          break;

        case 'getItem':
          that.getItem(data.tableName, data.callbackId, data.keyValue);
          break;

        case 'insertData':
          that.insertData(data.tableName, data.dataArray, data.callbackId, data.fieldsToPreserve);
          break;

        case 'filter':
          that.filter(data.tableName, data.callbackOnItem, data.callbackOnComplete, data.condition, data.haltOnFirst);
          break;

        case 'slice':
          break;

        case 'removeItem':
          that.removeItem(data.tableName,data.callbackId, data.keyValue);
          break;

        case 'removeData':
          that.removeData(data.tableName,data.callbackId, data.arrayOfKeys);
          break;

        case 'cleanList':
          break;

        case 'blankList':
          break;

      }
    }

  };

  var getMandatoryFields = function(tableName, callback) {
    for (var osd in that.context.objectStoreDescription) {
      if (that.context.objectStoreDescription.hasOwnProperty(osd)) {
        var mandatoryFields = '';
        var osdInfo = that.context.objectStoreDescription[osd];
        if (osdInfo.keyPath > "") {
          if (osdInfo.keyPath.indexOf(";") >= 0)
            osdInfo.keyPath = osdInfo.keyPath.split(";");
          else if (osdInfo.keyPath.indexOf(",") >= 0)
            osdInfo.keyPath = osdInfo.keyPath.split(",");

          if (mandatoryFields>"")
            mandatoryFields+=", ";

          mandatoryFields += osdInfo.keyPath;
        }
        if (mandatoryFields=="") {
          mandatoryFields="_id";
        }
        mandatoryFields += ", data";

        mandatoryFields = mandatoryFields.replace(/\ /g, "", true);

        if ((tableName == '*') || (osdInfo.name == tableName))
          callback(osdInfo.name, mandatoryFields);
      }
    }
  };

  var init = function(dbTag, objectStoreDescription, dbVersion) {
    if ((dbTag || '') > '') {
      if (that.status == 0) {
        that.context.dbTag = dbTag;
        that.context.dbVersion = str2int(dbVersion || 1);
        that.context.objectStoreDescription = (objectStoreDescription || []).slice();

        that.status = 1;

        if (canUseIndexedDB) {
          that._log("Using IndexedDB");
          var reqDB = indexedDB.open(that.dbTag, that.dbVersion);
          reqDB.onupgradeneeded = function(e) {
            var _db = e.target.result;
            _dumpy(128,2,"upgraded needed");
            try {
              for (var osd = 0; osd < that.context.objectStoreDescription.length; osd++) {

                var osdInfo = that.context.objectStoreDescription[osd];

                if ("string" == typeof osdInfo.keyPath) {
                  if (osdInfo.keyPath.indexOf(";") >= 0)
                    osdInfo.keyPath = osdInfo.keyPath.split(";");
                  else if (osdInfo.keyPath.indexOf(",") >= 0)
                    osdInfo.keyPath = osdInfo.keyPath.split(",");
                }

                if ("undefined" == typeof osdInfo.keyPath)
                  osdInfo.keyPath = "_id";

                var osdDeclaration = {
                  "keyPath": osdInfo.keyPath
                    // "autoIncrement": st2bool(osdInfo.autoIncrement)  <<<< keyPath eh um vetor
                };

                _dumpy(128,2,"CREATING " + osdInfo.name);
                _dumpy(128,2,"  +--> " + JSON.stringify(osdDeclaration));
                var objectStore = _db.createObjectStore(
                  osdInfo.name,
                  osdDeclaration
                );

              }
              that._log("Object Store created");
              that._db_success();
            } catch (err) {
              that._log("Error occurred: {0}".format(err.message));
              that._db_failure();
            }
          };

          reqDB.onversionchange = function(e) {
            _dumpy(128,2,"********************************");
            _dumpy(128,2,"** ON VERSION CHANGE ");
            _dumpy(128,2,"********************************");
          };

          reqDB.onsuccess = function(e) {
            db = e.target.result;
            that._db_success();
          };
          reqDB.onerror = that._db_failure;
        } else {
          try {
            that._log("Using WEBSql");
            db = openDatabase(that.dbTag+"."+that.dbVersion, that.dbVersion, that.dbTag, 40 * 1024 * 1024);
            _dumpy(128,2,"DB "+typeof db);
            if (db) {
              if ((that.context.objectStoreDescription || []).length == 0) {
                that._db_success();
              } else {
                var withError = 0,
                  toProcess = that.context.objectStoreDescription.length,
                  sqlList = [], 

                  _addCreateTableSql = function(tableName, mandatoryFields) {
                    sqlList[sqlList.length] = "CREATE TABLE IF NOT EXISTS {0} ({1})".format(tableName, mandatoryFields.replace("_id", "_id unique"));
                  };

                for (var i = toProcess - 1; i >= 0; i--) {
                  getMandatoryFields(
                    that.context.objectStoreDescription[i].name,
                    _addCreateTableSql
                  );
                }

                var _txSuccess = function(sqlTransaction, sqlResultSet) {
                  toProcess--;
                  if (toProcess <= 0)
                    that._db_success();

                };

                var _txError = function(sqlTransaction, sqlError) {
                  withError++;
                  if (toProcess <= 0)
                    that._db_failure();

                };

                db.transaction(
                  function(tx) {
                    for (var i = 0; i < sqlList.length; i++)
                      tx.executeSql(
                        sqlList[i], [], _txSuccess, _txError
                      );
                  });
              }
            } else {
              _dumpy(128,2,"Error creating WEBSql database!");
              that._db_failure();
            }
          } catch (e) {
            that._log("Error occurred: {0}".format(e.message));
            that._db_failure();
          }
        }
      }
    }
  };

  return that;
};

function startup() {
  var ydb = yIndexedDBSlaveObj();
  if (yloader.isWorker) {
    self.addEventListener("message", ydb.receiveMessage, false);
  } else {
    window.addEventListener("message", ydb.receiveMessage, false);
  }
}

startup();
