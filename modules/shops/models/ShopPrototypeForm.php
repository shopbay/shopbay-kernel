<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of ShopPrototypeForm
 * Shop prototype attribute values are preload with dummy values (with "create" scenario)
 * For "update" scenario, all shop prototype attributs is null, so that merchant is expected to update them (the logic is handled at controller)
 * 
 * @author kwlok
 */
class ShopPrototypeForm extends SFormModel 
{
    public $account_id, $name, $status, $slug;
    public $contact_person, $contact_no, $email;
    public $timezone, $language, $currency, $weight_unit, $category;
    /*
     * Initial shop values (to be changed by user)
     */
    private $_initialName;
    private $_initialSlug;
    /**
     * Constructor.
     * @param string $scenario name of the scenario that this model is used in.
     * See {@link CModel::scenario} on how scenario is used by models.
     * @see getScenario
     */
    public function __construct($user,$scenario='create')
    {
        $this->account_id = $user;
        $this->status = Process::SHOP_PROTOTYPE;
        $this->language = param('LOCALE_DEFAULT');
        $count = Shop::model()->count('account_id='.$user);
        if ($scenario=='create'){
            if ($count==0){
                $this->_initialName = 'My First Shop'.$this->nameSuffix();
                $this->_initialSlug = $this->slugSuffix('prototype-first-shop-'); 
            }
            else {
                $this->_initialName = 'My New Shop'.$this->nameSuffix($count);
                $this->_initialSlug = $this->slugSuffix('prototype-new-shop-',$count); 
            }
        }
        if ($scenario=='update')
            $this->_initialName = '';
        //convert attribute name into multi-lang format
        $localeNames = new CMap();
        foreach (Shop::model()->getLanguageKeys() as $language) {
            $localeNames->add($language,Sii::tl('sii', $this->_initialName,$language));
        }
        $this->name = json_encode($localeNames->toArray());
        parent::__construct($scenario);
    }
    /**
     * Initializes this model.
     */
    public function init()
    { 
        if ($this->getScenario()=='create'){
            //set dummy values
            $this->slug = $this->_initialSlug;
            $this->contact_person = 'contact_person';
            $this->contact_no = 88888888;
            $this->email = $this->account_id.'.'.time().'@shop.shopbay.org';
            $this->timezone = 'timezone';
            $this->currency = '¥';//not using "$" sign as dashboard js chart will break with $ 
            $this->weight_unit = 'g';
            $this->category = 0;//unset
            logTrace(__METHOD__.' Initial attributes',$this->attributes);
        }
    }
    /**
     * 
     * @return form display name
     */
    public function displayName() 
    {
        return Shop::model()->displayName();
    }

    public function nameSuffix($count=null)
    {
        $suffix = Shop::NAME_SUFFIX.$this->account_id;
        if (isset($count))
            $suffix .= Shop::NAME_SUFFIX.$count;
        return $suffix;
    }
    
    public function slugSuffix($slug,$count=null)
    {
        return substr($slug.sha1($this->nameSuffix($count).time()),0,50);//keep max length is 50
    }    
}