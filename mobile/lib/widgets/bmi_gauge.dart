import 'package:flutter/material.dart';
// ignore: depend_on_referenced_packages
import 'package:syncfusion_flutter_gauges/gauges.dart'; // Import the Syncfusion Gauges library

class BMIGauge extends StatelessWidget {
  final double? bmi;

  const BMIGauge({Key? key, this.bmi}) : super(key: key);

  // Helper function to get BMI category based on value
  String _getBmiCategory(double bmiValue) {
    if (bmiValue < 18.5) {
      return 'Underweight'; //
    } else if (bmiValue >= 18.5 && bmiValue < 25) {
      return 'Normal Weight'; //
    } else if (bmiValue >= 25 && bmiValue < 30) {
      return 'Overweight'; //
    } else if (bmiValue >= 30 && bmiValue < 35) {
      return 'Obese'; //
    } else {
      return 'Extremely Obese'; //
    }
  }

  // Helper function to get color for BMI category
  Color _getBmiColor(double bmiValue) {
    if (bmiValue < 18.5) {
      return const Color(0xFF6A9DD8); // Blue for Underweight
    } else if (bmiValue >= 18.5 && bmiValue < 25) {
      return const Color(0xFF8BC34A); // Green for Normal Weight
    } else if (bmiValue >= 25 && bmiValue < 30) {
      return const Color(0xFFFFC107); // Yellow/Amber for Overweight
    } else if (bmiValue >= 30 && bmiValue < 35) {
      return const Color(0xFFFB8C00); // Orange for Obese
    } else {
      return const Color(0xFFE53935); // Red for Extremely Obese
    }
  }

  @override
  Widget build(BuildContext context) {
    // If BMI is null, return an empty container. The HomeScreen will display the "BMI data not available" message.
    if (bmi == null) {
      return Container(
        height: 250, // Provide some height even when empty to maintain layout
        alignment: Alignment.center,
      );
    }

    final String bmiCategory = _getBmiCategory(bmi!);
    final Color bmiColor = _getBmiColor(bmi!);

    return Container(
      padding: const EdgeInsets.symmetric(vertical: 20.0, horizontal: 10.0),
      margin: const EdgeInsets.symmetric(horizontal: 0), // Adjust margin as needed
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(15),
        boxShadow: [
          BoxShadow(
            color: Colors.grey.withOpacity(0.1),
            spreadRadius: 2,
            blurRadius: 5,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      child: Column(
        children: [
          SizedBox(
            height: 180, // Height for the gauge itself
            child: SfRadialGauge(
              axes: <RadialAxis>[
                RadialAxis(
                  minimum: 0,
                  maximum: 40, // Max BMI value for the gauge. You can adjust this.
                  showLabels: false,
                  showTicks: false,
                  startAngle: 180, // Start from the left
                  endAngle: 0, // End at the right to make it a half circle
                  axisLineStyle: const AxisLineStyle(
                    thickness: 0, // No default axis line, ranges will define it
                  ),
                  pointers: <GaugePointer>[
                    NeedlePointer(
                      value: bmi!.clamp(0.0, 40.0), // Clamp BMI to gauge range
                      enableAnimation: true,
                      animationDuration: 1000,
                      needleStartWidth: 0,
                      needleEndWidth: 6,
                      needleColor: Colors.black, // Dark needle as in screenshot
                      knobStyle: const KnobStyle(
                        knobRadius: 0.06,
                        color: Colors.black, // Dark knob as in screenshot
                      ),
                    ),
                  ],
                  ranges: <GaugeRange>[
                    GaugeRange(
                      startValue: 0,
                      endValue: 18.5,
                      color: const Color(0xFF6A9DD8), // Underweight blue
                      startWidth: 25, // Thicker ranges
                      endWidth: 25,
                    ),
                    GaugeRange(
                      startValue: 18.5,
                      endValue: 25,
                      color: const Color(0xFF8BC34A), // Normal Weight green
                      startWidth: 25,
                      endWidth: 25,
                    ),
                    GaugeRange(
                      startValue: 25,
                      endValue: 30,
                      color: const Color(0xFFFFC107), // Overweight yellow
                      startWidth: 25,
                      endWidth: 25,
                    ),
                    GaugeRange(
                      startValue: 30,
                      endValue: 35,
                      color: const Color(0xFFFB8C00), // Obese orange
                      startWidth: 25,
                      endWidth: 25,
                    ),
                    GaugeRange(
                      startValue: 35,
                      endValue: 40,
                      color: const Color(0xFFE53935), // Extremely Obese red
                      startWidth: 25,
                      endWidth: 25,
                    ),
                  ],
                  annotations: <GaugeAnnotation>[
                    GaugeAnnotation(
                      angle: 90, // Center annotation
                      positionFactor: 0.1, // Adjust vertical position
                      widget: Column(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Text(
                            bmi!.toStringAsFixed(1), // Display BMI value with 1 decimal place
                            style: TextStyle(
                              fontSize: 38, // Larger font for BMI value
                              fontWeight: FontWeight.bold,
                              color: bmiColor, // Color based on BMI category
                            ),
                          ),
                          Text(
                            bmiCategory,
                            style: TextStyle(
                              fontSize: 16, // Font for category
                              fontWeight: FontWeight.w600,
                              color: bmiColor, // Color based on BMI category
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
          const SizedBox(height: 10), // Spacing between gauge and legend

          // BMI category legend as seen in the screenshot
          Wrap(
            alignment: WrapAlignment.center,
            spacing: 8.0, // horizontal spacing
            runSpacing: 4.0, // vertical spacing
            children: [
              _buildBmiLegendItem(const Color(0xFF6A9DD8), '<18.5 Underweight'), //
              _buildBmiLegendItem(const Color(0xFF8BC34A), '18.5-24.9 Normal'), //
              _buildBmiLegendItem(const Color(0xFFFFC107), '25-29.9 Overweight'), //
              _buildBmiLegendItem(const Color(0xFFFB8C00), '30-34.9 Obese'), //
              _buildBmiLegendItem(const Color(0xFFE53935), '>=35.0 Extremely Obese'), //
            ],
          ),
        ],
      ),
    );
  }

  // Helper for building legend items
  Widget _buildBmiLegendItem(Color color, String text) {
    return Row(
      mainAxisSize: MainAxisSize.min, // Keep items close
      children: [
        Container(
          width: 12,
          height: 12,
          decoration: BoxDecoration(
            color: color,
            borderRadius: BorderRadius.circular(3), // Slightly rounded square
          ),
        ),
        const SizedBox(width: 6),
        Text(text, style: const TextStyle(fontSize: 11.5)), // Smaller font for legend
      ],
    );
  }
}