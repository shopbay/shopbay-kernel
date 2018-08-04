function closeaccount(message,id){

    if (confirm(message)){
        $('.page-loader').show();
        $('#'+id).submit();
    }
}
