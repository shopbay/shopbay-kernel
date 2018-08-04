<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * This is the model class for table "s_dim_currency".
 *
 * The followings are the available columns in table 's_dim_currency':
 * @property integer $id
 * @property string $currency
 *
 * @author kwlok
 */
class DimCurrency extends CActiveRecord
{
    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return Metric the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    } 
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 's_dim_currency';
    }
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('currency', 'required'),
            array('currency', 'length', 'max'=>3),
            array('id, currency', 'safe', 'on'=>'search'),
        );
    }
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => Sii::t('sii','ID'),
            'currency' => Sii::t('sii','Currency'),
        );
    }
    /**
     * Retrieve DimCurrency record; Will auto create a new one when not found
     * 
     * @param string $currency
     * @return \DimCurrency
     * @throws CException
     */
    public function retrieve($currency) 
    {
        $model = DimCurrency::model()->find('currency = "'.$currency.'"');
        if ($model===null){
            $model = new DimCurrency();
            $model->currency = $currency;
            $model->save();
            logInfo(__METHOD__.' new currency dimension created', $model->getAttributes());
        }
        return $model;
    }  
    
}
