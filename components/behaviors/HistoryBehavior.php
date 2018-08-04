<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of HistoryBehavior
 *
 * @author kwlok
 */
class HistoryBehavior extends CActiveRecordBehavior 
{
    /**
    * @var string The name of the model to performn history audit. Defaults to 'undefined'
    */
    public $model = 'undefined';

    public function recordHistory($attributes=array())
    {
        $_m = new $this->model;
        foreach ($attributes as $key => $value)
            $_m->{$key} = $value;
        if ($_m->save())
            logInfo($this->model.' record created',$_m->getAttributes());
        else
            logError($this->model.' fail to record',$_m->getErrors());
    }
    
}
