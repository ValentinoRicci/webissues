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
  <BaseForm v-bind:title="$t( 'cmd.DeleteIssue' )" size="small" with-buttons v-on:ok="submit" v-on:cancel="returnToDetails">
    <Prompt path="prompt.DeleteIssue"><strong>{{ name }}</strong></Prompt>
    <Prompt path="prompt.WarningDeleteIssue" alert-class="alert-danger"><strong>{{ $t( 'label.Warning' ) }}</strong></Prompt>
  </BaseForm>
</template>

<script>
export default {
  props: {
    issueId: Number,
    name: String
  },

  methods: {
    submit() {
      this.$form.block();

      this.$ajax.post( '/issues/delete.php', { issueId: this.issueId } ).then( () => {
        this.$store.commit( 'list/setDirty' );
        this.$form.close();
      } ).catch( error => {
        this.$form.error( error );
      } );
    },

    returnToDetails() {
      this.$router.push( 'IssueDetails', { issueId: this.issueId } );
    }
  }
}
</script>
