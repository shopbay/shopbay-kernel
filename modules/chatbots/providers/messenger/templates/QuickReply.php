<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of QuickReply
 *
 * @author kwlok
 */
class QuickReply extends CComponent
{
    protected $title;//button title
    protected $payload;//button payload
    /**
     * Constructor.
     * @param string $title
     * @param string $payload
     */
    public function __construct($title,$payload)
    {
        $this->title = $title;
        $this->payload = $payload;
    }
    /**
     * @return array
     */
    protected function getData()
    {
        return [
            'content_type' => 'text',
            'title' => $this->title,
            'payload' => $this->payload,
        ];    
    }
}
