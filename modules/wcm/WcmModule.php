<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of WcmModule
 *
 * @author kwlok
 */
class WcmModule extends SModule 
{
    /**
     * Init
     */
    public function init()
    {
        // import the module-level models and components
        $this->setImport([
            'wcm.models.*',
        ]);
    }
}