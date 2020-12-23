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

class Server_Api_Issues_Edit
{
    public $access = '*';

    public $params = array(
        'issueId' => array( 'type' => 'int', 'required' => true ),
        'name' => 'string',
        'values' => 'array'
    );

    public function run( $issueId, $name, $values )
    {
        $helper = new Server_Api_Helpers_Issues();
        $values = $helper->extractValues( $values );

        $issueManager = new System_Api_IssueManager();
        $issue = $issueManager->getIssue( $issueId );

        $validator = new System_Api_Validator();
        $validator->setProjectId( $issue[ 'project_id' ] );

        if ( $name != null )
            $validator->checkString( $name, System_Const::ValueMaxLength );

        $typeManager = new System_Api_TypeManager();
        $type = $typeManager->getIssueTypeForIssue( $issue );
        $rows = $issueManager->getAllAttributeValuesForIssue( $issue );

        $attributes = array();
        foreach ( $rows as $row )
            $attributes[ $row[ 'attr_id' ] ] = $row;

        $oldValues = array();
        foreach ( $rows as $row )
            $oldValues[ $row[ 'attr_id' ] ] = $row[ 'attr_value' ];

        $helper->checkValues( $values, $attributes, $validator );

$debug = System_Core_Application::getInstance()->getDebug();

        foreach ( $oldValues as $id => $oldValue ) {
            if ( !isset( $values[ $id ] ) ) {
                $attribute = $attributes[ $id ];
                $validator->checkAttributeValue( $attribute[ 'attr_def' ], $oldValue );
            }
        }

        $orderedValues = $helper->getOrderedValues( $values, $type );

        $lastStampId = null;

        if ( $name != null ) {
            $stampId = $issueManager->renameIssue( $issue, $name );
            if ( $stampId != false )
                $lastStampId = $stampId;
        }

        $subscriptionManager = new System_Api_SubscriptionManager();
	$sessionManager = new System_Api_SessionManager();

        foreach ( $orderedValues as $row ) {
            $stampId = $issueManager->setValue( $issue, $attributes[ $row[ 'attr_id' ] ], $row[ 'attr_value' ] );
            if ( $stampId != false )
                $lastStampId = $stampId;

            if (substr( $attributes[ $row[ 'attr_id' ] ][ 'attr_def' ], 0, 4 ) === "USER") {
                $oldUser = $oldValues[ $row[ 'attr_id' ] ];
		$newUser = $row[ 'attr_value' ];

		$principal = System_Api_Principal::getCurrent();

                if ($issue[ 'created_by' ] !== $oldUser) {
                    $user = $sessionManager->getUserByUserName($oldUser);

                    $userPrincipal = new System_Api_Principal( $user );
                    System_Api_Principal::setCurrent( $userPrincipal );
                    $issueUser = $issueManager->getIssue( $issueId, System_Api_IssueManager::NoCachedValue );

                    $subscription = $subscriptionManager->getSubscriptionForIssue( $issueUser );
                    $subscriptionManager->deleteSubscription( $subscription);
		}

                if ($issue[ 'created_by' ] !== $newUser) {
                    $user = $sessionManager->getUserByUserName($newUser);
                    $userPrincipal = new System_Api_Principal( $user );
                    System_Api_Principal::setCurrent( $userPrincipal );
                    $issueUser = $issueManager->getIssue( $issueId, System_Api_IssueManager::NoCachedValue );
                    $subscriptionManager->addSubscription( $issueUser );
                }

                System_Api_Principal::setCurrent( $principal );

            }

        }

        $result[ 'issueId' ] = $issueId;
        $result[ 'stampId' ] = $lastStampId;

        return $result;
    }
}

System_Bootstrap::run( 'Server_Api_Application', 'Server_Api_Issues_Edit' );
