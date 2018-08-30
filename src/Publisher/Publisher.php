<?php

/**
 * packagist-release-publisher.
 *
 * @package    packagist-release-publisher
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2018 netzmacht David Molineus
 * @license    LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace App\Publisher;

use App\Release\Release;

/**
 * Interface Publisher
 */
interface Publisher
{
    public function supports(Release $release): bool;

    public function publish(Release $release): void;
}
