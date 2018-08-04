<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.widgets.SWidget");
Yii::import("common.widgets.sgridlayout.elements.*");
Yii::import("common.widgets.sgridlayout.widgets.*");
/**
 * Description of SGridLayout
 * Using Bootstrap as its underlying css framework 
 * 12 columns grid system
 * Refer to http://getbootstrap.com/css/#grid
 *
 * @author kwlok
 */
class SGridLayout extends SWidget
{
    /*
     * Element types
     */
    CONST ROW            = 'row';//row has columns 
    CONST COLUMN         = 'column';//column can has nested container
    CONST CONTAINER_BLOCK= 'containerblock';//container has both rows and columns (can be nested within column)
    CONST HTML_BLOCK     = 'htmlblock';
    CONST FIXTURE_BLOCK  = 'fixtureblock';
    CONST IMAGE_BLOCK    = 'imageblock';
    CONST SLIDE_BLOCK    = 'slideblock';
    CONST TEXT_BLOCK     = 'textblock';
    CONST LIST_BLOCK     = 'listblock';
    CONST CATEGORY_BLOCK = 'categoryblock';
    CONST MENU_BLOCK     = 'menublock';
    CONST BOX_BLOCK      = 'boxblock';
    /**
     * The path alias to access assets
     * @property string
     */
    public $pathAlias = 'common.widgets.sgridlayout.assets';
    /**
     * string the asset name of the widget
     */
    public $assetName = 'sgridlayout';
    /**
     * Use .container for a responsive fixed width container.
     * Use .container-fluid for a full width container, spanning the entire width of your viewport.
     */
    public $container = 'container-fluid';//default container type
    /**
     * The view file to render container
     */
    public $containerViewFile = 'common.widgets.sgridlayout.views.index';
    /**
     * The view data to pass to container
     */
    public $containerViewData = [];
    /**
     * The layout config
     * @var array
     */
    public $layout = [];
    /**
     * When true the layout will set all elements to include modal forms
     * @var boolean 
     */
    public $modal = false;
    /**
     * The rows to be put inside the containter
     * @property array SGridRow
     */
    protected $rows = [];
    /**
     * Run widget
     * @throws CException
     */
    public function run()
    {
        if (empty($this->layout)){
            $this->layout = $this->getLayout();
        }
        foreach ($this->layout as $row) {
            //include modal setup (only control via widget instantiation)
            $this->rows[] = new SGridRow($this, $this->controller,array_merge($row,['modal'=>$this->modal]));//controller is a magic getter method of CWidget
        }
        $this->render($this->containerViewFile,$this->containerViewData);
    }
    /**
     * Get layout config (For child class to customize)
     * @return array Sample layout
     * @throws CException
     */
    public function getLayout()
    {
        return [];
//        return [
//            [
//                'name'=>'header',
//                'columns'=>[
//                    [
//                        'size'=>12,
//                        'html'=>[
//                            'en_sg'=>'12-size header section',
//                            'zh_cn'=>'12-尺寸页头区',
//                        ],
//                    ],
//                ],
//            ],
//            [
//                'name'=>'section',
//                'columns'=>[
//                    [
//                        'size'=>6,
//                        'html'=>[
//                            'en_sg'=>'6-size header section',
//                            'zh_cn'=>'6-尺寸页头区',
//                        ],
//                    ],
//                    [
//                        'size'=>6,
//                        'html'=>[
//                            'en_sg'=>'6-size header section',
//                            'zh_cn'=>'6-尺寸页头区',
//                        ],
//                    ],
//                ],
//            ],
//            [
//                'columns'=>[
//                    [
//                        'size'=>12,
//                        'type'=>SGridLayout::CATEGORY_BLOCK,
//                        'name'=>'trends_most_likes',
//                        'title'=>[
//                            'en_sg'=>'Most Likes Products',
//                        ],
//                        'category'=>'trends_page.mostliked',
//                        'itemsPerRow'=>4,
//                        'itemsLimit'=>4,
//                        'style'=>'background:blue;',
//                    ],
//                ],
//            ],
//            [
//                'name'=>'slide-block',
//                'columns'=>[
//                    [
//                        'size'=>12,
//                        'type'=>SGridLayout::SLIDE_BLOCK,
//                        'name'=>'slide-block',
//                        'items'=>[
//                            [
//                                'image' => 'https://www.skb.com/images/fullimage7.jpg',
//                                //'caption' => 'caption1',//todo To support at page editor
//                            ],
//                            [
//                                'image' => 'https://www.skb.com/images/fullimage6.jpg',
//                                //'caption' => 'caption2',
//                            ],
//                        ],
//                        'style'=>'color:green;',
//                    ],
//                ],
//            ],            
//            [
//                'name'=>'text-block',
//                'columns'=>[
//                    [
//                        'size'=>12,
//                        'type'=>SGridLayout::TEXT_BLOCK,
//                        'name'=>'text-block',
//                        'title'=>[
//                            'en_sg'=>'This is heading',
//                        ],
//                        'text'=>[
//                            'en_sg'=>'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
//                        ],
//                        'style'=>'color:green;',
//                    ],
//                ],
//            ],            
//            [
//                'name'=>'block',
//                'columns'=>[
//                    [
//                        'size'=>4,
//                        'type'=>SGridLayout::IMAGE_BLOCK,
//                        'name'=>'image-block',
//                        'title'=>[
//                            'en_sg'=>'Item Title',
//                        ],
//                        'desc'=>[
//                            'en_sg'=>'Item Desc',
//                        ],
//                        'cta'=>[
//                            'label'=>[
//                                'en_sg'=>'Click Me!'
//                            ],
//                            'url'=>'#',
//                        ],
//                        'style'=>'background-color:whitesmoke;',
//                    ],
//                    [
//                        'size'=>4,
//                        'name'=>'image-block',
//                        'type'=>SGridLayout::IMAGE_BLOCK,
//                        'title'=>[
//                            'en_sg'=>'Item Title 2',
//                        ],
//                        'desc'=>[
//                            'en_sg'=>'Item Desc 2',
//                        ],
//                        'cta'=>[
//                            'label'=>[
//                                'en_sg'=>'Click Me 2!'
//                            ],
//                            'url'=>'#',
//                        ],
//                        'style'=>'background-color:red;background-image:url(https://www.skb.com/images/fullimage1.jpg);',
//                    ],
//                    [
//                        'size'=>4,
//                        'name'=>'image-block',
//                        'type'=>SGridLayout::IMAGE_BLOCK,
//                        'title'=>[
//                            'en_sg'=>'Item Title 3',
//                        ],
//                        'desc'=>[
//                            'en_sg'=>'Item Desc 3',
//                        ],
//                        'cta'=>[
//                            'label'=>[
//                                'en_sg'=>'Click Me 3!'
//                            ],
//                            'url'=>'#',
//                        ],
//                        'style'=>'background-color:white;background-image:url(https://www.skb.com/images/fullimage2.jpg);',
//                    ],
//                ],
//            ],
//            [
//                'name'=>'slide-block',
//                'columns'=>[
//                    [
//                        'size'=>12,
//                        'type'=>SGridLayout::SLIDE_BLOCK,
//                        'name'=>'slide-block',
//                        'items'=>[
//                            [
//                                'image' => 'https://www.skb.com/images/fullimage5.jpg',
//                            ],
//                            [
//                                'image' => 'https://www.skb.com/images/fullimage4.jpg',
//                            ],
//                        ],
//                    ],
//                ],
//            ],            
//            [
//                'name'=>'footer',
//                'columns'=>[
//                    [
//                        'size'=>12,
//                        'html'=>[
//                            'en_sg'=>'12-size footer section',
//                            'zh_cn'=>'12-尺寸页脚区',
//                        ],
//                    ],
//                ],
//            ],
//        ];
    }    
    
    public static function existsType($type)
    {
        $refl = new ReflectionClass('SGridLayout');
        //logTrace(__METHOD__,$type);
        return in_array($type, array_values($refl->getConstants()));
    }    
}
