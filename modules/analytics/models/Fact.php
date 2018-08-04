<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of Fact
 * The followings are the common available columns in FACT family tables:
 * @property integer $id
 * @property integer $account_id dimension account
 * @property integer $shop_id dimension shop
 * @property integer $date_id dimension date
 * 
 * @author kwlok
 */
abstract class Fact extends CActiveRecord
{
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('account_id, shop_id, date_id', 'required'),
            array('shop_id, date_id', 'numerical', 'integerOnly'=>true),
            array('id, account_id, shop_id, date_id', 'safe', 'on'=>'search'),
        );
    }
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => Sii::t('sii','ID'),
            'account_id' => Sii::t('sii','Account'),
            'shop_id' => Sii::t('sii','Shop'),
            'date_id' => Sii::t('sii','Date'),
        );
    }    
    /**
     * Retrieve record by standard params below
     * @param type $account_id
     * @param type $shop_id
     * @param array $dims array of dimensions
     * @return \Fact
     */
    public function retrieveByDims($dims) 
    {
        if (!is_array($dims))
            throw new CException('Invalid dimensions');
        
        $criteria = new CDbCriteria();
        foreach ($dims as $key => $value) {
            $criteria->addColumnCondition(array($key=>$value));
        }
        $this->getDbCriteria()->mergeWith($criteria);
        return $this;
    }     
     
}
