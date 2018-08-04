<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of MerchantController
 *
 * @author kwlok
 */
class MerchantController extends HelpBaseController 
{
    public function init()
    {
        parent::init();
        $this->pageTitle = Sii::t('sii','Help Center');
        //publish images for help files
        $this->module->publishImages();
    }
    /*
     * @overriden
     */
    public function getDocpath() 
    {
        return parent::getDocpath().DIRECTORY_SEPARATOR.$this->id.DIRECTORY_SEPARATOR.user()->getLocale();
    }

}
