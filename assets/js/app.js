/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
require('../css/app.scss');
require('../css/custom.scss');

// Need jQuery? Install it with "yarn add jquery", then uncomment to import it.
// import $ from 'jquery';
require('bootstrap');
require('chart.js');
require('datatables.net');

$(document).ready(function () {
    $('.table thead tr').clone(true).appendTo('.table thead');
    $('.table thead tr:eq(1) th').each(function (i) {
        const title = $(this).text();
        if (i < 2) {
            $(this).html('<input type="text" placeholder="Search ' + title + '" />');
            $('input', this).on('keyup change', function () {
                if (table.column(i).search() !== this.value) {
                    table
                        .column(i)
                        .search(this.value)
                        .draw();
                }
            });
        } else {
            $(this).html('');
        }
    });

    let table = $('.table').DataTable({
        orderCellsTop: true,
        fixedHeader: true,
        paging: false,
        ordering: true,
        info: false,
        searching: true,
        order: [[ 2, "desc" ]]
    });
});