<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of SMessageSource
 *
 * @author kwlok
 */
class SMessageSource extends CPhpMessageSource 
{
    private $_m;//current module that is being accessed for the message source
    /**
     * Loads the message translation for the specified language and category.
     * @param string $category the message category
     * @param string $language the target language
     * @return array the loaded messages
     */
    protected function loadMessages($category,$language)
    {
        //skip image module (non exists) <-- this is trigger when doing image thumbnail
        if (substr($category, 0, 11)=='ImageModule')
            $category = 'ImagesModule';
        $messages = parent::loadMessages($category, $language);
        if ($this->_m!=null){
            logTrace(__METHOD__." loading sii for $this->_m ...");
            $module = Yii::app()->getModule($this->_m);
            if ($module instanceof SModule){
                foreach ($module->getDependencySii() as $alias) {
                    $sii = explode('.', $alias);
                    //@todo should use pathofAlias. app modules are not available in shopbay-kernel 
                    $moduleSource = require(ROOT.DIRECTORY_SEPARATOR.$sii[0].DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$sii[1].DIRECTORY_SEPARATOR.'messages'.DIRECTORY_SEPARATOR.$language.DIRECTORY_SEPARATOR.'sii.php');
                    $messages = array_merge($messages,$moduleSource);
                }
                //logTrace(__METHOD__,$messages);
            }
        }
        return $messages;
    }
    
    public function setModule($id)
    {
        $this->_m = $id;
        logTrace(__METHOD__." set to $id ok");
    }
}
