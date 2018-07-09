var objSample01 = function() {
  var that=objSection().init('sample01');

  that.fillDataTable = function(aTableName, aData) {
    ycomm.dom.fillElement(
      aTableName,
      aData,
      {
        'inplaceData': [ 'code' ],
        'rows': [
                  '<td><b>%(name)</b>'+
                  '<td align=right>%int(code)</b>'
              ]
      });
  };

  return that;
}
var sample01;

addOnLoadManager(
  function() {
    sample01 = objSample01();
  }
);
