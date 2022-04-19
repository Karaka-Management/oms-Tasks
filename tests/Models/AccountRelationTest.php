<?php
/**
 * Karaka
 *
 * PHP Version 8.1
 *
 * @package   tests
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://karaka.app
 */
declare(strict_types=1);

namespace Modules\Tasks\tests\Models;

use Modules\Admin\Models\NullAccount;
use Modules\Tasks\Models\AccountRelation;
use Modules\Tasks\Models\DutyType;

/**
 * @internal
 */
final class AccountRelationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers Modules\Tasks\Models\AccountRelation
     * @group module
     */
    public function testDefault() : void
    {
        $obj = new AccountRelation();
        self::assertEquals(0, $obj->getId());
        self::assertEquals(0, $obj->getRelation()->getId());
        self::assertEquals(DutyType::TO, $obj->getDuty());
    }

    /**
     * @covers Modules\Tasks\Models\AccountRelation
     * @group module
     */
    public function testSetGet() : void
    {
        $obj = new AccountRelation($a = new NullAccount(1), DutyType::CC);
        self::assertEquals(1, $obj->getRelation()->getId());
        self::assertEquals(DutyType::CC, $obj->getDuty());

        self::assertEquals([
            'id'       => 0,
            'duty'     => DutyType::CC,
            'relation' => $a,
        ], $obj->toArray());
        self::assertEquals($obj->toArray(), $obj->jsonSerialize());

        $obj->setDuty(DutyType::TO);
        self::assertEquals(DutyType::TO, $obj->getDuty());
    }
}
