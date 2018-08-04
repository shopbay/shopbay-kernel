<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.modules.shops.components.BasePage");
Yii::import("common.modules.shops.components.ShopBrowseMenu");
/**
 * Description of ShopPageFilter
 * 
 * @author kwlok
 */
class ShopPageFilter extends BasePage 
{
    /**
     * Default brand/category/subcategory slug url is used;
     * The shop page filter direct url must be set at each brand, category or subcategory url slug.
     * Only direct matching of slug to category or subcategory will pick up products under it.
     * Example:
     *   Brand => Apple (苹果), slug => "apple"
     *   Catergory => Mobile Devices (电子移动产品）, slug => "mobile-devices"
     *     SubCatergory => Laptop (膝上电脑）       , slug => "laptop"
     *     SubCatergory => Mobile Phone (手机）    , slug => "mobile-phone"
     * Below url works:
     * http:://<domain>/shop/<shop name>/brand/apple
     * http:://<domain>/shop/<shop name>/category/mobile-devices
     * http:://<domain>/shop/<shop name>/category/mobile-devices/laptop
     * http:://<domain>/shop/<shop name>/category/mobile-devices/mobile-phone
     * 
     * If $smartUrl is enabled "True", smart url mode becomes the secondary url method to locate brand/cateogry/subcategory products
     * Smart url means that shop page filter url is auto generated (or supported) based on category and subcategory name (in either languages)
     * 
     * In this case, below url auto works following examples above (including other language).
     * http:://<domain>/shop/<shop name>/brand/Apple
     * http:://<domain>/shop/<shop name>/brand/苹果
     * http:://<domain>/shop/<shop name>/category/Mobile%20Devices
     * http:://<domain>/shop/<shop name>/category/电子移动产品
     * http:://<domain>/shop/<shop name>/category/Mobile%20Devices/Laptop
     * http:://<domain>/shop/<shop name>/category/电子移动产品/膝上电脑
     * http:://<domain>/shop/<shop name>/category/电子移动产品/Laptop
     * http:://<domain>/shop/<shop name>/category/Mobile%20Devices/Mobile%20Phone
     * http:://<domain>/shop/<shop name>/category/电子移动产品/手机
     * 
     * Else,
     * When set to "false", only slug url method will be used.
     */
    public $smartUrl = true;
    /*
     * Private property
     */
    private $_d;//Filter raw data for browse by category or brands 
    private $_t;//Filter type
    private $_v;//Filter value
    private $_m;//Filter model
    /**
     * Set filter raw data
     */
    public function setData($data)
    {
        $this->_d = $data;
    }     
    /**
     * @return boolean 
     */
    public function getHasData()
    {
        return isset($this->_d);
    }      
    /**
     * Get filter data
     * Filter data format: array($type=>$value)
     * @return type
     */
    public function getData($locale=null) 
    {
        return [$this->type=>$this->value];
    }
    /**
     * Set filter model
     * @param type $model
     */
    public function setFilterModel($model)
    {
        $this->_m = $model;
    }
    /**
     * Get filter model 
     */
    public function getFilterModel()
    {
        if ($this->hasData && !isset($this->_m)){
            if ($this->type==ShopBrowseMenu::CATEGORY && CategorySub::model()->hasKey($this->value)){
                try {
                    $key = CategorySub::model()->parseKey($this->value);
                    $this->_m = CategorySub::model()->findByPk($key[1]);
                } catch (CException $ex) {
                    $this->_m = null;
                }
            }
            else {
                $filterType = ucfirst($this->type);
                $this->_m =  $filterType::model()->findByPk($this->value);
            }            
        }
        return $this->_m;
    }   
    /**
     * Get filter name 
     */
    public function getName($locale=null)
    {
        if (isset($this->filterModel)){
            if ($locale==null)
                $locale = param('LOCALE_DEFAULT');
            
            if ($this->filterModel instanceof CategorySub)
                return $this->filterModel->toString($locale);
            else
                return $this->filterModel->displayLanguageValue('name',$locale);
        }
        else
            return null;
    }     
    /**
     * Get filter menu array (suitable for breadcrumbs) 
     */
    public function getMenuArray($locale)
    {
        if (isset($this->filterModel)){
            return $this->filterModel->toMenuArray($locale);
        }
        else
            return [];
    }     
    
    public function getUrl()
    {
        $menu = $this->getMenuArray(param('DEFAULT_LOCALE'));//dummy use, getting filter url does not need locale
        return array_pop($menu);//the current filter url is the last array element
    }
    /**
     * Get filter tag 
     */
    public function getTag($locale)
    {
        if ($this->hasData){
            $tag = CHtml::openTag('div',['class'=>'filter '.$this->type]);
            $tag .= CHtml::tag('span',['class'=>'name'],ShopBrowseMenu::getLabels($this->type));
            $tag .= CHtml::tag('span',['class'=>'value'],$this->getName($locale));
            $tag .= CHtml::tag('span',['class'=>'close','onclick'=>'removefilter('.$this->model->id.',1);','title'=>Sii::t('sii','Remove Filter')],'X');
            $tag .= CHtml::closeTag('div');
            return $tag;
        }
        else
            return null;
    } 
    /**
     * Get filter type 
     */
    public function getType()
    {
        if ($this->hasData && !isset($this->_t)){
            $filterKey = array_keys($this->_d);//expect only one key
            $this->_t = $filterKey[0];
        }
        return $this->_t;
    }    
    /**
     * Get filter value
     */
    public function getValue()
    {
        if ($this->hasData && !isset($this->_v)){
            $filterValue = array_values($this->_d);//expect only one value
            $this->_v = $filterValue[0];//only contains one element
            if ($this->type==ShopBrowseMenu::CATEGORY){
                $this->_v = $this->parseCategoryValue($this->_v);
            }
            if ($this->type==ShopBrowseMenu::BRAND){
                $this->_v = $this->parseBrandValue($this->_v);
            }
        }
        return $this->_v;
    }

    protected function parseBrandValue($value)
    {
        if (!is_numeric($value)){
            logTrace(__METHOD__.' search brand for value',$value);
            $model = $this->parseModel($value);
            if ($model!=false){
                $this->setFilterModel($model);
                logTrace(__METHOD__.' brand found',$this->filterModel->attributes);
                return $this->filterModel->id;
            }
            return null;
        }
        return $value;//for numeric value, just return intact as brand key
    }

    protected function parseCategoryValue($value)
    {
        if (!is_numeric($value)){
            $key = explode(CategorySub::KEY_SEPARATOR, $value);
            if (isset($key[0])&&isset($key[1])){//subcategory presents
                $model = $this->parseModel($key[0]);
                if ($model!=false){
                    $submodel = $this->parseCategorySubModel($model->id, $key[1]);
                    if ($submodel!=false){
                        $this->setFilterModel($submodel);
                        //logTrace(__METHOD__.' subcategory found',$this->filterModel->attributes);
                        return $this->filterModel->toKey();
                    }
                }
                return null;
            }
            elseif (isset($key[0])&&!isset($key[1])){//main category presents
                $model = $this->parseModel($key[0]);
                if ($model!=false){
                    $this->setFilterModel($model);
                    //logTrace(__METHOD__.' category found',$this->filterModel->attributes);
                    return $this->filterModel->id;
                }
                return null;
            }
        }
        return $value;//for numeric value, just return intact as category key
    }

    protected function parseModel($value)
    {
        $modelType = ucfirst($this->type);
        $model = $modelType::model()->findModelBySlug($this->model->id,$value);
        if ($model==false){
            if ($this->smartUrl){
                $model = $modelType::model()->findModelByLanguageName($value,['shop_id'=>$this->model->id]);
            }
        }
        return $model;
    }

    protected function parseCategorySubModel($category_id,$value)
    {
        $model = CategorySub::model()->findModelBySlug($category_id,$value);
        if ($model==false){
            if ($this->smartUrl){
                $model = CategorySub::model()->findModelByLanguageName($value,['category_id'=>$category_id]);
            }
        }
        return $model;
    }
}
