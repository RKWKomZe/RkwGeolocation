<?php

namespace RKW\RkwGeolocation\Controller;

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
 * GeolocationController
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwGeolocation
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class GeolocationController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{

    /**
     * action test
     * for testing only. Do not use for applications :-)
     *
     * @return void
     */
    public function testAction()
    {


        /** @var \RKW\RkwGeolocation\Service\Geolocation $geolocationService */
        //	$geolocationService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwGeolocation\\Service\\Geolocation');

        //	$geolocationService->setLongitude(50.9169829);
        //	$geolocationService->setLatitude(8.7079389);

        //	$geolocationService->setPostalCode("35083");

        //	$result = $geolocationService->getGeoData();

        //	\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($result);

    }

}
