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

    $accountId = isset($_POST['accountId']) ? $_POST['accountId'] : 0;
    $accessToken = isset($_POST['accessToken']) ? $_POST['accessToken'] : '';

    $funds = isset($_POST['funds']) ? $_POST['funds'] : 0;

    $clientId = helper::clearInt($clientId);
    $funds = helper::clearInt($funds);

    $auth = new auth($dbo);

    if ($clientId != CLIENT_ID) {

        api::printError(ERROR_UNKNOWN, "Error client Id.");
    }

    if (!$auth->authorize($accountId, $accessToken)) {

        api::printError(ERROR_ACCESS_TOKEN, "Error authorization.");
    }

    $result = array("error" => true,
                    "error_code" => ERROR_UNKNOWN);

    $account = new account($dbo, $accountId);
    $result = $account->setBalance($account->getBalance() + $funds);

    if ($result['error'] === false) {

        $result['balance'] = $account->getBalance();

        $refill = new refill($dbo);
        $refill->setRequestFrom($accountId);

        $refill->addToHistory($accountId, 0, $funds);
    }

    echo json_encode($result);
    exit;
}
