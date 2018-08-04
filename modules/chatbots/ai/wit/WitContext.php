<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
use Tgallice\Wit\Model\Context;
use Tgallice\Wit\Helper;
/**
 * Description of WitContext
 *
 * @author kwlok
 */
class WitContext extends Context
{
    /**
     * @see Definition in the Wit.ai actions / stories.
     */
    CONST GREETING_PERSON   = 'greeting_person';
    CONST PRODUCT_NOT_FOUND = 'product_not_found';
    CONST PRODUCT_MESSAGE   = 'product_message';
    CONST PAYLOAD           = 'payload';
    /**
     * @param $entityName
     * @param array $entities
     *
     * @return mixed
     */
    public static function getFirstEntityValue($entityName, array $entities)
    {
        return Helper::getFirstEntityValue($entityName,$entities);
    }
   
}
