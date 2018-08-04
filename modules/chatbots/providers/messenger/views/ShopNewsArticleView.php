<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
Yii::import('common.modules.chatbots.providers.messenger.views.MessengerView');
/**
 * Description of ShopNewsArticleView
 * 
 * @author kwlok
 */
class ShopNewsArticleView extends MessengerView
{
    protected $totalPages;
    protected static $sentencePerPage = 3;
    /**
     * Render view (send to messenger)
     * @param ChatbotPayload $payload 
     */
    public function render($payload) 
    {
        $news = new ChatbotNews($payload->params['news_id']);
        $heading = $news->name."\n".$news->creationTime;

        $currentPage = $this->getCurrentPage($payload);
        $currentPageContent = $this->getCurrentPageContent($news, $currentPage);
        if ($currentPage==0)
            $currentPageContent = $heading."\n\n".$currentPageContent;
        
        if ($this->hasNextPage($news, $currentPage)){
            $payload->params['current_page'] = $currentPage + 1;//for next page
            $buttons[] = new PostbackButton(Sii::t('sii','Next Page'), $payload->toString());
            $this->sendButtonTemplate($this->context->sender,$currentPageContent,$buttons);
        }
        else
            $this->sendTextMessage($this->context->sender, $currentPageContent);
        
        if ($currentPage==0){
            $this->sendImage($this->context->sender, $news->imageUrl);
        } 
        
        if ($this->getIsLastPage($news, $currentPage)){
            $this->sendTextMessage($this->context->sender, Sii::t('sii','You are at the end of the news article.'));
        }
        
    }

    protected function hasNextPage($news,$currentPage)
    {
        return $this->getTotalPages($news) > ($currentPage + 1);
    }
    
    protected function getIsLastPage($news,$currentPage)
    {
        return $this->getTotalPages($news) == ($currentPage + 1);
    }
    
    protected function getCurrentPageContent($news,$currentPage)
    {
        $sentences = explode('.', $news->content);
        $pageIndex = $currentPage * self::$sentencePerPage;
        $content = $sentences[$pageIndex].'.';//put back the full stop.
        for ($i=1;$i<self::$sentencePerPage;$i++){
            if (isset($sentences[$pageIndex+$i]))
                $content .= $sentences[$pageIndex+$i].'.';
        }
        
        return $content;
    }
    
    protected function getTotalPages($news)
    {
        if (!isset($this->totalPages)){
            $sentences = explode('.', $news->content);//extract by puntuation '.'
            $this->totalPages = ceil(count($sentences)/self::$sentencePerPage);
        }
        return $this->totalPages;
    }
}
