<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.widgets.SWidget");
/*
 * SChildForm widget class file.
 *
 * @author kwlok
 */
class SChildForm extends SWidget
{
    /**
     * The path alias to access assets
     * @property string
     */
    public $pathAlias = 'common.widgets.schildform.assets';
    /**
     * string the asset name of the widget
     */
    public $assetName = 'schildform';    
    /**
     * string the state variable name of the widget
     */
    public $stateVariable;    
    /**
     * string the state variable name of the widget
     */
    public $formView = '_form';    
    /**
     * string the header data
     */
    public $headerData;    
    /**
     * string the div section; If set (not null), this will be inserted above table 
     */
    public $divSection;    
    /**
     * string the script to run after widget is loaded
     * Other value is "all"
     */
    public $runScript;    
    /**
     * string control how delete buttons to be shown; Default to "last", only the last row will have delete button
     * Other value is "all"
     */
    public $deleteControl = self::DELETE_CONTROL_LAST;    
    /**
     * @var array
     */
    public $htmlOptions = array();    
    /**
     * Run widget
     * @throws CException
     */
    public function run()
    {
        if (!($this->deleteControl==self::DELETE_CONTROL_ALL||
              $this->deleteControl==self::DELETE_CONTROL_LAST))
            throw new CException('Invalid setup for delete control');
        
        $this->render('index');
    }
    
    public function getHasData()
    {
        return SActiveSession::exists($this->stateVariable);     
    }
    
    public function loadData()
    {
        return SActiveSession::load($this->stateVariable);     
    }
    
    public function getHeader()
    {
        $header = '';
        foreach ($this->headerData as $value) {
            $header .= CHtml::tag('th',array(),$value);
        }
        $header .= CHtml::tag('th');//empty column catered for delete button
        return $header;     
    }
    
    public function htmlOptionsToString()
    {
        $string = '';
        foreach ($this->htmlOptions as $key => $value) {
            $string .= $key.'="'.$value.'" ';
        }
        return $string;     
    }    
    
    protected function registerRunScript()
    {
        $minified = $this->getDeleteScript();
        if (isset($this->runScript)){
            Yii::import('common.extensions.escriptboost.EScriptBoost');
            $minified .= EScriptBoost::minifyJs($this->runScript,EScriptBoost::JS_MIN);
        }
        cs()->registerScript(__CLASS__.'runscript',$minified,CClientScript::POS_END);
    }
    
    protected function getDeleteScript()
    {
        $selector = '';
        if ($this->deleteControl==self::DELETE_CONTROL_LAST)
            $selector = ':'.$this->deleteControl;
        
        return '$(".del-button'.$selector.'").show();';
    }
    
    const DELETE_CONTROL_ALL  = 'all';
    const DELETE_CONTROL_LAST = 'last';
    
}
