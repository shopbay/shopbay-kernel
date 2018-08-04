<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.widgets.spagefilter.models.SPageFilterForm');
/**
 * Description of AllOrdersFilterForm
 * Order are market-scoped/global-scoped; 
 * 
 * This form's attributes must match to the search model defined in controller
 * @see SPageIndexController::searchMap
 * @see SPageIndexAction::getSearchModel()
 * @see SPageIndexControllerTrait::assignFilterFormAttributes()
 * 
 * @author kwlok
 */
class AllOrdersFilterForm extends OrderFilterForm
{
    /**
     * Form fields
     * The sequence of fields will decide its display order
     */
    public $shop;
    /**
     * Initializes this model.
     */
    public function init()
    {
        parent::init();
        $this->config = array_merge($this->config, [
            'shop'=>[
                'type'=>SPageFilterForm::TYPE_TEXTFIELD,
                'htmlOptions'=>['maxlength'=>50,'placeholder'=>Sii::t('sii','Enter any shop name')],
            ],
        ]);
    }    
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array_merge(parent::rules(),[
            ['shop', 'length', 'max'=>100],
        ]);
    }
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(),[
            'shop'=>Sii::t('sii','Shop'),
        ]);
    }    
}
