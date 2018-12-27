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

$this->document->addStyle([
'href' => '/templates/default/admin/assets/css/jquery-ui.css',
'rel'  => 'stylesheet'
]);
$this->document->addStyle([
'href' => 'https://cdnjs.cloudflare.com/ajax/libs/jstree/3.2.1/themes/default/style.min.css',
'rel'  => 'stylesheet'
]);



$this->document->addScript('http://test.calcn1.ru/templates/default/admin/assets/js/jsgrid/jsgrid.js');
$this->document->addScript('http://test.calcn1.ru/templates/default/admin/assets/js/jquery/jquery-ui.js');
$this->document->addScript('http://test.calcn1.ru/templates/default/admin/assets/js/jquery/js.cookie.js');
$this->document->addScript('https://cdnjs.cloudflare.com/ajax/libs/jstree/3.2.1/jstree.min.js');


//echo "
//print_r($data);
//exit(0);
?>
<div class="grid-mass-actions">
	<div class="dropdown">
		<a class="btn btn-primary lock-on-click" href="<?php echo $data['insert']; ?>" title="<?php echo $data['button_add']; ?>">
			<i class="fa fa-plus fa-fw"></i>
		</a>
		<button class="btn btn-primary dropdown-toggle" type="button" id="groupActionButtons" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" disabled>
			<i class="fa fa-cogs"></i>
		</button>
		<div class="dropdown-menu" aria-labelledby="groupActionButtons">
			<a class="btn btn-block btn-danger" id="batchDelete"><i class="fa fa-trash"></i> Delete Selected</a>
			<!-- <a class="btn btn-block btn-primary"><i class="fa fa-save"></i> Save Selected</a> -->
			<a class="btn btn-block btn-primary" id="batchEnable"><i class="fa fa-toggle-on"></i> Activate Selected</a>
			<a class="btn btn-block btn-primary" id="batchDisable"><i class="fa fa-toggle-off"></i> DeActivate Selected</a>
		</div>
	</div>
	<div class="dropdown">
		<button class="btn btn-primary dropdown-toggle" type="button" id="dropdownHideShow" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
			<i class="fa fa-eye"></i>
		</button>
		<div class="dropdown-menu" id="shoHideTroggler" aria-labelledby="dropdownHideShow">
		</div>
	</div>
	<button class="btn btn-primary" id="groupEdit" type="button">
		Group Edit
	</button>
	<button class="btn btn-primary" id="groupSave" type="button">
		Group Save
	</button>
	<button class="btn btn-primary" id="groupCancel" type="button">
		Cancel Group Edit
	</button>
</div>
<div class="grid-container-main" id="resizable-container">
	<div id="resizable-left" class="grid-col-left">
		<div id="<?php echo $data['table_id']; ?>"></div>
	</div>

	<div id="grid_action" class="grid-col-right">
	<div id="grid_action_inner" class="">
		<ul class="nav nav-tabs" id="actionsTab" role="tablist">
			<li class="nav-item">
				<a class="nav-link active" id="hierarchy-action-tab" data-toggle="tab" href="#hierarchy-action" role="tab" aria-controls="hierarchy" aria-selected="true"><i class="fa fa-sitemap"></i> Hierarchy</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" id="detail-action-tab" data-toggle="tab" href="#detail-action" role="tab" aria-controls="hierarchy" aria-selected="true"><i class="fa fa-file-alt"></i> Detail</a>
			</li>
		</ul>
		<div class="tab-content" id="actionsTabContent">
			<div class="tab-pane fade show active" id="hierarchy-action" role="tabpanel" aria-labelledby="hierarchy-tab">
				<div id="jstree_div"></div>
			</div>
			<div class="tab-pane fade show" id="detail-action" role="tabpanel" aria-labelledby="detail-tab">
			</div>
		</div>
	</div>
	</div>
</div>
<script>
	var JSGRID_EDIT_ROW_DATA_KEY = "JSGridEditRow";
	var JSGRID_ROW_DATA_KEY = "JSGridItem";
	var editRowClass = "jsgrid-edit-row";
	var gridConfig = {};

	// --------- Resizable action bar ---------
	$(function(){
		$("#grid_action").resizable({
			handles: 'w',
			animate: false,
			ghost: true,
			stop:  function(event, ui) {
				var wdiv = ui.size.width;
				$('#resizable-left').width($("#resizable-container").width()-wdiv);
				$.cookie("grid_action_width", wdiv);
			}
		});
	});

	$(window).resize(function(){
		$('#resizable-left').width($("#resizable-container").width()-$("#grid_action").width());
	});


	gridConfig.onRefreshed = function() {
		if($.cookie("grid_action_width")) {
			var grid_action_width = $.cookie("grid_action_width");
			 	$("#grid_action").width(grid_action_width);
				$('#resizable-left').width($("#resizable-container").width()-grid_action_width);
		}
	};


	$(document).ready(function() {
		if($.cookie("grid_action_width")) {
			var grid_action_width = $.cookie("grid_action_width");
		// 	$("#grid_action").animate({
		// 		width: grid_action_width
		// 	}, 100 );
		}
	});
	// --------- End Resizable action bar ---------


	// ---------  JsGrid bar ---------

	var parent_id = 0;
	var children_count = 0;

	dataControler = {
		loadData: function (filter) {

			filter.parent_id = parent_id;
			filter.children_count = children_count;

			var d = $.Deferred();
			$.ajax({
				type: "GET",
				url: abc.grid_settings.url,
				data: filter,
				contentType: "application/json; charset=utf-8",
				dataType: "JSON",
				success: function (data) {
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
		gridConfig.pageSize = abc.grid_settings.pageSize || 7;
		gridConfig.pageButtonCount = abc.grid_settings.pageButtonCount || 3;
		gridConfig.deleteConfirm = abc.grid_settings.deleteConfirm || "Do you really want to delete item?";
		gridConfig.controller = dataControler;

		gridConfig.onItemEditing = function (item) {
		};


		if (abc.grid_settings.control) {
			abc.grid_settings.colModel.push({
				type: "control",
				itemTemplate: function(value, item) {
					var $result = jsGrid.fields.control.prototype.itemTemplate.apply(this, arguments);

					var $customButton = $("<a>").attr({class: "customGridButton"}).html('<i class="fa fa-edit"></i>')
						.click(function(e) {
							window.location.href = abc.grid_settings.actions.edit.href.replace("%ID%", item.id);
							e.stopPropagation();
						});

					return $result.add($customButton);
				},
				width: 100,
			});
		}

		var selectedItems = [];

		abc.grid_settings.colModel.unshift(
			{
				headerTemplate: function() {
					return $("<input>").attr("type", "checkbox")
						.on("change", function () {
							if ($(this).is(":checked")) {
								$('.jsGrid-select').prop('checked', true);
								$('.jsGrid-select').change();
							} else {
								$('.jsGrid-select').prop('checked', false);
								$('.jsGrid-select').change();
							}
						});
				},
				itemTemplate: function(_, item) {
					return $("<input>").attr("type", "checkbox").attr("class", "jsGrid-select")
						.attr("data-select-id", item.id)
						.prop("checked", $.inArray(item, selectedItems) > -1)
						.on("change", function (event) {
							$(this).is(":checked") ? selectItem(item) : unselectItem(item);
						})
						.on("click", function (event) {
							$(this).is(":checked") ? $(this).prop("checked", false) : $(this).prop("checked", true);
						});
				},
				editing: false,
				filtering: false,
				inserting: false,
				selecting: false,
				sorting: false,
				align: "center",
				width: 50
			},
		);

		gridConfig.fields = abc.grid_settings.colModel;

		var selectItem = function(item) {
			if ($.inArray(item, selectedItems) == -1) {
				selectedItems.push(item);
			}
			changeGroupActionButtonsState();
		};

		var unselectItem = function(item) {
			selectedItems = $.grep(selectedItems, function(i) {
				return i !== item;
			});
			changeGroupActionButtonsState();
		};

		var changeGroupActionButtonsState = function() {
			if (selectedItems.length > 0) {
				$('#groupActionButtons').prop('disabled', false);
			} else {
				$('#groupActionButtons').prop('disabled', true);
			}
		};


		gridConfig._eachField_forBatch = function (myfields, callBack) {
			var self = this;
			$.each(myfields, function (index, field) {
				if (field.visible) {
					callBack.call(self, field, index);
				}
			});
		};
		gridConfig.editItems_forBatch = function (items) {
			this.editFields_forBatch = [];
			this._editingRows_forBatch = [];
			for(var i = 0; i < items.length; i++)
			{
				var $row = this.rowByItem(items[i]);
				if ($row.length) {
					this._editRows_forBatch($row, items[i]);
				}
			}
		};

		gridConfig._editRows_forBatch = function ($row) {
			if (!this.editing)
				return;

			var item = $row.data(JSGRID_ROW_DATA_KEY);

			var args = this._callEventHandler(this.onItemEditing, {
				row: $row,
				item: item,
				itemIndex: this._itemIndex(item)
			});

			if (args.cancel)
				return;

			if (this._editingRow) {
				this.cancelEdit();
			}

			var $editRow = this._createEditRow(item);

			var row_num = this.editFields_forBatch.length;
			var rowFields = [];
			for(var fc = 0; fc < this.fields.length; fc++) {
				var thisField = Object.create(this.fields[fc]);
				thisField.mypara = row_num;
				if(this.fields[fc].editControl) thisField.editControl = Object.create(this.fields[fc].editControl);
				rowFields.push(thisField);
			}
			this.editFields_forBatch.push(rowFields);

			this._editingRows_forBatch.push($row);
			$row.hide();
			$editRow.insertBefore($row);
			$row.data(JSGRID_EDIT_ROW_DATA_KEY, $editRow);
		};

		gridConfig.updateItems_forBatch = function (item) {
			var editingRows = [];
			var editedItems = [];
			for(var i = 0; i < this._editingRows_forBatch.length; i++)
			{
				var currentEditingRow = this._editingRows_forBatch[i];
				var currentEditingRowFields = this.editFields_forBatch[i];
				var editedItem ;

				var item = this._getEditedItem_forBatch(currentEditingRowFields);
				// editedItem = this._validateItem(item, this._getEditRow()) ? item : null;
				editedItem = this._validateItem(item, currentEditingRow) ? item : null;

				if (!editedItem)
					return false;
				editingRows.push(currentEditingRow);
				editedItems.push(editedItem);
			}
			for(var i = 0; i < editedItems.length; i++)
			{
				this._updateRow(editingRows[i], editedItems[i]);
			}

			this.editFields_forBatch = [];
			this._editingRows_forBatch = [];
			return;
		};
		gridConfig._finishUpdate = function ($updatingRow, updatedItem, updatedItemIndex) {
			if(this._editingRow) this.cancelEdit();
			else this.cancelEdit_forBatch_row($updatingRow);
			this.data[updatedItemIndex] = updatedItem;

			var $updatedRow = this._createRow(updatedItem, updatedItemIndex);
			$updatingRow.replaceWith($updatedRow);
			return $updatedRow;
		};
		gridConfig._getEditedItem_forBatch = function (myfields) {
			var result = {};
			this._eachField_forBatch(myfields, function (field) {
				if (field.editing) {
					this._setItemFieldValue(result, field, field.editValue());
				}
			});
			return result;
		};
		gridConfig.cancelEdit_forBatch_row = function (updatedRow) {
			if (!updatedRow)
				return;
			updatedRow.prev("tr." + this.editRowClass).remove();
			updatedRow.show();
		};
		gridConfig.cancelEdit_forBatch = function (rowItems) {
			for(var i = 0; i < rowItems.length; i++)
			{
				var $row = this.rowByItem(rowItems[i]);
				if ($row.length) {
					this.cancelEdit_forBatch_row($row);
				}
			}
		};
		gridConfig.rowClick = function (args) {
			getItemDetails(args.item.id);
			if ($('.jsGrid-select[data-select-id="'+args.item.id+'"]').prop("checked")) {
				$('.jsGrid-select[data-select-id="' + args.item.id + '"]').prop("checked", false);
				unselectItem(args.item);
			} else {
				$('.jsGrid-select[data-select-id="' + args.item.id + '"]').prop("checked", true);
				selectItem(args.item);
			}
		};

		$("#groupEdit").on("click", function () {
			$("#groupEdit").prop("disabled", true);
			$("#groupSave").prop("disabled", false);
			$("#groupCancel").prop("disabled", false);
			$("#" + abc.grid_settings.table_id).jsGrid("fieldOption", 6, "visible", false);
			$("#" + abc.grid_settings.table_id).jsGrid("fieldOption", 0, "visible", false);
			var rows = $("#" + abc.grid_settings.table_id).jsGrid("option", "data");
			$("#" + abc.grid_settings.table_id).jsGrid("editItems_forBatch", rows);
		});

		$("#groupSave").on('click', function() {
			if($("#" + abc.grid_settings.table_id).jsGrid("updateItems_forBatch")){
				$("#" + abc.grid_settings.table_id).jsGrid("fieldOption", 6, "visible", true);
				$("#" + abc.grid_settings.table_id).jsGrid("fieldOption", 0, "visible", true);
				$("#groupEdit").prop('disabled', false);
				$("#groupSave").prop('disabled', true);
				$("#groupCancel").prop('disabled', true);
			}
		});
		$("#groupCancel").on('click', function () {
			var rows = $("#" + abc.grid_settings.table_id).jsGrid("option", "data");
			$("#" + abc.grid_settings.table_id).jsGrid("cancelEdit_forBatch", rows);
			$("#" + abc.grid_settings.table_id).jsGrid("fieldOption", 6, "visible", true);
			$("#" + abc.grid_settings.table_id).jsGrid("fieldOption", 0, "visible", true);
			$("#groupEdit").prop('disabled', false);
			$("#groupSave").prop('disabled', true);
			$("#groupCancel").prop('disabled', true);
		});

		$('#batchDelete').on('click', function () {
			var ids = [];
			for (var i=0; i<selectedItems.length; i++) {
				ids.push(selectedItems[i].id);
			}
			$.ajax({
				url: abc.grid_settings.url,
				type: 'DELETE',
				data: {
					id: ids.join(','),
				},
				success: function(result) {
					$('.jsGrid-select').prop('checked', false);
					$('.jsGrid-select').change();
					$("#" + abc.grid_settings.table_id).jsGrid("loadData");
				},
				fail: function () {
					$('.jsGrid-select').prop('checked', false);
					$('.jsGrid-select').change();
				},
			});
		});

		$('#batchDisable').on('click', function () {
			for (var i=0; i<selectedItems.length; i++) {
				selectedItems[i].status = 0;
				$.ajax({
					url: abc.grid_settings.url,
					type: 'PUT',
					data: selectedItems[i],
					success: function (result) {
						$('.jsGrid-select').prop('checked', false);
						$('.jsGrid-select').change();
						$("#" + abc.grid_settings.table_id).jsGrid("loadData");
					},
					fail: function () {
						$('.jsGrid-select').prop('checked', false);
						$('.jsGrid-select').change();
						$("#" + abc.grid_settings.table_id).jsGrid("loadData");
					},
				});
			}
		});

		$('#batchEnable').on('click', function () {
			for (var i=0; i<selectedItems.length; i++) {
				selectedItems[i].status = 1;
				$.ajax({
					url: abc.grid_settings.url,
					type: 'PUT',
					data: selectedItems[i],
					success: function (result) {
						$('.jsGrid-select').prop('checked', false);
						$('.jsGrid-select').change();
						$("#" + abc.grid_settings.table_id).jsGrid("loadData");
					},
					fail: function () {
						$('.jsGrid-select').prop('checked', false);
						$('.jsGrid-select').change();
						$("#" + abc.grid_settings.table_id).jsGrid("loadData");
					},
				});
			}
		});

		$(document).ready(function () {
			$("#groupSave").prop('disabled', true);
			$("#groupCancel").prop('disabled', true);
		});


		$("#" + abc.grid_settings.table_id).jsGrid(gridConfig);

	});
	// --------- End of JsGrid bar ---------

	// ---------  JsTree ---------

	$(function() {
		$.ajax({
			async : true,
			type : "GET",
			url : abc.grid_settings.tree_url,
			dataType : "json",

			success : function(json) {
				createJSTrees(json);
			},

			error : function(xhr, ajaxOptions, thrownError) {
				alert(xhr.status);
				alert(thrownError);
			}
		});
	});

	function createJSTrees(jsonData) {
		$('#jstree_div').jstree({
			"core" : {
				"data" : jsonData
			},
			"plugins" : [
				"state",
				"sort",
			],
		});

		$('#jstree_div').on('changed.jstree', function (e, data) {
				var i, j, r = [];
				for(i = 0, j = data.selected.length; i < j; i++) {
					r.push(data.instance.get_node(data.selected[i]));
				}

			if (r[0]) {
				parent_id = r[0].id;
				children_count = r[0].children.length;
				$("#" + abc.grid_settings.table_id).jsGrid("loadData");
			}
			})
	}

	// ---------  End JsTree ---------


	$(document).ready(function () {
		for (var i=0; i<abc.grid_settings.colModel.length; i++) {
			if (abc.grid_settings.colModel[i].title && abc.grid_settings.colModel[i].name) {
				if($.cookie(abc.grid_settings.table_id+"_"+abc.grid_settings.colModel[i].name+"_visible") != undefined) {
					var visible = $.cookie(abc.grid_settings.table_id+"_"+abc.grid_settings.colModel[i].name+"_visible");
					if (visible == "true") {
						var $hideShowButton = col_visible(i);
					} else {
						var $hideShowButton = col_invisible(i);
						$("#" + abc.grid_settings.table_id).jsGrid("fieldOption", abc.grid_settings.colModel[i].name, "visible", false);
					}
				} else {
					var $hideShowButton = col_visible(i);
				}
				$hideShowButton.appendTo($('#shoHideTroggler'));
			}
		}

		$('.hideColumn').on('click', function () {
			var i = $(this).data('column-id');
			if ($("#" + abc.grid_settings.table_id).jsGrid("fieldOption", $(this).data('column-name'), "visible")) {
				$("#" + abc.grid_settings.table_id).jsGrid("fieldOption", $(this).data('column-name'), "visible", false);
				$(this).html('<i class="fa fa-toggle-off"></i> '+abc.grid_settings.colModel[i].title);
				$(this).removeClass("btn-success");
				$(this).addClass("btn-secondary");
				$.cookie(abc.grid_settings.table_id+"_"+abc.grid_settings.colModel[i].name+"_visible", false);
			} else {
				$("#" + abc.grid_settings.table_id).jsGrid("fieldOption", $(this).data('column-name'), "visible", true);
				$(this).html('<i class="fa fa-toggle-on"></i> '+abc.grid_settings.colModel[i].title);
				$(this).removeClass("btn-secondary");
				$(this).addClass("btn-success");
				$.cookie(abc.grid_settings.table_id+"_"+abc.grid_settings.colModel[i].name+"_visible", true);
			}
		});
	});

	function col_visible(i) {
		return $("<a>").attr({class: "hideColumn btn btn-block btn-success"})
			.attr("data-column-name", abc.grid_settings.colModel[i].name)
			.attr("data-column-id", i)
			.html('<i class="fa fa-toggle-on"></i> '+abc.grid_settings.colModel[i].title);
	}
	function col_invisible(i) {
		return $("<a>").attr({class: "hideColumn btn btn-block btn-secondary"})
			.attr("data-column-name", abc.grid_settings.colModel[i].name)
			.attr("data-column-id", i)
			.html('<i class="fa fa-toggle-off"></i> '+abc.grid_settings.colModel[i].title);
	}

	var getItemDetails = function (id) {
		var data = {
			id: id,
			getDetails: true,
		};
		$('#detail-action').html('Loading...');
		$.ajax({
			url: abc.grid_settings.url,
			type: 'GET',
			data: data,
			success: function (result) {
				$('#detail-action').html(result);
			},
		});
	};

</script>