<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.services.ServiceManager");
/**
 * Description of InventoryManager
 *
 * @author kwlok
 */
class InventoryManager extends ServiceManager 
{
    /**
     * Initialization
     */
    public function init() 
    {
        parent::init();
    }    
    /**
     * Create inventory model
     * 
     * @param integer $user Session user id
     * @param CModel $model CModel model to update
     * @return CModel $model
     * @throws CException
     */
    public function createInventory($user,$model)
    {
        $this->validate($user, $model, false);
        $model->account_id = $user;
        return $this->execute($model, array(
                'save'=>self::EMPTY_PARAMS,//call save() so that $model->id is created
                'saveInitialHistory'=>$model->quantity,
                'recordActivity'=>Activity::EVENT_CREATE,
        ));
    }
    /**
     * Update inventory model
     * 
     * @param integer $user Session user id
     * @param CModel $model CModel model to update
     * @param int $movement Stock movement
     * @return CModel $model
     * @throws CException
     */
    public function updateInventory($user,$model,$checkAccess,$movement)
    {
        $this->validate($user, $model, $checkAccess);
        return $this->execute($model, array(
                'update'=>self::EMPTY_PARAMS,
                'recordHistory'=>array(
                    'inventory_id'=>$model->id,
                    'description'=>'UPDATE '.$model->sku,
                    'type'=>$movement>0?InventoryHistory::TYPE_INFLOW:InventoryHistory::TYPE_OUTFLOW,
                    'movement'=>abs($movement),
                    'post_available'=>$model->available,
                    'post_quantity'=>$model->quantity,
                    'create_by'=>$user,
                ),
                'recordActivity'=>Activity::EVENT_UPDATE,
        ));
    } 
    /**
     * Delete inventory model
     * 
     * @param integer $user Session user id
     * @param CModel $model CModel model to update
     * @return CModel $model
     * @throws CException
     */
    public function deleteInventory($user,$model,$checkAccess=true)
    {
        $this->validate($user, $model, $checkAccess);
        return $this->execute($model, array(
                    'recordActivity'=>array(
                        'event'=>Activity::EVENT_DELETE,
                        'account'=>$user,
                    ),
                    'recordHistory'=>array(
                        'inventory_id'=>$model->id,
                        'description'=>'DELETE '.$model->sku,
                        'type'=>InventoryHistory::TYPE_DELETE,
                        'movement'=>0,
                        'post_available'=>0,
                        'post_quantity'=>0,
                        'create_by'=>$user,
                    ),
                    'delete'=>self::EMPTY_PARAMS,
                ),'delete');
    }    
    /**
     * Check inventory if has stock
     * 
     * @param integer $pid product id
     * @param string $sku
     * @param integer $quantity Purchased quantity
     * @return integer available stock
     * @throws CException
     */
    public function checkInventory($pid,$sku,$quantity=null)
    {
        $inventory = $this->findInventory($pid,$sku);
        
        $this->_validateInventory($inventory, $quantity);
                
        logInfo(__METHOD__.' SKU '.$sku.' available '.$inventory->available.(isset($quantity)?' > quantity '.$quantity:''));  
        
        return $inventory->available;
    }   
    /**
     * Normal mode:
     * - Hold inventory when order is confirmed
     * - Move inventory stock from available to hold
     * 
     * Rollback mode:
     * - Return (unhold) inventory when order is cancelled or rejected
     * - Move back inventory stock from hold to available
     *
     * @param type $user
     * @param type $pid product id
     * @param type $sku
     * @param type $quantity
     * @param type $reference The object trigger the inventory movement
     * @param boolean $rollback 
     * @return boolean True for successful, False for failure
     * @throws CException
     */
    public function holdInventory($user,$pid,$sku,$quantity,$reference,$rollback=false)
    {
        $inventory = $this->findInventory($pid,$sku);
        
        if ($this->_validateInventory($inventory, $quantity, $rollback?'hold':'available')){
            
            $holdBefore = $inventory->hold;
            $availBefore = $inventory->available;
            
            if ($rollback){
                $inventory->available += $quantity;
                $inventory->hold -= $quantity;   
                $movement = Sii::t('sii','ROLLBACK HOLD');
            }
            else {
                $inventory->available -= $quantity;
                $inventory->hold += $quantity;
                $movement = Sii::t('sii','HOLD');
            }            
            
            $inventory->update();
            $inventory->recordHistory([
                'inventory_id'=>$inventory->id,
                'description'=>Sii::t('sii','{movement} {before}>>{after}, AVAIL {beforeAvail}>>{afterAvail} FOR {reference}',[
                                    '{movement}'=>$movement,
                                    '{beforeAvail}'=>$availBefore,
                                    '{afterAvail}'=>$inventory->available,
                                    '{before}'=>$holdBefore,
                                    '{after}'=>$inventory->hold,
                                    '{reference}'=>$reference]),
                'type'=>$rollback?InventoryHistory::TYPE_INFLOW:InventoryHistory::TYPE_OUTFLOW,
                'movement'=>$quantity,
                'post_available'=>$inventory->available,
                'post_quantity'=>$inventory->quantity,
                'create_by'=>$user,
            ]);

            logInfo(__METHOD__." $movement inventory quantity $quantity for $reference",$inventory->getAttributes());  

            return true;
        }
        
        return false;
    }  
    /**
     * Sold inventory (when item is confirmed has stock and sold) 
     * Move stock from hold to sold (or rollback)
     * 
     * @param type $user
     * @param type $pid product id
     * @param type $sku
     * @param type $quantity
     * @param type $reference The object trigger the inventory movement
     * @param type $rollback Default to false; If true, it will reverse the process
     * @return boolean True for successful, False for failure
     * @throws CException
     */
    public function soldInventory($user,$pid,$sku,$quantity,$reference,$rollback=false)
    {
        $inventory = $this->findInventory($pid,$sku);
        if ($this->_validateInventory($inventory,$quantity,'hold',$rollback)){
            
            $holdBefore = $inventory->hold;
            $soldBefore = $inventory->sold;
            
            if ($rollback){
                $inventory->hold += $quantity;
                $inventory->sold -= $quantity;
                $movement = Sii::t('sii','ROLLBACK SOLD');
            }
            else {
                $inventory->hold -= $quantity;
                $inventory->sold += $quantity;
                $movement = Sii::t('sii','SOLD');
            }
        
            $inventory->update();
            $inventory->recordHistory(array(
                'inventory_id'=>$inventory->id,
                'description'=>Sii::t('sii','{movement} {before}>>{after}, HOLD {beforeHold}>>{afterHold} FOR {reference}',array(
                                            '{movement}'=>$movement,
                                            '{beforeHold}'=>$holdBefore,
                                            '{afterHold}'=>$inventory->hold,
                                            '{before}'=>$soldBefore,
                                            '{after}'=>$inventory->sold,
                                            '{reference}'=>$reference)),
                'type'=>InventoryHistory::TYPE_STATIC,
                'movement'=>$quantity,
                'post_available'=>$inventory->available,
                'post_quantity'=>$inventory->quantity,
                'create_by'=>$user,
            ));
            logInfo(__METHOD__." $movement inventory quantity $quantity for $reference",$inventory->getAttributes());  
            return true;
        }
        return false;
    }     
    /**
     * Return inventory (when item return is accepted) 
     * Move stock from sold to avalable 
     * 
     * @param type $user
     * @param type $pid product id
     * @param type $sku
     * @param type $quantity
     * @param type $reference The object trigger the inventory movement
     * @param type $rollback Default to false; If true, it will reverse the process
     * @return boolean True for successful, False for failure
     * @throws CException
     */
    public function returnInventory($user,$pid,$sku,$quantity,$reference)
    {
        $inventory = $this->findInventory($pid,$sku);
        if ($this->_validateInventory($inventory,$quantity,'return')){
            
            $inventory->available += $quantity;
            $inventory->sold -= $quantity;
            $movement = Sii::t('sii','RETURN SOLD');
            $inventory->update();
            $inventory->recordHistory(array(
                'inventory_id'=>$inventory->id,
                'description'=>Sii::t('sii','{movement} {before}>>{after}, AVAIL {beforeAvail}>>{afterAvail} FOR {reference}',array(
                                            '{movement}'=>$movement,
                                            '{beforeAvail}'=>$inventory->available - $quantity,
                                            '{afterAvail}'=>$inventory->available,
                                            '{before}'=>$inventory->sold + $quantity,
                                            '{after}'=>$inventory->sold,
                                            '{reference}'=>$reference)),
                'type'=>InventoryHistory::TYPE_INFLOW,
                'movement'=>$quantity,
                'post_available'=>$inventory->available,
                'post_quantity'=>$inventory->quantity,
                'create_by'=>$user,
            ));
            logInfo(__METHOD__." $movement inventory quantity $quantity for $reference",$inventory->getAttributes());  
            return true;
        }
        return false;
    }      
    /**
     * Empty inventory (confirm no more stock) 
     * This will release hold stock, and set inventory available to zero
     * Those purchased items with hold stock can still proceed processing next stage
     * 
     * @param type $user
     * @param type $pid product id
     * @param type $sku
     * @param type $quantity
     * @param type $reference The object trigger the inventory movement
     * @return boolean True for successful, False for failure
     * @throws CException
     */
    public function emptyInventory($user,$pid,$sku,$quantity,$reference)
    {
        $inventory = $this->findInventory($pid,$sku);
        if ($this->_validateInventory($inventory,$quantity,'hold')){
            $inventory->hold -= $quantity;
            $inventory->quantity =  $inventory->quantity - $inventory->available - $quantity;
            $inventory->available = 0;//set to zero
            $inventory->update();
            $inventory->recordHistory(array(
                'inventory_id'=>$inventory->id,
                'description'=>Sii::t('sii','EMPTY AVAILABLE STOCK FOR {reference}',array('{reference}'=>$reference)),
                'type'=>InventoryHistory::TYPE_OUTFLOW,
                'movement'=>$quantity,
                'post_available'=>$inventory->available,
                'post_quantity'=>$inventory->quantity,
                'create_by'=>$user,
            ));
            logInfo(__METHOD__.' empty SKU available stock for '.$reference,$inventory->getAttributes());  
            return true;
        }
        return false;
    }      
    /**
     * Confirm inventory is bad after inspection check 
     * Move stock from sold to bad
     * 
     * @param type $user
     * @param type $pid product id
     * @param type $sku
     * @param type $quantity
     * @param type $reference The object trigger the inventory movement
     * @param type $rollback Default to false; If true, it will reverse the process
     * @return boolean True for successful, False for failure
     * @throws CException
     */
    public function badInventory($user,$pid,$sku,$quantity,$reference,$rollback=false)
    {
        $inventory = $this->findInventory($pid,$sku);
        if ($this->_validateInventory($inventory,$quantity,'sold',$rollback)){
            
            $soldBefore = $inventory->sold;
            $badBefore = $inventory->bad;
            
            if ($rollback){
                $inventory->sold += $quantity;
                $inventory->bad -= $quantity;
                $movement = Sii::t('sii','ROLLBACK BAD');
            }
            else {
                $inventory->sold -= $quantity;
                $inventory->bad += $quantity;
                $movement = Sii::t('sii','BAD');
            }            
            
            $inventory->update();
            $inventory->recordHistory(array(
                'inventory_id'=>$inventory->id,
                'description'=>Sii::t('sii','{movement} {before}>>{after}, SOLD {beforeSold}>>{afterSold} FOR {reference}',array(
                                            '{movement}'=>$movement,
                                            '{beforeSold}'=>$soldBefore,
                                            '{afterSold}'=>$inventory->sold,
                                            '{before}'=>$badBefore,
                                            '{after}'=>$inventory->bad,
                                            '{reference}'=>$reference)),
                'type'=>InventoryHistory::TYPE_STATIC,
                'movement'=>$quantity,
                'post_available'=>$inventory->available,
                'post_quantity'=>$inventory->quantity,
                'create_by'=>$user,
            ));
            logInfo(__METHOD__." $movement inventory quantity $quantity for $reference",$inventory->getAttributes());  
            return true;
        }
        return false;
    }     
    /**
     * Find inventory by sku
     * 
     * @param integer $pid product id
     * @param string $sku
     * @return Inventory
     */
    public function findInventory($pid,$sku)
    {
        $inventory = Inventory::findBySKU($pid,$sku);
        if ($inventory==null){
            logError(__METHOD__.' sku inventory not found - '.$sku.' product '.$pid);
            throw new CException(Sii::t('sii','SKU {sku} not found',array('{sku}'=>$sku)));
        }
        return $inventory;
    }    
    /**
     * Check if inventory exists
     * 
     * @return boolean
     */
    public function existsInventory($pid,$sku)
    {
        logTrace(__METHOD__.' sku '.$sku.', product id '.$pid);
        return Inventory::findBySKU($pid,$sku)!=null;
    }    
    /**
     * Get inventory available by product id and product options (attributes)
     * Wrapper of Inventory::getInfoByProductOptions() as we should not access model methods directly
     * 
     * @return integer Inventory available
     */
    public function getAvailableByProductOptions($productId,$options)
    {
        $available = Inventory::getInfoByProductOptions($productId,$options,'available');
        logTrace(__METHOD__.' product='.$productId.' available='.$available.' options',$options);  
        return $available;
    }    
    /**
     * Get SKU by product id and sku parts (attributes)
     * Wrapper of Inventory::getInfoByProductOptions() as we should not access model methods directly
     * 
     * @return integer Inventory available
     */
    public function getSKUByParts($productId,$parts)
    {
        $sku = Inventory::getInfoByProductOptions($productId,$parts,'sku');
        logTrace(__METHOD__.' product='.$productId.' sku='.$sku.' parts',$parts);  
        return $sku;
    }    
    /**
     * Return inventory of sku
     * 
     * @return Inventory
     */
    private function _validateInventory($inventory,$quantity,$mode='available',$rollback=false)
    {
        if (!Helper::isInteger($quantity))
            throw new CException(Sii::t('sii','Purchased Quantity is not an integer'));
        
        if ($mode=='hold'){
            if (!$rollback && ($inventory->hold <= 0 || $inventory->hold < $quantity)){
                logError(__METHOD__.' SKU has insufficient hold stock for quantity '.$quantity,$inventory->getAttributes(),false);  
                throw new CException(Sii::t('sii','{source} ({sku}) has insufficient hold stock',array('{source}'=>$inventory->source->displayLanguageValue('name'),'{sku}'=>$inventory->sku)));
            }
            //for $rollback=true no validation
        }
        else if ($mode=='sold'){
            if (!$rollback && ($inventory->sold <= 0 || $inventory->sold < $quantity)){
                logError(__METHOD__.' SKU has insufficient sold stock for quantity '.$quantity,$inventory->getAttributes(),false);  
                throw new CException(Sii::t('sii','{source} ({sku}) has insufficient sold stock',array('{source}'=>$inventory->source->displayLanguageValue('name'),'{sku}'=>$inventory->sku)));
            }
        }
        else {
            if ($inventory->available <= 0 || $inventory->available < $quantity){
                logError(__METHOD__.' SKU is out of stock for quantity '.$quantity,$inventory->getAttributes(),false);  
                throw new CException(Sii::t('sii','{source} ({sku}) is out of stock',array('{source}'=>$inventory->source->displayLanguageValue('name'),'{sku}'=>$inventory->sku)));
            }
        }

        return true;
    }    

}
