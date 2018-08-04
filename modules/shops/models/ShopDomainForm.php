<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.shops.models.BaseShopSettingsForm');
/**
 * Description of ShopDomainForm
 *
 * @author kwlok
 */
class ShopDomainForm extends BaseShopSettingsForm 
{
    protected $blacklistRule = JsonSettingsForm::RULE_EQUALS;//or set to "contains" 
    public $customDomain;//this is the submain under shopbay
    /**
     * Declares the validation rules.
     */
    public function rules()
    {
        return array_merge(parent::rules(),[
            ['customDomain', 'length', 'min'=>8, 'max'=>50],
            //only alphanumeric chars, lower case, and dash "-"
            ['customDomain', 'match', 'pattern'=>'/^[a-z0-9-]+$/', 'message'=>Sii::t('sii','Custom domain accepts only lower case letters, digits or hypen.')],
            //scan through existing subdomain used
            ['customDomain', 'ruleSubdomain'],
            //scan through reserved list
            ['customDomain', 'ruleReservedlist'],
        ]);
    }
    /**
     * Validation rules for taken subdomains
     */
    public function ruleSubdomain($attribute,$params)
    {
        if (!empty($this->customDomain)){
            $settingModelClass = $this->ownerSettingClass;
            $settingModel = $settingModelClass::model()->customDomain($this->customDomain)->find();
            if ($settingModel!=null && $settingModel->{$this->ownerAttribute} != $this->{$this->ownerAttribute})
                $this->addError($attribute, Sii::t('sii','{model} domain "{domain}" is already taken. Please try others.',['{domain}'=>$this->customDomain,'{owner}'=>$this->owner->displayName()]));
        }
    }    
    /**
     * Validation rules for reserved subdomains 
     */ 
    public function ruleReservedlist($attribute,$params)
    {
        $reservedList = include $this->getReservedDomainDatasource();
        $startsWith = function ($haystack, $needle) {
            $length = strlen($needle);
            return (substr($haystack, 0, $length) === $needle);
        };
        foreach ($reservedList as $alphabet => $words) {
            if (!empty($words)){
                foreach ($words as $word) {
                    if ($this->blacklistRule==JsonSettingsForm::RULE_CONTAINS){
                        if (preg_match('/'.$word.'/i', $this->$attribute)){
                            $this->addError($attribute, Sii::t('sii','The word "{word}" is reserved. Please try others.',['{word}'=>$word]));
                            break;
                        }
                    }
                    else {//default rule is "equals"
                        //scan through suffix "-" and prefix "-" also
                        if ($word==$this->$attribute || ($word.'-')==$this->$attribute || ('-'.$word)==$this->$attribute){
                            $this->addError($attribute, Sii::t('sii','The word "{word}" is reserved. Please try others.',['{word}'=>$word]));
                            break;
                        }
                        //scan through if start with 'reserved domain'
                        if ($startsWith($this->$attribute,$word)){
                            $this->addError($attribute, Sii::t('sii','Domain cannot start with the word "{word}" as it is reserved. Please try others.',['{word}'=>$word]));
                            break;
                        }
                    }
                }
            }
        }
    }
    
    public function getReservedDomainDatasource()
    {
        $filepath = Yii::getPathOfAlias('common.data');
        return $filepath.DIRECTORY_SEPARATOR.'reserved_subdomains.php';
    }
}
