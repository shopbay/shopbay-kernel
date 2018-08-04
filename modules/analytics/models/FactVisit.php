<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.analytics.models.Fact');
/**
 * This is the model class for table "s_fact_sale".
 *
 * The followings are the available columns in table 's_fact_sale':
 * @property integer $id
 * @property integer $account_id
 * @property integer $shop_id
 * @property integer $date_id
 * @property integer $visitor (unique visitor count; For login user, this will be account_id; For guest, this will be IP address)
 * @property integer $pageview
 * @property integer $addcart how many times visitor reach add to cart page
 * @property integer $checkout how many times visitor reach checkout page
 * @property integer $purchased how many times visitor reach confirmed order page
 *
 * @author kwlok
 */
class FactVisit extends Fact
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
        return 's_fact_visit';
    }
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array_merge(parent::rules(),array(
            array('pageview, addcart, checkout, purchased', 'numerical', 'integerOnly'=>true),
            array('pageview, addcart, checkout, purchased', 'length', 'max'=>11),
            array('visitor', 'length', 'max'=>20),
            array('pageview, visitor, addcart, checkout, purchased', 'safe', 'on'=>'search'),
        ));
    }
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(),array(
            'visitor' => Sii::t('sii','Visitors'),
            'pageview' => Sii::t('sii','Pageview'),
            'addcart' => Sii::t('sii','AddCart'),
            'checkout' => Sii::t('sii','Checkout'),
            'purchased' => Sii::t('sii','Purchased'),
        ));
    }
}
