<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of WitActionInterface
 * 
 * The interface methods are to be the implementation of the method defined in the Wit.ai actions / stories.
 * 
 * @author kwlok
 */
interface WitActionInterface 
{
    /**
     * Say action  
     * @see WitActionMapping
     */
    public function say($sessionId,$message,$context,$entities);
    /**
     * @see Definition in the Wit.ai actions / stories.
     */
    public function findProduct($sessionId, $context, $entities = []);
    /**
     * @see Definition in the Wit.ai actions / stories.
     */
    public function findGreetingPerson($sessionId, $context, $entities = []);
}
