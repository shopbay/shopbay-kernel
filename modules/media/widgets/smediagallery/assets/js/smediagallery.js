function getmediagalleryform(url) 
{
    if ($('#media_gallery_modal').length>0)
        $('#media_gallery_modal').remove();  
    $.ajax({
        url: url,
        dataType: 'json',
        cache: false,
        success: function(data) {
            $('.page-container').append(data.modal);
            $(document).ready(function () {
                setmediagalleryready();
            });            
        }
    });
}

function setmediagalleryready(confirmCallback,cancelCallback,refreshgallerymethod)
{
    if (confirmCallback==undefined){
        var confirmCallback = function(){
            console.log('setmediagalleryready confirmCallback');
            addimagefrommediagallery($('#mediaGalleryConfirmButton').data('route'),$('.media-gallery-dialog').data('formModel'));
        };
    }
    if (cancelCallback==undefined){
        var cancelCallback = function(){
            console.log('setmediagalleryready cancelCallback');
            closesmodal('#media_gallery_modal');
        };
    }
    if (refreshgallerymethod==undefined){
        refreshgallerymethod = 'setmediagalleryready';
    }
    
    $('.page-container .page-content').css({'min-height':'1000px'});/*cater enough height for media gallery*/
    $('.page-container').off('click');
    $('.media-gallery-dialog .items .list-box').click(function() {
        $('.media-gallery-dialog .items .list-box').removeClass('selected');
        $(this).addClass('selected');
    });
    $('#mediaGalleryCancelButton').button().off('click');/*off first*/
    $('#mediaGalleryCancelButton').button().click(function(){cancelCallback();});
    $('#mediaGalleryConfirmButton').button().off('click');/*off first*/
    $('#mediaGalleryConfirmButton').button().click(function(){confirmCallback();});
    setpaginationlink(refreshgallerymethod);
}

function addimagefrommediagallery(route,formModel){
    var url = route+'?m='+$('.media-gallery-dialog .list-box.selected').data('media');
    $.ajax({
        url: url,
        dataType: 'json',
        cache: false,
        success: function(data) {
            if (data.status=='success'){
                closesmodal('#'+$('#mediaGalleryCancelButton').data('container'));
                if (formModel=='SingleImageForm'){
                    $('.single-image-container .template-download').remove();
                    $('.single-image-container').append(data.html);
                    $('#SingleImageForm-form').bind('fileuploaddestroyed', function() {restoresingleimage();});
                }
                else {/* MultipleImagesForm */
                    initmultiimages();/*refer to simagemanager.js*/
                    $('.images-gallery table tbody').append(data.html);
                }
            }
            else {
                alert(data.message);
            }
        }
    });
}

function setpaginationlink(refreshgallerymethod)
{
    console.log('setpaginationlink refreshgallerymethod = '+refreshgallerymethod);
    $('.media-gallery-dialog .spager li').each(function(){
        var href = $(this).find('a').attr('href')+'?ajax&pager=true';
        $(this).find('a').attr('href','javascript:void(0);');
        $(this).find('a').attr('onclick','getmediagallerypage("'+href+'","'+refreshgallerymethod+'")');
    });
}

function getmediagallerypage(url,refreshgallerymethod) 
{
    $.ajax({
        url: url,
        dataType: 'json',
        cache: false,
        success: function(data) {
            $('.media-gallery-dialog .list-view').replaceWith(data.modal);
            $(document).ready(function () {
                window[refreshgallerymethod]();/* a more robust way to support customization */
                scrolltop();
            });            
        }
    });
}
