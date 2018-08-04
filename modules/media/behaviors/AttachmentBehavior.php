<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * AttachmentBehavior
 * @author kwlok
 */
class AttachmentBehavior extends CActiveRecordBehavior 
{
    /**
    * @var string The name of the attribute for root directory. Defaults to '/undefined'
    */
    public $stateVariable = 'undefined';
    /**
     * @var boolean If to use owner object type and id. Default to "false"
     */
    public $useOwner = false;
    /**
     * afterSave
     *
     * @param mixed $event
     * @access public
     * @return void
     */
    public function afterSave($event)
    {
        //save attachment record if any
        
        if( SActiveSession::exists($this->stateVariable) ) {
            
            $userAttachments = SActiveSession::get($this->stateVariable);
                
            foreach($userAttachments as $file){

                logTrace(__METHOD__." parsing attachment file...",$file);
                
                if( is_file( $file["path"] ) ) {
                   
                        $attachment = new Attachment;
                        $attachment->obj_id = $this->useOwner?$this->getOwner()->id:$file['obj_id'];
                        $attachment->obj_type = $this->useOwner?$this->getOwner()->tableName():$file['obj_type'];
                        $attachment->group = $file['group'];
                        $attachment->name = $file['name'];
                        $attachment->description = $file['description'];
                        $attachment->filename = $file['filename'];
                        $attachment->src_url = $attachment->downloadUrl;
                        $attachment->mime_type = $file['mime'];
                        $attachment->size = $file['size'];
                        if( !$attachment->save( ) ) {
                            logError(__METHOD__." Could not save Attachment",$attachment->getErrors());
                            //this exception will rollback the transaction
                            throw new Exception( 'Could not save Attachment');
                        }
                        //move file (after save, so that filepath can be identified)
                        //@see Downloadable
                        if( rename( $file["path"], $attachment->filepath ) ) {
                            chmod( $attachment->filepath, 0777 );                        
                            logTrace(__METHOD__." file move ok",$attachment->filepath);
                        }
                        
                } else {
                    //You can also throw an execption here to rollback the transaction
                    logWarning(__METHOD__.' '.$file["path"]." is not a file");
                }

            }

             //clear session attachments
             SActiveSession::clear($this->stateVariable);
            
        }
            
    }
    
}