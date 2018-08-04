<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Search question Active record
 *
 * @author kwlok
 */
class SearchQuestion extends SearchModel
{
    public $arClass = 'Question';
    /**
     * Note: path mapping for '_id' is setup to field 'id'
     * 
     * @return array the list of attributes for this record
     */
    public function attributes()
    {
        return ['id', 'question_by' , 'title', 'question' ,'tags', 'status'];
    }
}