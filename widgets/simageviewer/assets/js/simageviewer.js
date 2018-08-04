function swapimage(cssclass,id){
    $('.'+cssclass+' a').hide();
    $('#picture-'+id).show();
}   
function loadfancybox(cssclass,imagename){
    $('.'+cssclass+' a').fancybox({
        transitionIn:'none',
        transitionOut:'none',
        titlePosition:'over',
        titleFormat: function(title, currentArray, currentIndex, currentOpts) {
            return '<span id="fancybox-title-over"><span style="float:right">Image ' +  (currentIndex + 1) + ' / ' + currentArray.length + ' ' + title + ' </span>' + imagename + '</span>';
        },
        onComplete:function() {
            $("#fancybox-wrap").hover(function() {
                    $("#fancybox-title-over").show();
            }, function() {
                    $("#fancybox-title-over").hide();
            });
        }
    });    
}

