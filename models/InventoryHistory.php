<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * This is the model class for table "s_inventory_history".
 *
 * The followings are the available columns in table 's_inventory_history':
 * @property integer $id
 * @property integer $inventory_id
 * @property string $description
 * @property integer $type
 * @property integer $movement
 * @property integer $post_available
 * @property integer $post_quantity
 * @property integer $create_by
 * @property integer $create_time
 *
 * The followings are the available model relations:
 * @property Inventory $inventory
 *
 * @author kwlok
 */
class InventoryHistory extends CActiveRecord
{
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 's_inventory_history';
    }
    /**
     * Behaviors for this model
     */
    public function behaviors()
    {
        return array(
            'timestamp' => array(
                'class'=>'common.components.behaviors.TimestampBehavior',
                'updateEnable'=>false,
            ),
        );
    }        
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('inventory_id, description, type, movement, post_available, post_quantity, create_by', 'required'),
            array('inventory_id, type, movement, post_available, post_quantity', 'numerical', 'integerOnly' => true),
            array('create_by', 'length', 'max' => 12),
            array('description', 'length', 'max' => 100),
            
            array('id, inventory_id, description, type, movement, post_available, post_quantity, create_by, create_time', 'safe', 'on' => 'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
            'inventory' => array(self::BELONGS_TO, 'Inventory', 'inventory_id'),
        );
    }
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => Sii::t('sii','ID'),
            'inventory_id' => Sii::t('sii','Inventory'),
            'description' => Sii::t('sii','Description'),
            'type' => Sii::t('sii','Type'),
            'movement' => Sii::t('sii','Movement'),
            'post_available' => Sii::t('sii','Available'),
            'post_quantity' => Sii::t('sii','Quantity'),
            'create_by' => Sii::t('sii','Created By'),
            'create_time' => Sii::t('sii','Create Time'),
        );
    }
    /**
     * Retrieves a list of models based on the current search/filter conditions.
     *
     * Typical usecase:
     * - Initialize the model fields with values from filter form.
     * - Execute this method to get CActiveDataProvider instance which will filter
     * models according to data in model fields.
     * - Pass data provider to CGridView, CListView or any similar widget.
     *
     * @return CActiveDataProvider the data provider that can return the models
     * based on the search/filter conditions.
     */
    public function search()
    {
        // @todo Please modify the following code to remove attributes that should not be searched.
        $criteria=new CDbCriteria;

        $criteria->compare('id',$this->id);
        $criteria->compare('inventory_id',$this->inventory_id);
        $criteria->compare('description',$this->description,true);
        $criteria->compare('type',$this->type);
        $criteria->compare('movement',$this->movement);
        $criteria->compare('post_available',$this->post_available);
        $criteria->compare('post_quantity',$this->post_quantity);
        $criteria->compare('create_by',$this->create_by);
        $criteria->compare('create_time',$this->create_time);

        return new CActiveDataProvider($this, array(
                'criteria'=>$criteria,
        ));
    }
    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return InventoryHistory the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    const TYPE_INFLOW       = 1;
    const TYPE_OUTFLOW      = 2;        
    const TYPE_STATIC       = 3;//internal movement
    const TYPE_DELETE       = 99;//inventory get deleted        
    public function getTypes()
    {
        return array(
            InventoryHistory::TYPE_INFLOW => 'INFLOW',
            InventoryHistory::TYPE_OUTFLOW => 'OUTFLOW',
            InventoryHistory::TYPE_STATIC => 'STATIC',
            InventoryHistory::TYPE_DELETE => 'DELETE',
            //other supported types, e.g. relocation within warehouse
        );
    }        
    public function getTypeDesc()
    {
        $types = $this->getTypes();
        return $types[$this->type];
    }        
}
