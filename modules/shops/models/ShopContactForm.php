<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
/**
 * ShopContactForm class.
 * ContactForm is the data structure for keeping contact form data. 
 *
 * @author kwlok
 */
class ShopContactForm extends ContactForm
{
    protected $model;
    protected $page = ShopPage::CONTACT;
    protected $route = '/contactpost';
    /**
     * Constructor.
     */
    public function __construct($model,$scenario='')
    {
        $this->model = $model;
        parent::__construct($scenario);
    }    
    /**
     * Declares the validation rules.
     */
    public function rules()
    {
        return [
            ['name, email, body', 'required'],
            ['name, email', 'length', 'max'=>100],
            // email has to be a valid email address
            ['email', 'email'],
            // body needs to be purified
            ['body', 'length', 'max'=>1000],
            ['body','rulePurify'],
        ];
    }

    public function displayName() 
    {
        return Sii::t('sii','Contact us');
    }
    /**
     * Declares customized attribute labels.
     */
    public function attributeLabels()
    {
        return [
            'name'=>Sii::t('sii','Name'),
            'email'=>Sii::t('sii','Email'),
            'body'=>Sii::t('sii','Message'),
        ];
    }
    /**
     * This is used for field placeholder
     */
    public function attributeToolTips()
    {
        return [
            'name'=>Sii::t('sii','Name'),
            'email'=>Sii::t('sii','Email'),
            'body'=>Sii::t('sii','Enter your message here. If you have questions for product, please leave us product name. Thanks!'),
        ];
    }
    /**
     * Check no error and under scenario 'post'
     * @see ContactPostAction for scenario setting
     */
    public function successMessage()
    {
        if ($this->getScenario()=='POST' && !$this->hasErrors())
            echo CHtml::tag('div',['class'=>'success'],Sii::t('sii','Thanks for contacting us! Your message is important to us and we will respond to you shortly.'));
    }
    
    public function errorMessage()
    {
        echo CHtml::errorSummary($this);
    }
    
    public function value($field)
    {
        echo $this->$field; 
    }
    
    public function label($field)
    {
        echo $this->getAttributeLabel($field); 
    }

    public function placeholder($field)
    {
        echo $this->getToolTip($field); 
    }
    
    public function renderPartial($controller)
    {
        return $controller->renderPartial($controller->getThemeView('_contact'),['form'=>$this],true);
    }        
    
    public function render($controller)
    {
        $content = $this->renderPartial($controller).$this->getCSRFForm();//attach csrf form
        Helper::registerJs($this->getButtonScript());
        return CHtml::tag('div',['class'=>'form '.$this->page,'data-url'=>$this->route],$content);
    }        
    /**
     * Generate dummy form to produce csrf token 
     * Dummy url = url(csrf), but form name is fixed to 'csrf_form' for query used.
     */
    protected function getCSRFForm()
    {
        $html = CHtml::openTag('div',['style'=>'display:none;']); 
        $html.= CHtml::form(url('csrf'),'post',['id'=>'csrf_form']); 
        $html.= CHtml::endForm(); 
        $html.= CHtml::closeTag('div'); 
        return $html;
    }    

    public function getButtonScript() 
    {
        $script = <<<EOJS
$('.form #contact_form_send').click(function(){contactus('$this->page');});
EOJS;
        return $script;
    }
    
    //BELOW IS USED BY NOTIFICATION MANAGER
    public function getMailAddressTo()
    {
        return $this->model->email;
    }
    
    public function getMailAddressName()
    {
        return $this->model->localeName(user()->getLocale());
    }
    
    public function getMailSubject()
    {
        return Sii::t('sii','You have feedback sent by customer: {customer}',array('{customer}'=>$this->name));
    }
    
    public function getMailBody()
    {
        $body = '<p>'.Sii::t('sii','From').': '.$this->name.' <em>'.$this->email.'</em></p>';
        $body .= '<p>'.Sii::t('sii','Message').': '.$this->body.'</p>';
        return $body;
    }
        
}