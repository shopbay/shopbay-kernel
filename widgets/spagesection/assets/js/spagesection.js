$(document).ready(function(){
    enablesectionbuttons();
});
function enablesectionbuttons(){
    var mode = $('#section-buttonmode').text();
    if (mode=='image')
        enableimagebuttons();
    else
        enabletextbuttons();
}
function enabletextbuttons() {
    //generic use for section expand/close all
    $('.section-button').click(function(){
        $('.'+$(this).attr('name')+' .section-body').slideToggle(0);
        if ($(this).find('i').hasClass('fa-angle-down')) {
            $(this).find('i').removeClass('fa-angle-down');
            $(this).find('i').addClass('fa-angle-right');
        }
        else {
            $(this).find('i').removeClass('fa-angle-right');
            $(this).find('i').addClass('fa-angle-down');
        }
    });
    $('.section-button-all').click(function(){
        if ($(this).find('i').hasClass('fa-angle-double-up')) {
            $(this).find('i').removeClass('fa-angle-double-up');
            $(this).find('i').addClass('fa-angle-double-down');
            $('.section-body').hide();
            $('.section-button').find('i').removeClass('fa-angle-down');
            $('.section-button').find('i').addClass('fa-angle-right');
        }
        else {
            $(this).find('i').removeClass('fa-angle-double-down');
            $(this).find('i').addClass('fa-angle-double-up');
            $('.section-body').show();
            $('.section-button').find('i').removeClass('fa-angle-right');
            $('.section-button').find('i').addClass('fa-angle-down');
        }
    });
}
function enableimagebuttons() {
    var asseturl = $('#section-asseturl').text();
    //generic use for section expand/close all
    $('.section-button img').click(function(){
        $('.'+$(this).parent().attr('name')+' .section-body').slideToggle(0);
        if ($(this).attr('src') == asseturl+'/open.jpg') 
            $(this).attr('src',asseturl+'/close.jpg');
        else 
            $(this).attr('src',asseturl+'/open.jpg');

    });
    $('.section-button-all img').click(function(){
        if ($(this).attr('src') == asseturl+'/open.jpg') {
            $(this).attr('src',asseturl+'/close.jpg');
            $('.section-body').show();
            $('.section-button img').attr('src',asseturl+'/close.jpg');
        }
        else {
            $(this).attr('src',asseturl+'/open.jpg');
            $('.section-body').hide();
            $('.section-button img').attr('src',asseturl+'/open.jpg');
        }
    });
}