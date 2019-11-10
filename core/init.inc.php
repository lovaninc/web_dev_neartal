<?php

    /*!
     * ifsoft engine v1.0
     *
     * http://ifsoft.com.ua, http://ifsoft.co.uk
     * raccoonsquare@gmail.com
     *
     * Copyright 2012-2018 Demyanchuk Dmitry (raccoonsquare@gmail.com)
     */

    error_reporting(E_ALL);

    // If timezone is not installed on the server

    if (!ini_get('date.timezone')) {

        date_default_timezone_set('Europe/London'); // Please set you timezone identifier, see here: http://php.net/manual/en/timezones.php
    }

    include_once($_SERVER['DOCUMENT_ROOT']."/config/db.inc.php");
    include_once($_SERVER['DOCUMENT_ROOT']."/config/constants.inc.php");
    include_once($_SERVER['DOCUMENT_ROOT']."/config/lang.inc.php");

    foreach ($C as $name => $val) {

        define($name, $val);
    }

    foreach ($B as $name => $val) {

        define($name, $val);
    }

    if(!isset($_SESSION)) {

        ini_set('session.cookie_domain', APP_HOST);
        session_set_cookie_params(0, '/', APP_HOST);
        @session_regenerate_id(true);
        session_start();
    }

    $dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME;
    $dbo = new PDO($dsn, DB_USER, DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"));

//    $dbo = new PDO($dsn, DB_USER, DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"));

    spl_autoload_register(function($class)
    {

        $filename = $_SERVER['DOCUMENT_ROOT']."/class/class.".$class.".inc.php";

        if (file_exists($filename)) {

            include_once($filename);
        }
    });

    $helper = new helper($dbo);
    $auth = new auth($dbo);

    if (!auth::isSession() && isset($_COOKIE['user_name']) && isset($_COOKIE['user_password'])) {

        $account = new account($dbo, $helper->getUserId($_COOKIE['user_name']));

        $accountInfo = $account->get();

        if ($accountInfo['error'] === false && $accountInfo['state'] == ACCOUNT_STATE_ENABLED) {

            $auth = new auth($dbo);

            if ($auth->authorize($accountInfo['id'], $_COOKIE['user_password'])) {

                auth::setSession($accountInfo['id'], $accountInfo['username'], $accountInfo['fullname'], $accountInfo['lowPhotoUrl'], $accountInfo['verify'], $accountInfo['pro'], $accountInfo['free_messages_count'], $account->getAccessLevel($accountInfo['id']), $_COOKIE['user_password']);

                $account->setLastActive();

            } else {

                auth::clearCookie();
            }

        } else {

            auth::clearCookie();
        }
    }

