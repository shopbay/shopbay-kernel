<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.modules.orders.components.OrderNumberGenerator");
/**
 * Description of ReceiptNumberGenerator
 * @author kwlok
 */
class ReceiptNumberGenerator extends OrderNumberGenerator
{
    public $separator = ReceiptNumberGenerator::SEPARATOR_DASH;
    public $prefix = 'R';
    public $template = '{prefix}{randomstring}{separator}{checksum}';
    /**
     * Generate the number
     * @return string
     */
    public function generate()
    {
        return parent::generate(self::DATETIME);
    }
    /**
     * A up to 2 char length shop code (to represent the station generate the receipt number)
     * @return type
     */
    protected function getStation()
    {
        $seed = rand(0,9);
        if (isset($this->owner->account_id))
            $seed += $this->owner->account_id;
        return strtoupper(base_convert($this->getChecksum((string)$seed, 2),10,36));
    }
    /**
     * Receipt number example
     * @return string
     */
    public static function example()
    {
        Yii::import("common.modules.billings.models.Receipt");
        $num = new ReceiptNumberGenerator(new Receipt());
        return Sii::t('sii','Example: {example}',['{example}'=>$num->generate()]);
    }           
}
