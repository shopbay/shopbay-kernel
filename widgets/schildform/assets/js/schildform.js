function getchildform(url){
    var deletecontrol = $('#child_table').data('deleteControl');
    $(".page-loader").show();
    $.get(url, function(data) {
        if (data.status=='success'){
            $('#child_table').addClass('items');
            $('#child_table thead').show();
            $('#child_table tbody').append(data.form);
            if (deletecontrol=='last'){
                $(".del-button").hide();
                $(".del-button:last").show();
            }
            if (deletecontrol=='all'){
                $(".del-button").show();
            }
            $(".page-loader").hide();
            var tr = $('#child_table tbody tr:last-child').attr('id');
            if ($('#'+tr+' .page-tab').length>0)
                $('#'+tr+' .page-tab').yiitab();
        }
        else if (data.status=='serviceNotAvailable'){
            alert(data.message);
            $(".page-loader").hide();
        }
        else
            alert('No data found');
    })
    .error(function(XHR) { 
        error(XHR); 
        $(".page-loader").hide();
    }); 
}

function removechildform(url,key){
    var deletecontrol = $('#child_table').data('deleteControl');
    $(".page-loader").show();
    $.get(url+'/key/'+key, function(data) {
        $(".page-loader").hide();
        if (data.status=='success'){
            $('#child_'+key).remove();
            if (deletecontrol=='last'){
                $(".del-button").hide();
                $(".del-button:last").show();
            }
            if (data.count==0){
                $('#child_table thead').hide();
                $('#child_table').removeClass('items');
            }
        }
        else {
            $('#flash-bar').html(data.message);
            scrolltop();
        }
    })
    .error(function(XHR) { 
        error(XHR); 
        $(".page-loader").hide();
    }); 
}

function removeallchildforms(url){
    $(".page-loader").show();
    $.get(url+'/key/all', function(data) {
        if (data.status=='success'){
            $('#child_table thead').hide();
            $('#child_table tbody').html('');
            $('#child_table').removeClass('items');
            $(".page-loader").hide();
        }
        else
            alert('Child form cannot be deleted');
    })
    .error(function(XHR) { 
        error(XHR); 
        $(".page-loader").hide();
    }); 
}