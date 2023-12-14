<?php
namespace ChatBundle\EntityListener;

use ChatBundle\Entity\ChatMessage;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use RootBundle\Service\NodeApp;
use Symfony\Bundle\SecurityBundle\Security;

#[AsEntityListener(event: Events::prePersist, entity: ChatMessage::class)]
#[AsEntityListener(event: Events::postPersist, entity: ChatMessage::class)]
class ChatMessageListener{

    public function __construct(private NodeApp $nodeApp, private Security $security){}

    public function prePersist(ChatMessage $chatMessage){
        $chatMessage->setSender($this->security->getUser());
    } 

    public function postPersist(ChatMessage $chatMessage){
        
        $this->nodeApp->sendChatMessage($chatMessage);    //Send the message with node to the receiver
    }    
}