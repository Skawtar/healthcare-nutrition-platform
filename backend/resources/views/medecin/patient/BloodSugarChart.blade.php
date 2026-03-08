<div>
                                <div class="max-w-sm w-full bg-white rounded-lg shadow-sm dark:bg-gray-800 p-4 md:p-6">
                                    <div class="flex justify-between">
                                        <div>
                                            <h5 class="leading-none text-3xl font-bold text-gray-900 dark:text-white pb-2" id="bloodSugarValue"></h5>
                                            <p class="text-base font-normal text-gray-500 dark:text-gray-400" id="bloodSugarTimeRange">
                                                Blood Sugar (Last 7 days)
                                            </p>
                                        </div>
                                        <div class="flex items-center px-2.5 py-0.5 text-base font-semibold text-green-500 dark:text-green-500 text-center" id="bloodSugarChangeContainer">
                                            <span id="bloodSugarChange"></span>
                                            <svg class="w-3 h-3 ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 14">
                                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13V1m0 0L1 5m4-4 4 4"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div id="blood-sugar-area-chart"></div>
                                    <div class="grid grid-cols-1 items-center border-gray-200 border-t dark:border-gray-700 justify-between">
                                        <div class="flex justify-between items-center pt-5">
                                            <button
                                                id="bsDropdownButton"
                                                data-dropdown-toggle="bsLastDaysdropdown"
                                                data-dropdown-placement="bottom"
                                                class="text-sm font-medium text-gray-500 dark:text-gray-400 hover:text-gray-900 text-center inline-flex items-center dark:hover:text-white"
                                                type="button">
                                                Last 7 days
                                                <svg class="w-2.5 m-2.5 ms-1.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4"/>
                                                </svg>
                                            </button>
                                            <div id="bsLastDaysdropdown" class="z-10 hidden bg-white divide-y divide-gray-100 rounded-lg shadow-sm w-44 dark:bg-gray-700">
                                                <ul class="py-2 text-sm text-gray-700 dark:text-gray-200" aria-labelledby="bsDropdownButton">
                                                    <li><a href="#" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white" data-period="yesterday">Yesterday</a></li>
                                                    <li><a href="#" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white" data-period="today">Today</a></li>
                                                    <li><a href="#" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white" data-period="7days">Last 7 days</a></li>
                                                    <li><a href="#" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white" data-period="14days">Last 14 days</a></li>
                                                    <li><a href="#" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white" data-period="30days">Last 30 days</a></li>
                                                    <li><a href="#" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white" data-period="90days">Last 90 days</a></li>
                                                </ul>
                                            </div>
                                            <a
                                                href="#"
                                                class="uppercase text-sm font-semibold inline-flex items-center rounded-lg text-blue-600 hover:text-blue-700 dark:hover:text-blue-500  hover:bg-gray-100 dark:hover:bg-gray-700 dark:focus:ring-gray-700 dark:border-gray-700 px-3 py-2">
                                                BS Report
                                                <svg class="w-2.5 h-2.5 ms-1.5 rtl:rotate-180" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
   <script>

                                                    // For Blood Sugar Chart
                                const initialBloodSugarValues = @json($bloodSugarData['sugarLevels'] ?? []);
                                const initialBloodSugarCategories = @json($bloodSugarData['measurementTimes'] ?? []);

                                        let bsChart = null;

                                    function loadBloodSugarChart(timeRange = '7days', isInitialLoad = false) {
                                        let sugarLevels = [];
                                        let measurementTimes = [];

                                        // If it's the initial load, use data passed from PHP
                                        if (isInitialLoad) {
                                            sugarLevels = initialBloodSugarValues;
                                            measurementTimes = initialBloodSugarCategories;
                                            // Set the initial latest reading and time range text
                                            document.getElementById('bloodSugarValue').textContent = sugarLevels.length > 0 ? sugarLevels[sugarLevels.length - 1] + ' mg/dL' : 'No Data';
                                            document.getElementById('bloodSugarTimeRange').textContent = 'Blood Sugar (Last 7 days)';
                                            // No change calculation on initial load as there's no "previous" context
                                            document.getElementById('bloodSugarChange').textContent = 'N/A';
                                            document.getElementById('bloodSugarChangeContainer').classList.remove('text-green-500', 'text-red-500'); // Neutral color

                                            renderOrUpdateBloodSugarChart(sugarLevels, measurementTimes, timeRange);

                                        } else {
                                            // For subsequent loads (from dropdown), use AJAX
                                            const patientCin = "{{ $patient->cin }}"; // Assuming $patient is available in the Blade view
                                            const url = `/patients/${patientCin}/blood-sugar-data?timeRange=${timeRange}`; // Your AJAX endpoint

                                            // Update the displayed time range text
                                            let timeRangeText = '';
                                            switch (timeRange) {
                                                case 'yesterday':
                                                    timeRangeText = 'Blood Sugar (Yesterday)';
                                                    break;
                                                case 'today':
                                                    timeRangeText = 'Blood Sugar (Today)';
                                                    break;
                                                case '7days':
                                                    timeRangeText = 'Blood Sugar (Last 7 days)';
                                                    break;
                                                case '14days':
                                                    timeRangeText = 'Blood Sugar (Last 14 days)';
                                                    break;
                                                case '30days':
                                                    timeRangeText = 'Blood Sugar (Last 30 days)';
                                                    break;
                                                case '90days':
                                                    timeRangeText = 'Blood Sugar (Last 90 days)';
                                                    break;
                                                default:
                                                    timeRangeText = 'Blood Sugar';
                                            }
                                            document.getElementById('bloodSugarTimeRange').textContent = timeRangeText;

                                            fetch(url)
                                                .then(response => {
                                                    if (!response.ok) {
                                                        throw new Error('Network response was not ok');
                                                    }
                                                    return response.json();
                                                })
                                                .then(data => {
                                                    sugarLevels = data.sugarLevels;
                                                    measurementTimes = data.measurementTimes;

                                                    // Display the most recent blood sugar level
                                                    document.getElementById('bloodSugarValue').textContent = sugarLevels.length > 0 ? sugarLevels[sugarLevels.length - 1] + ' mg/dL' : 'No Data';

                                                   
                                                    renderOrUpdateBloodSugarChart(sugarLevels, measurementTimes, timeRange);
                                                })
                                                .catch(error => {
                                                    console.error('Error fetching blood sugar data:', error);
                                                    document.getElementById("blood-sugar-area-chart").innerHTML = '<p class="text-center text-gray-500 py-8">Could not load blood sugar data. Please try again.</p>';
                                                });
                                        }
                                    }

                                    // Helper function to render or update the blood sugar chart
                                    function renderOrUpdateBloodSugarChart(sugarLevels, measurementTimes, timeRange) {
                                        const options = {
                                            chart: {
                                                height: "100%",
                                                maxWidth: "100%",
                                                type: "area",
                                                fontFamily: "Inter, sans-serif",
                                                dropShadow: {
                                                    enabled: false,
                                                },
                                                toolbar: {
                                                    show: false,
                                                },
                                            },
                                            tooltip: {
                                                enabled: true,
                                                x: {
                                                    show: true,
                                                    formatter: function (val) {
                                                        // Use the formatted time from the backend directly
                                                        return val;
                                                    }
                                                },
                                            },
                                            fill: {
                                                type: "gradient",
                                                gradient: {
                                                    opacityFrom: 0.55,
                                                    opacityTo: 0,
                                                    shade: "#1C64F2",
                                                    gradientToColors: ["#1C64F2"],
                                                },
                                            },
                                            dataLabels: {
                                                enabled: false,
                                            },
                                            stroke: {
                                                width: 6,
                                            },
                                            grid: {
                                                show: false,
                                                strokeDashArray: 4,
                                                padding: {
                                                    left: 2,
                                                    right: 2,
                                                    top: 0
                                                },
                                            },
                                            series: [
                                                {
                                                    name: "Blood Sugar",
                                                    data: sugarLevels,
                                                    color: "#1A56DB",
                                                },
                                            ],
                                            xaxis: {
                                                categories: measurementTimes, // Use the dynamically fetched/initial times
                                                labels: {
                                                    show: true,
                                                    rotate: -45,
                                                    trim: true,
                                                    formatter: function (val) {
                                                        return val;
                                                    }
                                                },
                                                axisBorder: {
                                                    show: false,
                                                },
                                                axisTicks: {
                                                    show: false,
                                                },
                                            },
                                            yaxis: {
                                                show: true,
                                                labels: {
                                                    formatter: function (value) {
                                                        return value.toFixed(0);
                                                    }
                                                }
                                            },
                                        };

                                        if (bsChart) {
                                            bsChart.updateOptions(options); // Update existing chart
                                        } else {
                                            bsChart = new ApexCharts(document.getElementById("blood-sugar-area-chart"), options);
                                            bsChart.render();
                                        }
                                    }


                                    document.addEventListener('DOMContentLoaded', () => {
                                        // Initialize Blood Pressure chart
                                        if (document.getElementById("blood-pressure-chart")) {
                                            loadBloodPressureChart('7days'); // Initial load for BP chart using AJAX

                                            document.querySelectorAll('#bpLastDaysdropdown ul li a').forEach(item => {
                                                item.addEventListener('click', function(e) {
                                                    e.preventDefault();
                                                    const selectedPeriod = this.dataset.period;
                                                    const selectedText = this.textContent.trim();

                                                    loadBloodPressureChart(selectedPeriod);

                                                    document.getElementById('bpDropdownButton').innerHTML = `
                                                        ${selectedText}
                                                        <svg class="w-2.5 m-2.5 ms-1.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4"/>
                                                        </svg>
                                                    `;
                                                });
                                            });
                                        }

                                        // Initialize Blood Sugar chart
                                        if (document.getElementById("blood-sugar-area-chart")) {
                                            loadBloodSugarChart('7days', true); // Initial load for BS chart using PHP-passed data

                                            document.querySelectorAll('#bsLastDaysdropdown ul li a').forEach(item => {
                                                item.addEventListener('click', function(e) {
                                                    e.preventDefault();
                                                    const selectedPeriod = this.dataset.period;
                                                    const selectedText = this.textContent.trim();

                                                    loadBloodSugarChart(selectedPeriod, false); // Subsequent loads use AJAX

                                                    document.getElementById('bsDropdownButton').innerHTML = `
                                                        ${selectedText}
                                                        <svg class="w-2.5 m-2.5 ms-1.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4"/>
                                                        </svg>
                                                    `;
                                                });
                                            });
                                        }
                                    });
                        </script>                          