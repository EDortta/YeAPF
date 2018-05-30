var gestaoProjetosObj = function () {
  var that = {};

  /* atualiza o estado do projeto na tela mostrando ou ocultando os botoes */
  that.updateProjectStatus = function(projectKey) {
    var info=that.projects.getItem(projectKey);
    var buttons = {
      'download': 'btnDownloadProjectData_'+projectKey,
      'upload':   'btnUploadProjectData_'+projectKey,
      'erase':    'btnEraseProjectData_'+projectKey,
      'play':     'btnPlayProject_'+projectKey,
      'pause':    'btnPauseProject_'+projectKey,
      'stop':     'btnStopProject_'+projectKey
    };
    var _btnEnabled = function(btnName, btnEnabled) {
      var btn;
      btnName=btnName.split(" ");
      for(var aBtnNdx in btnName) {
        btn=y$(buttons[btnName[aBtnNdx]]);
        if (btn)
          btn.style.display=btnEnabled?'inline-block':'none';
      }
    };

    switch (info.status) {
      case 'stop':
        _btnEnabled("download upload erase play", true);
        _btnEnabled("pause stop", false);
        break;

      case 'pause':
        _btnEnabled("upload download play stop", true);
        _btnEnabled("pause erase", false);
        break;

      case 'play':
        _btnEnabled("pause", true);
        _btnEnabled("upload erase download play stop", false);
        break;

      default:
        _btnEnabled("erase", true);
        _btnEnabled("download upload pause stop play", false);
    }
  };

  /* muda o estado do projeto */
  that.setProjectStatus = function(projectKey, projectStatus) {
    ycomm.crave(
      "management",
      "changeProjectStatus",
      {
        projectKey: projectKey,
        projectStatus: projectStatus
      },
      function(status, error, data) {
        if (status==200) {
          var info=that.projects.getItem(projectKey);
          info.status=data['status'];
          that.projects.setItem(projectKey, info);
          that.updateProjectStatus(projectKey);
        }
      }
    );
  };

  that.playProject = function(e) {
    var projectKey=this.getAttribute('data-projectKey');
    that.setProjectStatus(projectKey, 'play');
  };

  that.pauseProject = function(e) {
    var projectKey=this.getAttribute('data-projectKey');
    that.setProjectStatus(projectKey, 'pause');
  };

  that.stopProject = function(e) {
    var projectKey=this.getAttribute('data-projectKey');
    that.setProjectStatus(projectKey, 'stop');
  };

  that.downloadProjectData = function(e) {
    var projectKey=this.getAttribute('data-projectKey');

  };

  that.uploadProjectData = function(e) {
    var projectKey=this.getAttribute('data-projectKey');
    mTabNav.showTab("vw_upload_project_data");
  };

  that.eraseProjectData = function(e) {
    var projectKey=this.getAttribute('data-projectKey');

  };

  that.getProjects = function () {
    mTabNav.showTab("vw_aguarde");
    ycomm.crave(
      "management",
      "getProjects",
      {},
      function(status, error, data) {
        var t=y$('tbl_projects'), i;
        var _addProjectButton = function (cell, btnClass, actionClass, actionTag, actionName) {
          cell.innerHTML+="<button type='button' class='btn {0} {4}' id='{4}_{3}' data-projectKey='{3}' style='display:none'><a><i class='fa {1}' data-projectKey='{3}'></i><div>{2}</div></a></button>".format(btnClass, actionClass, actionTag, data[i].key, actionName);

        };
        while (t.tBodies[0].rows.length>0)
          t.tBodies[0].deleteRow(0);

        for(var i=0; i<data.length; i++) {
          var tr=t.tBodies[0].insertRow(-1);
          var td1=tr.insertCell(0),
              td2=tr.insertCell(1),
              td3=tr.insertCell(2),
              td4=tr.insertCell(3),
              td5=tr.insertCell(4);
          td1.innerHTML=i;
          td2.innerHTML="<a>"+data[i].key+"</a>";
          td3.innerHTML="<big>"+data[i].name+"</big>";
          td4.innerHTML="";
          td5.innerHTML="";

          /* botoes para gestao de dados */
          if (window.lblDownloadProjectData>'')
            _addProjectButton(td4, 'btn-default btn-project','fa-download', window.lblDownloadProjectData, 'btnDownloadProjectData');
          if (window.lblUploadProjectData>'')
            _addProjectButton(td4, 'btn-default btn-project','fa-upload',   window.lblUploadProjectData,   'btnUploadProjectData');
          if (window.lblCleanProjectData>'')
            _addProjectButton(td4, 'btn-default btn-project','fa-trash',    window.lblCleanProjectData,    'btnEraseProjectData');

          /* botoes para controle do projeto */
          _addProjectButton(td5, 'btn-default', 'fa-play',  '', 'btnPlayProject');
          _addProjectButton(td5, 'btn-default', 'fa-pause', '', 'btnPauseProject');
          _addProjectButton(td5, 'btn-default', 'fa-stop',  '', 'btnStopProject');

          /* atributos da linha para poder puxar mais tarde */
          tr.setAttribute("data-projectKey", data[i].key);

          /* eventos da linha */
          addEvent(td1, "click", that.escolheProjeto);
          addEvent(td2, "click", that.escolheProjeto);
          addEvent(td3, "click", that.escolheProjeto);

          /* dados salvos na sessao */
          that.projects.setItem(data[i].key, data[i]);

          /* estado atual do projeto */
          that.updateProjectStatus(data[i].key);

        }

        addEvent(".btnDownloadProjectData", "click", that.downloadProjectData);
        addEvent(".btnUploadProjectData",   "click", that.uploadProjectData);
        addEvent(".btnEraseProjectData",    "click", that.eraseProjectData);

        addEvent(".btnPlayProject", "click", that.playProject);
        addEvent(".btnPauseProject",   "click", that.pauseProject);
        addEvent(".btnStopProject",    "click", that.stopProject);

        mTabNav.showTab("vw_projects");
        y$('vw_projects').removeClass("animated");
      }
    );
  };

  that.recreateDeviceKey = function(e) {
    if (e.target.tagName=="I")
      e=e.target.parentNode;
      else
        e=e.target;
    var deviceId=e.getAttribute("data-deviceId"),
        deviceKey=e.getAttribute("data-deviceKey");
    ycomm.crave(
      "management",
      "recreateDeviceKey",
      {
        projectKey: that.projectKey,
        deviceId: deviceId,
        deviceKey: deviceKey
      },
      function(status,error,data) {
        that._puxarChavesProjeto();
      }
    );
  };

  that.removeDeviceKey = function(e) {
    console.log("Preparando-se para remover key");
    if (e.target.tagName=="I")
      e=e.target.parentNode;
      else
        e=e.target;
    var deviceId=e.getAttribute("data-deviceId"),
        deviceKey=e.getAttribute("data-deviceKey");
    ycomm.crave(
      "management",
      "removeDeviceKey",
      {
        projectKey: that.projectKey,
        deviceId: deviceId,
        deviceKey: deviceKey
      },
      function(status,error,data) {
        that._puxarChavesProjeto();
      }
    );
  };

  that.toggleDevice = function(e) {
    if (e.target.tagName=="I")
      e=e.target.parentNode;
      else
        e=e.target;
    var deviceId=e.getAttribute("data-deviceId"),
        deviceKey=e.getAttribute("data-deviceKey");
    ycomm.crave(
      "management",
      "toggleDevice",
      {
        projectKey: that.projectKey,
        deviceId: deviceId,
        deviceKey: deviceKey
      },
      function(status,error,data) {
        that._puxarChavesProjeto();
      }
    );
  };

  that.mostrarDispositivos = function () {

  };

  that._puxarChavesProjeto = function() {
    ycomm.crave(
      "management",
      "getProjectKeys",
      {
        projectKey: that.projectKey
      },
      function (status, error, data) {
        var t=y$('tbl_devices');
        while (t.tBodies[0].rows.length>0)
          t.tBodies[0].deleteRow(0);
        for(var i in data) {
          if (data.hasOwnProperty(i)) {
            var tr=t.tBodies[0].insertRow(-1);
            var cols=0,
                td1=tr.insertCell(cols++),
                td2=tr.insertCell(cols++),
                td3=tr.insertCell(cols++),
                td4=tr.insertCell(cols++),
                tdControl=tr.insertCell(cols++);
            td1.innerHTML=data[i].deviceId;
            td2.innerHTML=data[i].deviceKey;
            td3.innerHTML="<i class='fa {0}'></i>".format((data[i].enabled==1)?'fa-check-square-o':'fa-square-o');
            td3.setAttribute('align','center');
            td4.innerHTML="<div><b class='device-status-{0}'>{0}</b> {1}</div>".format(data[i].status || '', data[i].statusDescription || '');
            td4.setAttribute('align','center');

            tdControl.setAttribute('align', 'right');
            tdControl.innerHTML="<button type=button class='btn btn-default' id='btnRenewKey{0}'><i class='fa fa-exchange'></i></button><button type=button class='btn btn-warning' id='btnRevokeKey{0}'><i class='fa fa-close'></i></button>".format(data[i].deviceKey);
            if (data[i].enabled==1) {
              tdControl.innerHTML+="<button type=button class='btn btn-primary' id='btnToggleDevice{0}'><i class='fa fa-pause'></i></button>".format(data[i].deviceKey);
            } else {
              tdControl.innerHTML+="<button type=button class='btn btn-primary' id='btnToggleDevice{0}'><i class='fa fa-play'></i></button>".format(data[i].deviceKey);
            }

            tr.setAttribute("data-projectKey", that.projectKey);
            tr.setAttribute("data-deviceId",   data[i].deviceId);
            tr.setAttribute("data-deviceKey",  data[i].deviceKey);

            var buttons = [
                  "btnRenewKey{0}".format(data[i].deviceKey),
                  "btnRevokeKey{0}".format(data[i].deviceKey),
                  "btnToggleDevice{0}".format(data[i].deviceKey)
                ];
            for(var n=0;n<buttons.length; n++) {
              y$(buttons[n]).setAttribute("data-projectKey", that.projectKey);
              y$(buttons[n]).setAttribute("data-deviceId",   data[i].deviceId);
              y$(buttons[n]).setAttribute("data-deviceKey",  data[i].deviceKey);
            }


            addEvent(td1,        "click", that.escolheDispositivo);
            addEvent(buttons[0], "click", that.recreateDeviceKey);
            addEvent(buttons[1], "click", that.removeDeviceKey);
            addEvent(buttons[2], "click", that.toggleDevice);
          }
        }

        mTabNav.showTab("vwDevicesPerProject");
        y$('vwDevicesPerProject').removeClass("animated");
      }
    );

  };

  that.escolheProjeto = function (e) {
    if (e.target.tagName=='TD')
      e=e.target.parentNode;
    else
      e=e.target.parentNode.parentNode;
    that.projectKey=e.getAttribute("data-projectKey");
    that._puxarChavesProjeto();
  };

  that.newProject = function() {
    if (trim(y$('newProjectName').value)>'') {
        ycomm.crave(
          "management",
          "createProject",
          {
            newProjectName: encodeURI(y$('newProjectName').value)
          },
          function(status, error, data) {
            y$('newProjectName').value='';
            that.getProjects();
          }
        );
    }
  };

  that.newDevice = function() {
    if (trim(y$('newDeviceName').value)>'') {
        ycomm.crave(
          "management",
          "createDevice",
          {
            projectKey: that.projectKey,
            deviceId: encodeURI(y$('newDeviceName').value),
            USR: y$('newDeviceName').getAttribute('data-usr')
          },
          function(status, error, data) {
            y$('newDeviceName').value='';
            that._puxarChavesProjeto();
          }
        );
    }
  };

  that.limparTabela = function(tbl_id) {
    while(y$(tbl_id).tBodies[0].rows.length>0) y$(tbl_id).tBodies[0].deleteRow(0);
  };

  that.verDadosProjetos = function () {
    that.limparTabela('tbl_project_data');
    mTabNav.showTab("vwProjectData");

    ycomm.crave(
      "management",
      "getLastModifiedData",
      {
        projectKey: that.projectKey
      },
      function(status, error, data) {
        ycomm.dom.fillElement("tbl_project_data", data);
        addEvent('.btnShowDataItem', 'click', that.showMapPoint)
      }
    );
  };

  that.getCanWork = function() {
    ycomm.crave(
      "management",
      "canWork",
      {},
      function(status, error, data) {
        var canWork=false;
        if (data) {
          canWork=(data.canWork=='yes') && (data.user>'');
        }

        if (!canWork)
          alert("In order to work, this application requires:\n\t1) .htaccess file defined and operational\n\t2) keys.config.php present and well configured\n\t3) cfgIdServerURL cannot be defined\nReason given by server:\n\t"+data['reason']);
      }
    )
  };

  that.fecharMapa = function () {
    mTabNav.showTab("vwProjectData");
  };

  that.verEstatisticaProjeto = function () {
    that.limparTabela('tbl_project_stats');
    mTabNav.showTab("vwProjectStats");
    ycomm.crave(
      "management",
      "getProjectStats",
      {
        projectKey: that.projectKey
      },
      function(status, error, data) {
        ycomm.dom.fillElement("tbl_project_stats", data);
      }
    );
  };

  that.initMap = function() {
    if (!that.mapInitialized) {
      that.mapInitialized=true;
      that.map = L.map('mapid').setView([-22.1211, -51.3930], 13);

      L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
          attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
      }).addTo(that.map);
    }
  };

  that.showMapPoint=function(e) {
    e=e.target.parentNode;
    var latitude = e.getAttribute('data-latitude'),
        longitude = e.getAttribute('data-longitude');
    that.initMap();
    mTabNav.showTab("vwProjectPoints");
    var marker = L.marker([latitude, longitude]).addTo(that.map);
    that.map.setView([latitude, longitude]);
  };

  that.init=function() {
    /* inicializar banco de dados local */
    that.projects=ySingleDb('coletores');

    Object.defineProperty(
      that,
      'projectKey',
      {
        enumerable: true,
        configurable: true,
        get: function () {
          return that._projectKey_;
        },
        set: function (newProjectKey) {
          that._projectKey_=newProjectKey;
          y$('lblProjectKey').innerHTML=newProjectKey;
        }
      }
    );

    ycomm.setDataLocation(document.location.href.split('?')[0].split('#')[0]+"/rest.php");

    addEvent("btnProjetos",          "click", that.getProjects);
    addEvent("btnDispositivos",      "click", that._puxarChavesProjeto);
    addEvent("btnRefreshDeviceList", "click", that._puxarChavesProjeto);
    addEvent("btnViewDatabase",      "click", that.verDadosProjetos);
    addEvent("btnViewStats",         "click", that.verEstatisticaProjeto);
    addEvent("btnNewProject",        "click", that.newProject);
    addEvent("btnNewDevice",         "click", that.newDevice);
    addEvent("btnCloseMap",          "click", that.fecharMapa);

    ycomm.dom.fillElement("tbl_project_data", {});
    ycomm.dom.fillElement("tbl_project_stats", {});

    that.getCanWork();

    that.initMap();

    return that;
  };

  return that.init();
};

var gestaoProjetos;

addOnLoadManager(
  function() {
    gestaoProjetos = gestaoProjetosObj();
  }
);
