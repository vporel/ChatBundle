<?php
namespace ChatBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use RootBundle\Entity\Entity;
use Doctrine\ORM\Mapping as ORM;
use UserAccountBundle\Entity\User;
use Symfony\Component\Serializer\Annotation as Serializer;
use RootBundle\Entity\Trait\TimestampsTrait;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use ApiPlatform\Metadata as Api;
use ChatBundle\Controller\ChatApiController;

/**
 * Chat between two users
 * @ORM\Entity
 * @ORM\Table(name="chats")
 * @author Vivian NKOUANANG (https://github.com/vporel) <dev.vporel@gmail.com>
 */
#[UniqueEntity(["user1", "user2"])]
#[
    Api\ApiResource(security: "is_granted('ROLE_USER')", normalizationContext: ["groups" => ["default", "chat:read:collection", "user:read:simplified", "chat_message:read:collection"]]),
    Api\Get(requirements: ["id" => "\d+"], security: "is_granted('ROLE_USER')"),
    Api\Post(
        controller: ChatApiController::class."::createChat",
        openapiContext: [
            "summary" => "Ouvrir un chat avec un autre utilisateur",
            "requestBody" => ["content" => ["application/json" => ["schema" => ["type" => "object", "properties" => [
                "receiverId" => ["type" => "integer", "required" => true, "description" => "Id de l'autre utilisateur"]
            ]]]]]
        ]
    ),
    Api\Post(
        uriTemplate: "/chats/{id}/mark-messages-seen",
        controller: ChatApiController::class."::markChatMesagesSeen",
        write: false,   //Handled by the controller
        openapiContext: [
            "summary" => "Marquer tous les messages d'un chat comme ayant été lus",
            "requestBody" => ["content" => ["application/json" => []]],
            "responses" => ["200" => ["content" => ["application/json" => []]]]
        ]
    ),
    Api\GetCollection(
        controller: ChatApiController::class."::getChats",
        read: false
    )
]
class Chat extends Entity{
    use TimestampsTrait;
    
    /**
    * @var User
    * @ORM\ManyToOne(targetEntity="UserAccountBundle\Entity\UserInterface")
    * @ORM\JoinColumn(name="user1_id", referencedColumnName="id")
    */
    #[Serializer\Groups(["chat:read:collection"])]
    private $user1;

    /**
    * @var User
    * @ORM\ManyToOne(targetEntity="UserAccountBundle\Entity\UserInterface")
    * @ORM\JoinColumn(name="user2_id", referencedColumnName="id")
    */
    #[Serializer\Groups(["chat:read:collection"])]
    private $user2;

    /**
     * @var Collection<ChatMessage>
     * @ORM\OneToMany(targetEntity="ChatMessage", mappedBy="chat", cascade={"persist", "remove"})
     */
    #[Serializer\Groups(["chat:read:collection"])]
    private $messages;

    public function __construct(){
        $this->messages = new ArrayCollection();
    }

    public function getUser2(): User
    {
        return $this->user2;
    }

    public function setUser2(User $user2): self
    {
        $this->user2 = $user2;

        return $this;
    }

    public function getUser1(): User
    {
        return $this->user1;
    }

    public function setUser1(User $user1): self
    {
        $this->user1 = $user1;
        return $this;
    }

    /**
     * @return  Collection<ChatMessage>
     */ 
    public function getMessages()
    {
        return $this->messages;
    }
}