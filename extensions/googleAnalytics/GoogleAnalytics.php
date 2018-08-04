<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * GoogleAnalytics widget using TPGoogleAnalytics
 * @see TPGoogleAnalytics
 *
 * @author kwlok
 */
class GoogleAnalytics extends CApplicationComponent
{
    /**
     * GTM Account ID, from the configuration
     * @var string
     */
    public $gtmAccount;
    /**
     * if to load the widget
     * @var type 
     */
    public $enable = false;
    /**
     * init function - Yii automaticall calls this
     */
    public function init()
    {
        //init code
    }
    /**
     * Render script
     * @return type
     */
    public function renderGTM()
    {
        if ($this->enable && $this->gtmAccount != null) {
            // Start the JS string
            $js = <<<EOJS
<noscript><iframe src="//www.googletagmanager.com/ns.html?id=$this->gtmAccount"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'//www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','$this->gtmAccount');</script>
EOJS;
            return $js;
        }
        else
            return null;
    }
    /**
     * Load Global Site Tag (gtag.js)for tracking
     * @param string $trackingId
     * @return type
     */
    public function renderGTag($trackingId=null)
    {
        if ($trackingId==null)
            $trackingId = $this->gtmAccount;
        
        if ($this->enable && $trackingId != null) {
            // Start the JS string
            $js = <<<EOJS
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=$trackingId"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', '$trackingId');
</script>
EOJS;
            return $js;
        }
        else
            return null;
    }
}
