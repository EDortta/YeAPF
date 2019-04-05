var htmlEditorObject = function() {
  var that = {};
  that.mainTarget = y$('mainTarget');
  that.status = y$('status');

  /* DragAndDrop */
  that.componentDropped = function(aDraggedElement) {
    var componentName = aDraggedElement.getAttribute("data-component");
    var newId = function() {
      return dec2hex(Math.floor(((new Date()).getTime() - 1542657731593) / 10));
    }

    var addButtons = function(parentNode) {
      var c = 0;
      var buttonsSpace = document.createElement("div");
      var buttons = ['fa-times', 'fa-caret-square-o-left', 'fa-caret-square-o-right', 'fa-wrench'];
      var buttonsClass = ['btn-warning', 'btn-success', 'btn-success', 'btn-info'];

      var classNameT = "target-" + newId();
      var classDefinitionT = extractStyleRule('.target-model');
      createStyleRule(classNameT, classDefinitionT);

      var classNameBO = "button-overlap-" + newId();
      var classDefinitionBO = extractStyleRule('.button-overlap-model');
      createStyleRule(classNameBO, classDefinitionBO);

      createStyleRule("{0}:hover {1}".format(classNameT, classNameBO));

      buttonsSpace.addClass(classNameBO);
      for (c = 0; c < buttons.length; c++) {
        buttonsSpace.innerHTML += "<button type='button' class='btn-xs {0}'><i class='fa {1}'></i></button>".format(buttonsClass[c], buttons[c]);
      }
      parentNode.appendChild(buttonsSpace);
      parentNode.addClass(classNameT);
    }

    if (componentName == "rowsplace") {
      var colDef = (y$('rowplace').value || '').replace(/\ \ /g, " ");
      var columns = colDef.split(' ');
      var c, t = 0;
      for (c = 0; c < columns.length; c++) {
        t += str2int(columns[c]);
      }
      if ((t > 0) && (t <= 12)) {
        var row = document.createElement("div");
        this.appendChild(row);
        row.addClass("row");
        row.addClass("target");
        row.ondrop = that.componentDropped;
        row.ondragover = that.componentDraggedOver;

        for (c = 0; c < columns.length; c++) {
          if (str2int(columns[c])) {
            var col = document.createElement("div");
            col.addClass("col-md-{0}".format(columns[c]));
            col.addClass("target");
            col.setAttribute("droppable", "yes");
            col.ondrop = that.componentDropped;
            col.ondragover = that.componentDraggedOver;
            row.appendChild(col);
            addButtons(col);
          }
        }
      }
    } else {
      var re = /id\=([\"A-Za-z_\-0-9]+)/g;
      var innerHTML = aDraggedElement.innerHTML;
      var result;
      var shuffle = dec2hex(Math.floor(((new Date()).getTime() - 1542657731593) / 100));
      var replacements = [];
      while (result = re.exec(innerHTML)) {
        var toSearch = result[0];
        var id = (result[1] || '').unquote();
        var newId = id + "_" + shuffle;
        replacements[toSearch] = toSearch.replace(id, newId);
        replacements[id] = newId;
      }
      for (var rep in replacements) {
        if (replacements.hasOwnProperty(rep)) {
          var toReplace = new RegExp(rep, "g");
          innerHTML = innerHTML.replace(toReplace, replacements[rep]);
        }
      }
      var div = document.createElement("div");
      div.innerHTML = innerHTML;
      div.id = componentName + "_" + shuffle;
      div.addClass("editable-component");
      this.appendChild(div);
      var childs = div.getElementsByTagName("*");
      for (var i = 0; i < childs.length; i++) {
        if (childs[i].nodeName != "DIV")
          childs[i].setAttribute("contenteditable", "true");
      }
    }
  };

  that.componentDraggedOver = function(aDraggedElement) {
    var componentName = aDraggedElement.getAttribute("data-component");
    var canUseRow = (((this.hasClass('row')) || (this.id == 'mainTarget')) && (componentName == 'rowsplace'));
    var canUseCol = ((this.hasClass('col-*')) && (componentName != 'rowsplace'));
    var ret = (this.nodeName == 'DIV') &&
      (canUseRow || canUseCol);
    if (that.status) {
      that.status.innerHTML = "Component: <b>{0}</b> Target: <b>{1}</b>/<b>{2}</b> {3} or {4} = {5}".format(componentName, this.id, this.nodeName, bool2str(canUseRow), bool2str(canUseCol), bool2str(ret));
    }
    return ret;
  };

  /* fill columns per line */
  that.chooseRowColumns = function(e) {
    if (e) {
      e = e.target;
      if (e) {
        var columns = (e.innerHTML || '').split('+');
        var rowplace = y$('rowplace');
        if (rowplace) {
          rowplace.value = columns.join(" ");
        }
      }
    }
  };

  function process(str) {

    var div = document.createElement('div');
    div.innerHTML = str.trim().replace(/\s{2,}/g, ' ');

    return format(div, 0).innerHTML;
  }

  function format(node, level) {

    var indentBefore = new Array(level++ + 1).join('  '),
      indentAfter = new Array(level - 1).join('  '),
      textNode;

    for (var i = 0; i < node.children.length; i++) {

      textNode = document.createTextNode('\n' + indentBefore);
      node.insertBefore(textNode, node.children[i]);

      format(node.children[i], level);

      if (node.lastElementChild == node.children[i]) {
        textNode = document.createTextNode('\n' + indentAfter);
        node.appendChild(textNode);
      }
    }

    return node;
  }

  that.prepareToDownload = function(e) {
    var mainTarget = y$("mainTarget");
    var buttons=getElementsByClassName(mainTarget, 'div', 'button-overlap-*');
    var b=buttons.length;
    while (b>0) {
      var btn=buttons[b-1].parentNode;
      btn.removeChild(buttons[b-1]);
      b--;
    }

    var html = mainTarget.innerHTML;
    html=html.replace(/class\=\"editable-component\"/gim, ' ' );
    html=html.replace(/contenteditable\=\"true\"/gim, ' ' );
    html=html.replace(/droppable\=\"yes\"/gim, ' ' );
    html=html.replace(/target\-[0-9,a-z\_]+/gim, ' ' );
    html=html.replace(/target\b/gim, ' ' );
    html=html.replace(/\ \"/gim, '"' );
    y$("textToDownload").innerText = process(html);
  }

  function download(filename, text) {
    var element = document.createElement('a');
    element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(text));
    element.setAttribute('download', filename);

    element.style.display = 'none';
    document.body.appendChild(element);

    element.click();

    document.body.removeChild(element);
  }

  that.downloadSourceCode = function() {
    var filename=y$('blockOfCodeFilename').value.trim();
    if (filename>'') {
      download(filename, y$("textToDownload").innerText);
    }
  }

  var init = function() {
    mainTarget.ondrop = that.componentDropped;
    mainTarget.ondragover = that.componentDraggedOver;
    addEvent(".btn-choose-rowcolumns", "click", that.chooseRowColumns);
    addEvent("btnDownladPage", "click", that.prepareToDownload);
    addEvent("btnSaveSourceCode", "click", that.downloadSourceCode);
    return that;
  }

  return init();
}

var htmlEditor;

addOnLoadManager(function() {
  htmlEditor = htmlEditorObject();
});