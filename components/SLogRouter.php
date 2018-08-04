<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of SLogRouter
 *
 * @author kwlok
 */
class SLogRouter extends CLogRouter
{
    public $autoFlush = 20;
    public $autoDump = false;

    public function init()
    {
        parent::init();
        $logger = Yii::getLogger();
        $logger->autoFlush = $this->autoFlush;
        $logger->autoDump = $this->autoDump;
    }
	
}