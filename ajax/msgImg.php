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

    if (!$auth->authorize(auth::getCurrentUserId(), auth::getAccessToken())) {

        header('Location: /');
    }

    if (!empty($_FILES['userfile']['tmp_name'])) {

        $result = array("error" => true);

        if ($_FILES["userfile"]["size"] < 20 * 1024 * 1024) {

            $imgLib = new imglib($dbo);
            $result = $imgLib->createChatImg($_FILES['userfile']['tmp_name'], $_FILES['userfile']['name']);
        }

        echo json_encode($result);
        exit;
    }

