<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of WizardBase
 *
 * @author kwlok
 */
class WizardBase extends CComponent
{
    private $_id;//id
    private $_n;//name
    /**
     * WizardBase constructor
     * @param string $id 
     */
    public function __construct($id)
    {
        $this->_id = $id;
        $this->_n = Sii::t('sii','Wizard');
        $this->attachBehaviors($this->behaviors());
    }
    /**
     * @return behaviors
     */
    public function behaviors()
    {
        return array();
    }
    /**
     * @return id
     */
    public function getId()
    {
        return $this->_id;
    }
    /**
     * @return name
     */
    public function getName()
    {
        return $this->_n;
    }
    /**
     * Set name
     * @param name
     */
    public function setName($name)
    {
        $this->_n = $name;
    }
}