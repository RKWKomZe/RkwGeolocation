<?php
namespace RKW\RkwGeolocation\ViewHelpers;

use Madj2k\CoreExtended\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

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
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwGeolocation
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class MapsViewHelper extends \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper
{
    use CompileWithRenderStatic;

    /**
     * As this ViewHelper renders HTML, the output must not be escaped.
     *
     * @var bool
     */
    protected $escapeOutput = false;


    /**
     * Initialize arguments
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('longitude', 'float', 'The longitude-value.', false, 8.5709247);
        $this->registerArgument('latitude', 'float', 'The latitude-value.', false, 50.1307615);
        $this->registerArgument('address', 'string', 'The address.', false, '');
        $this->registerArgument('zoom', 'int', 'The zoom-factor.', false, 12);
        $this->registerArgument('noMarker', 'bool', 'Do not display a marker on the map.', false, false);
        $this->registerArgument('language', 'string', 'The language for the map.', false, 'de');
        $this->registerArgument('width', 'string', 'The width (CSS-Syntax).', false, '100%');
        $this->registerArgument('height', 'string', 'The height (CSS-Syntax).', false, '300px');
    }


    /**
     * return true, if the given fieldName is NOT in given mandatoryFields (string-list from TypoScript)
     * true if optional
     *
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return string
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ): string {

        $settings = GeneralUtility::getTypoScriptConfiguration('RkwGeolocation');
        $longitude =  $arguments['longitude'];
        $latitude =  $arguments['latitude'];
        $address =  $arguments['address'];
        $zoom =  $arguments['zoom'];
        $noMarker =  $arguments['noMarker'];
        $language =  $arguments['language'];
        $width =  $arguments['width'];
        $height =  $arguments['height'];

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
}
