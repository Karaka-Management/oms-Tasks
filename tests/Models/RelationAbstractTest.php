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

use Modules\Tasks\Models\DutyType;
use Modules\Tasks\Models\RelationAbstract;

/**
 * @internal
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Modules\Tasks\Models\RelationAbstract::class)]
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

            public function jsonSerialize() : mixed { return []; }
        };
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    public function testDefault() : void
    {
        self::assertEquals(0, $this->rel->id);
        self::assertEquals(DutyType::TO, $this->rel->getDuty());
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    public function testDutyInputOutput() : void
    {
        $this->rel->setDuty(DutyType::CC);
        self::assertEquals(DutyType::CC, $this->rel->getDuty());
    }
}
