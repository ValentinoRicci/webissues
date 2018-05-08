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

const electron = require( 'electron' );
const { app, BrowserWindow, clipboard, ipcMain, Menu, shell } = electron;

const path = require( 'path' );
const url = require( 'url' );

const { dataPath, loadJSON, saveJSON } = require( './lib/files' );

const config = {
  settings: {
    baseURL: null
  },
  position: {
    x: null,
    y: null,
    width: 1280,
    height: 800,
    maximized: true
  }
};

let configSaved = false;

let mainWindow = null;

app.on( 'ready', () => {
  loadConfiguraton( () => {
    createWindow();
  } );
} );

app.on( 'window-all-closed', () => {
  if ( process.platform != 'darwin' )
    app.quit();
} );

app.on( 'will-quit', event => {
  if ( !configSaved ) {
    event.preventDefault();

    saveConfiguration( () => {
      configSaved = true;
      app.quit();
    } );
  }
} );

app.on( 'activate', () => {
  if ( mainWindow == null )
    createWindow();
} );

ipcMain.on( 'save-settings', ( event, arg ) => {
  config.settings = arg;
  saveConfiguration( () => {} );
} );

ipcMain.on( 'restart-client', ( event, arg ) => {
  config.settings = arg;
  saveConfiguration( () => {
    event.sender.session.clearStorageData( { storages: [ 'cookies' ] }, () => {
      event.sender.send( 'start-client', config.settings );
    } );
  } );
} );

function createWindow() {
  const position = config.position;
  adjustPosition( position );

  mainWindow = new BrowserWindow( {
    x: position.x,
    y: position.y,
    width: position.width,
    height: position.height,
    minWidth: 200,
    minHeight: 120,
    show: !position.maximized
  } );

  if ( position.maximized )
    mainWindow.maximize();

  let pathname;
  if ( process.env.NODE_ENV == 'production' )
    pathname = path.join( __dirname, '../../index.html' );
  else
    pathname = path.join( __dirname, '../index-dev.html' );

  mainWindow.loadURL( url.format( { pathname, protocol: 'file:', slashes: true } ) );

  mainWindow.webContents.on( 'did-finish-load', () => {
    mainWindow.webContents.send( 'start-client', config.settings );
  } );

  mainWindow.webContents.on( 'will-navigate', handleLink );
  mainWindow.webContents.on( 'new-window', handleLink );

  function handleLink( event, url ) {
    event.preventDefault();
    shell.openExternal( url );
  }

  mainWindow.webContents.on( 'context-menu', makeContextMenuHandler() );

  mainWindow.on( 'resize', handleStateChange );
  mainWindow.on( 'move', handleStateChange );

  function handleStateChange() {
    if ( !mainWindow.isMinimized() && !mainWindow.isFullScreen() ) {
      if ( !mainWindow.isMaximized() ) {
        const bounds = mainWindow.getBounds();
        position.x = bounds.x;
        position.y = bounds.y;
        position.width = bounds.width;
        position.height = bounds.height;
      }
      position.maximized = mainWindow.isMaximized();
    }
  }

  mainWindow.on( 'close', () => {
    mainWindow.removeListener( 'resize', handleStateChange );
    mainWindow.removeListener( 'move', handleStateChange );

    config.position = position;
  } );

  mainWindow.on( 'closed', () => {
    mainWindow = null;
  } );
}

function loadConfiguraton( callback ) {
  loadJSON( path.join( dataPath, 'config.json' ), ( error, data ) => {
    if ( error == null && data != null )
      mergeConfig( config, data );

    callback( error, config );
  } );
}

function saveConfiguration( callback ) {
  saveJSON( path.join( dataPath, 'config.json' ), config, callback );
}

function mergeConfig( target, source ) {
  for ( const key in source ) {
    if ( target[ key ] != null && typeof target[ key ] == 'object' && source[ key ] != null && typeof source[ key ] == 'object' )
      mergeConfig( target[ key ], source[ key ] );
    else
      target[ key ] = source[ key ];
  }
}

function adjustPosition( position ) {
  let workArea;
  if ( position.x != null && position.y != null )
    workArea = electron.screen.getDisplayMatching( position ).workArea;
  else
    workArea = electron.screen.getPrimaryDisplay().workArea;

  if ( position.width > workArea.width )
    position.width = workArea.width;
  if ( position.height > workArea.height )
    position.height = workArea.height;

  if ( position.x != null && position.y != null ) {
    if ( position.x >= workArea.x + workArea.width )
      position.x = workArea.x + workArea.width - position.width;
    if ( position.y >= workArea.y + workArea.height )
      position.y = workArea.y + workArea.height - position.height;
  }
}

function makeContextMenuHandler() {
  const inputMenu = Menu.buildFromTemplate( [
    { role: 'undo' },
    { role: 'redo' },
    { type: 'separator' },
    { role: 'cut' },
    { role: 'copy' },
    { role: 'paste' },
    { type: 'separator' },
    { role: 'selectall' },
  ] );

  const selectionMenu = Menu.buildFromTemplate( [
    { role: 'copy' },
  ] );

  const linkMenu = Menu.buildFromTemplate( [
    { label: 'Open link', click: openLink },
    { label: 'Copy link address ', click: copyLink }
  ] );

  let linkURL = null;

  function openLink() {
    shell.openExternal( linkURL );
  }

  function copyLink() {
    clipboard.writeText( linkURL );
  }

  return function contextMenuHandler( event, props ) {
    if ( props.isEditable ) {
      inputMenu.items[ 0 ].enabled = props.editFlags.canUndo;
      inputMenu.items[ 1 ].enabled = props.editFlags.canRedo;
      inputMenu.items[ 3 ].enabled = props.editFlags.canCut;
      inputMenu.items[ 4 ].enabled = props.editFlags.canCopy;
      inputMenu.items[ 5 ].enabled = props.editFlags.canPaste;
      inputMenu.items[ 7 ].enabled = props.editFlags.canSelectAll;
      inputMenu.popup( mainWindow ) ;
    } else if ( props.selectionText != '' ) {
      selectionMenu.popup( mainWindow );
    } else if ( props.linkURL != '' ) {
      let baseURL = props.pageURL;
      const index = baseURL.indexOf( '#' );
      if ( index >= 0 )
        baseURL = baseURL.substr( 0, index );
      if ( !props.linkURL.startsWith( baseURL ) ) {
        linkURL = props.linkURL;
        linkMenu.popup( mainWindow );
      }
    }
  };
}