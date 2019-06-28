<?php include($tpl_common_dir . 'action_confirm.tpl'); ?>
<script src="/templates/default/admin/assets/js/tree-table/AcsAbilityTable.umd.min.js"></script>
<link rel="stylesheet" href="/templates/default/admin/assets/css/tree-table/AcsAbilityTable.css">
<style>
	tr.selected_row {
		background: #eee;
	}
	.v-datatable.v-table.theme--light tr.active {
		background: #CDF4FF;
	}
</style>
<div id="app">
	<v-app>
		<v-layout row fill-height>
			<v-flex xs6 fill-height>
				<v-card>
					<v-card-title>
						<v-input>
							<v-form ref="form">
								<v-text-field
										v-model="new_role"
										label="Add Role"
										data-vv-as="Add Role"
										:name="add_role"
										single-line
										v-validate="'required|min:3|max:250'"
										:error-messages="errors.first(add_role)"
								></v-text-field>
							</v-form>
							<v-icon
									small
									class="mr-2"
									@click="addNewRole"
							>
								save
							</v-icon>
							<v-spacer></v-spacer>
							<v-text-field
									v-model="search"
									append-icon="search"
									label="Search"
									single-line
							></v-text-field>
						</v-input>
					</v-card-title>
					<v-data-table
							:headers="roles_table_headers"
							:items="roles"

							v-model="selected"
							item-key="id"
							:search="search"
							class="elevation-1"
					>
						<template v-slot:items="props" :active="props.selected">
							<tr @click="selectRow(props.item) " v-bind:class="{ active: props.selected }">
								<td>
									<v-input v-if="props.item.edit">
										<v-form>
											<v-text-field
													v-model="props.item.title"
													placeholder="Role Name"
													:single-line=true
													data-vv-as="Role Name"
													:name="edit_role"
													v-validate="'required|min:3|max:250'"
													:error-messages="errors.first(edit_role)"
											></v-text-field>
										</v-form>
										<v-icon
												small
												class="mr-2"
												@click="saveItem(props.item)"
										>
											save
										</v-icon>
										<v-icon
												small
												class="mr-2"
												@click="cancelChanges(props.item)"
										>
											cancel
										</v-icon>
									</v-input>

									<span v-else>
						{{ props.item.title }}
					</span>
								</td>
								<td>
									{{ props.item.user_count }}
								</td>
								<td class="justify-center layout px-0">
									<v-icon
											small
											class="mr-2"
											@click="editItem(props.item)"
											v-if="!props.item.edit"
									>
										edit
									</v-icon>
									<v-icon
											small
											@click="deleteItem(props.item)"
											v-if="!props.item.edit"
									>
										delete
									</v-icon>
								</td>
							</tr>
						</template>
					</v-data-table>
				</v-card>
			</v-flex>
			<v-flex xs6>
				<acs-ability-table
						v-if="renderAbilities"
						:rows="abilities_rows"
						:columns="abilities_columns"
						:filter="abilities_filter"
						@change="changeCheckbox"
				/>
			</v-flex>
		</v-layout>
	</v-app>
</div>


<script>

	if (typeof abc.role !== 'undefined') {


		Vue.use(VeeValidate);

		var getAbilities = function (item) {
			if (typeof abc.role.user_model != "undefined") {
				item.user_model = abc.role.user_model;
			}
			var param = item;

			if (typeof vm != "undefined") {
				vm.ability_table_loading = true;
			}

			axios.post(abc.role.get_abilities_url, param)
				.then(function (response) {
					if (typeof vm != "undefined") {
						vm.ability_table_loading = false;
						vm.abilities_rows = response.data;
						vm.forceRerender();
					}
				})
				.catch(function (error) {
					if (typeof vm != "undefined") {
						vm.ability_table_loading = false;
					}
					alert(error);
				});
		}

		var changePermissions = function (item) {
			if (typeof abc.role.user_model != "undefined") {
				item.user_model = abc.role.user_model;
			}
			var param = item;

			axios.post(abc.role.change_permissions_url, param)
				.then(function (response) {
				})
				.catch(function (error) {
					alert(error);
				});
		}

		var addNewRole = function (roleName) {
			var param = {
				'roleName': roleName
			};
			if (typeof abc.role.user_model != "undefined") {
				param.user_model = abc.role.user_model;
			}
			axios.post(abc.role.create_role_url, param)
				.then(function (response) {
				})
				.catch(function (error) {
					alert(error);
				});
		}

		var deleteRole = function (role) {
			var param = {
				'role': role
			};
			axios.post(abc.role.delete_role_url, param)
				.then(function (response) {
				})
				.catch(function (error) {
					alert(error);
				});
		}

		var updateRole = function (role) {
			var param = {
				'role': role
			};
			axios.post(abc.role.update_role_url, param)
				.then(function (response) {
				})
				.catch(function (error) {
					alert(error);
				});
		}

		var getRoles = function () {
			var param = {};
			if (typeof abc.role.user_model != "undefined") {
				param.user_model = abc.role.user_model;
			}
			axios.post(abc.role.get_roles_url, param)
				.then(function (response) {
					if (typeof response.data != "undefined") {
						vm.roles = response.data;
					}
				})
				.catch(function (error) {
					alert(error);
				});
		}


		var vm = new Vue({
				el: '#app',
				components: {
					'acs-ability-table': AcsAbilityTable
				},
				data: {
					selected: [],
					add_role: 'add_role',
					edit_role: 'edit_role',
					new_role: '',
					roles_table_headers: abc.role.roles_table_headers,
					roles: abc.role.roles,
					rules: {
						required: value => !!value || 'Required.',
						counter: value => value.length <= 250 || 'Max 250 characters',
					},
					search: '',
					selected_role: [],

					abilities_columns: abc.role.abilities_table_headers,
					abilities_rows: [{}],
					abilities_page: 0,
					renderAbilities: true,
					abilities_filter: '',
				},
				mounted() {
					if (typeof this.roles[0] != "undefined") {
						this.selectRow(this.roles[0]);
					}
				},
				methods: {
					editItem(item) {
						this.roles_table_headers[0].sortable = false;
						index = this.roles.indexOf(item);
						this._beforeEditingCache = Object.assign({}, this.roles[index]);
						this.roles[index].edit = true;
					},
					saveItem(item) {
						this.roles_table_headers[0].sortable = false;
						index = this.roles.indexOf(item);
						this._beforeEditingCache = null;
						this.roles[index].edit = false;
						updateRole(this.roles[index]);
						this.roles_table_headers[0].sortable = true;
					},
					cancelChanges(item) {
						index = this.roles.indexOf(item);
						Object.assign(this.roles[index], this._beforeEditingCache);
						this._beforeEditingCache = null;
						this.roles_table_headers[0].sortable = true;
					},
					deleteItem(item) {
						if (confirm('Are you sure you want to delete this item?')) {
							deleteRole(item);
							index = this.roles.indexOf(item);
							this.roles.splice(index, 1);
						}
					},
					selectRow(item) {
						this.selected = [];
						this.selected.push(item);
						this.selected_role = item;
						getAbilities(item)
					},
					forceRerender() {
						this.renderAbilities = false;

						this.$nextTick(() => {
							this.renderAbilities = true;
						});
					},
					changeCheckbox(item) {
						changePermissions(item);
					},
					addNewRole() {
						var thisHolder = this;
						this.$validator.validateAll().then((result) => {
							if (result === true) {
								addNewRole(thisHolder.new_role);
								thisHolder.new_role = '';
								thisHolder.$validator.reset();
								getRoles();
							}
						}).catch(() => {
							return false
						});

					}
				},
				computed: {},
			})
		;

	} else {
		alert('Role data is empty!');
	}


</script>
