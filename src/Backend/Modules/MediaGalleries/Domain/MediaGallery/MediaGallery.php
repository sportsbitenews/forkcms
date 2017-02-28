<?php

namespace Backend\Modules\MediaGalleries\Domain\MediaGallery;

use Ramsey\Uuid\Uuid;
use Doctrine\ORM\Mapping as ORM;
use Backend\Core\Engine\Model;
use Backend\Modules\MediaLibrary\Domain\MediaGroup\MediaGroup;
use Common\ModuleExtraType;

/**
 * MediaGallery
 *
 * @ORM\Entity(repositoryClass="Backend\Modules\MediaGalleries\Domain\MediaGallery\MediaGalleryRepository")
 * @ORM\HasLifecycleCallbacks
 */
class MediaGallery
{
    /**
     * @var string
     *
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     */
    protected $userId;

    /**
     * @var integer
     *
     * ToDo: when Fork CMS has ModuleExtra entity in core, use its Entity as type
     *
     * @ORM\Column(type="integer")
     */
    protected $moduleExtraId;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $action;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $title;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $text;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    protected $createdOn;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    protected $editedOn;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    protected $publishOn;

    /**
     * @var Status
     *
     * @ORM\Column(type="media_gallery_status")
     */
    protected $status;

    /**
     * @var MediaGroup
     *
     * @ORM\OneToOne(
     *      targetEntity="Backend\Modules\MediaLibrary\Domain\MediaGroup\MediaGroup",
     *      cascade="persist",
     *      orphanRemoval=true
     * )
     * @ORM\JoinColumn(
     *      name="mediaGroupId",
     *      referencedColumnName="id",
     *      onDelete="cascade"
     * )
     */
    protected $group;

    /**
     * MediaGallery constructor.
     *
     * @param string $title
     * @param string $text
     * @param string $action
     * @param int $userId
     * @param int $moduleExtraId
     * @param \DateTime $publishOn
     * @param MediaGroup $group
     * @param Status $status
     */
    private function __construct(
        string $title,
        string $text,
        string $action,
        int $userId,
        int $moduleExtraId,
        \DateTime $publishOn,
        MediaGroup $group,
        Status $status
    ) {
        $this->userId = $userId;
        $this->moduleExtraId = $moduleExtraId;
        $this->action = $action;
        $this->title = $title;
        $this->text = $text;
        $this->publishOn = $publishOn;
        $this->group = $group;
        $this->status = $status;
    }

    /**
     * @param string $title
     * @param string $text
     * @param string $action
     * @param int $userId
     * @param \DateTime $publishOn
     * @param MediaGroup $group
     * @param Status $status
     * @return MediaGallery
     */
    public static function create(
        string $title,
        string $text,
        string $action,
        int $userId,
        \DateTime $publishOn,
        MediaGroup $group,
        Status $status
    ) : MediaGallery{
        return new self(
            $title,
            $text,
            $action,
            $userId,
            0,
            $publishOn,
            $group,
            $status
        );
    }

    /**
     * @param string $action
     * @param string $title
     * @param string|null $text
     * @param \DateTime $publishOn
     * @param MediaGroup $group
     * @param Status $status
     * @return MediaGallery
     */
    public function update(
        string $action,
        string $title,
        string $text = null,
        \DateTime $publishOn,
        MediaGroup $group,
        Status $status
    ) : MediaGallery{
        $this->action = $action;
        $this->title = $title;
        $this->text = $text;
        $this->publishOn = $publishOn;
        $this->group = $group;
        $this->status = $status;

        return $this;
    }

    /**
     * To array
     *
     * @return array
     */
    public function __toArray(): array
    {
        return [
            'id' => $this->id,
            'userId' => $this->userId,
            'moduleExtraId' => $this->moduleExtraId,
            'action' => $this->action,
            'title' => $this->title,
            'text' => $this->text,
            'createdOn' => $this->createdOn->getTimestamp(),
            'editedOn' => $this->editedOn->getTimestamp(),
            'publishOn' => $this->publishOn->getTimestamp(),
            'status' => (string) $this->status,
            'group' => $this->group->__toArray(),
        ];
    }

    /**
     * Gets the value of id.
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Gets the value of userId.
     *
     * @return integer
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * Gets the value of moduleExtraId.
     *
     * @return integer
     */
    public function getModuleExtraId(): int
    {
        return $this->moduleExtraId;
    }

    /**
     * Gets the Action
     *
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * Gets the value of title.
     *
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Gets the value of text.
     *
     * @return string|null
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Gets the value of createdOn.
     *
     * @return \DateTime
     */
    public function getCreatedOn(): \DateTime
    {
        return $this->createdOn;
    }

    /**
     * Gets the value of editedOn.
     *
     * @return \DateTime
     */
    public function getEditedOn(): \DateTime
    {
        return $this->editedOn;
    }

    /**
     * Gets the value of publishOn.
     *
     * @return \DateTime
     */
    public function getPublishOn(): \DateTime
    {
        return $this->publishOn;
    }

    /**
     * @return Status
     */
    public function getStatus(): \Backend\Modules\MediaGalleries\Domain\MediaGallery\Status
    {
        return $this->status;
    }

    /**
     * Gets the value of group.
     *
     * @return MediaGroup
     */
    public function getGroup(): MediaGroup
    {
        return $this->group;
    }

    /**
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->createdOn = $this->editedOn = new \Datetime();

        $this->moduleExtraId = Model::insertExtra(
            ModuleExtraType::widget(),
            'MediaGalleries',
            'Gallery',
            'Gallery',
            array(),
            false
        );
    }

    /**
     * @ORM\PreUpdate
     */
    public function onPreUpdate()
    {
        $this->editedOn = new \Datetime();
    }

    /**
     * @ORM\PostPersist
     */
    public function onPostPersist()
    {
        $this->updateModuleExtraData();
    }

    /**
     * @ORM\PostUpdate
     */
    public function onPostUpdate()
    {
        $this->updateModuleExtraData();
    }

    /**
     * @ORM\PostRemove
     */
    public function onPostRemove()
    {
        Model::deleteExtraById(
            $this->moduleExtraId,
            true
        );
    }

    /**
     * Get extra label
     *
     * @return string The gallery extra_label.
     */
    protected function getExtraLabel(): string
    {
        return '"' . $this->title . '"'
            . ' - '
            . ucfirst($this->action);
    }

    /**
     * Update module extra data
     */
    protected function updateModuleExtraData()
    {
        // Update ModuleExtra data
        Model::updateExtra(
            $this->getModuleExtraId(),
            'data',
            array(
                'gallery_id' => $this->id,
                'extra_label' => $this->getExtraLabel(),
                'edit_url' =>
                    Model::createURLForAction('Edit', 'MediaGalleries')
                    . '&id=' . $this->id,
            )
        );

        // Update hidden
        Model::updateExtra(
            $this->moduleExtraId,
            'hidden',
            'N'
        );
    }

    /**
     * Is visible only returns true if the "status" is "active" and the "publishOn" is in the past.
     *
     * @return bool
     */
    public function isVisible(): bool
    {
        $now = new \DateTime();

        return ($this->status->isActive() && $this->publishOn->getTimestamp() < $now->getTimestamp());
    }

    /**
     * @param MediaGalleryDataTransferObject $mediaGalleryDataTransferObject
     * @return MediaGallery
     */
    public static function fromDataTransferObject(MediaGalleryDataTransferObject $mediaGalleryDataTransferObject): MediaGallery
    {
        if ($mediaGalleryDataTransferObject->hasExistingMediaGallery()) {
            /** @var MediaGallery $mediaGallery */
            $mediaGallery = $mediaGalleryDataTransferObject->getMediaGalleryEntity();

            $mediaGallery->update(
                $mediaGalleryDataTransferObject->action,
                $mediaGalleryDataTransferObject->title,
                $mediaGalleryDataTransferObject->text,
                $mediaGalleryDataTransferObject->publishOn,
                $mediaGalleryDataTransferObject->mediaGroup,
                Status::fromString($mediaGalleryDataTransferObject->status)
            );

            return $mediaGallery;
        }

        /** @var MediaGallery $mediaGallery */
        $mediaGallery = MediaGallery::create(
            $mediaGalleryDataTransferObject->title,
            $mediaGalleryDataTransferObject->text,
            $mediaGalleryDataTransferObject->action,
            $mediaGalleryDataTransferObject->userId,
            $mediaGalleryDataTransferObject->publishOn,
            $mediaGalleryDataTransferObject->mediaGroup,
            Status::fromString($mediaGalleryDataTransferObject->status)
        );

        return $mediaGallery;
    }
}
