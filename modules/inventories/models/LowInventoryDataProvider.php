<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of LowInventoryDataProvider
 *
 * @author kwlok
 */
class LowInventoryDataProvider 
{
    public $shop_id = -1;//default, this shop_id is needed for notification
    public $shop_name = 'Demo Shop';//default
    public $locale = 'en_sg';//default
    /**
     * Example data:
     * array(
     *   array(
     *     'id' => '-1',
     *     'sku' => '637346',
     *     'product_name' => '{"zh_cn":"\u6253\u53d1\u6253\u53d1\u8eab\u4efd\u53ef","en_sg":"First Product"}',
     *     'image_url' => '/files/images/default.jpg',
     *     'quantity' => '3',
     *     'available' => '0',
     *   ),
     *   array (
     *     'id' => '-1',
     *     'sku' => 'SET329-cL-3B',
     *     'product_name' => '{"zh_cn":"\u7b2c\u4e8c\u4ea7\u54c1","en_sg":"Second Product"}',
     *     'image_url' => '/files/images/default.jpg',
     *     'quantity' => '19',
     *     'available' => '3',
     *   ),
     *  );
     * @var type 
     */
    public $items = array();//items that have low stock level
    
    public function hasItems()
    {
        return $this->getItemsCount()>0;   
    }
    
    public function getItemsCount()
    {
        return count($this->items);
    }
    
    public function getShopName()
    {
        $name = '';
        foreach ($this->items as $item) {
            $name = $item['shop_name'];//retreive from first item
            break;
        }
        return $name;
    }
    
    public function getShopLocale()
    {
        $locale = $this->locale;
        foreach ($this->items as $item) {
            $locale = $item['locale'];//retreive from first item
            break;
        }
        return $locale;
    }
                
}
