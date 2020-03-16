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

require('@fortawesome/fontawesome-free/css/all.min.css');
require('@fortawesome/fontawesome-free/js/all.js');

$(document).ready(function () {
    $('.table').DataTable({
        orderCellsTop: true,
        fixedHeader: true,
        paging: false,
        ordering: true,
        info: false,
        searching: true,
        order: [[ $('.table').data('sort'), "desc" ]]
    });

    $('.finger').animate({
        left: '-=200',
    }, 1000, function() {
        $(this).hide();
    });

    $('.location').click(function () {
        let listElement = $('.compare-list');
        let locationList = listElement.text().length ? listElement.html().split(',') : [];

        if($.inArray($(this).text(), locationList) === -1){
            locationList.push($(this).text());
        }
        console.log(locationList);
        listElement.html(locationList.join(','));
        $('.compare-button').attr('href', $('.compare-button').data('href')+locationList.join(','));
    });
});