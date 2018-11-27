<?php

namespace RKW\RkwGeolocation\Service;

use \RKW\RkwBasics\Helper\Common;
use \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

/**
 * Geolocation
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwGeolocation
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Geolocation implements \TYPO3\CMS\Core\SingletonInterface
{

    /**
     * Constants for permissions of FE-User Admins
     *
     * @const integer
     */
    const GOOGLE_API_URL = 'https://maps.google.com/maps/api/geocode/json';

    /**
     * Constants for permissions of FE-User Authors
     *
     * @const integer
     */
    const GOOGLE_API_KEY = '';

    /**
     * @var integer
     */
    protected $longitude = 0;

    /**
     * @var integer
     */
    protected $latitude = 0;

    /**
     * @var string
     */
    protected $address;


    /**
     * @var string
     */
    protected $zip;


    /**
     * @var string
     */
    protected $country = 'Germany';

    /**
     * Logger
     *
     * @var \TYPO3\CMS\Core\Log\Logger
     */
    protected $logger;

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
     * @deprecated Use getZip() instead
     */
    public function getPostalCode()
    {
        return $this->zip;
    }

    /**
     * Sets the postalCode
     *
     * @param integer $zip
     * @return void
     * @deprecated Use setZip($zip) instead
     */
    public function setPostalCode($zip)
    {
        $this->zip = $zip;
    }


    /**
     * Returns the address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Sets the address
     *
     * @param string $address
     * @return void
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }


    /**
     * Returns the zip
     *
     * @return integer $postalCode
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * Sets the zip
     *
     * @param integer $zip
     * @return void
     */
    public function setZip($zip)
    {
        $this->zip = $zip;
    }

    /**
     * Returns the country
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Sets the country
     *
     * @param string $country
     * @return void
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }


    /**
     * Fetches the geodata via Google API
     * Aet a normal address and otherwise a pair of longitude and latitude
     * Attention: Works with "long + lat" OR "postalCode" OR "address" (address is most generally and can include an postal code)
     * Hint: Country is optional. Needed if there are only a postal code is given. "de" for germany is default.
     * -> Both is possible: "DE" or "germany"
     *
     * @return \RKW\RkwGeolocation\Domain\Model\Geolocation|false
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function fetchGeoData()
    {

        $destinationArray = array();
        $destination = null;

        // if an address is given
        if ($this->getAddress()) {
            $destinationArray[] = $this->getAddress();
            $destinationArray[] = $this->getCountry();

            // if only a postal code is given
        } elseif ($this->getZip()) {
            $destinationArray[] = $this->getZip();
            $destinationArray[] = $this->getCountry();

            // else long AND lat is given (only one of these doesn't work)
        } elseif ($this->getLongitude() && $this->getLatitude()) {
            $destinationArray[] = $this->getLongitude();
            $destinationArray[] = $this->getLatitude();


        } else {

            $this->getLogger()->log(
                \TYPO3\CMS\Core\Log\LogLevel::ERROR,
                sprintf('No valid location given. You must specify an address or a zip-code or longitude and latitude.')
            );

            return false;
            //===
        }


        $settings = $this->getSettings();

        // is URL not defined, key is interpreted also as not defined
        if ($settings['googleApiUrl']) {
            $apiUrl = $settings['googleApiUrl'];
            $apiKey = $settings['googleApiKey'];
        } else {
            $apiUrl = self::GOOGLE_API_URL;
            $apiKey = self::GOOGLE_API_KEY;
        }

        $completeUrlSave = $completeUrl = $apiUrl . '?sensor=false&address={' . urlencode(implode(',
		', $destinationArray)) . '}';

        // check for additional api key (not mandatory)
        if ($apiKey) {
            $completeUrl .= "&key=" . $apiKey;
        }


        try {

            // set up context if proxy is used
            $aContext = array();
            if ($settings['proxy']) {

                $aContext = array(
                    'http' => array(
                        'proxy'           => $settings['proxy'],
                        'request_fulluri' => true,
                    ),
                );

                if ($settings['proxyUsername']) {
                    $auth = base64_encode($settings['proxyUsername'] . ':' . $settings['proxyPassword']);
                    $aContext['http']['header'] = 'Proxy-Authorization: Basic ' . $auth;
                }
            }

            // get data from Google
            $cxContext = stream_context_create($aContext);
            $responseJson = file_get_contents($completeUrl, false, $cxContext);
            $response = json_decode($responseJson, true);

            // if response is "OK", than set retrieved data
            if ($response['status'] == 'OK') {

                $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');

                /** @var \RKW\RkwGeolocation\Domain\Model\Geolocation $geoLocation */
                $geoLocation = $objectManager->get('RKW\\RkwGeolocation\\Domain\\Model\\Geolocation');

                $geoLocation->setLatitude(filter_var($response['results'][0]['geometry']['location']['lat'], FILTER_VALIDATE_FLOAT));
                $geoLocation->setLongitude(filter_var($response['results'][0]['geometry']['location']['lng'], FILTER_VALIDATE_FLOAT));
                $geoLocation->setFormattedAddress(filter_var($response['results'][0]['formatted_address'], FILTER_SANITIZE_STRING));

                $addressComponents = $response['results'][0]['address_components'];

                // additional get zip
                if (is_array($addressComponents)) {
                    foreach ($addressComponents as $component) {

                        if ($component['types'][0] == 'postal_code') {
                            $geoLocation->setPostalCode($component['long_name']);
                            break;
                            //===
                        }
                    }
                }

                $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('Successfully received data from Google API for location "%s"', implode(',', $destinationArray)));

                return $geoLocation;
                //===

            }

            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::WARNING, sprintf('Google API call for location "%s" failed (%s). Reason: %s. %s', implode(',', $destinationArray), $completeUrlSave, $response['status'], $response['error_message']));

        } catch (\Exception $e) {
            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::ERROR, sprintf('Google API for location "%s" call failed (%s). Reason: %s.', implode(',', $destinationArray), $completeUrlSave, $e->getMessage()));
        }

        return false;
        //===
    }


    /**
     * function determineGeoData
     *
     * @deprecated since 2017-02-23, Alias of fetchGeoData, use fetchGeoData instead
     * @return void
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function determineGeoData()
    {
        $this->fetchGeoData();
    }


    /**
     * Prepares a MySql-Select for distance search
     *
     * @param \TYPO3\CMS\Extbase\Persistence\QueryInterface $query
     * @param  string $table Table for select
     * @param  int $limit Max items to select
     * @param  int $offset Items to skip
     * @param  string $where Additional where-string
     * @param  string $orderBy Order-Clause
     * @param  int $maxDistance Maximum distance to include
     * @param  string $database Type of database used
     * @return string
     * @throws \TYPO3\CMS\Core\Type\Exception\InvalidEnumerationValueException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function getQueryStatementDistanceSearch($query, $table, $limit, $offset, $where, $orderBy = 'distance', $maxDistance = 5000, $database = 'mysql')
    {

        if (
            ($geoLocation = $this->fetchGeoData())
            && ($geoLocation instanceof \RKW\RkwGeolocation\Domain\Model\Geolocation)
        ) {

            switch ($database) {
                default:
                    $query->statement('
                        SELECT *,
                        (
                            6371.165 * acos(
                                cos(
                                    radians( ' . addslashes($geoLocation->getLatitude()) . ' )
                                ) * cos(
                                    radians( ' . $table . '.latitude )
                                ) * cos(
                                    radians( ' . $table . '.longitude ) - radians( ' . addslashes($geoLocation->getLongitude()) . ' )
                                ) + sin(
                                    radians( ' . addslashes($geoLocation->getLatitude()) . ' )
                                ) * sin(
                                    radians( ' . $table . '.latitude )
                                )
                            )
                        ) AS distance
                        FROM ' . $table . '
                        WHERE ' . $table . '.longitude > 0 AND ' . $table . '.latitude > 0 ' .
                        \RKW\RkwBasics\Helper\QueryTypo3::getWhereClauseForEnableFields($table) .
                        \RKW\RkwBasics\Helper\QueryTypo3::getWhereClauseForVersioning($table) .
                        ' ' . $where .
                        '
                        HAVING distance <= ' . intval($maxDistance) . '
                        ORDER BY ' . addslashes($orderBy) . '
                        LIMIT ' . intval($offset) . ',' . intval($limit) . ';
                    ');
            }
        }
    }


    /**
     * calculateDistance between two geo data
     *
     * @param float|int $lat1 Lat for point 1
     * @param float|int $lng1 Lng for point 1
     * @param float|int $lat2 Lat for point 2
     * @param float|int $lng2 Lng for point 2
     * @return float|int
     * @deprecated since 2017-02-23
     */
    public function calculateDistance($lat1, $lng1, $lat2, $lng2)
    {

        $earthRadius = 6378.16;

        $dlng = self::radians($lng2 - $lng1);
        $dlat = self::radians($lat2 - $lat1);
        $a = pow(sin($dlat / 2), 2) + cos(self::radians($lat1)) * cos(self::radians($lat2)) * pow(sin($dlng / 2), 2);
        $angle = 2 * atan2(sqrt($a), sqrt(1 - $a));

        $result = $angle * $earthRadius;

        return $result < 10 ? round($result, 1) : round($result);
        //===
    }


    /**
     * radians
     *
     * @param $x
     * @return float|int
     * @deprecated since 2017-02-23
     */
    protected function radians($x)
    {

        return $x * pi() / 180;
        //===
    }


    /**
     * Returns TYPO3 settings
     *
     * @param string $which Which type of settings will be loaded
     * @return array
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    protected function getSettings($which = ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS)
    {
        return Common::getTyposcriptConfiguration('RkwGeolocation', $which);
        //===
    }


    /**
     * Returns logger instance
     *
     * @return \TYPO3\CMS\Core\Log\Logger
     */
    protected function getLogger()
    {

        if (!$this->logger instanceof \TYPO3\CMS\Core\Log\Logger) {
            $this->logger = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Log\\LogManager')->getLogger(__CLASS__);
        }

        return $this->logger;
        //===
    }
}