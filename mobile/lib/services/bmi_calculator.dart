import 'package:flutter/material.dart';
import 'package:flutter_application_1/widgets/bmi_gauge.dart'; // Import the BMIGauge widget

class BMICalculator extends StatefulWidget {
  const BMICalculator({super.key});

  @override
  State<BMICalculator> createState() => _BMICalculatorState();
}

class _BMICalculatorState extends State<BMICalculator> {
  final TextEditingController _heightController = TextEditingController();
  final TextEditingController _weightController = TextEditingController();

  double _bmiResult = 0.0;
  String _bmiCategory = '';
  Color _categoryColor = Colors.black;

  // Function to calculate BMI
  void _calculateBMI() {
    // Dismiss the keyboard
    FocusScope.of(context).unfocus();

    final String heightStr = _heightController.text;
    final String weightStr = _weightController.text;

    if (heightStr.isEmpty || weightStr.isEmpty) {
      _showSnackBar('Please enter both height and weight.');
      return;
    }

    final double? heightCm = double.tryParse(heightStr);
    final double? weightKg = double.tryParse(weightStr);

    if (heightCm == null || weightKg == null || heightCm <= 0 || weightKg <= 0) {
      _showSnackBar('Please enter valid positive numbers for height and weight.');
      return;
    }

    // Convert height from centimeters to meters
    final double heightMeters = heightCm / 100;

    setState(() {
      _bmiResult = weightKg / (heightMeters * heightMeters);
      _bmiCategory = _getBMICategory(_bmiResult);
      _categoryColor = _getCategoryColor(_bmiResult);
    });
  }

  // Determine BMI category - aligning with BMIGauge for consistency
  String _getBMICategory(double bmi) {
    if (bmi < 18.5) {
      return 'Underweight';
    } else if (bmi >= 18.5 && bmi < 25) {
      return 'Normal Weight';
    } else if (bmi >= 25 && bmi < 30) {
      return 'Overweight';
    } else if (bmi >= 30 && bmi < 35) {
      return 'Obese';
    } else {
      return 'Extremely Obese';
    }
  }

  // Determine color based on BMI category - aligning with BMIGauge for consistency
  Color _getCategoryColor(double bmi) {
    if (bmi < 18.5) {
      return Colors.blue; // Underweight
    } else if (bmi >= 18.5 && bmi < 25) {
      return Colors.green; // Normal Weight
    } else if (bmi >= 25 && bmi < 30) {
      return Colors.yellow.shade700; // Overweight
    } else if (bmi >= 30 && bmi < 35) {
      return Colors.orange; // Obese
    } else {
      return Colors.red; // Extremely Obese
    }
  }

  void _showSnackBar(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(message)),
    );
  }

  @override
  void dispose() {
    _heightController.dispose();
    _weightController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      child: Container(
        padding: EdgeInsets.only(
          left: 20,
          right: 20,
          top: 20,
          bottom: MediaQuery.of(context).viewInsets.bottom + 20, // Adjust for keyboard
        ),
        decoration: const BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min, // Make column only take necessary space
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Align(
              alignment: Alignment.center,
              child: Container(
                width: 40,
                height: 5,
                margin: const EdgeInsets.only(bottom: 15),
                decoration: BoxDecoration(
                  color: Colors.grey[300],
                  borderRadius: BorderRadius.circular(5),
                ),
              ),
            ),
            Text(
              'BMI Calculator',
              style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                    fontWeight: FontWeight.bold,
                    color: const Color(0xFF4682B4), // Steel Blue
                  ),
            ),
            const Divider(height: 30, thickness: 1),
            TextField(
              controller: _heightController,
              keyboardType: TextInputType.number,
              decoration: InputDecoration(
                labelText: 'Height (cm)',
                hintText: 'e.g., 170',
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(10),
                ),
                prefixIcon: const Icon(Icons.height, color: Color(0xFF87CEEB)), // Blue Sky
              ),
            ),
            const SizedBox(height: 15),
            TextField(
              controller: _weightController,
              keyboardType: TextInputType.number,
              decoration: InputDecoration(
                labelText: 'Weight (kg)',
                hintText: 'e.g., 70',
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(10),
                ),
                prefixIcon: const Icon(Icons.fitness_center, color: Color(0xFF87CEEB)), // Blue Sky
              ),
            ),
            const SizedBox(height: 25),
            Center(
              child: ElevatedButton.icon(
                onPressed: _calculateBMI,
                icon: const Icon(Icons.calculate, color: Colors.white),
                label: const Text(
                  'Calculate BMI',
                  style: TextStyle(color: Colors.white, fontSize: 16),
                ),
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF4682B4), // Steel Blue
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(10),
                  ),
                  padding: const EdgeInsets.symmetric(horizontal: 30, vertical: 15),
                ),
              ),
            ),
            if (_bmiResult > 0) ...[
              const SizedBox(height: 25),
              // Use the BMIGauge widget here
              BMIGauge(bmi: _bmiResult),
              const SizedBox(height: 15),
              Center(
                child: Text(
                  'Note: BMI is a screening tool and not a diagnostic tool.',
                  style: TextStyle(fontSize: 12, color: Colors.grey[600]),
                  textAlign: TextAlign.center,
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }
}