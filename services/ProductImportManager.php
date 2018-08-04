<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.extensions.sphpspreadsheet.SPhpSpreadsheet');
Yii::import('common.modules.products.models.ProductImport');
/**
 * Description of ProductImportManager
 * Note: This is a standalone manager and not a child class of ServiceManager
 * 
 * @author kwlok
 */
class ProductImportManager extends CApplicationComponent
{
    /*
     * Maximum number of products allowed to be imported per file
     */
    public static $maximumImport = 100;
    /*
     * The number of rows reserved for headers; Default to 2 
     * First two rows are not used as produt records
     */
    public $headerRows = 2;
    /*
     * The basepath of download files (for guide and template)
     */
    public static $downloadBasepath = 'files';
    /*
     * Internal private properties
     */
    private $_a;//the account id owns the products
    private $_s;//the shop id owns the products
    private $_f;//import file to be processed
    private $_p;//excel parser
    private $_r;//the imported raw data 
    private $_d;//the imported product data 
    private $_e;//the imported product has error 
    /**
     * Constructor
     * @param type $account The account id
     * @param type $shop The shop id
     * @param type $file import file to be processed
     */
    public function __construct($account,$shop,$file) 
    {
        $this->_a = $account;
        $this->_s = $shop;
        $this->_f = $file;
        $this->_p = new SPhpSpreadsheet();
        $this->_p->init();        
        $this->getData();
    }
    /**
     * Run import (assume all validations are completed)
     * Import has its own transaction management 
     */
    public function run()
    {
        $transaction = Yii::app()->db->beginTransaction();        
        try {
            
            foreach ($this->data as $index => $row) {
                $productRow = new ProductImportRow($this->account, $this->shop, $row, $index);
                $productRow->import();
            }

            //[7] create product import record and activity
            $import = new ProductImport();
            $import->account_id = $this->account;
            $import->shop_id = $this->shop;
            $import->summary = json_encode(array(
                'total_count'=>$this->count,
            ));
            $import->save();
            $import->recordActivity(array(
                'event'=>Activity::EVENT_IMPORT,
                'description'=>$import->shop->serializeLanguageValue('Poduct Import ({count})',array('{count}'=>$this->count)),
            ));
            
            $transaction->commit();
            
            logInfo(__METHOD__.' ok');
            
            return $import;
            
        } catch (CException $e) {
            $transaction->rollback();
            logError(__METHOD__.' rollback: '.$e->getTraceAsString());
            throw new CException($e->getMessage());
        }        
    }
    /**
     * @return Account who owns the products to be imported
     */    
    public function getAccount()
    {
        return $this->_a;
    }    
    /**
     * @return Shop which owns the products to be imported
     */    
    public function getShop()
    {
        return $this->_s;
    }
    /**
     * @return File to be processed for import
     */    
    public function getFile()
    {
        return $this->_f;
    }
    /**
     * @return Spreadsheet Parser
     * @see common.extensions.sphpspreadsheet.SPhpSpreadsheet
     */
    public function getFileParser()
    {
        return $this->_p;
    }  
    /**
     * Example:
     *   echo dump($productImport->getData(0));
     *   echo dump($productImport->getData(1));
     *   echo $productImport->getData(1,ProductImport::$productName_en_sg);
     *
     * @return array Get imported data read from Excel file (excluding header rows)
     */       
    public function getData($row=null,$col=null)
    {
        try {
            if (!isset($this->_d)){
                $this->_r = $this->fileParser->readSpreadsheet($this->file);
                $this->_d = $this->_r;
                //remove header rows
                for ($i = 0; $i < $this->headerRows; $i++) {
                    array_shift($this->_d);//shift header row out from array
                }
            }        

            if ($row===null)
                return $this->_d;
            elseif ($col===null)
                return $this->_d[$row];
            else 
                return $this->_d[$row][$col];
            
        } catch (CException $ex) {
            throw new CException($ex->getMessage());
        }
    }
    /**
     * Return raw data
     * @return type
     */
    public function getRawData()
    {
        return $this->_r;
    }
    /**
     * Print imported raw data in the Excel file after reading 
     * @return string
     */
    public function getPreviewDataProvider()
    {
        $previewData = [];
        foreach ($this->rawData as $index => $row) {
            if ($index > $this->headerRows){//skip header rows
                $productRow = new ProductImportRow($this->account, $this->shop, $row, $index - $this->headerRows);
                $productRow->validateRow();
                //insert missing columns (as a parsing result from SPhpSpreadsheet) with null value
                foreach (ProductImportColumn::getAllColumns() as $column) {
                    if (!isset($row[$column])){
                        if ($column==ProductImportColumn::$productErrors) {
                            //logTrace(__METHOD__." row[$index]: insert error status column");
                            if ($productRow->hasErrors()){
                                $this->addError($productRow->rowId,$productRow->rowErrors);
                                $productRow->setColumnValue(ProductImportColumn::$productErrors, ProductImportColumn::getErrorIcon());
                            } else {
                                $productRow->setColumnValue(ProductImportColumn::$productErrors, ProductImportColumn::getOKIcon());
                            }
                        } 
                        else {
                            //logTrace(__METHOD__." row[$index]: insert missing column $column");
                            $productRow->setColumnValue($column, null);
                        }
                    }
                    elseif (isset($row[$column]) && $column==ProductImportColumn::$productImages){
                        $productRow->setColumnValue($column, $productRow->previewImages);
                    }
                }
                $previewData[] = Helper::sortArrayByKey($productRow->row);//do a column alphabetical sort (A-Z)
                if ($productRow->hasErrors()){
                    //insert validation error as a new row with single column 
                    //UI display is handled at javascript - products.js previewproductimport()
                    $previewData[] = array(
                        ProductImportColumn::$productErrors=>$this->getErrors($productRow->rowId),
                    );
                } 
            }
        }
        return new CArrayDataProvider($previewData,array('keyField'=>false));        
    }     
    
    public function getHasErrors()
    {
        return $this->_e!=null && count($this->errors)>0;
    }    
    
    public function getErrors($key=null)
    {
        if (!isset($this->_e))
            return [];//empty array
        
        if (isset($key))
            return $this->_e->itemAt($key);
        else
            return $this->_e->toArray();
    }
    
    public function addError($productCode,$errors)
    {
        if (!isset($this->_e)){
            $this->_e = new CMap();
        }
        $this->_e->add($productCode, $errors);
    }      
    /**
     * @return int number of product to be imported (header rows are not counted)
     */       
    public function getCount()
    {
        return count($this->data);
    }
    
    public static function getImportGuideLink()
    {
        $file = array(
            'name'=>Sii::t('sii','Instructions and Sample'),
            'download'=>Config::getSystemSetting('product_import_guide'),
            'url'=>url(self::$downloadBasepath.'/'.Config::getSystemSetting('product_import_guide')),
        );
        return Helper::htmlDownloadLink($file);
    }
    public static function getImportTemplateLink()
    {
        $file = array(
            'name'=>Sii::t('sii','File Template'),
            'download'=>Config::getSystemSetting('product_import_template'),
            'url'=>url(self::$downloadBasepath.'/'.Config::getSystemSetting('product_import_template')),
        );
        return Helper::htmlDownloadLink($file);
    }
}
/**
 * Description of ProductImportRow
 * ProductImportRow is implemented as a child class of ProductForm mainly to leverage on its validation framework.
 * 
 * @author kwlok
 */
class ProductImportRow extends ProductForm
{
    /*
     * Maximum number of images allowed to be imported per product
     */
    public $imagesLimit = 3;
    /**
     * Inherited attributes to be excluded and not applicable
     * @see LanguageForm
     */
    protected $exclusionAttributes = array('categories','productAttributes');//so that to skip validation, and let validation done at validateChildForms
    protected $persistentAttributes = array('categories','shippings','shop_id','code');
    /*
     * Local attributes
     */
    public $quantity;
    public $brand_name;
    public $productAttributes = array();
    /*
     * Private property
     */
    private $_n;//imported row number
    private $_r;//imported row record
    /**
     * Constructor.
     * @param string $scenario name of the scenario that this model is used in.
     * See {@link CModel::scenario} on how scenario is used by models.
     * @see getScenario
     */
    public function __construct($account_id,$shop_id,$row,$rowNum,$scenario='')
    {
        $this->imagesLimit = Config::getBusinessSetting('limit_product_image');
        $this->_r = $row;
        $this->_n = $rowNum;
        //---------------------------
        //Start setting column values
        $this->account_id = $account_id;
        $this->shop_id = $shop_id;
        $this->code = $this->_parseColumn(ProductImportColumn::$productCode);
        $this->name = $this->_formatLanguageAttribute('productName');
        $this->spec = $this->_formatLanguageAttribute('productSpec');
        $this->unit_price = $this->_parseColumn(ProductImportColumn::$productPrice);
        $this->slug = $this->_parseColumn(ProductImportColumn::$productUrlSlug);
        $this->weight = $this->_parseColumn(ProductImportColumn::$productWeight);
        $this->image = $this->_parseColumn(ProductImportColumn::$productImages);
        $this->brand_name = $this->_parseColumn(ProductImportColumn::$productBrand);
        $this->categories = $this->_parseColumnAsArray(ProductImportColumn::$productCategories);
        $this->shippings = $this->_parseColumnAsArray(ProductImportColumn::$productShipping);
        $this->status = $this->_parseColumn(ProductImportColumn::$productStatus,Process::NO);
        $this->quantity = $this->_parseColumn(ProductImportColumn::$productQuantity);
        //setting attribute column values
        foreach (ProductImportAttributeColumn::getArray() as $attribute) {
            if ($this->_hasProductAttributeColumn($attribute))
                $this->productAttributes[] = $this->_parseProductAttributeColumn($attribute);
        }
        //---------------------------//
        parent::__construct(null,$scenario);
    }
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(),array(
            'quantity'=>Sii::t('sii','Quantity'),
        ));
    }          
    /**
     * Validation rules for locale attributes
     * 
     * Note: that all different locale values of one attributes are to be stored in db table column
     * Hence, model attribute (table column) wil have separate validation rules following underlying table definition
     * 
     * @return array validation rules for locale attributes.
     */
    public function rules()
    {
        return array_merge(array(
            array('code', 'ruleCompositeUniqueKey','errorMessage'=>Sii::t('sii','Code is already taken')),
            array('image', 'ruleImages'),
            array('brand_name', 'length', 'max'=>100),
            array('brand_name', 'ruleExists'),
            array('quantity', 'required'),
            array('quantity', 'ruleQuantity'),
            array('status', 'ruleActivation'),
            //below rules are not called as already exluded from $exclusionAttributes
            //let validation done at validateChildForms
            array('categories', 'ruleCategories'),
            array('productAttributes', 'ruleProductAttributes'),
        ),parent::rules());
    }     
    /**
     * Verify product url slug uniqueness
     */
    public function ruleSlugUnique($attribute,$params)
    {
        $this->setScenario('create');//follow the setup in ProductForm
        parent::ruleSlugUnique($attribute, $params);
    }    
    /**
     * Verify product quantity
     */
    public function ruleQuantity($attribute,$params)
    {
        $checkQuantity = function ($value,$sku=null){
            if (!Helper::isInteger($value)){
                return Sii::t('sii','"{SKU}" Quantity "{value}" is not a number.',array('{SKU}'=>$sku,'{value}'=>$value));
            }
            else {
                if ($value<1){
                    return Sii::t('sii','"{SKU}" Quantity "{value}" must be equal or greater than 1.',array('{SKU}'=>$sku,'{value}'=>$value));
                }
                else
                    return false;
            }
        };
        
        if (count($this->productAttributes)>0){//has product attribute
            $errors = new CList();
            $data = explode(ProductImportColumn::$delimiter, $this->quantity);
            foreach ($data as $key => $value) {
                $quantity = explode(ProductImportColumn::$quantitySeparator, $value);
                //format: '<option code 1>-<option code 2>:<quantity>',
                if (isset($quantity[0]) && !isset($quantity[1]) ||
                    isset($quantity[0]) && empty($quantity[1]))
                    $errors->add(Sii::t('sii','"{SKU}" Quantity cannot be blank.',array('{SKU}'=>$quantity[0])));
                if (isset($quantity[0])){
                    $message = $checkQuantity($quantity[1],$quantity[0]);
                    if ($message!=false)
                        $errors->add($message);
                }
            }
            if ($errors->count>0)
                $this->addError($attribute,Helper::htmlList($errors->toArray(),array('style'=>'margin:0px;padding:0px;list-style:none;')));
        }
        else {
            $message = $checkQuantity($this->quantity,$this->code);
            if ($message!=false)
                $this->addError($attribute,$message);
        }
    }    
    /**
     * @see ProductForm validation rules "CompositeUniqueKeyValidator"
     */
    public function ruleCompositeUniqueKey($attribute,$params)
    {
        $this->setScenario('create');//follow the setup in ProductForm
        parent::ruleCompositeUniqueKey($attribute, $params);
    }    
    /**
     * Validate for images
     */
    public function ruleImages($attribute,$params)
    {
        if (!empty($this->$attribute)){
            $errors = new CList();
            $images = $this->_parseColumnAsArray(ProductImportColumn::$productImages);
            //logTrace(__METHOD__.' array',$images);
            
            if (count($images) > $this->imagesLimit){
                $errors->add(Sii::t('sii','Maximum {max} images only are allowed per product.',array('{max}'=>$this->imagesLimit)));
            }
            
            $urlValidator = new CUrlValidator();
            foreach ($images as $index => $image) {
                if ($this->isMediaImage($image)){
                    $mediaModel = Media::model()->findByPk($image);
                    if ($mediaModel==null){
                        $errors->add(Sii::t('sii','{attribute} "{value}" not found.',array('{attribute}'=>Media::model()->displayName(),'{value}'=>$image)));
                    }
                    elseif ($mediaModel!=null && !$mediaModel->isImage){
                        $errors->add(Sii::t('sii','Media "{value}" is not an image.',array('{value}'=>$image)));
                    }
                }
                else {
                    if (!$urlValidator->validateValue($image))
                        $errors->add(Sii::t('sii','Image "{value}" is not a valid url.',array('{value}'=>$image)));
                }
            }
            if ($errors->count>0)
                $this->addError($attribute,Helper::htmlList($errors->toArray(),array('style'=>'margin:0px;padding:0px;list-style:none;')));
             
        }
    }    
    /**
     * Validate if attribute exisits
     */
    public function ruleExists($attribute,$params)
    {
        $checkExists = function ($class,$value) use ($attribute) {
            if ($this->findModelByName($class, $value)==false)
                $this->addError($attribute,Sii::t('sii','{attribute} "{value}" not found.',array('{attribute}'=>$class::model()->displayName(),'{value}'=>$value)));
        };
        
        if (!empty($this->$attribute)){
            $attr = explode('_', $attribute);
            $modelClass = $attr[0]=='shippings'?'Shipping':ucfirst($attr[0]);
            //logTrace(__METHOD__." $modelClass has value = ".$this->$attribute);
            if (is_array($this->$attribute)){
                foreach ($this->$attribute as $value) {
                    $checkExists($modelClass, $value);
                }
            }
            else {
                $checkExists($modelClass, $this->$attribute);
            }
        }
    }    
    /**
     * Validate status values and activation rules
     * (1) Verify that value is either Y or N
     * (2) Verify that need at least 1 online shipping for product activation
     */
    public function ruleActivation($attribute,$params)
    {
        $errors = new CList();
        if (!in_array($this->$attribute,array(Process::YES,Process::NO))){
            $errors->add(Sii::t('sii','Product status must be either {Y} or {N}.',array('{Y}'=>Process::YES,'{N}'=>Process::NO)));
        }
        
        if ($this->$attribute==Process::YES) {
            //[1] check product shipping must have been specified
            if (count($this->shippings)<=0){
                $errors->add(Sii::t('sii','Product must have shipping to be brought online.'));
            }
            //[2] check product shipping must be online
            $pass = false;
            foreach ($this->shippings as $shipping) {
                $model = $this->findModelByName('Shipping', $shipping);
                if ($model!=false){
                    if ($model->deactivable()){//deactivable means currently online
                        $pass = true;
                        break;                        
                        
                    } 
                }
            }
            if (!$pass){
                $errors->add(Sii::t('sii','Product must have at least one online shipping associated. Shipping "{value}" is offline.',array('{value}'=>  implode(',', $this->shippings))));
            }            
        }
        if ($errors->count>0)
            $this->addError($attribute,Helper::htmlList($errors->toArray(),array('style'=>'margin:0px;padding:0px;list-style:none;')));
    }    
    /**
     * Validate rules of categories - if category or subcateogry exists
     * 
     * This validation is not called via rules() as it is already not in array but element value, 
     * thanks to LanguageForm::validateLocaleAttributes
     */
    public function ruleCategories($attribute,$params)
    {
        foreach ($this->categories as $category) {
            $data = explode(ProductImportColumn::$subcategorySeparator, $category);
            if (isset($data[0])){//category
                $categoryModel = $this->findModelByName('Category', $data[0]);
                if ($categoryModel==false){
                    $this->addError($attribute,Sii::t('sii','{attribute} "{value}" not found.',array('{attribute}'=>Category::model()->displayName(),'{value}'=>$data[0])));
                }
                if (isset($data[1]) && $categoryModel!=false){
                    if ($this->findModelByName('CategorySub', $data[1],array('category_id'=>$categoryModel->id))==false){
                        $this->addError($attribute,Sii::t('sii','{attribute} "{value}" not found.',array('{attribute}'=>CategorySub::model()->displayName(),'{value}'=>$data[1])));
                    }
                }
            }
        }
    }    
    /**
     * OVERRIDDEN METHOD
     * Validate rules of shippings
     */
    public function ruleShippings($attribute,$params)
    {
        //[1]validate shippping existence
        $this->ruleExists('shippings', array());
        //[2]validate shipping tiers
        $previousBase = null;
        foreach ($this->shippings as $shipping) {
            //logTrace(__METHOD__.' '. $shipping.' attribute');
            $tier = ShippingTier::model()->findByAttributes(array('shipping_id'=>$shipping));
            if ($tier!=null){
                if ($previousBase===null)
                    $previousBase = $tier->base;
                if ($previousBase != $tier->base){
                    $this->addError($attribute,Sii::t('sii','Tiered Type Shipping Base must all be the same.'));
                    break;
                }
                $previousBase = $tier->base;
            }
        }//end for loop
    } 
    /**
     * Validate rules of ProductAttributes
     * 
     * This validation is not called via rules() as it is already not in array but element value, 
     * thanks to LanguageForm::validateLocaleAttributes
     */
    public function ruleProductAttributes($attribute,$params)
    {
        foreach ($this->productAttributes as $key => $productAttr) {
            $productAttr->validateLocaleAttributes();
            if ($productAttr->hasErrors())
                $this->addError($attribute,CHtml::tag('ul',array('class'=>'product-attribute-error'),Sii::t('sii','Product #{m} Attribute #{n}',array('{m}'=>$this->getRowId(),'{n}'=>$key+1))).Helper::htmlErrors($productAttr->getErrors(),array('style'=>'margin:0px;padding:0px;list-style:none;')));
        }            
    }     
    /**
     * Validate childforms
     */
    public function validateChildForm() 
    {
        $this->ruleCategories('categories',array());
        $this->ruleShippings('shippings',array());
        $this->ruleProductAttributes('productAttributes',array());
    }      
    /**
     * @return array import product row
     */
    public function getRow()
    {
        return $this->_r;
    }
    /**
     * @return int import row id
     */
    public function getRowId()
    {
        if (isset($this->row[ProductImportColumn::$productCode]))
            return $this->row[ProductImportColumn::$productCode];
        else
            return Sii::t('sii','Product #{n}',array($this->rowNum));
    }
    /**
     * @return int import row number
     */
    public function getRowNum()
    {
        return $this->_n;
    }
    /**
     * Set column value
     */
    public function setColumnValue($column,$value)
    {
        $this->_r[$column] = $value;
    }    
    /**
     * Prepare images to be previewed
     */
    public function isMediaImage($image)
    {
        return Helper::isInteger($image);
    }
    public function getPreviewImages()
    {
        $preview = '';
        $images = array_filter(explode(ProductImportColumn::$delimiter, $this->row[ProductImportColumn::$productImages]));
        foreach ($images as $image) {
            $imageUrl = $image;
            if ($this->isMediaImage($image)){
                $media = Media::model()->findByPk($image);
                if ($media!=null)
                    $imageUrl = $media->assetUrl;
            }
            $preview .= CHtml::image($imageUrl, 'Image', array('width'=>'100px'));
        }
        return $preview;
    }
    /**
     * @return array row errors
     */
    public function getRowErrors()
    {
        return $this->hasErrors()?Helper::htmlErrors($this->getErrors(),array('style'=>'margin:0;')):null;
    }
    /**
     * Validate product by using ProductForm
     * @return array error status
     */
    public function validateRow()
    {
        return $this->validateLocaleAttributes();
    }
    /**
     * Parse row column; Check if column key exists, if not return null
     * The value are mainly controlled by $this->data
     * @param type $column
     * @return type
     */
    private function _parseColumn($column,$nullValue=null)
    {
        return array_key_exists($column,$this->row)?$this->row[$column]:$nullValue;
    }    
    /**
     * Parse row column as array
     * @return array
     */
    private function _parseColumnAsArray($column,$delimiter=null)
    {
        if ($delimiter===null)
            $delimiter = ProductImportColumn::$delimiter;
        
        if ($this->_parseColumn($column)!=null)
            return array_filter(explode($delimiter,$this->row[$column]));
        else
            return array();
    }   
    /**
     * Check if has attribute column data (non empty data)
     * @return boolean
     */
    private function _hasProductAttributeColumn($attribute)
    {
        $nullCount = 0;
        foreach ($attribute as $key => $column) {
            if ($this->_parseColumn($column)===null){
                $nullCount++;
            }
        }
        if ($nullCount==count($attribute))
            return false;
        else 
            return true;
    }
    /**
     * Add attribute column data
     * @return ProductAttributeForm (also contains ProductAttributeOptionForm)
     */
    private function _parseProductAttributeColumn($attribute)
    {
        //a second level validation to make sure locales are found in shop
        $serializeOptionLanguageValue = function($values){
            $names = new CMap;
            foreach ($this->shop->getLanguageKeys() as $language) {
                if (in_array($language, array_keys($values)))
                    $names->add($language,$values[$language]);
            }
            return json_encode($names->toArray());            
        };
    
        $tempProductId = user()->getId().time();//temp product id
        $tempAttrId = user()->getId().time() + rand(100,999);//temp attribute id
        //skip "precreate" scenario validation as the limit will not happen 
        //when using import file method to create prodcut
        $attrForm = new ProductAttributeForm($tempProductId,'create');
        $attrForm->id = $tempAttrId;
        $attrForm->code = $this->_parseColumn($attribute[ProductImportAttributeColumn::$attributeCode]);
        $attrForm->name = $this->_formatLanguageAttribute($attribute,'ProductImportAttributeColumn','attributeName');
        $attrForm->type = ProductAttribute::TYPE_SELECT;//default value
        $attrForm->share = 0;//default value "false"
        //load options
        for ($i = ProductImportAttributeColumn::$optionCode ; 
             $i < ProductImportAttributeColumn::$columnsPerAttribute; 
             $i = $i + (ProductImportAttributeColumn::$optionSurcharge - ProductImportAttributeColumn::$optionCode + 1) ) {//4 fields per option
                 
            //if all option value is null, option not exists
            if (!($this->_parseColumn($attribute[$i])  ==null && 
                  $this->_parseColumn($attribute[$i+1])==null &&
                  $this->_parseColumn($attribute[$i+2])==null && 
                  $this->_parseColumn($attribute[$i+3])==null 
                )){
                
                $optionForm = new ProductAttributeOptionForm($attrForm->product_id);
                $optionForm->attr_id = $attrForm->id;
                $optionForm->code = $this->_parseColumn($attribute[$i]);
                $optionForm->name = $serializeOptionLanguageValue(array(
                    ProductImportAttributeColumn::selectOptionLocale($i+1)=>$this->_parseColumn($attribute[$i+1]),
                    ProductImportAttributeColumn::selectOptionLocale($i+2)=>$this->_parseColumn($attribute[$i+2]),
                ));
                $optionForm->surcharge = $this->_parseColumn($attribute[$i+3]);
                $attrForm->options[] = $optionForm;
            }
        }
        return $attrForm;
    }    
    /**
     * Parsing Quantity column when product attribute presents
     * format: '<option code 1>-<option code 2>:<quantity>',
     */
    private function _parseQuantityColumn()
    {
        $column = [];
        foreach (explode(ProductImportColumn::$delimiter, $this->quantity) as $value) {
            $data = [];
            $quantity = explode(ProductImportColumn::$quantitySeparator, $value);
            if (isset($quantity[0])){
                $data['optionString'] = $quantity[0];
                $data['optionCodes'] = [];
                foreach (explode(ProductImportColumn::$optionSeparator, $quantity[0]) as $key => $option) {
                    $attr = ProductImportAttributeColumn::getArray($key);
                    $data['optionCodes'] = array_merge($data['optionCodes'],[$this->_parseColumn($attr[ProductImportAttributeColumn::$attributeCode]) => $option]);
                }
            }
            if (isset($quantity[1])){
                $data['quantity'] = $quantity[1];
            }
            $column[] = $data;
        }        
        logTrace(__METHOD__,$column);
        return $column;
    }
    /**
     * Format attribute value with locales
     * As a correct input for self::validateLocaleAttributes()
     * 
     * @param type $product
     * @param type $attribute
     * @return array 
     */
    private function _formatLanguageAttribute($attribute,$columnType='ProductImportColumn',$column=null)
    {
        $names = new CMap;
        foreach ($this->shop->getLanguageKeys() as $language) {
            if ($columnType=='ProductImportAttributeColumn' && is_array($attribute) && $column!=null)
                $names->add($language,$this->_parseColumn($attribute[$columnType::${$column.'_'.$language}]));
            else
                $names->add($language,$this->_parseColumn($columnType::${$attribute.'_'.$language}));
        }
        return $names->toArray();            
    }   
    /**
     * Overridden method from LanguageForm
     */
    protected function cloneForm()
    {
        $validatingForm = get_class($this);
        $form = new $validatingForm($this->account_id,$this->shop_id,$this->row,$this->rowNum);
        return $form;
    }      
    /**
     * Assign initial product model attribute values
     * @return type
     */
    protected function assignModelAttributes()
    {
        $this->modelInstance->setScenario('manualSlug');//refer to Product validation rules and getSlugValue()
        //direct mapping attributes
        $this->modelInstance->attributes = $this->getAttributes(array(
            'account_id',
            'shop_id',
            'code',
            'unit_price',
            'weight',
            'slug',
        ));
        //serialize multi-lang attributes
        $this->modelInstance->name = json_encode($this->name);
        $this->modelInstance->spec = json_encode($this->spec);
        //product image set to temporary image first, so that validation can go through
        $this->modelInstance->image = Image::DEFAULT_IMAGE;
        //set brand id if any
        $this->modelInstance->brand_id = $this->retrieveModelIdByName('Brand', $this->brand_name);
        //initial product status as offline; whether product activation or not handled at later stage
        $this->modelInstance->status = Process::PRODUCT_OFFLINE;
        //logTrace(__METHOD__,$this->modelInstance->attributes);
    }
    /**
     * Import row as product (including images, shippings, inventory, activation) into database
     * @throws CException
     */
    public function import()
    {
        //[1] assign product model attributes
        $this->assignModelAttributes();
        //[2] import product
        if (!$this->modelInstance->save()){
            logError(__METHOD__.' product validation errors',$this->modelInstance->errors);
            throw new CException(Sii::t('sii','Failed to import {object}.',array('{object}'=>$this->modelInstance->displayName())));
        }
        //[3] import product images
        $this->importImages();
        //[4] insert product categories
        $this->importCategories();
        //[5] insert product shippings
        $this->importShippings();
        //[6] insert product attributes
        $this->importProductAttributes();
        //[7] insert product inventory
        $this->importInventory();
        //[8] activate product
        if ($this->status==Process::YES){
            Yii::app()->getModule('products')->serviceManager->transition($this->account_id,$this->modelInstance,'activate',null,false);
        }
    }
    
    protected function importImages()
    {
        foreach ($this->_parseColumnAsArray(ProductImportColumn::$productImages) as $index => $imageValue) {

            if ($this->isMediaImage($imageValue)){
                $media = Media::model()->findByPk($imageValue);
                $mediaAssoc = $media->attachToOwner($this->modelInstance,'ProductImport');
            }
            else {
                $image = new ImageExternal();//calling it to get its init to auto-populated data (name, mime_type, and size)
                $image->setImageKey($index);
                $mediaAssoc =  Yii::app()->serviceManager->mediaManager->create($this->modelInstance->account_id,[
                                    'name'=>$image->name,
                                    'filename'=> $image->filename,
                                    'mime_type'=>$image->mime_type,
                                    'size'=>$image->size,
                                    'src_url'=>$imageValue,
                                    'external_media'=>true,
                                    'owner'=>$this->modelInstance,
                                ]);                
            }

            if ($index==0){//set first image as primary image
                //product image set to external source; 
                $this->modelInstance->image = $mediaAssoc->id;
                $this->modelInstance->update();
            }
        }
    }
    
    protected function importCategories()
    {
        foreach ($this->categories as $category) {
            $data = explode(ProductImportColumn::$subcategorySeparator, $category);
            if (isset($data[0]) && isset($data[1])){//subcategory
                $categoryModel = $this->findModelByName('Category', $data[0]);
                $subcategoryModel = $this->findModelByName('CategorySub', $data[1], array('category_id'=>$categoryModel->id));
                $model = new ProductCategory();
                $model->product_id = $this->modelInstance->id;
                $model->category_id = $subcategoryModel->category_id;
                $model->subcategory_id = $subcategoryModel->id;
                if (!$model->save()){
                    logError(__METHOD__.' model validation errors',$model->errors);
                    throw new CException(Sii::t('sii','Failed to import {object}.',array('{object}'=>$model->displayName())));
                }
                //logTrace(__METHOD__.' product category ok',$model->attributes);
            }
            if (isset($data[0]) && !isset($data[1])){//category only
                $categoryModel = $this->findModelByName('Category', $data[0]);
                $model = new ProductCategory();
                $model->product_id = $this->modelInstance->id;
                $model->category_id = $categoryModel->id;
                if (!$model->save()){
                    logError(__METHOD__.' model validation errors',$model->errors);
                    throw new CException(Sii::t('sii','Failed to import {object}.',array('{object}'=>$model->displayName())));
                }
                //logTrace(__METHOD__.' category ok',$model->attributes);
            }
        }
    }
    
    protected function importShippings()
    {
        foreach ($this->shippings as $shipping_name) {
            $model = new ProductShipping();
            $model->product_id = $this->modelInstance->id;
            $model->shipping_id = $this->retrieveModelIdByName('Shipping', $shipping_name);
            if (!$model->save()){
                logError(__METHOD__.' model validation errors',$model->errors);
                throw new CException(Sii::t('sii','Failed to import {object}.',array('{object}'=>$model->displayName())));
            }
            //logTrace(__METHOD__.' ok',$model->attributes);
        }
    }
    
    protected function importProductAttributes()
    {
        foreach ($this->productAttributes as $productAttribute) {
            $model = new ProductAttribute();
            $model->product_id = $this->modelInstance->id;
            $model->attributes = $productAttribute->getAttributes(array('code','name','type','share'));
            $model->name = json_encode($productAttribute->name);
            $model->options = $productAttribute->getChildModels();
            if (!$model->save()){
                logError(__METHOD__.' model validation errors',$model->errors);
                throw new CException(Sii::t('sii','Failed to import {object}.',array('{object}'=>$model->displayName())));
            }
            $model->insertChilds();
            //logTrace(__METHOD__.' ok',$model->attributes);
        }
    }
    
    protected function importInventory()
    {
        if (count($this->productAttributes)>0){
            foreach ($this->_parseQuantityColumn() as $data) {
                $this->createInventory(Inventory::formatSKU2($this->code, $data['optionCodes']),$data['quantity']);
            }
        }
        else {
            //use code as sku for now without product attributes
            $this->createInventory($this->code,$this->quantity);
        }
    }
    /**
     * Create an inventory record based on SKU
     * @param type $sku
     * @throws CException
     */
    protected function createInventory($sku,$quantity)
    {
        $model = new Inventory();
        $model->obj_type = Product::model()->tableName();
        $model->obj_id = $this->modelInstance->id;
        //direct mapping attributes
        $model->attributes = $this->getAttributes(array(
            'account_id',
            'shop_id',
        ));
        $model->quantity = $quantity;
        $model->available = $quantity;
        $model->sku = $sku;
        if (!$model->save()){
            logError(__METHOD__.' model validation errors',$model->errors);
            throw new CException(Sii::t('sii','Failed to import {object}.',array('{object}'=>$model->displayName())));
        }
        $model->saveInitialHistory($model->quantity);
        //logTrace(__METHOD__.' ok',$model->attributes);
    }    
    
    protected function findModelByName($class,$name,$keyColumnCondition=array())
    {
        if (empty($keyColumnCondition))
            $keyColumnCondition = array('shop_id'=>$this->shop_id);
        return $this->findModelByLanguageName($name,$keyColumnCondition,$class);
    }
    
    protected function retrieveModelIdByName($class,$name,$keyColumnCondition=array())
    {
        $model = $this->findModelByName($class, $name, $keyColumnCondition);
        if ($model!=false){
            logTrace(__METHOD__,$model->attributes);
            return $model->id;
        }
        else
            return null;
    }
}
/**
 * Description of ProductImportColumn
 * 
 * @author kwlok
 */
class ProductImportColumn
{
    /*
     * Below are column definitions
     */
    public static $productErrors       = '0';
    public static $productCode         = 'A';
    public static $productName_en_sg   = 'B';
    public static $productName_zh_cn   = 'C';
    public static $productSpec_en_sg   = 'D';
    public static $productSpec_zh_cn   = 'E';
    public static $productPrice        = 'F';
    public static $productWeight       = 'G';
    public static $productImages       = 'H';
    public static $productUrlSlug      = 'I';
    public static $productCategories   = 'J';
    public static $productBrand        = 'K';
    public static $productShipping     = 'L';
    public static $productStatus       = 'M';
    public static $productQuantity     = 'N';
    /*
     * The delimiter used to separate entry values for column supports multi-entries
     */
    public static $delimiter           = '|';
    public static $subcategorySeparator= '>';
    public static $quantitySeparator   = ':';
    public static $optionSeparator     = '-';
    /**
     * @return all columns for import file
     */
    public static function getAllColumns()
    {
        return array_merge(self::getArray(),ProductImportAttributeColumn::getColumnArray());
    }
    /**
     * Return columns in array (follow the sequence in Excel file)
     * @return array
     */
    public static function getArray()
    {
        return array(
            self::$productErrors,
            self::$productCode,
            self::$productName_en_sg,
            self::$productName_zh_cn,
            self::$productSpec_en_sg,
            self::$productSpec_zh_cn,
            self::$productPrice,
            self::$productWeight,
            self::$productImages,
            self::$productUrlSlug,
            self::$productCategories,
            self::$productBrand,
            self::$productShipping,
            self::$productQuantity,
            self::$productStatus,
        );
    }
    /**
     * Return total number of columns
     * @return type
     */
    public static function getCount()
    {
        return count(self::getArray());
    }
    
    public static function getLabel($column)
    {
        switch ($column) {
            case self::$productErrors:
                return '';
            case self::$productCode:
                return Product::model()->getAttributeLabel('code');
            case self::$productName_en_sg:
                return Product::model()->getAttributeLabel('name').' '.Sii::t('sii','(English)');
            case self::$productName_zh_cn:
                return Product::model()->getAttributeLabel('name').' '.Sii::t('sii','(Chinese)');
            case self::$productSpec_en_sg:
                return Product::model()->getAttributeLabel('spec').' '.Sii::t('sii','(English)');
            case self::$productSpec_zh_cn:
                return Product::model()->getAttributeLabel('spec').' '.Sii::t('sii','(Chinese)');
            case self::$productPrice:
                return Product::model()->getAttributeLabel('unit_price');
            case self::$productWeight:
                return Product::model()->getAttributeLabel('weight');
            case self::$productImages:
                return Product::model()->getAttributeLabel('image');
            case self::$productUrlSlug:
                return Product::model()->getAttributeLabel('slug');
            case self::$productCategories:
                return Product::model()->getAttributeLabel('category_id');
            case self::$productBrand:
                return Product::model()->getAttributeLabel('brand_id');
            case self::$productShipping:
                return Sii::t('sii','Shipping');
            case self::$productQuantity:
                return Inventory::model()->getAttributeLabel('quantity');
            case self::$productStatus:
                return Sii::t('sii','Online Status');
            default:
                return ProductImportAttributeColumn::getLabel($column);
        }
    }
    
    public static function getOKIcon()
    {
        return '<i class="fa fa-check-circle fa-fw"></i>';
    }
    
    public static function getErrorIcon()
    {
        return '<i class="fa fa-minus-circle fa-fw"></i>';
    }    
    
}

class ProductImportAttributeColumn 
{
    public static $firstColumn = 'O';
    public static $columnsPerAttribute  = 35;
    public static $maxAttributes = 6;
    public static $maxOptions = 8;
    /*
     * Below are attribute column definitions
     */
    public static $attributeCode       = 0;
    public static $attributeName_en_sg = 1;
    public static $attributeName_zh_cn = 2;
    //each attribute has 4 option columns, and is repeating for 8 times (max is 8 options)
    public static $optionCode          = 3;
    public static $optionName_en_sg    = 4;
    public static $optionName_zh_cn    = 5;
    public static $optionSurcharge     = 6; 
    /**
     * Select option locale based on key position
     */
    public static function selectOptionLocale($currentIndex)
    {
        if (($currentIndex - self:: $optionName_en_sg) % 4 == 0)
            return 'en_sg';
        if (($currentIndex - self:: $optionName_zh_cn) % 4 == 0)
            return 'zh_cn';
    }
    /**
     * Return columns position in Excel file (follow the sequence in Excel file)
     * @return array
     */
    public static function getArray($index=null)
    {
        if (!isset($index)){
            $attrs = new CList();
            $columns = new CList();
            $count = 0;
            foreach (range('A','Z') as $col) { 
                if ($col>=self::$firstColumn) {    
                    $count++;
                    $columns->add($col);
                }  
            }
            foreach (range('A','Z') as $col1) { 
                foreach (range('A','Z') as $col2) { 
                    if ($count % self::$columnsPerAttribute ==0){
                        $attrs->add($columns->toArray());
                        $columns = new CList();//reset
                        $count = 0;
                    }
                    $columns->add($col1.$col2);
                    $count++;
                }
                if ($attrs->count >= self::$maxAttributes)
                    break;
            }
            return $attrs->toArray();
        }
        else {
            $array = self::getArray();
            return $array[$index];
        }
    }
    /**
     * Get all columns (8*35) in one array for one product row 
     * @param type $key
     * @return type
     */
    public static function getColumnArray()
    {
        $columns = [];
        foreach (self::getArray() as $value) {
            foreach ($value as $column) {
                $columns[] = $column;
            }
        }
        return $columns;
    }
    
    public static function getLabel($column)
    {
        $data = self::findColumn($column);
        if (is_array($data)){
            $attributeIndex = $data['attributeIndex'] + 1;
            switch ($data['key']) {
                case self::$attributeCode:
                    return '#'.$attributeIndex.' '.ProductAttribute::model()->displayName().' '.ProductAttribute::model()->getAttributeLabel('code');
                case self::$attributeName_en_sg:
                    return '#'.$attributeIndex.' '.ProductAttribute::model()->displayName().' '.ProductAttribute::model()->getAttributeLabel('name').' '.Sii::t('sii','(English)');
                case self::$attributeName_zh_cn:
                    return '#'.$attributeIndex.' '.ProductAttribute::model()->displayName().' '.ProductAttribute::model()->getAttributeLabel('name').' '.Sii::t('sii','(Chinese)');
                case ($data['key'] - self::$optionCode) % 4 == 0:
                    return '#'.$attributeIndex.' '.ProductAttribute::model()->displayName().' '.ProductAttributeOption::model()->getAttributeLabel('code');
                case ($data['key'] - self::$optionName_en_sg) % 4 == 0:
                    return '#'.$attributeIndex.' '.ProductAttribute::model()->displayName().' '.ProductAttributeOption::model()->getAttributeLabel('name').' '.Sii::t('sii','(English)');
                case ($data['key'] - self::$optionName_zh_cn) % 4 == 0:
                    return '#'.$attributeIndex.' '.ProductAttribute::model()->displayName().' '.ProductAttributeOption::model()->getAttributeLabel('name').' '.Sii::t('sii','(Chinese)');
                case ($data['key'] - self::$optionSurcharge) % 4 == 0:
                    return '#'.$attributeIndex.' '.ProductAttribute::model()->displayName().' '.ProductAttributeOption::model()->getAttributeLabel('surcharge');
                default:
                    return Sii::t('sii','undefined');
            }
            
        }
        else {
            //should not reach here
            return Sii::t('sii','undefined '.$column);
        }
    }
    
    public static function findColumn($column)
    {
        foreach (self::getArray() as $attributeIndex => $columns) {
            $key = array_search($column, $columns, true);
            if (is_numeric($key))
                return array('attributeIndex'=>$attributeIndex,'key'=>$key,'value'=>$column);
        }
        return false;
    }    
    
}