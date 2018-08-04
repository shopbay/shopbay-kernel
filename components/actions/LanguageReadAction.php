<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.components.actions.ReadAction");
/**
 * Description of LanguageReadAction
 *
 * @author kwlok
 */
class LanguageReadAction extends ReadAction
{
    /**
     * Show page title based on default locale
     * @param type $model
     * @return string
     */
    public function getPageTitle($model)
    {
        $title = $model->displayName();
        if (isset($this->pageTitleAttribute))
            $title = $model->getLanguageValue($this->pageTitleAttribute).' | '.$title;
 
        return $title;
    }
}