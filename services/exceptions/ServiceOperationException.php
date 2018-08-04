<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of ServiceOperationException
 *
 * @author kwlok
 */
class ServiceOperationException extends ServiceException
{
    protected $name = 'Service Operation Error';
    protected $code = self::OPERATION_ERROR;
}
