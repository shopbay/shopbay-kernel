<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of ActivitiesModule
 *
 * @author kwlok
 */
class ActivitiesModule extends SModule 
{
    public function init()
    {
        $this->setImport([
            'activities.models.*',
            'activities.components.*',
        ]);

    }
}
