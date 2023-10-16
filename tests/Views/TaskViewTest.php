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

namespace Modules\Tasks\tests\Views;

use Modules\Admin\Models\AccountMapper;
use Modules\Admin\Models\NullAccount;
use Modules\Media\Models\Media;
use Modules\Profile\Models\Profile;
use Modules\Profile\Models\ProfileMapper;
use Modules\Tasks\Views\TaskView;

/**
 * @internal
 */
class TaskViewTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers Modules\Tasks\Views\TaskView
     * @group module
     */
    public function testDefault() : void
    {
        $view = new TaskView();

        self::assertStringContainsString('', $view->getAccountImage(999));
    }

    /**
     * @covers Modules\Tasks\Views\TaskView
     * @group module
     */
    public function testAccountImageUrl() : void
    {
        $media              = new Media();
        $media->createdBy   = new NullAccount(1);
        $media->description = 'desc';
        $media->setPath('Web/Backend/img/default-user.jpg');
        $media->size      = 11;
        $media->extension = 'png';
        $media->name      = 'Image';

        if (($profile = ProfileMapper::get()->where('account', 1)->execute())->id === 0) {
            $profile = new Profile();

            $profile->account  = AccountMapper::get()->where('id', 1)->execute();
            $profile->image    = $media;
            $profile->birthday =  new \DateTime('now');

            $id = ProfileMapper::create()->execute($profile);
            self::assertGreaterThan(0, $profile->id);
            self::assertEquals($id, $profile->id);
        } else {
            $profile->image    = $media;
            $profile->birthday =  new \DateTime('now');

            ProfileMapper::update()->with('image')->execute($profile);
        }

        $view = new TaskView();
        self::assertEquals('Web/Backend/img/default-user.jpg', $view->getAccountImage(1));
    }
}
