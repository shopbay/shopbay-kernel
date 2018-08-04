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
class AdminController extends HelpBaseController 
{
    public function init()
    {
        parent::init();
        $this->pageTitle = Sii::t('sii','Administrator Help');
    }
    /**
     * Display the helpfile.
     */
    public function actionIndex($doc=null)
    {
        $doc = isset($doc)?$doc:$this->helpfile;
        if (substr($doc, 0 , 8)=='merchant')//extract doc name only for merchant guides
            $doc = str_replace ('/', DIRECTORY_SEPARATOR.user()->getLocale().DIRECTORY_SEPARATOR, $doc);
        
        if ($this->otherGuide('merchant',$doc))
            $doc = $this->merchantDocpath.DIRECTORY_SEPARATOR.$doc;
        else
            $doc = $this->docpath.DIRECTORY_SEPARATOR.$doc;
        
        $this->render('index',array('helpfile'=>$doc));
        
    }
    
    protected function otherGuide($type,$doc)
    {
        $docpath = $this->docpath.DIRECTORY_SEPARATOR.$type.DIRECTORY_SEPARATOR.user()->getLocale();
        if ($handle = opendir($docpath)) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != ".." && $file != "README.md" && $file==$doc) {
                    $found = true;
                    logTrace($doc.' found');
                    break;
                }
            }
            closedir($handle);
        }
        return isset($found)?true:false;
    }
    
    protected function getMerchantDocpath()
    {
        return $this->docpath.DIRECTORY_SEPARATOR.'merchant'.DIRECTORY_SEPARATOR.user()->getLocale();
    }
}