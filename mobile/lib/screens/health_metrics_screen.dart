import 'dart:math' as math;
import 'dart:math';

import 'package:flutter/material.dart';
import 'package:flutter_application_1/services/auth/api_service.dart';
import 'package:flutter_application_1/services/health_metric_service.dart';
import 'package:intl/intl.dart';
import 'package:fl_chart/fl_chart.dart';
import '../models/blood_pressure.dart';
import '../models/blood_sugar.dart';
import 'edit_health_metric_screen.dart';

class HealthMetricsScreen extends StatefulWidget {
  const HealthMetricsScreen({super.key});

  @override
  State<HealthMetricsScreen> createState() => _HealthMetricsScreenState();
}

class _HealthMetricsScreenState extends State<HealthMetricsScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;
  late ApiService _apiService;
  late HealthMetricService _healthMetricService;
  List<BloodPressure> _bloodPressures = [];
  List<BloodSugar> _bloodSugars = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
    _apiService = ApiService();
    _healthMetricService = HealthMetricService(_apiService);
    _loadData();
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  Future<void> _loadData() async {
    try {
      setState(() => _isLoading = true);
      _bloodPressures = await _healthMetricService.getBloodPressures();
      _bloodSugars = await _healthMetricService.getBloodSugars();
      setState(() => _isLoading = false);
    } catch (e) {
      setState(() => _isLoading = false);
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(SnackBar(content: Text('Error: ${e.toString()}')));
    }
  }

  Widget _buildBloodPressureChart() {
    if (_bloodPressures.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.monitor_heart_outlined,
              size: 48,
              color: Colors.blueGrey.shade400,
            ),
            SizedBox(height: 16),
            Text(
              'No blood pressure data available',
              style: TextStyle(color: Colors.blueGrey.shade600, fontSize: 16),
            ),
          ],
        ),
      );
    }

    final sortedData = List<BloodPressure>.from(_bloodPressures)
      ..sort((a, b) => a.measurementAt.compareTo(b.measurementAt));

    final minSystolic = sortedData.isNotEmpty
        ? math.min(
            sortedData.map((bp) => bp.systolic).reduce(math.min).toDouble() -
                10,
            200,
          )
        : 90;
    final maxSystolic = sortedData.isNotEmpty
        ? sortedData.map((bp) => bp.systolic).reduce(math.max).toDouble() + 10
        : 140;
    final minDiastolic = sortedData.isNotEmpty
        ? math.min(
            sortedData.map((bp) => bp.diastolic).reduce(math.min).toDouble() -
                10,
            120,
          )
        : 60;
    final maxDiastolic = sortedData.isNotEmpty
        ? sortedData.map((bp) => bp.diastolic).reduce(math.max).toDouble() + 10
        : 90;

    return Card(
      elevation: 4,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      margin: const EdgeInsets.only(bottom: 16),
      child: Container(
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(16),
          color: Colors.white, // White background for pretty look
        ),
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            SizedBox(
              height: 300,
              child: LineChart(
                LineChartData(
                  minX: sortedData.first.measurementAt.millisecondsSinceEpoch
                      .toDouble(),
                  maxX: sortedData.last.measurementAt.millisecondsSinceEpoch
                      .toDouble(),
                  minY: min(minSystolic, minDiastolic) - 5,
                  maxY: max(maxSystolic, maxDiastolic) + 5,
                  lineBarsData: [
                    LineChartBarData(
                      spots: sortedData
                          .map(
                            (bp) => FlSpot(
                              bp.measurementAt.millisecondsSinceEpoch
                                  .toDouble(),
                              bp.systolic.toDouble(),
                            ),
                          )
                          .toList(),
                      gradient: LinearGradient(
                        colors: [
                          Colors.blue.shade700,
                          Colors.blue.shade300,
                        ], // Nicer blue gradient
                      ),
                      barWidth: 4,
                      isCurved: true,
                      dotData: FlDotData(
                        show: true,
                        getDotPainter: (spot, percent, barData, index) =>
                            FlDotCirclePainter(
                              radius: 5,
                              color: Colors
                                  .blue
                                  .shade700, // Dot color matching line start
                              strokeWidth: 2,
                              strokeColor: Colors.white,
                            ),
                      ),
                      belowBarData: BarAreaData(
                        show: true,
                        gradient: LinearGradient(
                          colors: [
                            Colors.blue.shade700.withOpacity(
                              0.15,
                            ), // Lighter opacity
                            Colors.blue.shade300.withOpacity(0.05),
                          ],
                        ),
                      ),
                    ),
                    LineChartBarData(
                      spots: sortedData
                          .map(
                            (bp) => FlSpot(
                              bp.measurementAt.millisecondsSinceEpoch
                                  .toDouble(),
                              bp.diastolic.toDouble(),
                            ),
                          )
                          .toList(),
                      gradient: LinearGradient(
                        colors: [
                          Colors.lightBlue.shade700,
                          Colors.lightBlue.shade300,
                        ], // Another shade of blue
                      ),
                      barWidth: 4,
                      isCurved: true,
                      dotData: FlDotData(
                        show: true,
                        getDotPainter: (spot, percent, barData, index) =>
                            FlDotCirclePainter(
                              radius: 5,
                              color: Colors
                                  .lightBlue
                                  .shade700, // Dot color matching line start
                              strokeWidth: 2,
                              strokeColor: Colors.white,
                            ),
                      ),
                      belowBarData: BarAreaData(
                        show: true,
                        gradient: LinearGradient(
                          colors: [
                            Colors.lightBlue.shade700.withOpacity(
                              0.15,
                            ), // Lighter opacity
                            Colors.lightBlue.shade300.withOpacity(0.05),
                          ],
                        ),
                      ),
                    ),
                  ],
                  titlesData: FlTitlesData(
                    show: true,
                    rightTitles: AxisTitles(
                      sideTitles: SideTitles(showTitles: false),
                    ),
                    topTitles: AxisTitles(
                      sideTitles: SideTitles(showTitles: false),
                    ),
                    bottomTitles: AxisTitles(
                      sideTitles: SideTitles(
                        showTitles: true,
                        reservedSize: 30,
                        getTitlesWidget: (value, meta) {
                          final date = DateTime.fromMillisecondsSinceEpoch(
                            value.toInt(),
                          );
                          return Padding(
                            padding: const EdgeInsets.only(top: 8.0),
                            child: Text(
                              DateFormat('MMM dd').format(date),
                              style: TextStyle(
                                fontSize: 10,
                                color: Colors
                                    .blueGrey
                                    .shade700, // Consistent text color
                              ),
                            ),
                          );
                        },
                        interval: _calculateDateInterval(sortedData),
                      ),
                    ),
                    leftTitles: AxisTitles(
                      sideTitles: SideTitles(
                        showTitles: true,
                        getTitlesWidget: (value, meta) {
                          return Text(
                            value.toInt().toString(),
                            style: TextStyle(
                              fontSize: 10,
                              color: Colors
                                  .blueGrey
                                  .shade700, // Consistent text color
                            ),
                          );
                        },
                        reservedSize: 40,
                        interval: _calculateValueInterval(
                          maxSystolic.toDouble(),
                          minDiastolic.toDouble(),
                        ),
                      ),
                    ),
                  ),
                  gridData: FlGridData(
                    show: true,
                    drawVerticalLine: true,
                    drawHorizontalLine: true,
                    getDrawingHorizontalLine: (value) => FlLine(
                      color: Colors.blueGrey.shade100,
                      strokeWidth: 1,
                    ), // Lighter grid lines
                    getDrawingVerticalLine: (value) => FlLine(
                      color: Colors.blueGrey.shade100,
                      strokeWidth: 1,
                    ), // Lighter grid lines
                  ),
                  borderData: FlBorderData(
                    show: true,
                    border: Border.all(
                      color: Colors.blueGrey.shade200,
                      width: 1,
                    ), // Softer border
                  ),
                ),
              ),
            ),
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16.0),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                children: [
                  _buildRangeIndicator('Normal', Colors.green),
                  _buildRangeIndicator('Caution', Colors.orange),
                  _buildRangeIndicator('Critical', Colors.red),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildBloodSugarChart() {
    if (_bloodSugars.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.bloodtype_outlined,
              size: 48,
              color: Colors.blueGrey.shade400,
            ),
            SizedBox(height: 16),
            Text(
              'No blood sugar data available',
              style: TextStyle(color: Colors.blueGrey.shade600, fontSize: 16),
            ),
          ],
        ),
      );
    }

    final sortedData = List<BloodSugar>.from(_bloodSugars)
      ..sort((a, b) => a.measurementAt.compareTo(b.measurementAt));

    final minValue = math.max(
      sortedData.map((bs) => bs.value).reduce(math.min) - 20,
      30,
    );
    final maxValue = sortedData.map((bs) => bs.value).reduce(math.max) + 20;

    final ranges = {
      'fasting': RangeValues(70, 100),
      'after_meal': RangeValues(70, 140),
      'random': RangeValues(70, 140),
      'bedtime': RangeValues(90, 150),
    };

    return Card(
      elevation: 4,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      margin: const EdgeInsets.only(bottom: 16),
      child: Container(
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(16),
          color: Colors.white,
        ),
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            SizedBox(
              height: 300,
              child: LineChart(
                LineChartData(
                  minX: sortedData.first.measurementAt.millisecondsSinceEpoch
                      .toDouble(),
                  maxX: sortedData.last.measurementAt.millisecondsSinceEpoch
                      .toDouble(),
                  minY: minValue.toDouble(),
                  maxY: maxValue.toDouble(),
                  lineBarsData: [
                    LineChartBarData(
                      spots: sortedData
                          .map(
                            (bs) => FlSpot(
                              bs.measurementAt.millisecondsSinceEpoch
                                  .toDouble(),
                              bs.value,
                            ),
                          )
                          .toList(),
                      gradient: LinearGradient(
                        colors: [
                          Colors.cyan.shade700,
                          Colors.lightBlue.shade300,
                        ], // Pleasant blue-green gradient
                      ),
                      barWidth: 4,
                      isCurved: true,
                      dotData: FlDotData(
                        show: true,
                        getDotPainter: (spot, percent, barData, index) {
                          final bs = sortedData[index];
                          final range =
                              ranges[bs.measurementType] ?? ranges['random']!;
                          final isCritical = bs.value < 50 || bs.value > 250;
                          final isAbnormal =
                              bs.value < range.start || bs.value > range.end;

                          return FlDotCirclePainter(
                            radius: isCritical ? 6 : 4,
                            color: isCritical
                                ? Colors
                                      .redAccent // More vivid red for critical
                                : isAbnormal
                                ? Colors
                                      .orangeAccent // More vivid orange for abnormal
                                : Colors.green.shade500, // Green for normal
                            strokeWidth: 2,
                            strokeColor: Colors.white,
                          );
                        },
                      ),
                      belowBarData: BarAreaData(
                        show: true,
                        gradient: LinearGradient(
                          colors: [
                            Colors.cyan.shade700.withOpacity(0.15),
                            Colors.lightBlue.shade300.withOpacity(0.05),
                          ],
                        ),
                      ),
                    ),
                  ],
                  titlesData: FlTitlesData(
                    show: true,
                    rightTitles: AxisTitles(
                      sideTitles: SideTitles(showTitles: false),
                    ),
                    topTitles: AxisTitles(
                      sideTitles: SideTitles(showTitles: false),
                    ),
                    bottomTitles: AxisTitles(
                      sideTitles: SideTitles(
                        showTitles: true,
                        reservedSize: 30,
                        getTitlesWidget: (value, meta) {
                          final date = DateTime.fromMillisecondsSinceEpoch(
                            value.toInt(),
                          );
                          return Padding(
                            padding: const EdgeInsets.only(top: 8.0),
                            child: Text(
                              DateFormat('MMM dd').format(date),
                              style: TextStyle(
                                fontSize: 10,
                                color: Colors
                                    .blueGrey
                                    .shade700, // Consistent text color
                              ),
                            ),
                          );
                        },
                        interval: _calculateDateIntervalForSugar(sortedData),
                      ),
                    ),
                    leftTitles: AxisTitles(
                      sideTitles: SideTitles(
                        showTitles: true,
                        getTitlesWidget: (value, meta) {
                          return Text(
                            value.toInt().toString(),
                            style: TextStyle(
                              fontSize: 10,
                              color: Colors
                                  .blueGrey
                                  .shade700, // Consistent text color
                            ),
                          );
                        },
                        reservedSize: 40,
                        interval: _calculateValueIntervalForSugar(
                          maxValue.toDouble(),
                          minValue.toDouble(),
                        ),
                      ),
                    ),
                  ),
                  gridData: FlGridData(
                    show: true,
                    drawVerticalLine: true,
                    drawHorizontalLine: true,
                    getDrawingHorizontalLine: (value) => FlLine(
                      color: Colors.blueGrey.shade100,
                      strokeWidth: 1,
                    ), // Lighter grid lines
                    getDrawingVerticalLine: (value) => FlLine(
                      color: Colors.blueGrey.shade100,
                      strokeWidth: 1,
                    ), // Lighter grid lines
                  ),
                  borderData: FlBorderData(
                    show: true,
                    border: Border.all(
                      color: Colors.blueGrey.shade200,
                      width: 1,
                    ), // Softer border
                  ),
                  lineTouchData: LineTouchData(
                    touchTooltipData: LineTouchTooltipData(
                      getTooltipItems: (List<LineBarSpot> touchedSpots) {
                        return touchedSpots.map((spot) {
                          final bs = sortedData[spot.spotIndex];
                          final range =
                              ranges[bs.measurementType] ?? ranges['random']!;
                          final status = bs.value < range.start
                              ? 'Low'
                              : bs.value > range.end
                              ? 'High'
                              : 'Normal';

                          return LineTooltipItem(
                            '${bs.value} mg/dL (${status})\n${bs.measurementType.replaceAll('_', ' ')}\n${DateFormat('MMM d, h:mm a').format(bs.measurementAt)}',
                            const TextStyle(
                              color: Colors.white,
                              fontWeight: FontWeight.bold,
                            ),
                          );
                        }).toList();
                      },
                    ),
                  ),
                  // Removed range annotations for a cleaner look as per "pretty" request
                  // rangeAnnotations: RangeAnnotations(
                  //   horizontalRangeAnnotations: [
                  //     HorizontalRangeAnnotation(
                  //       y1: 70,
                  //       y2: 140,
                  //       color: Colors.green.withOpacity(0.1),
                  //     ),
                  //     HorizontalRangeAnnotation(
                  //       y1: 50,
                  //       y2: 70,
                  //       color: Colors.orange.withOpacity(0.1),
                  //     ),
                  //     HorizontalRangeAnnotation(
                  //       y1: 0,
                  //       y2: 50,
                  //       color: Colors.red.withOpacity(0.1),
                  //     ),
                  //   ],
                  // ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildRangeIndicator(String label, Color color) {
    return Row(
      children: [
        Container(
          width: 12,
          height: 12,
          decoration: BoxDecoration(color: color, shape: BoxShape.circle),
        ),
        SizedBox(width: 4),
        Text(
          label,
          style: TextStyle(fontSize: 12, color: Colors.blueGrey.shade700),
        ),
      ],
    );
  }

  Widget _buildRecentMeasurementsHeader(String metricType) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16.0),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(
            'Recent Measurements',
            style: TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.bold,
              color: Colors.blue.shade800,
            ),
          ),
          IconButton(
            icon: Icon(Icons.add, color: Colors.blue.shade800),
            onPressed: () => _navigateToAddScreen(metricType),
          ),
        ],
      ),
    );
  }

  Widget _buildBloodPressureCard(BloodPressure entry) {
    return Card(
      margin: const EdgeInsets.symmetric(vertical: 8, horizontal: 16),
      color: Colors.white, // Ensure card is white
      child: InkWell(
        onTap: () {
          _navigateToEditScreen(entry, 'blood_pressure');
        },
        child: Padding(
          padding: const EdgeInsets.all(16.0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    '${entry.systolic}/${entry.diastolic} mmHg',
                    style: TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                      color: Colors.blue.shade800, // Blue text as requested
                    ),
                  ),
                  _buildHealthStatusIndicator(entry.systolic, entry.diastolic),
                ],
              ),
              const SizedBox(height: 8),
              Text(
                DateFormat('MMM d, h:mm a').format(entry.measurementAt),
                style: TextStyle(
                  color: Colors.blueGrey[600],
                ), // Softer grey for date
              ),
              if (entry.notes != null && entry.notes!.isNotEmpty)
                Padding(
                  padding: const EdgeInsets.only(top: 8.0),
                  child: Text(
                    entry.notes!,
                    style: TextStyle(
                      color: Colors.blueGrey[800],
                    ), // Softer grey for notes
                  ),
                ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildBloodSugarCard(BloodSugar entry) {
    return Card(
      margin: const EdgeInsets.symmetric(vertical: 8, horizontal: 16),
      color: Colors.white, // Ensure card is white
      child: InkWell(
        onTap: () {
          _navigateToEditScreen(entry, 'blood_sugar');
        },
        child: Padding(
          padding: const EdgeInsets.all(16.0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    '${entry.value} mg/dL (${entry.measurementType.replaceAll('_', ' ')})',
                    style: TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                      color: Colors
                          .cyan
                          .shade800, // Distinct blue-green for blood sugar text
                    ),
                  ),
                  _buildBloodSugarStatusIndicator(
                    entry.value,
                    entry.measurementType,
                  ),
                ],
              ),
              const SizedBox(height: 8),
              Text(
                DateFormat('MMM d, h:mm a').format(entry.measurementAt),
                style: TextStyle(
                  color: Colors.blueGrey[600],
                ), // Softer grey for date
              ),
              if (entry.notes != null && entry.notes!.isNotEmpty)
                Padding(
                  padding: const EdgeInsets.only(top: 8.0),
                  child: Text(
                    entry.notes!,
                    style: TextStyle(
                      color: Colors.blueGrey[800],
                    ), // Softer grey for notes
                  ),
                ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildHealthStatusIndicator(int systolic, int diastolic) {
    Color color;
    String status;

    if (systolic >= 140 || diastolic >= 90) {
      color = Colors.red;
      status = 'High';
    } else if (systolic <= 90 || diastolic <= 60) {
      color = Colors.blue;
      status = 'Low';
    } else {
      color = Colors.green;
      status = 'Normal';
    }

    return Chip(
      backgroundColor: color.withOpacity(0.2),
      label: Text(
        status,
        style: TextStyle(color: color, fontWeight: FontWeight.bold),
      ),
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
    );
  }

  Widget _buildBloodSugarStatusIndicator(double value, String measurementType) {
    Color color;
    String status;

    if (measurementType == 'fasting') {
      if (value >= 126) {
        color = Colors.red;
        status = 'High';
      } else if (value <= 70) {
        color = Colors.blue;
        status = 'Low';
      } else {
        color = Colors.green;
        status = 'Normal';
      }
    } else if (measurementType == 'after_meal') {
      if (value >= 200) {
        color = Colors.red;
        status = 'High';
      } else if (value <= 70) {
        color = Colors.blue;
        status = 'Low';
      } else {
        color = Colors.green;
        status = 'Normal';
      }
    } else {
      if (value >= 200) {
        color = Colors.red;
        status = 'High';
      } else if (value <= 70) {
        color = Colors.blue;
        status = 'Low';
      } else {
        color = Colors.green;
        status = 'Normal';
      }
    }

    return Chip(
      backgroundColor: color.withOpacity(0.2),
      label: Text(
        status,
        style: TextStyle(color: color, fontWeight: FontWeight.bold),
      ),
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
    );
  }

  void _navigateToEditScreen(dynamic metric, String metricType) {
    Navigator.of(context).push(
      MaterialPageRoute(
        builder: (context) => EditHealthMetricScreen(
          metric: metric,
          metricType: metricType,
          healthMetricService: _healthMetricService,
          onUpdate: _loadData,
          isNew: false,
        ),
      ),
    );
  }

  void _navigateToAddScreen(String metricType) {
    Navigator.of(context).push(
      MaterialPageRoute(
        builder: (context) => EditHealthMetricScreen(
          metric: metricType == 'blood_pressure'
              ? BloodPressure(
                  id: 0,
                  patientId: 0,
                  systolic: 120,
                  diastolic: 80,
                  measurementAt: DateTime.now(),
                  notes: null,
                  createdAt: DateTime.now(),
                  updatedAt: DateTime.now(),
                )
              : BloodSugar(
                  id: 0,
                  value: 100,
                  measurementType: 'fasting',
                  measurementAt: DateTime.now(),
                  notes: null,
                  patientId: 0,
                ),
          metricType: metricType,
          healthMetricService: _healthMetricService,
          onUpdate: _loadData,
          isNew: true,
        ),
      ),
    );
  }

  double _calculateDateInterval(List<BloodPressure> data) {
    if (data.length < 2) {
      return 24 * 60 * 60 * 1000.toDouble();
    }
    final duration = data.last.measurementAt.difference(
      data.first.measurementAt,
    );
    return duration.inDays > 30 ? 7 * 24 * 60 * 60 * 1000 : 24 * 60 * 60 * 1000;
  }

  double _calculateDateIntervalForSugar(List<BloodSugar> data) {
    if (data.length < 2) {
      return 24 * 60 * 60 * 1000.toDouble();
    }
    final duration = data.last.measurementAt.difference(
      data.first.measurementAt,
    );
    return duration.inDays > 30 ? 7 * 24 * 60 * 60 * 1000 : 24 * 60 * 60 * 1000;
  }

  double _calculateValueInterval(double max, double min) {
    final range = max - min;
    if (range <= 20) return 5;
    if (range <= 50) return 10;
    return 20;
  }

  double _calculateValueIntervalForSugar(double max, double min) {
    final range = max - min;
    if (range <= 50) return 10;
    if (range <= 100) return 20;
    return 30;
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        backgroundColor: const Color(0xFF87CEEB), // Sky Blue AppBar
        elevation: 2, // Subtle shadow for the app bar
        title: const Text(
          'Health Metrics',
          style: TextStyle(color: Colors.white), // White title
        ),
        bottom: TabBar(
          controller: _tabController,
          labelColor: Colors.white, // White selected tab text
          unselectedLabelColor: Colors.white.withOpacity(
            0.7,
          ), // Slightly transparent white for unselected
          indicatorColor: Colors.white, // White indicator line
          tabs: const [
            Tab(text: 'Blood Pressure', icon: Icon(Icons.monitor_heart)),
            Tab(text: 'Blood Sugar', icon: Icon(Icons.bloodtype)),
          ],
        ),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : TabBarView(
              controller: _tabController,
              children: [
                // Blood Pressure Tab
                Padding(
                  padding: const EdgeInsets.only(
                    top: 20.0,
                    left: 16.0,
                    right: 16.0,
                  ),
                  child: RefreshIndicator(
                    onRefresh: _loadData,
                    child: SingleChildScrollView(
                      physics: const AlwaysScrollableScrollPhysics(),
                      child: Column(
                        children: [
                          _buildBloodPressureChart(),
                          const SizedBox(height: 16),
                          _buildRecentMeasurementsHeader(
                            'blood_pressure',
                          ), // Use the common header with add button
                          ..._bloodPressures
                              .map((bp) => _buildBloodPressureCard(bp))
                              .toList(), // Use toList() for spread operator
                        ],
                      ),
                    ),
                  ),
                ),
                // Blood Sugar Tab
                Padding(
                  padding: const EdgeInsets.only(
                    top: 20.0,
                    left: 16.0,
                    right: 16.0,
                  ),
                  child: RefreshIndicator(
                    onRefresh: _loadData,
                    child: SingleChildScrollView(
                      physics: const AlwaysScrollableScrollPhysics(),
                      child: Column(
                        children: [
                          _buildBloodSugarChart(),
                          const SizedBox(height: 16),
                          _buildRecentMeasurementsHeader(
                            'blood_sugar',
                          ), // Use the common header with add button
                          ..._bloodSugars
                              .map((bs) => _buildBloodSugarCard(bs))
                              .toList(), // Use toList() for spread operator
                        ],
                      ),
                    ),
                  ),
                ),
              ],
            ),
      floatingActionButton: FloatingActionButton(
        onPressed: () {
          showDialog(
            context: context,
            builder: (context) => AlertDialog(
              backgroundColor: Colors.white,
              title: const Text('Add New Measurement'),
              content: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  ListTile(
                    leading: const Icon(
                      Icons.monitor_heart,
                      color: Colors.blue,
                    ),
                    title: const Text('Blood Pressure'),
                    onTap: () {
                      Navigator.pop(context);
                      _navigateToAddScreen('blood_pressure');
                    },
                  ),
                  ListTile(
                    leading: const Icon(
                      Icons.bloodtype,
                      color: Colors.cyan,
                    ), // Changed to a shade of blue-green
                    title: const Text('Blood Sugar'),
                    onTap: () {
                      Navigator.pop(context);
                      _navigateToAddScreen('blood_sugar');
                    },
                  ),
                ],
              ),
            ),
          );
        },
        backgroundColor: Colors.lightBlueAccent[400], // Match AppBar color
        child: const Icon(
          Icons.add,
          color: Color.fromARGB(255, 255, 255, 255),
        ), // White plus icon
      ),
    );
  }
}
