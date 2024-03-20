<?php
/**
 * Jingga
 *
 * PHP Version 8.1
 *
 * @package   tests
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
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
#[\PHPUnit\Framework\Attributes\CoversClass(\Modules\Tasks\Models\GroupRelation::class)]
final class GroupRelationTest extends \PHPUnit\Framework\TestCase
{
    #[\PHPUnit\Framework\Attributes\Group('module')]
    public function testDefault() : void
    {
        $obj = new GroupRelation();
        self::assertEquals(0, $obj->id);
        self::assertEquals(0, $obj->getRelation()->id);
        self::assertEquals(DutyType::TO, $obj->getDuty());
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    public function testSetGet() : void
    {
        $obj = new GroupRelation($g = new NullGroup(1), DutyType::CC);
        self::assertEquals(1, $obj->getRelation()->id);
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
