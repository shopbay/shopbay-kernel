<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.services.ServiceManager");
/**
 * Description of TaxManager
 *
 * @author kwlok
 */
class TaxManager extends ServiceManager 
{
    /**
     * Initialization
     */
    public function init() 
    {
        parent::init();
    } 
    /**
     * Create model
     * 
     * @param integer $user Session user id
     * @param CModel $model CModel model to update
     * @return CModel $model
     * @throws CException
     */
    public function create($user,$model)
    {
        $this->validate($user, $model, false);
        
        $model->account_id = $user;
        $model->status =  Process::TAX_OFFLINE;

        $model->account_id = $user;
        return $this->execute($model, array(
            'insert'=>self::EMPTY_PARAMS,
            'recordActivity'=>array(
                'event'=>Activity::EVENT_CREATE,
                'description'=>$model->name,
            ),
        ));
    }
    /**
     * Update model
     * 
     * @param integer $user Session user id
     * @param CModel $model CModel model to update
     * @param boolean $checkAccess Indicate if to perform check access; Default to 'true'
     * @return CModel $model
     * @throws CException
     */
    public function update($user,$model,$checkAccess=true)
    {
        $this->validate($user, $model, $checkAccess);
        return $this->execute($model, array(
            'update'=>self::EMPTY_PARAMS,
            'recordActivity'=>array(
                'event'=>Activity::EVENT_UPDATE,
                'description'=>$model->name,
            ),
        ));
    }
    /**
     * Delete model
     * 
     * @param integer $user Session user id
     * @param CModel $model Model to update
     * @param boolean $checkAccess Indicate if to perform check access; Default to 'true'
     * @return CModel $model
     * @throws CException
     */
    public function delete($user,$model,$checkAccess=true)
    {
        $this->validate($user, $model, $checkAccess);
        return $this->execute($model, array(
                    'recordActivity'=>array(
                        'event'=>Activity::EVENT_DELETE,
                        'description'=>$model->name,
                        'account'=>$user,
                    ),
                    'delete'=>self::EMPTY_PARAMS,
                ),'delete');
    }
    /**
     * Return shop tax payables in array of following data elements, for example:
     * <pre>
     * array (
     *   'tax id=> 'tax name|tax payable',
     *    1=>'GST 7%|$2.14', //for example
     *    2=>'VAT 10%|$5.00', //for example
     *      ...
     * )
     * </pre>
     * @param CModel $shop This is shop instance 
     * @param type $amount The total amount subject to tax
     * @return array Array of payables; Empty array if no payable
     */
    public function checkPayables($shop,$amount)
    {
        if (!($shop instanceof Shop))
            throw new CException(Sii::t('sii','Invalid shop instance'));
        
        $payables = new CMap();
        $taxes = $shop->getTaxes();
        if (count($taxes)>0){
            foreach ($taxes as $tax) {
                $payables->add($tax->id,$this->setTaxData($tax, $amount));
            }
            logTrace(__METHOD__.' shop='.$shop->id.', amount='.$amount,$payables->toArray());
        }
        else {
            logTrace(__METHOD__.' shop='.$shop->id.', amount='.$amount.', no payables');
        }
        return $payables->toArray();
    }   
    /**
     * Compute tax total 
     * @param array $payables Expect return from $this->checkPayables()
     */
    public function getPayablesTotal($payables)
    {
        $total = 0.0;
        foreach ($payables as $data) {
            $payable = $this->parseTaxData($data);
            $total += $payable->amount;
        }
        return $total;
    }
    /**
     * Compute tax rate 
     * @param array $payables Expect return from $this->checkPayables()
     */
    public function getPayablesRate($payables)
    {
        $rate = 0.0;
        foreach ($payables as $data) {
            $payable = $this->parseTaxData($data);
            $rate += $payable->rate;
        }
        return $rate;
    }
    /**
     * Set tax data for transfer (DTO)
     * @return string
     */
    public function setTaxData($tax,$amount)
    {
        return $tax->name.Helper::PIPE_SEPARATOR.
               $tax->getPayable($amount).Helper::PIPE_SEPARATOR.
               $tax->getPayable($amount,true).Helper::PIPE_SEPARATOR.
               $tax->rate.Helper::PIPE_SEPARATOR.
               $tax->formatPercentage($tax->rate);
    }  
    /**
     * Parse tax data for transfer (DTO)
     * @return array
     */
    public function parseTaxData($data)
    {
        $d = explode(Helper::PIPE_SEPARATOR, $data);
        return (object)array(
            'name'=>$d[0],
            'amount'=>$d[1],
            'amount_text'=>$d[2],
            'rate'=>$d[3],
            'rate_text'=>$d[4],
        );                
    }         
    
}
