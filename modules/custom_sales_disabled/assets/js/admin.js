"use strict";

$(function () {
	let _currentPath = window.location.pathname;

	const salesItemsViewInfo = () => {
		let _currentPath = window.location.pathname;

		let isSalesItemsView = false;
		let resourcesType = "";
		let resourcesId = "";

		for (let i = 0; i < CUSTOM_SALES_MODULE.item_types.length; i++) {
			const itemType = CUSTOM_SALES_MODULE.item_types[i];
			let pathId = `${itemType}s/${itemType}`;
			let pathIdPosition = _currentPath.indexOf(pathId);
			if (pathIdPosition !== -1) {
				isSalesItemsView = true;
				resourcesType = itemType;
				resourcesId = _currentPath
					.substring(pathIdPosition)
					.split("/")[2];
				break;
			}
		}
		return {isSalesItemsView, resourcesType, resourcesId};
	};

	let {isSalesItemsView, resourcesType, resourcesId} = salesItemsViewInfo();
	if (isSalesItemsView) {
		// Insert add title input with button to the dom
		$(
			`
        <div class="form-group mbot25 items-wrapper input-group-select">
            <div class="input-group input-group-select">
                <input list="custom-sales-title-list" class="form-control custom-sales-title" type="text" placeholder="${CUSTOM_SALES_MODULE.lang.custom_sales_add_item_title}">
                <div class="input-group-btn">
                    <button class="btn btn-default" onclick="customSalesAddTitle()" type="button">
                        <i class="fa fa-plus"></i>
                    </button>
                </div>
                <datalist id="custom-sales-title-list">
                </datalist>
            </div>
        </div>
    `
		).insertAfter(".form-group.items-wrapper.input-group-select");

		customSalesUpdateTitlesList();

		// Update tax amount
		if (
			CUSTOM_SALES_MODULE.settings
				.custom_sales_add_tax_to_item_table_amounts
		) {
			$(document).on(
				"sales-total-calculated",
				customSalesUpdateAmountsWithTax
			);
		}

		if (resourcesType?.length && resourcesId?.length) {
			// Add items title to the view
			getResourcesTitles(resourcesId, resourcesType);
		}
		waitForElementToExist(".ui-sortable").then((element) => {
			// Select the sortable list container
			const sortableContainer = document.querySelector(".ui-sortable");
			customSalesListenToOrderChange(sortableContainer);
		});
	}

	// Special case for proposal converting to invoice/estimate. Ensure the titles are added
	// Listen to the click
	$(document).on(
		"click",
		".proposal_buttons .dropdown-menu-right a[data-template]",
		function () {
			// Clear the html
			$("#convert_helper").html("");
			// Wait for the modal to appear
			waitForElementToExist("#convert_helper .table.items").then(
				(element) => {
					// Then trigger get title
					getResourcesTitles(window.proposal_id, "proposal");
				}
			);
		}
	);

	// Add menu option to convert estimate to deposit invoices
	if (
		_currentPath.indexOf("estimates") !== -1 &&
		CUSTOM_SALES_MODULE.settings.custom_sales_allow_deposit_invoice === 1
	) {
		waitForElementToExist(".horizontal-scrollable-tabs").then((element) => {
			// Select the button containing convert text
			let $estimateToInvoiceBtn = $(
				'button.btn:contains("' +
					CUSTOM_SALES_MODULE.lang.estimate_convert_to_invoice +
					'")'
			);

			if ($estimateToInvoiceBtn?.length) {
				let $ul = $estimateToInvoiceBtn.next("ul.dropdown-menu");
				$ul.prepend(`
                <li>
                    <a href="javascript:;" class="create-deposit-invoice">${CUSTOM_SALES_MODULE.lang.estimate_create_deposit_invoices}</a>
                </li>
                `);

				customSalesInitDepositInvoice();
			}
		});
	}

	// Disable updating deposit invoice if set.
	if (
		_currentPath.indexOf("invoices") !== -1 &&
		CUSTOM_SALES_MODULE.settings
			.custom_sales_disable_updating_deposit_invoice === 1
	) {
		waitForElementToExist(".horizontal-scrollable-tabs").then((element) => {
			if (typeof DEPOSIT_INVOICE_PREVIEW !== "undefined") {
				var invoiceEditAnchors = $("a").filter(function () {
					var link = $(this).attr("href");
					return (
						link &&
						link?.indexOf(
							"invoices/invoice/" + DEPOSIT_INVOICE_PREVIEW
						) !== -1
					);
				});
				invoiceEditAnchors
					.attr("href", "#")
					.attr("disabled", "disabled");

				invoiceEditAnchors.on("click", function (e) {
					e.preventDefault();
				});
			}
		});
	}
});

/**
 *
 * Fetch titles for invoice or estimates and render on the table.
 *
 * @param {string} resourcesId The resources id
 * @param {string} resourcesType The resoureces type (singular) i.e estimate
 */
function getResourcesTitles(resourcesId, resourcesType) {
	requestGetJSON(
		admin_url +
			`custom_sales/custom_sales/get_items_titles/${resourcesId}/${resourcesType}`
	).done(function (response) {
		reorder_items();
		$.each(response.titles, function (i, obj) {
			let order = obj.child_item_order;
			let $row = $('input.order[value="' + order + '"]');
			let template = getTitleInputHtml(obj.title, order);

			if ($row.length) {
				$(template).insertBefore($row.parents("tr.item"));
			} else {
				$("table.items tbody").append(template);
			}
		});
		customSalesReorderItemsTitle();
	});
}

/**
 * Generate HTML string for inserting a row itemables
 *
 * @param {string} title The title of the row
 * @param {mixed} childItemOrder The child item order
 * @returns string
 */
function getTitleInputHtml(title, childItemOrder = "") {
	let totalItemTitles = $("tr.title").length ?? 0;
	let titleId = totalItemTitles + 1;
	let removeIcon = childItemOrder === "" ? "fa-trash" : "fa-times";
	let totalColumns =
		$("table.items thead tr:first-of-type th:not(.hidden)").length ??
		$("table.items thead tr:first-of-type td:not(.hidden)").children
			.length ??
		$("table.items tr:first-of-type td:not(.hidden)").children.length ??
		8;
	totalColumns = totalColumns == 0 ? 8 : totalColumns;

	return `
        <tr class="sortable title">
            <td class="dragger ui-sortable-handle">
            <input type="hidden" class="title-child-item-order" name="custom_sales_items_titles[${titleId}][child_item_order]" value="${childItemOrder}">
            </td>
            <td colspan="${totalColumns - 2}">
                <input name="custom_sales_items_titles[${titleId}][title]" type="text" class="form-control" value="${title}">
            </td>
            <td>
            <button type="button" class="btn btn-danger pull-left" onclick="delete_item(this); return false;"><i class="fa ${removeIcon}"></i></button>
            </td>
        </tr>
        `;
}

/**
 * Add the current title to the items list.
 */
function customSalesAddTitle() {
	let $input = $(".custom-sales-title");
	let title = $input.val();
	let childItemId;
	if (title.length) {
		let template = getTitleInputHtml(title);
		$("table.items tbody").append(template);
		$input.val("");
	}
}

/**
 * Add sibling item order to each title
 */
function customSalesReorderItemsTitle() {
	var rows = $("table.items tbody>tr.title");
	$.each(rows, function () {
		let order = 1;

		// Get next sibling item order
		let $siblingItem = $(this).nextAll("tr.item").first();
		if ($siblingItem) order = $siblingItem.find("input.order").val();

		$(this).find("input.title-child-item-order").val(order);
	});
}

/**
 * Detect change in the sortable and recalculate titles child item order
 */
function customSalesListenToOrderChange(sortableContainer) {
	// Create a new MutationObserver instance
	const observer = new MutationObserver((mutationsList) => {
		for (const mutation of mutationsList) {
			if (mutation.type === "childList") {
				// Check if the mutation is triggered by the addition/removal of elements
				if (
					mutation.addedNodes.length ||
					mutation.removedNodes.length
				) {
					// Perform actions here when the sortable list is rearranged
					customSalesReorderItemsTitle();
					break; // Optional: stop processing mutations after the first one
				}
			}
		}
	});

	// Start observing mutations in the sortable list container
	observer.observe(sortableContainer, {childList: true});
}

/**
 * Update the order of items title
 * @returns void
 */
function customSalesUpdateTitlesOrder() {
	var table = $(".table.items-preview");
	var rows = table.find("tbody tr.title");
	var type = table.attr("data-type"),
		order = [];

	if (!type || !rows?.length) {
		customSalesUpdateTitlesList();
		return false;
	}

	$.each(rows, function () {
		var $sibling = $(this).nextAll("tr:not(.title)").first();
		var i = $sibling?.length ? $sibling.attr("data-item-order") : "";
		order.push([$(this).data("title-id"), i]);
	});

	setTimeout(function () {
		$.post(admin_url + "custom_sales/update_title_order/" + type, {
			data: order,
		});
		customSalesUpdateTitlesList();
	}, 200);
}

/**
 * Get unique list of title list and populate the list
 */
function customSalesUpdateTitlesList() {
	$.get(admin_url + "custom_sales/get_title_list").done((resp) => {
		if (typeof resp !== "object") resp = JSON.parse(resp);
		var selector = "#custom-sales-title-list";
		var options = "";
		if (resp.titles) {
			resp.titles.reverse().forEach((row) => {
				options += `<option>${row.title}</option>`;
			});
		}
		$(selector).html(options);
	});
}

/**
 * Get unique list of title list and populate the list
 */
function customSalesUpdateAmountsWithTax() {
	var table = $(".table.items");
	var rows = table.find("tbody tr:not(.title)");
	$.each(rows, function (i, row) {
		var taxes = $(row).find("select.tax")?.val();
		var quantity = $(row).find("[data-quantity]").val();
		if (quantity === "") {
			quantity = 1;
		}
		var amount = accounting.toFixed(
			$(row).find("td.rate input").val() * quantity,
			app.options.decimal_places
		);
		amount = parseFloat(amount);
		if (taxes?.length) {
			var totalTax = 0;
			taxes.forEach((tax) => {
				tax = parseFloat(tax.split("|").reverse()[0]);
				tax = (tax / 100) * amount;
				totalTax += tax;
			});
			$(row)
				.find(".amount")
				.text(customSalesFormatMoney(amount + totalTax, true));
		}
	});
}

/**
 * Function to bootstrap and init deposit invoice logic
 */
function customSalesInitDepositInvoice() {
	var minRows = 2;

	$(".create-deposit-invoice").click(function () {
		$("#deposit-invoice-modal").modal("show");
	});

	$("#depositInvoiceForm").submit(function (e) {
		e.preventDefault();

		var $form = $("#depositInvoiceForm");
		var submitBtn = $form.find("button[type=submit]");
		const estimateId = $('input[name="estimateid"]').val();

		submitBtn.attr("disabled", "disabled");
		// Send data to server
		let data = $form.serialize();

		$.post(admin_url + "custom_sales/deposit_invoice/" + estimateId, data)
			.done(function (response) {
				try {
					response = JSON.parse(response);
					alert_float(response.status, response.message);
					if (response.status == "success" && response?.url?.length) {
						setTimeout(() => {
							window.location.href = response.url;
						}, 3000);
						return;
					}
				} catch (error) {
					alert_float("danger", error.message);
				}
				submitBtn.removeAttr("disabled");
			})
			.fail(function () {
				submitBtn.removeAttr("disabled");
			});
	});

	$("#addRowBtn").click(function () {
		customSalesAddDepositAmountRow();
	});

	$(document).on("click", ".deleteRowBtn", function () {
		var serialRows = $(".custom-sales-deposit-invoice-row");
		if (serialRows.length > minRows) {
			$(this).closest(".custom-sales-deposit-invoice-row").remove();

			$.each($(".custom-sales-deposit-invoice-row"), function (i, obj) {
				$(obj)
					.find(".sn")
					.text(i + 1);
			});
		}
	});

	$(document).on(
		"keyup",
		".custom-sales-deposit-invoice-row input[name='amount[]']",
		customSalesSumDepositAmountRow
	);
}

/**
 * Add a row of deposit invoice amount. It find existing from server and duplicate
 */
function customSalesAddDepositAmountRow() {
	var minRows = 2;
	var maxRows = CUSTOM_SALES_MODULE.settings.custom_sales_max_deposit_invoice;

	var serialRows = $("#serialRows");
	var totalRows = serialRows.children(
		".custom-sales-deposit-invoice-row"
	)?.length;

	if (totalRows < maxRows) {
		var $newRow = $(
			".custom-sales-deposit-invoice-row:first-of-type"
		).clone();
		$newRow.find("input").val("");
		$newRow.find(".sn").text(totalRows + 1);
		$newRow
			.find("div[data-class=amount]")
			.append(
				'<button type="button" class="btn btn-danger deleteRowBtn tw-absolute" style="right:1.25rem"><i class="fa fa-trash"></i></button>'
			);
		serialRows.append($newRow);
	} else {
		alert_float(
			"danger",
			CUSTOM_SALES_MODULE.lang.custom_sales_max_deposit_invoices_reached
		);
	}
}

function customSalesSumDepositAmountRow() {
	let total = 0;
	$(".custom-sales-deposit-invoice-row input[name='amount[]']").each(
		(index, element) => {
			var _total = parseFloat(element.value ?? "");
			if (!isNaN(_total)) total += _total;
		}
	);
	let totalContainer = $(".custom-sales-deposit-invoice-total");
	totalContainer.text(customSalesFormatMoney(total));
}

function customSalesFormatMoney(total) {
	let currency = CUSTOM_SALES_MODULE.currency?.symbol || "";
	return currency + format_money(total, true);
}

/*************************************Overide default function in app main.js*********************************************/
function save_ei_items_order() {
	var table = $(".table.items-preview");
	var rows = table.find("tbody tr:not(.title)");
	var i = 1,
		type = table.attr("data-type"),
		order = [],
		_order_id,
		item_id;

	if (!type) {
		return false;
	}

	$.each(rows, function () {
		$(this).attr("data-item-order", i);

		order.push([$(this).data("item-id"), i]);
		// update item number when reordering
		$(this).find("td.item_no").html(i);
		i++;
	});

	setTimeout(function () {
		$.post(admin_url + "misc/update_ei_items_order/" + type, {
			data: order,
		});
	}, 200);

	customSalesUpdateTitlesOrder();
}

/*****************************Utilities*******************************************/
/**
 * Wait for an element to enter the DOM
 * @param {string} selector
 * @returns mixed
 */
function waitForElementToExist(selector) {
	return new Promise((resolve) => {
		if (document.querySelector(selector)) {
			return resolve(document.querySelector(selector));
		}

		const observer = new MutationObserver(() => {
			if (document.querySelector(selector)) {
				resolve(document.querySelector(selector));
				observer.disconnect();
			}
		});

		observer.observe(document.body, {
			subtree: true,
			childList: true,
		});
	});
}
