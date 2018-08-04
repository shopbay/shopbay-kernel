<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of CommentableBehavior
 *
 * @author kwlok
 */
class CommentableBehavior extends CActiveRecordBehavior 
{
    private $_counter;
    public function getCommentCounter()
    {
        if (!isset($this->_counter)){
            $comment = Comment::model()->byType($this->getOwner()->tableName(),$this->getOwner()->id)->find();
            if ($comment===null)
                $this->_counter = 0;
            else 
                $this->_counter = $comment->counter;
        }
        return $this->_counter;
    }
    /**
     * Search comments
     * @param type $page
     * @return \CActiveDataProvider
     */
    public function searchComments($page=0)
    {
       $criteria = new CDbCriteria();
       $criteria->addColumnCondition(array('obj_type'=>$this->getOwner()->tableName()));
       $criteria->addColumnCondition(array('obj_id'=>$this->getOwner()->id));
       $criteria->order = 'create_time ASC';
       $dataProvider = new CActiveDataProvider(Comment::model(),
                                    array('criteria'=>$criteria,
                                          'pagination'=>array('pageSize'=>Config::getSystemSetting('record_per_page')),
                                          'sort'=>false,
                                    ));
       //show last page
       $pager = $dataProvider->pagination;
       $pager->itemCount = $dataProvider->totalItemCount;
       $pager->currentPage = $pager->pageCount-$page;
       logTrace(__METHOD__.' $pager->pageCount='.$pager->pageCount.', currentPage='.$pager->currentPage);
       return $dataProvider;
    } 
    
}
