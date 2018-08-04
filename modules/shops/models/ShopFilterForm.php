<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.widgets.spagefilter.models.SPageFilterForm');
/**
 * Description of ShopFilterForm
 * 
 * This form's attributes must match to the search model defined in controller
 * @see SPageIndexController::searchMap
 * @see SPageIndexAction::getSearchModel()
 * @see SPageIndexControllerTrait::assignFilterFormAttributes()
 * 
 * @author kwlok
 */
class ShopFilterForm extends SPageFilterForm 
{
    CONST STATUS_FLAG = 'SHOP;';//shop status starting string; Refer to Process model
    /**
     * Form fields
     * The sequence of fields will decide its display order
     */
    public $shop;
    public $date;
    public $shipping;
    public $payment_method;
    public $timezone;
    public $currency;
    public $status;
    /**
     * Initializes this model.
     */
    public function init()
    {
        $this->config = [
            'shop'=>[
                'type'=>SPageFilterForm::TYPE_TEXTFIELD,
                'htmlOptions'=>['maxlength'=>100,'placeholder'=>Sii::t('sii','Enter any shop name')],
            ],
            'date'=>[
                'type'=>SPageFilterForm::TYPE_DATE,
                'ops'=>[
                    SPageFilterForm::OP_EQUAL,
                    SPageFilterForm::OP_NOT_EQUAL,
                    SPageFilterForm::OP_GREATER_THAN,
                    SPageFilterForm::OP_GREATER_THAN_OR_EQUAL,
                    SPageFilterForm::OP_LESS_THAN,
                    SPageFilterForm::OP_LESS_THAN_OR_EQUAL,
                ],
                'htmlOptions'=>['maxlength'=>10,'class'=>'date-field','placeholder'=>Sii::t('sii','yyyy-mm-dd')],
            ],
            'shipping'=>[
                'type'=>SPageFilterForm::TYPE_TEXTFIELD,
                'htmlOptions'=>['maxlength'=>100,'placeholder'=>Sii::t('sii','Enter any shipping name')],
            ],
            'payment_method'=>[
                'type'=>SPageFilterForm::TYPE_TEXTFIELD,
                'htmlOptions'=>['maxlength'=>100,'placeholder'=>Sii::t('sii','Enter any payment method')],
            ],
            'timezone'=>[
                'type'=>SPageFilterForm::TYPE_DROPDOWNLIST,
                'selectOptions'=>array_merge([''=>Sii::t('sii','Select Time Zone')],SLocale::getTimeZones()),
                'htmlOptions'=>['placeholder'=>Sii::t('sii','Select Time Zone')],
            ],
            'currency'=>[
                'type'=>SPageFilterForm::TYPE_DROPDOWNLIST,
                'selectOptions'=>array_merge([''=>Sii::t('sii','Select Currency')],SLocale::getCurrencies()),
                'htmlOptions'=>['placeholder'=>Sii::t('sii','Select Currency')],
            ],
            'status'=>[
                'type'=>SPageFilterForm::TYPE_DROPDOWNLIST,
                'selectOptions'=>array_merge([''=>Sii::t('sii','Select Status')],self::getAllStatus()),
                'htmlOptions'=>['placeholder'=>Sii::t('sii','Select Status')],
            ],            
        ];
    }    
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            ['shop, shipping, payment_method', 'length', 'max'=>100],
            ['currency, timezone', 'length', 'max'=>20],
            ['currency', 'length', 'max'=>3],
            ['status', 'length', 'max'=>20],
            ['status', 'ruleStatus'],
        ];
    }
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return [
            'shop'=>Sii::t('sii','Shop Name'),
            'date'=>Sii::t('sii','Created At'),
            'shipping'=>Sii::t('sii','Shipping Option'),
            'payment_method'=>Sii::t('sii','Payment Method'),
            'timezone'=>Sii::t('sii','Time Zone'),
            'currency'=>Sii::t('sii','Currency'),
            'status'=>Sii::t('sii','Status'),
        ];
    }    
    /**
     * @inheritdoc
     */
    public static function getAllStatus($status=[])
    {
        return parent::getAllStatus([
            self::STATUS_ONLINE,
            self::STATUS_OFFLINE,
        ]);
    }    
}
