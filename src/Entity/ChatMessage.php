<?php
namespace ChatBundle\Entity;

use RootBundle\Entity\Entity;
use Doctrine\ORM\Mapping as ORM;
use RootBundle\Entity\Trait\SentAtTrait;
use UserAccountBundle\Entity\User;
use Symfony\Component\Serializer\Annotation as Serializer;
use ApiPlatform\Metadata as Api;
use Symfony\Component\Validator\Constraints as Assert;
use UserAccountBundle\Entity\UserAuthorInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="chats_messages")
 * @author Vivian NKOUANANG (https://github.com/vporel) <dev.vporel@gmail.com>
 */
#[
    Api\ApiResource(shortName: "Chats/messages", security: "is_granted('ROLE_USER')"),
    Api\Post(
        denormalizationContext: ["groups" => ["chat_message:create"]],
        normalizationContext: ["groups" => ["default", "chat_message:read:collection", "user:read:simplified"]],
        openapiContext: [
            "summary" => "Envoyer un message", "description" => ""
        ]
    )
]
class ChatMessage extends Entity implements UserAuthorInterface{

    use SentAtTrait;

    /**
     * @var Chat
     * @ORM\ManyToOne(targetEntity="Chat", inversedBy="messages")
     * @ORM\JoinColumn(name="chat_id", referencedColumnName="id")
     */
    #[Serializer\Groups(["chat_message:create", "chat_message:read:with_chat"])]
    private $chat;

    /**
    * @var User
    * @ORM\ManyToOne(targetEntity="UserAccountBundle\Entity\UserInterface")
    * @ORM\JoinColumn(name="sender_id", referencedColumnName="id")
    */
    #[Serializer\Groups(["chat_message:read:collection"])]
    private $sender;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    #[Assert\NotBlank]
    #[Serializer\Groups(["chat_message:read:collection", "chat_message:create"])]
    private $content;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    #[Serializer\Groups(["chat_message:read:collection"])]
    private $seen = false;

    public function getSender(): User
    {
        return $this->sender;
    }

    public function setSender(User $sender): self
    {
        $this->sender = $sender;

        return $this;
    }

    #[Serializer\Groups(["chat_message:read:collection"])]
    public function getReceiver(): User
    {
        return $this->sender == $this->chat->getUser1() ? $this->chat->getUser2() : $this->chat->getUser1();
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getChat(): Chat
    {
        return $this->chat;
    }

    public function setChat(Chat $chat): self
    {
        $this->chat = $chat;

        return $this;
    }

    public function isSeen(): bool
    {
        return $this->seen;
    }

    public function markSeen(): self
    {
        $this->seen = true;
        return $this;
    }

    public function getAuthor(): User
    {
        return $this->sender;
    }

    public function setAuthor(User $user){
        $this->setSender($user);
    }

    public function prePersist(){
        parent::prePersist();
        
        $this->chat->touch();
    }
}