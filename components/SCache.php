<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of SCache
 *
 * @author kwlok
 */
class SCache extends CFileCache
{
    const PUBLISHED_PACKAGES = 'published_packages';
    const FEATURE_CACHE = '__feature_cache';
    const FEATURES_CACHE = '__features_cache';//all features
    const TAGS_CACHE     = '__tags_cache';
    const PAGE_CACHE    = '__page_cache';
    const REMOTE_DELETE_TOKEN = 'CLEARCACHE-28862-0910100-142-218';//token to gain remote deletion
    
    public $remoteDelete = true;
    /**
     * Initializes this application component.
     * This method is required by the {@link IApplicationComponent} interface.
     */
    public function init()
    {
        $this->keyPrefix = __CLASS__;
        $this->cachePath = KERNEL.'/runtime/cache';
        parent::init();
    }    
    /**
     * @overriden
     * This overrides the parent delete, 
     * and attempts to delete remote also cache when app are distributed
     * @param string $id the key of the value to be deleted
     * @return boolean if no error happens during deletion
     */
    public function delete($id)
    {
        $res1 = $this->localDelete($id);
        if ($this->remoteDelete){
            logTrace(__METHOD__.' remote cache '.$id.'... ');
            $res2 = $this->remoteDelete($id);
        }
        return $res1 && (isset($res2)?$res2:true);
    }
    /**
     * Delete cache located locally
     */
    public function localDelete($id)
    {
        return parent::delete($id);
    }
    /**
     * Delete cache located remotely
     */
    public function remoteDelete($id)
    {
        $url = Yii::app()->urlManager->createMerchantUrl('site/maintenance/ops/'.self::REMOTE_DELETE_TOKEN.'.'.$id,true);
        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_SSL_VERIFYPEER => false, //skip verifying SSL cert
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_HTTPHEADER => array(
            "cache-control: no-cache",
          ),
        ));
        
        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            logError(__METHOD__." cURL Error #:" . $err);
            return false;
        } else {
            logInfo(__METHOD__.' cache '.$id.' ok at '.$url, $response);
            return true;
        }        
    }
}
