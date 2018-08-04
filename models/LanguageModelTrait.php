<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of LanguageModelTrait
 *
 * @author kwlok
 */
trait LanguageModelTrait 
{  
    /**
     * A generic implementation to get all supported locale languages for this shop;
     * This can be extended to let individual shop have own supported languages
     * by having own implementation
     * @return array
     */    
    public function getLanguages()
    {
        return SLocale::getLanguages();
    }    
    /**
     * A generic implementation to get all supported locale languages for this shop;
     * This can be extended to let individual owner have own supported languages
     * by having own implementation
     * @return array
     */    
    public function getLanguageKeys()
    {
        return array_keys($this->getLanguages());
    }

}
