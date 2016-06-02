var #(dbName)Base = function () {
	var that={};

	that._loadContext = {
		xq_start: 0		
	}

	that._pullData = function() {
    	ycomm.invoke(
    		"#(dbName)",
    		"getTable",
    		that._loadContext,
    		function(status, error, data, userMsg, context) {
    			ycomm.dom.fillElement(
    				"tbl_#(dbName)",
    				data,
    				{
    					inplaceData : ['#(primaryKey)'], 
    					rows: ["#(tBody)"]
    				},
    				that._loadContext.xq_start==0
    			);

    			if (context){
	     			if (context.rowCount==context.requestedRows) {
	    				that._loadContext.xq_start+=context.rowCount;
	    				setTimeout(that._pullData, 125);
	    			}
	    	    }
    		}
    	);
	}

    that.loadTable = function() {
    	mTabNav.showTab('list_#(dbTable)');
    	that._loadContext.xq_start=0;
    	that._pullData();
    };

    that.newItem = function() {
    	mTabNav.showTab('form_#(dbTable)');
    	ycomm.dom.cleanForm('frm_#(dbTable)');
    };

    that.editTableItem = function(rowid) {
    	var key=ycomm.dom.getTableInplaceData ('tbl_#(dbTable)', rowid, '#(primaryKey)');
    	that.newItem();
    	ycomm.invoke(
    		"#(dbTable)",
    		"getItem",
    		{
    			'#(primaryKey)': key
    		},
    		function(status, error, data) {
    			ycomm.dom.fillElement("frm_#(dbTable)", data, {elementPrefixName: 'frm_#(dbTable)_'});
    		}
    	);
    };

    that.confirmItemDeletion = function(key) {
    	console.warn("Place your code here to confirm user's intention");

    	return true;
    };

    that.deleteTableItem = function(rowid) {    	
    	var key=ycomm.dom.getTableInplaceData ('tbl_#(dbTable)', rowid, '#(primaryKey)');
    	if (that.confirmItemDeletion(key)) {    	
    		ycomm.invoke(
	    	    		"#(dbTable)",
	    	    		"deleteItem",
	    	    		{
	    	    			'#(primaryKey)': key
	    	    		},
	    	    		function(status, error, data, userMsg) {
	    	    			window.alert("Your record was deleted");
	    	    		}
	    	    	);
	    }    
	};

    that.saveItem = function() {
    	var formData=ycomm.dom.getFormElements("frm_#(dbTable)");
    	ycomm.invoke(
    		"#(dbTable)",
    		"saveItem",
    		formData,
    		function(status, error, data, userMsg) {
    			mTabNav.showTab('list_#(dbTable)');
    		}
    	);
    };

    that.cancelEdit = function () {
    	mTabNav.showTab('list_#(dbTable)');
    };

	that.init = function () {
	  addEvent("btnSave_#(dbTable)",   "click", that.saveItem);
	  addEvent("btnCancel_#(dbTable)", "click", that.cancelEdit);
	  return that;
	};

	return that.init();
}

var #(dbName)=null;
addOnLoadManager(
  function() {
	#(dbName)=#(dbName)Base();
  }
)