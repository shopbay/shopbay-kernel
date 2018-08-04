<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.pages.models.PageTrait');
/**
 * Description of TutorialTrait
 * 
 * Sibling Trait: 
 * @see plans/MakerCheckerTrait (probably boht can combine)
 * 
 * @author kwlok
 */
trait TutorialTrait 
{
    use PageTrait, LanguageModelTrait;
    /**
     * Common behaviors
     * @return type
     */
    public function getCommonBehaviors()
    {
        return [
            'sluggable' => [
                'class'=>'common.components.behaviors.SlugBehavior',
                'dynamicColumn'=>[
                    'method'=>'getSlugValue',
                ],
            ],
            'timestamp' => [
              'class'=>'common.components.behaviors.TimestampBehavior',
            ],
            'activity' => [
                'class'=>'common.modules.activities.behaviors.ActivityBehavior',
                'iconUrlSource'=>'account',
                'descriptionAttribute'=>'name',
            ],
            'account' => [
              'class'=>'common.components.behaviors.AccountBehavior',
            ], 
            'locale' => [
                'class'=>'common.components.behaviors.LocaleBehavior',
                'ownerParent'=>'accountProfile',
                'localeAttribute'=>'locale',
            ],  
            'multilang' => [
                'class'=>'common.components.behaviors.LanguageBehavior',
            ],              
            'commentable' => [
                'class'=>'common.modules.comments.behaviors.CommentableBehavior',
            ],      
            'taggable' => [
                'class'=>'common.modules.tags.behaviors.TaggableBehavior',
                'tagUrlMethod'=>'getUrl',
            ],  
        ];
    }    
    /**
     * A wrapper method to return drafted records of this model
     * @return \Tutorial
     */
    public function drafted() 
    {
        $this->getDbCriteria()->mergeWith([
            'condition'=>'status = \''.$this->draftedStatus.'\'',
        ]);
        return $this;
    }
    /**
     * A wrapper method to return submitted records of this model
     * @return \Tutorial
     */
    public function submitted() 
    {
        $this->getDbCriteria()->mergeWith([
            'condition'=>'status = \''.$this->submittedStatus.'\'',
        ]);
        return $this;
    }
    /**
     * A wrapper method to return approved records of this model
     * @return \Tutorial
     */
    public function published() 
    {
        $this->getDbCriteria()->mergeWith([
            'condition'=>'status = \''.$this->publishedStatus.'\'',
        ]);
        return $this;
    }
    /**
     * Check if tutorial can be submitted
     */
    public function submitable()
    {
        return $this->status==$this->draftedStatus;
    }
    /**
     * Check if tutorial can be published
     */
    public function publishable()
    {
        return $this->status==$this->submittedStatus;
    }
    /**
     * Check if tutorial is online (published)
     */
    public function online()
    {
        return $this->status==$this->publishedStatus;
    }
    /**
     * @todo 
     * For now make Administrator is able to edit tutorial (as they are assumed to be power user and know what they are writing.
     * Need a proper version control solution to open tutorial editing to all user especially after tutorial is published and accesible by public
     * @return type
     */
    public function updatable()
    {
        return ($this->account_id==user()->getId() && $this->submitable()) || user()->hasRole(Role::ADMINISTRATOR);
    }    
    
    public function deletable()
    {
        return $this->account_id==user()->getId() && $this->submitable();
    }   
    
    public function getAuthor()
    {
        return $this->account;
    }
    /**
     * Return account profile
     * @return type
     */
    public function getAccountProfile()
    {
        return $this->account->profile;
    }
    
    public function getReturnUrl()
    {
        return $this->url;
    }      
    /**
     * @todo The tutorial base url (right now only accessible via merchant app)
     * @return string
     */
    public function getAccessUrl($route)
    {
        return Yii::app()->urlManager->createHostUrl('community/'.$route);
    }
}
