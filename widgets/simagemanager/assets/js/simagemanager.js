function getimageurlform(url) 
{
    if ($('#imageurlform_modal').length>0)
        $('#imageurlform_modal').remove();  
    $.ajax({
        url: url,
        dataType: 'json',
        cache: false,
        success: function(data) {
            $('.page-container').append(data.modal);
            $(document).ready(function () {
                $(".page-container").off('click');
                $('#imageUrlConfirmButton').button().click(function(){addimagebyurl($(this).data('route'),$('.imageurlform-dialog').data('formModel'));});
                $('#imageUrlCancelButton').button().click(function(){closesmodal('#'+$(this).data('container'));});
            });            
        }
    });
}

function addimagebyurl(route,formModel){
    var url = route+'?u='+$('#imageurl').val();
    $.ajax({
        url: url,
        dataType: 'json',
        cache: false,
        success: function(data) {
            if (data.status=='success'){
                closesmodal('#'+$('#imageUrlCancelButton').data('container'));
                if (formModel=='SingleImageForm'){
                    $('.single-image-container .template-download').remove();
                    $('.single-image-container').append(data.html);
                    $('#SingleImageForm-form').bind('fileuploaddestroyed', function() {restoresingleimage();});
                }
                else {/* MultipleImagesForm */
                    initmultiimages();
                    $('.images-gallery table tbody').append(data.html);
                }
            }
            else {
                alert(data.message);
            }
        }
    });
}

function insertsingleimage() {
    $('.single-image-container .preview').hide();
    $('.single-image-container .template-download').css({height:'auto'});
    enabledeletebutton('#delete-button');
}

function restoresingleimage() {
    $('.single-image-container .preview').show();
    $('.single-image-container .template-download').remove();
}

function initmultiimages()
{
    $('.files').parent().removeClass('no-items');
    $('.files tr.empty').hide();
    $('.files').parent().addClass('items');
    $('#MultipleImagesForm-form').bind('fileuploaddestroyed', function() {restoremultiimages();});
    $('.page-loader').hide(); 
}

function restoremultiimages()
{
    if ($('.files tr').not('.empty').size()==0){
        $('.files').parent().removeClass('items');
        $('.files tr.empty').show();
        $('.files').parent().addClass('no-items');
    }
}

function enabledeletebutton(selection)
{
    $(selection).click(function (e) {
        e.preventDefault();
        var obj = $(this);
        $.ajax({
          dataType: 'json',
          url: obj.data('url'),
          type: obj.data('type')
        })
    });
}

function enabledeletebutton_multi(selection)
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
            restoremultiimages();
        });
    });
}

function SingleImageFormProgress(event)
{
    $('.file-upload-progress').css({height:$('.files').height()});
    $('.files .preview').hide();
    if ($('.files .template-download').length>0){
        $('.files .template-download').remove();
    }
    updateprogressbar(event,'.file-upload-progress .bar');
}

function MultipleImagesFormProgress(event)
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