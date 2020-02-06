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

const { app } = require( 'electron' );

const fs = require( 'fs' );
const path = require( 'path' );
const zlib = require( 'zlib' );

const dataPath = initializeDataPath();

function loadJSON( filePath, callback ) {
  fs.readFile( filePath, 'utf8', ( error, text ) => {
    if ( error != null )
      return callback( error, null );

    let result;
    try {
      result = JSON.parse( text );
    } catch ( error ) {
      return callback( error, null );
    }

    callback( null, result );
  } );
}

function saveJSON( filePath, data, callback ) {
  const text = JSON.stringify( data, null, 2 );

  writeFileSafe( filePath, text, 'utf8', callback );
}

function packJSON( data, callback ) {
  const text = JSON.stringify( data );
  const buffer = Buffer.from( text, 'utf8' );

  zlib.gzip( buffer, callback );
}

function unpackJSON( buffer, callback ) {
  zlib.gunzip( buffer, ( error, unpacked ) => {
    if ( error != null )
      return callback( error, null );

    const text = unpacked.toString( 'utf8' );

    let result;
    try {
      result = JSON.parse( text );
    } catch ( error ) {
      return callback( error, null );
    }

    callback( null, result );
  } );
}

function writeFileSafe( filePath, data, options, callback ) {
  const tempPath = filePath + '.tmp';

  fs.writeFile( tempPath, data, options, error => {
    if ( error != null )
      return callback( error );

    fs.rename( tempPath, filePath, error => {
      if ( error != null ) {
        fs.unlink( tempPath, () => {
          callback( error );
        } );
      } else {
        callback( null );
      }
    } );
  } );
}

function initializeDataPath() {
  let dataPath;

  if ( process.platform == 'win32' )
    dataPath = path.join( process.env.LOCALAPPDATA, 'WebIssues Client\\2.0' )
  else
    dataPath = path.join( app.getPath( 'appData' ), 'webissues-2.0' );

  app.setPath( 'userData', path.join( dataPath, 'browser' ) );

  return dataPath;
}

module.exports = {
  dataPath,
  loadJSON,
  saveJSON,
  packJSON,
  unpackJSON,
  writeFileSafe
};
