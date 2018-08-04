<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of TicketReplyForm
 *
 * @author kwlok
 */
class TicketReplyForm extends SFormModel
{
    private $_m;//store reply target object 
    
    public $id ='ticket_reply_form';
    public $target;//equivalent to Ticket->id
    public $group;//equivalent to Ticket->shop_id
    public $content;
    public $page=1;//support pagination: refer to CommentForm
    public $formScript = 'replyticket($(this).attr(\'form\'));';
    /**
     * Model display name 
     * @return string the model display name
     */
    public function displayName()
    {
        return Sii::t('sii','Ticket');
    }     
    /**
     * Constructor.
     * @param string $scenario name of the scenario that this model is used in.
     * See {@link CModel::scenario} on how scenario is used by models.
     * @see getScenario
     */
    public function __construct($scenario,$target=null)
    {
        parent::__construct($scenario);
        $this->target = $target;
    }            
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('target, content, id', 'required'),
            array('group', 'required','on'=>'merchant'),
            array('target, group', 'numerical', 'integerOnly'=>true),
            array('content','rulePurify'),
            array('content', 'length','max'=>5000),
            array('group, target, content, page', 'safe'),
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
            'content'=>Sii::t('sii','Reply'),
        );
    }
    
    public function getTicket()
    {
        if (!isset($this->_m)){
            return Ticket::model()->findByPk($this->target);            
        }
        return $this->_m;
    }

    public function getTicketDisplayName()
    {
        return strtolower($this->ticket->displayName());
    }
           
}
