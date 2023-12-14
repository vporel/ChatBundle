<?php
namespace ChatBundle\Controller;

use ChatBundle\Entity\Chat;
use ChatBundle\Entity\ChatMessage;
use ChatBundle\Repository\ChatRepository;
use RootBundle\Controller\AbstractApiController;
use Doctrine\ORM\EntityManagerInterface;
use RootBundle\Service\NodeApp;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use UserAccountBundle\Repository\UserRepositoryInterface;

/**
 * @author Vivian NKOUANANG (https://github.com/vporel) <dev.vporel@gmail.com>
 */
class ChatApiController extends AbstractApiController{

    public function __construct(private ChatRepository $repository){}
    
    public function getChats(){
        $chats = $this->repository->findBy([["user1" => $this->getUser()], ["user2" => $this->getUser()]], "-updatedAt");
        $data = [];    //Chats with at least one message
        foreach($chats as $chat){
            if($chat->getMessages()->count() > 0) $data[] = $chat;
        }
        return $this->success($data);
    }

    public function createChat(Request $request, UserRepositoryInterface $userRepository){
        $receiverId = $request->request->getInt("receiverId");
        $receiver = $userRepository->find($receiverId);
        $user = $this->getUser();
        //Search existing chat
        $existingChat = $this->repository->findOneBy([
            ["user1" => $user, "user2" => $receiver],   //or
            ["user2" => $user, "user1" => $receiver]
        ]);
        if($existingChat) return $existingChat;
        $chat = new Chat();
        $chat->setUser1($user);
        $chat->setUser2($receiver);
        return $chat;
    }

    public function markChatMesagesSeen(EntityManagerInterface $em, Chat $chat): Chat{
        foreach($chat->getMessages() as $message){
            if($message->getSender() != $this->getUser() && !$message->isSeen()) $message->markSeen();
        }
        $em->flush();
        return $chat;
    }
}