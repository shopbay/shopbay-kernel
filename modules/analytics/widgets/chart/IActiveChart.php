<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * ActiveChart Interface
 * @author kwlok
 */
interface IActiveChart 
{
    /**
     * Get data for charting
     * @param type $metrics
     * @return array
     */
    public function getData($metrics);
}

