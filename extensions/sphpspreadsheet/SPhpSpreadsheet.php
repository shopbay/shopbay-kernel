<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.extensions.sphpspreadsheet.Psr');
/**
 * SPhpSpreadsheet class file - A wrapper class of PhpSpreadsheet for use in context
 * 
 * @see Vendor library at common.vendors.phpspreadsheet
 * 
 * @see https://phpspreadsheet.readthedocs.io/en/develop/
 * 
 * @author kwlok
 */
class SPhpSpreadsheet extends CApplicationComponent
{
    /**
     * Supported file types to read
     * @var type 
     */
    private $_fileTypes = ['Xls', 'Xlsx'];//can be extended. 
    /**
     * Init 
     */
    public function init()
    {
        parent::init();
        // unregister Yii's autoloader
        spl_autoload_unregister(['YiiBase', 'autoload']);
        
        // Autoload PhpSpreadsheet classes
        $classpath = Yii::getPathOfAlias('common.extensions.sphpspreadsheet');
        require_once($classpath.DIRECTORY_SEPARATOR.'Psr_autoloader.php');
        require_once($classpath.DIRECTORY_SEPARATOR.'PhpSpreadsheet_autoloader.php');

        // register Yii's autoloader again
        spl_autoload_register(['YiiBase', 'autoload']);        
    }
    /**
     * Validate if file is in supported file types
     * @param type $inputFileName
     * @return boolean
     */
    public function validateSpreadsheet($inputFileName)
    {
        /**  Identify the type of $inputFileName  **/
        $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($inputFileName);
        
        $valid = false;
        foreach ($this->_fileTypes as $fileType) {
            if ($fileType == $inputFileType) {
                $valid = true;
                break;
            }
        }
        if (!$valid)
            throw new CException(Sii::t('sii','File "{file}" is not in Excel Format.',['{file}'=>$inputFileName]));
        else
            return $inputFileType;
    }
    /**
     * Read an active worksheet and return data
     * @param type $filepath
     * @return array
     */
    public function readSpreadsheet($filepath)
    {
        /**  Identify the type of $inputFileName  **/
        $inputFileType = $this->validateSpreadsheet($filepath);
        /**  Create a new Reader of the type defined in $inputFileType  **/
        $readerClass = '\\PhpOffice\\PhpSpreadsheet\\Reader\\'.$inputFileType;
        $reader = new $readerClass();
        $reader->setReadDataOnly(true);
        /**  Load $filepath to a Spreadsheet Object  **/
        $spreadsheet = $reader->load($filepath);
        $array = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
        return $this->trimRowsAndColumns($array);
    }
    /**
     * This will trim empty rows and columns
     * @param type $sheetArray
     * @return type
     */
    protected function trimRowsAndColumns($sheetArray)
    {
        $data = array_map('array_filter', $sheetArray);
        return array_filter($data);        
    }
}
