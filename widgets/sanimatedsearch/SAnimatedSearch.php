<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.widgets.ssearch.SSearch");
/**
 * Description of SAnimatedSearch
 * Base class is widget SSearch
 *
 * @author kwlok
 */
class SAnimatedSearch extends SSearch
{
    /**
     * The path alias to access assets
     * @property string
     */
    public $pathAlias = 'common.widgets.sanimatedsearch.assets';
    /**
     * string the asset name of the widget
     */
    public $assetName = 'sanimatedsearch';
    /**
     * The search function
     * @var string 
     */
    protected $searchFn = 'sanimatedsearch';
    /**
     * The search input element id/name; Default to 'sanimatedsearch_q'
     * @var string 
     */
    public $inputId = 'sanimatedsearch_q';
    /**
     * The input icon / image
     * @var boolean 
     */
    public $useImage = true;//if false, will use web font 'fa-search'
}
