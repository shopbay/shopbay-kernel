<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * This is the model class for table "s_like".
 *
 * The followings are the available columns in table 's_like':
 * @property integer $id
 * @property string $account_id
 * @property string $obj_type
 * @property integer $obj_id
 * @property string $obj_name
 * @property string $obj_url
 * @property string $obj_pic_url
 * @property string $obj_src_id
 * @property string $status
 * @property integer $create_time
 * @property integer $update_time
 *
 * @author kwlok
 */
class Like extends SActiveRecord 
{
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 's_like';
    }
    /**
     * Model display name 
     * @param $mode singular or plural, if the language supports, e.g. english
     * @return string the model display name
     */
    public function displayName($mode=Helper::SINGULAR)
    {
        return Sii::t('sii','Like|Likes',array($mode));
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
            'accountbehavior' => array(
                'class'=>'common.components.behaviors.AccountBehavior',
            ),
            'accountobjectbehavior' => array(
                'class'=>'common.components.behaviors.AccountObjectBehavior',
            ),
            'activity' => array(
                'class'=>'common.modules.activities.behaviors.ActivityBehavior',
                'descriptionAttribute'=>'obj_name',
                'iconUrlSource'=>'object',
            ),
            'locale' => array(
                'class'=>'common.components.behaviors.LocaleBehavior',
                'ownerParent'=>'account',
                'localeAttribute'=>'profileLocale',
            ),             
            'multilang' => array(
                'class'=>'common.components.behaviors.LanguageBehavior',
            ),                      
        );
    }    
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('account_id, obj_type, obj_id, obj_name, obj_url, obj_pic_url', 'required'),
            array('obj_id, obj_src_id', 'numerical', 'integerOnly'=>true),
            array('account_id', 'length', 'max'=>12),
            array('obj_type', 'length', 'max'=>20),
            array('obj_name', 'length', 'max'=>100),
            array('obj_url, obj_pic_url', 'length', 'max'=>255),
            array('status', 'length', 'max'=>1),

            array('id, account_id, obj_type, obj_id, obj_name, obj_url, obj_pic_url, obj_src_id, status, create_time, update_time', 'safe', 'on'=>'search'),
        );
    }
    /**
     * A wrapper method to return all records of this model
     * @return \Activity
     */
    public function all() 
    {
        $this->getDbCriteria()->mergeWith(array(
            'condition'=>'status=\''.Process::YES.'\'',
        ));
        return $this;
    }
    /**
     * A scope method to return all liked items based on object type
     * @return \Like
     */
    public function scopeObject($obj_type,$obj_id=null)
    {
        $condition = 'obj_type = \''.$obj_type.'\' AND status=\''.Process::YES.'\'';
        if ($obj_id!=null)
            $condition .= ' AND obj_id = '.$obj_id;
        $this->getDbCriteria()->mergeWith(array(
            'condition'=>$condition,
        ));
        return $this;
    }
    /**
     * A scope method to return all liked items pertaining to Shop model
     * @return \Like
     */
    public function shop($obj_id=null) 
    {
        return $this->scopeObject(Shop::model()->tableName(), $obj_id);
    }
    /**
     * A scope method to return all liked items pertaining to Product model
     * @return \Like
     */
    public function product($obj_id=null) 
    {
        return $this->scopeObject(Product::model()->tableName(), $obj_id);
    }
    /**
     * A scope method to return all liked items pertaining to CampaignBga model
     * @return \Like
     */
    public function campaignBga($obj_id=null) 
    {
        return $this->scopeObject(CampaignBga::model()->tableName(), $obj_id);
    }
    /**
     * A scope method to return all liked items pertaining to Tutorial model
     * @return \Like
     */
    public function tutorial($obj_id=null) 
    {
        return $this->scopeObject(Tutorial::model()->tableName(), $obj_id);
    }
    /**
     * A scope method to return all liked items pertaining to TutorialSeries model
     * @return \Like
     */
    public function tutorialSeries($obj_id=null) 
    {
        return $this->scopeObject(TutorialSeries::model()->tableName(), $obj_id);
    }
    /**
     * A scope method to return all liked items pertaining to Question model
     * @return \Like
     */
    public function question($obj_id=null) 
    {
        return $this->scopeObject(Question::model()->tableName(), $obj_id);
    }
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => Sii::t('sii','ID'),
            'account_id' => Sii::t('sii','Account'),
            'obj_type' => Sii::t('sii','Object Type'),
            'obj_id' => Sii::t('sii','Object'),
            'obj_name' => Sii::t('sii','Object Name'),
            'obj_url' => Sii::t('sii','Object Url'),
            'obj_pic_url' => Sii::t('sii','Object Picture Url'),
            'obj_src_id' => Sii::t('sii','Object Source ID'),
            'status' => Sii::t('sii','Status'),
            'create_time' => Sii::t('sii','Create Time'),
            'update_time' => Sii::t('sii','Update Time'),
        );
    }
    /**
     * Retrieves a list of models based on the current search/filter conditions.
     *
     * Typical usecase:
     * - Initialize the model fields with values from filter form.
     * - Execute this method to get CActiveDataProvider instance which will filter
     * models according to data in model fields.
     * - Pass data provider to CGridView, CListView or any similar widget.
     *
     * @return CActiveDataProvider the data provider that can return the models
     * based on the search/filter conditions.
     */
    public function search()
    {
        $criteria=new CDbCriteria;

        $criteria->compare('id',$this->id);
        $criteria->compare('account_id',$this->account_id);
        $criteria->compare('obj_type',$this->obj_type,true);
        $criteria->compare('obj_id',$this->obj_id);
        $criteria->compare('obj_name',$this->obj_name,true);
        $criteria->compare('obj_url',$this->obj_url,true);
        $criteria->compare('obj_pic_url',$this->obj_pic_url,true);
        $criteria->compare('obj_src_id',$this->obj_src_id);
        $criteria->compare('status',$this->status,true);
        $criteria->compare('create_time',$this->create_time);
        $criteria->compare('update_time',$this->update_time);

        return new CActiveDataProvider($this, array(
                'criteria'=>$criteria,
        ));
    }
    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return Like the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    public function getCounter()
    {
        return Yii::app()->serviceManager->getAnalyticManager()->getMetricValue($this->obj_type, $this->obj_id, Metric::COUNT_LIKE);
    }      

    public function updateCounter($value)
    {
        Yii::app()->serviceManager->getAnalyticManager()->setCounterMetric($this->obj_type, $this->obj_id, Metric::COUNT_LIKE, $value);
    }           

    public function likable()
    {
        return ($this->status==null || $this->status==Process::NO);
    }     

    public function getObjectPicUrl()
    {
        return $this->obj_pic_url;
    }       
    public function getObjectThumbnail()
    {
        if ($this->obj_type == Tutorial::model()->tableName() || $this->obj_type == Question::model()->tableName())
            return $this->getObject()->getImageThumbnail();
        else 
            return CHtml::image($this->getObjectPicUrl(),'Like!',array('width'=>100,'height'=>100));
    }       

    public function getObjectUrl()
    {
        return $this->obj_url;
    }       
    /**
     * Return the Like object
     * @return Like
     */
    public function getObject()
    {
        $type = SActiveRecord::resolveTablename($this->obj_type);
        return $type::model()->findByPk($this->obj_id);
    }       
    /**
     * Url to view this model
     * @return string url
     */
    public function getViewUrl()
    {
        return $this->getObjectUrl();
    }
    
}
