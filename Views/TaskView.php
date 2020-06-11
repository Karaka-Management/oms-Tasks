<?php
/**
 * Orange Management
 *
 * PHP Version 7.4
 *
 * @package   Modules\Tasks
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://orange-management.org
 */
declare(strict_types=1);

namespace Modules\Tasks\Views;

use Modules\Tasks\Models\TaskStatus;
use phpOMS\Views\View;
use phpOMS\Uri\UriFactory;
use Modules\Profile\Models\ProfileMapper;

/**
 * Task view class.
 *
 * @package Modules\Tasks
 * @license OMS License 1.0
 * @link    https://orange-management.org
 * @since   1.0.0
 */
class TaskView extends View
{
    /**
     * Get the profile image
     *
     * If the profile doesn't have an image a random default image is used
     *
     * @param int $account Account
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function getAccountImage(int $account) : string
    {
        $profile = ProfileMapper::getFor($account, 'account');

        if ($profile === null || $profile->getImage()->getPath() === '') {
            return UriFactory::build('Web/Backend/img/user_default_' . \mt_rand(1, 6) . '.png');
        }

        return UriFactory::build($profile->getImage()->getPath());
    }

    /**
     * Get task status color.
     *
     * @param int $status Status
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function getStatus(int $status) : string
    {
        if ($status === TaskStatus::OPEN) {
            return 'darkblue';
        } elseif ($status === TaskStatus::DONE) {
            return 'green';
        } elseif ($status === TaskStatus::WORKING) {
            return 'purple';
        } elseif ($status === TaskStatus::CANCELED) {
            return 'red';
        } elseif ($status === TaskStatus::SUSPENDED) {
            return 'yellow';
        }

        return 'black';
    }
}
