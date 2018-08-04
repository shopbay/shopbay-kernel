/* Set the width of the side navigation to width */
function openoffcanvasmenu_overlay(menuId,width) {
    var menu = document.getElementById(menuId);
    if (menu.style.width == 0 || menu.style.width == '0px') {
        menu.style.width = width;/* open it when not closed */
    }
    else {
        closeoffcanvasmenu_overlay(menuId);/* close it when opened */
    }
}
/* Set the width of the side navigation to 0 */
function closeoffcanvasmenu_overlay(menuId) {
    document.getElementById(menuId).style.width = "0";
}   
/* Set the width of the side navigation and the left margin of the page content to width */
function openoffcanvasmenu_push(menuId,pageId,width) {
    var menu = document.getElementById(menuId);
    if (menu.style.width == 0 || menu.style.width == '0px') {
        menu.style.width = width;
        if ($('#'+menuId+'.left').length>0)
            document.getElementById(pageId).style.marginLeft = width;
        if ($('#'+menuId+'.right').length>0)
            document.getElementById(pageId).style.marginRight = width;
        $('#'+pageId).css({'width':'100%'});
    }
    else {
        closeoffcanvasmenu_push(menuId,pageId);/* close it when opened */
    }
}
/* Set the width of the side navigation to 0 and the left margin of the page content to 0 */
function closeoffcanvasmenu_push(menuId,pageId) {
    document.getElementById(menuId).style.width = "0";
    if ($('#'+menuId+'.left').length>0)
        document.getElementById(pageId).style.marginLeft = "0";
    if ($('#'+menuId+'.right').length>0)
        document.getElementById(pageId).style.marginRight = "0";
}
/* Open the sidenav */
function openoffcanvasmenu_full(menuId) {
    document.getElementById(menuId).style.width = "100%";
}
/* Close/hide the sidenav */
function closeoffcanvasmenu_full(menuId) {
    document.getElementById(menuId).style.width = "0";
}