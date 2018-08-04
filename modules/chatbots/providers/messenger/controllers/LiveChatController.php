<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.controllers.PayloadController');
/**
 * LiveChatController relay message between customer and support agent
 *
 * @author kwlok
 */
class LiveChatController extends PayloadController
{
    /**
     * Process a payload 
     * @param ChatbotContext $context
     * @param LiveChatMetadata $metadata 
     */
    public function processMetaData(ChatbotContext $context,LiveChatMetadata $metadata)
    {
        logInfo(__METHOD__.' received metadata: '.$metadata->toString());
        switch ($metadata->status) {
            case LiveChatPayload::START:
                $metadata = $this->prepareSession($context,$metadata);
                if ($metadata->isClosed){
                    $this->renderView($context, new LiveChatPayload(LiveChatPayload::CLOSE,$this->getLiveChatWorkingTimetable($context)));
                }
                else {
                    //start customer live chat session 
                    Yii::app()->user->startLiveChat($context,$metadata);
                    $customerPayload = new LiveChatPayload(LiveChatPayload::START,['metadata'=>$metadata->toString()]);
                    $this->renderView($context, $customerPayload);
                    //start agent live chat session 
                    $metadata->sender =  $metadata->agent;//change agent as sender
                    $agentContext = new ChatbotContext($context->client, $context->app, $metadata->agent);
                    Yii::app()->user->startLiveChat($agentContext,$metadata);
                    $agentPayload = new LiveChatPayload(LiveChatPayload::START,['metadata'=>$metadata->toString()]);
                    $this->renderView($agentContext, $agentPayload);
                }
                break;
            case LiveChatPayload::RELAY:
                if ($metadata->messageType==MessengerPayload::TEXT && $metadata->text!=null){
                    $sessionMetadata = Yii::app()->user->getLiveChatMetadata($context);
                    $sessionMetadata->text = $metadata->text;
                    $payload = new LiveChatPayload(LiveChatPayload::RELAY,['metadata'=>$sessionMetadata->toString()]);
                    $this->renderView($context, $payload);
                }
                break;
            case LiveChatPayload::END:
                $sessionMetadata = Yii::app()->user->getLiveChatMetadata($context);
                if ($sessionMetadata instanceof LiveChatMetadata){
                    //end customer live chat session
                    Yii::app()->user->endLiveChat(new ChatbotContext($context->client, $context->app, $sessionMetadata->customer));
                    //end agent live chat session
                    Yii::app()->user->endLiveChat(new ChatbotContext($context->client, $context->app, $sessionMetadata->agent));
                    $payload = new LiveChatPayload(LiveChatPayload::END,['metadata'=>$sessionMetadata->toString()]);
                    $this->renderView($context, $payload);
                }
                break;
            default:
                logWarning(__METHOD__." unknown metadata",$metadata->toString());
                break;//do nothing 
        }
    }
    
    /**
     * Process a attachment payload 
     * @param ChatbotContext $context
     * @param LiveChatMetadata $metadata 
     */
    public function processAttachment(ChatbotContext $context,LiveChatMetadata $metadata)
    {
        logInfo(__METHOD__.' received attachment metadata: '.$metadata->toString());
        if ($metadata->isAttachment && $metadata->url!=null) {
            
            if ($metadata->fromAgent){
                $sender = $metadata->agentName;
                $recipient = $metadata->customer;
            }
            else {
                $sender= $metadata->customerName;
                $recipient = $metadata->agent;
            }            
            $sendMethod = 'send'.ucfirst($metadata->messageType);
            
            $this->sendTextMessage($recipient, Sii::t('sii','{sender} sends an attachment',['{sender}'=>$sender]));
            $this->{$sendMethod}($recipient, $metadata->url);
        }
    }

    protected function prepareSession(ChatbotContext $context,LiveChatMetadata $metadata)
    {
        if ($this->isLiveChatOpened($context)){
            //Assign an agent
            $agent = $this->findAgent($context);
            if ($agent!=null){
                $metadata->agent = $agent->id;
                $metadata->agentName = $agent->name;
                //Setup customer info
                $metadata->customer = $context->sender;
                $profile = $this->getUserProfile($context->sender);
                $metadata->customerName = ($profile!=null)?$profile->firstName:'guest';
                $metadata->sender = $context->sender;
                $metadata->appName = $context->chatbotOwner->name;
            }
            else {
                //no agent found; close live chat
                logInfo(__METHOD__.' No agent found; close live chat.');
                $metadata->status = LiveChatPayload::CLOSE;
            }
        }
        else {
            $metadata->status = LiveChatPayload::CLOSE;
        }
        
        return $metadata;
    }
    
    protected function isLiveChatOpened(ChatbotContext $context)
    {
        $chatbot = $context->chatbotOwner->model->messenger;
        if ($chatbot->isSupportEnabled){
            //check working days
            $day = date('w',time());
            
            if (ChatbotSupport::isWorkingDay($day, $chatbot->supportWorkingDays)){
                //within working days, check working hours
                return ChatbotSupport::isWorkingHour($chatbot->supportOpenTime, $chatbot->supportCloseTime);
            }
            else
                return false;//outside working days
        }
        else
            return false;
    }
    /**
     * Collect working timetable - suitable for display
     * @see LiveChatCloseView
     * 
     * @param ChatbotContext $context
     * @return type
     */
    protected function getLiveChatWorkingTimetable(ChatbotContext $context)
    {
        $workingDaysRef = ChatbotSupport::getWorkingDaysArray();
        $chatbot = $context->chatbotOwner->model->messenger;
        $date = new SDateTime(time());
        $currentDateFormat = $date->format('Y-m-d');//proxy to get open/close time format
        $openDate = SDateTime::createFromFormat('Y-m-d Hi', $currentDateFormat.' '.$chatbot->supportOpenTime);
        $closeDate = SDateTime::createFromFormat('Y-m-d Hi', $currentDateFormat.' '.$chatbot->supportCloseTime);
        $offDays = [];
        if ($chatbot->supportWorkingDays!=null){
            foreach ($chatbot->supportWorkingDays as $day => $opened) {
                if (!$opened){
                    $offDays[] = $workingDaysRef[$day];//take it display text
                }
            }
            return [
                'open_time'=>$openDate->format('g:ia'),//e.g. 9:15am
                'close_time'=>$closeDate->format('g:ia'),//e.g. 6:30pm
                'off_days'=>  implode(', ', $offDays),
            ];
        }
        else
            return [];
    }
    /**
     * Find agent to assign the support duty
     * 
     * @todo for now supports only one agent, to have support team and on job rotation
     * 
     * @param ChatbotContext $context
     * @return \LiveChatAgent
     */
    protected function findAgent(ChatbotContext $context)
    {
        $agentId = $context->chatbotOwner->model->messenger->supportAgentId;
        $agentName = $context->chatbotOwner->model->messenger->supportAgentName;
        if ($agentId==null || $agentName==null)
            return null;

        return new LiveChatAgent($agentId, $agentName);
    }
    
    protected function renderView($context,$payload)
    {
        $view = $this->getPayloadView($payload->type);
        $viewObj = new $view($this->token);
        $viewObj->setContext($context);
        $viewObj->render($payload);
    }
    
}
