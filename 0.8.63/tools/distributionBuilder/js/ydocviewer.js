/*
tools/distributionBuilder/js/ydocviewer.js
YeAPF 0.8.63-106 built on 2019-07-11 09:42 (-3 DST)
Copyright (C) 2004-2019 Esteban Daniel Dortta - dortta@yahoo.com - MIT License
2019-07-11 09:40:16 (-3 DST)
*/

var yDocViewerObj = function () {
  'use strict';
  var that={};

  that.expandDocument = function (id) {
    var docEntryList = y$('.docEntry');
    if (docEntryList) {
        docEntryList.forEach(function (e) { e.style.display='none'; });
        var docEntry = y$(id);
        if (docEntry) {
          docEntry.style.display='';
          var i  = y$('.expander-'+id);
          if (i) {
            i=i[0];
            i.addClass('fa-rotate-90');
            window.homeHistoryPosition--;
          }
        }  
    }
  };

  var expandDocument = function(e) {
    if ((e) && (e.target)) {
      var expandDoumentList = y$('.expand-document');
      expandDoumentList.forEach( function (e) { e.deleteClass('fa-rotate-90'); });
      var a  = e.target.closest('A');
      var id = a.getAttribute('data-id');
      that.expandDocument(id);
    }
  };

  var openIfOnlyOneDoc = function() {
    var ret=false;
    var expandDoumentList = y$('.expand-document');
    if (expandDoumentList.length==1) {
      that.expandDocument(expandDoumentList[0].getAttribute('data-id'));
      ret=true;
    }
    return ret;
  };

  var init=function() {
    addEvent('expand-document', 'click', expandDocument);
    var href=document.location.href.split("#");
    if (href.length=2)
      that.expandDocument(href[1]);
    else
      openIfOnlyOneDoc();
  }

  return init();
}

addOnLoadManager(function() { window.yDocViewer = yDocViewerObj() });