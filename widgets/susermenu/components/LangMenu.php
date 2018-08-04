<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.widgets.susermenu.components.UserMenu");
Yii::import("common.widgets.susermenu.components.UserMenuItem");
/**
 * Description of LangMenu
 * @todo Bring from user language menu into here
 * 
 * @author kwlok
 */
class LangMenu extends UserMenu 
{
    /**
     * Constructor
     * @param type $user
     */
    public function __construct($user,$config=[]) 
    {
        $this->user = $user;
        $this->loadConfig($config);
        
        $langform  = CHtml::form($this->langHostInfo.'/site/locale','post',['id'=>'langform']);
        $langform .= CHtml::hiddenField('language', $user->getLocale());
        $langform .= CHtml::endForm(); 
        
        $this->items[static::$lang] = new UserMenuItem([
            'id'=> static::$lang,
            'label'=>$langform.'<span class="mobile-display-only">'.Sii::t('sii','Language').'</span>',
            'icon'=>'<i class="fa fa-globe"></i>',
            'iconDisplay'=>$this->iconDisplay,
            'iconPlacement'=>$this->iconPlacement,
            'url'=>'javascript:void(0);',
            'cssClass'=>$user->isGuest?'language-menu':'quickaccess language',
            'items'=>$this->languages,
        ]);        
    }       
    
    protected function getLangHostInfo()
    {
        return request()->getHostInfo();//shop on submain will be the host info
    }    
    
    protected function getLanguages() 
    {
        $languages = [];
        foreach (SLocale::getLanguages() as $key => $value) {
            $languages[] = ['label'=>$value, 'url'=>'javascript:void(0);','linkOptions'=>['onclick'=>'switchlang("'.$key.'")']];
        }
        return $languages;
    }
}
