<?php

/*!
 * ifsoft.co.uk
 *
 * http://ifsoft.com.ua, http://ifsoft.co.uk
 * raccoonsquare@gmail.com
 *
 * Copyright 2012-2019 Demyanchuk Dmitry (raccoonsquare@gmail.com)
 */

include_once($_SERVER['DOCUMENT_ROOT']."/core/init.inc.php");
include_once($_SERVER['DOCUMENT_ROOT']."/config/api.inc.php");

if (!empty($_POST)) {

    $accountId = isset($_POST['accountId']) ? $_POST['accountId'] : 0;
    $accessToken = isset($_POST['accessToken']) ? $_POST['accessToken'] : '';

    $itemId = isset($_POST['itemId']) ? $_POST['itemId'] : 0;

    $gender = isset($_POST['gender']) ? $_POST['gender'] : -1;
    $online = isset($_POST['online']) ? $_POST['online'] : -1;
    $photo = isset($_POST['photo']) ? $_POST['photo'] : -1;
    $proMode = isset($_POST['pro']) ? $_POST['pro'] : -1;
    $ageFrom = isset($_POST['ageFrom']) ? $_POST['ageFrom'] : 13;
    $ageTo = isset($_POST['ageTo']) ? $_POST['ageTo'] : 110;

    $sex_orientation = isset($_POST['sex_orientation']) ? $_POST['sex_orientation'] : 0;

    if ($gender != -1) $gender = helper::clearInt($gender);
    if ($online != -1) $online = helper::clearInt($online);
    if ($photo != -1) $photo = helper::clearInt($photo);
    if ($proMode != -1) $proMode = helper::clearInt($proMode);

    $ageFrom = helper::clearInt($ageFrom);
    $ageTo = helper::clearInt($ageTo);

    $itemId = helper::clearInt($itemId);

    $sex_orientation = helper::clearInt($sex_orientation);

    if ($gender != -1) $gender = helper::clearInt($gender);

    $result = array("error" => true,
                    "error_code" => ERROR_UNKNOWN);

    $auth = new auth($dbo);

    if (!$auth->authorize($accountId, $accessToken)) {

        api::printError(ERROR_ACCESS_TOKEN, "Error authorization.");
    }

    $search = new find($dbo);
    $search->setRequestFrom($accountId);

    $result = $search->preload($itemId, $gender, $online, $photo, $proMode, $ageFrom, $ageTo, $sex_orientation);

    echo json_encode($result);
    exit;
}
