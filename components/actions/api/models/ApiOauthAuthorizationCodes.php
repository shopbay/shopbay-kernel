<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * This is the model class for table "oauth_authorization_codes".
 *
 * The followings are the available columns in table 'oauth_authorization_codes':
 * @property string $authorization_code
 * @property string $client_id
 * @property string $user_id
 * @property string $redirect_uri
 * @property string $expires
 * @property string $scope
 * 
 * @author kwlok
 */
class ApiOauthAuthorizationCodes extends CActiveRecord 
{ 
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
        return 'oauth_authorization_codes';
    }
    /**
     * Validation rules for model attributes
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            ['client_id, authorization_code, expires, redirect_uri', 'required'],
            ['authorization_code', 'length', 'max'=>40],
            ['client_id', 'length', 'max'=>32],
            ['user_id', 'length', 'max'=>12],
            ['redirect_uri', 'length', 'max'=>1000],
            ['expires', 'length', 'max'=>15],//timestamp value
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
}