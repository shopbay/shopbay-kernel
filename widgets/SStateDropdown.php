<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of SStateDropdown
 *
 * @author kwlok
 */
class SStateDropdown extends CWidget
{
    public $stateGetActionUrl;
    public $countryFieldId;
    public $stateFieldId;
    public $useChosen = true;//if dropdown is using Chosen library
    public $countryChosenSelectClass = 'chzn-select-country';
    public $stateChosenSelectClass = 'chzn-select-state';
    
    public function init()
    {
        parent::init();
    }
    /**
     * Renders the dropdown view.
     */
    public function run()
    {
        $chosen = '';
        $script = <<<EOJS
$('#$this->countryFieldId').change(function(){
    $('#$this->stateFieldId').find('option').remove().end();      
    $.get('$this->stateGetActionUrl'+'?country='+$('#$this->countryFieldId').val(), function( data ) {
        $.each(data, function(key,value) {
            $('#$this->stateFieldId').append($("<option />").val(key).text(value));
        });
        if (data.length===0){
            $('#$this->stateFieldId').append($("<option />").val('').text(''));
        }
        $chosen
    })
    .error(function(XHR) {
        alert(XHR.status+' '+XHR.statusText+': ' + XHR.responseText);
    });        
});
EOJS;
        //if Chosen is used
        if ($this->useChosen){
            $chosen = <<<EOJS
$('.$this->stateChosenSelectClass').trigger("liszt:updated");
EOJS;
            $setupChosen = <<<EOJS
$('.$this->countryChosenSelectClass').chosen();
$('.$this->stateChosenSelectClass').chosen();
EOJS;
            $script = $setupChosen.$script;//prefix script
        }
        //run script
        Helper::registerJs($script);        
    }    
}
