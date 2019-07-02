function createMenuEntries(aMenuArray, aParentElement)
{
  if ('undefined' != typeof aMenuArray) {
    if (aMenuArray != null) {
      var a = document.createElement('a');
      if (aMenuArray[1]!=null)
        a.setAttribute('href', aMenuArray[1]);
      a.innerHTML=aMenuArray[0];
      a.target='_mainBody_';

      var newLi = document.createElement("li");
      newLi.appendChild(a);
      aParentElement.appendChild(newLi);

      if ((typeof aMenuArray =='object') && (aMenuArray[2]!=null)) {
        var newUL=document.createElement('UL');
        newLi.appendChild(newUL);
        for(var k=2; k<aMenuArray.length; k++)
          createMenuEntries(aMenuArray[k], newUL);
      }
    }
  }
}

var menu=document.getElementById('menu');
if (menu) {
  var newUL=document.createElement('UL');
  menu.appendChild(newUL);
  for(var i=2; i<TREE_ITEMS[0].length; i++)
    createMenuEntries(TREE_ITEMS[0][i], newUL, 1);

} else
  console.log("'menu' div not defined");
