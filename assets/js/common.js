$(document).ready(function () {
    $(window).scroll(function () {
        if ($(this).scrollTop() > 100) 
            $('.scrollup').fadeIn();
        else 
            $('.scrollup').fadeOut();
    });
    $('.scrollup').click(function () {
        $("html, body").animate({scrollTop:0}, 500);
        return false;
    });
    $(".page-container").click(function() {
        if ($('.smodal-container').length>0)
            closesmodal('.smodal-wrapper');/*checkout smodal.js for event.stopPropagation();*/
    });
    /**
     * @see common.css for dynamic header container width due to 'position:fixed'
     * width must be set for mobile device else mobile button positioning is not well placed
     */
    var width = $(window).width();
    if (width <= 640){/*start adjustment when device screen is smaller than 640*/
        console.log('device width = '+width);
        $(".header-container").css({'width':(width+1)+'px'});
    }
});
function getdomain(hostinfo)
{
    if (hostinfo==undefined)
        hostinfo='';
    hostinfo += '/';
    return hostinfo;
}
function openmodalbyjsonp(url,data){
    $.ajax({
        url: url,
        jsonp: "callback",
        dataType: "jsonp",
        data: data,
        success: function( response ) {
            fillmodal(response.container,response.html,response.action);
        }
    });
}
function login(hostinfo,route){
    signin(hostinfo,route,'login');
}
function signin(hostinfo,route,path) {
    if (path==undefined)
        path='signin';
    if (route==undefined)
        route='account/authenticate/loginform';
    if (!mobiledisplay()){
        closesmodal('#page_modal');
        $('body').css('overflow', 'hidden');   
        openmodalbyjsonp(getdomain(hostinfo)+route,{"container":"#page_modal","action":"login"});
        $("html, body").animate({scrollTop: 0}, 100);
    }
    else {
        var parser = document.createElement('a');/*making use of <a> element */
        parser.href = getdomain(hostinfo)+route;
//        parser.protocol; // => "http:"
//        parser.hostname; // => "example.com"
//        parser.port;     // => "3000"
//        parser.pathname; // => "/pathname/"
//        parser.search;   // => "?search=test"
//        parser.hash;     // => "#hash"
//        parser.host;     // => "example.com:3000"        
        window.location.href = getdomain(hostinfo)+path+parser.search;//add on the query params e.g. return url
    }
}
function logincallback(){
    $('#LoginForm_username').focus();
    loadoauth();
}
function loadoauth(){
    $('.oauthWidget a').click(function() {
        var signinWin;
        var screenX     = window.screenX !== undefined ? window.screenX : window.screenLeft,
            screenY     = window.screenY !== undefined ? window.screenY : window.screenTop,
            outerWidth  = window.outerWidth !== undefined ? window.outerWidth : document.body.clientWidth,
            outerHeight = window.outerHeight !== undefined ? window.outerHeight : (document.body.clientHeight - 22),
            width       = 480,
            height      = 480,
            left        = parseInt(screenX + ((outerWidth - width) / 2), 10),
            top         = parseInt(screenY + ((outerHeight - height) / 2.5), 10),
            options    = (
                'width=' + width +
                ',height=' + height +
                ',left=' + left +
                ',top=' + top
            );

        signinWin=window.open(this.href,'Login',options);

        if (window.focus) {signinWin.focus()}

        return false;
    });    
}
function signup(hostinfo) {
    if (!mobiledisplay()){
        closesmodal('#page_modal');
        $('body').css('overflow', 'hidden');   
        openmodalbyjsonp(getdomain(hostinfo)+'account/signup/getform',{"container":"#page_modal","action":"signup"});
        $("html, body").animate({scrollTop: 0}, 100);
    }
    else {
        window.location.href = getdomain(hostinfo)+'signup';
    }
}
function signupcallback(){
    $('#SignupForm_email').focus();
    $('#page_modal .smodal-content').find('.form.form-container').css('padding-bottom', '10px');   
    $('#signup-captcha').click(function(){
        jQuery.ajax({
            url: "/account/signup/captcha/refresh/1",
            dataType: 'json',
            cache: false,
            success: function(data) {
                jQuery('#signup-captcha').attr('src', data['url']);
                jQuery('body').data('/account/signupcaptcha.hash', [data['hash1'], data['hash2']]);
            }
        });
        return false;
    });
}
/*a helper function to decide if in mobile display*/
function mobiledisplay(){
   if ($('.mobile-button').length>0){
       return $('.mobile-button').css('display')!='none';
   }
   else if ($('.m-pikabu-nav-toggle-icon').length>0)
       return $('.m-pikabu-nav-toggle-icon').css('display')!='none';
   else
       return false;
}
function switchlang(lang)
{
    $('#language').val(lang);
    $('#langform').submit();
}

function logout() 
{
    $.ajax({
        url: "/account/authenticate/logout",
        dataType: 'json',
        cache: false,
        success: function(data) {
            /*redirect to after logout url*/
            if (data.showModal){
                $('.page-container').append(data.modal);
                $('#logout_modal .smodal').css('top', '50px');  
                $(document).ready(function () {
                    $(".page-container").click(function() {
                        if ($('#logout_modal .smodal-container').length>0)
                            closelogoutmodal('logout_modal',data.redirect);
                    });
                });            
            }
            else 
                window.location.href = data.redirect;
        }
    });
}

function closelogoutmodal(id,redirect) 
{
    closesmodal(id);
    window.location.href = redirect;
}

function servicepostcheck(form)
{
    $.post($('#'+form).attr('action'), $('#'+form).serialize() , function(data) {
        if (data.status=='serviceNotAvailable'){
            //$('#flash-bar').html(data.message);
            window.location.href = data.return_url;
        }
        else{
            document.open();
            document.write(data);
            document.close();
        }
    })
    .error(function(XHR) { 
        error(XHR); 
    });
    
}

function redirect(url)
{
    window.location.href = url;
}