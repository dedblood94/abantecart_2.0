<?php
use abc\core\ABC;

$this->document->addStyle([
'href' => '/templates/default/admin/assets/js/jsgrid/jsgrid.css',
'rel'  => 'stylesheet'
]);

$this->document->addStyle([
'href' => '/templates/default/admin/assets/js/jsgrid/jsgrid-theme.css',
'rel'  => 'stylesheet'
]);

$this->document->addStyle([
'href' => '/templates/default/admin/assets/js/jsgrid/abc-grid.css',
'rel'  => 'stylesheet'
]);


$this->document->addScript('http://test.calcn1.ru/templates/default/admin/assets/js/jsgrid/jsgrid.js');


//echo "
//print_r($data);
//exit(0);
?>

	<a class="btn btn-default" id="hideColumn">Show/Hide column</a>

<div id="<?php echo $data['table_id']; ?>">
</div>

	<div id="grid_action">
	<div id="grid_action_inner" class="shadow-lg p-3 mb-5 bg-white rounded">
		а тут у нас активность по гриду!
	</div>
	</div>

<script>
	console.log(abc.grid_settings);

	$('#hideColumn').on('click', function () {
		if ($("#" + abc.grid_settings.table_id).jsGrid("fieldOption", 0, "visible")) {
			$("#" + abc.grid_settings.table_id).jsGrid("fieldOption", "title", "visible", false);
		} else {
			$("#" + abc.grid_settings.table_id).jsGrid("fieldOption", "title", "visible", true);
		}
	});

	dataControler = {
		loadData: function (filter) {
			console.log(filter);

			var d = $.Deferred();
			$.ajax({
				type: "GET",
				url: abc.grid_settings.url,
				data: filter,
				contentType: "application/json; charset=utf-8",
				dataType: "JSON",
				success: function (data) {
					console.log(data);
					d.resolve(data);
				},
				error: function (e) {
					alert("error: " + e.responseText);
				}
			});

			return d.promise();
		},

		insertItem: function (item) {
			return $.ajax({
				type: "POST",
				url: abc.grid_settings.url,
				data: item
			});
		},

		updateItem: function (item) {
			return $.ajax({
				type: "PUT",
				url: abc.grid_settings.url,
				data: item
			});
		},

		deleteItem: function (item) {
			return $.ajax({
				type: "DELETE",
				url: abc.grid_settings.url,
				data: item
			});
		},
	};

	$(function () {

		var gridConfig = {};


		gridConfig.width = abc.grid_settings.width || "100%";

		if (abc.grid_settings.filtering != undefined) {
			gridConfig.filtering = abc.grid_settings.filtering;
		} else {
			gridConfig.filtering = true;
		}
		if (abc.grid_settings.editing != undefined) {
			gridConfig.editing = abc.grid_settings.editing;
		} else {
			gridConfig.editing = true;
		}
		if (abc.grid_settings.sorting != undefined) {
			gridConfig.sorting = abc.grid_settings.sorting;
		} else {
			gridConfig.sorting = true;
		}
		if (abc.grid_settings.paging != undefined) {
			gridConfig.paging = abc.grid_settings.paging;
		} else {
			gridConfig.paging = true;
		}
		if (abc.grid_settings.autoload != undefined) {
			gridConfig.autoload = abc.grid_settings.autoload;
		} else {
			gridConfig.autoload = true;
		}
		if (abc.grid_settings.pageLoading != null) {
			gridConfig.pageLoading = abc.grid_settings.pageLoading;
		} else {
			gridConfig.pageLoading = true;
		}
		gridConfig.pageSize = abc.grid_settings.pageSize || 20;
		gridConfig.pageButtonCount = abc.grid_settings.pageButtonCount || 3;
		gridConfig.deleteConfirm = abc.grid_settings.deleteConfirm || "Do you really want to delete item?";
		gridConfig.controller = dataControler;

		if (abc.grid_settings.actions.edit.href) {
			gridConfig.editItem = function (item) {
				window.location.href = abc.grid_settings.actions.edit.href.replace("%ID%", item.id);
				return false;
			};
		}

		if (abc.grid_settings.control) {
			abc.grid_settings.colModel.push({type: "control"});
		}

		gridConfig.fields = abc.grid_settings.colModel;


		$("#" + abc.grid_settings.table_id).jsGrid(gridConfig);

	});
</script>