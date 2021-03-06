<?php

/**
 * Release notifier.
 *
 * @package    release-notifier
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2018 netzmacht David Molineus
 * @license    LGPL-3.0-or-later https://github.com/netzmacht/release-notifier/blob/master/LICENSE
 * @filesource
 */

namespace Netzmacht\ReleaseNotifier\Package;

use ArrayIterator;
use Countable;
use DateTimeImmutable;
use IteratorAggregate;

/**
 * Class ReleaseIterator
 */
final class ReleaseIterator implements IteratorAggregate, Countable
{
    /**
     * Last modified date of the package release information.
     *
     * @var DateTimeImmutable
     */
    private $lastModified;

    /**
     * List of releases.
     *
     * @var Release[]|array
     */
    private $releases;

    /**
     * ReleaseIterator constructor.
     *
     * @param DateTimeImmutable $lastModified Last modified date of the package release information.
     * @param Release[]|array   $releases     List of releases.
     */
    public function __construct($releases, DateTimeImmutable $lastModified)
    {
        $this->lastModified = $lastModified;
        $this->releases     = $releases;
    }

    /**
     * Get lastModified.
     *
     * @return DateTimeImmutable
     */
    public function lastModified(): DateTimeImmutable
    {
        return $this->lastModified;
    }

    /**
     * Get the iterator.
     *
     * @return \Iterator<Release>
     */
    public function getIterator(): \Iterator
    {
        return new ArrayIterator($this->releases);
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        return count($this->releases);
    }
}
