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

    $gcm_regId = isset($_POST['gcm_regId']) ? $_POST['gcm_regId'] : '';
    $ios_fcm_regId = isset($_POST['ios_fcm_regId']) ? $_POST['ios_fcm_regId'] : '';
    $fcm_regId = isset($_POST['fcm_regId']) ? $_POST['fcm_regId'] : '';

    $gcm_regId = helper::clearText($gcm_regId);
    $gcm_regId = helper::escapeText($gcm_regId);

    $ios_fcm_regId = helper::clearText($ios_fcm_regId);
    $ios_fcm_regId = helper::escapeText($ios_fcm_regId);

    $fcm_regId = helper::clearText($fcm_regId);
    $fcm_regId = helper::escapeText($fcm_regId);

    if (strlen($fcm_regId) == 0) {

        $fcm_regId = $gcm_regId;

        if (strlen($fcm_regId) == 0) {

            $fcm_regId = $ios_fcm_regId;
        }
    }

    $result = array(
        "error" => true,
        "error_code" => ERROR_UNKNOWN);

    $auth = new auth($dbo);

    if (!$auth->authorize($accountId, $accessToken)) {

        api::printError(ERROR_ACCESS_TOKEN, "Error authorization.");
    }

    $auth_id = $auth->getAuthorizationId($accountId, $accessToken);
    $auth->updateAuthorizationId($auth_id, $fcm_regId);

    $result = array(
        "error" => false,
        "error_code" => ERROR_SUCCESS);

    echo json_encode($result);
    exit;
}
