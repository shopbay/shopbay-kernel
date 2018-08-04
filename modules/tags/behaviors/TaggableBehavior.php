<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of TaggableBehavior
 *
 * @author kwlok
 */
class TaggableBehavior extends CActiveRecordBehavior 
{
    /**
    * @var string The name of the attribute to store tags. Defaults to 'tags'
    */
    public $tagAttribute = 'tags';
    /**
    * @var string The method to get tag url. Defaults to 'getViewUrl'
    */
    public $tagUrlMethod = 'getViewUrl';
    /**
     * Return tags in array
     */
    public function hasTags()
    {
        return $this->getOwner()->{$this->tagAttribute}!=null;
    }    
    /**
     * Return tags in array
     * @param $url if to return url
     */
    public function parseTags($url=true)
    {
        $list = Tag::getList(user()->getLocale());
        //logTrace(__METHOD__.' Tags '.$this->getOwner()->{$this->tagAttribute},$list);
        $tags = explode(',', $this->getOwner()->{$this->tagAttribute});
        if ($url) {
            foreach ($tags as $key => $value) {
                if (isset($list[$value]))//only show when tag key is found (in case tag is changed)
                    $tags[$key] = CHtml::link($list[$value],Tag::model()->{$this->tagUrlMethod}($value));
            }
        }
        return $tags;
    }
        
}
