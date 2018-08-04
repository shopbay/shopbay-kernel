<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of InvalidUserCredentialsException
 *
 * @author kwlok
 */
class InvalidUserCredentialsException extends CHttpException 
{
    public function __construct($message = null, $code = 0) 
    {
        parent::__construct(401, $message, $code);
    }
}
