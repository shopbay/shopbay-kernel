<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.components.validators.CompositeUniqueKeyValidator");
/**
 * This is the model class for table "s_product_attribute".
 *
 * The followings are the available columns in table 's_product_attribute':
 * @property integer $id
 * @property integer $product_id
 * @property string $code
 * @property string $name
 * @property integer $type
 * @property integer $share
 * @property integer $create_time
 * @property integer $update_time
 *
 * The followings are the available model relations:
 * @property Product $product
 * @property ProductAttributeOption[] $productAttributeOptions
 *
 * @author kwlok
 */
class ProductAttribute extends SActiveRecord
{
    /**
     * Limit is 6 for below reason:
     * Table s_inventory column `sku` varchar(30) NOT NULL 
     * format=[ObjectCode]+[AttributeCode+OptionCode][2][3]..[n]; 
     * length 30 supports up to n=6 attributes (total length 6 + 6x4 = 30)',
     */
    const LIMIT = 6;
    const SHARE = 1;
    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return ProductAttribute the static model class
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
        return 's_product_attribute';
    }
    /**
     * Model display name 
     * @param $mode singular or plural, if the language supports, e.g. english
     * @return string the model display name
     */
    public function displayName($mode=Helper::SINGULAR)
    {
        return Sii::t('sii','Attribute|Attributes',array($mode));
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
                'accountSource'=>'product',
            ),
            'merchant' => array(
                'class'=>'common.components.behaviors.MerchantBehavior',
                'merchantAttribute'=>'product_id',
            ),
            'activity' => array(
                'class'=>'common.modules.activities.behaviors.ActivityBehavior',
                'iconUrlSource'=>'product',
            ),     
            'multilang' => array(
                'class'=>'common.components.behaviors.LanguageBehavior',
            ),     
            'locale' => array(
                'class'=>'common.components.behaviors.LocaleBehavior',
                'ownerParent'=>'shop',
            ),   
            'childbehavior' => array(
                'class'=>'common.components.behaviors.ChildModelBehavior',
                'parentAttribute'=>'attr_id',
                'childAttribute'=>'options',
                'childModelClass'=>'ProductAttributeOption',
                'childUpdatableAttributes'=>array('name','code','surcharge'),
                'afterInsert'=>'shareAttributes',
                'afterUpdate'=>'shareAttributes',
            ),            
        );
    }
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('product_id, code, name, type, share', 'required'),
            array('product_id, type', 'numerical', 'integerOnly'=>true),
            array('share', 'length', 'max'=>1),
            array('code', 'length', 'max'=>2),
            //This column stored json encoded name in different languages, 
            //It buffers about 20 languages, assuming each take 50 chars.
            array('name', 'length', 'max'=>1000),
            //validate options 
            array('id', 'ruleOptions'),
            //on create scenario, id field here as dummy
            array('code', 'CompositeUniqueKeyValidator','keyColumns'=>'product_id, code','errorMessage'=>Sii::t('sii','Code is already taken'),'on'=>'create'),
            //on pre-create scenario, id field here as dummy
            array('id', 'ruleAttributeLimit','params'=>array(),'on'=>'precreate'),
            array('product_id', 'ruleInventoryExists','params'=>array(),'on'=>'precreate'),
            //on delete scenario, id field here as dummy
            array('id', 'ruleSKUExists','params'=>array(),'on'=>'delete'),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, product_id, code, name, type, share, create_time, update_time', 'safe', 'on'=>'search'),
        );
    }
    /**
     * Validate product attributes limit
     */
    public function ruleAttributeLimit($attribute,$params)
    {
        if ($this->hitLimit())
            $this->addError('id',Sii::t('sii','You already used up the maximum {limit} product attributes allowed',array('{limit}'=>self::LIMIT)));
    }
    /**
     * Validate product attribute exists sku
     */
    public function ruleSKUExists($attribute,$params)
    {
       if ($this->existsSKU())
            $this->addError('id',Sii::t('sii','Attribute "{attribute}" has inventories. Please clear its inventory if you wish to delete this attribute.',array('{attribute}'=>$this->code)));
    }
    /**
     * Validate product if already has inventory
     * Product has SKU with inventory is not allowed to create attribute anymore to avoid complex scenarios
     * (1) When product may already accepting order which based on stocks SKU (attribute linked)
     * (2) Avoid stock re-distribute or re-define when inventory (SKU based) already exists 
     */
    public function ruleInventoryExists($attribute,$params)
    {
       if ($this->product->hasInventory()){
           $message = Sii::t('sii','This product has inventory of SKU created with attributes, therefore you are not allowed to create new attribute.');
           $message .= '<br>'.Sii::t('sii','Try to create new product of similar item first and later create the new attribute.');
           $this->addError('product_id',$message);
        }
    }
    /**
     * Validate subcategories 
     */
    public function ruleOptions($attribute,$params)
    {
        $this->ruleChilds('id');//use id field as proxy
    }    

    public function hitLimit()
    {
       return count($this->product->attrs)==self::LIMIT;
    }
    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
            'product' => array(self::BELONGS_TO, 'Product', 'product_id'),
            'options' => array(self::HAS_MANY, 'ProductAttributeOption', 'attr_id'),
        );
    }
    /**
     * Return shop model
     * @return CModel
     */
    public function getShop() 
    {
        if ($this->product!=null){
            return $this->product->shop;
        }
        else 
            return null;
    }       
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => Sii::t('sii','ID'),
            'product_id' => Sii::t('sii','Product'),
            'code' => Sii::t('sii','Code'),
            'name' => Sii::t('sii','Name'),
            'type' => Sii::t('sii','Type'),
            'share' => Sii::t('sii','Share this attribute and its options with other products'),
            'create_time' => Sii::t('sii','Create Time'),
            'update_time' => Sii::t('sii','Update Time'),
        );
    }

    public function shareAttributes()
    {
        $criteria = new CDbCriteria();
        $criteria->addColumnCondition(array('obj_type'=>$this->product->tableName()));
        $criteria->addColumnCondition(array('code'=>$this->code));
        $attr = Attribute::model()->mine()->find($criteria);
        if ($this->share== ProductAttribute::SHARE){
            if ($attr==null){
                $attr = new Attribute();
                $attr->account_id = $this->product->account_id;
                $attr->obj_type = $this->product->tableName();
                $attr->attributes = $this->getAttributes(array('code','name','type'));
                if ($attr->save()){
                    logTrace($this->product->tableName().' attribute created',$attr->getAttributes());
                    foreach($this->searchOptions()->data as $option)
                        $this->shareAttributeOptions($attr, $option);
                }
                else
                    logError($this->product->tableName().' attribute creation error',$attr->getErrors());
            }
            else {
                $attr->attributes = $this->getAttributes(array('name','type'));//name and type can be changed
                if ($attr->save()){
                    logTrace($this->product->tableName().' attribute updated',$attr->getAttributes());
                    foreach($this->searchOptions()->data as $option)
                        $this->shareAttributeOptions($attr, $option);
                }
                else
                    logTrace($this->product->tableName().' attribute update error',$attr->getErrors());
            }
        }
        else {//unshare
            if ($attr!=null){
                foreach($this->searchOptions()->data as $option)
                    $this->unshareAttributeOptions($attr, $option);
                $this->unshareAttributes($attr);
            }
        }
    } 
    public function shareAttributeOptions($attr, $option)
    {
        $criteria = new CDbCriteria();
        $criteria->addColumnCondition(array('attr_id'=>$attr->id));
        $criteria->addColumnCondition(array('code'=>$option->code));
        $_option = AttributeOption::model()->find($criteria);
        if ($_option==null){
            $_option = new AttributeOption();
            $_option->attr_id = $attr->id;
            $_option->attributes = $option->getAttributes(array('code','name'));
            if ($_option->save())
                logTrace('shop attribute option created',$_option->getAttributes());
            else
                logError('shop attribute option creation error',$_option->getErrors());
        }
        else {
            $_option->attributes = $option->getAttributes(array('name'));//name can be changed
            if ($_option->save())
                logTrace('shop attribute option updated',$_option->getAttributes());
            else
                logError('shop attribute option update error',$_option->getErrors());
        }
    } 
    public function unshareAttributes($attr)
    {
        //last round check if any option left, if no more option, unshare attribute
        $criteria = new CDbCriteria();
        $criteria->addColumnCondition(array('attr_id'=>$attr->id));
        $_options = AttributeOption::model()->findAll($criteria);
        if (count($_options)==0){//no option found, unshare attribute
            $count = $attr->delete();
            logTrace('shop attribute unshared (deleted) count='.$count);
            logTrace('shop attribute $criteria',$criteria);
        }
    } 
    public function unshareAttributeOptions($attr, $option)
    {
        $criteria = new CDbCriteria();
        $criteria->addColumnCondition(array('attr_id'=>$attr->id));
        $criteria->addColumnCondition(array('code'=>$option->code));
        $count = AttributeOption::model()->deleteAll($criteria);
        logTrace('shop attribute option unshared (deleted) count='.$count);
        logTrace('shop attribute option $criteria',$criteria);
    } 
    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search()
    {
        $criteria=new CDbCriteria;

        $criteria->compare('id',$this->id);
        $criteria->compare('product_id',$this->product_id);
        $criteria->compare('code',$this->code,true);
        $criteria->compare('name',$this->name,true);
        $criteria->compare('type',$this->type);
        $criteria->compare('share',$this->share,true);
        $criteria->compare('create_time',$this->create_time);
        $criteria->compare('update_time',$this->update_time);

        return new CActiveDataProvider($this, array(
                'criteria'=>$criteria,
        ));
    }
    public function searchOptions()
    {
        $criteria=new CDbCriteria;
        $criteria->addColumnCondition(array('attr_id'=>$this->id));
        return new CActiveDataProvider('ProductAttributeOption', array(
                'criteria'=>$criteria,
        ));
    }

    const TYPE_SELECT     = 1;
    const TYPE_TEXTFIELD  = 2;
    public function getTypes()
    {
        return array(
            self::TYPE_SELECT => Sii::t('sii','Select Field'),
            //self::TYPE_TEXTFIELD => Sii::t('sii','Text Field'),
        );
    }        
    public function getTypeText()
    {
        $types = $this->getTypes();
        return $types[$this->type];
    }    

    public function getOptionsArray($locale=null,$verbose=false)
    {
        $criteria=new CDbCriteria;
        $criteria->addColumnCondition(array('attr_id'=>$this->id));
        $criteria->order  = 'name ASC';
        //TODO should filter by those not yet have stocks defined
        logTrace(__METHOD__,$criteria);
        $list = new CMap();
        foreach($this->options as $option)
            $list->add($option->encodeOption($verbose),$option->getOptionText($locale));
        return $list->toArray();

    } 

    public function getOptionsText($locale=null)
    {
        if ($this->type==self::TYPE_SELECT){
            $text = '';
            foreach ($this->searchOptions()->data as $data) {
                $text .= $data->code.': '.$data->displayLanguageValue('name',$locale);
                $text .= $data->getSurchargeText();
                $text .= '<br>';
            }
            return $text;
        }
        else
            return Sii::t('sii','NA');
    }  

    public function getShareText()
    {
        if ($this->share==self::SHARE)
            return Sii::t('sii','Shared');
        else
            return Sii::t('sii','Unshared');
    }     

    public function existsSKU($sku=null)
    {
        foreach ($this->options as $option) {
            $opt = $this->code.$option->code;
            if (Inventory::lookupSKUOption($this->product_id,$opt,$sku))
                return true;
        }
        return false;
    }        
    /**
     * Url to view this model
     * @return string url
     */
    public function getViewUrl()
    {
        return url('product/attribute/view/'.$this->id);
    }          
    

}