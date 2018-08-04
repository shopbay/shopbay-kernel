<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * This is the interface Campaign
 *
 * @author kwlok
 */
interface ICampaign 
{
    public function getId();
    public function getAccountId();
    public function getShopId();
    public function getName();
    public function getType();
    public function getTypeColor();
    public function getOfferTypes();
    public function getCampaignText();    
    public function getValidityText($prefix=null);
    public function notExpired();
}
