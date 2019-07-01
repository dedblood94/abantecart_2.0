<?php include($tpl_common_dir . 'action_confirm.tpl'); ?>
<?php echo $tabs; ?>

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
			<v-flex xs12>
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

	if (typeof abc.permissions !== 'undefined') {


		var changePermissions = function (item) {
			if (typeof abc.permissions.user_model != "undefined") {
				item.user_model = abc.permissions.user_model;
			}
			if (typeof abc.permissions.user_id != "undefined") {
				item.user_id = abc.permissions.user_id;
			}
			var param = item;

			console.log(param);
			console.log(abc.permissions.change_permissions_url);
			console.log(abc.permissions.user_id);

			axios.post(abc.permissions.change_permissions_url, param)
				.then(function (response) {
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
					abilities_columns: abc.permissions.abilities_table_headers,
					abilities_rows: abc.permissions.abilities,
					abilities_page: 0,
					renderAbilities: true,
					abilities_filter: '',
				},
				mounted() {
				},
				methods: {
					forceRerender() {
						this.renderAbilities = false;

						this.$nextTick(() => {
							this.renderAbilities = true;
						});
					},
					changeCheckbox(item) {
						changePermissions(item);
					},
				},
				computed: {},
			})
		;

	} else {
		alert('Role data is empty!');
	}


</script>
