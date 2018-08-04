<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of OAuthSignupForm
 *
 * @author kwlok
 */
class OAuthSignupForm extends SignupForm
{
    /**
     * Profile information
     * array(
     *  'first_name' => <first_name>,
     *  'last_name' => <last_name>,
     *  'alias_name' => <alias_name>,
     *  'gender' => <gender>,
     *  'birthday' => <birthday>,
     *  'locale' => <locale>,
     * )
     * @var array 
     */
    public $profile = [];
}
