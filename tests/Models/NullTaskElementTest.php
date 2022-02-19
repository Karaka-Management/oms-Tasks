<?php
/**
 * Karaka
 *
 * PHP Version 8.0
 *
 * @package   tests
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://karaka.app
 */
declare(strict_types=1);

namespace Modules\Tasks\tests\Models;

use Modules\Tasks\Models\NullTaskElement;

/**
 * @internal
 */
final class NullTaskElementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers Modules\Tasks\Models\NullTaskElement
     * @group framework
     */
    public function testNull() : void
    {
        self::assertInstanceOf('\Modules\Tasks\Models\TaskElement', new NullTaskElement());
    }

    /**
     * @covers Modules\Tasks\Models\NullTaskElement
     * @group framework
     */
    public function testId() : void
    {
        $null = new NullTaskElement(2);
        self::assertEquals(2, $null->getId());
    }
}
