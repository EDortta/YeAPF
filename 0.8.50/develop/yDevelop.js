/*
    develop/yDevelop.js
    YeAPF 0.8.50-10 built on 2016-08-29 09:16 (-3 DST)
    Copyright (C) 2004-2016 Esteban Daniel Dortta - dortta@yahoo.com
    2016-01-23 22:00:39 (-3 DST)
*/
if (typeof resizeIframe == 'undefined') {
  /* redimensiona o frame quando o documento é carregado. */
  function resizeIframe(obj) {
    var s1 = screen.height;
    var s2 = obj.contentWindow.document.body.scrollHeight + 40;
    var bestSize=Math.max(s1, s2);
    obj.style.height = bestSize + 'px';
  }
}

if (typeof $ =='undefined')
  (
    $ = function (aElementId) {
      var ret=document.getElementById(aElementId);
      if (!ret)
        ret=document.getElementsByName(aElementId)[0];
      return ret;
    }
  )();


/*
 * abra a aba desejada (aTabId)
 * fechando todas as abas que pertencam à mesma classe especificada (aClassName)
 */
function openTabById(aClassName, aTabId) {
  var myList = document.getElementsByClassName(aClassName);
  for(var ndx=0; ndx<myList.length; ndx++)
    myList[ndx].style.display='none';

  var aTab = $(aTabId);
  if (typeof aTab != 'undefined')
   aTab.style.display='block';

}

/*
 * abre a aba que contêm os sub-itens do select tocado
 * ou seja, se escolheu Banco de dados, mostra a aba correspondente
 */
function yDevelopMenu(e) {
  if (e)
    openTabById('yDevelopSubMenu', 'yds_'+e[e.selectedIndex].value);
}

var yDevelop=function() {
  var that = {};

  that.fillList=function(aUL, data) {
    if (aUL) {
      while (aUL.firstChild) {
        aUL.removeChild(aUL.firstChild);
      }
      for(var n in data) {
        if (data.hasOwnProperty(n)) {
          var myDesc='';
          if (typeof data[n]['description'] != 'undefined') {
            var auxDesc=data[n]['description'].split(';');
            for(var j=0; j<auxDesc.length; j++) {
              if (myDesc>'')
                myDesc+='<br>';
              myDesc+=yAnalise(auxDesc[j]);
            }
          }

          var a = document.createElement('a');
          a.setAttribute('href', yAnalise(data[n]['href']));
          a.innerHTML="<b>"+yAnalise(data[n]['label'])+"</b><div class=description>"+myDesc+"</div>";
          if (data[n]['isDefault']==1)
            a.style.color='#F8F876';

          var newLi = document.createElement("li");
          newLi.id=data[n]['appRegistry'];

          newLi.appendChild(a);
          aUL.appendChild(newLi);
       }
     }
    } else
      console.log("UL Não definido");
  };

  that.joinHTML=function(data) {
    var auxHTML='';
    for(var n in data) {
      if (data.hasOwnProperty(n)) {
        var t=typeof data[n]['html'];
        if (t=='string')
          auxHTML+=data[n]['html'];
        else if (t=='object')
          auxHTML+=that.joinHTML(data[n]['html']);
        else if (t=='undefined') {
          for(var k in data[n]) {
            if (data[n].hasOwnProperty(k))
              auxHTML+=data[n][k];
          }
        }
      }
    }
    return auxHTML;
  }

  that.createFormAndFill = function(aDiv, data) {
    var auxHTML=that.joinHTML(data);

    aDiv.innerHTML=yAnalise(auxHTML);
  };

  that.fillForm = function(aForm, data) {
    var formElements = that.getFormFields(aForm);
    for(var i in formElements) {
      if (formElements.hasOwnProperty(i)) {
        if (typeof data[0][i] != 'undefined')
          formElements[i].value = data[0][i];
      }
    }
  };

  /*
   * pega todos os elementos de um form e os acomoda em um objeto
   * porem, so os que tiverem ID
   */
  that.getFormFields = function (aForm) {
    var ret={};
    for(var i=0; i<aForm.elements.length; i++) {
      if (aForm.elements[i].id>'')
        ret[aForm.elements[i].id]=aForm.elements[i]
    }
    return ret;
  }

  that.getFormElements = function(aFormName) {
    var inputs=document.forms[aFormName].getElementsByTagName("input");
    var selects=document.forms[aFormName].getElementsByTagName("select");
    var textarea=document.forms[aFormName].getElementsByTagName("textarea");
  };

  that.deleteItem = function(aFormName, a0) {
    var fieldValues = {};
    fieldValues['XID'] = $('XID').value;
    fieldValues['deleteChildrens']=($('_deleteChildrens_').checked?'Y':'N');
    that.invoke(this, a0+':deleteItem', fieldValues);
  };

  that.saveForm = function(aFormName, a0) {
    var fieldValues={}; var aux=[]; var n=0;
    var iName;
    var iValue;
    /*
    var inputs=document.forms[aFormName].getElementsByTagName("input");
    var selects=document.forms[aFormName].getElementsByTagName("select");
    var textarea=document.forms[aFormName].getElementsByTagName("textarea");
    */
    var formElements=document.forms[aFormName].elements;
    for(var k in formElements) {
      if (formElements.hasOwnProperty(k)) {
        iValue='';
        iName=(formElements[k].name) || (formElements[k].id);
        if (aux.indexOf(iName)<0) {
          if (formElements[k].type=='checkbox') {
            if (formElements[k].checked)
              iValue=formElements[k].value;
          } else if (formElements[k].type=='select-one') {
            iValue=formElements[k].selectedIndex;
            if (formElements[k].options[iValue])
              iValue = formElements[k].options[iValue].value;
          } else
            iValue=formElements[k].value;
          fieldValues[iName]=iValue;
          aux[n++]=iName;
        }
      }
    }
    that.invoke(this, a0+':saveForm', fieldValues);
  }

  that.toggleLeftMenu = function () {
    var leftMenu = $('leftMenu');
    if (leftMenu.style.display=='block') {
      leftMenu.style.display = 'none';
      $('adminContainer').style.marginLeft='0px';
      yDevelopReturn(null);
    } else {
      leftMenu.style.display = 'block';
      $('adminContainer').style.marginLeft='200px';
    }
  }

  that.home = function () {
    var applicatioMainFrame_ = $('applicatioMainFrame_');
    applicatioMainFrame_.src = 'index.php';
    yDevelopReturn(null);
  }

  that.reload = function () {
    var applicatioMainFrame_ = $('applicatioMainFrame_');
    applicatioMainFrame_.src = applicatioMainFrame_.src;
    yDevelopReturn(null);
  }

  that.invoke=function(e, a, oFieldValues) {
    var aAux=a.split(':');
    var eventName=aAux[0];
    var eventVerb=aAux[1];
    ycomm.invoke( 'yeapf:develop',
                  a,
                  oFieldValues,
                  function(status, error, data) {
                    if (eventVerb=='getList') {

                      listID=eventName+'List';
                      /* identificar a lista a ser preenchida */
                      ul=$(listID);
                      if (ul) {
                        /* preencher a lista com os dados que vieram do servidor */
                        that.fillList(ul, data);
                      } else
                        console.log(listID+' not found!');

                    } else if ((eventVerb=='getForm') || (eventVerb=='newItem')) {

                        formID=eventName+'_Body';
                        that.createFormAndFill($(formID), data);

                        if (eventVerb=='newItem')
                          $('Xancestor').value = myDevelop.menuPath[myDevelop.menuPath.length-1];

                    } else if (eventVerb=='getFormValues') {

                        formID=eventName+'_Form';
                        that.fillForm($(formID), data);

                    } else if (eventVerb == 'open') {

                      $('yah_returnToApp').style.display='block';
                      openTabById('bodyTab', eventName+'_Body');

                      that.invoke(e, eventName+':getList');


                    } else if ((eventVerb=='deleteItem') || (eventVerb=='saveForm')) {

                      /* Após salvar o formulário, recarregamos a lista */
                      that.invoke(e, eventName+':getList');

                    } else if (eventVerb=='getMenuRoot') {
                      if ((data) && (data[0]))
                        myDevelop.setMenuRoot(data[0]['menuRoot']);

                    }
                 });
    return that;
    }

    return that;
}

/* criamos um objeto do yDevelop */
var myDevelop=yDevelop();

/* pilha de menus escolhidos */
myDevelop.menuPath=[];
/* menu principal do aplicativo (definido no db.csv) */
myDevelop.menuRoot=null;

myDevelop.flipSpan = function(aActiveID, aInactiveID) {
  var a=$(aActiveID);
  var i=$(aInactiveID);
  i.style.display='none';
  a.style.display='block';
}

/* evento de chegada do menu escolhido
 * se for a primeira vez, guardar o menu principal do aplicativo */
myDevelop.setMenuRoot = function(aMenuRoot) {

  if (myDevelop.menuPath.length==0) {
    myDevelop.menuRoot=aMenuRoot;
    myDevelop.flipSpan('yds_dbMenuRefresh', 'yds_dbMenuBack');
  } else
    myDevelop.flipSpan('yds_dbMenuBack','yds_dbMenuRefresh');
  myDevelop.menuPath[myDevelop.menuPath.length]=aMenuRoot;
  myDevelop.invoke($('yds_dbMenu'), 'dbMenu:getList', {ancestor: aMenuRoot});
}

/* voltar na trilha de menu um nível */
myDevelop.backMenuHistory = function() {
  /* caso haja info na trilha, pegar o último elemento */
  if (myDevelop.menuPath.length>0) {
    var aux=myDevelop.menuPath.slice(0,myDevelop.menuPath.length-1);
    myDevelop.menuPath=aux;
    var aMenuRoot = myDevelop.menuPath[myDevelop.menuPath.length-1] || myDevelop.menuRoot;
    myDevelop.invoke($('yds_dbMenu'), 'dbMenu:getList', {ancestor: aMenuRoot});
  } else {
    myDevelop.invoke($('yds_dbMenu'), 'dbMenu:getMenuRoot');
  }

  /* ligar os icones apropriados */
  if (myDevelop.menuPath.length>1)
    myDevelop.flipSpan('yds_dbMenuBack','yds_dbMenuRefresh');
  else
    myDevelop.flipSpan('yds_dbMenuRefresh', 'yds_dbMenuBack');
}

/* voltar a mostrar a tela do aplicativo escondendo a de desenvolvimento */
function yDevelopReturn(e) {
  $('yah_returnToApp').style.display='none';
  $('yds_returnToApp').style.display='none';
  openTabById('bodyTab', 'guestBody');
}

/*
 * Funções para a edição da lista 'to-do'
 */
function ytodoSaveForm(e) {
  var formValues = myDevelop.getFormFields($('todo_Form'));
  var id = formValues['id'].value;
  var todoItem = formValues['todoItem'].value;
  myDevelop.invoke(e,
                   'todo:saveForm',
                   {
                     'id': id,
                     'todoItem': todoItem
                   }
                  );
}

function ydbTodoViewEntry(todoObj) {
  myDevelop.invoke($('todo_Body'), 'todo:getFormValues', todoObj );
}

/*
 * Funções que respondem aos clicks dos items carregados via REST
 */

/* dbConn */
function ydbConnViewEntry(dbConnID) {
  $('yds_returnToApp').style.display='block';
  openTabById('bodyTab', 'dbConn_Body');
  myDevelop.invoke($('dbConn_Body'), 'dbConn:getForm', dbConnID);
}


/* dbTables */
function ydbTablesViewEntry(dbTableName) {
  $('yds_returnToApp').style.display='block';
  openTabById('bodyTab', 'dbTables_Body');
}

/* dbMenu - click no item */
function ydbMenuViewEntry(dbMenuS) {
  $('yds_returnToApp').style.display='block';
  openTabById('bodyTab', 'dbMenu_Body');
  myDevelop.setMenuRoot(dbMenuS.ancestor);
  myDevelop.invoke($('dbMenu_Body'), 'dbMenu:getForm', dbMenuS);
}

/* dbMenu - click no escudo */
function ydbMenuEditMenuEntry(dbMenuS) {
  $('yds_returnToApp').style.display='block';
  openTabById('bodyTab', 'dbMenu_Body');
  myDevelop.invoke($('dbMenu_Body'), 'dbMenu:getForm', dbMenuS);
}

function ydbMenuLaunch(dbMenuS, dbMenuA) {
  yDevelopReturn();
  $('applicatioMainFrame_').src="body.php?s={0}&a={1}&u={2}".format(dbMenuS, dbMenuA, null);
}

function ydbMenuCreateEvent(dbMenuS, dbMenuA) {
  yDevelopReturn();
  $('applicatioMainFrame_').src="body.php?_Ys_=createSkeletonImplementation&s={0}&a={1}&u={2}".format(dbMenuS, dbMenuA, null);
  setTimeout(myDevelop.backMenuHistory, 1250);

}

/*=================================== ONLOAD!
  ativar o menu principal */
yDevelopMenu($('yds_options'));

/* carregar a lista de conexões */
myDevelop.invoke($('yds_dbConnection'), 'dbConn:getList');

/* carregar a lista de tabelas (da conexão ativa) */
myDevelop.invoke($('yds_dbTables'), 'dbTables:getList');

/* carregar a lista de eventos (da conexão ativa) */
myDevelop.invoke($('yds_dbMenu'), 'dbMenu:getMenuRoot');

