<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of HelpBaseController
 *
 * @author kwlok
 */
class HelpBaseController extends AuthenticatedController 
{
    const README = 'README';
    protected $docpath = 'undefined';
    protected $helpfile = 'undefined';

    public function init()
    {
        parent::init();
        $this->docpath = $this->getDocpath();
        $this->helpfile = self::README.'.md';//markdown format
        $this->menu = array(
            array('id'=>'ticket','title'=>Sii::t('sii','Support Center'),'subscript'=>Sii::t('sii','support'), 'url'=>url('tickets/management/create'),'linkOptions'=>array('class'=>'primary-button')),
        );
    }
    /**
     * Display the helpfile.
     */
    public function actionIndex($doc=null)
    {
        if ($doc==null)
            $doc = $this->helpfile;
        $this->render('index',array('helpfile'=>$this->docpath.DIRECTORY_SEPARATOR.$doc));
    }

    public function getDocpath() 
    {
        return dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'docs';
    }

    public function getBreadcrumbs($helpfile) 
    {
        $topic = $this->getHelpTopic($helpfile);
        if ($topic==self::README){
            return array(
                Sii::t('sii','Help Center')
            );
        }
        else{
            //resolve topic containing locale info
            $topic = str_replace('Merchant'.DIRECTORY_SEPARATOR.user()->getLocale().DIRECTORY_SEPARATOR,'',$topic);
            
            return array(
                Sii::t('sii','Help')=>url('help'),
                $topic,
            );
        }
    }

    public function getHelpTopic($helpfile) 
    {
        $file = substr($helpfile, strlen($this->docpath)+1);
        return Sii::t('sii',ucfirst(str_replace('.md', '', $file)));
    }
}