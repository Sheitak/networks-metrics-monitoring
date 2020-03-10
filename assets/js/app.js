/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */
// any CSS you require will output into a single css file (app.css in this case)
require('../scss/app.scss');
// Need jQuery? Install it with "yarn add jquery", then uncomment to require it.
// const $ = require('jquery');
require('bootstrap');

const months = ['janv.', 'févr.', 'mars', 'avr.', 'mai', 'juin', 'juil.', 'août', 'sept.', 'oct.', 'nov.', 'déc.'];

function formatDate(date) {
    return date
        .split('T')[0]
        .split('-')
        .map((x, index) => (index % 2 ? months[Number(x) - 1] : Number(x)))
        .reverse()
        .splice(0, 2);
}

window.chartColors = {
    red: 'rgb(255,99,132)',
    green: 'rgb(75,192,192)',
    blue: 'rgb(54,162,235)',
    yellow: 'rgb(255,205,86)',
    orange: 'rgb(255,159,64)',
    purple: 'rgb(153,102,255)',
    grey: 'rgb(201,203,207)',
};
// no-undef

function config(pageMetrics) {
    let metricLabels = [];
    // eslint-disable-next-line no-restricted-syntax
    for (const metricObject of pageMetrics[0].values) {
        metricLabels = [...metricLabels, formatDate(metricObject.end_time).toString()];
    }

    let datasets = [];
    // eslint-disable-next-line no-plusplus
    for (let i = 0; i < pageMetrics.length; i++) {
        datasets = [...datasets, {
            label: `${pageMetrics[i].title} (Today: ${pageMetrics[i].values[pageMetrics[i].values.length - 1].value})`,
            fill: false,
            borderColor: window.chartColors[Object.keys(window.chartColors)[i]],
            backgroundColor: window.chartColors[Object.keys(window.chartColors)[i]],
            data: pageMetrics[i].values.map(data => data.value),
        }];
    }
    return {
        type: 'line',
        data: {
            labels: metricLabels,
            datasets,
        },
        options: {
            responsive: true,
            scales: {
                xAxes: [{
                    display: true,
                    ticks: {
                        callback(dataLabel, index) {
                            // eslint-disable-next-line max-len
                            // Hide the label of every 2nd dataset. return null to hide the grid line too
                            return index % 2 === 0 ? dataLabel : '';
                        },
                    },
                }],
                yAxes: [{
                    display: true,
                    beginAtZero: false,
                }],
            },
        },
    };
}
// no-undef
// eslint-disable-next-line func-names
window.onload = function () {
    const canvasList = document.querySelectorAll('.chart');
    const canvasArray = Array.from(canvasList);
    // eslint-disable-next-line no-restricted-syntax
    for (const canvas of canvasArray) {
        const values = JSON.parse(canvas.dataset.values);
        // eslint-disable-next-line no-console
        // eslint-disable-next-line no-new,no-undef
        new Chart(canvas.getContext('2d'), config(values));
    }
};
