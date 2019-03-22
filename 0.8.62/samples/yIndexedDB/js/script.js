var listaEmailsObj = function() {
	var that={};

	that.salvarEmail = function () {
		var d=ycomm.dom.getFormElements("frmUser");
		if (d._id=='')
			d._id=guid();
		that.db.setItem(d._id, d, function() { ycomm.dom.cleanForm("frmUser"); that.listarEmails();});
	}

	that.eliminarEmail = function (e) {
		e=e.target;
		if (e.nodeName=='I')
			e=e.parentNode;
		var id=e.getAttribute('data-id'), elemento = y$('td-'+id);
		if (elemento) {
			elemento=elemento.parentNode;
			that.db.removeItem(id, function() { y$('tblLista').deleteRow(elemento.rowIndex); });
		}
	}

	that.editarEmail = function (e) {
		e=e.target;
		if (e.nodeName=='I')
			e=e.parentNode;
		var id=e.getAttribute('data-id');
		that.db.getItem(
			id, 
			function(status, error, data) {
				if (status===200) {
					ycomm.dom.fillElement("frmUser", data);	
				}
			}
		);
	}

	that.listarEmails = function () {
		ycomm.dom.fillElement("tblLista");
		that.db.filter(
			function(data) {
				ycomm.dom.fillElement("tblLista", [data], {}, {deleteRows: false} );
			},
			function() {
				addEvent(".btn-remove-email", "click", that.eliminarEmail);
				addEvent(".btn-edit-email",   "click", that.editarEmail);
			}
		)
	}

	that.init = function() {
		that.db=yIndexedDB("emails", "_id", that.listarEmails);

		addEvent("btnSalvarUsuario", "click", that.salvarEmail);

		return that;
	}

	return that.init();
}

var listaEmails;
addOnLoadManager(
	function() {
		listaEmails = listaEmailsObj();
	}
)