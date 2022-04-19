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

use Modules\Tasks\Models\DutyType;
use Modules\Tasks\Models\RelationAbstract;

/**
 * @internal
 */
final class RelationAbstractTest extends \PHPUnit\Framework\TestCase
{
    private RelationAbstract $rel;

    /**
     * {@inheritdoc}
     */
    protected function setUp() : void
    {
        $this->rel = new class() extends RelationAbstract
        {
            public function getRelation() { return new Modules\Admin\Models\Group(); }

            public function jsonSerialize() { return []; }
        };
    }

    /**
     * @covers Modules\Tasks\Models\RelationAbstract
     * @group module
     */
    public function testDefault() : void
    {
        self::assertEquals(0, $this->rel->getId());
        self::assertEquals(DutyType::TO, $this->rel->getDuty());
    }

    /**
     * @covers Modules\Tasks\Models\RelationAbstract
     * @group module
     */
    public function testDutyInputOutput() : void
    {
        $this->rel->setDuty(DutyType::CC);
        self::assertEquals(DutyType::CC, $this->rel->getDuty());
    }
}
