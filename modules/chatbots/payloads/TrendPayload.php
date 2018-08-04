<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.modules.chatbots.payloads.ChatbotPayload");
/**
 * Description of TrendPayload
 *
 * @author kwlok
 */
class TrendPayload extends ChatbotPayload 
{
    CONST RECENT_LIKES       = 'recent_likes';
    CONST RECENT_PURCHASES   = 'recent_purchases';
    CONST RECENTLY_DISCUSSED = 'recently_discussed';
    CONST MOST_LIKES         = 'most_likes';
    CONST MOST_PURCHASED     = 'most_purchased';
    CONST MOST_DISCUSSED     = 'most_discussed';
    /**
     * Type prefix
     * @return string
     */
    public function getTypePrefix()
    {
        return ChatbotPayload::TREND;
    }    
    /**
     * @return the payload title
     */
    public function getTitle()
    {
        $type = substr($this->type, strlen($this->typePrefix));
        switch ($type) {
            case self::RECENT_LIKES:
                return Sii::t('sii','Recent likes');
            case self::RECENT_PURCHASES:
                return Sii::t('sii','Recent purchases');
            case self::RECENTLY_DISCUSSED:
                return Sii::t('sii','Recently discussed');
            case self::MOST_LIKES:
                return Sii::t('sii','Most likes');
            case self::MOST_PURCHASED:
                return Sii::t('sii','Most purchased');
            case self::MOST_DISCUSSED:
                return Sii::t('sii','Most discussed');
            default:
                return null;
        }
    }
}
