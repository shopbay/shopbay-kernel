<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.threads.PostbackMenuItem');
/**
 * Description of PayloadMenuItem
 *
 * @author kwlok
 */
class PayloadMenuItem extends PostbackMenuItem
{
    /**
     * Constructor.
     * @param string $payload
     */
    public function __construct($payload)
    {
        parent::__construct(null,$payload);//title set to null; not needed
    }
    /**
     * OVERRIDDEN
     * Omitting $type and $title
     * @return array
     */
    protected function getData()
    {
        return [
            'payload' => $this->payload,
        ];    
    }
}
