function dislike(id){
    $.get('/likes/management/undo/id/'+id, function(data) {
          $('.main-view .body').html(data.body);
          $('#flash-bar').html(data.flash);
    })
    .error(function(XHR) { 
       error(XHR);
    });
}
