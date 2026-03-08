                            <div>
                                <div class="max-w-sm w-full bg-white rounded-lg shadow-sm dark:bg-gray-800 p-4 md:p-6">
                                    <div class="flex justify-between pb-4 mb-4 border-b border-gray-200 dark:border-gray-700">
                                        <div class="flex items-center">
                                            <div>
                                                <h5 class="leading-none text-2xl font-bold text-gray-900 dark:text-white pb-1">Blood Pressure</h5>
                                            </div>
                                        </div>
                                        <div>
                                            <span class="bg-blue-100 text-blue-800 text-xs font-medium inline-flex items-center px-2.5 py-1 rounded-md dark:bg-blue-900 dark:text-blue-300">
                                                Weekly Trend
                                            </span>
                                        </div>
                                    </div>
                                    <div id="blood-pressure-apex-chart"></div>
                                </div>
                            </div>
          <script>
                            // Data passed from controller for daily averages over the last week
                                        const systolicDailyAvg = @json($bloodPressureData['systolicDailyAvg'] ?? []);
                                        const diastolicDailyAvg = @json($bloodPressureData['diastolicDailyAvg'] ?? []);
                                        const dailyCategories = @json($bloodPressureData['dailyCategories'] ?? []);



                            const options = {
                                colors: ["#1A56DB", "#FDBA8C"], // Blue for Systolic, Orange for Diastolic
                                series: [
                                    {
                                        name: "Systolic (Avg)", // Changed label for clarity
                                        color: "#1A56DB",
                                        data: systolicDailyAvg,
                                    },
                                    {
                                        name: "Diastolic (Avg)", // Changed label for clarity
                                        color: "#FDBA8C",
                                        data: diastolicDailyAvg,
                                    },
                                ],
                                chart: {
                                    type: "bar",
                                    height: "320px",
                                    fontFamily: "Inter, sans-serif",
                                    toolbar: {
                                        show: false,
                                    },
                                },
                                plotOptions: {
                                    bar: {
                                        horizontal: false,
                                        columnWidth: "70%",
                                        borderRadiusApplication: "end",
                                        borderRadius: 8,
                                    },
                                },
                                tooltip: {
                                    shared: true,
                                    intersect: false,
                                    style: {
                                        fontFamily: "Inter, sans-serif",
                                    },
                                    x: {
                                        show: true,
                                        format: 'MMM D, ddd' // Show full date and day of week in tooltip
                                    },
                                    y: {
                                        formatter: function (val) {
                                            return val + ' mmHg'; // Add unit to tooltip values
                                        }
                                    }
                                },
                                states: {
                                    hover: {
                                        filter: {
                                            type: "darken",
                                            value: 1,
                                        },
                                    },
                                },
                                stroke: {
                                    show: true,
                                    width: 0,
                                    colors: ["transparent"],
                                },
                                grid: {
                                    show: false, // Hide background grid
                                    strokeDashArray: 4,
                                    padding: {
                                        left: 2,
                                        right: 2,
                                        top: -14
                                    },
                                },
                                dataLabels: {
                                    enabled: false,
                                },
                                legend: {
                                    show: true, // Show legend to differentiate Systolic and Diastolic
                                    position: 'top',
                                    horizontalAlign: 'left',
                                    markers: {
                                        width: 12,
                                        height: 12,
                                        radius: 4,
                                    },
                                    itemMargin: {
                                        horizontal: 10,
                                        vertical: 0
                                    },
                                    fontFamily: "Inter, sans-serif",
                                    fontSize: '12px',
                                },
                                xaxis: {
                                    categories: dailyCategories, // Use the aggregated daily labels
                                    floating: false,
                                    labels: {
                                        show: true,
                                        style: {
                                            fontFamily: "Inter, sans-serif",
                                            cssClass: 'text-xs font-normal fill-gray-500 dark:fill-gray-400'
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
                                    show: true, // Show Y-axis to see values
                                    labels: {
                                        formatter: function (value) {
                                            return value + ' mmHg'; // Add unit to Y-axis labels
                                        },
                                        style: {
                                            fontFamily: "Inter, sans-serif",
                                            cssClass: 'text-xs font-normal fill-gray-500 dark:fill-400'
                                        }
                                    },
                                    min: 60, // Set appropriate min/max for BP
                                    max: 180,
                                    tickAmount: 6 // Ensure a good number of ticks
                                },
                                fill: {
                                    opacity: 1,
                                },
                            };

                            if(document.getElementById("blood-pressure-apex-chart") && typeof ApexCharts !== 'undefined') {
                                const chart = new ApexCharts(document.getElementById("blood-pressure-apex-chart"), options);
                                chart.render();
                            }
         </script>                       