<?php

namespace RKW\RkwGeolocation\ViewHelpers;

use \RKW\RkwBasics\Helper\Common;
use \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

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
 * Class MapsViewHelper
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwGeolocation
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class MapsViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{

    /**
     * As this ViewHelper renders HTML, the output must not be escaped.
     *
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * maps
     *
     * @param float $longitude
     * @param float $latitude
     * @param string $address
     * @param int $zoom
     * @param bool $noMarker
     * @param string $language
     * @param string $width
     * @param string $height
     * @return string $string
     */
    public function render($longitude = 8.5709247, $latitude = 50.1307615, $address = '', $zoom = 12, $noMarker = false, $language = "de", $width = '100%', $height = '300px')
    {
        $settings = $this->getSettings();

        return '
            <!-- Google Maps API -->
		    <script src="https://maps.googleapis.com/maps/api/js?v=3&amp;key=' . $settings['googleApiKeyJs'] . '&amp;language=' . $language . '"></script>

            <script>
             var txRkwGeolocationGoogleMaps = {

                /* Google map object */
                map : null,

                /* Google Geo-Coder */
                geoCoder : null,

                /* ====================================================== */
                /* Initialize and display a google map */
                init : function ()
                {

                    /* Create a Google coordinate object for where to center the map */
                    var latlngDC = new google.maps.LatLng(' . floatval($latitude) . ', ' . floatval($longitude) . ');

                    /* Map options for how to display the Google map */
                    /* Coordinates of Washington, DC (area centroid) */
                    var mapOptions = { zoom: ' . intval($zoom) . ', center: latlngDC  };

                    /* Show the Google map in the div with the attribute id map-canvas. */
                    this.map = new google.maps.Map(document.getElementById(\'tx-rkwgeolocation-map-canvas\'), mapOptions);

                    /* Check address? */
                    if (true == ' . (!empty($address) ? 'true' : 'false') . ') {

                        this.geoCoder = new google.maps.Geocoder();
                        this.geoCoder.geocode( { \'address\': \'' . addslashes($address) . '\'}, function(results, status) {
                            if (status == google.maps.GeocoderStatus.OK) {
                                this.map.setCenter(results[0].geometry.location);
                                if (false == ' . ($noMarker ? 'true' : 'false') . ') {
                                    var marker = new google.maps.Marker({
                                        map: this.map,
                                        position: results[0].geometry.location
                                    });
                                }
                            }
                        });
                    }

                    if (false == ' . ($noMarker ? 'true' : 'false') . ') {
                        /* Place a standard Google Marker at the same location as the map center (Washington, DC) */
                        /* When you hover over the marker, it will display the title */
                        var marker = new google.maps.Marker( {
                            position: latlngDC,
                            map: this.map
                        });
                    }
                }
            };

            /* Call the method init() to display the google map when the web page is displayed ( load event ) */
            google.maps.event.addDomListener( window, \'load\', txRkwGeolocationGoogleMaps.init );

            </script>
            <style>
                /* style settings for Google map */
                #tx-rkwgeolocation-map-canvas
                {
                    width : ' . $width . '; 	/* map width */
                    height: ' . $height . ';	/* map height */
                }
            </style>
            <div id="tx-rkwgeolocation-map-canvas"></div>
        ';
    }


    /**
     * Returns TYPO3 settings
     *
     * @param string $which Which type of settings will be loaded
     * @return array
     */
    protected function getSettings($which = ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS)
    {
        return Common::getTyposcriptConfiguration('RkwGeolocation', $which);
        //===
    }

}