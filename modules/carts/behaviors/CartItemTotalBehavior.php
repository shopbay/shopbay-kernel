<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of CartItemTotalBehavior
 *
 * @author kwlok
 */
class CartItemTotalBehavior extends CBehavior 
{
    /**
     * Returns total price for all units of the position
     * This price is after discount price, if has discount
     * 
     * @param bool $includeShippingSurcharge
     * @return float
     */
    public function getTotalPrice($includeShippingSurcharge = true) 
    {
        $sum = $this->owner->getQuantity() * $this->owner->getPrice();//getPrice() is after discount if any
        $sum += $this->getTotalOptionFee();
        if ($includeShippingSurcharge==true)
            $sum +=  $this->getTotalShippingSurcharge();
        return $sum;
    }
    /**
     * Returns total option fee of the position
     * @return float
     */
    public function getTotalOptionFee() 
    {
        return $this->owner->getQuantity() * $this->owner->getOptionFee();
    }
    /**
     * Returns total shipping surcharge of the position
     * @return float
     */
    public function getTotalShippingSurcharge() 
    {
        return $this->owner->getQuantity() * $this->owner->getShippingSurcharge();
    }
    /**
     * Returns total weight for all units of the position
     * @return float
     */
    public function getTotalWeight() 
    {
        return $this->owner->getQuantity() * ($this->owner->getWeight()==null?0:$this->owner->getWeight());
    }
    
}