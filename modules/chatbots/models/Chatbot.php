<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * This is the model class for table "s_chatbot".
 *
 * The followings are the available columns in table 's_chatbot':
 * @property integer $id
 * @property integer $client_id
 * @property integer $owner_id (composite primary key)
 * @property string $owner_type  (composite primary key)
 * @property string $provider  (composite primary key)
 * @property string $settings
 * @property string $status
 * @property string $create_time
 * @property integer $update_time
 *
 * @author kwlok
 */
class Chatbot extends SActiveRecord
{
    private $_o;//owner instance
    /*
     * List of support provider
     */
    CONST MESSENGER = 'messenger';
    /**
     * Get supported providers 
     * @return array
     */
    public static function getProviders()
    {
        return [
            Chatbot::MESSENGER,
        ];
    }    
    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Category the static model class
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
        return Sii::t('sii','Chatbot|Chatbots',array($mode));
    }        
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 's_chatbot';
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
                'accountSource'=>'owner',
            ],
            'activity' => [
                'class'=>'common.modules.activities.behaviors.ActivityBehavior',
                'buttonIcon'=> [
                    'enable'=>true,
                ]
            ],
            'messengerbehavior' => [
                'class'=>'common.modules.chatbots.behaviors.MessengerConfigBehavior',
            ],
        ];
    }    
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            ['client_id, owner_id, owner_type, provider, status', 'required'],
            ['client_id', 'length', 'max'=>50],
            ['owner_id', 'numerical', 'integerOnly'=>true],
            ['owner_id', 'length', 'max'=>11],
            ['owner_type', 'length', 'max'=>11],
            ['owner_type, provider, status', 'length', 'max'=>20],
        ];
    }
    /**
     * A query method to find chatbot 
     * @param string $clientId
     * @return \Chatbot
     */
    public function locateClient($clientId) 
    {
        $this->getDbCriteria()->mergeWith(array(
            'condition'=>'client_id=\''.$clientId.'\'',
        ));
        return $this;
    }    
    /**
     * A query method to find chatbot 
     * @param string $ownerType
     * @param integer $ownerId
     * @param string $provider
     * @return \Chatbot
     */
    public function forOwner($ownerType,$ownerId,$provider) 
    {
        $this->getDbCriteria()->mergeWith(array(
            'condition'=>'provider=\''.$provider.'\' AND owner_id='.$ownerId.' AND owner_type=\''.ucfirst($ownerType).'\'',
        ));
        return $this;
    }
    /**
     * Chatbot view url
     */
    public function getViewUrl() 
    {
        return url('shops/settings/view/'.$this->owner->slug.'?setting='.ShopSetting::$chatbot);
    }
    /**
     * Chatbot name; also used by Activity recording
     */
    public function getName()
    {
        return ucfirst($this->provider);
    }
    /**
     * Return owner model
     * @return CModel
     */
    public function getOwner($configureMode=false) 
    {
        if (!isset($this->_o)){
            $modelClass = $this->owner_type;
            $owner = $modelClass::model()->findByPk($this->owner_id);
            if ($configureMode){
                if (!$owner->hasSubscription){
                    logError(__METHOD__.' chatbot owner has no subscription');
                    throw new CException('Chatbot Owner not found');
                }
            }
            else {
                if (!$owner->online() && !$owner->hasSubscription){
                    logError(__METHOD__.' chatbot owner is neither online nor has subscription');
                    throw new CException('Chatbot Owner not found');
                }
            }
            $this->_o = $owner;
        }
        return $this->_o;
    }
    /**
     * Smart save the chatbot settings (saving delta change only)
     * @param array $values
     */
    public function saveSettings($values=[])
    {
        $currentSettings = json_decode($this->settings,true);
        if ($currentSettings==null){
            logTrace(__METHOD__." current settings is empty, and input values",$values);
            if (!empty($values))
                $currentSettings = $values;
            else
                $currentSettings = [];//first time creation
        }
        else {
            foreach ($currentSettings as $key => $value) {//only override those received in $values
                if (isset($values[$key])){
                    $currentSettings[$key] = $values[$key];
                    logTrace(__METHOD__." set setting $key to",$values[$key]);
                }
            }
            foreach ($values as $key => $value) {//store those new setting not available in current settings
                if (!isset($currentSettings[$key])){
                    $currentSettings[$key] = $value;
                    logTrace(__METHOD__." set setting $key to",$value);
                }
            }
        }
        $this->settings = json_encode($currentSettings);//json encode back
        $this->save();
    }
    /**
     * Get setting by field
     * @param type $field
     * @return array of setting values
     */
    public function getSettings()
    {
        $settings = json_decode($this->settings,true);
        if (empty($settings))
            return [];
        else
            return $settings;
    }
    /**
     * Get setting by field
     * @param type $field
     * @return array of setting values
     */
    public function getSetting($field)
    {
        $settings = $this->getSettings();
        if (isset($settings[$field]))
            return $settings[$field];
        else
            return null;
    }
    /**
     * A helper method to find a chatbot (valid one)
     * @param string $clientId
     * @return Chatbot
     */
    public static function findClient($clientId)
    {
        $chatbot = Chatbot::model()->locateClient($clientId)->find();
        if ($chatbot!=null && $chatbot->owner!=null)
            return $chatbot;
        else
            return null;
    }

}
