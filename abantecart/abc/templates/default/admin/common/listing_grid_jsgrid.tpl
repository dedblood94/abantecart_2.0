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

<div class="grid-container-main">
	<div id="resizable-left" class="grid-col-left">
		<div id="<?php echo $data['table_id']; ?>"></div>
	</div>

	<div id="grid_action" class="grid-col-right">
	<div id="grid_action_inner" class="shadow-lg p-3 mb-5 bg-white rounded">
		<ul class="nav nav-tabs" id="actionsTab" role="tablist">
			<li class="nav-item">
				<a class="nav-link" id="batch-action-tab" data-toggle="tab" href="#batch-action" role="tab" aria-controls="home" aria-selected="true"><i class="fa fa-cogs"></i> Actions</a>
			</li>
			<li class="nav-item">
				<a class="nav-link active" id="hierarchy-action-tab" data-toggle="tab" href="#hierarchy-action" role="tab" aria-controls="hierarchy" aria-selected="true"><i class="fa fa-sitemap"></i> Hierarchy</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" id="hierarchy-action-tab" data-toggle="tab" href="#visibility-action" role="tab" aria-controls="visibility" aria-selected="true"><i class="fa fa-eye"></i> Visibility</a>
			</li>

		</ul>
		<div class="tab-content" id="actionsTabContent">
			<div class="tab-pane fade show" id="batch-action" role="tabpanel" aria-labelledby="home-tab">
				<a class="btn btn-block btn-danger"><i class="fa fa-trash"></i> Delete All</a>
				<a class="btn btn-block btn-primary"><i class="fa fa-save"></i> Save All</a>
				<a class="btn btn-block btn-primary"><i class="fa fa-toggle-on"></i> Activate All</a>
				<a class="btn btn-block btn-primary"><i class="fa fa-toggle-off"></i> DeActivate All</a>
			</div>
			<div class="tab-pane fade show active" id="hierarchy-action" role="tabpanel" aria-labelledby="hierarchy-tab">
				<div id="jstree_div"></div>
			</div>
			<div class="tab-pane fade show" id="visibility-action" role="tabpanel" aria-labelledby="visibility-tab">

			</div>
		</div>
	</div>
	</div>
</div>
<script>

	// --------- Resizable action bar ---------
	$(function(){
		$("#grid_action").resizable({
			handles: 'w',
			animate: true,
			ghost: true,
			stop:  function(event, ui) {
				var wdiv = ui.size.width;
				$.cookie("grid_action_width", wdiv);
			}
		});
	});

	$(document).ready(function() {
		if($.cookie("grid_action_width")) {
			var grid_action_width = $.cookie("grid_action_width");
			$("#grid_action").animate({
				width: grid_action_width
			}, 100 );
		}
	});
	// --------- End Resizable action bar ---------


	// ---------  JsGrid bar ---------

	var parent_id = 0;

	dataControler = {
		loadData: function (filter) {

			filter.parent_id = parent_id;

			if($.cookie("grid_action_width")) {
				var grid_action_width = $.cookie("grid_action_width");
				$("#grid_action").animate({
					width: grid_action_width
				}, 100 );
			}

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
		gridConfig.pageSize = abc.grid_settings.pageSize || 7;
		gridConfig.pageButtonCount = abc.grid_settings.pageButtonCount || 3;
		gridConfig.deleteConfirm = abc.grid_settings.deleteConfirm || "Do you really want to delete item?";
		gridConfig.controller = dataControler;

		//if (abc.grid_settings.actions.edit.href) {
		//	gridConfig.editItem = function (item) {
		//		window.location.href = abc.grid_settings.actions.edit.href.replace("%ID%", item.id);
		//		return false;
		//	};
		//}

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
							} else {
								$('.jsGrid-select').prop('checked', false);
							}
						});
				},
				itemTemplate: function(_, item) {
					return $("<input>").attr("type", "checkbox").attr("class", "jsGrid-select")
						.prop("checked", $.inArray(item, selectedItems) > -1)
						.on("change", function () {
							$(this).is(":checked") ? selectItem(item) : unselectItem(item);
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
			if (!$.inArray(item, selectedItems)) {
				selectedItems.push(item);
			}
		};

		var unselectItem = function(item) {
			selectedItems = $.grep(selectedItems, function(i) {
				return i !== item;
			});
		};


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
				$hideShowButton.appendTo($('#visibility-action'));
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
</script>