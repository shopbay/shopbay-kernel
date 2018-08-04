<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Description of PaymentBaseController
 *
 * @author kwlok
 */
class PaymentBaseController extends AuthenticatedController 
{
    /**
     * Lists all models.
     */
    public function actionIndex()
    {
        $type = new $this->modelType;

        $dataProvider=new CActiveDataProvider(
                            $type->mine(),
                            array(
                                'criteria'=>array(
                                  'order'=>'create_time DESC',
                                 ),
                                'pagination'=>array('pageSize'=>Config::getSystemSetting('record_per_page')),
                            ));

        $this->render('index',array('dataProvider'=>$dataProvider));
    }

}