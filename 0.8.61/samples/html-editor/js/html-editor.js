var htmlEditorObject = function() {
  var that = {};
  that.mainTarget = y$('mainTarget');
  that.status = y$('status');

  /* DragAndDrop */
  that.componentDropped = function(aDraggedElement) {
    var componentName = aDraggedElement.getAttribute("data-component");
    if (componentName == "rowsplace") {
      var colDef = (y$('rowplace').value || '').replace(/\ \ /g," ");
      var columns = colDef.split(' ');
      var c,t=0;
      for(c=0; c<columns.length; c++) {
        t+=str2int(columns[c]);
      }
      if (t<=12) {
        var row = document.createElement("div");
        this.appendChild(row);
        row.addClass("row");
        row.addClass("target");
        row.ondrop = that.componentDropped;
        row.ondragover = that.componentDraggedOver;
        for(c=0; c<columns.length; c++) {
          if (str2int(columns[c])) {
            var col = document.createElement("div");
            col.addClass("col-md-{0}".format(columns[c]));
            col.addClass("target");
            col.setAttribute("droppable","yes");
            col.ondrop = that.componentDropped;
            col.ondragover = that.componentDraggedOver;
            row.appendChild(col);              
          }
        }          
      }
    } else {
      var re=/id\=([\"A-Za-z_\-0-9]+)/g;
      var innerHTML = aDraggedElement.innerHTML;
      var result;
      var shuffle = Math.floor(((new Date()).getTime() - 1542657731593) / 100);
      var replacements = [];
      while (result = re.exec(innerHTML)) {
        var toSearch = result[0];
        var id = ( result[1] || '').unquote();
        var newId=id+"_"+shuffle;
        replacements[toSearch] = toSearch.replace(id, newId);
      }
      for(var rep in replacements) {
        if (replacements.hasOwnProperty(rep)) {
          innerHTML = innerHTML.replace(rep, replacements[rep]);
        }
      }
      var div = document.createElement("div");
      div.innerHTML=innerHTML;
      this.appendChild(div);
    }
  };

  that.componentDraggedOver = function(aDraggedElement) {
    var componentName = aDraggedElement.getAttribute("data-component");
    var canUseRow = ( ((this.hasClass('row')) || (this.id=='mainTarget')) && (componentName=='rowsplace') );
    var canUseCol =                             ((this.hasClass('col-*')) && (componentName!='rowsplace') );
    var ret = (this.nodeName == 'DIV') && 
              ( canUseRow || canUseCol );
    if (that.status) {
      that.status.innerHTML ="Component: <b>{0}</b> Target: <b>{1}</b>/<b>{2}</b> {3} or {4} = {5}".format( componentName, this.id, this.nodeName, bool2str(canUseRow), bool2str(canUseCol), bool2str(ret) );
    }
    return ret;
  };

  /* fill columns per line */
  that.chooseRowColumns = function(e) {
    if (e) {
      e=e.target;
      if (e) {
        var columns=(e.innerHTML || '').split('+');
        var rowplace=y$('rowplace');
        if (rowplace) {
          rowplace.value=columns.join(" ");
        }
      }
    }
  };

  var init = function() {
    mainTarget.ondrop = that.componentDropped;
    mainTarget.ondragover = that.componentDraggedOver;
    addEvent(".btn-choose-rowcolumns", "click", that.chooseRowColumns);
    return that;
  }

  return init();
}

var htmlEditor;

addOnLoadManager(function() {
  htmlEditor = htmlEditorObject();
});