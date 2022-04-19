<?php
/**
 * Karaka
 *
 * PHP Version 8.1
 *
 * @package   Modules\Tasks\Models
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://karaka.app
 */
declare(strict_types=1);

namespace Modules\Tasks\Models;

use Modules\Admin\Models\Account;
use Modules\Admin\Models\NullAccount;

/**
 * Task relation to account
 *
 * @package Modules\Tasks\Models
 * @license OMS License 1.0
 * @link    https://karaka.app
 * @since   1.0.0
 */
class AccountRelation extends RelationAbstract
{
    /**
     * Relation object
     *
     * @var Account
     * @since 1.0.0
     */
    private Account $relation;

    /**
     * Constructor.
     *
     * @param null|Account $account Account
     * @param int          $duty    Duty type
     *
     * @since 1.0.0
     */
    public function __construct(Account $account = null, int $duty = DutyType::TO)
    {
        $this->relation = $account ?? new NullAccount();
        $this->duty     = $duty;
    }

    /**
     * Get relation object.
     *
     * @return Account
     *
     * @since 1.0.0
     */
    public function getRelation() : Account
    {
        return $this->relation;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray() : array
    {
        return [
            'id'       => $this->id,
            'duty'     => $this->duty,
            'relation' => $this->relation,
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
