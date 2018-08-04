function opennetwork(returnUrl)
{
    if(window.opener) {
        if(returnUrl) {
            window.opener.location.href = returnUrl;
        } else {
            window.opener.location.reload();
        }
        window.close();
    }
    else {
        window.location.href = returnUrl;
    }
}
function unlinknetwork(obj)
{
    var row=obj.parent().parent();
    var token=$('#oauth_networks').data('csrf');
    var unlinkRoute=$('#oauth_networks').data('unlinkRoute');
    var provider=$("#oauth_networks .keys span:nth-child("+(row.index()+1)+")").text();
    $.ajax({
        type:'POST',
        url:unlinkRoute,
        datatype: 'json',
        data:{network:provider,APP_CSRF_TOKEN:token.APP_CSRF_TOKEN},
        success:function(data){
            if (data.status=='success') {
                row.find('.oauth-network-actions').html(data.actions);
                row.find('.provider-link a').attr('href','#');
                row.find('.user-info').html('');
                /*Keep network row there for relink*/
                /*$("#oauth_networks .keys span:nth-child("+(row.index()+1)+")").remove();
                row.remove();*/
            }
            $('#flash-bar').html(data.flash);
        },
    });
}