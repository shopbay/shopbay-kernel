<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of ServiceWorkflowException
 *
 * @author kwlok
 */
class ServiceWorkflowException extends ServiceException
{
    protected $name = 'Service Workflow Error';
    protected $code = self::WORKFLOW_ERROR;
}
