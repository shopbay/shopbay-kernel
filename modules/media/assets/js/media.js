function initmultimediafiles()
{
    $('#MediaUploadForm-form').bind('fileuploaddestroyed', function() {restoremultimediafiles();});
}

function restoremultimediafiles()
{
    if ($('.files tr').not('.empty').size()==0){
        $('.files').parent().removeClass('items');
        $('.files tr.empty').show();
        $('.files').parent().addClass('no-items');
    }
}

function enabledelete_multimediafiles(selection)
{
    $(selection).click(function (e) {
        e.preventDefault();
        var obj = $(this);
        $.ajax({
          dataType: 'json',
          url: obj.data('url'),
          type: obj.data('type')
        })
        .success(function(){
            obj.parent().parent().remove();
            restoremultimediafiles();
        });
    });
}

function MediaUploadFormProgress(event)
{
    updateprogressbar(event,'.file-upload-progress td .bar');
}

function updateprogressbar(event,selection)
{
    var percent = (event.loaded / event.total) * 100;
    console.log('file upload progress '+percent+'%');
    /*percent variable can be used for modifying the length of your progress bar.*/
    $('.file-upload-progress').show();
    $(selection).css({width:percent+'%'});
    $(selection).html(Math.round(percent)+'%');
    if (percent==100)
        $('.file-upload-progress').hide();
}