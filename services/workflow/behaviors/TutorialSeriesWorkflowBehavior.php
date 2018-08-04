<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.services.workflow.behaviors.WorkflowBehavior");
Yii::import("common.services.workflow.behaviors.TutorialWorkflowBehavior");
/**
 * TutorialSeriesWorkflowBehavior class describes all the behaviors when action is invoked
 * as defined in s_workflow for object type that supports transition (draft, submit, publish)
 *	 
 * @author kwlok
 */
class TutorialSeriesWorkflowBehavior extends TutorialWorkflowBehavior
{
    //full inheritance
}
