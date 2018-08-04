function searchinternal(url) {
    $('#page_modal .page-loader').show();
    $.get(url, function( data ) {
        $('#search_result_page .main-view .heading .subscript').html(data.query);
        $('#search_results').replaceWith(data.results);
        $('#page_modal .page-loader').hide();
        $('#search_results').yiiListView({'ajaxUpdate':['search_results'],'ajaxVar':'ajax','pagerClass':'spager','loadingClass':'list-view-loading','sorterClass':'sorter','enableHistory':false});
    })
    .error(function(XHR) { 
        $('#page_modal .page-loader').hide();
        error(XHR); 
    });   
}
function dosearch() {
    $('#page_modal .page-loader').show();
    var url = '/search?query='+$('#q').val();
    searchinternal(url);
}

function sitesearch() {
    $('#page_modal .page-loader').show();
    window.location.href = '/search?query='+$('#q').val();
}