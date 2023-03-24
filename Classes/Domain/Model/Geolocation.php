<?php
namespace RKW\RkwGeolocation\Domain\Model;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Geolocation
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwGeolocation
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Geolocation extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{

    /**
     * @var float
     */
    protected float $longitude = 0.0;


    /**
     * @var float
     */
    protected float $latitude = 0.0;


    /**
     * @var string
     */
    protected string $postalCode = '';


    /**
     * @var string
     */
    protected string $formattedAddress = '';


    /**
     * Returns the longitude
     *
     * @return float $longitude
     */
    public function getLongitude(): float
    {
        return $this->longitude;
    }


    /**
     * Sets the longitude
     *
     * @param float $longitude
     * @return void
     */
    public function setLongitude(float $longitude): void
    {
        $this->longitude = $longitude;
    }


    /**
     * Returns the latitude
     *
     * @return float $latitude
     */
    public function getLatitude(): float
    {
        return $this->latitude;
    }


    /**
     * Sets the latitude
     *
     * @param float $latitude
     * @return void
     */
    public function setLatitude(float $latitude): void
    {
        $this->latitude = $latitude;
    }


    /**
     * Returns the postalCode
     *
     * @return string $postalCode
     */
    public function getPostalCode(): string
    {
        return $this->postalCode;
    }


    /**
     * Sets the postalCode
     *
     * @param string $postalCode
     * @return void
     */
    public function setPostalCode(string $postalCode): void
    {
        $this->postalCode = $postalCode;
    }


    /**
     * Returns the formattedAddress
     *
     * @return string $formattedAddress
     */
    public function getFormattedAddress(): string
    {
        return $this->formattedAddress;
    }


    /**
     * Sets the formattedAddress
     *
     * @param string $formattedAddress
     * @return void
     */
    public function setFormattedAddress(string $formattedAddress): void
    {
        $this->formattedAddress = $formattedAddress;
    }

}
