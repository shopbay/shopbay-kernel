function sanimatedsearch(input,field,url) {
    $('#page_modal .page-loader').show();
    var query = $('#'+input).val();
    console.log('sanimatedsearch input: '+input+', query: '+query);
    window.location.href = url+'&'+field+'='+query;
}