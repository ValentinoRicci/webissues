<?php
/**************************************************************************
* This file is part of the WebIssues Server program
* Copyright (C) 2006 Michał Męciński
* Copyright (C) 2007-2017 WebIssues Team
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU Affero General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU Affero General Public License for more details.
*
* You should have received a copy of the GNU Affero General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
**************************************************************************/

require_once( '../../../system/bootstrap.inc.php' );

class Server_Api_Account_Edit
{
    public $access = '*';

    public $params = array(
        'email' => array( 'type' => 'string', 'default' => '' ),
        'language' => array( 'type' => 'string', 'default' => '' )
    );

    public function run( $email, $language )
    {
        $userId = System_Api_Principal::getCurrent()->getUserId();

        $validator = new System_Api_Validator();
        $validator->checkString( $email, System_Const::ValueMaxLength, System_Api_Validator::AllowEmpty );
        $validator->checkString( $language, System_Const::ValueMaxLength, System_Api_Validator::AllowEmpty );
        $validator->checkPreference( 'email', $email );
        $validator->checkPreference( 'language', $language );

        $userManager = new System_Api_UserManager();
        $user = $userManager->getUser( $userId );

        $changed = $userManager->editUser( $user, $user[ 'user_name' ], $user[ 'user_login' ], $email, $language );

        $result[ 'userId' ] = $userId;
        $result[ 'changed' ] = $changed;

        return $result;
    }
}

System_Bootstrap::run( 'Server_Api_Application', 'Server_Api_Account_Edit' );
