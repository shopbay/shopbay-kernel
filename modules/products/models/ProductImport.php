<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * This is the model class for table "s_product_import".
 *
 * The followings are the available columns in table 's_product_import':
 * @property integer $id
 * @property integer $account_id
 * @property integer $shop_id
 * @property string $summary
 * @property integer $create_by
 * @property integer $create_time
 * 
 * @author kwlok
 */
class ProductImport extends SActiveRecord
{
    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Brand the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    /**
     * Model display name 
     * @param $mode singular or plural, if the language supports, e.g. english
     * @return string the model display name
     */
    public function displayName($mode=Helper::SINGULAR)
    {
        return Sii::t('sii','Product|Products',array($mode));
    }    
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 's_product_import';
    }
    /**
     * Behaviors for this model
     */
    public function behaviors()
    {
        return array(
            'timestamp' => array(
                'class'=>'common.components.behaviors.TimestampBehavior',
            ),
            'account' => array(
                'class'=>'common.components.behaviors.AccountBehavior',
            ),
            'merchant' => array(
                'class'=>'common.components.behaviors.MerchantBehavior',
            ),  
            'locale' => array(
                'class'=>'common.components.behaviors.LocaleBehavior',
            ),                      
            'activity' => array(
                'class'=>'common.modules.activities.behaviors.ActivityBehavior',
                'buttonIcon'=>array(
                    'enable'=>true,
                ),
            ),
            'attachment' => array(
                'class'=>'common.modules.media.behaviors.AttachmentBehavior',
                'stateVariable'=>SActiveSession::ATTACHMENT,
                'useOwner'=>true,
            ),     
        );
    }    
    /**
     * Validation rules for model attributes
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('account_id, shop_id, summary', 'required'),
            array('account_id, shop_id', 'numerical', 'integerOnly'=>true),
            array('summary', 'length', 'max'=>1000),
            array('id, account_id, shop_id, summary, create_time, update_time', 'safe', 'on'=>'search'),
        );
    }
    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
            'account' => array(self::BELONGS_TO, 'Account', 'account_id'),
            'shop' => array(self::BELONGS_TO, 'Shop', 'shop_id'),
        );
    }
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'account_id' => Sii::t('sii','Account'),
            'shop_id' => Sii::t('sii','Shop'),
            'summary' => Sii::t('sii','Summary'),
            'create_time' => Sii::t('sii','Upload Time'),
            'update_time' => Sii::t('sii','Update Time'),
            //custom labels
            'total_count' => Sii::t('sii','Total Uploaded'),
            'uploaded_file' => Sii::t('sii','Uploaded File'),
        );
    }
    
    public function getAttachment()
    {
        $criteria = new CDbCriteria();
        $criteria->addColumnCondition(array(
            'obj_type'=>$this->tableName(),
            'obj_id'=>$this->id,
        ));
        return Attachment::model()->find($criteria);
    }
    /**
     * @return array summary as array
     */
    public function getSummaryData()
    {
        return json_decode($this->summary,true);
    }
    
    public function getCount()
    {
        return $this->summaryData['total_count'];
    }
    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search()
    {
        $criteria=new CDbCriteria;
        $criteria->compare('id',$this->id);
        $criteria->compare('account_id',$this->account_id);
        $criteria->compare('shop_id',$this->shop_id);
        $criteria->compare('summary',$this->summary,true);
        $criteria->compare('create_time',$this->create_time);
        $criteria->compare('update_time',$this->update_time);

        $criteria->mergeWith($this->mine()->getDbCriteria());

        logTrace(__METHOD__.' criteria',$criteria);

        return new CActiveDataProvider($this,
                        array(
                            'criteria'=>$criteria,
                            'pagination'=>array('pageSize'=>Config::getSystemSetting('record_per_page')),
                    ));
    }
    /**
     * Url to view this model
     * @return string url
     */
    public function getViewUrl()
    {
        return url('product/management/import/view/'.$this->id);
    }    

}
