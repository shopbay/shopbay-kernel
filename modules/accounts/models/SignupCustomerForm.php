<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.accounts.models.SignupForm');
Yii::import('common.modules.accounts.models.AccountTypeTrait');
Yii::import('common.modules.customers.models.CustomerFormTrait');
/**
 * Description of SignupCustomerForm
 *
 * @author kwlok
 */
class SignupCustomerForm extends SignupForm
{
    use AccountTypeTrait, CustomerFormTrait;
    
    public $order_no;
    /**
     * Constructor.
     * @param string $scenario name of the scenario that this model is used in.
     * See {@link CModel::scenario} on how scenario is used by models.
     * @see getScenario
     */
    public function __construct($scenario='')
    {
        parent::__construct('FullForm');
        $this->initAddress();
    }    
    /**
     * Declares the validation rules.
     * The rules state that username and password are required,
     * and password needs to be authenticated.
     */
    public function rules()
    {
        return array_merge(parent::rules(),$this->formRules());
    }
    /**
     * Declares attribute labels.
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(),$this->formAttributeLabels());
    }
    
}
