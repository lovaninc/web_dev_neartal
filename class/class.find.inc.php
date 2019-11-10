<?php

/*!
 * ifsoft.co.uk engine v1.0
 *
 * http://ifsoft.com.ua, http://ifsoft.co.uk
 * raccoonsquare@gmail.com
 *
 * Copyright 2012-2018 Demyanchuk Dmitry (raccoonsquare@gmail.com)
 */

class find extends db_connect
{

    private $requestFrom = 0;
    private $language = 'en';

    public function __construct($dbo = NULL)
    {
        parent::__construct($dbo);
    }

    private function getCount($queryText, $gender = 3, $online = 0, $photo = 0, $proMode = 0, $ageFrom = 18, $ageTo = 105, $sexOrientation = 0)
    {
        $queryText = "%".$queryText."%";

        $genderSql = "";

        if ($gender != 3) {

            $genderSql = " AND sex = {$gender}";
        }

        $onlineSql = "";

        if ($online > 0) {

            $current_time = time() - (15 * 60);

            $onlineSql = " AND last_authorize > {$current_time}";
        }

        $photoSql = "";

        if ($photo > 0) {

            $photoSql = " AND lowPhotoUrl <> ''";
        }

        $proModeSql = "";

        if ($proMode > 0) {

            $proModeSql = " AND pro != 0";
        }

        $sexOrientationSql = "";

        if ($sexOrientation > 0) {

            $sexOrientationSql = " AND sex_orientation = {$sexOrientation}";
        }

        $dateSql = " AND u_age >= {$ageFrom} AND u_age <= {$ageTo}";

        $sql = "SELECT count(*) FROM users WHERE state = 0 AND (login LIKE '{$queryText}' OR fullname LIKE '{$queryText}' OR email LIKE '{$queryText}' OR country LIKE '{$queryText}')".$genderSql.$onlineSql.$photoSql.$proModeSql.$sexOrientationSql.$dateSql;

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function lastIndex()
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM users");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn() + 1;
    }

    public function query($queryText = '', $userId = 0, $gender = 3, $online = 0, $photo = 0, $proMode = 0, $ageFrom = 18, $ageTo = 105, $sexOrientation = 0)
    {
        $originQuery = $queryText;

        if ($userId == 0) {

            $userId = $this->lastIndex();
            $userId++;
        }

        $endSql = " ORDER BY regtime DESC LIMIT 20";

        $genderSql = "";

        if ($gender != 3) {

            $genderSql = " AND sex = {$gender}";
        }

        $onlineSql = "";

        if ($online > 0) {

            $current_time = time() - (15 * 60);

            $onlineSql = " AND last_authorize > {$current_time}";
        }

        $photoSql = "";

        if ($photo > 0) {

            $photoSql = " AND lowPhotoUrl <> ''";
        }

        $proModeSql = "";

        if ($proMode > 0) {

            $proModeSql = " AND pro != 0";
        }

        $sexOrientationSql = "";

        if ($sexOrientation > 0) {

            $sexOrientationSql = " AND sex_orientation = {$sexOrientation}";
        }

        $dateSql = " AND u_age >= {$ageFrom} AND u_age <= {$ageTo}";

        $users = array("error" => false,
                       "error_code" => ERROR_SUCCESS,
                       "itemCount" => $this->getCount($originQuery, $gender, $online, $photo, $proMode, $ageFrom, $ageTo, $sexOrientation),
                       "itemId" => $userId,
                       "query" => $originQuery,
                       "items" => array());

        $queryText = "%".$queryText."%";

        $sql = "SELECT id, regtime FROM users WHERE state = 0 AND (login LIKE '{$queryText}' OR fullname LIKE '{$queryText}' OR email LIKE '{$queryText}' OR country LIKE '{$queryText}') AND id < {$userId}".$genderSql.$onlineSql.$photoSql.$proModeSql.$sexOrientationSql.$dateSql.$endSql;
        $stmt = $this->db->prepare($sql);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                while ($row = $stmt->fetch()) {

                    $profile = new profile($this->db, $row['id']);
                    $profile->setRequestFrom($this->requestFrom);

                    array_push($users['items'], $profile->getVeryShort());

                    $users['itemId'] = $row['id'];

                    unset($profile);
                }
            }
        }

        return $users;
    }

    public function preload($itemId = 0, $gender = 3, $online = 0, $photo = 0, $proMode = 0, $ageFrom = 18, $ageTo = 105, $sexOrientation = 0)
    {
        if ($itemId == 0) {

            $itemId = $this->lastIndex();
            $itemId++;
        }

        $endSql = " ORDER BY regtime DESC LIMIT 20";

        $genderSql = "";

        if ($gender != 3) {

            $genderSql = " AND sex = {$gender}";
        }

        $onlineSql = "";

        if ($online > 0) {

            $current_time = time() - (15 * 60);

            $onlineSql = " AND last_authorize > {$current_time}";
        }

        $photoSql = "";

        if ($photo > 0) {

            $photoSql = " AND lowPhotoUrl <> ''";
        }

        $proModeSql = "";

        if ($proMode > 0) {

            $proModeSql = " AND pro != 0";
        }

        $sexOrientationSql = "";

        if ($sexOrientation > 0) {

            $sexOrientationSql = " AND sex_orientation = {$sexOrientation}";
        }

        $dateSql = " AND u_age >= {$ageFrom} AND u_age <= {$ageTo}";

        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "itemId" => $itemId,
                        "items" => array());

        $sql = "SELECT id, regtime FROM users WHERE state = 0 AND id < {$itemId}".$genderSql.$onlineSql.$photoSql.$proModeSql.$sexOrientationSql.$dateSql.$endSql;
        $stmt = $this->db->prepare($sql);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                while ($row = $stmt->fetch()) {

                    $profile = new profile($this->db, $row['id']);
                    $profile->setRequestFrom($this->requestFrom);

                    array_push($result['items'], $profile->getVeryShort());

                    $result['itemId'] = $row['id'];

                    unset($profile);
                }
            }
        }

        return $result;
    }

    public function setLanguage($language)
    {
        $this->language = $language;
    }

    public function getLanguage()
    {
        return $this->language;
    }

    public function setRequestFrom($requestFrom)
    {
        $this->requestFrom = $requestFrom;
    }

    public function getRequestFrom()
    {
        return $this->requestFrom;
    }
}

