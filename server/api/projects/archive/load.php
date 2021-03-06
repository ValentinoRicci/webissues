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

require_once( '../../../../system/bootstrap.inc.php' );

class Server_Api_Projects_Archive_Load
{
    public $access = 'anonymous';

    public $params = array(
        'projectId' => array( 'type' => 'int', 'required' => true ),
        'description' => array( 'type' => 'bool', 'default' => false ),
        'html' => array( 'type' => 'bool', 'default' => false )
    );

    public function run( $projectId, $description, $html )
    {
        $projectManager = new System_Api_ProjectManager();
        $project = $projectManager->getArchivedProject( $projectId );

        $resultDetails[ 'id' ] = $project[ 'project_id' ];
        $resultDetails[ 'name' ] = $project[ 'project_name' ];

        $result[ 'details' ] = $resultDetails;

        if ( $html )
            System_Web_Base::setLinkMode( System_Web_Base::RouteLinks );

        $result[ 'row' ] = $project;

        if ( $description ) {
            if ( $project[ 'descr_id' ] != null ) {
                $descr = $projectManager->getProjectDescription( $project );

                $resultDescription[ 'modifiedBy' ] = $descr[ 'modified_user' ];
                $resultDescription[ 'modifiedDate' ] = $descr[ 'modified_date' ];

                $resultDescription[ 'text' ] = $this->convertText( $descr[ 'descr_text' ], $html, $descr[ 'descr_format' ] );
                $resultDescription[ 'format' ] = $descr[ 'descr_format' ];

                $result[ 'description' ] = $resultDescription;
            } else {
                $result[ 'description' ] = null;
            }
        }

        return $result;
    }

    private function convertText( $text, $html, $format = System_Const::PlainText )
    {
        if ( $html ) {
            if ( $format == System_Const::TextWithMarkup )
                return System_Web_MarkupProcessor::convertToHtml( $text, $prettyPrint );
            else
                return System_Web_LinkLocator::convertToHtml( $text );
        } else {
            return $text;
        }
    }
}

System_Bootstrap::run( 'Server_Api_Application', 'Server_Api_Projects_Archive_Load' );
