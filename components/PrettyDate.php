<?php 
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * PrettyDate class
 * Example:
 * pass in a String DateTime, compared to another String DateTime (defaults to now)
 * <pre>
 * echo Date_Difference::getStringResolved('-1 weeks');
 * echo '<br>';
 * echo Date_Difference::getStringResolved('-7 days', '+1 week');
 * echo '<br>';
 * </pre>
 *
 * pass in a DateTime object, compared to another DateTime object (defaults to now)
 * useful with the Propel ORM, which uses DateTime objects internally.
 * <pre>
 * echo Date_Difference::getString(new DateTime('-7 weeks'));
 * echo '<br>';
 * echo Date_Difference::getString(new DateTime('-7 weeks'), new DateTime('+1 week'));
 * echo '<br>';
 * </pre>
 * @author kwlok
 */
class PrettyDate  
{ 
    public static function getStringResolved($date, $compareTo = NULL) 
    { 
        if(!is_null($compareTo)) { 
            $compareTo = new DateTime($compareTo); 
        } 
        return self::getString(new DateTime($date), $compareTo); 
    } 

    public static function getString(DateTime $date, DateTime $compareTo = NULL) 
    { 
        if(is_null($compareTo)) { 
            $compareTo = new DateTime('now'); 
        } 
        $diff = $compareTo->format('U') - $date->format('U'); 
        $dayDiff = floor($diff / 86400); 

        if(is_nan($dayDiff) || $dayDiff < 0) { 
            return ''; 
        } 
                 
        if($dayDiff == 0) { 
            if($diff < 60) { 
                return Sii::t('sii','Just now'); 
            } elseif($diff < 120) { 
                return Sii::t('sii','1 minute ago'); 
            } elseif($diff < 3600) { 
                return Sii::t('sii','{n} minutes ago',array(floor($diff/60))); 
            } elseif($diff < 7200) { 
                return Sii::t('sii','1 hour ago'); 
            } elseif($diff < 86400) { 
                return Sii::t('sii','{n} hours ago',array(floor($diff/3600))); 
            } 
        } elseif($dayDiff == 1) { 
            return Sii::t('sii','Yesterday'); 
        } elseif($dayDiff < 7) { 
            return Sii::t('sii','{n} days ago',array($dayDiff));
        } elseif($dayDiff == 7) { 
            return Sii::t('sii','1 week ago'); 
        } elseif($dayDiff < (7*6)) { // Modifications Start Here 
            // 6 weeks at most 
            return Sii::t('sii','{n} weeks ago',array(ceil($dayDiff/7))); 
        } elseif($dayDiff < 365) { 
            return Sii::t('sii','{n} months ago',array(ceil($dayDiff/(365/12))));
        } else { 
            $years = round($dayDiff/365); 
            return Sii::t('sii','{n} year{s} ago',array($years,'{s}'=>$years != 1 ? 's' : ''));
        } 
    } 
}