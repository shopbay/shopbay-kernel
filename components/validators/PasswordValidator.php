<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of PasswordValidator
 *
 * @author kwlok
 */
class PasswordValidator extends CValidator 
{
    const WEAK   = 0;
    const STRONG = 1;

    public $allowNull = false;//when true, password will be checked on non-empty values only
    public $strength;
    /**
     * Password must be between 8 and 15 characters long, 
     * contains at least one number and one uppercase letter
     */
    private $weak_pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,15}$/';
    /**
     * Password must be between 8 and 15 characters long, 
     * contains at least one number, one uppercase letter, and one special character
     */
    private $strong_pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\da-zA-Z]).{8,15}$/';
    /**
     * Validates the attribute of the object.
     * If there is any error, the error message is added to the object.
     * @param CModel $object the object being validated
     * @param string $attribute the attribute being validated
     */
    protected function validateAttribute($object,$attribute)
    {
        // check the strength parameter used in the validation rule of our model
        if ($this->strength == self::WEAK) {
            $message = Sii::t('sii', 'Password must be between 8 and 15 characters long, contains at least one number and one uppercase letter.');
            $pattern = $this->weak_pattern;
        } elseif ($this->strength == self::STRONG) {
            $message = Sii::t('sii', 'Password must be between 8 and 15 characters long, contains at least one number, one uppercase letter and one special character.');
            $pattern = $this->strong_pattern;
        }

        //extract the attribute value from it's model object
        $value = $object->$attribute;
        if ($this->allowNull) {
            if (!empty($value)){
                if (!preg_match($pattern, $value)) {
                    $this->addError($object, $attribute, $message);
                }
            }
        }
        else {
            if (!preg_match($pattern, $value)) {
                $this->addError($object, $attribute, $message);
            }
        }
        
    }
    
}
