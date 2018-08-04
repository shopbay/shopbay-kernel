<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * This is the model class for table "oauth_clients".
 * But written in Yii1 CActiveRecord, and is used internally by api action classes
 * As a internall tweak to get client_id/client_secret for accounts (oauth2)
 *
 * The followings are the available columns in table 'oauth_clients':
 * @property string $client_id
 * @property string $client_secret
 * @property string $redirect_uri
 * @property string $grant_types
 * @property string $scope
 * @property string $user_id
 * 
 * @author kwlok
 */
class ApiOauthClient extends CActiveRecord 
{ 
    const REDIRECT_URI = 'https://shopbay-api-app/';//indicator of internal use
    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Brand the static model class
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
        return 'oauth_clients';
    }
    /**
     * Validation rules for model attributes
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            ['client_id, client_secret, redirect_uri, grant_types', 'required'],
            ['client_id, client_secret', 'length', 'max'=>32],
            ['user_id', 'length', 'max'=>12],
            ['redirect_uri', 'length', 'max'=>1000],
            ['grant_types', 'length', 'max'=>100],
            ['scope', 'length', 'max'=>2000],
        ];
    }
    /**
     * Behaviors for this model
     */
    public function behaviors()
    {
        return [
            'accountobjectbehavior' => [
                'class'=>'common.components.behaviors.AccountObjectBehavior',
                'accountAttribute'=>'user_id',
            ],
        ];
    }    

    public static function getClientInfo($accountId,$grant_type='client_credentials')
    {
        $criteria = new CDbCriteria();
        $criteria->addColumnCondition(array(
            'redirect_uri'=>self::REDIRECT_URI,
            'user_id'=>$accountId,
        ));
        $client = ApiOauthClient::model()->find($criteria);
        if ($client===null){
            $client = new ApiOauthClient();
            $client->client_id = md5(uniqid($accountId,true));
            $client->client_secret = md5(uniqid($accountId.time(),true));
            $client->redirect_uri = self::REDIRECT_URI;
            $client->user_id = $accountId;
            $client->grant_types = $grant_type;
            $client->save();
        }
        return [
            'id'=>$client->client_id,
            'secret'=>$client->client_secret,
        ];
    }
}