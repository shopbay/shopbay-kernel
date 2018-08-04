<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.widgets.spagefilter.models.SPageFilterForm');
/**
 * Description of PageFilterForm
 * 
 * This form's attributes must match to the search model defined in controller
 * @see SPageIndexController::searchMap
 * @see SPageIndexAction::getSearchModel()
 * @see SPageIndexControllerTrait::assignFilterFormAttributes()
 * 
 * @author kwlok
 */
class PageFilterForm extends SPageFilterForm 
{
    CONST STATUS_FLAG = 'PG;';//page status starting string; Refer to Process model
    /**
     * Form fields
     * The sequence of fields will decide its display order
     */
    public $page;
    public $date;
    public $status;
    /**
     * Initializes this model.
     */
    public function init()
    {
        $this->config = [
            'page'=>[
                'type'=>SPageFilterForm::TYPE_TEXTFIELD,
                'htmlOptions'=>['maxlength'=>100,'placeholder'=>Sii::t('sii','Enter any page name')],
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
            ['page', 'length', 'max'=>100],
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
            'page'=>Sii::t('sii','Page Name'),
            'date'=>Sii::t('sii','Created At'),
            'status'=>Sii::t('sii','Status'),
        ];
    }    
    
    public static function getAllStatus($status=[])
    {
        return parent::getAllStatus([
            self::STATUS_ONLINE,
            self::STATUS_OFFLINE,
        ]);
    }
    
}
