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

if ( !defined( 'WI_VERSION' ) ) die( -1 );

class Server_Api_Application extends System_Core_Application
{
    protected $command;

    protected function __construct( $commandClass )
    {
        parent::__construct();

        $this->command = new $commandClass();
    }

    protected function execute()
    {
        if ( $this->request->getRequestMethod() != 'POST' )
            throw new Server_Error( Server_Error::SyntaxError );

        if ( $this->request->getContentType() != 'application/json' )
            throw new Server_Error( Server_Error::SyntaxError );

        $body = $this->request->getPostBody();

        if ( !mb_check_encoding( $body ) )
            throw new System_Api_Error( System_Api_Error::InvalidString );

        if ( preg_match( '/[\x00-\x1f\x7f]/', $body ) )
            throw new System_Api_Error( System_Api_Error::InvalidString );

        $arguments = json_decode( $body, true );

        if ( !is_array( $arguments ) )
            throw new Server_Error( Server_Error::SyntaxError );

        $result = $this->command->run( $arguments );

        if ( $result != null ) {
            $this->response->setContentType( 'application/json' );
            $this->response->setContent( json_encode( $result ) );
        } else {
            $this->response->setStatus( '204 No Content' );
        }
    }

    protected function displayErrorPage()
    {
        $exception = $this->getFatalError();
        $error = Server_Error::getErrorFromException( $exception );
        $content = Server_Error::getErrorResponse( $error, $status );

        $this->response->setStatus( $status );
        $this->response->setContentType( 'application/json' );
        $this->response->setContent( $content );

        $this->response->send();
    }
}