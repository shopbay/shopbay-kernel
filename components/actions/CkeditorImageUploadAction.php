<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of CkeditorImageUploadAction
 *
 * @author kwlok
 */
class CkeditorImageUploadAction extends CAction 
{
    public $supportMimeType = ['image/gif','image/jpeg','image/png'];
    /**
     * The domain to host the uploaded image
     * Default to host domain (where the app domain is calling this action)
     * TODO Should we have a cdn domain to host all public accessible images
     * @var string 
     */
    public $hostDomain;
    /**
     * Set if the uploaded image file is to be accessed using preview mode
     * Note: preview mode means it is publicly accessible and not protected by authentication
     * @var string 
     */
    public $imagePreview = true;
    /**
     * The main action that handles the image upload from ckeditor.
     */
    public function run() 
    {
        if(isset($_FILES['upload'])){
            
            try {

                $upload = CUploadedFile::getInstanceByName('upload');

                if (!empty($upload)){

                    if (in_array($upload->getType(),$this->supportMimeType)){
                        $media = Yii::app()->serviceManager->mediaManager->create(user()->getId(),[
                                    'status'=>Process::MEDIA_ONLINE,//default set to online
                                    'name'=>$upload->getName(),
                                    'filename'=>'cked!!'.time().'.'.$upload->getExtensionName(),
                                    'mime_type'=>$upload->getType(),
                                    'size'=>$upload->getSize(),
                                    'move_file'=>false,
                                ]);
                        if ($media->hasErrors()){
                            throw new CException(Helper::htmlErrors($media->errors));                            
                        }
                        else {
                            if ($upload->saveAs($media->filepath)){
                                // Required: anonymous function reference number as explained above.
                                $funcNum = $_GET['CKEditorFuncNum'] ;
                                // Optional: instance name (might be used to load a specific configuration file or anything else).
                                $CKEditor = $_GET['CKEditor'];
                                // Optional: might be used to provide localized messages.
                                $langCode = $_GET['langCode'];
                                // Check the $_FILES array and save the file. Assign the correct path to a variable ($url).
                                if ($this->hostDomain==null)
                                    $this->hostDomain = Yii::app()->urlManager->hostDomain;
                                $url = $media->getAssetUrl($this->hostDomain,$this->imagePreview);
                                // Usually you will only assign something here if the file could not be uploaded.
                                $message = '';

                                $output = '<html><body><script type="text/javascript">window.parent.CKEDITOR.tools.callFunction('.$funcNum.', "'.$url.'","'.$message.'");</script></body></html>';
                                echo $output;
                            }
                            else
                                throw new CException('Image upload failed');                            
                        }
                    }
                    else
                         throw new CException('Image format not supported: '.$upload->getType());
                }

            } catch(CException $e) {
                logError($e->getMessage());
                echo $e->getMessage();        
            }

        }
    }                
}