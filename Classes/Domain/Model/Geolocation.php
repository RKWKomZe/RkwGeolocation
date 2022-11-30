<?php

namespace RKW\RkwGeolocation\Domain\Model;
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
     * @var integer
     */
    protected $longitude = 0;

    /**
     * @var integer
     */
    protected $latitude = 0;

    /**
     * @var integer
     */
    protected $postalCode = '';

    /**
     * @var string
     */
    protected $formattedAddress = '';


    /**
     * Returns the longitude
     *
     * @return integer $longitude
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Sets the longitude
     *
     * @param integer $longitude
     * @return void
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;
    }


    /**
     * Returns the latitude
     *
     * @return integer $latitude
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Sets the latitude
     *
     * @param integer $latitude
     * @return void
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;
    }


    /**
     * Returns the postalCode
     *
     * @return integer $postalCode
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * Sets the postalCode
     *
     * @param integer $postalCode
     * @return void
     */
    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;
    }


    /**
     * Returns the formattedAddress
     *
     * @return string $formattedAddress
     */
    public function getFormattedAddress()
    {
        return $this->formattedAddress;
    }

    /**
     * Sets the formattedAddress
     *
     * @param string $formattedAddress
     * @return void
     */
    public function setFormattedAddress($formattedAddress)
    {
        $this->formattedAddress = $formattedAddress;
    }

}
