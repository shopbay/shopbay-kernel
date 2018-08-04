<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of ProductWizardBehavior
 *
 * @author kwlok
 */
class ProductWizardBehavior extends CBehavior 
{
    private $_p;//product model
    /**
     * @return Product model
     */
    public function setProduct($product)
    {
        $this->_p = $product;
    }    
    /**
     * @return Product model
     */
    public function getProduct()
    {
        return $this->_p;
    }    
}
