<!--
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
-->

<template>
  <BaseForm v-bind:title="$t( 'title.WebIssuesSettings' )" size="small" with-buttons v-bind:close-hidden="!isConfigured" v-bind:cancel-hidden="!isConfigured"
            v-on:ok="submit" v-on:cancel="cancel">
    <FormInput ref="baseURL" id="baseURL" v-bind:label="$t( 'label.ServerURL' )" v-bind="$field( 'baseURL' )" v-model="baseURL" v-on:keydown.enter="submit"/>
  </BaseForm>
</template>

<script>
import { Reason } from '@/constants'
import { makeParseError, makeVersionError } from '@/utils/errors'

export default {
  fields() {
    return {
      baseURL: {
        value: this.$client.settings.baseURL != null ? this.$client.settings.baseURL + '/' : '',
        type: String,
        required: true,
        maxLength: 250,
        parse: this.parseURL
      }
    }
  },

  computed: {
    isConfigured() {
      return this.$client.settings.baseURL != null;
    }
  },

  methods: {
    submit() {
      if ( !this.$fields.validate() )
        return;

      if ( !this.$fields.modified() ) {
        this.$form.close();
        return;
      }

      let baseURL = this.baseURL.replace( /\/$/, '' );

      this.$form.block();

      this.$ajax.get( baseURL + '/server/api/info.php' ).then( ( { serverName, serverVersion, responseURL } ) => {
        if ( !this.$client.isSupportedVersion( serverVersion ) )
          throw makeVersionError( serverVersion );

        const match = responseURL.match( /\/server\/api\/info\.php$/ );
        if ( match != null )
          baseURL = responseURL.substr( 0, match.index );

        this.$client.settings.baseURL = baseURL;
        this.$client.settings.serverName = serverName;
        this.$client.settings.serverVersion = serverVersion;

        this.$client.restartClient();
      } ).catch( error => {
        this.$form.unblock();
        this.baseURLError = this.getErrorMessage( error );
        this.$nextTick( () => {
          this.$refs.baseURL.focus();
        } );
      } );
    },

    cancel() {
      this.$form.close();
    },

    parseURL( value ) {
      const matches = /^(https?:\/\/)?[a-z0-9]+([\-\.][a-z0-9]+)*(:[0-9]{1,5})?(\/.*)?$/i.exec( value );
      if ( matches == null )
        throw makeParseError( this.$t( 'error.InvalidURL' ) );
      if ( matches[ 1 ] == null )
        value = 'http://' + value;
      value = value.replace( /\/+(client\/)?(index\.php)?$/, '' );
      return value + '/';
    },

    getErrorMessage( error ) {
      switch ( error.reason ) {
        case Reason.NetworkError:
          return this.$t( 'error.NetworkError' );
        case Reason.APIError:
        case Reason.InvalidResponse:
          return this.$t( 'error.InvalidResponse' );
        case Reason.ServerError:
          if ( error.errorCode == 501 || error.errorCode == 502 )
            return this.$t( 'error.ServerNotConfigured' );
          else
            return this.$t( 'error.ServerError' );
        case Reason.BadRequest:
          return this.$t( 'error.BadRequest' );
        case Reason.UnsupportedVersion:
          return this.$t( 'error.UnsupportedVersion' );
        default:
          return this.$t( 'error.UnknownError' );
      }
    }
  },

  mounted() {
    this.$refs.baseURL.focus();
  }
}
</script>
