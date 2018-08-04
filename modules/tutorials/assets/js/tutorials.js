function updatetutorialseries(ul){
    var menu = '';
    ul.find('li').each(function(i){
       menu += $(this).attr('id')+',';
    });
    $('#TutorialSeriesForm_tutorials').val(menu);
}
