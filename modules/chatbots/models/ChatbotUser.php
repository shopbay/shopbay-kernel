<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.models.Chatbot');
/**
 * This is the model class for table "s_chatbot_user".
 *
 * The followings are the available columns in table 's_chatbot_user':
 * @property string $client_id
 * @property string $app_id
 * @property string $user_id
 * @property string $user_data
 * @property integer $account_id
 * @property string $session_data
 *
 * @author kwlok
 */
class ChatbotUser extends CActiveRecord
{
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
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 's_chatbot_user';
    }
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            ['client_id, app_id, user_id', 'required'],
            ['client_id', 'length', 'max'=>50],
            ['app_id, user_id', 'length', 'max'=>200],
            ['account_id', 'length', 'max'=>12],
        ];
    }
    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return [
            'account' => [self::BELONGS_TO, 'Account', 'account_id'],
            'chatbot' => [self::BELONGS_TO, 'Chatbot', 'client_id'],
        ];
    }
    /**
     * Clear session data when user logout social network site
     * @return whether the model successfully saved
     */
    public function clearSessionData()
    {
        $this->session_data = null;
        return $this->save();
    }    
    /**
     * Get the session data
     * @return mixed
     */
    public function getSessionData($field)
    {
        if ($this->isConnected){
            $data = json_decode($this->session_data,true);
            if (isset($data[$field]))
                return $data[$field];
        }
        return null;
    }    
    /**
     * Return the session authorization code (only when connected and session data is present)
     * @return string
     */
    public function getAuthorizationCode()
    {
        return $this->getSessionData('authorization_code');
    }
    /**
     * Return the session id (only when connected and session data is present)
     * @return string
     */
    public function getSessionId()
    {
        return $this->getSessionData('session_id');
    }
    /**
     * binds local user to current provider 
     * 
     * @param mixed $account_id id of the user
     * @access public
     * @return whether the model successfully saved
     */
    public function bindTo($account_id)
    {
        $this->account_id = $account_id;
        return $this->save();
    }
    /**
     * @access public
     * @return whether this oauth account linked to existing chatbot user
     */
    public function getIsBond()
    {
        return !empty($this->account_id);
    }
    /**
     * Check if user is connected to the chatbot provider
     * @return boolean
     */
    public function getIsConnected()
    {
        return !empty($this->session_data);
    }
}
