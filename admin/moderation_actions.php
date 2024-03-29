<?php

    /*!
     * ifsoft.co.uk admin engine v1.1
     *
     * http://ifsoft.com.ua, http://ifsoft.co.uk
     * raccoonsquare@gmail.com
     *
     * Copyright 2012-2019 Demyanchuk Dmitry (raccoonsquare@gmail.com)
     */

    include_once($_SERVER['DOCUMENT_ROOT']."/core/init.inc.php");

    if (!admin::isSession()) {

        header("Location: /admin/login.php");
        exit;
    }

    $stats = new stats($dbo);
    $admin = new admin($dbo);
    $report = new report($dbo);

    if (isset($_GET['account_id'])) {

        $action = isset($_GET['act']) ? $_GET['act'] : '';

        $accountId = isset($_GET['account_id']) ? $_GET['account_id'] : 0;
        $accessToken = isset($_GET['access_token']) ? $_GET['access_token'] : '';

        $accountId = helper::clearInt($accountId);

        if ($accessToken === admin::getAccessToken() && !APP_DEMO) {

            $moderator = new moderator($dbo);

            switch ($action) {

                case "photo_approve": {

                    $moderator->approvePhoto($accountId);

                    break;
                }

                case "photo_reject": {

                    $moderator->rejectPhoto($accountId);

                    break;
                }

                case "cover_approve": {

                    $moderator->approveCover($accountId);

                    break;
                }

                case "cover_reject": {

                    $moderator->rejectCover($accountId);

                    break;
                }

                default: {

                    break;
                }
            }

            unset($moderator);
        }

    } else {

        header("Location: /admin/main.php");
        exit;
    }
