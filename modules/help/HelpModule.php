<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * Description of HelpModule
 *
 * @author kwlok
 */
class HelpModule extends SModule 
{
    /**
     * @property string the default controller.
     */
    public $entryController = 'undefined';
    /**
     * Behaviors for this module
     */
    public function behaviors()
    {
        return [
            'assetloader' => [
                'class'=>'common.components.behaviors.AssetLoaderBehavior',
                'name'=>'help',
                'pathAlias'=>'help.assets',
            ],
        ];
    }

    public function init()
    {
        // import the module-level models and components
        $this->setImport([
            'help.components.*',
        ]);
        // import module dependencies classes
        $this->setDependencies([]);             

        $this->defaultController = $this->entryController;

        //load layout and common css/js files
        $this->registerScripts();
        
        //load _process.css, and register its assets
        $this->registerProcessCssFile();
        
        //create full process list markdown at "docs" folder
        $this->_createProcessListMarkdown();

    }
    /*
     * Publish images required by all the help files (md files)
     */
    public function publishImages()
    {
        $src = Yii::getPathOfAlias('help.docs.images');
        $dest = Yii::getPathOfAlias('webroot.images.help');
        $filehelper = new CFileHelper();
        if (!file_exists($dest) || Yii::app()->assetManager->forceCopy){
            $filehelper->copyDirectory($src,$dest);
        }
    } 
    
    private function _createProcessListMarkdown()
    {
        $mdfile = '_process.md';
        $basepath = Yii::getPathOfAlias('help.docs');
        $filepath = $basepath.DIRECTORY_SEPARATOR.$mdfile;
        if (!file_exists($filepath)){
            $content  = "Process List\n";
            $content .= "============\n\n";
            foreach (Process::getList() as $process) {
                //adding css of each process status label   
                $process_text = strtolower($process['text']);
                $content .= "<code class=\"status ".str_replace(" ","-",$process_text)."\">".$process_text."</code>\n\n";
            }
            // Write the contents to the file
            file_put_contents($filepath, $content);
        }
    }  

}