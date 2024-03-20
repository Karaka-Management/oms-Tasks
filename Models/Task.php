<?php
/**
 * Jingga
 *
 * PHP Version 8.2
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
use Modules\Tag\Models\Tag;

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
    public array $taskElements = [];

    /**
     * Tags.
     *
     * @var Tag[]
     * @since 1.0.0
     */
    public array $tags = [];

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
     * Account this ticket is for
     *
     * This is not the person who is working on the ticket but the person who needs help.
     * This can be different from the person who created it.
     *
     * @var null|Account
     * @since 1.0.0
     */
    public ?Account $for = null;

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
     * Get accounts that are responsible for this task
     *
     * @return Account[]
     *
     * @since 1.0.0
     */
    public function getResponsible() : array
    {
        $responsible = [];
        foreach ($this->taskElements as $element) {
            if (empty($element->accRelation)) {
                continue;
            }

            $first = true;

            foreach ($element->accRelation as $accRel) {
                if ($accRel->duty === DutyType::TO) {
                    if ($first) {
                        $responsible = [];
                    }

                    $responsible[] = $accRel->relation;
                    $first         = false;
                }
            }
        }

        return $responsible;
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

    /**
     * Create a new task from a task template.
     *
     * A task template is a normal task with the type of TEMPLATE.
     *
     * @param self $task Task to "clone"
     *
     * @return self
     *
     * @since 1.0.0
     */
    public static function fromTemplate(self $task) : self
    {
        $now = new \DateTimeImmutable('now');

        $task->id   = 0;
        $task->type = TaskType::SINGLE;

        if ($task->due !== null) {
            $task->due->setTimestamp(
                $task->due->getTimestamp()
                + ($now->getTimestamp() - $task->createdAt->getTimestamp())
            );
        }

        $task->createdAt = $now;

        // We need to create a new relation since the old one references the template
        foreach ($task->attributes as $attribute) {
            $attribute->id  = 0;
            $attribute->ref = 0;
        }

        foreach ($task->taskElements as $element) {
            $element->id   = 0;
            $element->task = 0;

            if ($element->due !== null) {
                $element->due->setTimestamp(
                    $element->due->getTimestamp()
                    + ($now->getTimestamp() - $element->createdAt->getTimestamp())
                );
            }

            $element->createdAt = $now;

            // We need to create a new relation since the old one references the template
            foreach ($element->accRelation as $relation) {
                $relation->id      = 0;
                $relation->element = 0;
            }

            foreach ($element->grpRelation as $relation) {
                $relation->id      = 0;
                $relation->element = 0;
            }
        }

        return $task;
    }

    use \Modules\Media\Models\MediaListTrait;
    use \Modules\Attribute\Models\AttributeHolderTrait;
}
