/*********************************************
 * app-src/js/ytabnav.js
 * YeAPF 0.8.49-100 built on 2016-07-28 17:26 (-3 DST)
 * Copyright (C) 2004-2016 Esteban Daniel Dortta - dortta@yahoo.com
 * 2016-06-23 17:55:11 (-3 DST)
 * First Version (C) 2012 - esteban daniel dortta - dortta@yahoo.com
 * Purpose: to control multiple tabs in a only-one-page application
 *          this is specially useful when building web mobile applications
**********************************************/

var tabNavBase = function () {
  var that = {};

  if (isOnMobile()) {
    _dump("Loading mobile tabs");
    that.tabchangeEvent = document.createEvent('Events');
    that.tabchangeEvent.initEvent('tabchange');

    that.tabblurEvent = document.createEvent('Events');
    that.tabblurEvent.initEvent('tabblur');

    that.tabfocusEvent = document.createEvent('Events');
    that.tabfocusEvent.initEvent('tabfocus');
  } else {
    if (typeof Event=='function') {
      _dump("Loading desktop tabs");
      that.tabchangeEvent = new Event('tabchange');
      that.tabblurEvent = new Event('tabblur');
      that.tabfocusEvent = new Event('tabfocus');
    } else 
      _dump("Tabs are not supported");    
  }

  that.currentTabNdx = -1;
  that.currentContainerNdx = -1;
  that.containerList = [];
  that.tabList = [];

  that.storage = null;

  that.initialized = -1;
  that.lock = {
    returnTabId: null,
    locked: false
  };

  that.isContainer = function (aDiv) {
    var ret = false;
    if (aDiv) {
      ret = aDiv.hasClass('tnContainer');
    }
    return ret;
  };

  that.isTab = function (aDiv) {
    var ret = false;
    if (aDiv) {
      ret = aDiv.hasClass('tnTab');
    }
    return ret;
  };

  that.getContainer = function (aContainerNdx) {
    return that.containerList[aContainerNdx];
  }

  that.getCurrentContainer = function () {
    return that.getContainer(that.currentContainerNdx);
  }

  that.getContainerById = function (aContainerId) {
    var ret=null;
    for(var i=0; i < that.containerList.length; i++) {
      if (that.containerList[i].element.id==aContainerId)
        ret = that.containerList[i];
    }

    return ret;
  }

  that.getTabContainer = function (aTabId) {
    var ret=null;
    if (aTabId) {
      for(var i=0; (i < that.containerList.length) && (ret==null); i++) {
        for(var n=0; (n < that.containerList[i].childs.length) && (ret==null); n++) {
          if (typeof aTabId=='string') {
            if (that.containerList[i].childs[n].id == aTabId) {
              ret = that.containerList[i];
            }
          } else {
            if (that.containerList[i].childs[n] == aTabId) {
              ret = that.containerList[i];
            }
          }
        }
      }
    }
    return ret;
  }

  that.getFirstTabInContainer = function(aContainer) {
    var ret=null, myContainer;
    if (typeof aContainer == 'string')
      aContainer=y$(aContainer);

    if (that.isTab(aContainer))
      myContainer=that.getTabContainer(aContainer);
    else {
      var containerNdx=that.getContainerNdx(aContainer);
      if (containerNdx>=0)
        myContainer=that.containerList[containerNdx];
    }

    if (myContainer)
      ret=myContainer.childs[0];

    return ret;
  }

  that.getFirstChildTab = function(aTab) {
    var ret=null, myContainer;
    if (typeof aTab == 'string')
      aTab=y$(aTab);

    if (that.isTab(aTab)) {
      var aContainerList=aTab.getElementsByClassName('tnContainer');
      if (aContainerList.hasOwnProperty('0')) {
        var containerNdx=that.getContainerNdx(aContainerList[0]);
        if (containerNdx>=0)
          ret=that.containerList[containerNdx].childs[0];
      } else
        ret=aTab;

    }
    return ret;
  }

  that.getContainerFromParam = function (aContainer, aTabId) {
    if (aContainer==undefined) {
      if (aTabId==undefined)
        aContainer = that.getCurrentContainer();
      else
        aContainer = that.getTabContainer(aTabId);

    } else if (typeof aContainer == 'string') {
      aContainer = that.getContainerById(aContainer);
    } else if (isNumber(aContainer)) {
      aContainer = that.getContainer(aContainer);
    } else if (typeof aContainer != 'object') {
      _dump("getContainerFromParam() parameter is not null, valid string, object nor a number");
      aContainer = null;
    }
    return aContainer;
  }

  that.getCurrentTabNdx = function(aContainer) {
    var ret = -1;
    aContainer = that.getContainerFromParam(aContainer);
    if (aContainer) {
      ret = aContainer.currentTabNdx;
    }

    return ret;
  }

  that.setCurrentContainer = function (aNewContainerNdx) {
    if (that.initialized < 0)
      that.init();
    that.currentContainerNdx = aNewContainerNdx % that.containerList.length;
  }

  that.getContainerNdx = function (aTab) {
    var ret=-1;
    for (var i=0; i<that.containerList.length; i++) {
      if (that.containerList[i].element==aTab)
        ret=i;
    }
    return ret;
  }

  that.addContainer = function (aTab) {
    if (that.initialized < 0)
      that.init();
    if (aTab) {
      if (that.getContainerNdx(aTab)<0) {
        that.currentContainerNdx = that.containerList.length;
        that.containerList[that.currentContainerNdx] = {
          childs: [],
          element: aTab,
          currentTabNdx: -1
        }
        var auxTabList=aTab.getElementsByClassName('tnTab');
        for(var i in auxTabList)
          if (auxTabList.hasOwnProperty(i)) {
            if (typeof auxTabList[i]=='object') {
              var l=that.containerList[that.currentContainerNdx].childs.length;
              that.containerList[that.currentContainerNdx].childs[l]=auxTabList[i];
            }
          }
      }
    }
  };

  that.addTab = function (aTab) {
    if (that.initialized < 0)
      that.init();
    if (aTab) {
      var aux = that.getCurrentContainer().childs;
      if (aux.indexOf(aTab)<0)
        aux[aux.length] = aTab;
    }
  };

  that.init = function (aDivContainer) {
    if (that.initialized < 0) {
      _dump("Initializing tabs");

      that.initialized = 0;

      var allContainers = document.getElementsByClassName('tnContainer'),
          firstTab = null, aDiv = null,
          i = 0;
      if (allContainers) {
        for (i=0; i<allContainers.length; i++) {
          aDiv=allContainers[i];
          that.addContainer(aDiv);
        }
      } else 
        _dump("ERROR: No containers defined. Use 'tnContainer' class on a DIV");

      var allTabs = document.getElementsByClassName('tnTab');
      if (allTabs) {
        for(var i=0; i<allTabs.length; i++)
          that.hideTab(allTabs[i]);
      } else
        _dump("ERROR: No tabs defined. Use 'tnTab' class on a DIV");

      if (that.containerList.length>0) {
        firstTab=that.containerList[0].childs[0];
        that.displayTab(that.getFirstChildTab(firstTab));
      }

      that.currentContainerNdx = 0;
      that.initialized = 1;
    }
    if (ycomm)
      ycomm.setWaitIconControl(that.waitIconControl);
    return that;
  };

  that.currentTab = function () {
    var theContainer = that.getCurrentContainer();
    if (theContainer.currentTabNdx>-1) {
      return theContainer.childs[theContainer.currentTabNdx];
    } else
      return null;
  };

  that.createTab = function (aDivContainer, aNewTabId) {
    var aDiv = null;
    if (y$(aNewTabId)==undefined) {
      aDiv = document.createElement('div');
      aDiv.className='tnTab';
      aDiv.style.display='none';
      aDiv.id=aNewTabId;

      aDivContainer.appendChild(aDiv);

      /* criar sob containerList */

      that.addTab(aDiv);
      that.hideTab(aDiv);

    }
    return aDiv;
  }

  that.delTab = function (aTab) {
  };

  that.displayTab = function (aTab, aContainer) {
    if (!that.changingView) {
      that.changingView=true;
      try {
        if (!that.locked()) {
          if (aTab) {
            if (that.initialized < 0)
              that.init();
            _dumpy(64,1,"displayTab "+aTab.id);
            var canChange = true,
                i = 0;
            canChange = aTab.dispatchEvent(that.tabchangeEvent) || canChange;
            /*
            if (that.ontabchange != undefined)
              canChange = that.ontabchange(aTab);
            */
            if (canChange) {
              var theContainer = that.getContainerFromParam(aContainer);
              if (theContainer) {

                _dumpy(64,1,"canchange");
                var aNdx = -1;
                var freeze = false;

                for(i = 0; i < theContainer.childs.length; i++) {
                  if (theContainer.childs[i] != aTab)
                    freeze |= !(that.hideTab(theContainer.childs[i], aTab, theContainer));
                  else
                    aNdx = i;
                }
                _dumpy(64,1,"readytochange "+!freeze);
                if (!freeze) {
                  that.setCurrentContainer(that.getContainerNdx(theContainer));
                  theContainer.currentTabNdx = aNdx;
                  aTab.dispatchEvent(that.tabfocusEvent);
                  /*
                  if (that.ontabfocus != undefined)
                    that.ontabfocus(aTab);
                  */
                  aTab.style.display = 'block';
                  var auxNode=aTab;
                  while ((auxNode) && (auxNode!=document.body)) {
                    auxNode.style.display = 'block';
                    auxNode=auxNode.parentNode;
                  }
                  var elems=aTab.getElementsByTagName('*');;
                  i=0;
                  while (i<elems.length) {
                    if ((elems[i].type=='checkbox') || (elems[i].type=='radio') || (elems[i].type=='password') || (elems[i].type=='hidden') || (elems[i].type=='text') || (elems[i].type=='select-one') || (elems[i].type=='textarea')) {
                      elems[i].focus();
                      break;
                    }
                    i++;
                  }
                } else {
                  _dumpy(64,1,"freeze");
                }
              }
            }
            _dumpy(64,1,"return");
          }
        }
      } finally {
        that.changingView=false;
      }
    }
  };

  that.showWaitIcon = function () {
    if (y$('waitIcon')) {
      y$('waitIcon').style.display='block';
    }
  };

  that.hideWaitIcon = function () {
    if (y$('waitIcon')) {
      y$('waitIcon').style.display='none';
    }
  };

  that.waitIconControl = function (display) {
    if (display!=undefined) {
      if (display)
        that.showWaitIcon();
      else
        that.hideWaitIcon();
    }
  }

  that.isInnerTab = function(aTabToBeShowed, aCurrentTab) {
    var ret = false;
    if (aTabToBeShowed) {
      var aTab = aTabToBeShowed;
      while ( (aTab) && (aTab.parent != aTab) ) {
        if (aCurrentTab == aTab)
          ret = true;
        aTab = aTab.parentNode;
      }
    }
    return ret;
  }

  that.hideTab = function (aTab, aTabToBeShowed, aContainer) {
    if (!that.locked()) {
      _dumpy(64,1,"hideTab "+aTab.id);
      var ret = true;
      var theContainer = that.getContainerFromParam(aContainer);
      if (theContainer) {
        if (theContainer.childs.indexOf(aTab) == theContainer.currentTabNdx) {
          ret = aTab.dispatchEvent(that.tabblurEvent) || ret;
          /*
          if (that.ontabblur != undefined)
            ret = that.ontabblur(aTab, aTabToBeShowed);
          */
          if (ret)
            theContainer.currentTabNdx = -1;
        } else
          ret = true;
      }
      if (ret) {
        if (typeof aTab=='object')
          if (!that.isInnerTab(aTabToBeShowed, aTab))
            aTab.style.display = 'none';
      }
      return ret;
    }
  };

  that.showNext =  function (aContainer) {
    aContainer = that.getContainerFromParam(aContainer);
    if (aContainer) {
      var currentTabNdx = aContainer.currentTabNdx;
      if (currentTabNdx<aContainer.childs.length-1)
        that.displayTab(aContainer.childs[currentTabNdx+1], aContainer);
      else
        that.displayTab(aContainer.childs[0], aContainer);
    }
  };

  that.showPrior = function (aContainer) {
    aContainer = that.getContainerFromParam(aContainer);
    if (aContainer) {
      var currentTabNdx = aContainer.currentTabNdx;
      if (currentTabNdx>0)
        that.displayTab(aContainer.childs[currentTabNdx-1], aContainer);
      else
        that.displayTab(aContainer.childs[aContainer.childs.length-1], aContainer);
    }
  };

  that.getCurrentTabId = function (aContainer) {
    var ret = null;
    aContainer = that.getContainerFromParam(aContainer);
    if (aContainer) {
      var currentTabNdx = aContainer.currentTabNdx;
      if (currentTabNdx>-1)
        ret = aContainer.childs[currentTabNdx].id;
    }
    return ret;
  }

  that.showTab = function (aTabId, aLockTabAfterShow, aContainer) {
    if (!that.locked()) {
      var theContainer = that.getContainerFromParam(aContainer, aTabId);
      if (aTabId == undefined) {
        aTabId = theContainer.childs[0].id;
      }
      if (aLockTabAfterShow==undefined)
        aLockTabAfterShow=false;

      var aTab = document.getElementById(aTabId);
      var priorTabId = '';
      if (aTab) {
        if (aLockTabAfterShow) {
          priorTabId = that.getCurrentTabId(theContainer);
        }
        that.displayTab(aTab, theContainer);
        if (aLockTabAfterShow)
          that.lockTab(aTabId, priorTabId);
      } else
        alert(aTabId+" not found");
    }
  };

  that.locked = function () {
    return that.lock.locked;
  }

  that.releaseLockedTabs = function () {
    for(var i=0; i<that.getCurrentContainer().childs.length; i++) {
      if (that.getCurrentContainer().childs[i].locked)
        that.getCurrentContainer().childs[i].locked=false;
    }
    that.lock.locked=false;
    that.lock.returnTabId=null;
  }

  that.lockTab = function (aTabId, aReturnTabId) {
    if (that.locked())
      that.releaseLockedTabs();
    if (y$(aTabId)) {
      that.lock.locked = true;
      y$(aTabId).locked = true;
      that.lock.returnTabId = y$(aReturnTabId)?aReturnTabId:null;
    }
  }

  that.unlockTab = function (aTabId) {
    if (that.locked()) {
      if (y$(aTabId)) {
        if (y$(aTabId).locked) {
          var nextTabId = that.lock.returnTabId;
          that.releaseLockedTabs();
          if (nextTabId!=null)
            that.showTab(nextTabId);
        }
      }
    }
  }
  return that;
}

var mTabNav = tabNavBase();


