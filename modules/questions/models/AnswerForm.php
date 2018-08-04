<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of AnswerForm
 *
 * @author kwlok
 */
class AnswerForm extends SFormModel 
{
    public $id;
    public $shop_id;
    public $type;
    public $question;
    public $question_by;
    public $question_time;
    public $answer;
    public $answerUrl;
    public $formView;
    /**
     * Model display name 
     * @return string the model display name
     */
    public function displayName()
    {
        return Sii::t('sii','Answer');
    }     
    /**
     * Constructor.
     * @param string $scenario name of the scenario that this model is used in.
     * See {@link CModel::scenario} on how scenario is used by models.
     * @see getScenario
     */
    public function __construct($id='')
    {
        parent::__construct($id);

        $model = Question::model()->findByPk($id);
        $this->id = $model->id;
        $this->shop_id = $model->shop->id;
        $this->type = $model->type;
        $this->question = $model->question;
        $this->question = $model->question;
        $this->question_time = $model->question_time;
        $this->question_by = $model->question_by;
    }
    /**
     * Behaviors for this model
     */
    public function behaviors()
    {
        return array(
            'locale' => array(
                'class'=>'common.components.behaviors.LocaleBehavior',
            ),  
        );
    }   
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('answer, type', 'required'),
            array('answer','rulePurify'),            
            array('answer', 'length', 'max'=>5000),
            array('id, question, question_by, question_time', 'safe'),
        );
    }
    
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'answer' => Sii::t('sii','Answer'),
        );
    }   
    
    public function getShop()
    {
        return Shop::model()->findByPk($this->shop_id);
    }

    public function getQuestioner()
    {
        return AccountProfile::model()->findByAttributes(array('account_id'=>$this->question_by));
    }
    
    public function getTypeLabel()
    {
        return Question::model()->getTypeLabel($this->type);
    }    
}
