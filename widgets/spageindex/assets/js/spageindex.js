function filter(route,model,scope,callback){
    if ( $('#pageindex_loader.absolute').length>0){
        var pos = $(window).scrollTop() + $(window).height() / 2;
        $('#pageindex_loader.absolute').css({'top':pos,'bottom':'auto'});
    }

    var viewOption = $('.main-view .body > div').data('viewOption');
        
    $('#pageindex_loader').show();
    $.get('/'+route+'?scope='+scope+'&option='+viewOption, function(data) {
        renderarrow(scope);
        rendertab(scope);
        renderpagemenu(scope);
        $('.main-view .body').html(data.widget);
        if ($('.spagefilter').length>0){
            $('.page-sidebar').html(data.pagefilter);
        }
        $('#pageindex_loader').hide();
        renderpagedescription($('.grid-view').length?'gridview':'listview');
        $(document).ready(function(){
            if ($('.spagefilter').length>0){
                refreshpagefilterform('spagefilter','date');
            }
            if ($('.grid-view').length>0) {
                jQuery('#'+scope).yiiGridView({
                    ajaxUpdate:[scope],
                    ajaxVar: 'ajax',
                    pagerClass: 'spager',
                    loadingClass: 'grid-view-loading',
                    filterClass: 'filters',
                    tableClass: 'items',
                    selectableRows: 1,
                    enableHistory: false,
                    updateSelector: '{page}, {sort}',
                    filterSelector: '{filter}',
                    pageVar: model+'_page'
                });
            }
            if ($('.list-view').length>0) {
                jQuery('#'+scope).yiiListView({
                    ajaxUpdate: [scope],
                    ajaxVar: 'ajax',
                    pagerClass: 'spager',
                    loadingClass: 'list-view-loading',
                    sorterClass: 'sorter',
                    enableHistory: false
                });            
            }
        });
        console.log('filter callback function ',callback);
        callback();
    })
    .error(function(XHR,textStatus,errorThrown){
        error(XHR,textStatus,errorThrown);
    });
}
function rendertab(scope){
    $('.main-view .heading .tabs a').removeClass('active');
    $('#tab-'+scope+' a').addClass('active');
}
function renderarrow(scope){
    if ($('.main-view .heading .arrow-wrapper').length>0) {
        $('.main-view .heading .arrows li').removeClass('active');
        $('.main-view .heading #arrow-'+scope).addClass('active');
        $('.main-view .heading .arrow-wrapper').removeClass('show');
        $('.main-view .heading .arrow-wrapper').addClass('hidden');
        var position = $('#arrow-'+scope).position();
        var offset = $('#arrow-'+scope).width()/2 - 10;
        $('.main-view .heading .arrow-wrapper.'+scope).css({left:position.left+offset,position:'absolute'});
        $('.main-view .heading .arrow-wrapper.'+scope).removeClass('hidden');
        $('.main-view .heading .arrow-wrapper.'+scope).addClass('show');
    }    
}
function listviewupdate(){
    if ($('.star-rating').length>0) {
        $('.star-rating > input').rating({'readOnly':true});
    }   
}
function renderpagemenu(scope){
    $('.pageindex-page-menu').removeClass('active');
    $('.pageindex-page-menu.'+scope).addClass('active');
}
function renderpagedescription(viewoption){
    var desc = '';
    if (viewoption=='gridview')
        desc = $('.main-view .body .grid-view').data('description');
    else 
        desc = $('.main-view .body .list-view').data('description');
    $('.main-view .heading .description').html(desc);
}
