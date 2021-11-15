<?php
/**
 * Orange Management
 *
 * PHP Version 8.0
 *
 * @package   Modules\Tasks
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://orange-management.org
 */
declare(strict_types=1);

namespace Modules\Tasks\Views;

use Modules\Media\Models\Media;
use Modules\Media\Models\NullMedia;
use Modules\Profile\Models\ProfileMapper;
use Modules\Tasks\Models\TaskStatus;
use phpOMS\Uri\UriFactory;
use phpOMS\Views\View;

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
     * User profile image.
     *
     * @var Media
     * @since 1.0.0
     */
    public Media $defaultProfileImage;

    /**
     * Constructor
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->defaultProfileImage = new NullMedia();
    }

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

        if (($profile instanceof NullProfile) || $profile->image->getPath() === '') {
            return UriFactory::build('{/prefix}' . $this->defaultProfileImage->getPath());
        }

        return UriFactory::build('{/prefix}' . $profile->image->getPath());
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
