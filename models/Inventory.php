<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.modules.inventories.behaviors.InventoryBehavior");
Yii::import("common.components.validators.CompositeUniqueKeyValidator");
/**
 * This is the model class for table "s_inventory".
 *
 * The followings are the available columns in table 's_inventory':
 * @property integer $id
 * @property integer $account_id
 * @property integer $shop_id
 * @property string $obj_type
 * @property integer $obj_id
 * @property string $sku
 * format=[ObjectCode]+[AttributeCode+OptionCode][2][3]..[n]; 
 * length 30 supports up to n=6 attributes (total length 6 + 6x4 = 30)
 *
 * @property integer $quantity
 * @property integer $available
 * @property integer $hold
 * @property integer $sold
 * @property integer $bad
 * @property integer $create_time
 * @property integer $update_time
 *  
 * The followings are the available model relations:
 * @property Account $account
 * @property Product $product
 *
 * @author kwlok
 */
class Inventory extends SActiveRecord
{
    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return ProductInventory the static model class
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
        return Sii::t('sii','Inventory|Inventories',[$mode]);
    }  
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 's_inventory';
    }
    /**
     * Behaviors for this model
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class'=>'common.components.behaviors.TimestampBehavior',
            ],
            'account' => [
                'class'=>'common.components.behaviors.AccountBehavior',
            ],
            //rule: record history when there is a change on 'available' field
            'history' => [
                'class'=>'common.components.behaviors.HistoryBehavior',
                'model'=>'InventoryHistory',
            ],
            'merchant' => [
                'class'=>'common.components.behaviors.MerchantBehavior',
            ], 
            'activity' => [
                'class'=>'common.modules.activities.behaviors.ActivityBehavior',
                'descriptionAttribute'=>'sku',
                'iconUrlSource'=>'source',
            ],
            'locale' => [
                'class'=>'common.components.behaviors.LocaleBehavior',
            ],   
            'inventorybehavior' => [
                'class'=>'InventoryBehavior',
            ],      
            'multilang' => [//reserve for future use
                'class'=>'common.components.behaviors.LanguageBehavior',
            ],                      
            
       ];
    }
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            ['account_id, shop_id, obj_type, obj_id, sku', 'required'],
            ['account_id, shop_id, obj_id, hold, sold, bad', 'numerical', 'integerOnly'=>true],
            ['quantity', 'numerical','integerOnly'=>true, 'min'=>0,'allowEmpty'=>false],//cannot below zero and cannot empty
            ['available', 'numerical','integerOnly'=>true, 'min'=>0],//cannot below zero
            ['sku', 'length', 'max'=>30],
            ['sku', 'CompositeUniqueKeyValidator','keyColumns'=>'shop_id, sku','errorMessage'=>Sii::t('sii','SKU already exists'),'on'=>'create'],
            // validate field 'shop_id' to make sure product has correct shop
            ['obj_id', 'ruleShopCheck'],

            // validate field 'sku' to make sure inventory sku has all product attributes 
            ['sku', 'ruleSKU','params'=>[]],

            //on order scenario
            //validate field 'hold' to make sure product has sufficient hold stock
            ['hold', 'ruleInventoryHoldCheck','params'=>[],'on'=>Process::ORDERED],

            //on delete scenario, id field here as dummy
            ['id', 'ruleAssociations','params'=>[],'on'=>'delete'],

            ['id, account_id, shop_id, obj_id, obj_type, sku, quantity, available, hold, sold, bad, create_time, update_time', 'safe', 'on'=>'search'],
        ];
    }
    /**
     * Verify if Inventory SKU has all product attributes in it.
     * (1) All product attributes defined are mandatory
     * (2) Validate attribute options in sku are valid attributes
     * TODO: Can add feature to support which attribute is 'SKU-able"
     */
    public function ruleSKU($attribute,$params)
    {
        //validate if there is product
        if ($this->getSource() != null){
            $productAttrs = $this->getSource()->attrs;
            $original_count = count($productAttrs);
            if ($original_count != (count(explode(Helper::DASH_SEPARATOR, $this->sku)) - 1))//excluding product code itself
               $this->addError('sku',Sii::t('sii','SKU must contain all attrbutes'));

            $count=0;
            foreach ($productAttrs as $attr)
               if ($attr->existsSKU($this->sku))
                   $count++;

            if ($original_count != $count)
               $this->addError('sku',Sii::t('sii','SKU contains invalid attrbute'));
        }
    }
    /**
     * Verify if Inventory has any other associations
     * Possible associations
     * (1) Still has stocks - available > 0
     * (2) Has unfulfilled orders (quick way to check) - hold > 0
     */
    public function ruleAssociations($attribute,$params)
    {
        if(!($this->available==0 && $this->hold==0)) {
           $message = Sii::t('sii','SKU "{sku}" has either non-zero stocks or unfulfilled orders.',['{sku}'=>$this->sku]);
           $message .= '<br>'.Sii::t('sii','Please zerorize its stocks or fulfill its outstanding orders if you wish to delete this inventory');
           $this->addError('id',$message);
        }
    }
    /**
     * Verify stock level
     */
    public function ruleInventoryHoldCheck($attribute,$params)
    {
        if ($this->hold <= 0)
            $this->addError('hold',Sii::t('sii','No hold stock'));

        //below is unlikely to happen; if happen must be system bug
        if ($this->hold > $this->quantity){
            $this->addError('hold',Sii::t('sii','Hold stock overflow'));
            logError(__METHOD__,$this->getErrors());
        }
    }
    /**
     * Verify shop
     */
    public function ruleShopCheck($attribute,$params)
    {
        if ($this->obj_type!='s_system'){
            $type = SActiveRecord::resolveTablename($this->obj_type);
            $model = $type::model()->findByPk($this->obj_id);
            if ($model===null)
                $this->addError('obj_id',Sii::t('sii','Unknown class'));
            else 
                if($this->shop_id != $model->shop->id) 
                    $this->addError('obj_id',Sii::t('sii','Unmatched shop'));
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
        ];
    }
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return [
            'id' => Sii::t('sii','ID'),
            'account_id' => Sii::t('sii','Account'),
            'shop_id' => Sii::t('sii','Shop'),
            'obj_type' => 'Obj Type',
            'obj_id' => Sii::t('sii','Product'),//for now default to product; generic should be any obj
            'sku' => Sii::t('sii','SKU'),
            'quantity' => Sii::t('sii','Quantity'),
            'available' => Sii::t('sii','Available'),
            'hold' => Sii::t('sii','Hold'),
            'sold' => Sii::t('sii','Sold'),
            'bad' => Sii::t('sii','Bad'),
            'create_time' => Sii::t('sii','Create Time'),
            'update_time' => Sii::t('sii','Update Time'),
        ];
    }
    /**
     * Save initial inventory history
     */
    public function saveInitialHistory($movement)
    {
        $this->recordHistory([
            'inventory_id'=>$this->id,
            'description'=>'INITIAL STOCK OF SKU '.$this->sku,
            'type'=>InventoryHistory::TYPE_INFLOW,
            'movement'=>$movement,
            'post_available'=>$this->available,
            'post_quantity'=>$this->quantity,
            'create_by'=>$this->account_id,
        ]);                
    }
    /**
     * A wrapper method to return low stock records
     * @see Definition of low stock is based on shop setting lowInventoryThreshold
     * @return \Inventory
     */
    public function lowStock($threshold) 
    {
        $this->getDbCriteria()->mergeWith([
            'condition'=>'(available/quantity) <= '.$threshold.' AND available > 0',
        ]);
        return $this;
    }    
    /**
     * A wrapper method to return out of stock records
     * @return \Inventory
     */
    public function outOfStock() 
    {
        $this->getDbCriteria()->mergeWith([
            'condition'=>'available=0',
        ]);
        return $this;
    }    
    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search()
    {
        $criteria=new CDbCriteria;

        //$criteria->compare('id',$this->id);
        if (!empty($this->id))//used id as proxy to search by product names
            $criteria->addCondition($this->_getProductCondition($this->id));

        //for now default to search on product obj_type only
        $criteria->addColumnCondition(['obj_type'=>Product::model()->tableName()]);

        $criteria->compare('account_id',$this->account_id);
        $criteria->compare('shop_id',$this->shop_id);
        $criteria->compare('obj_id',$this->obj_id);
        $criteria->compare('sku',$this->sku,true);
        $criteria->compare('quantity',$this->quantity);
        $criteria->compare('available',$this->available);
        $criteria->compare('hold',$this->hold);
        $criteria->compare('sold',$this->sold);
        $criteria->compare('bad',$this->bad);
        $criteria->compare('create_time',$this->create_time);
        $criteria->compare('update_time',$this->update_time);

        $criteria->mergeWith($this->mine()->getDbCriteria());

        logTrace('Inventory.search',$criteria);

        return new CActiveDataProvider($this, [
            'criteria'=>$criteria,
            'pagination'=>['pageSize'=>Config::getSystemSetting('record_per_page')],
        ]);
    }

    private function _getProductCondition($input)
    {
        if (empty($input))
            return null;
        $inventories = new CList();
        $criteria=new CDbCriteria;
        $criteria->select='id';
        $criteria->compare('name',$input,true);//product name
        $criteria->mergeWith(Product::model()->mine()->getDbCriteria());
        //logTrace(__METHOD__,$criteria);
        $products = Product::model()->findAll($criteria);
        foreach ($products as $product)
            $inventories->add($product->id); 
        return QueryHelper::constructInCondition('obj_id',$inventories);
    }        


    public function getSource()
    {
        if ($this->obj_type==Product::model()->tableName()){
            return Product::model()->findByPk($this->obj_id);
        }
        else
            return null;
    }         
    /**
     * Return products that have no inventory defined yet
     * @param type $locale
     * @param type $shop
     * @return type
     */
    public static function getItemCandidates($locale,$shop=null)
    {
        $finder = Product::model()->mine();
        if ($shop!=null)
            $finder = $finder->locateShop($shop);

        $criteria=new CDbCriteria;
        $criteria->select = 'id, code, name, shop_id';
        $criteria->order  = 'name ASC';
        $dataprovider = new CActiveDataProvider($finder,[
            'criteria'=>$criteria,
            'pagination'=>['pageSize'=>100],//set to 100 for the time being
            'sort'=>false,
        ]);
        $list = new CMap();
        foreach($dataprovider->data as $data){
            logTrace(__METHOD__,$data->attributes);
            if (!$data->hasInventory())
                $list->add($data->id,$data->displayLanguageValue('name',$locale));
        }
        return $list->toArray();
    } 

    public static function getDisplayText($available,$html=true)
    {
        if ($available>0){
            $rawInfo = Process::getDisplayTextWithColor(Process::PICKED_ACCEPT);
            $info = ['text'=>Sii::t('sii','{available} In Stock',['{available}'=>$available]),'color'=>$rawInfo['color']];
            if ($html)
                $info = Helper::htmlColorText($info,true,true);
        }
        else {
            $rawInfo = Process::getDisplayTextWithColor(Process::PICKED_REJECT);
            $info = ['text'=>Process::getDisplayText(Sii::t('sii','Sold Out')),'color'=>$rawInfo['color']];
            if ($html)
                $info = Helper::htmlColorText($info);
        }
        return $info;
    } 
    /**
     * Find inventory by product id and sku
     * 
     * @param type $pid
     * @param type $sku
     * @return type
     */
    public static function getAvailableByProduct($pid,$sku=null)
    {
        $criteria = new CDbCriteria();
        $criteria->addColumnCondition(['obj_type'=>Product::model()->tableName()]);
        $criteria->addColumnCondition(['obj_id'=>$pid]);
        if ($sku!=null)
            $criteria->addColumnCondition(['sku'=>$sku]);
        $inventories = Inventory::model()->findAll($criteria);
        $totalAvailable = 0;
        foreach ($inventories as $inventory) {
            $totalAvailable += $inventory->available;
        }
        return $totalAvailable;
    }
    /**
     * Format SKU
     * @param type $productCode
     * @param mixed $productOptions array of encoded option values
     *              encoding: <attr code> + <attr name> + <option code> + <option name> + <surcharge> (optional)
     * @see ProductAttributeOption::encodeOption() for how option values is formatted 
     */
    public static function formatSKU($productCode,$productOptions=null)
    {
        $sku = $productCode;
        if (isset($productOptions)){
            foreach ($productOptions as $key => $value){
                $opt = explode(Helper::PIPE_SEPARATOR, $value);
                //$opt[0] is attribute code 
                //$opt[2] is option code 
                logTrace(__METHOD__.' decode options',$opt);
                $sku .= Helper::DASH_SEPARATOR.$opt[0].$opt[2];//target option value
            }
        }
        return $sku;
    }
    /**
     * Format SKU (method 2 with different input format)
     * @param type $productCode
     * @param mixed $optionCodes array ( <attr code> => <option code> )
     */
    public static function formatSKU2($productCode,$optionCodes=[])
    {
        $sku = $productCode;
        foreach ($optionCodes as $attrCode => $optionCode){
            $sku .= Helper::DASH_SEPARATOR.$attrCode.$optionCode;
        }
        return $sku;
    }    
    /**
     * Find inventory by product id and product options
     * 
     * @param type $pid
     * @param mixed $options array of encoded option values
     *              encoding: <attr code> + <attr name> + <option code> + <option name> + <surcharge> (optional)
     * @param type $field Supported field 'sku','available'
     * @see ProductAttributeOption::encodeOption() for how option values is formatted 
     * @return array 
     */
    public static function getInfoByProductOptions($pid,$options,$field)
    {
        if (!in_array($field, ['sku','available']))
            throw new CException(Sii::t('sii','Unsupported inventory field info'));
                
        $criteria = new CDbCriteria();
        $criteria->addColumnCondition(['obj_type'=>Product::model()->tableName()]);
        $criteria->addColumnCondition(['obj_id'=>$pid]);
        $inventories = self::model()->findAll($criteria);
        $info = 0;//default to zero to support available return value when inventory not found
        foreach ($inventories as $inventory) {
            $regex = self::_formatSKURegex($inventory->sku);
            $i=0;
            foreach($options as $option){
                $opt = explode(Helper::PIPE_SEPARATOR, $option);
                $skuToken = $opt[0].$opt[2];
                if (preg_match($regex, $skuToken)==1){
                    $i++;
                    logTrace(__METHOD__.' '.$skuToken.' matched '.$i.' of '.count($options).'!');
                }
            }
            if ($i==count($options)){
                //all $option inside array match inventory sku 
                if ($field=='available')
                    $info += $inventory->available;
                if ($field=='sku'){
                    $info = $inventory->sku;
                    break;
                }
            }
        }
        return $info;
    }     

    private static function _formatSKURegex($sku)
    {
        $regex = '/(';
        $options = explode(Helper::DASH_SEPARATOR, $sku);
        $cnt =0;
        foreach ($options as $option) {
            $regex .= '\b'.$option.'\b';
            $cnt++;
            if ($cnt<count($options))
                $regex .= Helper::PIPE_SEPARATOR;
        }
        $regex .= ')/';
        logTrace(__METHOD__.' '.$regex);        
        return $regex;
    }

    /**
     * Search inventory by SKU
     * Note that SKU is not unique to search by itself alone
     * 
     * @param integer $pid Product id
     * @param type $sku
     * @return Inventory
     */
    public static function findBySKU($pid,$sku)
    {
        $criteria = new CDbCriteria();
        $criteria->addColumnCondition(['obj_type'=>Product::model()->tableName()]);
        $criteria->addColumnCondition(['obj_id'=>$pid]);
        $criteria->addColumnCondition(['sku'=>$sku]);
        return self::model()->find($criteria);
    }  

    public function searchHistories()
    {
        $criteria=new CDbCriteria;
        $criteria->order = 'create_time DESC';
        $criteria->addColumnCondition(['inventory_id'=>$this->id]);
        return new CActiveDataProvider('InventoryHistory', [
            'criteria'=>$criteria,
        ]);
    }

    public static function lookupSKUOption($pid,$option,$sku=null)
    {
        $criteria = new CDbCriteria();
        $criteria->addColumnCondition(['obj_type'=>Product::model()->tableName()]);
        $criteria->addColumnCondition(['obj_id'=>$pid]);
        $inventories = self::model()->findAll($criteria);
        if ($sku===null){
            foreach ($inventories as $inventory) {
                $opt = explode(Helper::DASH_SEPARATOR, $inventory->sku);
                foreach($opt as $value){
                    if ($value==$option)
                    return true;
                }
            }
        }
        else {
            $opt = explode(Helper::DASH_SEPARATOR, $sku);
            foreach($opt as $value){
                if ($value==$option)
                return true;
            }
        }
        return false;
    }
    /**
     * Url to view this model
     * @return string url
     */
    public function getViewUrl($domain=null)
    {
        if (isset($domain))
            return app()->urlManager->createDomainUrl($domain,$this->getViewRoute(),true);
        else
            return url($this->getViewRoute());//$route cannot start with "/" else host info not following current scheme
    }    
    /**
     * This is called by Notification template "lowstock.php"
     */   
    public function getViewRoute()
    {
        return 'product/inventory/view/'.$this->id;
    }
    
    const TAKE_OUT    = 1;
    const PUT_BACK    = 2;
    const MARK_AS_BAD = 3;
    /**
     * This is used to handle item return
     * @param type $method
     * @return type
     */
    public static function getHandlingMethods($method=null) 
    {
        if (!isset($method)){
            return [
                Inventory::TAKE_OUT=>Sii::t('sii','Take out from inventory'),
                Inventory::PUT_BACK=>Sii::t('sii','Put back inventory'),
                Inventory::MARK_AS_BAD=>Sii::t('sii','Mark as bad inventory'),
            ];
        }
        else {
            $methods = self::getHandlingMethods();
            return $methods[$method];
        }
    }    
        
}