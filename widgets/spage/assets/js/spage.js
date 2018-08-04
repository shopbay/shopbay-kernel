function submitform(id){
    $('.page-loader').show();
    $('#'+id).submit();
}
function error(XHR){
    $('#loading').hide();
    var err = XHR.status+' '+XHR.statusText+'<br>';      
    err += XHR.responseText;      
    $('#flash-bar').attr('class','flash-error').html(err).show();
    scrolltop();
}
function errormsg(msg){
    $('#flash-bar').attr('class','flash-error').html(msg).show();
    scrolltop();
}
function clearerror(){
    $('#flash-bar').removeClass();
    $('#flash-bar').html('');
    $('div.errorSummary').remove();
    $('div.errorMessage').remove();
    $('.page-content div label').toggleClass();
    $('div input').removeClass('error');
    $('div select').removeClass('error');
    $('div textarea').removeClass('error');    
}
function scrolltop(){
    var new_position = $('body').offset();
    window.scrollTo(new_position.left,new_position.top);
}