<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of QuestionableBehavior
 *
 * @author kwlok
 */
class QuestionableBehavior extends CActiveRecordBehavior 
{
    /**
     * Search questions (apply to online question only)
     * @param type $page
     * @return \CActiveDataProvider
     */
    public function searchQuestions($page=0)
    {
       $criteria = new CDbCriteria();
       $criteria->addColumnCondition(array('obj_type'=>$this->getOwner()->tableName()));
       $criteria->addColumnCondition(array('obj_id'=>$this->getOwner()->id));
       $criteria->addColumnCondition(array('status'=>Process::QUESTION_ONLINE));
       $criteria->order = 'question_time ASC';
       $dataProvider = new CActiveDataProvider(Question::model(),
                                    array('criteria'=>$criteria,
                                          'pagination'=>array('pageSize'=>Config::getSystemSetting('record_per_page')),
                                          'sort'=>false,
                                    ));
       //show last page
       $pager = $dataProvider->pagination;
       $pager->itemCount = $dataProvider->totalItemCount;
       $pager->currentPage = $pager->pageCount-$page;
       logTrace(__METHOD__.' $pager->pageCount='.$pager->pageCount.', itemCount='.$pager->itemCount.', currentPage='.$pager->currentPage);
       return $dataProvider;
    } 
    
}
