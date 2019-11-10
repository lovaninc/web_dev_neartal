<?php

/*!
 * ifsoft.co.uk v1.0
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

    $username = isset($_POST['username']) ? $_POST['username'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    $clientId = helper::clearInt($clientId);
    $appType = helper::clearInt($appType);

    $lang = helper::clearText($lang);
    $lang = helper::escapeText($lang);

    $username = helper::clearText($username);
    $username = helper::escapeText($username);

    $password = helper::clearText($password);
    $password = helper::escapeText($password);

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

    $lang = helper::clearText($lang);
    $lang = helper::escapeText($lang);

    if ($clientId != CLIENT_ID) {

        api::printError(ERROR_UNKNOWN, "Error client Id.");
    }

    $access_data = array();

    $account = new account($dbo);
    $access_data = $account->signin($username, $password);

    unset($account);

    if ($access_data["error"] === false) {

        $account = new account($dbo, $access_data['accountId']);

        switch ($account->getState()) {

            case ACCOUNT_STATE_BLOCKED: {

                break;
            }

            case ACCOUNT_STATE_DISABLED: {

                break;
            }

            default: {

                $auth = new auth($dbo);
                $access_data = $auth->create($access_data['accountId'], $clientId, $appType, $fcm_regId, $lang);

                if ($access_data['error'] === false) {

                    $account = new account($dbo, $access_data['accountId']);
                    $account->setState(ACCOUNT_STATE_ENABLED);
                    $account->setLastActive();
                    $access_data['account'] = array();

                    array_push($access_data['account'], $account->get());
                }

                break;
            }
        }
    }

    echo json_encode($access_data);
    exit;
}
