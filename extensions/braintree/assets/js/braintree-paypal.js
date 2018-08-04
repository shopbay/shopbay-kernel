function loadbraintreepaypal(formId,buttonId,methodId,amount) {
    //console.log('buttonId', buttonId);
    //console.log('methodId', methodId);
    //console.log('amount', amount);
    var containerId =  $('#braintree_paypal_config').data('container');
    console.log('containerId', containerId);
    //clear container first
    $('#'+containerId).html('');
    var newButtonId = buttonId+'_paypal';
    resetformandbutton(formId,buttonId,newButtonId);
    var paypalsetup = {
        container:containerId,
        displayName:$('#braintree_paypal_config').data('shopName'),
        singleUse:true,
        amount:amount,
        currency:$('#braintree_paypal_config').data('currency'),
        enableShippingAddress:'true',
        shippingAddressOverride: $('#braintree_paypal_config').data('shippingAddress'),
    };
    //console.log('paypalsetup',paypalsetup);
    braintree.setup(
        $('#braintree_paypal_config').data('clientToken'), 
        $('#braintree_paypal_config').data('type'), 
        { 
            paypal:paypalsetup,
            onError:function(error){
                alert(error.type+': '+error.message);
                $(".page-loader").hide();
            },
            onReady:function(integration){
                $('#method-'+methodId).show();
                $('#method-'+methodId).addClass('braintree-loaded');
                $(".page-loader").hide();
            },
            onPaymentMethodReceived: function (payload) {
                //console.log('payload',payload);
                $('#'+containerId).append('<input type="hidden" name="payment_method_nonce" value="'+payload.nonce+'">');
                $('#'+newButtonId).button({disabled:false});
            },
        }
    );  
    
}

function resetformandbutton(formId,buttonId,newButtonId) {
    //clone buttonId to a new "button" button, and remove buttonId, assign new buttonId
    var buttonText  = $('#'+buttonId+' span').text();
    if (buttonText.length==0)
        buttonText  = $('#'+buttonId).val();//try cature again not looking for span
    var buttonStyle = $('#'+buttonId).attr('style');
    var buttonClass = $('#'+buttonId).attr('class');
    $('#'+buttonId).remove();
    $('#'+formId+' #buttons').append('<input type="button" id="'+newButtonId+'" class="'+buttonClass+'" style="'+buttonStyle+'" value="'+buttonText+'">');
    $('#'+newButtonId).button({type:'button',disabled:true}).click(function(){
        $('#page_modal .page-loader').show();
        proceed('SelectPaymentMethod');
    });
}