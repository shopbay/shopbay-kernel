function loadbraintreecustom(containerId,buttonId,amount,successCallback){
    var hostedFieldsConfig = $('#braintree_config').data('hostedFields');
    var onFieldEventFn = function (event) {
        if (event.type === "fieldStateChange") {
            //console.log('card is '+ event.isValid); // true|false
            //console.log('event', event);
            //console.log('event.target', event.target);
            $('#card_number').css({'background-image':''});
            $('#card_number').removeClass('visa master-card discover jcb american-expres diners-club maestro');
            //Handle a change in validation or card type
            if (event.card) {
                //console.log('card', event.card);
                $('#card_number').addClass(event.card.type);
                var icon = 'url("'+$('.credit-card-icons').data('baseUrl')+'/'+event.card.type+'.png'+'")';
                console.log('credit icon',icon);
                $('#card_number').css('background-image',icon);
                //If not enough information is available, or if there is invalid data
                console.log(event.card.type);//visa|master-card|american-express|diners-club|discover|jcb|unionpay|maestro
                if (($('#card_number.braintree-hosted-fields-valid').length>0) && 
                    ($('#cvv.braintree-hosted-fields-valid').length>0) && 
                    ($('#expiration_date.braintree-hosted-fields-valid').length>0)){
                   $('#'+buttonId).button({disabled:false});
                }
                else {
                    $('#'+buttonId).button({disabled:true});
                }
            }
        }
    };
    //[4]define onFieldEvent handler
    hostedFieldsConfig['onFieldEvent'] = onFieldEventFn;
    //console.log('hostedFieldsConfig', hostedFieldsConfig);
    //[5]setup braintree
    braintree.setup(
        $('#braintree_config').data('clientToken'), 
        $('#braintree_config').data('type'), 
        { 
            id:containerId,
            hostedFields:hostedFieldsConfig,
            onReady:function(integration){
                //insert amount as a hidden field
                $('#'+containerId).append('<input type="hidden" name="order_amount" value="'+amount+'">');
                $(".page-loader").hide();
            },
            onError:function(error){
                alert(error.type+': '+error.message);
                $(".page-loader").hide();
            },
            onPaymentMethodReceived: function (payload) {
                //console.log('payload',payload);
                $('#'+containerId).append('<input type="hidden" name="payment_method_nonce" value="'+payload.nonce+'">');
                $('#'+containerId).append('<input type="hidden" name="cc_type" value="'+payload.details.cardType+'">');
                $('#'+containerId).append('<input type="hidden" name="cc_lastTwo" value="'+payload.details.lastTwo+'">');
                $('#'+containerId).append('<input type="hidden" name="cc_icon_base_url" value="'+$('.credit-card-icons').data('baseUrl')+'">');
                successCallback();
            },
        }
    );  
    
}

function loadbraintree(containerId,buttonId,methodId,amount) {
    //container id is form id
    //console.log('containerId', containerId);
    //console.log('buttonId', buttonId);
    //console.log('methodId', methodId);
    //console.log('amount', amount);
    if ($('#braintree_config').data('type')=='dropin'){
        braintree.setup(
            $('#braintree_config').data('clientToken'), 
            $('#braintree_config').data('type'), 
            {container:containerId}
        );        
    }
    else {
        //[1]change form action url
        $('#'+containerId).attr({action:$('#braintree_config').data('formUrl')});
        //[2]clone buttonId to a new "submit" button, and remove buttonId
        var buttonText  = $('#'+buttonId).text();
        if (buttonText.length==0)
            buttonText  = $('#'+buttonId).val();//try cature again not looking for span
        if (buttonText.length==0)
            buttonText  = $('#'+buttonId).html();//try cature again not looking for tag

        var buttonStyle = $('#'+buttonId).attr('style');
        var buttonClass = $('#'+buttonId).attr('class');
        $('#'+buttonId).remove();
        var newButton = '<button type="submit" id="'+buttonId+'" class="'+buttonClass+'" style="'+buttonStyle+'">'+buttonText+'</button>';
        $('#'+containerId+' .buttons').append(newButton);
        $('#'+buttonId).button({type:'submit',disabled:true}).click(function(){$('#page_modal .page-loader').show();});
        //[3]insert amount as a hidden field
        var hostedFieldsConfig = $('#braintree_config').data('hostedFields');
        var onFieldEventFn = function (event) {
            if (event.type === "fieldStateChange") {
                //console.log('card is '+ event.isValid); // true|false
                //console.log('event', event);
                //console.log('event.target', event.target);
                $('#card_number').css({'background-image':''});
                $('#card_number').removeClass('visa master-card discover jcb american-expres diners-club maestro');
                //Handle a change in validation or card type
                if (event.card) {
                    //console.log('card', event.card);
                    $('#card_number').addClass(event.card.type);
                    var icon = 'url("'+$('.credit-card-icons').data('baseUrl')+'/'+event.card.type+'.png'+'")';
                    console.log('credit icon',icon);
                    $('#card_number').css('background-image',icon);
                    //If not enough information is available, or if there is invalid data
                    console.log(event.card.type);//visa|master-card|american-express|diners-club|discover|jcb|unionpay|maestro
                    if (($('#card_number.braintree-hosted-fields-valid').length>0) && 
                        ($('#cvv.braintree-hosted-fields-valid').length>0) && 
                        ($('#expiration_date.braintree-hosted-fields-valid').length>0)){
                       $('#'+buttonId).button({disabled:false});
                    }
                    else {
                        $('#'+buttonId).button({disabled:true});
                    }
                }
            }
        };
        //[4]define onFieldEvent handler
        hostedFieldsConfig['onFieldEvent'] = onFieldEventFn;
        //console.log('hostedFieldsConfig', hostedFieldsConfig);
        //[5]setup braintree
        braintree.setup(
            $('#braintree_config').data('clientToken'), 
            $('#braintree_config').data('type'), 
            { 
                id:containerId,
                hostedFields:hostedFieldsConfig,
                onReady:function(integration){
                    $('#'+containerId).append('<input type="hidden" name="order_amount" value="'+amount+'">');
                    $('#method-'+methodId).show();
                    $('#method-'+methodId).addClass('braintree-loaded');
                    $(".page-loader").hide();
                },
                onError:function(error){
                    alert(error.type+': '+error.message);
                    $(".page-loader").hide();
                },
                onPaymentMethodReceived: function (payload) {
                    //console.log('payload',payload);
                    $('#'+containerId).append('<input type="hidden" name="payment_method_nonce" value="'+payload.nonce+'">');
                    $('#'+containerId).append('<input type="hidden" name="cc_type" value="'+payload.details.cardType+'">');
                    $('#'+containerId).append('<input type="hidden" name="cc_lastTwo" value="'+payload.details.lastTwo+'">');
                    $('#'+containerId).append('<input type="hidden" name="cc_icon_base_url" value="'+$('.credit-card-icons').data('baseUrl')+'">');
                    proceed('SelectPaymentMethod');
                },
            }
        );  

        if ($('#method-'+methodId+'.braintree-loaded').length>0){
            $('#method-'+methodId).show();
            $(".page-loader").hide();
        }
    }
}

function processbraintree(containerId)
{
    $('#'+containerId).submit();
}