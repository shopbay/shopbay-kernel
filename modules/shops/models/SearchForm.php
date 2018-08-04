<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of SearchForm
 * This form is mainly used to detect that a search page is required for loading at StorefrontController
 * 
 * Currently the search query is not handled POST (form based), but directly using url GET method
 * 
 * @author kwlok
 */
class SearchForm extends SFormModel 
{   
    public $id='search_form';
    public $shop_id;
    public $query = '';
    /**
     * Model display name 
     * @return string the model display name
     */
    public function displayName()
    {
        return Sii::t('sii','Search');
    } 
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('query, shop_id', 'required'),
            array('shop_id', 'numerical', 'integerOnly'=>true),
            array('query','rulePurify'),
            array('query', 'length', 'max'=>500),
            array('shop_id, query', 'safe'),            
        );
    } 
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'shop_id' => Sii::t('sii','Shop'),
            'product_id' => Sii::t('sii','Product'),
            'question' => Sii::t('sii','Question'),
            'type' => Sii::t('sii','I want to share this question to public'),
        );
    }       
    public function hasQuery()
    {        
        return strlen($this->query)>0;
    }
    
    public function getShopName()
    {        
        if ($this->hasShop()){
            $shop = $this->shop;
            if ($shop!=null)
                return CHtml::link($shop->displayLanguageValue('name',user()->getLocale()),$shop->url);
        }
        return Sii::t('sii','Shop not found');
    }
    
    public function hasShop()
    {        
        return $this->shop_id!=null;
    }

    public function getShop()
    {        
        if ($this->hasShop())
            return Shop::model()->findByPk($this->shop_id);
        else
            return null;
    }    
    
}