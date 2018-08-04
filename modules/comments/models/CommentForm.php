<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * CommentForm class file.
 * 
 * @author kwlok
 */
class CommentForm extends SFormModel
{
    const SCENARIO_COUNTER = 'counter';

    public $id='comment_form';
    public $type;//equivalent to Comment->obj_type
    public $target;//equivalent to Comment->obj_id
    public $src_id;
    public $rating;
    public $content;
    public $page=1;
    public $counter;
    public $formScript = 'comment($(this).attr(\'form\'));';
    public $signInScript = 'signin();';
    /**
     * Model display name 
     * @return string the model display name
     */
    public function displayName()
    {
        return Sii::t('sii','Comment');
    }     
    /**
     * Constructor.
     * @param string $scenario name of the scenario that this model is used in.
     * See {@link CModel::scenario} on how scenario is used by models.
     * @see getScenario
     */
    public function __construct($scenario,$type=null,$target=null)
    {
        parent::__construct($scenario);
        if ($scenario===self::SCENARIO_COUNTER){
            if ($type===null)
                throw new CException(Sii::t('sii','Object type cannot be blank'));
            if ($target===null)
                throw new CException(Sii::t('sii','Object ID cannot be blank'));
            $this->type = $type;
            $this->target = $target;
            $this->retrieveCounter();
        }
    }            
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('type, target, content, id', 'required'),
            array('target, rating', 'numerical', 'integerOnly'=>true),
            array('type', 'length', 'max'=>20),
            array('content','rulePurify'),
            array('content', 'length','max'=>5000),
            array('src_id, target, type, content, rating, page', 'safe'),
        );
    }
    /**
     * Declares customized attribute labels.
     * If not declared here, an attribute would have a label that is
     * the same as its name with the first letter in upper case.
     */
    public function attributeLabels()
    {
        return array(
            'content'=>Sii::t('sii','Comment'),
            'rating'=>Sii::t('sii','Rating'),
        );
    }
    private $_t;//store comment target object 
    public function getTarget()
    {
        if (!isset($this->_t)){
            $type = $this->type;
            return $type::model()->findByPk($this->target);            
        }
        return $this->_t;
    }

    public function getTargetDisplayName()
    {
        switch ($this->type) {
            case get_class(CampaignBga::model()):
                return Sii::t('sii','promotion');
            default:
                return strtolower($this->getTarget()->displayName());
        }
    }
    public $isNewRecord;
    private $_reviewUrl;
    public function setReviewUrl($url)
    {
        $this->isNewRecord = true;
        $this->_reviewUrl = $url;
    }
    public function getReviewUrl(){
        return $this->_reviewUrl;
    }        

    public function retrieveCounter()
    {
        $this->counter = Yii::app()->serviceManager->getAnalyticManager()->getMetricValue(SActiveRecord::restoreTablename($this->type), $this->target, Metric::COUNT_COMMENT);
        return $this->counter;
    }            
}
