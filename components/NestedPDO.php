<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * NestedPDO class
 * @author kwlok
 */
class NestedPDO extends PDO 
{
    // Database drivers that support SAVEPOINTs.
    protected static $savepointTransactions = array("pgsql", "mysql");
 
    // The transaction id.
    protected $transId;
    // The current transaction level.
    protected $transLevel = 0;
 
    protected function nestable() 
    {
        return in_array($this->getAttribute(PDO::ATTR_DRIVER_NAME), self::$savepointTransactions);
    }
 
    public function beginTransaction() 
    {       
        if($this->transLevel == 0 || !$this->nestable()) {
            parent::beginTransaction();
            $this->transId = time();
            //logTrace(__METHOD__.' '.md5(spl_object_hash($this)).' SAVEPOINT LEVEL '.$this->transLevel);
            logTrace(__METHOD__.' '.$this->transId.' SAVEPOINT LEVEL '.$this->transLevel);
            $this->exec("SAVEPOINT LEVEL{$this->transLevel}");
            
        } else {
            logTrace(__METHOD__.' '.$this->transId.' SAVEPOINT LEVEL '.$this->transLevel);
            $this->exec("SAVEPOINT LEVEL{$this->transLevel}");
        }
 
        $this->transLevel++;
    }
 
    public function commit() 
    {
        $this->transLevel--;
        if($this->transLevel == 0 || !$this->nestable()) {
            logTrace(__METHOD__.' '.$this->transId.' physical commit SAVEPOINT LEVEL '.$this->transLevel);
            parent::commit();
        } else {
            logTrace(__METHOD__.' '.$this->transId.' logical commit SAVEPOINT LEVEL '.$this->transLevel);
            $this->exec("RELEASE SAVEPOINT LEVEL{$this->transLevel}");
        }
    }
 
    public function rollback() 
    {
        $this->transLevel--;
        if($this->transLevel == 0 || !$this->nestable()) {
            logTrace(__METHOD__.' '.$this->transId.' physical rollback SAVEPOINT LEVEL '.$this->transLevel);
            parent::rollback();
        } else {
            logTrace(__METHOD__.' '.$this->transId.' logical rollback SAVEPOINT LEVEL '.$this->transLevel);
            $this->exec("ROLLBACK TO SAVEPOINT LEVEL{$this->transLevel}");
        }
    }
}