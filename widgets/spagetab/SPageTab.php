<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.widgets.SWidget");
/**
 * STab widget class file.
 *
 * @author kwlok
 */
class SPageTab extends SWidget
{
    /**
     * The path alias to access assets
     * @property string
     */
    public $pathAlias = 'common.widgets.spagetab.assets';
    /**
     * string the asset name of the widget
     */
    public $assetName = 'spagetab';    
    /**
     * Container name; Default to null; If there is a name, it will be displayed
     */
    public $name;    
    /**
     * Tabs definitions
     * Data structure:
     * array(
     *  'key'=>'tab key', //optional
     *  'title=>'tab title',
     *  'content'=>'tab content',
     * )
     */
    public $tabs;    
    /**
     * Run widget
     * @throws CException
     */
    public function run()
    {
        $this->render('index');
    }
    
    public function getTabs()
    {
        if (isset($this->tabs) && is_array($this->tabs)) {
            $tabs = new CMap();
            foreach ($this->tabs as $index => $tab) {
                if (!is_array($tab))
                    throw new CException(Sii::t('sii','SPageTab tabs must be array'));            
                if (!array_key_exists('title', $tab))
                    throw new CException(Sii::t('sii','SPageTab tabs must have title'));            
                if (!array_key_exists('content', $tab))
                    throw new CException(Sii::t('sii','SPageTab tabs must have content'));            
                $key = isset($tab['key'])?$tab['key']:'pagetab_view_'.$index;
                $tabs->add($key,array(
                    'title'=>$tab['title'],
                    'view'=>'common.widgets.spagetab.views._tab',
                    'data'=>array('content'=>$tab['content']),
                ));
            }
            return $tabs->toArray();
        }
        else
            return array(//sample tabs
                'tab1'=>array(
                    'title'=>'Tab 1',
                    'view'=>'common.widgets.spagetab.views._tab',
                    'data'=>array('content'=>'content for tab 1'),
                ),
                'tab2'=>array(
                    'title'=>'Tab 2',
                    'view'=>'common.widgets.spagetab.views._tab',
                    'data'=>array('content'=>'content for tab 2'),
                ),
            );
    }
    
}
