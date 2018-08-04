<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.accounts.users.SWebUser');
/**
 * Description of WebAdmin
 *
 * @author kwlok
 */
class WebAdmin extends SWebUser 
{
    public function afterLogin($fromCookie)
    {
        parent::afterLogin($fromCookie);
    }  
}