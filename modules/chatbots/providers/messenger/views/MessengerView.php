<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.MessengerTrait');
Yii::import('common.modules.chatbots.payloads.*');
/**
 * Description of MessengerView
 *
 * @author kwlok
 */
abstract class MessengerView extends CComponent
{
    use MessengerTrait;
    /**
     * The chatbot context
     * @var ChatbotContext 
     */
    protected $context;
    /**
     * Set the session
     * @param ChatbotContext $context
     */
    public function setContext($context)
    {
        $this->context = $context;
    }
    /**
     * Render view (send to messenger)
     * @param ChatbotPayload $payload 
     */
    abstract public function render($payload);
    /**
     * Get search summary text
     * @param type $payload
     * @param type $searchResult
     * @param type $currentPage
     */
    protected function renderSummaryText($payload,$searchResult,$currentPage=0)
    {
        $buttons = [];
        $summaryText = Sii::t('sii','Here we show you {start} - {end} of {count} records.',['{start}'=>$searchResult->getPageStartPosition($currentPage),'{end}'=>$searchResult->getPageEndPosition($currentPage),'{count}'=>$searchResult->totalItemCount]);
        if ($searchResult->hasMorePages($currentPage)){
            $payload->params['current_page'] = $currentPage + 1;//for next page
            $buttons[] = new PostbackButton(Sii::t('sii','Next Page'), $payload->toString());
            $this->sendButtonTemplate($this->context->sender,$summaryText,$buttons);
        }
//        else
//          $this->sendTextMessage($payload->sender, $summaryText);
    }   
    /**
     * Get the current page
     * @param type $payload
     * @return type
     */
    protected function getCurrentPage($payload)
    {
        $currentPage = 0;
        if (isset($payload->params['current_page']))
            $currentPage = $payload->params['current_page'];
        return $currentPage;
    }
    /**
     * A boilerplate method to get search result
     * @param type $model
     * @param type $searchModelMethod
     * @param type $searchParams
     * @param type $pageSize
     * @return type
     */
    protected function getSearchResult($model,$searchModelMethod,$searchParams=[],$pageSize=null)
    {
        if (!isset($pageSize))
            $pageSize = GenericTemplate::$bubblesLimit;
        
        return call_user_func_array([$model, $searchModelMethod], array_merge($searchParams,[$pageSize]));
    }
    /**
     * Render search result
     * @param type $searchResult
     * @param type $payload
     * @return type
     */
    protected function renderBubbles($searchResult,$payload)
    {
        $bubbles = [];//maximum 10 (but auto trimmed when limit exceeds)
        foreach ($searchResult->data as $product) {
            $bubbles[] = $this->renderBubble($payload, $product);
        }
        return $bubbles;
    }
    /**
     * Get bubble
     * @param type $payload
     * @param type $product
     * @return \PostbackButton
     */
    protected function renderBubble($payload,$product)
    {
        throw new CException('Please implement this method as child class');
    }
    /**
     * Return chatbot model according to chatbot owner type
     * @return MessengerModel 
     */
    public function getMessengerModel() 
    {
        return $this->context->getChatbotOwner('Messenger');
    }
}
