<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.widgets.spagefilter.models.SPageFilterForm');
/**
 * Description of ItemFilterForm
 * 
 * This form's attributes must match to the search model defined in controller
 * @see SPageIndexController::searchMap
 * @see SPageIndexAction::getSearchModel()
 * @see SPageIndexControllerTrait::assignFilterFormAttributes()
 * 
 * @author kwlok
 */
class ItemFilterForm extends SPageFilterForm 
{
    /**
     * Form fields
     * The sequence of fields will decide its display order
     */
    public $item;
    public $unit_price;
    public $total_price;
    public $date;
    public $order_no;
    public $shipping_no;
    /**
     * Initializes this model.
     */
    public function init()
    {
        $this->config = [
            'order_no'=>[
                'type'=>SPageFilterForm::TYPE_TEXTFIELD,
                'htmlOptions'=>['maxlength'=>20,'placeholder'=>Sii::t('sii','Enter any purchase order no')],
            ],
            'shipping_no'=>[
                'type'=>SPageFilterForm::TYPE_TEXTFIELD,
                'htmlOptions'=>['maxlength'=>20,'placeholder'=>Sii::t('sii','Enter any shipping order no')],
            ],
            'unit_price'=>[
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
            'total_price'=>[
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
            'item'=>[
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
            ['order_no, shipping_no', 'length', 'max'=>20],   
            ['unit_price, total_price', 'length', 'max'=>10],   
            ['unit_price, total_price', 'type', 'type'=>'float'],
            ['item', 'length', 'max'=>100],
        ];
    }
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return [
            'item'=>Sii::t('sii','Item Name'),
            'date'=>Sii::t('sii','Purchase Date'),
            'order_no'=>Sii::t('sii','Purchase Order No'),
            'shipping_no'=>Sii::t('sii','Shipping Order No'),
            'unit_price'=>Sii::t('sii','Unit Price'),
            'total_price'=>Sii::t('sii','Item Total'),
        ];
    }    
}
