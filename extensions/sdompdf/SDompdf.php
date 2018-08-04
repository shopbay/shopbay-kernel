<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
define("DOMPDF_UNICODE_ENABLED", true);
/**
 * Description of SDompdf
 *
 * @author kwlok
 */
class SDompdf extends CComponent
{
    private $_dompdf;//instance
    /**
     * Construct SDompdf
     */
    public function __construct() 
    {
        $path = Yii::getPathOfAlias('common.vendors.dompdf');
        require_once $path.'/autoload.inc.php';
        require_once $path.'/lib/Cpdf.php';
        // instantiate and use the dompdf class
        $this->_dompdf = new Dompdf\Dompdf();
        mb_internal_encoding('UTF-8');
    }
    /**
     * @return DomPdf instance
     */
    public function getDompdf()
    {
        return $this->_dompdf;
    }
    /**
     * Output pdf
     * @param $html the Html content
     * @param $filepath The pdf file to be saved (the absolute path)
     * @return type
     */
    public static function save($html,$filepath)
    {
        $s = new SDompdf();
        
        $s->dompdf->loadHtml($html);

        // (Optional) Setup the paper size and orientation
        $s->dompdf->setPaper('A4', 'potrait');//or landscape

        // Render the HTML as PDF
        $s->dompdf->render();
        
        $pdf = $s->dompdf->output();// gets the PDF as a string

        file_put_contents($filepath,$pdf);// save the pdf file on server
    }
  
}
