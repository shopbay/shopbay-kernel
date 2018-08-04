<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * IdentityMerchant represents the data needed to identity a merchant.
 * It contains the authentication method that checks if the provided data can identity the user.
 *
 * @author kwlok
 */
class IdentityMerchant extends IdentityUser
{
    protected $_role = Role::MERCHANT;
}