<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.components.actions.api.models.ApiOauthClient');
/**
 * Description of ApiOauthClientTrait
 *
 * @author kwlok
 */
trait ApiOauthClientTrait 
{
    /**
     * Find oauth client
     * @param string $clientId
     * @throws CException
     */
    protected function findOAuthClient($clientId)
    {
        $criteria = new CDbCriteria();
        $criteria->addColumnCondition([
            'client_id'=>$clientId,
            'user_id'=>Account::SYSTEM,
            'grant_types'=>'authorization_code',
        ]);
        $client = ApiOauthClient::model()->find($criteria);
        if ($client===null)
            throw new CException('OAuth client not found');
        else
            return $client;
    } 
    
}
