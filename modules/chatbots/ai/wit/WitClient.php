<?php
/**
 * This file is part of Shopbay.org (https://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
use Tgallice\Wit\HttpClient\GuzzleHttpClient;
use Tgallice\Wit\Client;
use GuzzleHttp\Psr7\Response;
/**
 * Wrapper of \Tgallice\Wit\WitClient to support logging and further customization if any
 * This class is customized for more debug information and also error tracking.
 * 
 * Note 1: the method validateResponse() type is changed to GuzzleHttp\Psr7\Response instead of 
 * Psr\Http\Message\ResponseInterface as used in parent class Client
 * 
 * @todo This class customizatino ideally is not required. Unless we find a permanent fix for Note 1 above 
 * 
 * @author kwlok
 */
class WitClient extends Client
{
    /**
     * @var string Wit app token
     */
    private $accessToken;
    /**
     * @var string
     */
    private $apiVersion;
    /**
     * @var HttpClient client
     */
    private $client;
    /**
     * @var ResponseInterface|null
     */
    private $lastResponse;
    /**
     * Constructor
     * @param type $accessToken
     * @param HttpClient $httpClient
     * @param type $apiVersion
     */
    public function __construct($accessToken, HttpClient $httpClient = null, $apiVersion = self::DEFAULT_API_VERSION)
    {
        $this->accessToken = $accessToken;
        $this->apiVersion = $apiVersion;
        $this->client = $httpClient!=null  ? $httpClient : $this->defaultHttpClient();
    }    
    /**
     * @inheritdoc
     */
    public function send($method, $uri, $body = null, array $query = [], array $headers = [], array $options = [])
    {
        $this->validateMethod($method);
        logTrace(__METHOD__.' Validate method ok');
        
        $headers = array_merge($this->getDefaultHeaders(), $headers);
        
        try {
            $this->lastResponse = $this->client->send($method, $uri, $body, $query, $headers, $options);
            logInfo(__METHOD__.' Getting response = '.json_encode($this->lastResponse));
            
        } catch (Exception $ex) {
            logError(__METHOD__.' Client sending error '.$ex->getMessage(),$ex->getTraceAsString());
        }

        $this->validateResponse($this->lastResponse);
        logTrace(__METHOD__.' Validate response ok');

        return $this->lastResponse;
    }
    
    /**
     * @param ResponseInterface $response (Change type to 'GuzzleHttp\Psr7\Response' from 'Psr\Http\Message\ResponseInterface')
     *                                     @todo As getting type error. Very likely caused by the underlying version of Guzzle
     * @throws BadResponseException
     */
    private function validateResponse(Response $response)//change type from ResponseInterface
    {
        if ($response->getStatusCode() !== 200) {
            $message = empty($response->getReasonPhrase()) ? 'Bad response status code' : $response->getReasonPhrase();
            throw new BadResponseException($message, $response);
        }
    }
    /**
     * @return HttpClient
     */
    private function defaultHttpClient()
    {
        return new GuzzleHttpClient();
    }
    /**
     * @param $method
     *
     * @throw \InvalidArgumentException
     */
    private function validateMethod($method)
    {
        if (!in_array(strtoupper($method), self::$allowedMethod)) {
            throw new \InvalidArgumentException(sprintf('"%s" is not in the allowed methods "%s"', $method, implode(', ', self::$allowedMethod)));
        }
    }    
    /**
     * Get the defaults headers like the Authorization field
     *
     * @return array
     */
    private function getDefaultHeaders()
    {
        return [
            'Authorization' => 'Bearer '.$this->accessToken,
            // Used the accept field is needed to fix the API version and avoid BC break from the API
            'Accept' => 'application/vnd.wit.'.$this->apiVersion.'+json',
        ];
    }    
    
}
