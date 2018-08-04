<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.analytics.models.Fact');
/**
 * Description of FactDimCurrency
 * Supports additional Currency dimension
 * 
 * @property integer $currency_id currency dimension
 * 
 * @author kwlok
 */
abstract class FactDimCurrency extends Fact
{
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array_merge(parent::rules(),array(
            array('currency_id', 'required'),
            array('currency_id', 'numerical', 'integerOnly'=>true),
            array('currency_id', 'safe', 'on'=>'search'),
        ));
    }
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(),array(
            'currency_id' => Sii::t('sii','Currency'),
        ));
    }  
}
