<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of HostedFieldsTrait
 *
 * @author kwlok
 */
trait HostedFieldsTrait 
{
    protected $iconBaseUrl;
    protected $braintreeCustomJs = 'braintree-custom.js';
    protected $braintreeCSS = 'braintree-custom.css';
    /**
     * Init widget
     */
    public function init() 
    {
        $assets = dirname(__FILE__).'/../assets/images';
        $this->iconBaseUrl = Yii::app()->assetManager->publish($assets);
        $this->type = 'custom';
        parent::init();
    }   
    /**
     * Run widget
     */
    public function run()
    {
        $this->registerCustomCSSFile();
        echo $this->getConfigTag();
        $this->renderForm();
        $this->includeBraintreeScript();
        $this->includeCustomScript();
    }    
    /**
     * @return Hosted Fields configurations
     */
    public function getOptions($excludeId=false)
    {
        $options = [
            'id'=>$this->containerId,
            'hostedFields'=> [
                'number'=> [
                    'selector' => '#card_number',
                    'placeholder' => Sii::t('sii','Card Number'),
                ],
                'cvv'=> [
                    'selector' => '#cvv',
                    'placeholder' => Sii::t('sii','CVV'),
                ],
                'expirationDate'=> [
                    'selector' => '#expiration_date',
                    'placeholder' => 'MM/YYYY',
                ],
                'styles' => [
                    'input' => [
                      'font-family' => 'sans-serif',
                      'color' => 'black',
                    ],
                    'input:focus' => [
                      'outline' => 'none'
                    ],
                    'input.invalid' => [
                      'color' => 'red'
                    ],
                    'input.valid' => [
                      'color' => 'green'
                    ],
                ],
            ],
        ];
        
        if ($excludeId)
            return json_encode($options['hostedFields']);
        else 
            return json_encode($options);
    }
    
    public function pulishImages()
    {
        $assets = dirname(__FILE__).'/../assets/images';
        $baseUrl = Yii::app()->assetManager->publish($assets);
    }
    /**
     * Register custom css file
     */
    public function registerCustomCSSFile()
    {
        $assets = dirname(__FILE__).'/../assets/css';
        $baseUrl = Yii::app()->assetManager->publish($assets);
        if(is_dir($assets)){
            $css = <<<EOF
<link rel="stylesheet" type="text/css" href="$baseUrl/$this->braintreeCSS">
EOF;
            echo $css;
        }
    }
    /**
     * Publish and include custom scripts
     * @throws Exception
     */
    public function includeCustomScript()
    {
        $assets = dirname(__FILE__).'/../assets';
        $baseUrl = Yii::app()->assetManager->publish($assets,false,-1,YII_DEBUG);
        if(is_dir($assets)){
            $js = <<<EOJS
<script src="$baseUrl/js/$this->braintreeCustomJs"></script>
EOJS;
            echo $js;
        } else {
            logError(__METHOD__.' Could not find assets to publish: '.$js);
            throw new CException(__CLASS__.' Error: Could not find assets to publish.');
        }
    }    
    
    public function getCreditCardIcons()
    {
        return [
            'visa'=>[
                'imageUrl'=>$this->iconBaseUrl.'/visa.png',
                'alt'=>Sii::t('sii','Visa'),
            ],
            'master-card'=>[
                'imageUrl'=>$this->iconBaseUrl.'/master-card.png',
                'alt'=>Sii::t('sii','Master Card'),
            ],
            'american-express'=>[
                'imageUrl'=>$this->iconBaseUrl.'/american-express.png',
                'alt'=>Sii::t('sii','American Express'),
            ],            
            'unionpay'=>[
                'imageUrl'=>$this->iconBaseUrl.'/unionpay.png',
                'alt'=>Sii::t('sii','Union Pay'),
            ],
            'maestro'=>[
                'imageUrl'=>$this->iconBaseUrl.'/maestro.png',
                'alt'=>Sii::t('sii','Maestro'),
            ],
            'discover'=>[
                'imageUrl'=>$this->iconBaseUrl.'/discover.png',
                'alt'=>Sii::t('sii','Discover'),
            ],
            'jcb'=>[
                'imageUrl'=>$this->iconBaseUrl.'/jcb.png',
                'alt'=>Sii::t('sii','JCB'),
            ],
        ];
    }
    
    public static function getCreditCardIconBaseUrl()
    {
        $assets = dirname(__FILE__).'/../assets/images';
        return Yii::app()->assetManager->publish($assets);
    }
   
}
