<?php
/**************************************************************************
* This file is part of the WebIssues Server program
* Copyright (C) 2006 Michał Męciński
* Copyright (C) 2007-2020 WebIssues Team
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

class Server_Api_Reports_List
{
    public $access = '*';

    public $params = array(
        'publicReports' => array( 'type' => 'bool', 'default' => false ),
        'personalReports' => array( 'type' => 'bool', 'default' => false )
    );

    public function run( $publicReports, $personalReports )
    {
        $serverManager = new System_Api_ServerManager();
        if ( $serverManager->getSetting( 'email_engine' ) == null )
            throw new System_Api_Error( System_Api_Error::AccessDenied );

        if ( $publicReports ) {
            $alertManager = new System_Api_AlertManager();
            $reportRows = $alertManager->getPublicReports();

            $result[ 'publicReports' ] = array();

            foreach ( $reportRows as $report )
                $result[ 'publicReports' ][] = $this->processReport( $report );
        }

        if ( $personalReports ) {
            $alertManager = new System_Api_AlertManager();
            $reportRows = $alertManager->getPersonalReports();

            $result[ 'personalReports' ] = array();

            foreach ( $reportRows as $report )
                $result[ 'personalReports' ][] = $this->processReport( $report );
        }

        return $result;
    }

    private function processReport( $report )
    {
        $resultReport = array();
        $resultReport[ 'id' ] = $report[ 'alert_id' ];
        if ( $report[ 'view_name' ] != null )
            $resultReport[ 'view' ] = $report[ 'type_name' ] . " \xE2\x80\x94 " . $report[ 'view_name' ];
        else
            $resultReport[ 'view' ] = $report[ 'type_name' ];
        if ( $report[ 'folder_name' ] != null && $report[ 'project_name' ] != null )
            $resultReport[ 'location' ] = $report[ 'project_name' ] . " \xE2\x80\x94 " . $report[ 'folder_name' ];
        else if ( $report[ 'project_name' ] != null )
            $resultReport[ 'location' ] = $report[ 'project_name' ];
        else
            $resultReport[ 'location' ] = null;
        $resultReport[ 'type' ] = $report[ 'alert_type' ];
        $resultReport[ 'frequency' ] = $report[ 'alert_frequency' ];
        return $resultReport;
    }
}

System_Bootstrap::run( 'Server_Api_Application', 'Server_Api_Reports_List' );
