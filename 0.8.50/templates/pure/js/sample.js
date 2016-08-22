var sampleObjBase = function () {
  that={};
  that.currentTab=null;

  that.openTabByClick = function(e) {
    var linkId, linkParent;
    if (typeof e.target=="object") {
      linkId=e.target.id;
      linkParent=e.target.parentNode;
    } else {
      linkId=e.id;
      linkParent=e.parentNode;
    }
    var tabId='section_'+linkId.substring(0,linkId.length-3);

    if (that.currentTab)
      that.currentTab.deleteClass('active');
    linkParent.addClass('active');
    that.currentTab=linkParent;

    mTabNav.showTab(tabId);
  };

  that.init = function() {

    return that;
  }

  return that.init();
}


var sampleObj;
addOnLoadManager(
  function() {
    sampleObj=sampleObjBase();
    /* all cl-actions buttons are treated with the same 'click' event manager */
    addEvent("cl-actions",           "click", sampleObj.openTabByClick);
  }
);
