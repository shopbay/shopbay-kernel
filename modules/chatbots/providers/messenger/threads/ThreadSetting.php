<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of ThreadSetting
 *
 * @author kwlok
 */
abstract class ThreadSetting extends CComponent
{
    CONST CALL_TO_ACTIONS = 'call_to_actions';
    CONST GREETING = 'greeting';
    /**
     * Thread setting type
     * @var string
     */
    protected $type;
    /**
     * Constructor.
     * @param string $type
     * @param string $state
     */
    public function __construct($type)
    {
        $this->type = $type;
    }
    /**
     * Get data 
     * @return array
     */
    public function getData()
    {
        return [
            'setting_type' => $this->type,
        ];
    }    
}
