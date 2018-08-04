<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of PaymentPluginsTrait
 * A configuration container for all payment plugins settings
 * 
 * @see config file at ../modules/payments/plugins/plugins.php
 * 
 * @author kwlok
 */
trait PaymentPluginsTrait
{
    /*
     * Array to store all the plugins
     */
    private $_d;
    /**
     * @return string plugin path
     */
    protected function getPluginPath()
    {    
        return KERNEL.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'payments'.DIRECTORY_SEPARATOR.'plugins';
    }    
    /**
     * @return array plugins
     */
    public function getPlugins()
    {    
        if ($this->_d==null){
            $this->_d = include $this->getPluginPath().'/plugins.php';
            //logTrace(__METHOD__.' data',$this->_d);
            foreach ($this->_d as $plugin) {
                Yii::import($plugin['form']['class']);
                //logTrace(__METHOD__.' Import class..',$plugin['form']['class']);
                Yii::import($plugin['gateway']['class']);
                //logTrace(__METHOD__.' Import class..',$plugin['gateway']['class']);
            }
        } 
        return $this->_d;
    }    
    /**
     * Get plugin by type 
     * @param type $method
     * @param type $type
     * @param type $returnInstance true to create instance
     * @return \pluginName
     */
    public function getPlugin($method,$type,$returnInstance=true)
    {
        if ($method >= PaymentMethod::OFFLINE_PAYMENT)
            $method = PaymentMethod::OFFLINE_PAYMENT;  
        
        $plugins = $this->getPlugins();
        $pluginName = $plugins[$method][$type]['name'];
        if ($returnInstance){
            $plugin = new $pluginName();
            $plugin->init();
            return $plugin;
        }
        else
            return $pluginName;
    }
    /**
     * @return PaymentGateway
     */
    public function getPaymentGateway()
    {
        // Set the required components.
        $this->setComponents([
            'gateway'=>[
                'class'=>'PaymentGateway',
            ],                 
        ]);
        return $this->getComponent('gateway');
    }        
    /**
     * @return PayPalExpressCheckout
     */
    public function getPayPalExpress()
    {
        $gateway = $this->getPlugin(PaymentMethod::PAYPAL_EXPRESS_CHECKOUT, 'gateway');
        return $gateway->paypalApi;
    }           
}
