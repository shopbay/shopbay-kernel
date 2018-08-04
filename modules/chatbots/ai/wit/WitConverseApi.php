<?php
/**
 * This file is part of Shopbay.org (https://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
use Tgallice\Wit\ConverseApi;
use Tgallice\Wit\Client;
use Tgallice\Wit\Model\Context;
/**
 * Wrapper of \Tgallice\Wit\ConverseApi to support logging and further customization if any
 *
 * @author kwlok
 */
class WitConverseApi extends ConverseApi
{
    /**
     * @var Client
     */
    private $client;
    /**
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }    
    /**
     * @param string $sessionId
     * @param string|null $text
     * @param Context|null $context
     *
     * @return array
     */
    public function converse($sessionId, $text = null, Context $context = null)
    {
        $query = [
            'session_id' => $sessionId,
        ];

        if (!empty($text)) {
            $query['q'] = $text;
        }

        $body = null;
        // Don't send empty array
        if (null !== $context && !$context->isEmpty()) {
            $body = $context;
        }

        logInfo(__METHOD__." Sending query = ".json_encode($query));
        logInfo(__METHOD__." Sending context = ".json_encode($context->jsonSerialize()));
        
        $response = $this->client->send('POST', '/converse', $body, $query);

        logInfo(__METHOD__.' Getting response = '.json_encode($this->decodeResponse($response)));
        
        return $this->decodeResponse($response);
    }
}
