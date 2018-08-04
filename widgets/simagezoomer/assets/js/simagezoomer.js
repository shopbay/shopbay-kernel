function loadevelatezoom(){
    $('.elevatezoom').each(function(){ 
        var selection = '#'+$(this).find('img').attr('id');
        
        if (mobiledisplay())/* change to mobile mode*/
            var config = evelatezoomconfig_mobile();
        else 
            var config = evelatezoomconfig_default();
        
        if ($(this).find('.gallery').length>0){
            config.gallery = $(this).find('.gallery').attr('id');
            config.galleryActiveClass = 'active';
        }
        if ($(this).parent().hasClass('product-y-image')){
            config.zoomWindowPosition = 11;/*show at left*/
        }
        $(selection).elevateZoom(config);   
        console.log('zoomer '+selection,config);
    });
}

function unloadevelatezoom()
{
    $.removeData('.elevatezoom', 'elevateZoom');//remove zoom instance from image
    $('.zoomContainer').remove();
}

function evelatezoomconfig_default()
{
    return  {
        cursor:'crosshair',
        scrollZoom:true,
        zoomWindowFadeIn:500,
        zoomWindowFadeOut:600,
        zoomWindowWidth:500,
        zoomWindowHeight:400,
        borderSize:1
        /*loadingIcon:'loading.gif'*/
    };    
}
function evelatezoomconfig_mobile()
{
    return {
        'cursor':'crosshair',
        'scrollZoom':true,
        'zoomWindowFadeIn':500,
        'zoomWindowFadeOut':600,
        'zoomWindowPosition': 14,/*mobile mode*/
        'zoomWindowWidth':250,/*mobile mode*/
        'zoomWindowHeight':200,/*mobile mode*/
        'borderSize':1,
//        'zoomType': "lens",
//        'lensShape': "round",
//        'lensSize': 200,
    }; 
}