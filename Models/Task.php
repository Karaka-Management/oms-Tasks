<?php
/**
 * Jingga
 *
 * PHP Version 8.1
 *
 * @package   Modules\Tasks\Models
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Tasks\Models;

use Modules\Admin\Models\Account;
use Modules\Admin\Models\NullAccount;
use Modules\Calendar\Models\Schedule;
use Modules\Media\Models\Media;
use Modules\Tag\Models\NullTag;
use Modules\Tag\Models\Tag;
use phpOMS\Stdlib\Base\Exception\InvalidEnumValue;

/**
 * Task class.
 *
 * @package Modules\Tasks\Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
class Task implements \JsonSerializable
{
    /**
     * ID.
     *
     * @var int
     * @since 1.0.0
     */
    public int $id = 0;

    /**
     * Title.
     *
     * @var string
     * @since 1.0.0
     */
    public string $title = '';

    /**
     * Redirect.
     *
     * Used as reference or for redirection when opened.
     * This allows to open the task on a different page with a different layout if needed (e.g. ticket system, workflow, checklist, ...)
     *
     * @var string
     * @since 1.0.0
     */
    public string $redirect = '';

    /**
     * Trigger
     *
     * Event trigger to execute on task change.
     *
     * @var string
     * @since 1.0.0
     */
    public string $trigger = '';

    /**
     * Creator.
     *
     * @var Account
     * @since 1.0.0
     */
    public Account $createdBy;

    /**
     * Created.
     *
     * @var \DateTimeImmutable
     * @since 1.0.0
     */
    public \DateTimeImmutable $createdAt;

    /**
     * Description.
     *
     * @var string
     * @since 1.0.0
     */
    public string $description = '';

    /**
     * Description raw.
     *
     * @var string
     * @since 1.0.0
     */
    public string $descriptionRaw = '';

    /**
     * Type.
     *
     * @var int
     * @since 1.0.0
     */
    public int $type = TaskType::SINGLE;

    /**
     * Status.
     *
     * @var int
     * @since 1.0.0
     */
    public int $status = TaskStatus::OPEN;

    /**
     * Completion status
     * @var int
     * @since 1.0.0
     */
    public int $completion = -1;

    /**
     * Attributes.
     *
     * @var TaskAttribute[]
     * @since 1.0.0
     */
    private array $attributes = [];

    /**
     * Task can be closed by user.
     *
     * Setting it to false will only allow other modules to close this task
     *
     * @var bool
     * @since 1.0.0
     */
    public bool $isClosable = true;

    /**
     * Task can be edited by user.
     *
     * Setting it to false will only allow other modules to close this task
     *
     * @var bool
     * @since 1.0.0
     */
    public bool $isEditable = true;

    /**
     * Start.
     *
     * @var null|\DateTime
     * @since 1.0.0
     */
    public ?\DateTime $start = null;

    /**
     * Due.
     *
     * @var null|\DateTime
     * @since 1.0.0
     */
    public ?\DateTime $due = null;

    /**
     * Done.
     *
     * @var null|\DateTime
     * @since 1.0.0
     */
    public ?\DateTime $done = null;

    /**
     * Task elements.
     *
     * @var TaskElement[]
     * @since 1.0.0
     */
    protected array $taskElements = [];

    /**
     * Tags.
     *
     * @var Tag[]
     * @since 1.0.0
     */
    protected array $tags = [];

    /**
     * Schedule
     *
     * @var Schedule
     * @since 1.0.0
     */
    public Schedule $schedule;

    /**
     * Priority
     *
     * @var int
     * @since 1.0.0
     */
    public int $priority = TaskPriority::NONE;

    /**
     * Media files
     *
     * @var array
     * @since 1.0.0
     */
    protected array $media = [];

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->createdBy = new NullAccount();
        $this->createdAt = new \DateTimeImmutable('now');
        $this->schedule  = new Schedule();
        $this->start     = new \DateTime('now');
        $this->due       = new \DateTime('now');
        $this->due->modify('+1 day');
    }

    /**
     * Adding new task element.
     *
     * @param TaskElement $element Task element
     *
     * @return int
     *
     * @since 1.0.0
     */
    public function addElement(TaskElement $element) : int
    {
        $this->taskElements[] = $element;

        \end($this->taskElements);
        $key = (int) \key($this->taskElements);
        \reset($this->taskElements);

        return $key;
    }

    /**
     * Adding new tag.
     *
     * @param Tag $tag Tag
     *
     * @return int
     *
     * @since 1.0.0
     */
    public function addTag(Tag $tag) : int
    {
        $this->tags[] = $tag;

        \end($this->tags);
        $key = (int) \key($this->tags);
        \reset($this->tags);

        return $key;
    }

    /**
     * Get all media
     *
     * @return Media[]
     *
     * @since 1.0.0
     */
    public function getMedia() : array
    {
        return $this->media;
    }

    /**
     * Add media
     *
     * @param Media $media Media to add
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function addMedia(Media $media) : void
    {
        $this->media[] = $media;
    }

    /**
     * Check if user is in to
     *
     * @param int $id User id
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function isToAccount(int $id) : bool
    {
        foreach ($this->taskElements as $element) {
            if ($element->isToAccount($id)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if group is in to
     *
     * @param int $id Group id
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function isToGroup(int $id) : bool
    {
        foreach ($this->taskElements as $element) {
            if ($element->isToGroup($id)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user is in cc
     *
     * @param int $id User id
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function isCCAccount(int $id) : bool
    {
        foreach ($this->taskElements as $element) {
            if ($element->isCCAccount($id)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if group is in cc
     *
     * @param int $id Group id
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function isCCGroup(int $id) : bool
    {
        foreach ($this->taskElements as $element) {
            if ($element->isCCGroup($id)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get created by
     *
     * @return Account
     *
     * @since 1.0.0
     */
    public function getCreatedBy() : Account
    {
        return $this->createdBy;
    }

    /**
     * Set created by
     *
     * @param Account $account Created by
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function setCreatedBy(Account $account) : void
    {
        $this->createdBy           = $account;
        $this->schedule->createdBy = $account;
    }

    /**
     * Get status
     *
     * @return int
     *
     * @since 1.0.0
     */
    public function getStatus() : int
    {
        return $this->status;
    }

    /**
     * Set status
     *
     * @param int $status Task status
     *
     * @return void
     *
     * @throws InvalidEnumValue
     *
     * @since 1.0.0
     */
    public function setStatus(int $status) : void
    {
        if (!TaskStatus::isValidValue($status)) {
            throw new InvalidEnumValue((string) $status);
        }

        $this->status = $status;
    }

    /**
     * Get priority
     *
     * @return int
     *
     * @since 1.0.0
     */
    public function getPriority() : int
    {
        return $this->priority;
    }

    /**
     * Set priority
     *
     * @param int $priority Task priority
     *
     * @return void
     *
     * @throws InvalidEnumValue
     *
     * @since 1.0.0
     */
    public function setPriority(int $priority) : void
    {
        if (!TaskPriority::isValidValue($priority)) {
            throw new InvalidEnumValue((string) $priority);
        }

        $this->priority = $priority;
    }

    /**
     * Remove Element from list.
     *
     * @param int $id Task element
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function removeElement($id) : bool
    {
        if (isset($this->taskElements[$id])) {
            unset($this->taskElements[$id]);

            return true;
        }

        return false;
    }

    /**
     * Remove Tag from list.
     *
     * @param int $id Tag
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function removeTag($id) : bool
    {
        if (isset($this->tags[$id])) {
            unset($this->tags[$id]);

            return true;
        }

        return false;
    }

    /**
     * Get task elements.
     *
     * @return Tag[]
     *
     * @since 1.0.0
     */
    public function getTags() : array
    {
        return $this->tags;
    }

    /**
     * Get task elements.
     *
     * @param int $id Element id
     *
     * @return Tag
     *
     * @since 1.0.0
     */
    public function getTag(int $id) : Tag
    {
        return $this->tags[$id] ?? new NullTag();
    }

    /**
     * Get task elements.
     *
     * @return TaskElement[]
     *
     * @since 1.0.0
     */
    public function getTaskElements() : array
    {
        return $this->taskElements;
    }

    /**
     * Get task elements in inverted order.
     *
     * @return TaskElement[]
     *
     * @since 1.0.0
     */
    public function invertTaskElements() : array
    {
        return \array_reverse($this->taskElements);
    }

    /**
     * Get task elements.
     *
     * @param int $id Element id
     *
     * @return TaskElement
     *
     * @since 1.0.0
     */
    public function getTaskElement(int $id) : TaskElement
    {
        return $this->taskElements[$id] ?? new NullTaskElement();
    }

    /**
     * Get task type.
     *
     * @return int
     *
     * @since 1.0.0
     */
    public function getType() : int
    {
        return $this->type;
    }

    /**
     * Set task type.
     *
     * @param int $type Task type
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function setType(int $type = TaskType::SINGLE) : void
    {
        $this->type = $type;
    }

    /**
     * Add attribute to item
     *
     * @param TaskAttribute $attribute Note
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function addAttribute(TaskAttribute $attribute) : void
    {
        $this->attributes[] = $attribute;
    }

    /**
     * Get attributes
     *
     * @return TaskAttribute[]
     *
     * @since 1.0.0
     */
    public function getAttributes() : array
    {
        return $this->attributes;
    }

    /**
     * Has attribute value
     *
     * @param string $attrName  Attribute name
     * @param mixed  $attrValue Attribute value
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function hasAttributeValue(string $attrName, mixed $attrValue) : bool
    {
        foreach ($this->attributes as $attribute) {
            if ($attribute->type->name === $attrName && $attribute->value->getValue() === $attrValue) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get attribute
     *
     * @param string $attrName Attribute name
     *
     * @return null|TaskAttributeValue
     *
     * @since 1.0.0
     */
    public function getAttribute(string $attrName) : ?TaskAttributeValue
    {
        foreach ($this->attributes as $attribute) {
            if ($attribute->type->name === $attrName) {
                return $attribute->value;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray() : array
    {
        return [
            'id'             => $this->id,
            'createdBy'      => $this->createdBy,
            'createdAt'      => $this->createdAt,
            'title'          => $this->title,
            'description'    => $this->description,
            'descriptionRaw' => $this->descriptionRaw,
            'status'         => $this->status,
            'type'           => $this->type,
            'priority'       => $this->priority,
            'due'            => $this->due,
            'done'           => $this->done,
            'tags'           => $this->tags,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize() : mixed
    {
        return $this->toArray();
    }
}
