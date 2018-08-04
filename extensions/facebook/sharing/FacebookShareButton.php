<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of FacebookShareButton
 *
 * @author kwlok
 */
class FacebookShareButton extends CWidget
{
    public $url;
    /**
     * Run the widget
     */
    public function run()
    {
        if (isset($this->url))
            echo '<iframe src="https://www.facebook.com/plugins/share_button.php?href='.$this->url.'&layout=button_count&mobile_iframe=false&width=87&height=20&appId" width="87" height="20" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowTransparency="true"></iframe>';
    }

}
