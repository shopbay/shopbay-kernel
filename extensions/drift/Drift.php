<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of Drift
 *
 * @author kwlok
 */
class Drift extends CApplicationComponent
{
    public $version = '0.2.0';
    public $id = 'undefined';
    public $app;//the app to include the Drift
    public $enable = false;//if to show widget for guest
    public $enableAfterLogin = false;//if to show widget after login (authenticated user)
    /**
     * Init widget
     */
    public function init()
    {
        //init code
    }
    /**
     * Disable app to include Drift
     * @param type $app
     */
    public function disableApp($app)
    {
        if (isset($this->app[$app]))
            unset($this->app[$app]);
        if ($app=='all')
            $this->app = [];//clear all
    }
    /**
     * Generate snippet code
     */
    public function renderScript()
    {                
        if (user()->isGuest  && in_array($this->app, $this->enable)){
            $this->loadScript();
            logTrace(__METHOD__.' Enable for app '.$this->app);
        }
        if (!user()->isGuest && in_array($this->app, $this->enableAfterLogin)){
            $this->loadScript();
            logTrace(__METHOD__.' Enable "after-login" for app '.$this->app);
        }
    }
    /**
     * Load script
     */
    protected function loadScript()
    {
        $script = <<<EOJS
!function() {
  var t;
  if (t = window.driftt = window.drift = window.driftt || [], !t.init) return t.invoked ? void (window.console && console.error && console.error("Drift snippet included twice.")) : (t.invoked = !0, 
  t.methods = [ "identify", "track", "reset", "debug", "show", "ping", "page", "hide", "off", "on" ], 
  t.factory = function(e) {
    return function() {
      var n;
      return n = Array.prototype.slice.call(arguments), n.unshift(e), t.push(n), t;
    };
  }, t.methods.forEach(function(e) {
    t[e] = t.factory(e);
  }), t.load = function(t) {
    var e, n, o, r;
    e = 3e5, r = Math.ceil(new Date() / e) * e, o = document.createElement("script"), 
    o.type = "text/javascript", o.async = !0, o.crossorigin = "anonymous", o.src = "https://js.driftt.com/include/" + r + "/" + t + ".js", 
    n = document.getElementsByTagName("script")[0], n.parentNode.insertBefore(o, n);
  });
}();
drift.SNIPPET_VERSION = '$this->version'
drift.load('$this->id')
EOJS;
        Helper::registerJs($script,'drift',CClientScript::POS_BEGIN);         
    }
}
