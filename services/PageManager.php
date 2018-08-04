<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.services.ServiceManager");
Yii::import("common.widgets.sgridlayout.SGridLayout");
Yii::import("common.modules.pages.models.PageLayout");
/**
 * Description of PageManager
 *
 * @author kwlok
 */
class PageManager extends ServiceManager 
{
    /**
     * Initialization
     */
    public function init() 
    {
        parent::init();
    }    
    /**
     * Create model
     * 
     * @param integer $user Session user id
     * @param CModel $model CModel model to update
     * @return CModel $model
     * @throws CException
     */
    public function create($user,$model)
    {
        $this->validate($user, $model, false);
        $model->account_id = $user;
        $model->status = Process::PAGE_OFFLINE;        
        logTrace(__METHOD__.' scenario',$model->getScenario());
        $currentParams = json_decode($model->params,true);
        $currentParams['custom'] = true;//insert custom page indicator; used for limit counting
        $model->params = json_encode($currentParams);
        
        return $this->execute($model, [
            'insert'=>self::EMPTY_PARAMS,
            'recordActivity'=>Activity::EVENT_CREATE,
        ],$model->getScenario());
    }
    /**
     * Update model
     * 
     * @param integer $user Session user id
     * @param CModel $model CModel model to update
     * @param boolean $checkAccess Indicate if to perform check access; Default to 'true'
     * @return CModel $model
     * @throws CException
     */
    public function update($user,$model,$checkAccess=true)
    {
        $this->validate($user, $model, $checkAccess);
        logTrace(__METHOD__.' scenario',$model->getScenario());
        return $this->execute($model, [
            'update'=>self::EMPTY_PARAMS,
            'recordActivity'=>Activity::EVENT_UPDATE,
        ],$model->getScenario());
    }
    /**
     * Delete model
     * 
     * @param integer $user Session user id
     * @param CModel $model Model to update
     * @param boolean $checkAccess Indicate if to perform check access; Default to 'true'
     * @return CModel $model
     * @throws CException
     */
    public function delete($user,$model,$checkAccess=true)
    {
        $this->validate($user, $model, $checkAccess);
        return $this->execute($model, [
                'recordActivity'=>[
                    'event'=>Activity::EVENT_DELETE,
                    'account'=>$user,
                ],
//                'detachMediaAssociation'=>self::EMPTY_PARAMS,
                'delete'=>self::EMPTY_PARAMS,
            ],'delete');
    }
    /**
     * Save page layout
     * 
     * @param integer $user Session user id
     * @param string $page The layout of page 
     * @param string $theme The layout of page theme 
     * @param string $layoutName The name of layout
     * @param array $layout The layout to be saved (raw input)
     * @return CModel $model
     * @throws CException
     */
    public function saveLayout($user,$owner,$page,$theme,$layoutName,$layout)
    {
        if (!$owner instanceof Shop)
            throw new CException('Invalid page owner');
        
        if (empty($layout) || !is_array($layout))
            throw new CException('Invalid page layout data');
        
        //logTrace(__METHOD__.' Received layout for page "'.$page.'" theme "'.$theme.'"', $layout);

        $layout = $this->transformLayout($layout);
        //logTrace(__METHOD__.' Transformed layout for page "'.$page.'" theme "'.$theme.'"', $layout);

        //Find page layout record to save
        $model = PageLayout::model()->locateOwner($owner)->locatePage($theme,$page)->find();
        if ($model==null){
            $model = $this->createLayout($owner, $theme, $page, $layoutName, $layout);
        }
        
        return $this->execute($model, [
            'saveLayout'=>[
                'user'=>$user,
                'layout'=>$layout,
                'page'=>$page,
                'theme'=>$theme,
                'layoutName'=>$layoutName,
            ],
            'recordActivity'=>[
                'event'=>Activity::EVENT_UPDATE,
                'description'=>$page,
            ]
        ],$model->getScenario());
    }    
    /**
     * Traverse container and read its layout, and transform layout to "live" format that can be saved and read by SGridLayout
     * Note: This piece of logic can be moved to Javascript?
     * @return array
     */
    protected function transformLayout($layout)
    {
        foreach ($layout as $i => $row) {
            $hasColumns = true;
            if (isset($row['include']) &&  strlen($row['include'])>0){
                //Only keep include param; Remove other and can be reference later via 'include'
                unset($layout[$i]['columns']);
                unset($layout[$i]['name']);
                unset($layout[$i]['widget_id']);
                $hasColumns = false;
            }
            
            if (isset($row['columns']) && $hasColumns){
                
                unset($layout[$i]['include']);//remove row unwanted param
                unset($layout[$i]['widget_id']);//remove row unwanted param
                
                foreach ($row['columns'] as $j => $column) {
                    foreach ($column as $key => $value) {
                        
                        switch ($key) {
                            case 'container'://nested container
                                if (!empty($value))//if contains external container
                                    unset($layout[$i]['columns'][$j]['rows']);//remove unwanted rows data
                                break;
                            case 'rows'://nested block
                                $layout[$i]['columns'][$j][$key] = $this->transformLayout($value);//nested loop in to get sub layout
                                break;
                            case 'type':
                                $layout[$i]['columns'][$j][$key] = str_replace('sgrid','',$value);//remove unwanted prefix if any
                                break;
                            case 'ctaLabel':
                                $layout[$i]['columns'][$j]['cta']['label'] = json_decode($value,true);//language field
                                unset($layout[$i]['columns'][$j][$key]);
                                break;
                            case 'ctaUrl':
                                $layout[$i]['columns'][$j]['cta']['url'] = $value;
                                unset($layout[$i]['columns'][$j][$key]);
                                break;
                            case 'html':
                            case 'title':
                            case 'caption':
                            case 'desc':
                            case 'text':
                            case 'menu':
                            case 'fixtures':
                            case 'viewData':
                                $layout[$i]['columns'][$j][$key] = json_decode($value,true);//to keep data as array, and same as language field
                                break;
                            case 'undefined':
                            case 'widget_id':
                                //Remove all unwanted fields
                                unset($layout[$i]['columns'][$j][$key]);
                                break;
                            default:
                                break;
                        }
                    }
                }
            }
        }
        return $layout;
    }    
    /**
     * Create page layout 
     * @param type $owner
     * @param type $theme
     * @param type $page
     * @param type $layoutName
     * @param type $layout
     * @return \PageLayout
     */
    protected function createLayout($owner, $theme,$page,$layoutName,$layout)
    {
        $themeModel = Theme::model()->locateTheme($theme)->find();
        if ($themeModel==null)
            throw new CException('Theme '.$theme.' not found.');
        
        $model = new PageLayout();
        $model->owner_id = $owner->id;
        $model->owner_type = get_class($owner);
        $model->theme_id = $themeModel->id;
        $model->theme = $theme;
        $model->name = $layoutName;
        $model->page = $page;
        $model->layout = 'pending';//initial value, will be updated during saveLayout()
        if (!$model->validate()){
            logError(__METHOD__.' Failed to create page layout.',$model->errors);
            throw new CException('Failed to create page layout.');
        }
        $model->save();
        logTrace(__METHOD__.' PageLayout is created successfully.',$model->attributes);
        return $model;
    }
    /**
     * Reset page layout
     * Delete (soft delete) the page layout so that system will auto create new page layout record to reload 
     * layout from factory settings.
     * 
     * @param integer $user Session user id
     * @param string $page The layout of page 
     * @param string $theme The layout of page theme 
     * @param string $layoutName The name of layout
     * @param array $layout The layout to be saved (raw input)
     * @return CModel $model
     * @throws CException
     */
    public function resetLayout($user,$owner,$theme,$page)
    {
        if (!$owner instanceof Shop)
            throw new CException('Invalid page owner');
        
        //Find page layout record to save
        $model = PageLayout::model()->locateOwner($owner)->locatePage($theme,$page)->find();
        if ($model==null){
            throw new CException('Page not found');
        }
        
        $this->validate($user, $model);//check access is true
        
        $ownerClass = get_class($model->owner);
        $ownerPage = $ownerClass.'Page';
        Yii::import('common.modules.'.strtolower($ownerClass).'s.components.'.$ownerPage);
        //Make sure page is a in-built custom page (not created by user)
        if (!$ownerPage::isCustomPage($model->page)){
            throw new CException('Page cannot be reset.');
        }
        
        //By changing the page name to suffix value, this auto make it not findable using page editor
        $model->page = substr($model->page.'_reset'.time(), 0, 25);//keep max length to 25 as this is the limit of column 'page' in table s_page_layout
        
        $layout = new PageLayout();
        $layout->attributes = $model->attributes;//transfer all attributes
        $layout->page = $page;//use back the original page name
        
        $model = $this->execute($model, [
            'update'=>self::EMPTY_PARAMS,
            'recordActivity'=>[
                'event'=>Activity::EVENT_UPDATE,
                'description'=>'Reset '.$page,
                'obj_url'=>$layout->viewUrl,//to use back the original layout url (not using the changed page name url)
            ]
        ],$model->getScenario());
        
        return $layout->viewUrl;//return page layout url
    }        
}
