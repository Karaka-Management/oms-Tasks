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

namespace Modules\Tasks\tests\Views;

use Modules\Tasks\Views\TaskView;

/**
 * @internal
 */
class TaskViewTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers Modules\Tasks\Views\TaskView
     * @group framework
     */
    public function testDefault() : void
    {
        $view = new TaskView();

        self::assertStringContainsString('', $view->getAccountImage(999));
    }

    /**
     * @covers Modules\Tasks\Views\TaskView
     * @group framework
     */
    public function testAccountImageUrl() : void
    {
        $view = new TaskView();

        self::assertEquals('Web/Backend/img/default-user.jpg', $view->getAccountImage(1));
    }
}
