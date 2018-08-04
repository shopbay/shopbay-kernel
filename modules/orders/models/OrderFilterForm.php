<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.widgets.spagefilter.models.SPageFilterForm');
/**
 * Description of OrderFilterForm
 * Orders are shop-scoped; 
 * 
 * This form's attributes must match to the search model defined in controller
 * @see SPageIndexController::searchMap
 * @see SPageIndexAction::getSearchModel()
 * @see SPageIndexControllerTrait::assignFilterFormAttributes()
 * 
 * @author kwlok
 */
class OrderFilterForm extends SPageFilterForm 
{
    /**
     * Form fields
     * The sequence of fields will decide its display order
     */
    public $order_no;
    public $price;
    public $date;
    public $items;
    public $shipping;
    public $payment_method;
    /**
     * Initializes this model.
     */
    public function init()
    {
        $this->config = [
            'order_no'=>[
                'type'=>SPageFilterForm::TYPE_TEXTFIELD,
                'htmlOptions'=>['maxlength'=>20,'placeholder'=>Sii::t('sii','Enter any order no')],
            ],
            'price'=>[
                'type'=>SPageFilterForm::TYPE_TEXTFIELD,
                'ops'=>[
                    SPageFilterForm::OP_EQUAL,
                    SPageFilterForm::OP_NOT_EQUAL,
                    SPageFilterForm::OP_GREATER_THAN,
                    SPageFilterForm::OP_GREATER_THAN_OR_EQUAL,
                    SPageFilterForm::OP_LESS_THAN,
                    SPageFilterForm::OP_LESS_THAN_OR_EQUAL,
                ],
                'htmlOptions'=>['maxlength'=>10,'class'=>'numeric-field','placeholder'=>Sii::t('sii','Numeric only')],
            ],
            'date'=>[
                'type'=>SPageFilterForm::TYPE_DATE,
                'ops'=>[
                    SPageFilterForm::OP_LAST_24_HOURS,
                    SPageFilterForm::OP_LAST_7_DAYS,
                    SPageFilterForm::OP_LAST_30_DAYS,
                    SPageFilterForm::OP_LAST_90_DAYS,
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
                'htmlOptions'=>['maxlength'=>50,'placeholder'=>Sii::t('sii','Enter any shipping name')],
            ],
            'payment_method'=>[
                'type'=>SPageFilterForm::TYPE_TEXTFIELD,
                'htmlOptions'=>['maxlength'=>50,'placeholder'=>Sii::t('sii','Enter any payment method')],
            ],
            'items'=>[
                'type'=>SPageFilterForm::TYPE_TEXTFIELD,
                'htmlOptions'=>['maxlength'=>100,'placeholder'=>Sii::t('sii','Enter any product name')],
            ],
        ];
    }    
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            ['order_no', 'length', 'max'=>20],   
            ['price', 'length', 'max'=>10],   
            ['price', 'type', 'type'=>'float'],
            ['payment_method, shipping, items', 'length', 'max'=>100],
        ];
    }
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return [
            'date'=>Sii::t('sii','Purchase Date'),
            'order_no'=>Sii::t('sii','Order No'),
            'price'=>Sii::t('sii','Order Total'),
            'shipping'=>Sii::t('sii','Shipping Option'),
            'payment_method'=>Sii::t('sii','Payment Method'),
            'items'=>Sii::t('sii','Products'),
        ];
    }    
}
