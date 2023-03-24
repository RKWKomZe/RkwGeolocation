<?php
namespace RKW\RkwGeolocation\Service;

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

use Madj2k\CoreExtended\Utility\GeneralUtility;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogManager;
use \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Geolocation
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwGeolocation
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Geolocation implements \TYPO3\CMS\Core\SingletonInterface
{

    /**
     * @const string
     */
    const GOOGLE_API_URL = 'https://maps.google.com/maps/api/geocode/json';


    /**
     * @const string
     */
    const GOOGLE_API_KEY = '';


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
    protected string $address = '';


    /**
     * @var string
     */
    protected string $postalCode = '';


    /**
     * @var string
     */
    protected string $country = 'Germany';


    /**
     * Logger
     *
     * @var \TYPO3\CMS\Core\Log\Logger|null
     */
    protected ?Logger $logger = null;


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
    public function setLongitude(float $longitude)
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
    public function setLatitude(float $latitude)
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
    public function setPostalCode(string $postalCode)
    {
        $this->postalCode = $postalCode;
    }


    /**
     * Returns the address
     *
     * @return string
     */
    public function getAddress(): string
    {
        return $this->address;
    }


    /**
     * Sets the address
     *
     * @param string $address
     * @return void
     */
    public function setAddress(string $address)
    {
        $this->address = $address;
    }


    /**
     * Returns the country
     *
     * @return string
     */
    public function getCountry(): string
    {
        return $this->country;
    }


    /**
     * Sets the country
     *
     * @param string $country
     * @return void
     */
    public function setCountry(string $country)
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

        // if an address is given
        if ($this->getAddress()) {
            $destinationArray[] = $this->getAddress();
            $destinationArray[] = $this->getCountry();

            // if only a postal code is given
        } elseif ($this->getPostalCode()) {
            $destinationArray[] = $this->getPostalCode();
            $destinationArray[] = $this->getCountry();

            // else long AND lat is given (only one of these doesn't work)
        } elseif ($this->getLongitude() && $this->getLatitude()) {
            $destinationArray[] = $this->getLongitude();
            $destinationArray[] = $this->getLatitude();

        } else {

            $this->getLogger()->log(
                \TYPO3\CMS\Core\Log\LogLevel::ERROR,
                'No valid location given. You must specify an address or a zip-code or longitude and latitude.'
            );

            return false;
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

        $completeUrlSave = $completeUrl = $apiUrl . '?sensor=false&address={' . urlencode(implode(', ', $destinationArray)) . '}';

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
            if (ini_get('allow_url_fopen')) {
                $responseJson = file_get_contents($completeUrl, false, $cxContext);
            } else {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  // Disable SSL verification
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_URL,$completeUrl);
                $responseJson=curl_exec($ch);
                curl_close($ch);
            }
            $response = json_decode($responseJson, true);

            // if response is "OK", than set retrieved data
            if ($response['status'] == 'OK') {

                /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
                $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ObjectManager::class);

                /** @var \RKW\RkwGeolocation\Domain\Model\Geolocation $geoLocation */
                $geoLocation = $objectManager->get(\RKW\RkwGeolocation\Domain\Model\Geolocation::class);

                $geoLocation->setLatitude(
                    filter_var(
                        $response['results'][0]['geometry']['location']['lat'],
                        FILTER_VALIDATE_FLOAT
                    )
                );
                $geoLocation->setLongitude(
                    filter_var(
                        $response['results'][0]['geometry']['location']['lng'],
                        FILTER_VALIDATE_FLOAT
                    )
                );
                $geoLocation->setFormattedAddress(
                    filter_var(
                        $response['results'][0]['formatted_address'],
                        FILTER_SANITIZE_STRING
                    )
                );

                $addressComponents = $response['results'][0]['address_components'];

                // additional get postalCode
                if (is_array($addressComponents)) {
                    foreach ($addressComponents as $component) {

                        if ($component['types'][0] == 'postal_code') {
                            $geoLocation->setPostalCode($component['long_name']);
                            break;
                        }
                    }
                }

                $this->getLogger()->log(
                    \TYPO3\CMS\Core\Log\LogLevel::INFO,
                    sprintf(
                        'Successfully received data from Google API for location "%s"',
                        implode(',', $destinationArray)
                    )
                );

                return $geoLocation;

            }

            $this->getLogger()->log(
                \TYPO3\CMS\Core\Log\LogLevel::WARNING,
                sprintf(
                    'Google API call for location "%s" failed (%s). Reason: %s. %s',
                    implode(',', $destinationArray),
                    $completeUrlSave,
                    $response['status'],
                    $response['error_message'])
            );

        } catch (\Exception $e) {
            $this->getLogger()->log(
                \TYPO3\CMS\Core\Log\LogLevel::ERROR,
                sprintf('Google API for location "%s" call failed (%s). Reason: %s.',
                    implode(',', $destinationArray),
                    $completeUrlSave,
                    $e->getMessage()
                )
            );
        }

        return false;
    }


    /**
     * Prepares a MySql-Select for distance search
     *
     * @param string $table Table for select
     * @param int $limit Max items to select
     * @param int $offset Items to skip
     * @param string $where Additional where-string
     * @param string $orderBy Order-Clause
     * @param int $maxDistance Maximum distance to include
     * @return string
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     * @see https://tighten.co/blog/a-mysql-distance-function-you-should-know-about
     */
    public function getQueryStatementDistanceSearch(
        string $table,
        int $limit,
        int $offset,
        string $where,
        string $orderBy = 'distance',
        int $maxDistance = 5
    ): string
    {

        if (
            ($geoLocation = $this->fetchGeoData())
            && ($geoLocation instanceof \RKW\RkwGeolocation\Domain\Model\Geolocation)
        ) {
            return '
                SELECT *,
                (
                    SELECT ST_Distance_Sphere(
                        point(' . $geoLocation->getLongitude() . ', ' . $geoLocation->getLatitude() . '),
                        point(' . $table . '.longitude, ' . $table . '.latitude)
                    ) * 0.001
                ) AS distance
                FROM ' . $table . '
                WHERE ' . $table . '.longitude > 0 AND ' . $table . '.latitude > 0 ' .
                \Madj2k\CoreExtended\Utility\QueryUtility::getWhereClauseEnabled($table) .
                \Madj2k\CoreExtended\Utility\QueryUtility::getWhereClauseVersioning($table) .
                ' ' . $where .
                ' HAVING distance <= ' . $maxDistance . '
                ORDER BY ' . addslashes($orderBy) . '
                LIMIT ' . $offset . ',' . $limit . ';
            ';
        }

        return '';
    }


    /**
     * Returns TYPO3 settings
     *
     * @param string $which Which type of settings will be loaded
     * @return array
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    protected function getSettings(string $which = ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS): array
    {
        return GeneralUtility::getTypoScriptConfiguration('RkwGeolocation', $which);
    }


    /**
     * Returns logger instance
     *
     * @return \TYPO3\CMS\Core\Log\Logger
     */
    protected function getLogger(): Logger
    {

        if (!$this->logger instanceof \TYPO3\CMS\Core\Log\Logger) {
            $this->logger = GeneralUtility::makeInstance(LogManager::class)
                ->getLogger(__CLASS__);
        }

        return $this->logger;
    }
}
