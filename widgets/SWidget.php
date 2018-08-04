<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * SWidget class file.
 * @author kwlok
 */
abstract class SWidget extends CWidget
{
    /**
     * string the id of the widget
     */
    public $id;
    /**
     * string the asset name of the widget
     */
    public $assetName;
    /**
     * The path alias to access assets
     * @property string
     */
    public $pathAlias;
    /**
     * Init widget
     * Attach AssetLoaderBehavior
     */ 
    public function init()
    {
        //if not informed will generate Yii defaut generated id, since version 1.6
        if(!isset($this->id))
            $this->id = $this->getId();
        
        $this->attachBehaviors($this->behaviors());
        
        //publish the required assets
        $this->publishAssets();
    }
    /**
     * Behaviors for this class
     */
    public function behaviors()
    {
        if ($this->pathAlias==null)
            throw new CException(Sii::t('sii','SWidget must have path alias'));
        if ($this->assetName==null)
            throw new CException(Sii::t('sii','SWidget must have asset name'));
        
        return [
            'assetloader' => [
                'class'=>'common.components.behaviors.AssetLoaderBehavior',
                'name'=>$this->assetName,
                'pathAlias'=>$this->pathAlias,
            ],
        ];
    }	
    /**
     * Function to publish and register assets on page 
     * @throws CException
     */
    public function publishAssets()
    {
        $this->registerCssFile($this->pathAlias.DIRECTORY_SEPARATOR.'css',$this->assetName.'.css');
        $this->registerScriptFile($this->pathAlias.DIRECTORY_SEPARATOR.'js',$this->assetName.'.js');
    }    

}

    