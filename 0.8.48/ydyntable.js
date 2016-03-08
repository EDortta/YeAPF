/*********************************************
 * ydyntable.js
 * YeAPF 0.8.48-1 built on 2016-03-07 13:46 (-3 DST)
 * Copyright (C) 2004-2016 Esteban Daniel Dortta - dortta@yahoo.com
 * 2016-02-24 16:27:23 (-3 DST)
 * First Version (C) 2009 - esteban daniel dortta - dortta@yahoo.com
**********************************************/
//# sourceURL=ydyntable.js


function _dynCheckChilds(aStack, aField)
{
  var childOpenCondition=aField.childOpenCondition;
  if (childOpenCondition>'') {
    var aResult = Parser.evaluate(childOpenCondition, aStack);
    var childsContainer=y$(aField.name+'_childs');
    if (childsContainer) {
      if (aResult)
        childsContainer.style.display='block';
      else
        childsContainer.style.display='none';
    }
  }
}

function dynCheckChids(aFieldList, e)
{
  if (e==undefined)
    var e = window.event || arguments.callee.caller.arguments[0];
  if (e.target)
    e = e.target;

  // temporary stack to call Parser
  var aStack=new Array();
  for(var f in aFieldList)
    if ((aFieldList.hasOwnProperty(f)) && (y$(f)))
      aStack[f]=y$(f).value;

  // check if it is entering from a form field
  if (aFieldList[e.id])
    _dynCheckChilds(aStack, aFieldList[e.id]);
  else {
    for(var f in aFieldList)
      if (aFieldList.hasOwnProperty(f))
        _dynCheckChilds(aStack, aFieldList[f]);
  }
}

function _dynConfigOnChange(aElement, onChange)
{
  if (aElement) {
    if (aElement.onchange != __cbOnChange__) {
      aElement.dynOnChange=aElement.onchange;
      if (onChange==undefined)
        aElement.onchange=__dynOnChange__;
      else
        aElement.onchange=onChange;
    }
  }
}

function dynConfigOnchange(aElementList, onChange)
{
  if (typeof(aElementList) != 'object')
    var aList = aElementList.split(',');
  else
    var aList=aElementList;
  if (aList.length>0) {
    for(var i=0; i<aList.length; i++)
      _dynConfigOnChange(y$(aList[i]), onChange);
  } else
    for(var i in aList)
      if (aList.hasOwnProperty(i))
        _dynConfigOnChange(y$(i), onChange);
}

/*
 * Sometimes you need to attach some fields display to a checkbox situation
 * This function set onChange event of all the aElementSet
 */
function dynConfigCheckBoxChilds(aSaveOnChange, aElementSet)
{
  if (aElementSet==undefined)
    var aElementSet = document.getElementsByTagName('input');

  if (aSaveOnChange==undefined)
    var aSaveOnChange=false;

  var auxID;
  var ccDependents;
  var allElements=document.getElementsByTagName('*');
  for(var i=0; i<aElementSet.length; i++)
  {
    var canModifyEvents=false;
    if (aElementSet[i].type=='checkbox') {
      auxID = aElementSet[i].id+'.';
      ccDependents=0;
      for (var n=i+1; n<allElements.length; n++) {
        var aID=allElements[n].id;
        if (typeof aID == "string")
          if (aID.substr(0,auxID.length)==auxID) {
            dynSetElementDisplay(allElements[n].id, aElementSet[i].id, aElementSet[i].value);
            // aElementSet[n].style.visibility=aElementSet[i].checked?'visible':'hidden';
            ccDependents++;
          }
      }
      canModifyEvents=true;
    } else if (aElementSet[i].type=='text') {
      canModifyEvents=true;
    }

    if (canModifyEvents) {
      if (aElementSet[i].onchange != __cbOnChange__) {
        aElementSet[i].dynOnChange=aElementSet[i].onchange;
        aElementSet[i].onchange=__cbOnChange__;
        aElementSet[i].dynSaveOnChange=aSaveOnChange;
      }
    }
  }
}

function dynTableEnumerateCellElements(aTable, aFunction, aFirstRow)
{
  if (aFirstRow==undefined)
    aFirstRow=2;

  for(var r=aFirstRow; r<aTable.rows.length; r++)
    for (var c=0; c<aTable.rows[r].cells.length; c++) {
      aCell=aTable.rows[r].cells[c];
      var cellElements = aCell.getElementsByTagName('*');
      for(var e in cellElements)
        if (cellElements.hasOwnProperty(e))
          aFunction(cellElements[e]);
    }
}


function dynRenumberElements(e, aTable, aElementsSeed)
{
  var aLines= new Array();
  var aSequence=0;

  dynTableEnumerateCellElements(aTable, function (aElement) {
    if (typeof aElement.id != 'undefined') {
      var idInfo=aElement.id.split('.');
      if (aElementsSeed.indexOf(idInfo[0])>0) {
        var aNdx=str2int(idInfo[1]);
        if (aLines.indexOf(aNdx)<0)
          aLines[aSequence++]=aNdx;
      }
    }
  }, 1);

  for(var i in aLines)
    if (aLines.hasOwnProperty(i)) {
      var aNdx=aLines[i];
      for(var n in aElementsSeed)
        if (aElementsSeed.hasOwnProperty(n)) {
          document.getElementById(aElementsSeed[n]+'.'+zeroPad(aNdx,2)).id = aElementsSeed[n]+'.'+zeroPad(i,2);
        }
    }
}

function _dynCleanTableRow(e, aTable, aRowNdx)
{
  for(var n=0; n<aTable.rows[aRowNdx].childNodes.length; n++)
    dynCleanChilds(e, aTable.rows[aRowNdx].childNodes[n], true);
}

function dynCleanChilds(e, aDOMObject, aCleanAll, aChangeVisibility)
{
  if (aDOMObject instanceof Text)
    aDOMObject = aDOMObject.nextSibling;
  if (aDOMObject!=undefined) {
    if (aCleanAll==undefined)
      aCleanAll=false;
    if (aChangeVisibility==undefined)
      aChangeVisibility=true;

    var auxID;
    if (!((e==undefined) ||(e==null)))
      auxID = e.id+'.';
    else
      auxID = '*.';

    var allElements=aDOMObject.getElementsByTagName('*');
    for (var i=0; i<allElements.length; i++)
    {
      var aID=allElements[i].id;
      if (typeof aID == 'string')
        if ((aID.substr(0,auxID.length)==auxID) || (aCleanAll) || (auxID=='*.')) {
          if ((aChangeVisibility) && (auxID!='*.'))
            dynSetElementDisplay(allElements[i].id, e.id, e.value);

          if ((auxID=='*.') || (!e.checked)) {
            // limpar conteúdo dos campos dependentes
            // caso esteja ocultando
            if (allElements[i].type=='checkbox')
              allElements[i].checked=false;
            if (allElements[i].type=='radio')
              allElements[i].checked=false;
            if (allElements[i].type=='text')
              allElements[i].value='';
            if (allElements[i].type=='number')
              allElements[i].value='';

            if (allElements[i].rows != undefined) {
              // keeps the first two lines of the table and eliminates the rest
              // the first contains the titles
              // the second contains the fields template and buttons
              while (allElements[i].rows.length>2) {
                _dynCleanTableRow(e, allElements[i],2);
                allElements[i].deleteRow(2);
              }

              // clean the contents of the second row
              if (allElements[i].rows.length>1)
                for(var n=0; n<allElements[i].rows[1].childNodes.length; n++)
                  dynCleanChilds(e, allElements[i].rows[1].childNodes[n], true, false);
            } else if (allElements[i].cells != undefined) {
              for(var c=0; c<allElements[i].cells.length; c++)
                dynCleanChilds(e, allElements[i].cells[c], true, aChangeVisibility);
            } else
              if (allElements[i].type!=undefined)
                __cbOnChange__(allElements[i]);
          }
        }
    }
  }
}

function dynTableDelRow(e, aHeaderRowCount)
{
  if (aHeaderRowCount==undefined)
    aHeaderRowCount=2;
  while ((e!=undefined) && (!(e instanceof HTMLTableRowElement)))
    e=e.parentNode;
  if (e) {
    var table = e.parentNode;
    if (table.rows.length>aHeaderRowCount) {
      _dynCleanTableRow(e, table, e.rowIndex);
      table.deleteRow(e.rowIndex);
    } else  {
      _dynCleanTableRow(e, table, e.rowIndex);
      /*
      for (var c=0; c<e.cells.length; c++) {
        var aCell=e.cells[c];
        for (var i=0; i<aCell.childNodes.length; i++) {
          var el=aCell.childNodes[i];
          el.value=null;
        }
      }
      */
    }
  } else
    alert("Your button is outside a table");
}

function dynTableDelAllRows(aTable)
{
  while(aTable.rows.length>0)
    dynTableDelRow(aTable.rows[0],0);
}

function _dynExplodeTag(aTag)
{
  var sequence = aTag.match(/\d+$/)[0];
  var name = aTag.substr(0,aTag.length-sequence.length);
  return [name, sequence];
}

function dynTableCloneRow(e, onchange)
{
  while ((e!=undefined) && (!(e instanceof HTMLTableRowElement)))
    e=e.parentNode;
  if (e) {
    var table = e.parentNode;
    var obj = e.cloneNode(true);
    // Find a special object inside cells like INPUT elements
    // so we can pick the name and use it as base for next name
    // A name or id is composed of 'name' followed by 'sequence'
    // for example: 'product01' is a valid tag
    for (var c=0; c<obj.cells.length; c++) {
      var aCell=obj.cells[c];
      for (var i=0; i<aCell.childNodes.length; i++) {
        var el=aCell.childNodes[i];
        if (el.id>'') {
          var elID = _dynExplodeTag(el.id);
          var curSeq=elID[1];
          curSeq=0;
          while (y$(elID[0]+zeroPad(curSeq,2)))
            curSeq++;
          el.id=elID[0]+zeroPad(curSeq,2);
          el.name=el.id;
          el.onchange=onchange;
        }
        el.value=null;
      }
    }
    var newRow=table.insertBefore(obj,e.nextSibling);
    return newRow;
  } else {
    alert("Your button is outside a table");
    return null;
  }
}

function dynTableCloneLastRow(aTableId)
{
  var oTable=y$(aTableId);
  if (oTable.rows.length>0) {
    var r=oTable.rows[oTable.rows.length-1];
    dynTableCloneRow(r);
  }
}

function dynSetElementDisplay(aElementID, aMasterFieldName, aMasterFieldValue)
{
  var element=y$(aElementID);
  if (element) {
    var field=y$(aMasterFieldName);
    if (field) {
      var theFieldValue;
      if (field.type=='radio') {
        var auxRadio=document.getElementsByName(aMasterFieldName);
        // if not master value defined, we assume the last one of the series is the desired trigger value
        if (aMasterFieldValue==undefined)
          if (auxRadio.length>0)
            aMasterFieldValue=auxRadio[auxRadio.length-1].value;

        for(var i=0; i<auxRadio.length; i++)
          if (auxRadio[i].checked)
            theFieldValue=auxRadio[i].value;
      } else if (field.type=='checkbox') {
        if (field.checked)
          theFieldValue=field.value;
      } else
        theFieldValue=field.value;

      if (aMasterFieldValue==undefined)
        aMasterFieldValue=theFieldValue;


      if (aMasterFieldValue==theFieldValue)
        var aDisplay='';
      else
        var aDisplay='none';

      if (element.type=='table') {
        for (var r=0; r<element.rows.length; r++)
          element.rows[r].style.display=aDisplay;
      } else
        element.style.display=aDisplay;
    }
  }
}

function dynSetDisplay(aElementSet, aDisplayStyle)
{
  for(var i=0; i<aElementSet.length; i++) {
    aElementSet[i].style.display=aDisplayStyle;
    _dumpy(2,1,aElementSet[i].id, aElementSet[i].style.display);
  }
}

function dynSetVisibility(aElementSet, aVisibility)
{
  for(var i=0; i<aElementSet.length; i++) {
    aElementSet[i].style.visibility=aVisibility;
    _dumpy(2,1,aElementSet[i].id, aElementSet[i].style.visibility);
  }
}

function dynRemoveElements(aElementSet)
{
  for(var i=aElementSet.length-1; i>=0; i--) {
    var pai = aElementSet[i].parentNode;
    pai.removeChild(aElementSet[i]);
  }
}

function dynTablePrint(aTable, aWidth, aHeight)
{
}

function calcGridAddItem(aCalcGrid, aFieldValues, aNewFieldName, aTitle, aFieldList, aCalcExpr, aResultCellPostfix, aForce, aUnits, aDecimalPlaces)
{
  if (aForce==undefined)
    aForce=false;

  if (aUnits==undefined)
    aUnits='';

  if (aDecimalPlaces==undefined)
    aDecimalPlaces='2';

  var canAdd = true;
  var aFields = aFieldList.split(',');
  if (!aForce) {
    for(var i in aFields)
      if (aFields.hasOwnProperty(i))
        if (aFieldValues[aFields[i]]==undefined)
          canAdd = false;
  }
  if (canAdd) {
    if (aCalcGrid[aNewFieldName]==undefined) {
      aCalcGrid[aNewFieldName]=new Array();
      aCalcGrid[aNewFieldName]['title']=aTitle;
      aCalcGrid[aNewFieldName]['fieldList']=aFieldList;
      aCalcGrid[aNewFieldName]['calcExpr']=aCalcExpr;
      aCalcGrid[aNewFieldName]['resultCellPostfix']=aResultCellPostfix;
      aCalcGrid[aNewFieldName]['units']=aUnits;
      aCalcGrid[aNewFieldName]['decimalPlaces']=aDecimalPlaces;
    } else
      console.log("Field '"+aNewFieldName+"' already exists in calcGrid");
  } else
    console.log("Some fields does not exists in ("+aFieldValues+") list");
}

var _cg_rules = new Array();

function calcGridSetRules(aCalcGrid, aFieldList, aOnColumns)
{
  if (_cg_rules[aCalcGrid.id]==undefined)
    _cg_rules[aCalcGrid.id] = new Array();
  _cg_rules[aCalcGrid.id]['rules']=aFieldList;
  _cg_rules[aCalcGrid.id]['onColumns']=aOnColumns;
}

function calcGridSetCellsGuides(aCalcGrid, aColSet, aRowSet)
{
  if (_cg_rules[aCalcGrid.id]==undefined)
    _cg_rules[aCalcGrid.id] = new Array();
  _cg_rules[aCalcGrid.id]['area']=new Array();
  _cg_rules[aCalcGrid.id]['area']['colSet']=aColSet;
  _cg_rules[aCalcGrid.id]['area']['rowSet']=aRowSet;
}

function calcGridGetColsGuide(aCalcGrid)
{
  var ret=null;
  if (aCalcGrid!=undefined) {
    if (_cg_rules[aCalcGrid.id]!=undefined) {
      ret = _cg_rules[aCalcGrid.id]['area']['colSet']
    }
  }

  return ret;
}

function calcGridGetRowsGuide(aCalcGrid)
{
  var ret=null;
  if (aCalcGrid!=undefined) {
    if (_cg_rules[aCalcGrid.id]!=undefined) {
      ret = _cg_rules[aCalcGrid.id]['area']['rowSet']
    }
  }

  return ret;
}

function calcGridEnumerateCells(aCalcGrid, aFunction, aSchema)
{
  var ok=false;
  var aRules = _cg_rules[aCalcGrid.id];
  if (aRules) {
    var aColSet=aRules['area']['colSet'];
    var aRowSet=aRules['area']['rowSet'];
    for(var r in aRowSet) {
      if (aRowSet.hasOwnProperty(r)) {
        for(var c in aColSet)
          if (aColSet.hasOwnProperty(c)) {
            var aCell=y$(r+'_'+aColSet[c].name);
            if (aCell) {
              if (aSchema!=undefined) {
                ok=false;
                if (aSchema.editable)
                  ok=(aColSet[c].editable && aRowSet[r].editable);
                if (aSchema.name)
                  ok=ok || (aColSet[c].name==aSchema.name) || (aRowSet[r].name==aSchema.name);
              } else
                ok=true;
              if (ok)
                aFunction(aCell);
            }
          }
      }
    }
  }
}

function calcGridCleanContent(aCalcGrid)
{
  var aFields=new Array();
  aFields['value']='';
  aFields['calcGridSet']=aCalcGrid.id;

  calcGridEnumerateCells(aCalcGrid,
    function(a)
    {
      dynSetCellValue(a.id, aFields);
    }
  );
}

function calcGridCleanColumn(aCalcGrid, aColKey)
{
  var aRules = _cg_rules[aCalcGrid.id];
  if (aRules) {
    var aColSet=aRules['area']['colSet'];
    var aRowSet=aRules['area']['rowSet'];
    var aCol = aColSet[aColKey];
    var aCell;
    if (aCol) {
      for(var r in aRowSet) {
        if (aRowSet.hasOwnProperty(r)) {
          aCell=y$(r+'_'+aCol.name);
          if (aCell)
            dynSetCellValue(aCell.id,'');
        }
      }
    }
  }
}


function calcGridGetRules(aCalcGrid)
{
  var aRules = _cg_rules[aCalcGrid.id];
  if (aRules)
    return aRules['rules'];
  else
    return {};
}

function calcGridGetAssociatedRule(aCalcGrid, aFieldName)
{
  var ret=null;
  var aRules = calcGridGetRules(aCalcGrid);
  for(var f in aRules) {
    if (ret==null) {
      if (aRules.hasOwnProperty(f)) {
        if (aFieldName==f)
          ret=aRules[f];
      }
    }
  }
  return ret;
}

function calcGridGetRuleTitle(aCalcGrid, aFieldName)
{
  var ret=null;
  var theRule=calcGridGetAssociatedRule(aCalcGrid, aFieldName);
  if (theRule!=null)
    ret=theRule['title'];
  return ret;
}

function calcGridGetNextFieldName(aCalcGrid, aFieldName)
{
  var ret=null;
  var aRules = calcGridGetRules(aCalcGrid);
  var nextIsField=false;
  for(var f in aRules) {
    if (ret==null) {
      if (aRules.hasOwnProperty(f)) {
        if (nextIsField)
          ret=f;
        nextIsField=(f==aFieldName);
      }
    }
  }
  return ret;
}

function calcGridRecalc(aCalcGrid, aCellName)
{
  var aRules=_cg_rules[aCalcGrid.id];
  if (aRules) {
    aRules = aRules['rules'];
    for(var r in aRules)
      if (aRules.hasOwnProperty(r)) {
        var rule=aRules[r];
        if (rule['fieldList']) {
          if (rule['fieldList'].indexOf(aCellName) >= 0 ) {
            var aFieldList=rule['fieldList'].split(',');
            var aStack=new Array();
            for(var f in aFieldList)
              if (aFieldList.hasOwnProperty(f)) {
                aStack[aFieldList[f]]=y$(aFieldList[f]).innerHTML;
              }

            var aResultCellName;
            if (rule['resultCellPrefix']>'')
              aResultCellName=rule['resultCellPrefix']+'_'+r;
            else if (rule['resultCellPostfix']>'')
              aResultCellName=r+'_'+rule['resultCellPostfix'];
            else
              aResultCellName=r;
            var aResult = Parser.evaluate(rule['calcExpr'], aStack);
            var aZero=0.00;
            aZero = aZero.toFixed(rule['decimalPlaces']);
            aResult=aResult.toFixed(rule['decimalPlaces']);
            aResult =  (isNaN(aResult)) ? aZero : (isInfinity(aResult) ? aZero : aResult+rule['units']);
            y$(aResultCellName).innerHTML=aResult;
            y$(aResultCellName).style.border='solid 1px #96CBFF';
          }
        }
      }
  }
}

function dynTableGetCellParentGrid(aCellId)
{
  var aCell=y$(aCellId);
  var aParent=aCell.parentNode;
  while (aParent.tagName!='TABLE')
    aParent=aParent.parentNode;

  if (aParent.tagName=='TABLE')
    return aParent;
  else
    return null;
}

function dynTableCreate(aTableContainer, aTableID)
{
  if (aTableContainer) {
    var fTable = document.createElement('table');
    fTable.id=aTableID;
    fTable.name=aTableID;
    aTableContainer.appendChild(fTable);
  } else
    console.log('Error: You cannot create a dynTable without a div to contain it');
  return fTable;
}

function dynTableSetRowTitles(aTable, aFirstRow, aFieldList, aGraphFunctionName)
{
  for(var k in aFieldList)
    if ((aFieldList.hasOwnProperty(k)) && (k!='_context_')) {
      while (aTable.rows.length<aFirstRow) {
        var aRow=aTable.insertRow(aTable.rows.length);
        var aCell=aRow.insertCell(0);
      }
      var aRow = aTable.insertRow(aTable.rows.length);
      var aCell = aRow.insertCell(0);
      aCell.id=k;
      if (aFieldList[k].parent>'') {
        aCell.style.paddingLeft='18px';
        aCell.style.fontSize='80%';
      }
      var aTitle=aFieldList[k].title;
      if (undefined != aGraphFunctionName)
        if (aFieldList[k].graph)
          aTitle='<a href="javascript:'+aGraphFunctionName+'(\''+aTable.id+'\',\''+k+'\')">'+aTitle+'</a>';
      aCell.innerHTML=aTitle;
    }
}

function dynTableSetColTitles(aTable, aFirstCol, aSequence)
{
  var aRow, aCell;

  var i=aFirstCol;
  for(var k in aSequence) {
    if (aSequence.hasOwnProperty(k)) {
      for(var r=0; r<aTable.rows.length; r++) {
        aRow=aTable.rows[r];
        while (aRow.cells.length<aFirstCol)
          aCell=aRow.insertCell(aRow.cells.length);
        aCell=aRow.insertCell(i);
        if (r==0)
          aCell.innerHTML=aSequence[k].title;
        aCell.id=aTable.rows[r].cells[0].id+'_'+aSequence[k].name;
        aCell.style.textAlign='center';
      }
      i++;
    }
  }
}

function dynTableSetColWidth(aTable, aWidth,aStart,aFinish)
{
  if (aStart==undefined)
    var aStart=0;
  if (aFinish==undefined)
    var aFinish=aTable.rows[aTable.rows.length-1].cells.length;
  for(var c=aStart; c<=aFinish; c++)
    for (var r=0; r<aTable.rows.length; r++)
      if (aTable.rows[r].cells[c] != undefined)
        aTable.rows[r].cells[c].style.minWidth=aWidth+'px';
}

function dynTableSetRowHeight(aTable, aHeight, aStart, aFinish)
{
  if (aStart==undefined)
    var aStart=0;
  if (aFinish==undefined)
    var aFinish=aTable.rows.length-1;

  for (var r=aStart; r<=aFinish; r++)
    aTable.rows[r].style.height=aHeight+'px';
}

function dynSetCellValue(aCellName, aCellValue)
{
  if (y$(aCellName)) {

    var aCoordinates=aCellName.split('_');
    var aCalcGrid=dynTableGetCellParentGrid(aCellName);
    var aRule = calcGridGetAssociatedRule(aCalcGrid, aCoordinates[0]);
    if (aStack==undefined) {
      var aFieldList = calcGridGetRules(aCalcGrid);
      var aStack=new Array();
      for (var f in aFieldList)
        if ((f!=undefined) && (f!='_context_'))
          if (aFieldList.hasOwnProperty(f)) {
            aStack[f]=y$(f+'_'+aCoordinates[1]).innerHTML;
          }
    }

    var canChangeCellValue=true;

    if (y$(aCellName).innerHTML!='') {
      if ((aRule.minVal!=undefined) && (aRule.minVal>'')) {
        var checkMinVal = str2int(aCellValue.value)+' >= '+aRule.minVal;
        var canChangeCellValue=Parser.evaluate(checkMinVal, aStack);
        console.log(checkMinVal+' = '+canChangeCellValue);
      }
      if (canChangeCellValue) {
        if ((aRule.maxVal!=undefined) && (aRule.maxVal!='')) {
          var checkMaxVal = aRule.maxVal+' >= '+str2int(aCellValue.value);
          var canChangeCellValue=Parser.evaluate(checkMaxVal, aStack);
          console.log(checkMaxVal+' = '+canChangeCellValue);
        }
      }
    }

    if (canChangeCellValue) {
      var aTotal=aCoordinates[0]+'_total';
      var priorValue=str2int(y$(aCellName).innerHTML);
      y$(aCellName).innerHTML=aCellValue.value;

      if (y$(aTotal)) {
        var vTotal=str2int(y$(aTotal).innerHTML);
        vTotal=vTotal-priorValue+str2int(aCellValue.value);
        y$(aTotal).innerHTML=vTotal;
        var calcGridList = aCellValue.calcGridSet.split(',');
        for (var aCG in calcGridList)
          if (calcGridList.hasOwnProperty(aCG)) {
            if (calcGridList[aCG]>'') {
              var auxCalcGrid=y$(calcGridList[aCG]);
              calcGridRecalc(auxCalcGrid, aTotal);
            }
          }
      }
      // table -> tr -> td
      //var aCalcGrid=y$(aCellName).parentNode.parentNode.parentNode;
      if (aRule!=undefined) {
        var notificationFormId=aRule.notificationFormId;
        if (notificationFormId>'') {
          var fl='fl_'+notificationFormId;

          var aFields = this[fl];
          if (aFields) {
            aFields['_position']=new Array();
            aFields['_position']['name']='_position';
            aFields['_position']['type']='hidden';
            aFields['_position']['value']={ 'x': 100, 'y': 0};

            askValue('/','javascript:dynSaveForm()',aFields);
          }
        }
      }

      if (aCellValue.openNextField) {
        var nextField=calcGridGetNextFieldName(aCalcGrid, aCoordinates[0]);
        var nextCellName=nextField+'_'+aCoordinates[1];
        if (y$(nextCellName))
          y$(nextCellName).click();
      }
    } else {
      console.log("O Valor não pode ser lançado por não cumprir condições de existência");
      window.alert("O valor não é consistente.\nRevise valores do campo pai e o próprio valor lançado\nTente novamente");
      y$(aCellName).click();
    }
  }
}

function showCellInfo(aElement, show, aEditableFlag)
{
  var aMessage = '';

  if (aElement)
    aMessage = aElement.parentElement.firstChild.innerHTML+'<br><small>Dia: '+aElement.cellIndex+'</small>';

  if (!aEditableFlag)
    var aStyle='color:#aaa';
  else
    var aStyle='font-weight: 800; color:black';

  aMessage="<div style='"+aStyle+"'>"+aMessage+"</div>";

  var tip=document.getElementById('tipDiv');
  if (tip) {
    if (show) {
      tip.style.display='block';
      var aTargetX = getX(aElement) + aElement.offsetWidth;
      var aTargetY = getY(aElement) + aElement.offsetHeight;
      new Effect.Move('tipDiv', { x: aTargetX, y: aTargetY, mode: 'absolute', duration: .3});
    }
    tip.innerHTML = aMessage;
  }
}

function dynSetEditableCell(aTable, aColName, aRowName, aCalcGridSet, aEditable, aOnChangesFuncName)
{
  var aCellName=aRowName+'_'+aColName;
  var aCell=y$(aCellName);
  if (aCell) {
    // aCell.contentEditable=aEditableFlag;
    aCell.onmouseover=function() {
       //alert(this.parentElement.firstChild.innerHTML + this.cellIndex);
       showCellInfo(this, true, aEditable);
    }
    aCell.onmouseout=function() {
       //alert(this.parentElement.firstChild.innerHTML + this.cellIndex);
       showCellInfo(this, false, aEditable);
    }
    if (aEditable) {
      aCell.onclick=function() {
        // var aRowName=this.id.split('_')[0];
        var aFieldTitle = calcGridGetRuleTitle(this.parentNode.parentNode.parentNode, aRowName);
        if (aFieldTitle==null)
          aFieldTitle='Valor';

        var aFields=new Array();
        aFields['valor']=new Array();
        aFields['valor']['title']=aFieldTitle+' / '+aColName;
        aFields['valor']['name']='value';
        aFields['valor']['type']='integer';
        aFields['valor']['width']='4';
        aFields['valor']['value']=aCell.innerHTML;

        aFields['calcGridSet']=new Array();
        aFields['calcGridSet']['name']='calcGridSet';
        aFields['calcGridSet']['type']='hidden';
        aFields['calcGridSet']['value']=aCalcGridSet;

        aFields['openNextField']=new Array();
        aFields['openNextField']['name']='openNextField';
        aFields['openNextField']['type']='hidden';
        aFields['openNextField']['value']=1;

        var x=getX(aCell) + parseInt(aCell.offsetWidth);
        var y=getY(aCell) + parseInt(aCell.offsetHeight);

        aFields['_position']=new Array();
        aFields['_position']['name']='_position';
        aFields['_position']['type']='hidden';
        aFields['_position']['value']={ 'x': x, 'y': y};
        aFields['_position']['onChangesFuncName']=aOnChangesFuncName;

        askValue('/','javascript:dynSetCellValue("'+aRowName+'_'+aColName+'")',aFields); ;
      };
    } else
      aCell.onclick=null;
  }
}

function dynSetEditableCells(aTable, aRowFields, aColFields, aOnChangesFuncName)
{
  for(var r in aRowFields)
    if (aRowFields.hasOwnProperty(r))
      for(var c in aColFields) {
        if (aColFields.hasOwnProperty(c)) {
          dynSetEditableCell(aTable,
                             aColFields[c].name, aRowFields[r].name,
                             [aColFields[c]['calcGridAssoc'], aRowFields[r]['calcGridAssoc']],
                             (aColFields[c].editable) && (aRowFields[r].editable),
                             aOnChangesFuncName);
        }
    }
}

function dynSetClickableHeaders(aTable, aRowFields, aColFields, aOnClick)
{
  for(var c in aColFields)
    if (aColFields.hasOwnProperty(c)) {
      var aCell=y$('_'+aColFields[c].name);
      if (aCell) {
        aCell.onclick=aOnClick;
      }
    }
}

function dynSetClickableRowHeaders(aTable, aRowFields, aColFields, aOnClick)
{
  for(var r in aRowFields)
    if (aRowFields.hasOwnProperty(r)) {
      var aCell=y$(aRowFields[r].name);
      if (aCell) {
        aCell.onclick=aOnClick;
      }
    }
}

function sequenceSetValue(aSequence, aKey, aValue)
{
  for (var n in aSequence)
    if (aSequence.hasOwnProperty(n)) {
      aSequence[n][aKey]=aValue;
    }
}

function sequenceAdd(aSequence, aValue)
{
  /*
  var n=aSequence.length;
  aSequence[n]=new Array();
  aSequence[n]=aValue;
  */
  aSequence[aValue.name]=aValue;
}

function sequenceProducer(aFirstValue, aLastValue, aInc)
{
  var ret=new Array();
  if (aInc>0) {
    for(var n=aFirstValue; n<=aLastValue; n+=aInc) {
      var aValue=new Array();
      aValue.title=n;
      aValue.name=n;
      sequenceAdd(ret, aValue);
    }
  } else if (aInc<0) {

  } else
    console.log("You cannot create a non increment sequence");
  return ret;
}

function fillTable(aTableId, aSQL, aFieldOrder, aLink, aFieldIdName, aOnData, aOnReady, aOnSelect, aFlags)
{
  ycomm.invoke('yeapfDB',
               'doSQL',
               { 'sql': '"'+aSQL+'"' },
             function(status, xError, xData) {
                /*@20150330 hideWaitIcon();*/
                console.log(status, aTableId);

                var aFieldList = aFieldOrder.split(',');
                var aRows   = '';
                for(var i=0; i<aFieldList.length; i++)
                  aRows=aRows+'<td><a href="{1}">%({0})</a></td>'.format(aFieldList[i], aLink);

                ycomm.dom.fillElement(
                  aTableId,
                  xData,
                  { 'onNewItem':   aOnData,
                    'rows':        [aRows],
                    'inplaceData': [aFieldIdName],
                    'onNewItem':   aOnData,
                    'onReady':     aOnReady }
                );
                /*
                var oTable = y$(aTableId);
                if (oTable) {
                  var aContainer=oTable.parentNode;
                  if (aContainer)
                    aContainer.style.display='block';

                  var aHeaderLines=1;
                  var aBtnFlags=0;
                  if (aFlags) {
                    if (aFlags.headerLines!=undefined)
                      aHeaderLines = parseInt(aFlags.headerLines);
                    if (aFlags.btnFlags!=undefined)
                      aBtnFlags=parseInt(aFlags.btnFlags);
                  }

                  while(oTable.rows.length>aHeaderLines)
                    oTable.deleteRow(oTable.rows.length-1);
                  console.log(aFieldOrder);
                  var aFieldList=aFieldOrder.split(',');

                  for (var j in xData) {
                    if (xData.hasOwnProperty(j)) {
                      var aux=new Array();
                      var ndx=0;
                      var rowId='';
                      for (var col in aFieldList) {
                        if (aFieldList.hasOwnProperty(col)) {
                          var aColData=xData[j][trim(aFieldList[col])] || '';
                          if (aOnData!=undefined)
                            aColData=aOnData(aColData, trim(aFieldList[col]), aTableId, j);
                          aux[ndx++]=aColData;
                          if (trim(aFieldList[col])==aFieldIdName)
                            rowId=aColData;
                        }
                      }

                      if (rowId=='') {
                        for(var col in xData[j])
                          if (xData[j].hasOwnProperty(col)) {
                            if ((col==aFieldIdName) && (col>'')) {
                              aColData=xData[j][col];
                              if (aOnData!=undefined)
                                aColData=aOnData(aColData, trim(col), aTableId, j);
                              rowId=aColData;
                            }
                          }
                      }

                      if (rowId=='')
                        rowId=aTableId+'_'+j;

                      addRow(aTableId, aux, aLink, aBtnFlags, 0, -1, aOnSelect).id=rowId;
                    }
                  }
                }
                if (aOnReady!=undefined)
                  aOnReady(aTableId);
                  */
             });
}

function getCheckboxTable(aCheckboxID)
{
  if (y$(aCheckboxID).parentElement)
    return y$(aCheckboxID).parentElement.parentElement
  else
    return null;
}

function getAllCheckboxInTable(aTable)
{
  var ret={};
  var chk = aTable.getElementsByTagName('input');
  var len = chk.length;
  var n=0;

  for (var i = 0; i < len; i++) {
    if (chk[i].type === 'checkbox') {
        ret[n++]=chk[i];
    }
  }
  return ret;
}

function getFormSelectOptions(aArray, aFormName, aFormField, aOnReady)
{
  aArray.length=0;
  ycomm.invoke('yeapfDB',
             'getFormSelectOptions',
             { 'formName': '"'+aFormName+'"',
               'formField': '"'+aFormField+'"' },
             function(status, xError, xData) {
                /*@20150330 hideWaitIcon();*/
                console.log(status, aFormName+'.'+aFormField);
                for (var j in xData) {
                  if (xData.hasOwnProperty(j)) {
                    for(var k in xData[j])
                      if ((xData[j].hasOwnProperty(k)) && (k!='rowid'))
                        aArray[k]=xData[j][k];
                  }
                }
                if (aOnReady!=undefined)
                  aOnReady(aArray, aFormName, aFormField);
             });
}

function $value(aID, aDefaultValue)
{
  var a=y$(aID);
  if (a)
    return a.value;
  else
    return aDefaultValue;
}

function __saveFormInfo(e)
{
  var s=$value('s','');
  var a=$value('a','');
  a = 'save'+a.ucFirst();
  var id=$value('id','');

  var v;
  if (e.type=='checkbox')
    v=e.checked?e.value:'';
  else if (e.type=='text')
    v=e.value;

  var jFieldName = e.id;
  jFieldName = jFieldName.replace('.','_');

  _dumpy(2,1,'u',u,'s',s,'a',a,'id',id,'eID',e.id,'v',v);

  _DO(s,a,'(id,'+jFieldName+')','('+id+','+v+')');
}

function __dynOnChange__(e)
{
  if (e==undefined)
    var e = window.event || arguments.callee.caller.arguments[0];
  if (e.target)
    e = e.target;

  if (e.childOpenCondition)
    console.log(e.childOpenCondition);
}

function __cbOnChange__(e, saving)
{
  // procurar elemento que chamou esta função
  if (e==undefined)
    var e = window.event || arguments.callee.caller.arguments[0];
  if (e.target)
    e = e.target;

  if (saving==undefined)
    saving=e.dynSaveOnChange;

  _dumpy(2,1,'check ',e.id,e.checked);

  if (e.dynOnChange != undefined)
    e.dynOnChange();
  // salvar informação
  if (saving)
    __saveFormInfo(e);
/*
      for (var n=i+1; n<allElements.length; n++)
        if (allElements[n].id.substr(0,auxID.length)==auxID) {
          dynSetElementDisplay(allElements[n].id, aElementSet[i].id, aElementSet[i].value);
          // aElementSet[n].style.visibility=aElementSet[i].checked?'visible':'hidden';
          ccDependents++;
        }
 */
  // corrigir visualização dos elementos dependentes do clicado
  dynCleanChilds(e, document);

}
