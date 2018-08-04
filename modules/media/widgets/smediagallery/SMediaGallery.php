<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.widgets.SWidget");
/**
 * Description of SMediaGallery
 *
 * @author kwlok
 */
class SMediaGallery extends SWidget 
{
    /**
     * The path alias to access assets
     * @property string
     */
    public $pathAlias = 'common.modules.media.widgets.smediagallery.assets';
    /**
     * string the asset name of the widget
     */
    public $assetName = 'smediagallery';    
    /**
     * string the url route for processing when media is selected
     */
    public $route;  
    /**
     * string the mime type filter; Default to "image"
     * Can be "video", "audio" etc
     */
    public $mimeTypeFilter = 'image';  
    /**
     * string the container id
     */
    public $container;
    /**
     * string the asset name of the widget
     */
    public $formModel;    
    /**
     * boolean if true, it will only return partial view (for pagination use)
     */
    public $paginationMode = false;    
    /**
     * Run widget
     * @throws CException
     */
    public function run()
    {
        if ($this->paginationMode){
            echo $this->renderGallery();
        }
        else 
            $this->render('index',[
                'route'=>$this->route,
                'container'=>$this->container,
                'formModel'=>$this->formModel,
            ]);
                
    }
    
    protected function renderGallery()
    {
        return $this->widget('common.widgets.SListView', [
                'dataProvider'=>new CActiveDataProvider(Media::model()->mine()->mimeType($this->mimeTypeFilter)->all(),[
                    'criteria'=>['order'=>'update_time DESC'],
                    'pagination'=>['pageSize'=>Config::getSystemSetting('record_per_page')],
                ]),
                'template' => '{items}{pager}{summary}',
                'itemView'=>'_listview',
                'htmlOptions'=>[],
            ],true);    
    }
}
