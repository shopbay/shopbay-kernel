<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.widgets.SWidget");
/**
 * Description of SPageSection
 *
 * @author kwlok
 */
class SPageSection extends SWidget
{
    /**
     * The path alias to access assets
     * @property string
     */
    public $pathAlias = 'common.widgets.spagesection.assets';
    /**
     * string the asset name of the widget
     */
    public $assetName = 'spagesection';
    /**
     * The icon mode of button, either text or image. Default to text. 
     * Options:
     * [1] image
     * [2] text
     * @var string
     */
    public $buttonMode = 'text';    
    /**
     * The data array of sections to render. 
     * @var string
     */
    public $sections = [];    
    /**
     * Show empty text if no section found
     * @var boolean
     */
    public $showEmptyText = true;    
    /**
     * Run widget
     * @throws CException
     */
    public function run()
    {
        if (count($this->sections)==0){
            if ($this->showEmptyText)
                echo Chtml::tag('div',['class'=>'sections'],Sii::t('sii','No data found.'));
            //else do nothing
        }
        else
            $this->render('index');
    }
    /**
     * This script will overwrite the existing pagination url to custom url.
     * This is required when parent page owning section views are having own view url that does not support section views pagination
     * @param type $prefixUrl
     */
    public static function loadPaginationUrlScript($paginationRoute,$id,$containerSelection,$searchKey)
    {
        $urlPrefix = url($paginationRoute.'/id/'.$id);
$script = <<<EOJS
        $('#$containerSelection .spager li a').each(function(idx,data){
            var oldUrl = $(this).attr('href');
            var newUrl = '$urlPrefix';
            var n = oldUrl.indexOf('$searchKey');
            if (n > 0)
                newUrl += '/' + oldUrl.substring(n);
            $(this).attr('href',newUrl);
        });
EOJS;
        Helper::registerJs($script);        
    }
    
}
