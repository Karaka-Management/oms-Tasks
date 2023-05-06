<?php
/**
 * Karaka
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

use Modules\Tasks\Models\NullTask;

/**
 * @internal
 */
final class NullTaskTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers Modules\Tasks\Models\NullTask
     * @group framework
     */
    public function testNull() : void
    {
        self::assertInstanceOf('\Modules\Tasks\Models\Task', new NullTask());
    }

    /**
     * @covers Modules\Tasks\Models\NullTask
     * @group framework
     */
    public function testId() : void
    {
        $null = new NullTask(2);
        self::assertEquals(2, $null->id);
    }
}
