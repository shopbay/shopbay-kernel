<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.modules.campaigns.models.Campaign");
Yii::import("common.modules.campaigns.models.ICampaign");
Yii::import("common.modules.campaigns.behaviors.CampaignBgaModelBehavior");
/**
 * This is the model class for table "s_campaign_bga".
 * 
 * CampaignBga is a product level campaign.
 * 
 * The followings are the available columns in table 's_campaign_bga':
 * @property integer $id
 * @property string $name
 * @property string $description
 * @property integer $image
 * @property integer $shop_id
 * @property integer $buy_x
 * @property integer $buy_x_qty
 * @property integer $get_y
 * @property integer $get_y_qty
 * @property string $at_offer
 * @property string $offer_type
 * @property string $start_date
 * @property string $end_date
 * @property string $status
 * @property integer $create_time
 * @property integer $update_time
 * 
 * @author kwlok
 */
class CampaignBga extends Campaign implements ICampaign
{
    const B  = 'buy';
    const G  = 'get';
    const A  = 'at';
    //campaign scenario
    const X_OFFER      = 'x';
    const X_OFFER_MORE = 'xm';
    const X_X_OFFER    = 'xx';
    const X_Y_OFFER    = 'xy';
    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return CampaignBga the static model class
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
        return 's_campaign_bga';
    }
    /**
     * Model display name 
     * @param $mode singular or plural, if the language supports, e.g. english
     * @return string the model display name
     */
    public function displayName($mode=Helper::SINGULAR)
    {
        return Sii::t('sii','BGA Campaign|BGA Campaigns',array($mode));
    }     
    /**
     * Behaviors for this model
     */
    public function behaviors()
    {
        return array(
            'image' => array(
                'class'=>'common.modules.media.behaviors.SingleMediaBehavior',
                'transitionMedia'=>true,
                'label'=>Sii::t('sii','Image'),
                'stateVariable'=>SActiveSession::CAMPAIGN_BGA_IMAGE,
                'imageDefault'=>Image::DEFAULT_IMAGE_CAMPAIGN_BGA,
            ),
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
            'transition' => array(
                'class'=>'common.components.behaviors.TransitionBehavior',
                'activeStatus'=>Process::CAMPAIGN_ONLINE,
                'inactiveStatus'=>Process::CAMPAIGN_OFFLINE,
            ),
            'workflow' => array(
                'class'=>'common.services.workflow.behaviors.TransitionWorkflowBehavior',
            ),        
            'activity' => array(
                'class'=>'common.modules.activities.behaviors.ActivityBehavior',
                'descriptionAttribute'=>'name',
                'buttonIcon'=>array(
                    'enable'=>true,
                ),
            ),
            'commentable' => array(
                'class'=>'common.modules.comments.behaviors.CommentableBehavior',
            ),   
            'questionable' => array(
                'class'=>'common.modules.questions.behaviors.QuestionableBehavior',
            ),             
            'likablebehavior' => array(
                'class'=>'common.modules.likes.behaviors.LikableBehavior',
                'modelFilter'=>'campaignBga',
            ),   
            'multilang' => array(
                'class'=>'common.components.behaviors.LanguageBehavior',
            ),                
            'campaignbgamodel' => array(
                'class'=>'CampaignBgaModelBehavior',
            ),
            'sitemap' => array(
                'class'=>'common.components.behaviors.SitemapBehavior',
                'scopes'=>['active','notExpired'],
                'sort'=>'update_time DESC',
            ),
        );
    }
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('account_id, shop_id, name, buy_x, offer_type, start_date, end_date', 'required'),
            array('account_id, shop_id, image, buy_x, buy_x_qty, get_y, get_y_qty', 'numerical', 'integerOnly'=>true),
            //This column stored json encoded name in different languages, 
            //It buffers about 20 languages, assuming each take 100 chars.
            array('name', 'length', 'max'=>2000),
            array('at_offer', 'length', 'max'=>8),
            array('offer_type', 'length', 'max'=>1),
            array('status', 'length', 'max'=>10),
            array('image', 'safe'),
            array('description', 'safe'),
            array('buy_x', 'ruleOfferProduct'),             
            array('at_offer', 'ruleAtOffer','min'=>1,'max'=>100),
            array('id', 'ruleShippings'),
            array('start_date', 'compare','compareAttribute'=>'end_date','operator'=>'<','message'=>Sii::t('sii','Start Date must be smaller than End Date')),
            
            //on delete scenario, id field here as dummy
            array('id', 'ruleAssociations','params'=>array(),'on'=>'delete'),                    

            //activate scenario
            array('id, name, description, start_date, end_date, create_time', 'safe', 'on'=>'activate'),
            array('status', 'ruleActivation','on'=>'activate'),
            //deactivate scenario
            array('id, name, description, start_date, end_date, create_time', 'safe', 'on'=>'deactivate'),

            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, account_id, name, description, image, shop_id, buy_x, buy_x_qty, get_y, get_y_qty, at_offer, offer_type, start_date, end_date, status, create_time, update_time', 'safe', 'on'=>'search'),
        );
    }
    /**
     * Validate offer product settings
     * (1) only one product can have same buy_x with same quantity
     * (2) only one product can have same buy_x (with same quantity) and get_y (with same quantity)
     * (3) offer product must be online 
     * (4) offer product must have sufficient stock to minimally match buy_x_qty or get_y_qty 
     */
    public function ruleOfferProduct($attribute,$params)
    {
        if (!empty($this->buy_x) && !($this->getScenario()=='activate'||$this->getScenario()=='deactivate')){
            if (empty($this->buy_x_qty)){
                $this->addError('buy_x',Sii::t('sii','"Buy" product {product_name} quantity cannot be empty.',array('{product_name}'=>$this->x_product->displayLanguageValue('name',user()->getLocale()))));
                return;
            }
                
            //rule 1 and 2
            $condition = 'buy_x='.$this->buy_x.' AND buy_x_qty='.$this->buy_x_qty;
            if (!$this->isNewRecord)
                $condition .= ' AND id!='.$this->id;
                
            if ($this->hasG()){
                $condition .= ' AND get_y='.$this->get_y.' AND get_y_qty='.$this->get_y_qty;
                logTrace(__METHOD__.' condition '.$condition);
                foreach (CampaignBga::model()->findAll($condition) as $record) {
                    if ($record->id!=$this->id){
                        logTrace(__METHOD__." this.id = $this->id ",$record->attributes);
                        logTrace(__METHOD__.' clashed record',$record->attributes);
                        $this->addError('buy_x',Sii::t('sii','"Buy {x_quantity} {x_product}" together with "Get {y_quantity} {y_product}" is already defined in other campaigns.',
                                               array('{x_product}'=>$this->x_product->displayLanguageValue('name',user()->getLocale()),'{x_quantity}'=>$this->buy_x_qty,
                                                     '{y_product}'=>$this->y_product->displayLanguageValue('name',user()->getLocale()),'{y_quantity}'=>$this->get_y_qty)));
                        return;
                    }
                }
            }
            else {
                $condition .= ' AND get_y IS NULL AND get_y_qty IS NULL';
                logTrace(__METHOD__.' condition '.$condition);
                foreach (CampaignBga::model()->findAll($condition) as $record) {
                    if ($record->id!=$this->id){
                        logTrace(__METHOD__.' clashed record',$record->attributes);
                        $this->addError('buy_x',Sii::t('sii','"Buy {quantity} {product_name}" is already defined in other campaigns.',
                                                   array('{product_name}'=>$this->x_product->displayLanguageValue('name',user()->getLocale()),'{quantity}'=>$this->buy_x_qty)));
                        return;
                    }
                }
            }

            //rule 3
            if ($this->x_product->activable())
                $this->addError('buy_x',Sii::t('sii','"Buy" product {product_name} must be online.',array('{product_name}'=>$this->x_product->displayLanguageValue('name',user()->getLocale()))));
            if ($this->hasG()){
                if ($this->y_product->activable())
                    $this->addError('get_y',Sii::t('sii','"Get" product {product_name} must be online.',array('{product_name}'=>$this->y_product->displayLanguageValue('name',user()->getLocale()))));
            }

            //rule 4
            if ($this->x_product->getInventory()<$this->buy_x_qty)
                $this->addError('buy_x',Sii::t('sii','"Buy" product {product_name} has insufficient inventory (minimum is to match "Buy Quantity" {quantity}).',array('{product_name}'=>$this->x_product->displayLanguageValue('name',user()->getLocale()),'{quantity}'=>$this->buy_x_qty)));
            if ($this->hasG()){
                if ($this->x_product->getInventory()<$this->buy_x_qty)
                    $this->addError('get_y',Sii::t('sii','"Get" product {product_name} has insufficient inventory (minimum is to match "Get Quantity" {quantity}).',array('{product_name}'=>$this->y_product->displayLanguageValue('name',user()->getLocale()),'{quantity}'=>$this->get_y_qty)));
            }
        }
            
    }
    /**
     * Validate at_offer value
     */
    public function ruleAtOffer($attribute,$params)
    {
        if (!$this->onOfferFree()){
            $this->ruleOfferAmount($attribute, $params);
        }
    }    
    /**
     * Activation Check
     * (1) Verify that offer product must be online
     * (2) Verify that need at least 1 online shipping for campaign activation
     */
    public function ruleActivation($attribute,$params)
    {
        if ($this->hasExpired()){
            $this->addError('status',Sii::t('sii','You cannot activate campaign when it was expired.'));
            return;
        }
            
        if ($this->hasG()){
            if ($this->y_product->activable()){
                $this->addError('status',Sii::t('sii','Both "Buy" product {x_product} and "Get" product {y_product} must be online.',array('{x_product}'=>$this->x_product->displayLanguageValue('name',user()->getLocale()),'{y_product}'=>$this->y_product->displayLanguageValue('name',user()->getLocale()))));
                return;
            }
        }
        if ($this->x_product->activable()){
            $this->addError('status',Sii::t('sii','"Buy" product {product_name} must be online.',array('{product_name}'=>$this->x_product->displayLanguageValue('name',user()->getLocale()))));
            return;
        }
        
        $pass = false;
        foreach ($this->shippings as $shipping) {
            $model = Shipping::model()->findByPk($shipping->shipping_id);
            if ($model!=null){
                if ($model->deactivable()){//deactivable means currently online
                    $pass = true;
                    logTrace(__METHOD__.' pass');
                    break;
                }
            }
        }
        if (!$pass){
            $this->addError('status',Sii::t('sii','At least one product shipping must be online'));
        }
        
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
            $tier = ShippingTier::model()->findByAttributes(array('shipping_id'=>$shipping->shipping_id));
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
    /**
     * Validate if campaign has any associations
     * (1) Campaign is not offline
     */
    public function ruleAssociations($attribute,$params)
    {
        if ($this->deactivable())
            $this->addError('id',Sii::t('sii','"{object}" must be offline',array('{object}'=>$this->name)));
    }
    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
            'account' => array(self::BELONGS_TO, 'Account', 'account_id'),
            'shop' => array(self::BELONGS_TO, 'Shop', 'shop_id'),
            'x_product' => array(self::BELONGS_TO, 'Product', 'buy_x'),
            'y_product' => array(self::BELONGS_TO, 'Product', 'get_y'),
            'shippings' => array(self::HAS_MANY, 'CampaignShipping', 'campaign_id'),            
        );
    }
    /**
     * Insert shippings, validation has to be done first before calling this method
     */
    public function insertShippings()
    {
        foreach ($this->shippings as $shipping){
            $shipping->id = null;//set id to null to have auto increment key
            $shipping->campaign_id = $this->id;
            $shipping->insert();
        } 
    }
    /**
     * Update shippings, validation has to be done first before calling this method
     */
    public function updateShippings()
    {
       $deleteExcludeList = new CList();
       foreach ($this->shippings as $shipping){
            $found = CampaignShipping::model()->findByPk($shipping->id);
            if ($found==null){//record not found
                $_s = new CampaignShipping();//$tier->id is auto incremented value in db
                $_s->campaign_id=$this->id;
                $_s->attributes = $shipping->getAttributes(array('shipping_id','surcharge'));
                $_s->insert();
                $deleteExcludeList->add($_s->id);
                logTrace('campaign product shipping created successfully',$_s->getAttributes());
            }
            else{
                $found->attributes = $shipping->getAttributes(array('shipping_id','surcharge'));
                $found->update();
                $deleteExcludeList->add($found->id);
                logTrace('campaign product shipping  updated successfully',$found->getAttributes());
            }
       } 
        $this->deleteShippings($deleteExcludeList->toArray());
    }
    
    public function deleteShippings($excludes=array()) 
    {
        $criteria = new CDbCriteria();
        $criteria->addColumnCondition(array('campaign_id'=>$this->id));
        if (empty($excludes)){
            CampaignShipping::model()->deleteAll($criteria);
        }
        else {
            $criteria->addNotInCondition('id',$excludes);
            logTrace('deleteShippings',$criteria);
            foreach (CampaignShipping::model()->findAll($criteria) as $unwanted){
               try {   //delete db record
                    logTrace('delete unwanted '.$unwanted->id,$unwanted->getAttributes());
                    $unwanted->delete();
                    
                } catch (CException $e) {
                    logTrace('unwanted delete error ',$e->getTrace());
                }
            }
        }
    }    
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(),array(
            'buy_x' => Sii::t('sii','Buy'),
            'buy_x_qty' => 'X Quantity',
            'get_y' => Sii::t('sii','Get'),
            'get_y_qty' => 'Y Quantity',
            'at_offer' => Sii::t('sii','Offer'),
            'offer_type' => Sii::t('sii','Offer type'),
            'status' => Sii::t('sii','Status'),
        ));
    }        

    public function getNameColumnData($locale=null) 
    {
        $list = new CMap();
        $imageData = $this->getImageData('/files/campaigns/',Image::DEFAULT_IMAGE_CAMPAIGN_BGA);
        $list->add($this->displayLanguageValue('name',$locale),array(
            'image'=>$imageData,
        ));
        return $list;
    } 
    
    public function getBuyXColumnData() 
    {
        $list = new CMap();
        $imageData = $this->getImageData('/files/products/',Image::DEFAULT_IMAGE_PRODUCT,$this->x_product);
        $list->add($this->x_product->displayLanguageValue('name',user()->getLocale()).' '.Helper::htmlColorText($this->x_product->getStatusText()),array(
            'image'=>$imageData,
            'quantity'=>$this->buy_x_qty,
        ));
        return $list;
    } 
    public function getGetYColumnData() 
    {
        $list = new CMap();
        if ($this->get_y!=null){
            $imageData = $this->getImageData('/files/products/',Image::DEFAULT_IMAGE_PRODUCT,$this->y_product);
            $list->add($this->y_product->displayLanguageValue('name',user()->getLocale()).' '.Helper::htmlColorText($this->y_product->getStatusText()),array(
                'image'=>$imageData,
                'quantity'=>$this->get_y_qty,
            ));
        }
        return $list;
    } 
    /*
     * Wrapper of CampaignManager::checkProductOfferPrice() without formatting
     */
    public function getOfferTotalPrice($quantity)
    {
        $xOfferTotal = $this->offerScenario==self::X_X_OFFER?$this->getUsualPrice($this->x_product,$quantity):$this->getOfferPrice($this->x_product,$quantity);
        return $xOfferTotal + ($this->hasG()?$this->getOfferPrice($this->y_product,$quantity):0);
    }
    /*
     * Wrapper of CampaignManager::checkProductOfferPrice() without formatting
     */
    public function getOfferPrice($product,$quantity=1)
    {
        if ($product instanceof Product)
            return Yii::app()->serviceManager->getCampaignManager()->checkProductOfferPrice($product,$quantity,$this,Helper::NO_FORMAT);
        else
            throw new CException(Sii::t('sii','Invalid campaign product'));
    }
    /*
     * Wrapper of CampaignManager::checkProductUsualPrice() without formatting
     */
    public function getUsualPrice($product,$quantity=1)
    {
        if ($product instanceof Product)
            return Yii::app()->serviceManager->getCampaignManager()->checkProductUsualPrice($product,$quantity,Helper::NO_FORMAT);
        else
            throw new CException(Sii::t('sii','Invalid campaign product'));
    }
    /**
     * Return short description of offer tag
     * @param type $color
     * @param mixed $returnType Default return array format consists of "text" and "color"; If value is true, then return string
     * @return type
     */
    public function getOfferTag($textOnly=false,$short=false,$locale=null,$color='orange')
    {
        if ($this->hasG()){
            if ($this->offerScenario==self::X_X_OFFER){
                    $text = Sii::tp('sii','Buy {x_quantity} Get {y_quantity} {offer}',
                                      array('{x_quantity}'=>$this->buy_x_qty,
                                            '{y_quantity}'=>$this->get_y_qty,
                                            '{offer}'=>$this->getCampaignText($locale,self::A)),$locale);
            }
            else
                $text = Sii::tp('sii','Buy {x_quantity} Get {y_quantity} Promotion Item',
                                      array('{x_quantity}'=>$this->buy_x_qty,
                                            '{y_quantity}'=>$this->get_y_qty,
                                            '{product}'=>$this->y_product->displayLanguageValue('name',$locale)),$locale).
                                  ' '.$this->getCampaignText($locale,self::A);
            if ($short)
                $text = str_replace('At ','',$this->getCampaignText($locale,self::A));
        }
        else {
            $text = str_replace('At ','',$this->getCampaignText($locale,self::A));
            if ($short===false)
                $text = Sii::tp('sii','Buy {x_quantity} Get ',array('{x_quantity}'=>$this->buy_x_qty),$locale).$text;
        }
        
        if ($textOnly)
            return $text;
        else
            return array('text' =>$text,'color'=>$color);
    }
    
    public function getCampaignText($locale=null,$text='verbose',$returnArray=false)
    {
        if ($text=='verbose'){
            $offer = new CMap();
            switch ($this->offer_type) {
                case Campaign::OFFER_FREE:
                    if ($this->hasG()){
                        $offer->add(self::B,Sii::tp('sii','Buy {quantity} {product}',array('{quantity}'=>$this->buy_x_qty,'{product}'=>$this->x_product->displayLanguageValue('name',$locale)),$locale));
                        $offer->add(self::G,Sii::tp('sii','Get {quantity} {product}',array('{quantity}'=>$this->get_y_qty,'{product}'=>$this->y_product->displayLanguageValue('name',$locale)),$locale));
                        $offer->add(self::A,Sii::tl('sii','Free',$locale));
                    }
                    else{
                        $offer->add(self::B,Sii::tp('sii','Free {product}',array('{product}'=>$this->x_product->displayLanguageValue('name',user()->getLocale()),$locale)));
                        $offer->add(self::A,Sii::tl('sii','Free',$locale));
                    }
                    break;
                case Campaign::OFFER_PERCENTAGE:
                    if ($this->hasG()){
                        $offer->add(self::B,Sii::tp('sii','Buy {quantity} {product}',array('{quantity}'=>$this->buy_x_qty,'{product}'=>$this->x_product->displayLanguageValue('name',$locale)),$locale));
                        $offer->add(self::G,Sii::tp('sii','Get {quantity} {product}',array('{quantity}'=>$this->get_y_qty,'{product}'=>$this->y_product->displayLanguageValue('name',$locale)),$locale));
                        $offer->add(self::A,Sii::tp('sii','At {offer}% off',array('{offer}'=>round($this->at_offer)),$locale));
                    }
                    else{
                        $offer->add(self::B,Sii::tp('sii','Buy {quantity} {product}',array('{quantity}'=>$this->buy_x_qty,'{product}'=>$this->x_product->displayLanguageValue('name',$locale)),$locale));
                        $offer->add(self::A,Sii::tp('sii','At {offer}% off',array('{offer}'=>round($this->at_offer)),$locale));
                    }
                    break;
                case Campaign::OFFER_AMOUNT:
                    if ($this->hasG()){
                        $offer->add(self::B,Sii::tp('sii','Buy {quantity} {product}',array('{quantity}'=>$this->buy_x_qty,'{product}'=>$this->x_product->displayLanguageValue('name',$locale)),$locale));
                        $offer->add(self::G,Sii::tp('sii','Get {quantity} {product}',array('{quantity}'=>$this->get_y_qty,'{product}'=>$this->y_product->displayLanguageValue('name',$locale)),$locale));
                        $offer->add(self::A,Sii::tp('sii','At {offer} off',array('{offer}'=>$this->formatCurrency(round($this->at_offer)),$locale)));
                    }
                    else{
                        $offer->add(self::B,Sii::tp('sii','Buy {quantity} {product}',array('{quantity}'=>$this->buy_x_qty,'{product}'=>$this->x_product->displayLanguageValue('name',$locale)),$locale));
                        $offer->add(self::A,Sii::tp('sii','At {offer} off',array('{offer}'=>$this->formatCurrency(round($this->at_offer)),$locale)));
                    }
                    break;
                default:
                    break;
            }
            if ($returnArray)
                return $offer;
            else 
                return $offer[self::B].(isset($offer[self::G])?' '.$offer[self::G]:'').(isset($offer[self::A])?' '.$offer[self::A]:'');
        }
        else {
            $offer = $this->getCampaignText($locale,'verbose',true);
            return isset($offer[$text])?$offer[$text]:'';
        }
    }
    
    public function forProduct($buy_x,$excludeCampaign=null) 
    {
        $condition = 'buy_x='.$buy_x; 
        if (isset($excludeCampaign))
            $condition .= ' AND id!='.$excludeCampaign; 
        $this->getDbCriteria()->mergeWith(array('condition'=>$condition));
        logTrace(__METHOD__.' criteria',$this->getDbCriteria());
        return $this;
    } 
    
    public function exceptOfferBuyXOnly() 
    {
        $condition = '(buy_x_qty>1 AND get_y IS NULL AND get_y_qty IS NULL AND get_y IS NULL) OR (get_y IS NOT NULL AND get_y_qty IS NOT NULL)';        
        $this->getDbCriteria()->mergeWith(array('condition'=>$condition));
        //logTrace(__METHOD__.' criteria',$this->getDbCriteria());
        return $this;
    } 
    
    public function onOfferBuyXOnly($buy_x) 
    {
        $condition = 'buy_x='.$buy_x.' AND buy_x_qty=1 AND get_y_qty IS NULL AND get_y IS NULL';        
        $this->getDbCriteria()->mergeWith(array('condition'=>$condition));
        //logTrace(__METHOD__.' criteria',$this->getDbCriteria());
        return $this;
    } 
    public function onOfferBuyXMore($buy_x) 
    {
        $condition = 'buy_x='.$buy_x.' AND buy_x_qty>1 AND get_y_qty IS NULL AND get_y IS NULL';        
        $this->getDbCriteria()->mergeWith(array('condition'=>$condition));
        logTrace(__METHOD__.' criteria',$this->getDbCriteria());
        return $this;
    } 
    public function onOfferBuyXGetX($buy_x) 
    {
        $condition = 'buy_x='.$buy_x.' AND get_y=buy_x AND get_y_qty IS NOT NULL';        
        $this->getDbCriteria()->mergeWith(array('condition'=>$condition));
        logTrace(__METHOD__.' criteria',$this->getDbCriteria());
        return $this;
    } 
    public function onOfferBuyXGetY($buy_x) 
    {
        $condition = 'buy_x='.$buy_x.' AND get_y_qty IS NOT NULL AND get_y IS NOT NULL';        
        $this->getDbCriteria()->mergeWith(array('condition'=>$condition));
        logTrace(__METHOD__.' criteria',$this->getDbCriteria());
        return $this;
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
        $criteria->compare('name',$this->name,true);
        $criteria->compare('description',$this->description,true);
        $criteria->compare('image',$this->image);
        $criteria->compare('shop_id',$this->shop_id);

        if (!empty($this->buy_x))
            $criteria->mergeWith($this->_getXCondition($this->buy_x));

        //$criteria->compare('buy_x_qty',$this->buy_x_qty);
        if (!empty($this->get_y))
            $criteria->mergeWith($this->_getYCondition($this->get_y));
        //$criteria->compare('get_y_qty',$this->get_y_qty);

        //$criteria->compare('at_offer',$this->at_offer,true);
        //$criteria->compare('offer_type',$this->offer_type,true);
        $criteria->compare('start_date',$this->start_date,true);
        $criteria->compare('end_date',$this->end_date,true);
        $criteria->compare('status',$this->status,true);
        $criteria->compare('create_time',$this->create_time);
        $criteria->compare('update_time',$this->update_time);

        if ($this->getScenario()=='activate')
            $criteria->compare('status',Process::CAMPAIGN_OFFLINE);

        if ($this->getScenario()=='deactivate')
            $criteria->compare('status',Process::CAMPAIGN_ONLINE);

        $criteria->mergeWith($this->mine()->getDbCriteria());

        logTrace(__METHOD__.' criteria',$criteria);

        return new CActiveDataProvider(
                                $this,
                                array(
                                    'criteria'=>$criteria,
                                    'pagination'=>array('pageSize'=>Config::getSystemSetting('record_per_page')),
                                ));
    }
    
    public function searchShippings()
    {
       return new CActiveDataProvider(CampaignShipping::model(),
                                    array('criteria'=>array(
                                              'condition'=>'campaign_id='.$this->id,
                                              'order'=>'update_time DESC',
                                            ),
                                          'pagination'=>array('pageSize'=>Config::getSystemSetting('record_per_page')),
                                          'sort'=>false,
                                    ));
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

    private function _getXCondition($input)
    {
        if (empty($input))
            return null;
        $x = new CList();
        $p_criteria=new CDbCriteria;
        $p_criteria->select='id';
        $p_criteria->compare('name',$input,true);//product name
        $p_criteria->mergeWith(Product::model()->mine()->getDbCriteria());
        logTrace(__METHOD__,$p_criteria);
        $products = Product::model()->findAll($p_criteria);
        foreach ($products as $product)
            $x->add($product->id); 

        $criteria=new CDbCriteria(); 
        $criteria->addCondition(QueryHelper::constructInCondition('buy_x',$x),'OR');
        $criteria->addColumnCondition(array('buy_x_qty'=>$input),'AND','OR');
        return $criteria;
    }        
    private function _getYCondition($input)
    {
        if (empty($input))
            return null;
        $y = new CList();
        $p_criteria=new CDbCriteria;
        $p_criteria->select='id';
        $p_criteria->compare('name',$input,true);//product name
        $p_criteria->mergeWith(Product::model()->mine()->getDbCriteria());
        logTrace(__METHOD__,$p_criteria);
        $products = Product::model()->findAll($p_criteria);
        foreach ($products as $product)
            $y->add($product->id); 

        $criteria=new CDbCriteria(); 
        $criteria->addCondition(QueryHelper::constructInCondition('get_y',$y),'OR');
        $criteria->addColumnCondition(array('get_y_qty'=>$input),'AND','OR');
        if (preg_match('/free/i', $input)==1){
            logTrace('preg_match "/free/i", input='.$input);
            $criteria->addColumnCondition(array('at_offer'=>0),'AND','OR');
        }
        else
            $criteria->addColumnCondition(array('at_offer'=>$input),'AND','OR');

        return $criteria;
    }     
    /**
     * Url to view this model
     * @return string url
     */
    public function getViewUrl()
    {
        return url('campaign/bga/view/'.$this->id);
    }
    /**
     * This is public accessible url
     * @return type
     */
    public function getUrl($secure=false)
    {
        $translate = array(" " => "_", "%" => "_Percent");
        $route = '/promotions/'.strtr($this->getOfferTag(true),$translate).'/'.rtrim(base64_encode($this->id),'=');//triming away "=" sign will not affect base64 decode
        return $this->shop->getUrl($secure).$route;
    }  
    
    public function getReturnUrl()
    {
        return $this->url;
    }        
    /**
     * @override
     * Custom url to work on task for this model
     * 
     * @see urlManager for mapping (main.php)
     * @see Transitionable::getTaskUrl()
     * @return string url
     */
    public function getTaskUrl($action)
    {
        return url('tasks/campaign/bga/'.strtolower($action));
    }  
    
    public function getType() 
    {
        return $this->displayName();
    }

    public function getTypeColor() 
    {
        return 'skyblue';
    }

    public function getOfferTypes() 
    {
        return $this->getOfferTypeList(array(
            Campaign::OFFER_FREE,
            Campaign::OFFER_PERCENTAGE,
            Campaign::OFFER_AMOUNT,
         ));
    } 
    
    public function scaleQuantityYByX($qtyX)
    {
        if ($qtyX > $this->buy_x_qty)
            return $this->get_y_qty * ($qtyX / $this->buy_x_qty);
        else
            return $this->get_y_qty;//min qty to return
    }

}