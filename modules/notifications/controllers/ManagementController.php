<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import("common.modules.inventories.models.LowInventoryDataProvider");
/**
 * Description of ManagementController
 *
 * @author kwlok
 */
class ManagementController extends AuthenticatedController 
{
    /**
    * Initializes the controller.
    */
    public function init()
    {
        parent::init();
        //load layout and common css/js files
        $this->module->registerScripts();
    }
    /**
     * Lists all models.
     */
    public function actionIndex()
    {
        $this->render('index');
    }    
    /**
     * Show notification template
     */
    public function actionTemplate($key)
    {
        $this->render('template',['key'=>$key]);
    }    
    /**
     * Get individual template view
     * @param type $name
     * @param type $key
     * @return type
     */
    protected function getTemplateView($name) 
    {
        $modelType = explode('.', $name);//expecting template name format: email/message.[model].name
        if (!isset($modelType[1]))//second element is model type
            throwError404(Sii::t('sii','The requested page does not exist'));
        
        switch (strtolower($modelType[1])) {
            case 'account':
                if (strtolower($modelType[2])=='resetpassword')
                    $params = ['name'=>'Demo User','password'=>'password'];
                elseif (strtolower($modelType[2])=='welcome'){
                    if (isset($modelType[3]) && strtolower($modelType[3])=='merchant'){
                        $name = $modelType[0].'.'.$modelType[1].'.'.$modelType[2];
                        $model = $this->loadModel(Account::GUEST, ucfirst($modelType[1]));
                        $params = ['model'=>$model];
                    }
                    //NOT IN USED
                    //Customer welcome email is applicable only when Shopbay.org is evolving into a market place (a shopping mall housing all shops in Shopbay.org)
                    //And the email will be sent to Shopbay.org shopping mall users
                    elseif (isset($modelType[3]) && strtolower($modelType[3])=='customer'){
                        $name = $modelType[0].'.'.$modelType[1].'.'.$modelType[2];
                        $model = $this->loadModel(Account::GUEST, ucfirst($modelType[1]));
                        $params = ['model'=>$model,'customerRole'=>true];
                    }
                }
                else
                    $params = array('name'=>'Demo User','activate_str'=>'1234567890');
                break;
            case 'shop':
                $model = $this->loadModel(Shop::DEMO_SHOP, ucfirst($modelType[1]));
                $params = ['model'=>$model];
                break;
            case 'order':
                $model = $this->loadModel(Order::DEMO_ORDER, ucfirst($modelType[1]));
                $params = ['model'=>$model];
                break;
            case 'shippingorder':
                $model = $this->loadModel(ShippingOrder::DEMO_SHIPPING_ORDER, ucfirst($modelType[1]));
                $params = ['model'=>$model];
                break;
            case 'item':
                $model = $this->loadModel(Item::DEMO_ITEM, ucfirst($modelType[1]));
                $params = ['model'=>$model];
                break;
            case 'question':
                $model = $this->loadModel(Question::DEMO_QUESTION, ucfirst($modelType[1]));
                $params = ['model'=>$model];
                break;
            case 'tutorial':
                $model = $this->loadModel(Tutorial::DEMO_TUTORIAL, ucfirst($modelType[1]));
                $params = ['model'=>$model];
                break;
            case 'ticket':
                $model = $this->loadModel(Ticket::DEMO_TICKET, ucfirst($modelType[1]));
                $params = ['model'=>$model];
                break;
            case 'inventory':
                $data = new LowInventoryDataProvider();
                $data->shop_id = -1;//this shop_id is needed for notification
                $data->shop_name = Sii::t('sii','Demo Shop');
                $data->locale = user()->getLocale();
                $data->items = [
                    [
                        'id' => '-1',
                        'sku' => '637346',
                        'product_name' => '{"zh_cn":"\u6253\u53d1\u6253\u53d1\u8eab\u4efd\u53ef","en_sg":"First Product"}',
                        'image_url' => '/files/images/default.jpg',
                        'quantity' => '3',
                        'available' => '0',
                    ],
                    [
                        'id' => '-1',
                        'sku' => 'SET329-cL-3B',
                        'product_name' => '{"zh_cn":"\u7b2c\u4e8c\u4ea7\u54c1","en_sg":"Second Product"}',
                        'image_url' => '/files/images/default.jpg',
                        'quantity' => '19',
                        'available' => '3',
                    ],
                ];
                $params = ['data'=>$data];
                break;
            default:
                throwError404(Sii::t('sii','The requested page does not exist'));
                break;
        }
        
        $viewFile = 'common.modules.notifications.templates.'.$name;
        if ($this->getViewFile($viewFile)==false)
            throwError404(Sii::t('sii','The requested page does not exist'));
        else
            return $this->renderPartial($viewFile,$params,true);
    }
    
    public function getTemplateType($key)
    {
        $type = explode('.', $key);
        return Sii::t('sii',ucfirst($type[0]));
    }
}
