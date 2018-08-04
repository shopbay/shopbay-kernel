<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of CartData
 *
 * @author kwlok
 */
class CartData extends CComponent
{
    /**
     * Cart Item key Format: [sku]@[shipping].[shop].[campaign]
     * 
     * @param type $sku
     * @param type $shop
     * @param type $shipping
     * @param type $campaign Optional
     * @param type $seed Optional Additional seed; can be used by campaign offered item
     * @return string Encrypted value of item key
     */
    public static function formatItemKey($sku,$shop,$shipping,$campaign=null,$seed=null)
    {
        $key = $sku.'@'.$shipping.'.'.$shop.(isset($campaign)?'.'.$campaign:'').(isset($seed)?'#'.$seed:'');
        logTrace(__METHOD__.' '.$key);
        return Helper::hex_encode(base64_encode($key));//ensure output is alphanumeric and url safe
    }
    /**
     * Format Cart Item key: [sku]@[shipping].[shop].[campaign]
     * @param string $encodedKey
     * @return array Array of individual key component values
     */
    public static function parseItemKey($encodedKey)
    {
        $key = base64_decode(Helper::hex_decode($encodedKey));
        //retrieve sku
        $components = explode('@', $key);
        $sku = $components[0];
        //retrieve shipping and shop, campaign if any
        $domain = explode('.', $components[1]);
        $shipping = $domain[0];
        $shop = $domain[1];
        $result = ['sku'=>$sku,'shop'=>$shop,'shipping'=>$shipping];
        if (isset($domain[2]))
            $result = array_merge($result,['campaign'=>$domain[2]]);
        logTrace(__METHOD__,$result);
        return $result;
    }       
    /**
     * Parse shop id based on item key
     * @param string $encodedKey
     * @return string shop id
     */
    public static function parseShop($encodedKey)
    {
        $components = CartData::parseItemKey($encodedKey);
        return $components['shop'];
    }    
    /**
     * Parse shop id based on item key
     * @param string $encodedKey
     * @return string shop id
     */
    public static function parseSKU($encodedKey)
    {
        $components = CartData::parseItemKey($encodedKey);
        return $components['sku'];
    }        
}
