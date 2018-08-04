<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of LikeForm
 *
 * @author kwlok
 */
class LikeForm extends CFormModel
{
    const ACTION_LIKE    = 'like';
    const ACTION_DISLIKE = 'dislike';

    public $type;
    public $target;
    public $counter;
    public $action;
    public $modal = false;//indicate modal form
    //Note: Refer to common.modules.likes.views.management._buttonform for how form object / script is used
    public $formObject = '$(this).parent().parent()';//indicate form id
    public $formScript = 'liketoggle($(this).parent());';
    public $buttonScript;//if not set, will auto gen; refer to getButtonScript()

    /**
     * Constructor.
     * @param string $scenario name of the scenario that this model is used in.
     * See {@link CModel::scenario} on how scenario is used by models.
     * @see getScenario
     */
    public function __construct($type,$target)
    {
        parent::__construct();
        $this->type = $type;
        $this->target = $target;
        $this->determineAction();
        $this->retrieveCounter();
    }        
    /**
     * Initializes this model.
     * This method is invoked in the constructor right after {@link scenario} is set.
     * You may override this method to provide code that is needed to initialize the model (e.g. setting
     * initial property values.)
     */
    public function init()
    {
        parent::init();
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            ['type, target', 'required'],
            ['target', 'numerical', 'integerOnly'=>true],
            ['type', 'length', 'max'=>20],
            ['target, type, action, modal', 'safe'],
        ];
    }

    public function switchAction($return=false)
    {
        switch ($this->action) {
            case self::ACTION_LIKE:
                if ($return)
                    return self::ACTION_DISLIKE;
                else
                    $this->action=self::ACTION_DISLIKE;
                break;
            case self::ACTION_DISLIKE:
                if ($return)
                    return self::ACTION_LIKE;
                else
                    $this->action=self::ACTION_LIKE;
            default:
                break;
        }
    }

    public function parseAction()
    {
        if ($this->action==self::ACTION_LIKE)
            return Process::YES;
        if ($this->action==self::ACTION_DISLIKE)
            return Process::NO;
        return null;
    }        

    public function determineAction() 
    {
        if (Like::model()->mine()->{strtolower($this->type)}($this->target)->exists())
            $this->action = LikeForm::ACTION_DISLIKE;
        else
            $this->action = LikeForm::ACTION_LIKE;
    }        

    public function retrieveCounter()
    {
        $this->counter = Yii::app()->serviceManager->getAnalyticManager()->getMetricValue(SActiveRecord::restoreTablename($this->type), $this->target, Metric::COUNT_LIKE);
    }      

    public function getObjectDisplayName()
    {
        if ($this->type=='CampaignBga')
            return Sii::t('sii','offer');
        else {
            $type = $this->type;
            return strtolower($type::model()->displayName());
        }
    }
    
    public function getTitle() 
    {
        switch ($this->action) {
            case self::ACTION_LIKE:
                return Sii::t('sii','I like');
            case self::ACTION_DISLIKE:
                return Sii::t('sii','I dislike');
            default:
                return $this->action;
        }
    }

    public function searchTargets() 
    {
        $finder = Like::model()->scopeObject(SActiveRecord::restoreTablename($this->type),$this->target);
        logTrace(__METHOD__,$finder->getDbCriteria());
        return new CActiveDataProvider($finder, [
            'criteria'=>$finder->getDbCriteria(),
        ]);
    }
    
    public function getButtonScript()
    {
        if (isset($this->buttonScript))
            return $this->buttonScript;
        else
            return 'liketoggle('.$this->formObject.');';
    }
    
    public static function getIcon($action=self::ACTION_LIKE)
    {
        if ($action==self::ACTION_LIKE)
            return '<i style="color:darkorange" class="fa fa-heart"></i>';
        else if ($action==self::ACTION_DISLIKE)
            return static::getInvertedIcon();
        else 
            return Sii::t('sii','undefined');
    }
    
    public static function getInvertedIcon()
    {
        return '<i class="fa fa-heart-o"></i>';
    }
    
}