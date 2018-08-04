<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * This is the model class for table "s_dim_date".
 *
 * The followings are the available columns in table 's_dim_date':
 * @property integer $id
 * @property integer $date
 * @property integer $day
 * @property integer $week
 * @property float $month
 * @property integer $year
 *
 * @author kwlok
 */
class DimDate extends CActiveRecord
{
    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return Metric the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    } 
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 's_dim_date';
    }
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('date, day, week, month, year', 'required'),
            array('day, week, month, year', 'numerical', 'integerOnly'=>true),
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array('id, date, day, week, month, year', 'safe', 'on'=>'search'),
        );
    }
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => Sii::t('sii','ID'),
            'date' => Sii::t('sii','Date'),
            'day' => Sii::t('sii','Day'),
            'week' => Sii::t('sii','Week'),
            'month' => Sii::t('sii','Month'),
            'year' => Sii::t('sii','Year'),
        );
    }
    /**
     * Retrieve DimDate record; Will auto create a new one when not found
     * 
     * @param DateTime $datetime
     * @return \DimDate
     * @throws CException
     */
    public function retrieve($datetime) 
    {
        if (!($datetime instanceof DateTime))
            throw new CException(Sii::t('sii','Invalid DateTime object'));

        $model = DimDate::model()->find('date = "'.$datetime->format('Y-m-d').'"');
        if ($model===null){
            $model = new DimDate();
            $model->date = $datetime->format('Y-m-d');
            $model->day = $datetime->format('j');
            $model->week = $datetime->format('W');
            $model->month = $datetime->format('n');
            $model->year = $datetime->format('Y');
            $model->save();
            logInfo(__METHOD__.' new date dimension created', $model->getAttributes());
        }
        return $model;
    }  
}
