<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of QuestionsModule
 *
 * @author kwlok
 */
class QuestionsModule extends SModule 
{
    /**
     * Init
     */
    public function init()
    {
        // import the module-level models and components
        $this->setImport([
            'questions.models.*',
        ]);
    }
    /**
     * @return ServiceManager
     */
    public function getServiceManager($owner=null)
    {
        $this->setComponents([
            'servicemanager'=>[
                'class'=>'common.services.QuestionManager',
                'model'=>['Question'],
                'ownerAttribute'=>isset($owner)?$owner:'question_by',
            ],
        ]);
        return $this->getComponent('servicemanager');
    }
    
}