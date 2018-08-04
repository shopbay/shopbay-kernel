<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.widgets.spagefilter.models.SPageFilterForm');
/**
 * Description of CustomerFilterForm
 * 
 * This form's attributes must match to the search model defined in controller
 * @see SPageIndexController::searchMap
 * @see SPageIndexAction::getSearchModel()
 * @see SPageIndexControllerTrait::assignFilterFormAttributes()
 * 
 * @author kwlok
 */
class CustomerFilterForm extends SPageFilterForm 
{
    /**
     * Form fields
     * The sequence of fields will decide its display order
     */
    public $customer;
    public $address;
    public $date;
    public $tags;
    public $notes;
    /**
     * Initializes this model.
     */
    public function init()
    {
        $this->config = [
            'customer'=>[
                'type'=>SPageFilterForm::TYPE_TEXTFIELD,
                'htmlOptions'=>['maxlength'=>100,'placeholder'=>Sii::t('sii','Enter any customer name')],
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
                'htmlOptions'=>['maxlength'=>10,'placeholder'=>Sii::t('sii','yyyy-mm-dd'),'class'=>'date-field'],
            ],
            'address'=>[
                'type'=>SPageFilterForm::TYPE_TEXTFIELD,
                'htmlOptions'=>['maxlength'=>100,'placeholder'=>Sii::t('sii','Enter any address')],
            ],
            'tags'=>[
                'type'=>SPageFilterForm::TYPE_TEXTAREA,
                'htmlOptions'=>['maxlength'=>500,'placeholder'=>Sii::t('sii','Enter any tags')],
            ],
            'notes'=>[
                'type'=>SPageFilterForm::TYPE_TEXTAREA,
                'htmlOptions'=>['maxlength'=>500,'placeholder'=>Sii::t('sii','Enter any notes')],
            ],
        ];
    }    
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            ['customer, address', 'length', 'max'=>100],
            ['tags, notes', 'length', 'max'=>500],
        ];
    }
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return [
            'customer'=>Sii::t('sii','Customer Name'),
            'date'=>Sii::t('sii','Created At'),
            'notes'=>Sii::t('sii','Notes'),
            'tags'=>Sii::t('sii','Tags'),
            'address'=>Sii::t('sii','Address'),
        ];
    }    
}
