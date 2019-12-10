<?php

declare(strict_types=1);

/*
 * This file is part of the KnpDoctrineBehaviors package.
 *
 * (c) KnpLabs <http://knplabs.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\DoctrineBehaviors\Model\SoftDeletable;

/**
 * SoftDeletable trait.
 *
 * Should be used inside entity, that needs to be self-deleted.
 */
trait SoftDeletableMethods
{
    /**
     * Marks entity as deleted.
     */
    public function delete(): void
    {
        $this->deletedAt = $this->currentDateTime();
    }

    /**
     * Restore entity by undeleting it
     */
    public function restore(): void
    {
        $this->deletedAt = null;
    }

    /**
     * Checks whether the entity has been deleted.
     */
    public function isDeleted(): bool
    {
        if ($this->deletedAt !== null) {
            return $this->deletedAt <= $this->currentDateTime();
        }

        return false;
    }

    /**
     * Checks whether the entity will be deleted.
     */
    public function willBeDeleted(?\DateTime $at = null): bool
    {
        if ($this->deletedAt === null) {
            return false;
        }
        if ($at === null) {
            return true;
        }

        return $this->deletedAt <= $at;
    }

    /**
     * Returns date on which entity was been deleted.
     */
    public function getDeletedAt(): ?DateTime
    {
        return $this->deletedAt;
    }

    /**
     * Set the delete date to given date.
     *
     * @param DateTime $date
     * @param               object
     *
     * @return $this
     */
    public function setDeletedAt(\DateTime $date)
    {
        $this->deletedAt = $date;

        return $this;
    }

    /**
     * Get a instance of \DateTime with the current data time including milliseconds.
     */
    private function currentDateTime(): \DateTime
    {
        $dateTime = \DateTime::createFromFormat('U.u', sprintf('%.6F', microtime(true)));
        $dateTime->setTimezone(new \DateTimeZone(date_default_timezone_get()));

        return $dateTime;
    }
}
