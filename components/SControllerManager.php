<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of SControllerManager
 * Manage controller stuff, like layouts, views
 * 
 * @author kwlok
 */
class SControllerManager extends CApplicationComponent
{
    /**
     * @var string the default layout for the SController view. Defaults to 'application.views.layouts.site'
     * @see SController
     */
    public $layout = 'application.views.layouts.site';
    /**
     * @var string the default header view of $layout. Defaults to 'application.views.layouts._site_header'
     * @see SController
     */
    public $headerView = 'application.views.layouts._site_header';
    /**
     * @var string the default footer view of $layout. Defaults to 'application.views.layouts._site_footer'
     * @see SController
     */
    public $footerView = 'application.views.layouts._site_footer';
    /**
     * @var string the default layout for the authenticated view. 
     * @see AuthenticatedController
     */
    public $authenticatedLayout = 'application.views.layouts.authenticated';
    /**
     * @var string the default header view of the authenticated view. 
     * @see AuthenticatedController
     */
    public $authenticatedHeaderView = 'application.views.layouts._authenticated_header';
    /**
     * @var string the default footer view of the authenticated view. 
     * @see AuthenticatedController
     */
    public $authenticatedFooterView = 'application.views.layouts._authenticated_footer';
    /**
     * @var string the page title suffix filter class
     */
    public $pageTitleSuffixFilter = 'common.components.filters.PageTitleSuffixFilter';
    /**
     * @var string the default html body begin content before rendering header/content/footer
     */
    public $htmlBodyBegin;
    /**
     * @var string the default html body end content after rendering header/content/footer
     */
    public $htmlBodyEnd;
    /**
     * @var string the default html body css class. Defaults to 'Yii::app()->id'
     */
    public $htmlBodyCssClass;
    /**
     * Init
     */
    public function init()
    {
        parent::init();
        $this->htmlBodyCssClass = Yii::app()->id;
    }   
}
