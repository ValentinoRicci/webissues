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

if ( !defined( 'WI_VERSION' ) ) die( -1 );

class Common_Errors_Setup extends System_Web_Component
{
    protected function __construct()
    {
        parent::__construct();
    }

    protected function execute()
    {
        $this->view->setDecoratorClass( 'Common_Window' );
        $this->view->setSlot( 'window_size', 'small' );

        $application = System_Core_Application::getInstance();
        $error = $application->getFatalError();

        switch ( $error->getCode() ) {
            case System_Core_SetupException::SiteConfigNotFound:
                $this->view->setSlot( 'page_title', $this->t( 'title.WelcomeToWebIssues' ) );
                $this->infoMessage = $this->t( 'prompt.WelcomeToWebIssues' );
                $this->alertClass = 'info';
                $this->linkUrl = '/setup/install.php';
                $this->linkName = $this->t( 'cmd.ConfigureDatabase' );
                break;

            case System_Core_SetupException::DatabaseNotCompatible:
                $this->view->setSlot( 'page_title', $this->t( 'title.IncompatibleDatabase' ) );
                $this->infoMessage = $this->t( 'error.IncompatibleDatabase' );
                $this->alertClass = 'danger';
                break;

            case System_Core_SetupException::DatabaseNotUpdated:
                $this->view->setSlot( 'page_title', $this->t( 'title.UpdateRequired' ) );
                $this->infoMessage = $this->t( 'prompt.UpdateRequired' );
                $this->alertClass = 'info';
                $this->linkUrl = '/setup/update.php';
                $this->linkName = $this->t( 'cmd.UpdateDatabase' );
                break;
        }
   }
}
