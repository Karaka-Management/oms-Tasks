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
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Tasks\tests\Models;

use Modules\Admin\Models\NullGroup;
use Modules\Tasks\Models\DutyType;
use Modules\Tasks\Models\GroupRelation;

/**
 * @internal
 */
final class GroupRelationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers Modules\Tasks\Models\GroupRelation
     * @group module
     */
    public function testDefault() : void
    {
        $obj = new GroupRelation();
        self::assertEquals(0, $obj->getId());
        self::assertEquals(0, $obj->getRelation()->getId());
        self::assertEquals(DutyType::TO, $obj->getDuty());
    }

    /**
     * @covers Modules\Tasks\Models\GroupRelation
     * @group module
     */
    public function testSetGet() : void
    {
        $obj = new GroupRelation($g = new NullGroup(1), DutyType::CC);
        self::assertEquals(1, $obj->getRelation()->getId());
        self::assertEquals(DutyType::CC, $obj->getDuty());

        self::assertEquals([
            'id'       => 0,
            'duty'     => DutyType::CC,
            'relation' => $g,
        ], $obj->toArray());
        self::assertEquals($obj->toArray(), $obj->jsonSerialize());

        $obj->setDuty(DutyType::TO);
        self::assertEquals(DutyType::TO, $obj->getDuty());
    }
}
