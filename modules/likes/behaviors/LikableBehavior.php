<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of LikableBehavior
 *
 * @author kwlok
 */
class LikableBehavior extends CActiveRecordBehavior 
{
    /**
     * Like model filter for search; Default to "null"
     * @var type 
     */
    public $modelFilter;
    
    private $_counter;
    
    public function getLikeCounter()
    {
        if (!isset($this->_counter)){
            $like = Like::model()->{$this->modelFilter}($this->getOwner()->id)->find();
            if ($like===null)
                $this->_counter = 0;
            else 
                $this->_counter = $like->counter;
        }
        return $this->_counter;
    }    
}

