$("#add_product_form").on("submit", function () {
    var myEditor = document.querySelector('.quill-editor-default')
    var html = myEditor.children[0].innerHTML
    $("#hiddenArea").val(html);
})

$(function () {

    var start = moment().startOf('month');
    // var end = moment().subtract(29, 'days');
    var end = moment();

    function cb(start, end) {
        $('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));

        start_date = start.format('MMMM D, YYYY');
        end_date = end.format('MMMM D, YYYY');

        start = start.format('YYYY-MM-DD');
        end = end.format('YYYY-MM-DD');

        $('#start_date').val(start);
        $('#end_date').val(end);
        if (currentRoute === 'reports.carriers') {
            getCarrier();
        }

        updateUrl();
    }

    $('#reportrange').daterangepicker({
        startDate: start,
        endDate: end,
        ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        }
    }, cb);

    cb(start, end);

});

async function getCarrier() {
    var start_date = $('#start_date').val();
    var end_date = $('#end_date').val();
    var agentId = $('#select_agent').val();
    var table;
    await $.ajax({
        type: "get",
        url: '/reports/carriers',
        data: { 'start_date': start_date, 'end_date': end_date, 'agentId': agentId },
        success: function (response) {
            // Destroy the DataTable instance if it already exists
            if (table && $.fn.DataTable.isDataTable('#carrier_table')) {
                table.destroy();
            }

            $('#carrier_table').DataTable().clear().destroy();
            // Initialize the DataTable with the updated data
            table = $('#carrier_table').DataTable({
                data: response,
                columns: [
                    { title: "Sr. #" },
                    { title: "Agent Name" },
                    { title: "MC #" },
                    { title: "Name" },
                    { title: "Number" },
                    { title: "Truck Type" },
                    { title: "Assign To" },
                    { title: "Action" },
                ],
                columnDefs: [
                    { targets: 0, width: '12px' }, // Serial Number column
                    { targets: 2, width: '12px' }, // Serial Number column
                    { targets: 3, width: '37px' }, // Serial Number column
                    { targets: 4, width: '15px' }, // Serial Number column
                    { targets: 5, width: '15px' }, // Serial Number column
                    { targets: 6, width: '80px' }, // Serial Number column
                    // { targets: 7, width: '15px' }, // Serial Number column
                    { targets: -1, width: '8px' } // Action column (last column)
                ]
            });
        },
    })
}


// Handle select option change
$('#select_agent').on('change', function () {
    getCarrier();
    updateUrl();
});


function updateUrl() {
    var agentId = $('#select_agent').val();
    var start_date = $('#start_date').val();
    var end_date = $('#end_date').val();
    // Build the URL with parameters
    var url = window.location.href.split('?')[0]; // Get the base URL
    url += '?agentId=' + agentId + '&start_date=' + start_date + '&end_date=' + end_date;
    var url = url.replace('carriers', 'carrier_downloads');
    // Update the URL
    $('#excel_url').attr("href", url);
    // window.history.pushState({ path: url }, '', url);

}





$(function () {

    var start = moment().startOf('month');
    var end = moment();

    function cbd(start, end) {
        $('#reportrange_dispatch span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));

        start_date = start.format('MMMM D, YYYY');
        end_date = end.format('MMMM D, YYYY');

        start = start.format('YYYY-MM-DD');
        end = end.format('YYYY-MM-DD');

        $('#start_date_d').val(start);
        $('#end_date_d').val(end);

        if (currentRoute === 'reports.dispatchers') {
            getDispatch();
        }

        updateUrlDispatch();
    }

    $('#reportrange_dispatch').daterangepicker({
        startDate: start,
        endDate: end,
        ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        }
    }, cbd);

    cbd(start, end);

});

async function getDispatch() {
    var start_date = $('#start_date_d').val();
    var end_date = $('#end_date_d').val();
    var dispatcherId = $('#select_dispatcher').val();
    var table;
    await $.ajax({
        type: "get",
        url: '/reports/dispatchers',
        data: { 'start_date': start_date, 'end_date': end_date, 'dispatcherId': dispatcherId },
        success: function (response) {
            // Destroy the DataTable instance if it already exists
            if (table && $.fn.DataTable.isDataTable('#dispatch_table')) {
                table.destroy();
            }

            $('#dispatch_table').DataTable().clear().destroy();

            // $('#carrier_table').dataTable().fnClearTable();
            // $('#carrier_table').dataTable().fnDestroy();

            // Initialize the DataTable with the updated data
            table = $('#dispatch_table').DataTable({
                data: response,
                columns: [
                    { title: "Sr. #" },
                    { title: "Dispatcher Name" },
                    { title: "MC #" },
                    { title: "Pick Location" },
                    { title: "Delivery Location" },
                    { title: "Load Date" },
                    { title: "Carrier Name" },
                    { title: "Rate" },
                    { title: "Broker Name" },
                    { title: "Status" },
                    { title: "Action" },
                ],
                // columnDefs: [
                //     { targets: 0, width: '12px' }, // Serial Number column
                //     { targets: 1, width: '12px' }, // Serial Number column
                //     { targets: 2, width: '37px' }, // Serial Number column
                //     { targets: 3, width: '15px' }, // Serial Number column
                //     { targets: 4, width: '15px' }, // Serial Number column
                //     { targets: -1, width: '8px' } // Action column (last column)
                // ]
            });
        },
    });
}

// Handle select option change
$('#select_dispatcher').on('change', function () {
    getDispatch();
    updateUrlDispatch();
});


function updateUrlDispatch() {
    var dispatchId = $('#select_dispatcher').val();
    var start_date = $('#start_date_d').val();
    var end_date = $('#end_date_d').val();
    // Build the URL with parameters
    var url = window.location.href.split('?')[0]; // Get the base URL
    url += '?dispatchId=' + dispatchId + '&start_date=' + start_date + '&end_date=' + end_date;
    var url = url.replace('dispatchers', 'dispatcher_downloads');
    // Update the URL
    $('#excel_dispatch_url').attr("href", url);
    // window.history.pushState({ path: url }, '', url);

}


// =========== Set the filter of the truck type ==============//

$(function () {

    var start = moment().startOf('month');
    var end = moment();

    function cbtt(start, end) {
        $('#reportrange_truckType span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));

        start_date = start.format('MMMM D, YYYY');
        end_date = end.format('MMMM D, YYYY');

        start = start.format('YYYY-MM-DD');
        end = end.format('YYYY-MM-DD');

        $('#start_date_truckType').val(start);
        $('#end_date_truckType').val(end);

        if (currentRoute === 'reports.truckTypesReport') {
            getTruckTypes();
        }
        updateUrlTruckType();
    }

    $('#reportrange_truckType').daterangepicker({
        startDate: start,
        endDate: end,
        ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        }
    }, cbtt);

    cbtt(start, end);

});
async function getTruckTypes() {
    var start_date = $('#start_date_truckType').val();
    var end_date = $('#end_date_truckType').val();
    var truckType = $('#select_truckTypes').val();
    var table;
    await $.ajax({
        type: "get",
        url: '/reports/truck-types',
        data: { 'start_date': start_date, 'end_date': end_date, 'truckType': truckType },
        success: function (response) {
            // Destroy the DataTable instance if it already exists
            if (table && $.fn.DataTable.isDataTable('#truckTypes_table')) {
                table.destroy();
            }

            $('#truckTypes_table').DataTable().clear().destroy();
            // Initialize the DataTable with the updated data
            table = $('#truckTypes_table').DataTable({
                data: response,
                columns: [
                    { title: "Sr. #" },
                    { title: "Agent Name" },
                    { title: "MC #" },
                    { title: "Truck Name" },
                    { title: "Rate" },
                    { title: "%" },
                    { title: "Receivable" },
                    { title: "Load Date" },
                    { title: "Created At" },
                ],
                columnDefs: [
                    { targets: 0, width: '12px' }, // Serial Number column
                    { targets: 2, width: '12px' }, // Serial Number column
                    { targets: 3, width: '37px' }, // Serial Number column
                    { targets: 4, width: '15px' }, // Serial Number column
                    { targets: 5, width: '10px' }, // Serial Number column
                    { targets: 6, width: '50px' }, // Serial Number column
                ]
            });
        },
    })
}

$('#select_truckTypes').on('change', function () {
    getTruckTypes();
    updateUrlTruckType();
});

function updateUrlTruckType() {
    var truckType = $('#select_truckTypes').val();
    var start_date = $('#start_date_truckType').val();
    var end_date = $('#end_date_truckType').val();
    // Build the URL with parameters
    var url = window.location.href.split('?')[0]; // Get the base URL
    url += '?truckType=' + truckType + '&start_date=' + start_date + '&end_date=' + end_date;
    var url = url.replace('dispatchers', 'dispatcher_downloads');
    // Update the URL
    $('#excel_truck_types_url').attr("href", url);
    // window.history.pushState({ path: url }, '', url);

}

// =========== Set the filter of the truck type ==============//


$('#user-table').on('click', '.status-dot', async function () {
    var dot = $(this);
    var userId = dot.data('user-id');
    var currentStatus = dot.data('status');
    var newStatus = currentStatus === 'active' ? 'inactive' : 'active';
    await $.ajax({
        type: 'get',
        url: '/users/' + userId + '/change-status',
        data: { status: newStatus },
        success: function (response) {
            if (response.status == true) {
                dot.removeClass('active inactive').addClass(newStatus);
                dot.data('status', newStatus);
            } else {
                Swal.fire(
                    'Fail',
                    response.message,
                    'error'
                )
            }
        },
        error: function (xhr, status, error) {
            console.log('AJAX request failed');
        }
    });
});

function getDispatchId(data, carrierId) {
    // Show the loader and blur the background before making the AJAX request
    $('#loaderOverlay').removeClass('hidden');
    $('#blurBackground').removeClass('hidden');
    $.ajax({
        type: 'get',
        url: '/carriers/' + carrierId + '/assign-to',
        data: { 'dispatchId': data.value },
        success: function (response) {
            if (response.status == true) {
                Swal.fire(
                    'Success',
                    response.message,
                    'success'
                )
            } else {
                Swal.fire(
                    'Fail',
                    response.message,
                    'error'
                )
            }
            // Show the loader and blur the background before making the AJAX request
            $('#loaderOverlay').addClass('hidden');
            $('#blurBackground').addClass('hidden');
        },
        error: function (xhr, status, error) {
            // Show the loader and blur the background before making the AJAX request
            $('#loaderOverlay').removeClass('hidden');
            $('#blurBackground').removeClass('hidden');
            console.log('AJAX request failed');
        }
    });
}


// Dispatcher Pdf Report //

async function getDispatchPDFReport() {
    var start_date = $('#start_date1').val();
    var end_date = $('#end_date1').val();
    var mc_number = $('#select_mc_numbers').val();
    var invoice_status = $('#select_invoice_status').val() || '';
    $('#selected_invoices').val('');
    $("#invoice_all").prop("checked", false);

    var table;
    await $.ajax({
        type: "get",
        url: '/invoices/dispatcher-report',
        data: { 'start_date': start_date, 'end_date': end_date, 'mc_number': mc_number, 'invoice_status': invoice_status },
        success: function (response) {
            if (response.mc_html !== '') {
                $('#select_mc_numbers').html('');
                $('#select_mc_numbers').append(response.mc_html);
                $('#pdf_table').html('');
                $('#pdf_table').append(response.table_html);
            }
        },
    });
}

$(function () {

    var start1 = moment().subtract(6, 'days');
    var end1 = moment();

    function cbpdf(start1, end1) {
        $('#report_range_dispatch_pdf span').html(start1.format('MMMM D, YYYY') + ' - ' + end1.format('MMMM D, YYYY'));

        // start_date = start.format('MMMM D, YYYY');
        // end_date = end.format('MMMM D, YYYY');

        start1 = start1.format('YYYY-MM-DD');
        end1 = end1.format('YYYY-MM-DD');

        $('#start_date1').val(start1);
        $('#end_date1').val(end1);

        getDispatchPDFReport();
        updatePDFUrl();
    }

    $('#report_range_dispatch_pdf').daterangepicker({
        startDate: start1,
        endDate: end1,
        ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        }
    }, cbpdf);

    cbpdf(start1, end1);

});

$('#select_mc_numbers').on('change', function () {
    getDispatchPDFReport();
    updatePDFUrl();
    let selected_mc = $("#select_mc_numbers").val();
    if (selected_mc === '') {
        $(".download_pdf").hide();
        $(".download_xlx").show();
    } else {
        $(".download_pdf").show();
        $(".download_xlx").hide();
    }
});

$('#select_invoice_status').on('change', function () {
    getDispatchPDFReport();
    updatePDFUrl();
});


function updatePDFUrl() {
    var mc_number = $('#select_mc_numbers').val() || '';
    var start_date = $('#start_date1').val() || '';
    var end_date = $('#end_date1').val() || '';
    var invoice_status = $('#select_invoice_status').val() || '';

    // Get the comma-separated selected invoice IDs
    var selectedInvoices = $('#selected_invoices').val();

    // Build the URL with parameters
    var url = window.location.href.split('?')[0]; // Get the base URL
    url += '?mc_number=' + mc_number + '&start_date=' + start_date + '&end_date=' + end_date;
    if (invoice_status !== '') {
        url += '&invoice_status=' + invoice_status;
    }

    if (selectedInvoices) {
        url += '&selected_invoices=' + selectedInvoices;
        $(".download_pdf").show();
    } else if (mc_number !== '') {
        $(".download_pdf").show();
    } else {
        $(".download_pdf").hide();
    }

    var url_pdf = url.replace('dispatcher-report', 'download-pdf');
    var url_xlx = url.replace('dispatcher-report', 'download-xlx');

    // Update the URL
    $('#pdf_dispatch_url').attr("href", url_pdf);
    $('#xlx_dispatch_url').attr("href", url_xlx);
}

$(document).on('click', '#pdf_dispatch_url', function (e) {
    var selected = $('#selected_invoices').val();
    if (!selected) {
        // If checkboxes were checked without triggering updateSelectedInvoices, grab them now
        var checked = $('td input[name="invoice"]:checked').map(function () {
            return $(this).val();
        }).get().join(',');
        if (checked) {
            $('#selected_invoices').val(checked);
            updatePDFUrl();
            return true;
        }
        e.preventDefault();
        Swal.fire({
            icon: 'warning',
            title: 'No Loads Selected',
            text: 'Please check at least one load checkbox to download invoice PDF.',
            customClass: { confirmButton: 'btn btn-primary' }
        });
        return false;
    }
});
// Dispatcher Pdf Report //



function getNotificationMessage() {
    $.ajax({
        url: '/notifications',
        type: "get",
        success: function (res) {
            if (res.count > 0) {
                $("#total_count").text(res.count).show();
            } else {
                $("#total_count").text('0').hide();
            }
            if (res.html) {
                $("#notification_html").html(res.html);
            }
        },
        error: function (xhr, status, error) {
            // silent fail
        }
    });
}

function getChatMessage() {
    if ($(".total_chat_count").length === 0) return;
    $.ajax({
        url: '/chat-api/unread-count',
        type: "get",
        success: function (res) {
            if (res.count > 0) {
                $(".total_chat_count").text(res.count).show();
                var html = '';
                $.each(res.messages, function (i, msg) {
                    html += `
                        <li class="list-group-item list-group-item-action dropdown-notifications-item">
                            <a href="/chat/${msg.sender_id}" class="d-flex text-decoration-none text-dark">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar avatar-sm">
                                        <span class="avatar-initial rounded-circle bg-label-primary font-weight-bold">
                                            ${msg.sender_name.charAt(0).toUpperCase()}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0 text-truncate">${msg.sender_name}</h6>
                                    <small class="text-muted d-block text-truncate" style="max-width: 220px;">${msg.body}</small>
                                    <small class="text-muted fs-tiny">${msg.time}</small>
                                </div>
                                <div class="flex-shrink-0">
                                    <span class="badge bg-danger rounded-pill">${msg.count}</span>
                                </div>
                            </a>
                        </li>
                    `;
                });
                $(".messages_html").html(html);

                // Update contact list on chat page in real time
                if ($('#chat-contact-list').length) {
                    var unreadSenderIds = res.messages.map(function(m) { return String(m.sender_id); });

                    // Hide badges for contacts that have no unread messages
                    $('#chat-contact-list li.chat-contact-list-item').each(function() {
                        var uid = String($(this).data('user-id'));
                        if (!unreadSenderIds.includes(uid)) {
                            $(this).find('.unread-badge').hide();
                        }
                    });

                    // Update contacts with unread messages
                    $.each(res.messages, function (i, msg) {
                        var item = $('li[data-user-id="' + msg.sender_id + '"]');
                        if (item.length && !item.hasClass('active')) {
                            var badge = item.find('.unread-badge');
                            if (badge.length) {
                                badge.text(msg.count).show();
                            } else {
                                item.find('.chat-contact-info .d-flex.mt-1').append('<span class="badge bg-danger rounded-pill badge-sm unread-badge">' + msg.count + '</span>');
                            }
                            item.find('.chat-last-message').text(msg.body);
                        }
                    });
                }
            } else {
                $(".total_chat_count").text('0').hide();
                $(".messages_html").html('<li class="list-group-item text-center text-muted py-3">No new messages</li>');
                if ($('#chat-contact-list').length) {
                    $('.unread-badge').hide();
                }
            }
        },
        error: function (xhr, status, error) {
            // silent fail
        }
    });
}

// Call functions initially
getNotificationMessage();
getChatMessage();

// Set interval to poll every 5 seconds
setInterval(getNotificationMessage, 5000);
setInterval(getChatMessage, 5000);

// JavaScript to handle the notification click event
// $(document).on('click', '.notification-item', function() {
//     var notificationId = $(this).data('notification-id');
//     $.ajax({
//         url: '/notifications/' + notificationId, // Replace with your route
//         method: 'GET',
//         success: function(response) {
//             $('#notificationDetails').html(response.html);
//             $('#notificationModal').modal('show');
//         },
//         error: function(xhr, status, error) {
//             console.error('Error:', error);
//         }
//     });
// });

$(document).on('click', '.viewdetails', function () {
    var dispatcherId = $(this).data('id');
    $.ajax({
        url: '/dispatchers/' + dispatcherId, // Replace with your route
        method: 'GET',
        success: function (response) {
            console.log(response);
            $('#notificationDetails').html(response.html);
            $('#notificationModal').modal('show');
        },
        error: function (xhr, status, error) {
            console.error('Error:', error);
        }
    });
});

$(document).on('click', '.editdispatcherdetails', function () {
    var dispatcherId = $(this).data('id');
    $.ajax({
        url: '/dispatchers/' + dispatcherId + '/edit', // Replace with your route
        method: 'GET',
        success: function (response) {
            $('#dispatcherModalLabel').html('Edit Dispatcher Details');
            $('#dispatcherDetails').html(response.html);
            $('#dispatcherModal').modal('show');
        },
        error: function (xhr, status, error) {
            console.error('Error:', error);
        }
    });
});

$(document).ready(function () {
    $(document).on('submit', '#updateDispatcherForm', function (e) {
        e.preventDefault();
        document.activeElement.blur();
        // var dispatcherId = $(this).data('dispatcher-id');
        let formData = new FormData(this);

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        // Construct the URL for the Ajax request using the carrier ID
        let url = $(this).attr('action');
        jQuery.ajax({
            type: 'post',
            url: url,
            data: formData,
            processData: false,
            contentType: false,
            success: function (res) {
                if (res.status == 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: res.message,
                    })
                    $('#dispatcherModal').modal('hide');
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed!',
                        text: res.message,
                    })
                }
            },
            error: function (error) {
                if (error.responseJSON.errors) {
                    $('.alert-danger-li').html('');
                    $.each(error.responseJSON.errors, function (key, value) {
                        $('.alert-danger-li').append('<li>' + value + '</li>');
                    });
                    $('.show-errors').show();

                    Swal.fire({
                        icon: 'error',
                        title: 'Failed!',
                        text: 'Please check the validation errors',
                    })
                }
            }
        });
    });
});


$(document).on('click', '.viewcarrierdetails', function () {
    var carrierId = $(this).data('id');
    $.ajax({
        url: '/carriers/' + carrierId, // Replace with your route
        method: 'GET',
        success: function (response) {
            $('#carrierModalLabel').html('Carrier Details');
            $('#carrierDetails').html(response.html);
            $('#carrierModal').modal('show');
        },
        error: function (xhr, status, error) {
            console.error('Error:', error);
        }
    });
});


$(document).on('click', '.editcarrierdetails', function () {
    var carrierId = $(this).data('id');
    $.ajax({
        url: '/carriers/' + carrierId + '/edit', // Replace with your route
        method: 'GET',
        success: function (response) {
            $('#carrierModalLabel').html('Edit Carrier Details');
            $('#carrierDetails').html(response.html);
            $('#carrierModal').modal('show');
        },
        error: function (xhr, status, error) {
            console.error('Error:', error);
        }
    });
});

$(document).on('click', '.editTruckDetails', function () {
    var truckId = $(this).data('id');
    $.ajax({
        url: '/truck-types/' + truckId + '/edit',
        method: 'GET',
        success: function (response) {
            $('#carrierModalLabel').html('Edit Truck Details');
            $('#carrierDetails').html(response.html);
            $('#carrierModal').modal('show');
        },
        error: function (xhr, status, error) {
            console.error('Error:', error);
        }
    });
});

$(document).ready(function () {
    $(document).on('submit', '#updateCarrierForm', function (e) {
        e.preventDefault();
        document.activeElement.blur();
        var carrierId = $(this).data('carrier-id');
        let formData = new FormData(this);

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        // Construct the URL for the Ajax request using the carrier ID
        let url = $(this).attr('action');
        jQuery.ajax({
            type: 'post',
            url: url,
            data: formData,
            processData: false,
            contentType: false,
            success: function (res) {
                if (res.status == 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: res.message,
                    })
                    $('#carrierModal').modal('hide');
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed!',
                        text: res.message,
                    })
                }
            },
            error: function (error) {
                if (error.responseJSON.errors) {
                    $('.alert-danger-li').html('');
                    $.each(error.responseJSON.errors, function (key, value) {
                        $('.alert-danger-li').append('<li>' + value + '</li>');
                    });
                    $('.show-errors').show();

                    Swal.fire({
                        icon: 'error',
                        title: 'Failed!',
                        text: 'Please check the validation errors',
                    })
                }
            }
        });
    });
});

$(document).ready(function () {
    // Define the function to update selected invoices
    function updateSelectedInvoices() {
        var selectedInvoices = $('td input[name="invoice"]:checked:not(:disabled)').map(function () {
            return $(this).val();
        }).get().join(',');

        $('#selected_invoices').val(selectedInvoices);

        // Update Select All Checkbox state considering only selectable (not disabled) checkboxes
        var allCheckboxes = $('td input[name="invoice"]:not(:disabled)');
        var checkedCheckboxes = $('td input[name="invoice"]:checked:not(:disabled)');

        if (allCheckboxes.length > 0 && checkedCheckboxes.length === allCheckboxes.length) {
            $('#invoice_all').prop('checked', true);
        } else {
            $('#invoice_all').prop('checked', false);
        }

        // Uncheck Select All Checkbox if all individual checkboxes are unchecked
        if (checkedCheckboxes.length === 0) {
            $('#invoice_all').prop('checked', false);
        }

        updatePDFUrl();
    }

    // Attach event handler to Select All Checkbox (only toggle selectable ones)
    $(document).on('click', '#invoice_all', function () {
        var isChecked = $(this).prop('checked');
        $('td input[name="invoice"]:not(:disabled)').prop('checked', isChecked);
        updateSelectedInvoices();
    });

    // Attach event handler to Individual Checkboxes
    $(document).on('click', 'td input[name="invoice"]:not(:disabled)', function () {
        updateSelectedInvoices();
    });
});

$(document).ready(function () {
    $(document).on('submit', '#updateTruckTypeForm', function (e) {
        e.preventDefault();
        document.activeElement.blur();
        let formData = new FormData(this);

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        // Construct the URL for the Ajax request using the carrier ID
        let url = $(this).attr('action');
        jQuery.ajax({
            type: 'POST',
            url: url,
            data: formData,
            processData: false,
            contentType: false,
            success: function (res) {
                if (res.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: res.message,
                    })
                    $('#carrierModal').modal('hide');
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed!',
                        text: res.message,
                    })
                }
            },
            error: function (error) {
                if (error.responseJSON.errors) {
                    $('.alert-danger-li').html('');
                    $.each(error.responseJSON.errors, function (key, value) {
                        $('.alert-danger-li').append('<li>' + value + '</li>');
                    });
                    $('.show-errors').show();

                    Swal.fire({
                        icon: 'error',
                        title: 'Failed!',
                        text: 'Please check the validation errors',
                    })
                }
            }
        });
    });
});

function changeInvoiceStatus(data, dispatchId) {
    var invoice_status = data.value;
    $.ajax({
        url: '/invoices/' + dispatchId + '/change-status', // Replace with your route
        method: 'GET',
        data: { invoice_status: invoice_status },
        success: function (response) {
            if (response.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: response.message,
                    timer: 1500,
                    showConfirmButton: false
                });
                getDispatchPDFReport();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Failed!',
                    text: response.message,
                })
            }
        },
        error: function (xhr, status, error) {
            console.error('Error:', error);
        }
    });
}


// Push.create('Hello World!')

function getLoadCommission() {
    let user_type;
    user_type = $("#user_type").val();
    if (user_type === 'Dispatcher') {
        $(".div_load_commission").show();
    } else {
        $(".div_load_commission").hide();
        $("#load_commission").val('');
    }
}

function confirmAndSubmit(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            var form = document.getElementById('delete-record-' + id);
            if (form.checkValidity()) {
                // Form is valid, so submit it
                form.submit();
            }
        }
    })
    return false; // Prevent the button from doing a regular submit
}

var loadDateElem = document.getElementById("load_date");
if (loadDateElem) {
    loadDateElem.onkeydown = function (e) {
        e.preventDefault();
    };
}
// var bodyDocument = window.document.body;
// // $(document).on('click', '.is_cancel', function () {
// $(window.document).on('click', '.is_cancel', async function () {
//     var dot = $(this);
//     console.log(dot)
//     var dispatchId = dot.data('dispatch-id');
//     var currentStatus = dot.data('dispatch-status');
//     const newStatus = (currentStatus === '1') ? '0' : '1';
//     await $.ajax({
//         type: 'get',
//         url: '/dispatchers/' + dispatchId + '/is-cancel',
//         data: { status: newStatus },
//         success: function (response) {
//             if (response.status == true) {
//                 dot.removeClass('cancel-dot').addClass(newStatus);
//                 dot.data('dispatch-status', newStatus);
//             } else {
//                 Swal.fire(
//                     'Fail',
//                     response.message,
//                     'error'
//                 )
//             }
//         },
//         error: function (xhr, status, error) {
//             console.log('AJAX request failed');
//         }
//     });
// });


function cancelLoad(dispatchId) {
    var dot = $('.is_cancel[data-dispatch-id="' + dispatchId + '"]');
    var currentStatus = dot.data('dispatch-status');
    const newStatus = (currentStatus === '1') ? '0' : '1';
    $.ajax({
        type: 'get',
        url: '/dispatchers/' + dispatchId + '/is-cancel',
        data: { is_cancel: newStatus },
        success: function (response) {
            if (response.status == true) {
                if (newStatus == 1){
                    dot.addClass('cancel-dot');
                }else if(newStatus == 0){
                    dot.removeClass('cancel-dot');
                }
                dot.data('dispatch-status', newStatus);
            } else {
                Swal.fire(
                    'Fail',
                    response.message,
                    'error'
                )
            }
        },
        error: function (xhr, status, error) {
            console.log('AJAX request failed');
        }
    });
    // Return false to prevent default action (if needed)
    return false;
}

