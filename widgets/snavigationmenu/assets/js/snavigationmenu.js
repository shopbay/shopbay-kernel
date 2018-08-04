$(document).ready(function(){
    if ($('.navmenu-widget').data('pikabu')!=undefined){
        var pikabu = loadpikabu();
        console.log('pikabu loaded');
    }
});            

function loadpikabu()
{
    var openScript = 'opennavmenuleft';
    if ($('.navmenu-widget').data('onOpened').length>0){
        // console.log($('.navmenu-widget').data('onOpened'));
        openScript = $('.navmenu-widget').data('onOpened');
    }
    else
        console.log('pikabu use default open script');
        
    return new Pikabu({
        'viewportSelector':'.m-pikabu-viewport',
        'selectors': {
            element: '.m-pikabu-container',/*Selector for page content*/
            navToggles: '.m-pikabu-nav-toggle-icon',/*Pikabu toggle button*/
            overlay: '.m-pikabu-overlay', /*Click-to-close overlay*/
            common: '.m-pikabu-sidebar', /*Base class for either sidebar*/
            left: '.m-pikabu-left',  /*Left sidebar class*/
            /*right: '.m-pikabu-right'  /*Right sidebar class*/

        },
        'widths': {
            left: '70%',
            /*right: '70%'*/
        },
        'onInit':function(){initnavmenuleft();},
        'onOpened':function(){window[openScript]();},
        'onClosed':function(){closenavmenuleft();}
    });    
}
function initnavmenuleft()
{
    $('.m-pikabu-left').hide();
    $('.m-pikabu-left').html('<nav>'+$('.nav-menu').html()+'</nav>');
    $('.m-pikabu-left .m-pikabu-nav-toggle-icon').remove();
}
function closenavmenuleft()
{
    $('.m-pikabu-left').hide();
}

function opennavmenuleft()
{
    $('.m-pikabu-left').css({width:'80%'});
    $('.m-pikabu-left .nav-menuitems .quickaccess.home li.shop-menu ul').remove();
    /*check if to show shop sub menu*/
    var shopmenu = $('.sidebar-menu.theme.shop').html();
    if (shopmenu!=undefined){
        $('.m-pikabu-left .nav-menuitems .quickaccess.home li.shop-menu').append('<ul>'+$('.sidebar-menu.theme').html()+'</ul>');
        if ($('.m-pikabu-left .nav-menuitems .quickaccess.language').attr('origin-height')==undefined)/* only call once */
            calibratelangmenu($('.m-pikabu-left').height(),-5);
        return;
    }
    else {
        calibratelangmenu(0,0);
    }    
    /*check if to show profile sub menu*/
    if ($('#profile_page').length>0){
        $('.m-pikabu-left .nav-menuitems .quickaccess.home .profile-menu ul').show();
        calibratelangmenu($('.m-pikabu-left .nav-menuitems .quickaccess.home .profile-menu ul').height(),-5);
        return;
    }
    else{
        $('.m-pikabu-left .nav-menuitems .quickaccess.home .profile-menu ul').hide();
        calibratelangmenu(0,0);
    }
    /*check if to show account sub menu*/
    if ($('#account_page').length>0){
        $('.m-pikabu-left .nav-menuitems .quickaccess.home .account-menu ul').show();
        calibratelangmenu($('.m-pikabu-left .nav-menuitems .quickaccess.home .account-menu ul').height(),-5);
        return;
    }
    else {
        $('.m-pikabu-left .nav-menuitems .quickaccess.home .account-menu ul').hide();
        calibratelangmenu(0,0);
    }    
}

function calibratelangmenu(offset,adjust)
{
    var langMenuTopPos = parseInt($('.m-pikabu-left .nav-menuitems .quickaccess.language').css('top'), 10);/*force to return as number*/
    var height = langMenuTopPos + offset + adjust;
    $('.m-pikabu-left .nav-menuitems .quickaccess.language').css({top:height});
    $('.m-pikabu-left .nav-menuitems .quickaccess.language').attr('origin-height',langMenuTopPos);
}
