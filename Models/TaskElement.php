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
use Modules\Admin\Models\Group;
use Modules\Admin\Models\NullAccount;

/**
 * Task element class.
 *
 * @package Modules\Tasks\Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
class TaskElement implements \JsonSerializable
{
    /**
     * Id.
     *
     * @var int
     * @since 1.0.0
     */
    public int $id = 0;

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
     * Task.
     *
     * @var int
     * @since 1.0.0
     */
    public int $task = 0;

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
     * Status.
     *
     * @var int
     * @since 1.0.0
     */
    public int $status = TaskStatus::OPEN;

    /**
     * Due.
     *
     * @var null|\DateTime
     * @since 1.0.0
     */
    public ?\DateTime $due = null;

    /**
     * Priority
     *
     * @var int
     * @since 1.0.0
     */
    public int $priority = TaskPriority::NONE;

    /**
     * Accounts who received this task element.
     *
     * @var AccountRelation[]
     * @since 1.0.0
     */
    public array $accRelation = [];

    /**
     * Groups who received this task element.
     *
     * @var GroupRelation[]
     * @since 1.0.0
     */
    public array $grpRelation = [];

    public int $duration = 0;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->due = new \DateTime('now');
        $this->due->modify('+1 day');
        $this->createdAt = new \DateTimeImmutable('now');
        $this->createdBy = new NullAccount();
    }

    /**
     * Get to
     *
     * @return RelationAbstract[]
     *
     * @since 1.0.0
     */
    public function getTo() : array
    {
        $to = [];

        foreach ($this->accRelation as $acc) {
            if ($acc->getDuty() === DutyType::TO) {
                $to[] = $acc;
            }
        }

        foreach ($this->grpRelation as $grp) {
            if ($grp->getDuty() === DutyType::TO) {
                $to[] = $grp;
            }
        }

        return $to;
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
        foreach ($this->accRelation as $acc) {
            if ($acc->getDuty() === DutyType::TO
                && $acc->getRelation()->id === $id
            ) {
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
        foreach ($this->grpRelation as $grp) {
            if ($grp->getDuty() === DutyType::TO
                && $grp->getRelation()->id === $id
            ) {
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
        foreach ($this->accRelation as $acc) {
            if ($acc->getDuty() === DutyType::CC
                && $acc->getRelation()->id === $id
            ) {
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
        foreach ($this->grpRelation as $grp) {
            if ($grp->getDuty() === DutyType::CC
                && $grp->getRelation()->id === $id
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Add to
     *
     * @param Group|Account $to To
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function addTo($to) : void
    {
        if ($to instanceof Group) {
            $this->addGroupTo($to);
        } elseif (($to instanceof Account)) {
            $this->addAccountTo($to);
        }
    }

    /**
     * Add group as to
     *
     * @param Group $group Group
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function addGroupTo(Group $group) : void
    {
        $groupId = $group->id;

        foreach ($this->grpRelation as $grp) {
            $grpId = $grp->getRelation()->id;

            if ($grpId === $groupId && $grp->getDuty() === DutyType::TO) {
                return;
            }
        }

        $this->grpRelation[] = new GroupRelation($group, DutyType::TO);
    }

    /**
     * Add account as to
     *
     * @param Account $account Account
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function addAccountTo(Account $account) : void
    {
        $accountId = $account->id;

        foreach ($this->accRelation as $acc) {
            $accId = $acc->getRelation()->id;

            if ($accId === $accountId && $acc->getDuty() === DutyType::TO) {
                return;
            }
        }

        $this->accRelation[] = new AccountRelation($account, DutyType::TO);
    }

    /**
     * Get cc
     *
     * @return RelationAbstract[]
     *
     * @since 1.0.0
     */
    public function getCC() : array
    {
        $cc = [];

        foreach ($this->accRelation as $acc) {
            if ($acc->getDuty() === DutyType::CC) {
                $cc[] = $acc;
            }
        }

        foreach ($this->grpRelation as $grp) {
            if ($grp->getDuty() === DutyType::CC) {
                $cc[] = $grp;
            }
        }

        return $cc;
    }

    /**
     * Add cc
     *
     * @param Group|Account $cc CC
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function addCC($cc) : void
    {
        if ($cc instanceof Group) {
            $this->addGroupCC($cc);
        } elseif (($cc instanceof Account)) {
            $this->addAccountCC($cc);
        }
    }

    /**
     * Add group as cc
     *
     * @param Group $group Group
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function addGroupCC(Group $group) : void
    {
        $groupId = $group->id;

        foreach ($this->grpRelation as $grp) {
            $grpId = $grp->getRelation()->id;

            if ($grpId === $groupId && $grp->getDuty() === DutyType::CC) {
                return;
            }
        }

        $this->grpRelation[] = new GroupRelation($group, DutyType::CC);
    }

    /**
     * Add account as cc
     *
     * @param Account $account Account
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function addAccountCC(Account $account) : void
    {
        $accountId = $account->id;

        foreach ($this->accRelation as $acc) {
            $accId = $acc->getRelation()->id;

            if ($accId === $accountId && $acc->getDuty() === DutyType::CC) {
                return;
            }
        }

        $this->accRelation[] = new AccountRelation($account, DutyType::CC);
    }

    /**
     * {@inheritdoc}
     */
    public function toArray() : array
    {
        return [
            'id'             => $this->id,
            'task'           => $this->task,
            'createdBy'      => $this->createdBy,
            'createdAt'      => $this->createdAt,
            'description'    => $this->description,
            'descriptionRaw' => $this->descriptionRaw,
            'status'         => $this->status,
            'to'             => $this->getTo(),
            'cc'             => $this->getCC(),
            'due'            => $this->due,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize() : mixed
    {
        return $this->toArray();
    }

    use \Modules\Media\Models\MediaListTrait;
}
