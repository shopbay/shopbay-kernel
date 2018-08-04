<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of PageOwnerTrait
 * CModel must have two attributes: owner_id, and owner_type
 * 
 * @author kwlok
 */
trait PageOwnerTrait 
{
    private $_o;//owner instance
    /**
     * Behaviors for this model
     */
    public function ownerBehaviors()
    {
        return [
            'account' => [
                'class'=>'common.components.behaviors.AccountBehavior',
                'accountSource'=>'owner',
            ], 
        ];
    }    
    /**
     * @return array validation rules for model attributes.
     */
    public function ownerRules()
    {
        return [
            ['owner_id, owner_type', 'required'],
            ['owner_id', 'numerical', 'integerOnly'=>true],
            ['owner_type', 'length', 'max'=>25],
            ['owner_id, owner_type', 'safe', 'on'=>'search'],
        ];
    }    
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function ownerAttributeLabels()
    {
        return [
            'owner_id' => Sii::t('sii','Owner'),
            'owner_type' => Sii::t('sii','Owner Type'),
        ];
    }    
    /**
     * Return owner model
     * @return CModel
     */
    public function getOwner() 
    {
        if (!isset($this->_o)){
            $modelClass = $this->owner_type;
            $this->_o = $modelClass::model()->findByPk($this->owner_id);
        }
        return $this->_o;
    }    
    /**
     * A finder method by owner
     * @param CModel $ownerModel
     * @return \CActiveRecord
     */
    public function locateOwner($ownerModel) 
    {
        $this->getDbCriteria()->mergeWith([
            'condition'=>'owner_id='.$ownerModel->id.' AND owner_type=\''.get_class($ownerModel).'\'',
        ]);
        return $this;
    }    
    /**
     * Refresh owner type 
     */
    public function refreshOwnerType()
    {
        $this->owner_type = get_class($this->getOwner());
    }
    /**
     * This is public accessible owner url
     * @return type
     */
    public function getOwnerUrl($secure=false)
    {
        return $this->getOwner()->getUrl($secure);
    }    
    /**
     * BELOW METHODS are used to mimic Shop owner
     */
    public function getShop()
    {
        return $this->owner_type='Shop' ? $this->getOwner() : null;//If owner_type is shop
    }
    /**
     * A finder method by shop
     * @param int $shopId
     * @return \CActiveRecord
     */
    public function locateShop($shopId) 
    {
        $this->getDbCriteria()->mergeWith([
            'condition'=>'owner_id='.$shopId.' AND owner_type=\'Shop\'',
        ]);
        return $this;
    }        
    /**
     * A custom implementation to get all supported locale languages for owner
     * @see Shop::getLanguages()
     * @return array
     */    
    public function getLanguages()
    {
        return $this->getOwner()->getLanguages();
    }    
    /**
     * A generic implementation to get all supported locale languages for owner
     * @return array
     */    
    public function getLanguageKeys()
    {
        return array_keys($this->getLanguages());
    }    
}
