$(document).ready(function () {
    $('.smodal-wrapper .smodal-content').click(function(event) {
        event.stopPropagation();
    });
});        
function opensmodal(container,url,action) {
    $('#smodal_loader').show();
    $.get(url, function( data ) {
        fillmodal(container,data,action);
    })
    .error(function(XHR) {
        alert(XHR.status+' '+XHR.statusText+'\r\n' + XHR.responseText);
    });
}
function closesmodal(container) {
    $(container+' .smodal-overlay').hide();
    $(container+' .smodal-content').html('');
    $(container+' .smodal-container').hide();
    $('body').css('overflow', 'auto');    
}
function fillmodal(container,data,action){
    $(container+' .smodal-overlay').show();
    $(container+' .smodal-content').html(data);
    $(container+' .smodal-container').show();
    $(document).ready(function () {
        //$(container+' .smodal-content').find('#'+action+'-button').button([]);    
        if (action=='signup')
            signupcallback();
        if (action=='login')
            logincallback();
        if (action=='preview')
            previewcallback();
    });        
    $('#smodal_loader').hide();
}