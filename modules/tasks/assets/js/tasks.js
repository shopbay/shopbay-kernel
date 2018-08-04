function redirect(url){
    window.location.href = url;
}
/*refresh workfow action buttons after yiiGridView.ajaxUpdate*/
function wrb(id){
    var obj = $('#'+id+' a[fn]');
    obj.click(function(){window[obj.attr('fn')](obj);});
}
function getObjId(obj){
    var key = parseInt(obj.parent().parent().index()) + 1;
    return obj.parent().parent().parent().parent().parent().find('> .keys span:nth-child('+key+')').text();
}
function wp(obj) {
    _w(getObjId(obj),'Plan',obj.data('action'));
}
function wpk(obj) {
    _w(getObjId(obj),'Package',obj.data('action'));
}
function wt(obj) {
    _w(getObjId(obj),'Tutorial',obj.data('action'));
}
function wts(obj) {
    _w(getObjId(obj),'TutorialSeries',obj.data('action'));
}
function ws(obj) {
    _w(getObjId(obj),'Shop',obj.data('action'));
}
function wi(obj) {
    _w(getObjId(obj),'Item',obj.data('action'));
}
function wo(obj) {
    _w(getObjId(obj),'Order',obj.data('action'));
}
function wom(obj) {
    _w(getObjId(obj),'ShippingOrder',obj.data('action'));
}
/*item quick access of _w()*/
function qwi(id,action) {
    _w(id,'Item',action);
}
/*order quick access of _w()*/
function qwo(id,action) {
    _w(id,'Order',action);
}
/*shippingorder quick access of _w()*/
function qwom(id,action) {
    _w(id,'ShippingOrder',action);
}
/*order quick access of _w() by decision */
function qwByDecision(obj) {
    _w(obj.data('id'),obj.data('type'),obj.data('action'),obj.data('decision'));
}
/*order quick access of _w() by action */
function qwByAction(obj) {
    _w(obj.data('id'),obj.data('type'),obj.data('action'));
}
function _w(id,type,action,decision) {
    $.ajax({
        type: 'POST',
        url: '/tasks/workflow',
        data: {id: id, type: type,action:action,decision:decision, APP_CSRF_TOKEN:$('#csrf-form').find('input').val()},
        beforeSend:function(){
            $('.page-loader').show();
        },
        success:function(data){
            if (data.redirect!=undefined){
                window.location.href = data.redirect;
            }
            else {
                $('.page-layout').html(data);
                $(document).ready(function(){
                    renderform();
                    //enable jquery.fileupload
                    $('.upload-form').fileupload(
                        {
                            previewMaxWidth:30,
                            previewMaxHeight:30,
                            url:$('.upload-form').attr('action'),
                            autoUpload:true,
                            multiple:true
                        } 
                    );
                    if (type=='ShippingOrder'){
                        $('a[fn="wi"]').click(function(){wi($(this));});
                    }
                });
                scrolltop();
            }
        },
        error:function(XHR,textStatus,errorThrown){
            error(XHR,textStatus,errorThrown);
        }
    });
}
/**
 * Enable JUI buttons and attach onClick event
 * @returns {undefined}
 */
function renderform(){
    $('.page-loader').hide();
    $('[id$="button"]').each( function( key, value ){
        $(this).button([]);
    });
    
    if ($('.sections').length > 0) {/*if components exists*/
        enablesectionbuttons();
    }

    if ($('#rating').length > 0) {/*if components exists*/
        $('#rating > input').rating();
    }
    var disabledcnt = 0;
    $('.transition-button').each( function( key, value ) {
        if ($(this).data("decision")==null)
            $(this).button().click(function(){t();});
        else {
            $(this).button().click(function(){td($(this).data("decision"),$(this).data("message"));});
            if ($(this).attr("transition-disabled")){
                $(this).attr("disabled",true).addClass("ui-state-disabled");
                disabledcnt++;
            }
        }
    });
    if (disabledcnt===$('.transition-button').length){
        $('[id*="condition"]').attr("disabled",true);
        $('#AttachmentForm_description').attr("disabled",true);
        $('#attachment').css("pointer-events","none");
    }

    if ($('.chzn-select-condition1').length > 0) {/*if chosen components exists*/
        $('.chzn-select-condition1').chosen();
        $('.chzn-search').hide();
    }
    
    if ($('.chzn-select-condition2').length > 0) {/*if chosen components exists*/
        $('.chzn-select-condition2').chosen();
        $('.chzn-search').hide();
    }
    
    unloadevelatezoom();
}
function td(decision,message){
    clearerror();
    
    if (message!=undefined && message.length>0){
        if (confirm(message)==false) 
            return false;
    }
   
    $('.page-loader').show();
    $.post($('#transition-form').attr('action'), $('#transition-form').serialize()+'&Transition[decision]='+decision, function(data) {
        if (data.status=='success'){
            window.location.href = data.redirect;
        }
        else {
           $('#flash-bar').html(data.flash);
           $('#transition').html(data.form);
        }
        renderform();
    })
    .error(function(XHR,textStatus,errorThrown){
        error(XHR,textStatus,errorThrown);
    });
}
function t() {
    $.ajax({
        type: 'POST',
        url: $('#transition-form').attr('action'),
        data: $('#transition-form').serialize(),
        beforeSend:function(){
            clearerror();
            $('.page-loader').show();
        },
        success:function(data){
            if (data.status=='success'){
                window.location.href = data.redirect;
            }
            else {
               $('#flash-bar').html(data.flash);
               $('#transition').html(data.form);
            }
            renderform();
        },
        error:function(XHR,textStatus,errorThrown){
            error(XHR,textStatus,errorThrown);
        }
    });
}
function r(obj) {
    $.ajax({
        type: 'POST',
        url: '/tasks/'+obj.data('type')+'/rollback',
        data: {id:getObjId(obj),APP_CSRF_TOKEN:$('#csrf-form').find('input').val()},
        beforeSend:function(){
            clearerror();
            $('.page-loader').show();
        },
        success:function(data){
            if (data.status=='success'){
                window.location.href = data.redirect;
            }
            else {
               $('#flash-bar').html(data.flash);
            }
            renderform();
        },
        error:function(XHR,textStatus,errorThrown){
            error(XHR,textStatus,errorThrown);
        }
    });
}
function q(obj) {
    $.ajax({
        type: 'POST',
        url: '/questions/management/answer',
        data: {id: getObjId(obj), APP_CSRF_TOKEN:$('#csrf-form').find('input').val()},
        beforeSend:function(){
            $('.page-loader').show();
        },
        success:function(data){
            $('.page-layout').html(data);
            renderform();
            scrolltop();
        },
        error:function(XHR,textStatus,errorThrown){
            error(XHR,textStatus,errorThrown);
        }
    });
}
//for campaign, product and others activation/deactivation
function task(){
    var chk = 0;
    $('input[name^="task-checkbox"]').each(function() {
        if ($(this).prop('checked'))
           chk++;
    }); 
    //verify at least one item must be checked out
    if (chk==0) {
        alert('Please select one to proceed');
        return false;
    }
    $('.page-loader').show();
    $.post($('#task-form').attr('action'),$('#task-form').serialize(),function(data){
        $('.page-content').html(data);
        $('.page-loader').hide();
    })
    .error(function(XHR,textStatus,errorThrown){
        error(XHR,textStatus,errorThrown);
    });     
}