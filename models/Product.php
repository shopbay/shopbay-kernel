<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.services.workflow.models.Transitionable");
Yii::import("common.services.workflow.behaviors.TransitionWorkflowBehavior");
Yii::import("common.components.behaviors.*");
Yii::import("common.components.validators.CompositeUniqueKeyValidator");
Yii::import("common.modules.products.behaviors.ProductBehavior");
Yii::import("common.modules.pages.models.PageTrait");
/**
 * This is the model class for table "s_product".
 *
 * The followings are the available columns in table 's_product':
 * @property integer $id
 * @property integer $account_id
 * @property integer $shop_id
 * @property integer $brand_id
 * @property string $code
 * @property string $name
 * @property integer $image
 * @property string $unit_price
 * @property string $weight
 * @property string $spec
 * @property string $slug
 * @property string $meta_tags
 * @property string $status
 * @property integer $create_time
 * @property integer $update_time
 *
 * The followings are the available model relations:
 * @property Account $account
 *
 * @author kwlok
 */
class Product extends Transitionable 
{
    use PageTrait;
    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Product the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    /**
     * @inheritdoc
     */
    protected function getMetaTagAttribute()
    {
        return 'meta_tags';
    }    
    /**
     * Model display name 
     * @param $mode singular or plural, if the language supports, e.g. english
     * @return string the model display name
     */
    public function displayName($mode=Helper::SINGULAR)
    {
        return Sii::t('sii','Product|Products',[$mode]);
    } 
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 's_product';
    }
    /**
     * Behaviors for this model
     */
    public function behaviors()
    {
        return [
            'image' => [
                'class'=>'common.modules.media.behaviors.MultipleMediaBehavior',
                'transitionMedia'=>true,
                'stateVariable'=>SActiveSession::PRODUCT_IMAGE,
                'imageDefault'=>Image::DEFAULT_IMAGE_PRODUCT,
            ],
            'sluggable' => [
                'class'=>'common.components.behaviors.SlugBehavior',
                'dynamicColumn'=>[
                    'method'=>'getSlugValue',
                ],
                'skipScenario'=>'manualSlug',
            ],
            'timestamp' => [
                'class'=>'common.components.behaviors.TimestampBehavior',
            ],
            'account' => [
                'class'=>'common.components.behaviors.AccountBehavior',
            ],
            'merchant' => [
                'class'=>'common.components.behaviors.MerchantBehavior',
            ],
            'locale' => [
                'class'=>'common.components.behaviors.LocaleBehavior',
            ],
            'transition' => [
                'class'=>'common.components.behaviors.TransitionBehavior',
                'activeStatus'=>Process::PRODUCT_ONLINE,
                'inactiveStatus'=>Process::PRODUCT_OFFLINE,
            ],
            'workflow' => [
                'class'=>'common.services.workflow.behaviors.TransitionWorkflowBehavior',
                'subTransitionModelsCallback'=>'prepareTransitionAssociatedMedia',
            ],     
            'activity' => [
                'class'=>'common.modules.activities.behaviors.ActivityBehavior',
            ],    
            'commentable' => [
                'class'=>'common.modules.comments.behaviors.CommentableBehavior',
            ],             
            'questionable' => [
                'class'=>'common.modules.questions.behaviors.QuestionableBehavior',
            ],             
            'likablebehavior' => [
                'class'=>'common.modules.likes.behaviors.LikableBehavior',
                'modelFilter'=>'product',
            ],        
            'searchable' => [
                'class'=>'common.modules.search.behaviors.SearchableBehavior',
                'searchModel'=>'SearchProduct',
            ],    
            'multilang' => [
                'class'=>'common.components.behaviors.LanguageBehavior',
            ],             
            'productbehavior' => [
                'class'=>'ProductBehavior',
            ],  
            'childbehavior' => [
                'class'=>'common.components.behaviors.ChildModelBehavior',
                'parentAttribute'=>'product_id',
                'siblings'=>[
                    [
                        'childAttribute'=>'categories',
                        'childModelClass'=>'ProductCategory',
                        'childUpdatableAttributes'=>['category_id','subcategory_id'],
                    ],
                    [
                        'childAttribute'=>'shippings',
                        'childModelClass'=>'ProductShipping',
                        'childUpdatableAttributes'=>['shipping_id','surcharge'],
                    ],                    
                ],
            ],
            'sitemap' => [
                'class'=>'common.components.behaviors.SitemapBehavior',
                'scopes'=>['active'],
                'sort'=>'update_time DESC',
            ],
        ];
    }
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            ['account_id, code, name, shop_id, unit_price', 'required'],
            ['image', 'required','message'=>Sii::t('sii','Please select primary image for this product')],
            ['shop_id, brand_id, weight', 'numerical', 'integerOnly'=>true],
            ['code', 'length', 'max'=>6],
            ['code', 'CompositeUniqueKeyValidator','keyColumns'=>'shop_id, code','errorMessage'=>Sii::t('sii','Code is already taken'),'on'=>'create'],
            //This column stored json encoded name in different languages, 
            //It buffers about 20 languages, assuming each take 100 chars.
            ['name', 'length', 'max'=>2000],
            ['unit_price, status', 'length', 'max'=>10],
            ['unit_price', 'length', 'max'=>10],
            ['unit_price', 'type', 'type'=>'float'],
            ['slug', 'length', 'max'=>100],
            ['slug', 'ruleUnique'],
            ['slug', 'ruleSlugWhitelist'],
            //This column stored json encoded spec in different languages.
            ['spec', 'safe'],
            ['status', 'length', 'max'=>10],
            //business rules
            ['id', 'ruleShippings'],
            ['id', 'ruleCategories'],//use id as proxy
            ['weight', 'ruleWeight'],
            ['meta_tags', 'length', 'max'=>1000],//json encoded meta tags

            //on delete scenario, id field here as dummy
            ['id', 'ruleAssociations','params'=>[],'on'=>'delete'],

            //activate scenario
            ['id, name, unit_price, status, create_time', 'safe', 'on'=>'activate'],
            ['status', 'ruleActivation','on'=>'activate'],

            ['id, account_id, shop_id, brand_id, code, name, image, unit_price, weight, spec, slug, status, create_time, update_time', 'safe', 'on'=>'search'],
        ];
    }
    /**
     * Validate if shipping has any associations
     * (1) Product has attributes OR
     * (2) Product has inventory OR
     * (3) Product is not offline
     */
    public function ruleAssociations($attribute,$params)
    {
        if ($this->deactivable())
            $this->addError('id',Sii::t('sii','"{object}" must be offline',['{object}'=>$this->displayLanguageValue('name')]));

        if (count($this->attrs)>0)
            $this->addError('id',Sii::t('sii','"{object_name}" has associations with {association_object}. Please clear the association if you wish to delete this {object_type}.',[
                '{object_name}'=>$this->displayLanguageValue('name'),
                '{association_object}'=>strtolower(ProductAttribute::model()->displayName(Helper::PLURAL)),
                '{object_type}'=> strtolower($this->displayName()),
            ]));
        
        if ($this->hasInventory())        
            $this->addError('id',Sii::t('sii','"{object_name}" has associations with {association_object}. Please clear the association if you wish to delete this {object_type}.',[
                '{object_name}'=>$this->displayLanguageValue('name'),
                '{association_object}'=>strtolower(Inventory::model()->displayName(Helper::PLURAL)),
                '{object_type}'=> strtolower($this->displayName()),
            ]));
    }
    /**
     * Two validations:
     * [1] Validate product shipping surcharges
     * [2] Verify that product shipping having more than one 'Tiered Fee' must be of same base
     * One cannot having base "Weight" and "Order Subtotal" at the same time
     */
    public function ruleShippings($attribute,$params)
    {
        $previousBase = null;
        foreach ($this->shippings as $shipping) {
            //validation [1]
            if (!$shipping->validate()){
                if ($shipping->hasErrors('surcharge'))
                    $this->addError('id',$shipping->getError('surcharge'));//use id as proxy
                logError('shipping validation error', $shipping->getErrors());
            }
            //validation [2]
            $tier = ShippingTier::model()->findByAttributes(['shipping_id'=>$shipping->shipping_id]);
            if ($tier!=null){
                if ($previousBase===null)
                    $previousBase = $tier->base;
                if ($previousBase != $tier->base){
                    $this->addError('id',Sii::t('sii','Tiered Type Shipping Base must all be the same.'));
                    break;
                }
                $previousBase = $tier->base;
            }
        }
    }
    
    public function ruleCategories($attribute,$params) 
    {
        $validateAttributes = ['category_id','subcategory_id'];
        foreach ($this->categories as $child) {
            if (!$child->validate($validateAttributes)){
                foreach ($validateAttributes as $field){
                    if ($child->hasErrors($field))
                        $this->addError($attribute,$child->getError($field));
                }
            }
        }//end for loop
    }      
    /**
     * Verify weight field
     */
    public function ruleWeight($attribute,$params)
    {
        foreach (SActiveSession::get(SActiveSession::PRODUCT_SHIPPING) as $sessionProductShipping) {
            $model = ShippingTier::model()->findByAttributes(['shipping_id'=>$sessionProductShipping->shipping_id]);
            if ($model!=null)
                if ($model->base==ShippingTier::BASE_WEIGHT)
                    if ($this->weight==null){
                        $this->addError('weight',Sii::t('sii','Weight is required for weight-based shipping'));
                        break;
                    }
        }
    }
    /**
     * Activation Check
     * (1) Verify that need at least 1 online shipping for product activation
     * (2) Verify that product needs to have stock to bring it online
     */
    public function ruleActivation($attribute,$params)
    {
        $pass = false;
        foreach ($this->shippings as $productShipping) {
            $model = Shipping::model()->findByPk($productShipping->shipping_id);
            if ($model!=null)
                if ($model->deactivable()){//deactivable means currently online
                    $pass = true;
                    logTrace('activatecheck() pass');
                    break;
                }
        }

        if (!$pass){
            $error = Sii::t('sii','Product must have at least one online shipping associated');
            $this->addError('status',$error);
        }
        if (!$this->hasInventory()) {
            if (!$pass)
                $error .= Sii::t('sii',' and product must have inventory');
            else
                $error = Sii::t('sii','Product must have inventory');
            $this->addError('status',$error);
        }
    }
    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return [
            'account' => [self::BELONGS_TO, 'Account', 'account_id'],
            'shop' => [self::BELONGS_TO, 'Shop', 'shop_id'],
            'brand' => [self::BELONGS_TO, 'Brand', 'brand_id'],
            'category' => [self::BELONGS_TO, 'Category', 'category_id'],
            'shippings' => [self::HAS_MANY, 'ProductShipping', 'product_id'],
            'attrs' => [self::HAS_MANY, 'ProductAttribute', 'product_id'],
            'campaigns' => [self::HAS_MANY, 'CampaignBga', 'buy_x'],
            'categories' => [self::HAS_MANY, 'ProductCategory', 'product_id'],
        ];
    }
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array_merge($this->pageSeoAttributeLabels(),[
            'id' => Sii::t('sii','ID'),
            'account_id' => Sii::t('sii','Account'),
            'shop_id' => Sii::t('sii','Shop'),
            'brand_id' => Sii::t('sii','Brand'),
            'code' => Sii::t('sii','Code'),
            'name' => Sii::t('sii','Name'),
            'image' => Sii::t('sii','Image'),
            'unit_price' => Sii::t('sii','Unit Price'),
            'weight' => Sii::t('sii','Weight'),
            'spec' => Sii::t('sii','Specification'),
            'slug' => Sii::t('sii','SEO URL'),
            'meta_tags' => Sii::t('sii','SEO Meta Tags'),
            'status' => Sii::t('sii','Status'),
            'create_time' => Sii::t('sii','Create Date'),
            'update_time' => Sii::t('sii','Update Date'),
            'seo' => Sii::t('sii','Search Engine Optimization'),
        ]);
    }
    /**
     * Brand finder method
     * @param $brand_id
     * @return CComponent
     */
    public function locateBrand($brand_id)
    {
        $criteria = new CDbCriteria();
        $criteria->addCondition('brand_id='.$brand_id);
        $this->getDbCriteria()->mergeWith($criteria);
        return $this;
    }
    /**
     * Only return media associated to this model only and when media is offline
     * @return array
     */
    public function prepareTransitionAssociatedMedia($action)
    {
        $mediaArray = [];
        foreach ($this->searchMediaAssociation()->data as $assoc) {
            if ($assoc->media->hasSingleAssociation)
                $mediaArray[] = $assoc->media;
            elseif ($assoc->media->hasManyAssociations && $assoc->media->offline() && $action==WorkflowManager::ACTION_DEACTIVATE)
                $mediaArray[] = $assoc->media;
        }
        return $mediaArray;
    }

    public function searchAttributes($pageSize=null)
    {
       return new CActiveDataProvider(ProductAttribute::model(),[
           'criteria'=>[
                'condition'=>'product_id='.$this->id,
                'order'=>'update_time DESC',
            ],
            'pagination'=>[
                'pageSize'=>isset($pageSize)?$pageSize:Config::getSystemSetting('record_per_page'),
            ],
            'sort'=>false,
        ]);
    }  
    
    public function searchShippings($pageSize=null)
    {
       return new CActiveDataProvider(ProductShipping::model(),[
           'criteria'=>[
                'condition'=>'product_id='.$this->id,
                'order'=>'update_time DESC',
            ],
            'pagination'=>[
                'pageSize'=>isset($pageSize)?$pageSize:Config::getSystemSetting('record_per_page'),
            ],
            'sort'=>false,
        ]);
    }  

    public function searchInventories()
    {
        $criteria = new CDbCriteria();
        $criteria->addColumnCondition(['obj_type'=>$this->tableName()]);
        $criteria->addColumnCondition(['obj_id'=>$this->id]);
        $criteria->order = 'update_time DESC';
        return new CActiveDataProvider(Inventory::model(),[
            'criteria'=>$criteria,
            'pagination'=>['pageSize'=>Config::getSystemSetting('record_per_page')],
            'sort'=>false,
        ]);
    }  
        
    public function searchCampaigns($exceptOfferXOnly=true,$pageSize=null)
    {
        $criteria = new CDbCriteria();
        $criteria->order = 'create_time DESC';
        $criteria->addColumnCondition(['buy_x'=>$this->id]);
        $criteria->addColumnCondition(['status'=>Process::CAMPAIGN_ONLINE]);
        $finder = CampaignBga::model()->notExpired();
        if ($exceptOfferXOnly)
            $finder = $finder->exceptOfferBuyXOnly();
        return new CActiveDataProvider($finder,[
            'criteria'=>$criteria,
            'pagination'=>['pageSize'=>isset($pageSize)?$pageSize:Config::getSystemSetting('bga_per_page')],
        ]);
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
        $criteria->compare('brand_id',$this->brand_id);
        $criteria->compare('code',$this->code,true);
        $criteria->compare('name',$this->name,true);
        $criteria->compare('image',$this->image);
        $criteria->compare('unit_price',$this->unit_price,true);
        $criteria->compare('weight',$this->weight,true);
        $criteria->compare('spec',$this->spec,true);
        $criteria->compare('slug',$this->slug,true);
        $criteria->compare('create_time',$this->create_time);
        $criteria->compare('update_time',$this->update_time);

        if ($this->getScenario()=='activate')
            $criteria->compare('status',Process::PRODUCT_OFFLINE);
        else if ($this->getScenario()=='deactivate')
            $criteria->compare('status',Process::PRODUCT_ONLINE);
        else
            $criteria->compare('status',$this->status,true);

        $criteria->mergeWith($this->mine()->getDbCriteria());

        logTrace(__METHOD__.' criteria',$criteria);

        return new CActiveDataProvider('Product',[
            'criteria'=>$criteria,
            'pagination'=>['pageSize'=>Config::getSystemSetting('record_per_page')],
        ]);

    }
    /**
     * Before primary image saving, the $image is storing the filename
     */
    public function savePrimaryImage()
    {
        $primaryImage = false;
        foreach (MediaAssociation::model()->searchAssociation($this->tableName(),$this->id)->data as $mediaAssoc) {
            if ($mediaAssoc->filename==$this->image){
                $this->image = $mediaAssoc->id;//set to the correct primary image if found
                $primaryImage = true;
                logTrace(__METHOD__.' set to the correct primary image to media association',$this->image);
                break;
            }
        }
        if (!$primaryImage){
            throw new CException(Sii::t('sii','Product primary image must be set!'));
        }
        $this->isNewRecord=false;//set to not new record to allow update()
        $this->update();
    }      
    /**
     * This method is called attribute is set as url rather than image id
     * This is called via API
     * TODO Migrate this to Media when developing product API
     * @throws CException
     */
    public function saveImagesByUrl($createdBy)
    {
        $createImage = function($key,$url,$user){
            $img = new ImageExternal();
            $img->obj_id = $this->id;
            $img->obj_type = $this->tableName();
            $img->create_by = $user;
            $img->setImageKey($key);
            $img->setUrl($url);
            if( !$img->save()) {
                logError(__METHOD__. " Could not save image by url",$img->getErrors());
                throw new CException( 'Could not save image by url');
            }
            logTrace(__METHOD__.' create ok',$img->attributes);
            return $img;
        };
        $deleteImage = function(){
            $criteria = new CDbCriteria();
            $criteria->addColumnCondition(['obj_type'=>$this->tableName()]);
            $criteria->addColumnCondition(['obj_id'=>$this->id]);
            Image::model()->deleteAll($criteria);
        };
        
        if (is_array($this->image)){
            if (!empty($this->image))
                $deleteImage();//remove all images first before creating new ones
            foreach ($this->image as $key => $url) {
                $image = $createImage($key,$url,$createdBy);
                if ($key==0)//set first image as primary
                    $this->image = $image->filename;//stores image file back to model's image
            }
        }
        else {//single image url
            $deleteImage();//remove all images first before creating new ones
            $image = $createImage(0,$this->image,$createdBy);
            $this->image = $image->filename;//stores image file back to model's image
        }
        //turn off afterSave event
        //@see MultipleImageBehavior::afterSave()
        $this->disableImageAfterSave();
    }  
    
    public function getHasCategories()
    {
        return count($this->categories)>0;
    }
    
    public function getCategoriesData($locale=null)
    {
        $data = [];
        foreach ($this->categories as $category) {
            $data[] = $category->toString($locale);
        }
        return $data;
    }  
     
    public function deleteInventories() 
    {
        $criteria = new CDbCriteria();
        $criteria->addColumnCondition(['obj_type'=>$this->tableName()]);
        $criteria->addColumnCondition(['obj_id'=>$this->id]);
        Inventory::model()->deleteAll($criteria);
    }         
    public  function getNameColumnData($locale) 
    {
        $list = new CMap();
        $imageData = $this->getImageData('/files/products/',Image::DEFAULT_IMAGE_PRODUCT);
        $list->add($this->displayLanguageValue('name',$locale),['image'=>$imageData]);
        return $list;
    }       
    
    public function hasInventory($sku=null)
    {
        return Inventory::getAvailableByProduct($this->id,$sku)>0;
    }
    public function getInventory($sku=null)
    {
        return Inventory::getAvailableByProduct($this->id,$sku);
    }
    public function getInventoryText($sku=null)
    {
        return Inventory::getDisplayText(Inventory::getAvailableByProduct($this->id,$sku));
    } 

    public function hasWeightBasedShipping()
    {
        foreach ($this->shippings as $productShipping) {
            $model = ShippingTier::model()->findByAttributes(['shipping_id'=>$productShipping->shipping_id]);
            if ($model!=null)
                if ($model->base==ShippingTier::BASE_WEIGHT)
                        return true;
        }
        return false;
    }  
    
    public function getShippingsDataArray($locale=null)
    {
        $result = new CMap();
        $shippings = new CMap();
        foreach ($this->shippings as $model){
            $shippings->add($model->shipping_id,$model->getSurcharge()); 
        }        
        $condition = QueryHelper::constructInCondition('id',$shippings->getKeys());
        foreach(Shipping::model()->searchActive($condition)->data as $shipping){
            $surcharge = $shippings->itemAt($shipping->id);
            $value = $shipping->id.Helper::PIPE_SEPARATOR.$surcharge;
            $result->add($value,$shipping->getShippingText($surcharge,$locale));
        }
        return $result->toArray();
    }  

    private $_c;//store x offer only campaign id
    public function getCampaign()
    {
        if (!isset($this->_c)) {
            $this->_c = Yii::app()->serviceManager->getCampaignManager()->checkCampaignBga(CampaignBga::X_OFFER,$this->id);
            if ($this->_c!=null)//for tracing purpose
               logTrace(__METHOD__,$this->_c->getAttributes());
        }
        return $this->_c;
    }
    /**
     * Check if product has any direct offer on it 
     * @see CampaignBga::X_OFFER
     * @return type
     */
    public function hasCampaign()
    {
        return $this->getCampaign()!=null;
    }
    public function hasOtherCampaigns()
    {
        return Yii::app()->serviceManager->getCampaignManager()->existsCampaignBga(CampaignBga::X_OFFER_MORE,$this->id) ||
               Yii::app()->serviceManager->getCampaignManager()->existsCampaignBga(CampaignBga::X_Y_OFFER,$this->id);
    }
    public function countCampaigns($excludeCampaign=null)
    {
        return Yii::app()->serviceManager->getCampaignManager()->countCampaignBga($this->id,$excludeCampaign);
    }
    public function getPrice($quantity=1)
    {
        if ($this->hasCampaign()){
            return $this->getCampaign()->getOfferPrice($this,$quantity);
        }
        else
            return $this->unit_price * $quantity;
    } 
    /**
     * Url to view this model
     * @return string url
     */
    public function getViewUrl($id=null)
    {
        if (!isset($id))
            $id = $this->id;
        return url('product/view/'.$id);
    }     
    /**
     * Check if it is a new product added;
     * Rule: less than 3 days
     */
    public function isNew()
    {
        return time() - $this->create_time <= Config::getBusinessSetting('new_product_criteria') * 24 * 60 * 60;
    }
    /**
     * Check if product has brand
     * @return type
     */
    public function hasBrand()
    {
        return $this->brand_id!=null;
    }
    /**
     * Check if product has category
     * @return type
     */
    public function hasCategory()
    {
        return $this->category_id!=null;
    }
    /**
     * This additional attribute is return from complex sql query 
     * Thanks to Yii framework the additional column is automatically loaded like charm
     * 
     * @see Shop::searchMostLikedProducts()
     * @see Shop::searchMostDiscussedProducts()
     * @see Shop::searchMostPurchasedProducts()
     * @var integer 
     */
    public $most_counter;
//    /**
//     * Call back to delete childs for hard delete
//     * @var boolean 
//     */
//    protected $deleteChildsCallback = 'deleteChildData';    
//    /**
//     * Delete child records
//     */
//    public function deleteChildData()
//    {
//        $this->detachMediaAssociation();
//        $this->deleteSiblings();
//        $this->deleteInventories();
//    }    
}