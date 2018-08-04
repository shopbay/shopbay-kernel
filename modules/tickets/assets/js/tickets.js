function replyticket(form) {
    $('#flash-bar').html('');
    $.post('/tickets/management/reply', $('#'+form).serialize(), function(data) {
        if (data.status=='success'){
            $('#ticket_reply').show();
            $('.line-break.data-element').last().remove();
            $('#ticket_reply').append(data.content);
            $('.reply-form-wrapper textarea').val('');
            if ($('#ticket_reply #ticket_reply').length==1){
                $('.reply-wrapper').css({'border-top': '0px solid #F5F5F5'});
            }
        }
        else {
            $('#flash-bar').html(data.flash);
        }
    })
    .error(function(XHR) { 
        alert(XHR.status+' '+XHR.statusText+'\r\n' + XHR.responseText);
    });
}
