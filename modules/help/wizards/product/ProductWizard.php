<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.help.wizards.base.WizardContainer');
Yii::import('common.modules.help.wizards.product.*');
/**
 * Description of ProductWizard
 *
 * @author kwlok
 */
class ProductWizard extends WizardContainer
{
    /**
     * Wizard constructor
     * @param Product $product Product model
     */
    public function __construct($product,$profile)
    {
        parent::__construct(__CLASS__.$product->id);
        $this->setProfile(new $profile($product));
        $this->setName($this->profile->name);
        $this->setProduct($product);
    }
    /**
     * @return behaviors
     */
    public function behaviors()
    {
        return [
            'class'=>'common.modules.help.wizards.product.ProductWizardBehavior',
        ];
    }    
}
