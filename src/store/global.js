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

import { Access } from '@/constants'

const UpdateInterval = 5 * 60 * 1000; // 5 minutes

export default function makeGlobalModule( baseURL, initialState, ajax ) {
  return {
    namespaced: true,
    state: makeState( baseURL, initialState ),
    getters: makeGetters(),
    mutations: makeMutations(),
    actions: makeActions( ajax )
  };
}

function makeState( baseURL, { serverName, serverVersion, serverUUID, userId, userName, userAccess } ) {
  return {
    baseURL,
    serverName,
    serverVersion,
    serverUUID,
    userId,
    userName,
    userAccess,
    userEmail: null,
    projects: [],
    types: [],
    users: [],
    settings: {},
    languages: [],
    lastUpdate: null,
    dirty: false
  };
}

function makeGetters() {
  return {
    isAuthenticated( state ) {
      return state.userId != 0;
    },
    isAdministrator( state ) {
      return state.userAccess == Access.AdministratorAccess;
    },
    canManageProjects( state, getters ) {
      return getters.isAdministrator || state.projects.some( p => p.access == Access.AdministratorAccess );
    },
    checkUpdate( state ) {
      return () => {
        return state.dirty || state.lastUpdate == null || ( Date.now() - state.lastUpdate ) >= UpdateInterval;
      };
    }
  };
}

function makeMutations() {
  return {
    setDirty( state ) {
      state.dirty = true;
    },
    setData( state, { serverName, serverVersion, serverUUID, userId, userName, userAccess, userEmail, projects, types, users, settings, languages } ) {
      state.serverName = serverName;
      state.serverVersion = serverVersion;
      state.serverUUID = serverUUID;
      state.userId = userId;
      state.userName = userName;
      state.userAccess = userAccess;
      state.userEmail = userEmail;
      state.projects = projects;
      state.types = types;
      state.users = users;
      state.settings = settings;
      state.languages = languages;
    },
    setInitialView( state, { typeId, viewId } ) {
      const type = state.types.find( t => t.id == typeId );
      if ( type != null )
        type.initialView = viewId;
    },
    setHistoryFilter( state, value ) {
      state.settings.historyFilter = value;
    },
    setServerName( state, value ) {
      state.serverName = value;
    },
    beginUpdate( state ) {
      state.lastUpdate = Date.now();
      state.dirty = false;
    }
  };
}

function makeActions( ajax ) {
  return {
    load( { commit } ) {
      commit( 'beginUpdate' );
      return ajax.post( '/global.php' ).then( data => {
        commit( 'setData', data );
      } );
    }
  };
}
