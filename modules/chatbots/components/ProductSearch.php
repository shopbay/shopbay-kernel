<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.components.ChatbotSearch');
/**
 * Description of ProductSearch
 *
 * @author kwlok
 */
class ProductSearch extends ChatbotSearch 
{
    /**
     * Constructor
     * @param $shopId The shop to search product on 
     */
    public function __construct($shopId)
    {
        parent::__construct('SearchProduct');
        $this->addFilter('shop_id', $shopId);
    }
}
