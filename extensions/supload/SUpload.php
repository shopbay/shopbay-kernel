<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.extensions.xupload.XUpload");
/**
 * SUpload is an extension of XUpload but refresh its assets to use latest 
 * Jquery.File-Upload library (version 9.11.2 - Dec 2015).
 *
 * @author kwlok
 */
class SUpload extends XUpload 
{
    /*
     * Wheter or not to use jQuery UI plugin
     */
    public $useJQueryUIPlugin = false;  
    /*
     * Enable iframe cross-domain access via redirect option:
     */
    public $enableIframeCrossdomain = false;
    /**
     * Generates the required HTML and Javascript
     */
    public function run() 
    {
        list($name, $id) = $this->resolveNameID();
        
        if ($this->uploadTemplate === null) {
            $this->uploadTemplate = "#template-upload";
        }
        if ($this->downloadTemplate === null) {
            $this->downloadTemplate = "#template-download";
        }

        $this->render($this->uploadView);
        $this->render($this->downloadView);

        if (!isset($this->htmlOptions['enctype'])) {
            $this->htmlOptions['enctype'] = 'multipart/form-data';
        }

        if (!isset($this->htmlOptions['id'])) {
            $this->htmlOptions['id'] = $this->formId;
        }

        $this->options['url'] = $this->url;
        $this->options['autoUpload'] = $this->autoUpload;
        if ($this->autoUpload){
            $this->options = array_merge($this->options,array(
                'add'=>new CJavaScriptExpression('function (e, data) {data.submit();}'),
            ));
        }
        if (!$this->multiple) {
            $this->options['maxNumberOfFiles'] = 1;
        }

        //Initialize the jQuery File Upload widget:
        $init = CJavaScript::encode(array(
            // Uncomment the following to send cross-domain cookies:
            //'xhrFields'=> array('withCredentials'=>true),
            'url'=>$this->url,
        ));
        Yii::app()->clientScript->registerScript(__CLASS__.'init#'.$this->htmlOptions['id'], 
                        "jQuery('#{$this->htmlOptions['id']}').fileupload({$init});", 
                        CClientScript::POS_READY);
        //Enable iframe cross-domain access via redirect option:
        if ($this->enableIframeCrossdomain){
            $baseUrl = Yii::app()->baseUrl;
            Yii::app()->clientScript->registerScript(__CLASS__.'crossdomain#'.$this->htmlOptions['id'], 
                            "jQuery('#{$this->htmlOptions['id']}').fileupload('option','redirect',window.location.href.replace('/\/[^\/]*$/','$baseUrl/js/cors/result.html?%s'));", 
                            CClientScript::POS_READY);
        }

        //Enable image resizing, except for Android and Opera, which actually support image resizing, but fail to send Blob objects via XHR requests:
        $this->options['disableImageResize'] = '/Android(?!.*Chrome)|Opera/.test(window.navigator.userAgent)';
        $this->options['maxFileSize'] = 999000;

        $options = CJavaScript::encode($this->options);
        Yii::app()->clientScript->registerScript(__CLASS__.'option#'.$this->htmlOptions['id'], 
                        "jQuery('#{$this->htmlOptions['id']}').fileupload('option',{$options});", 
                        CClientScript::POS_READY);
        $htmlOptions = array();
        if ($this->multiple) {
            $htmlOptions["multiple"] = true;
            /* if($this->hasModel()){
                 $this -> attribute = "[]" . $this -> attribute;
             }else{
                 $this -> attribute = "[]" . $this -> name;
             }*/
        }

        $this->render($this->formView, compact('htmlOptions'));
    }    
    /**
     * Publises and registers the required CSS and Javascript
     * Jquery.File-Upload library (version 9.11.2 - Dec 2015)
     * @throws CHttpException if the assets folder was not found
     */
    public function publishAssets() 
    {
        $assets = dirname(__FILE__) . '/assets';
        $baseUrl = Yii::app() -> assetManager -> publish($assets);
        if (is_dir($assets)) {
            $this->registerCssAssets($baseUrl);
            $this->registerJsAssets($baseUrl);
            //The localization script
            $locale = CJavaScript::encode(array(
                'fileupload' => array(
                    'errors' => array(
                        "maxFileSize" => $this->t('File is too big'),
                        "minFileSize" => $this->t('File is too small'),
                        "acceptFileTypes" => $this->t('Filetype not allowed'),
                        "maxNumberOfFiles" => $this->t('Max number of files exceeded'),
                        "uploadedBytes" => $this->t('Uploaded bytes exceed file size'),
                        "emptyResult" => $this->t('Empty file upload result'),
                    ),
                    'error' => $this->t('Error'),
                    'start' => $this->t('Start'),
                    'cancel' => $this->t('Cancel'),
                    'destroy' => $this->t('Delete'),
                ),
            ));
            $js = "window.locale = {$locale}";
            Yii::app()->clientScript->registerScript('SUpload', $js, CClientScript::POS_END);
            /**
            <!-- The XDomainRequest Transport is included for cross-domain file deletion for IE 8 and IE 9 -->
            <!--[if (gte IE 8)&(lt IE 10)]>
            <script src="<?php echo Yii::app()->baseUrl; ?>/js/cors/jquery.xdr-transport.js"></script>
            <![endif]-->
             *
             */
        } 
        else {
            throw new CHttpException(500, __CLASS__ . ' - Error: Couldn\'t find assets to publish.');
        }
    }
    
    public function registerJsAssets($baseUrl)
    {
        //The Templates plugin is included to render the upload/download listings
        Yii::app()->clientScript->registerScriptFile($baseUrl.'/js/tmpl.min.js', CClientScript::POS_END);
        //The Load Image plugin is included for the preview images and image resizing functionality
        Yii::app()->clientScript->registerScriptFile($baseUrl.'/js/load-image.all.min.js', CClientScript::POS_END);
        //The Canvas to Blob plugin is included for image resizing functionality 
        Yii::app()->clientScript->registerScriptFile($baseUrl.'/js/canvas-to-blob.min.js', CClientScript::POS_END);
        //The Iframe Transport is required for browsers without support for XHR file uploads
        Yii::app()->clientScript->registerScriptFile($baseUrl.'/js/jquery.iframe-transport.js', CClientScript::POS_END);
        // The basic File Upload plugin
        Yii::app()->clientScript->registerScriptFile($baseUrl.'/js/jquery.fileupload.js', CClientScript::POS_END);
        //The File Upload processing plugin
        Yii::app()->clientScript->registerScriptFile($baseUrl.'/js/jquery.fileupload-process.js', CClientScript::POS_END);
        //he File Upload image preview & resize plugin
        Yii::app()->clientScript->registerScriptFile($baseUrl.'/js/jquery.fileupload-image.js', CClientScript::POS_END);
        //The File Upload audio preview plugin
        Yii::app()->clientScript->registerScriptFile($baseUrl.'/js/jquery.fileupload-audio.js', CClientScript::POS_END);
        //The File Upload video preview plugin
        Yii::app()->clientScript->registerScriptFile($baseUrl.'/js/jquery.fileupload-video.js', CClientScript::POS_END);
        //The File Upload validation plugin 
        Yii::app()->clientScript->registerScriptFile($baseUrl.'/js/jquery.fileupload-validate.js', CClientScript::POS_END);
        //The File Upload user interface plugin
        Yii::app()->clientScript->registerScriptFile($baseUrl.'/js/jquery.fileupload-ui.js', CClientScript::POS_END);
        if ($this->useJQueryUIPlugin){
            //The File Upload jQuery UI plugin
            Yii::app()->clientScript->registerScriptFile($baseUrl.'/js/jquery.fileupload-jquery-ui.js', CClientScript::POS_END);
        }
    }
    /**
     * For CSS adjustments for browsers with JavaScript disabled, include following
     * <noscript><link rel="stylesheet" href="css/jquery.fileupload-noscript.css"></noscript>
     * <noscript><link rel="stylesheet" href="css/jquery.fileupload-ui-noscript.css"></noscript>
     * @param type $baseUrl
     */
    public function registerCssAssets($baseUrl)
    {
        //CSS to style the file input field as button and adjust the Bootstrap progress bars
        Yii::app()->clientScript->registerCssFile($baseUrl.'/css/jquery.fileupload.css');
        Yii::app()->clientScript->registerCssFile($baseUrl.'/css/jquery.fileupload-ui.css');
    }
    
    protected function t($message, $params=array())
    {
        return Yii::t('supload.sii', $message, $params);
    }   
    
    public function getFormId()
    {
        return get_class($this->model)."-form";
    }
}
