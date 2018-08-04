<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of ShopWizardBehavior
 *
 * @author kwlok
 */
class ShopWizardBehavior extends CBehavior 
{
    private $_s;//shop model
    /**
     * @return Shop model
     */
    public function setShop($shop)
    {
        $this->_s = $shop;
    }    
    /**
     * @return Shop model
     */
    public function getShop()
    {
        return $this->_s;
    }    
}
