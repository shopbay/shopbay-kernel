/**
 * This submit form will accept POST form data, parse and then rewrite the url to send to server to get filtering result via HTTP GET
 * (1) Remove the first element which is APP_CSRF_TOKEN; not needed in the url
 * (2) Not to include operator field from url query string
 * (3) Prepend operator into query string value if matched
 * @param {type} id
 * @returns {string}
 */
function submitpagefilterform(id){
    $('.page-loader').show();
    var formArray = $('#'+id).serializeArray();
    //console.log('initial formArray for id '+id,formArray);
    formArray.splice(0,1);//remove the first element which is APP_CSRF_TOKEN; not needed in the url
    var opsArray = new Array();
    for (var key in formArray){
        if (formArray[key].name.substr(-3)=='_op')
            opsArray.push(formArray[key]);/*collecting all the ops*/
    }
    //console.log('after form array',formArray);
    //console.log('ops array',opsArray);
    //serialize formArray into url params string
    var output = new Array();
    for (var key in formArray){
        /* not to include operator field from url query string*/
        if (formArray[key].name.substr(-3)!='_op' && formArray[key].value.length>0){
            //console.log(' serialize '+formArray[key].name+' value=' + formArray[key].value);
            var op = '';
            for (var opKey in opsArray){
                /* prepend operator if found */
                if (formArray[key].name+'_op'==opsArray[opKey].name){
                    op = opsArray[opKey].value;
                    break;
                }
            }
            output.push(formArray[key].name + '=' + encodeURIComponent( op + formArray[key].value) );
        }
    }
    var serialize = output.join('&');
    //console.log('after serialize conversion',serialize);
    var url = $('#'+id).attr('action')+'&'+serialize;
    //console.log('after serialize url',url);
    window.location.href =url;
}

function pagefiltertextonchange(selection,form,field)
{
    $('.'+selection+' #'+field).change(function(){
        submitpagefilterform(form);
    });
}
function pagefilterselectonchange(selection,form,field)
{
    pagefiltertextonchange(selection,form,field);//same as text field    
}

function pagefilterdateonchange(selection,form,field,autoSubmitOps)
{
    var autoSubmitOpsArray = $.parseJSON(autoSubmitOps);
    //console.log('autoSubmitOps array',$.parseJSON(autoSubmitOps));
    /* operator on change*/
    $('.'+selection+' #'+field+'_op').change(function(){
        var selectedOp = $(this).find('option:selected').attr('value');
        for (var key in autoSubmitOpsArray){
            //console.log('op = '+ key + ' , value = '+autoSubmitOpsArray[key]);
            if (selectedOp==key){
                var d = new Date();
                d.setDate( d.getDate() - autoSubmitOpsArray[key]);//minus x days
                var dStr = d.getFullYear() + '-' + (d.getMonth() + 1) + '-'+ d.getDate(); 
                $('.'+selection+' #'+field).val(dStr);
                submitpagefilterform(form);
                return;
            }
        }
        if ($('.'+selection+' #'+field).length>0)
            submitpagefilterform(form);
    });
    /* date field on change*/
    pagefiltertextonchange(selection,form,field);
}
/**
 * a quick method to refresh form (when ajax call from other calling party)
 * @returns {undefined}
 */
function refreshpagefilterform(selection,datefield)
{
    var form = $('.'+selection+' .form form').attr('id');
    //console.log('refresh form id',form);
    $('.'+selection+' input[type="text"]').each(function(i,e){
        if ($(this).attr('id')!=datefield)
            pagefiltertextonchange(selection, form,$(this).attr('id'));
    });
    $('.'+selection+' input[type="textarea"]').each(function(i,e){
        pagefiltertextonchange(selection, form,$(this).attr('id'));
    });
    $('.'+selection+' select').each(function(i,e){
        if ($(this).attr('id').substr(-3)!='_op')
            pagefiltertextonchange(selection, form,$(this).attr('id'));
    });
    var autoSubmitOps = JSON.stringify($('.'+selection+' input#'+datefield).data('autoSubmitOps'));
    //console.log('autoSubmitOps = '+autoSubmitOps);
    pagefilterdateonchange(selection, form, datefield,autoSubmitOps);
    enabledatepicker(selection,datefield);
}

function preparemobilepagefilter()
{
    if (mobiledisplay()){
        $('.spagefilter-mobile').remove();//remove first
        /* clone spagefilter form from sidebar*/
        $('.page-content > .page').prepend('<div class="spagefilter-mobile">'+$('.spagefilter').html()+'</div>');
        
        $('.spagefilter-mobile h1 i').removeClass('fa-search');
        $('.spagefilter-mobile h1 i').addClass('fa-caret-down');    
        $('.spagefilter-mobile h1').click(function(){
            $('.spagefilter-mobile .form').toggle();
            if ($('.spagefilter-mobile h1').find('i').hasClass('fa-caret-down')) {
                $('.spagefilter-mobile h1').find('i').removeClass('fa-caret-down');
                $('.spagefilter-mobile h1').find('i').addClass('fa-caret-up');
            }
            else {
                $('.spagefilter-mobile h1').find('i').removeClass('fa-caret-up');
                $('.spagefilter-mobile h1').find('i').addClass('fa-caret-down');
            }
        });

        $(document).ready(function(){
            /* begin change web version form date to other name so not to confused with mobile vesion*/
//            $('.spagefilter-mobile .form form').attr('id','page_filter_mobile_form');
//            var datefield = 'date_web';
//            $('#ui-datepicker-div').remove();
//            $('.spagefilter .form form #date').removeClass('hasDatepicker');
//            $('.spagefilter .form form #date').datepicker( "option", "disabled", true);
//            $('.spagefilter .form form #date').attr('id',datefield);
//            $('.spagefilter .form form #date').attr('name',datefield);
//            $('.spagefilter .form form #date_op').attr('name',datefield+'_op');
//            $('.spagefilter .form form #date_op').attr('id',datefield+'_op');
//            $('.spagefilter .form form #date').off('datepicker');
            $('.spagefilter').remove();
            /* end change */
            refreshpagefilterform('spagefilter-mobile','date');
            $('.spagefilter-mobile input[type="text"]').each(function(i,e){
                if ($(this).val().length>0){
                    $('.spagefilter-mobile h1 i').removeClass('fa-caret-down');
                    $('.spagefilter-mobile h1 i').addClass('fa-caret-up');
                    $('.spagefilter-mobile .form').css({'display':'block'});
                }
            });
        });
    }
 
}

function enabledatepicker(selection,field)
{
    $('.'+selection+' #'+field).datepicker({
        'showAnim':'fold',
        'showOn':'both',
        'changeMonth':true,
        'changeYear':true,
        'dateFormat':'yy-mm-dd',
        'gotoCurrent':true,
        'buttonImage':$('.'+selection+' #date').data('icon'),
        'buttonImageOnly':true,
    });
}
