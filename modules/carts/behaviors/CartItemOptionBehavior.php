<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.models.ProductAttributeOption');
/**
 * Description of CartItemOptionBehavior
 *
 * @author kwlok
 */
class CartItemOptionBehavior extends CBehavior 
{
    public $optionsAttribute = 'options';
    public $optionFeeAttribute = 'option_fee';
    /**
     * Assign option values in following data format:
     * array(
     *     '<attr name>'=>'<option name>|<surcharge>(optional)',
     *     '<attr name>'=>'<option name>|<surcharge>(optional)',
     *     ...
     * )
     * 
     * @see ProductAttributeOption::encodeOption() for how option values is formatted 
     * encoding: <attr code> + <attr name> + <option code> + <option name> + <surcharge> (optional)
     * @param array $options
     */
    public function assignOptions($options)
    {
        $this->getOwner()->{$this->optionsAttribute} = [];//reset options
        foreach ($options as $key => $value){
            $opt = explode(Helper::PIPE_SEPARATOR, $value);
            //$opt[1] is attribute name (multi-lang)
            //$opt[3] is option name (multi-lang)
            //$opt[4] is surcharge
            if (isset($opt[4])){
                $this->getOwner()->{$this->optionsAttribute}[$opt[1]] = $opt[3].Helper::PIPE_SEPARATOR.$opt[4];
                $this->getOwner()->{$this->optionFeeAttribute} += $opt[4];
            }
            else {
                $this->getOwner()->{$this->optionsAttribute}[$opt[1]] = $opt[3];
            }
        }
    }     
    /**
     * Parse option values 
     * array(
     *     '<attr name in locale>'=>'<option name locale with surcharge>',
     *     '<attr name in locale>'=>'<option name locale with surcharge>',
     *     ...
     * )
     * 
     * Parsing following data structure:
     * array(
     *     '<attr name>'=>'<option name>|<surcharge>(optional)',
     *     '<attr name>'=>'<option name>|<surcharge>(optional)',
     *     ...
     * )
     * @see assignOptions() 
     * @return type
     */
    public function parseOptions($locale=null,$json=false)
    {
        $workingOptions = $this->getOwner()->{$this->optionsAttribute};
        
        if ($workingOptions==null)
            return array();
        else {
            if ($json)
                $workingOptions = json_decode($workingOptions,true);
                
            if (!isset($locale))
                $locale = $this->getOwner()->getLocale();
            
            $optMap = new CMap();
            foreach ($workingOptions as $key => $value) {
                $attr = $this->getOwner()->getLanguageValueObject(base64_decode($key));
                $opt = explode(Helper::PIPE_SEPARATOR, $value);
                $optName = $this->getOwner()->getLanguageValueObject(base64_decode($opt[0]));
                if (isset($opt[1])){//surcharge is $opt[1]
                    $optSurcharge = ProductAttributeOption::getSurchargeTextTemplate($opt[1], $this->getOwner());
                }
                if (is_object($attr) && is_object($optName))
                    $optMap->add($attr->$locale,$optName->$locale.(isset($optSurcharge)?' '.$optSurcharge:''));
                else
                    logError(__METHOD__." option $key or $opt[0] not found",$this->getOwner()->attributes);
            }
            //logTrace(__METHOD__,$optMap->toArray());
            return $optMap->toArray();
        }
    }
    
    public function printOptions()
    {
        logTrace(__METHOD__,$this->getOwner()->{$this->optionsAttribute});
    }
}
