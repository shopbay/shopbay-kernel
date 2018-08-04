<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of AssetsAction
 *
 * @author kwlok
 */
class AssetsAction extends CAction
{
    public $modelFilter;
    public $modelStatus;
    /**
     * Pickup the media asset file and return back its content
     */
    public function run() 
    {
        $search = current(array_keys($_GET));//take the first key as search attribute
        //logTrace(__METHOD__.' GET currency key '.$search,$_GET);
        if ($search=='preview'){
            $status = false;
            $search = $_GET['preview'];//media id is inside here
        }
        else
            $status = true;
        
//        $model = $this->mediaFinder->findFile($search)->find();
        $model = $this->getMediaFinder($status)->findByPk(rtrim($search, '.jpg'));
        if (!$model instanceof Media || $model===null)
            echo Sii::t('sii','File not found');
        else{
            header('Content-type: image/png');
            readfile($model->filepath);
        }
        Yii::app()->end();        
    }
    /**
     * Get media finder based on search mode (preview or by model filter)
     * @param type $status
     * @return type
     */
    protected function getMediaFinder($status=true)
    {
        $finder = Media::model();
        if (isset($this->modelFilter))
            $finder = $finder->{$this->modelFilter}();
        if (isset($this->modelStatus) && $status)
            $finder = $finder->status($this->modelStatus);
            
        return $finder;
    }
}
