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

    $clientId = isset($_POST['clientId']) ? $_POST['clientId'] : 0;

    $appType = isset($_POST['appType']) ? $_POST['appType'] : 0; // 0 = APP_TYPE_UNKNOWN
    $fcm_regId = isset($_POST['fcm_regId']) ? $_POST['fcm_regId'] : '';
    $lang = isset($_POST['lang']) ? $_POST['lang'] : '';

    $gcm_regId = isset($_POST['gcm_regId']) ? $_POST['gcm_regId'] : '';
    $ios_fcm_regId = isset($_POST['ios_fcm_regId']) ? $_POST['ios_fcm_regId'] : '';

    $facebookId = isset($_POST['facebookId']) ? $_POST['facebookId'] : '';

    $clientId = helper::clearInt($clientId);
    $appType = helper::clearInt($appType);

    $gcm_regId = helper::clearText($gcm_regId);
    $gcm_regId = helper::escapeText($gcm_regId);

    $facebookId = helper::clearText($facebookId);
    $facebookId = helper::escapeText($facebookId);

    $ios_fcm_regId = helper::clearText($ios_fcm_regId);
    $ios_fcm_regId = helper::escapeText($ios_fcm_regId);

    $lang = helper::clearText($lang);
    $lang = helper::escapeText($lang);

    $fcm_regId = helper::clearText($fcm_regId);
    $fcm_regId = helper::escapeText($fcm_regId);

    if (strlen($fcm_regId) == 0) {

        $fcm_regId = $gcm_regId;

        if (strlen($fcm_regId) == 0) {

            $fcm_regId = $ios_fcm_regId;
        }
    }

    $access_data = array("error" => true,
                         "error_code" => ERROR_UNKNOWN);

    $helper = new helper($dbo);

    $accountId = $helper->getUserIdByFacebook($facebookId);

    if ($accountId != 0) {

        $account = new account($dbo, $accountId);
        $account_info = $account->get();

        if ($account_info['state'] == ACCOUNT_STATE_ENABLED) {

            $auth = new auth($dbo);
            $access_data = $auth->create($accountId, $clientId, $appType, $fcm_regId, $lang);

            if ($access_data['error'] === false) {

                $account->setLastActive();
                $access_data['account'] = array();

                array_push($access_data['account'], $account_info);

                if (strlen($gcm_regId) != 0) {

                    $account->setGCM_regId($gcm_regId);
                }

                if (strlen($ios_fcm_regId) != 0) {

                    $account->set_iOS_regId($ios_fcm_regId);
                }
            }
        }
    }

    echo json_encode($access_data);
    exit;
}
