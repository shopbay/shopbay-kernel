<?php
/**
 * This file is part of Shopbay.org (https://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */
use Tgallice\Wit\ActionMapping;
use Tgallice\Wit\Model\Context;
use Tgallice\Wit\Model\Step\Action;
use Tgallice\Wit\Model\Step\Merge;
use Tgallice\Wit\Model\Step\Message;
use Tgallice\Wit\Model\Step\Stop;
/**
 * Implementation of ActionMapping
 *
 * @author kwlok
 */
class WitActionMapping extends ActionMapping
{
    /**
     * @var WitBot 
     */
    protected $witbot;
    /**
     * Constructor.
     * @param WitBot $witbot The Wit bot 
     */
    public function __construct($witbot) 
    {
        if (!$witbot instanceof WitBot)
            throw new CException('Witbot not found!');
        
        $this->witbot = $witbot;
    }
    /**
     * @inheritdoc
     */
    public function action($sessionId, Context $context, Action $step)
    {        
        logInfo(__METHOD__, $step->getAction());

        if (!empty($step->getEntities())) {
            logInfo(__METHOD__.' Entities provided:', json_encode($step->getEntities(), JSON_PRETTY_PRINT));
        }

        if (method_exists($this->witbot, $step->getAction())){
            $context = $this->witbot->{$step->getAction()}($sessionId, $context, $step->getEntities());
        }
        
        return $context;
    }
    /**
     * @inheritdoc
     */
    public function say($sessionId, Context $context, Message $step)
    {
        logInfo(__METHOD__, $step->getMessage());
        $this->witbot->say($sessionId,$step->getMessage(),$context,$step->getEntities());
    }
    /**
     * @inheritdoc
     */
    public function error($sessionId, Context $context, $error = 'Unknown Error', array $stepData = [])
    {
        logError(__METHOD__.' '.json_encode($error),$stepData);
    }
    /**
     * @inheritdoc
     */
    public function merge($sessionId, Context $context, Merge $step)
    {
        logInfo(__METHOD__.' context with:', json_encode($step->getEntities(), JSON_PRETTY_PRINT));

        return $context;
    }
    /**
     * @inheritdoc
     */
    public function stop($sessionId, Context $context, Stop $step)
    {
        logError(__METHOD__, $sessionId);
        return;
    }
    
}
