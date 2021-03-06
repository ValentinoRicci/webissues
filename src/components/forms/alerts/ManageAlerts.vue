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
  <BaseForm v-bind:title="$t( 'title.Alerts' )" auto-close save-position>
    <template v-if="isAdministrator">
      <FormSection v-bind:title="$t( 'title.PublicAlerts' )">
        <button type="button" class="btn btn-success" v-on:click="addPublicAlert" v-bind:title="$t( 'cmd.AddPublicAlert' )">
          <span class="fa fa-plus" aria-hidden="true"></span> {{ $t( 'cmd.Add' ) }}
        </button>
      </FormSection>
      <Grid v-if="publicAlerts.length > 0" v-bind:items="publicAlerts" v-bind:columns="columns" v-on:row-click="rowClickPublic"/>
      <Prompt v-else path="info.NoPublicAlerts"/>
    </template>
    <FormSection v-bind:title="$t( 'title.PersonalAlerts' )">
      <button type="button" class="btn btn-success" v-on:click="addPersonalAlert" v-bind:title="$t( 'cmd.AddPersonalAlert' )">
        <span class="fa fa-plus" aria-hidden="true"></span> {{ $t( 'cmd.Add' ) }}
      </button>
    </FormSection>
    <Grid v-if="personalAlerts.length > 0" v-bind:items="personalAlerts" v-bind:columns="columns" v-on:row-click="rowClickPersonal"/>
    <Prompt v-else path="info.NoPersonalAlerts"/>
  </BaseForm>
</template>

<script>
import { mapGetters } from 'vuex'

import { AlertType, AlertFrequency } from '@/constants'

export default {
  props: {
    publicAlerts: Array,
    personalAlerts: Array
  },

  computed: {
    ...mapGetters( 'global', [ 'isAdministrator' ] ),
    columns() {
      return {
        view: { title: this.$t( 'title.Filter' ), class: 'column-medium' },
        location: { title: this.$t( 'title.Location' ), class: 'column-medium' }
      };
    }
  },

  methods: {
    addPublicAlert() {
      this.$router.push( 'AddPublicAlert' );
    },
    addPersonalAlert() {
      this.$router.push( 'AddPersonalAlert' );
    },

    rowClickPublic( rowIndex ) {
      this.$router.push( 'AlertDetails', { alertId: this.publicAlerts[ rowIndex ].id } );
    },
    rowClickPersonal( rowIndex ) {
      this.$router.push( 'AlertDetails', { alertId: this.personalAlerts[ rowIndex ].id } );
    }
  }
}
</script>
