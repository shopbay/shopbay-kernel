<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.extensions.braintree.BraintreeApi');
/**
 * Description of BraintreeBaseForm
 *
 * You can use the following test credit card numbers when testing payments. 
 * The expiration date must be set to the present date or later:
 * Visa: 4111111111111111
 * Mastercard: 5555555555554444
 * American Express: 378282246310005
 * Discover: 6011111111111117
 * JCB: 3530111333300000
 *
 * @author kwlok
 */
abstract class BraintreeBaseForm extends CWidget
{
    protected $useRemotebraintreeJs = false;
    protected $braintreeLocalJs = 'braintree-web-2.17.4.js';
    protected $braintreeJs = 'https://js.braintreegateway.com/v2/braintree.js';
    protected $clientToken;
    protected $type;//either 'custom' or 'dropin'
    public    $apiMode;
    public    $merchantId;
    public    $publicKey;
    public    $privateKey;
    public    $merchantAccountId;//optional, if not set Braintree will use the default merchant account 
    public    $containerId = 'braintree_payment_form';
    /**
     * Init widget
     */
    public function init() 
    {
        try {
            $this->clientToken = $this->braintreeApi->getClientToken();
        } catch (Exception $ex) {
            logError(__METHOD__,$ex->getTraceAsString());
            throw new CException(Sii::t('sii','Failed to establish Braintree connection.'));
        }
        
        parent::init();
    }
    /**
     * Run widget
     */
    public function run()
    {
        $this->renderForm();
        $this->includeBraintreeScript();
        $this->renderBraintreeScript();
    } 
    /**
     * Include braintree script
     * @return mixed
     */
    public function includeBraintreeScript()
    {
        if ($this->useRemotebraintreeJs){
            $js = <<<EOJS
<script src="$this->braintreeJs"></script>
EOJS;
            echo $js;
        }
        else {
            $assets = dirname(__FILE__).'/../assets';
            $baseUrl = Yii::app()->assetManager->publish($assets,false,-1,YII_DEBUG);
            if(is_dir($assets)){
                $js = <<<EOJS
<script src="$baseUrl/js/$this->braintreeLocalJs"></script>
EOJS;
                echo $js;
            }
        }
    }
    /**
     * Render braintree setup script
     * @return mixed
     */
    public function renderBraintreeScript($echo=true)
    {
        $js = <<<EOJS
braintree.setup("$this->clientToken", "$this->type", $this->options);
EOJS;
        if ($echo)
            echo '<script>'.$js.'</script>';
        else
            return $js;
    }    
    /**
     * @return braintree sript options
     */
    abstract public function getOptions($excludeId=false);
    /**
     * @return Html form
     */
    abstract public function renderForm();
    /**
     * @return config in HTML tag
     */
    abstract public function getConfigTag();    
    /**
     * @return 
     */
    public function getBraintreeApi()
    {
        return new BraintreeApi($this->apiMode,$this->merchantId,$this->publicKey,$this->privateKey,$this->merchantAccountId);
    }
    
}
