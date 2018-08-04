<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * TransitionEvent represents the parameter for the {@link WorkflowManager::onTransition onTransition} event.
 *
 * @author kwlok 
 */
class TransitionEvent extends CEvent
{
    /**
     * @var string Model undergoes the transition
     */
    public $model;
    /**
     * @var string Transition details
     */
    public $transition;
    /**
     * @var bollean Whether to save transition record
     */
    public $saveTransition;
    /**
     * Constructor
     * 
     * @param type $sender
     * @param type $model
     * @param type $transition
     * @param type $save
     */
    public function __construct($sender,$model,$transition,$saveTransition)
    {
        $this->model=$model;
        $this->transition=$transition;
        $this->saveTransition=$saveTransition;
        parent::__construct($sender);
    }
}