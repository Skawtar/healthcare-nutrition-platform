import 'package:flutter/material.dart';
import 'package:flutter_application_1/models/blood_pressure.dart';
import 'package:flutter_application_1/models/blood_sugar.dart';
import 'package:flutter_application_1/services/health_metric_service.dart';
import 'package:intl/intl.dart';

class EditHealthMetricScreen extends StatefulWidget {
  final dynamic metric;
  final String metricType;
  final HealthMetricService healthMetricService;
  final Function() onUpdate;
  final bool isNew;

  const EditHealthMetricScreen({
    super.key,
    required this.metric,
    required this.metricType,
    required this.healthMetricService,
    required this.onUpdate,
    required this.isNew,
  });

  @override
  State<EditHealthMetricScreen> createState() => _EditHealthMetricScreenState();
}

class _EditHealthMetricScreenState extends State<EditHealthMetricScreen> {
  late final TextEditingController _systolicController;
  late final TextEditingController _diastolicController;
  late final TextEditingController _valueController;
  late final TextEditingController _notesController;
  late DateTime _measurementDate;
  late TimeOfDay _measurementTime;
  late String _measurementType;
  bool _isLoading = false;

  @override
  void initState() {
    super.initState();
    _initializeForm();
  }

  void _initializeForm() {
    final metric = widget.metric;
    final measurementAt = metric.measurementAt;

    _measurementDate = measurementAt;
    _measurementTime = TimeOfDay.fromDateTime(measurementAt);
    _notesController = TextEditingController(text: metric.notes ?? '');

    if (widget.metricType == 'blood_pressure') {
      _systolicController =
          TextEditingController(text: metric.systolic.toString());
      _diastolicController =
          TextEditingController(text: metric.diastolic.toString());
    } else {
      _valueController = TextEditingController(text: metric.value.toString());
      _measurementType = metric.measurementType ?? 'fasting';
    }
  }

  @override
  void dispose() {
    _notesController.dispose();
    if (widget.metricType == 'blood_pressure') {
      _systolicController.dispose();
      _diastolicController.dispose();
    } else {
      _valueController.dispose();
    }
    super.dispose();
  }

  Future<void> _selectDate(BuildContext context) async {
    final DateTime? picked = await showDatePicker(
      context: context,
      initialDate: _measurementDate,
      firstDate: DateTime(2000),
      lastDate: DateTime.now().add(const Duration(days: 1)),
    );
    if (picked != null) {
      setState(() {
        _measurementDate = DateTime(
          picked.year,
          picked.month,
          picked.day,
          _measurementTime.hour,
          _measurementTime.minute,
        );
      });
    }
  }

  Future<void> _selectTime(BuildContext context) async {
    final TimeOfDay? picked = await showTimePicker(
      context: context,
      initialTime: _measurementTime,
    );
    if (picked != null) {
      setState(() {
        _measurementTime = picked;
        _measurementDate = DateTime(
          _measurementDate.year,
          _measurementDate.month,
          _measurementDate.day,
          picked.hour,
          picked.minute,
        );
      });
    }
  }

  Future<void> _submitForm() async {
    if (!_validateForm()) return;

    setState(() => _isLoading = true);

    try {
      final measurementAt = _measurementDate;
      final notes =
          _notesController.text.trim().isEmpty ? null : _notesController.text.trim();

      if (widget.metricType == 'blood_pressure') {
        final bloodPressure = BloodPressure(
          id: widget.metric.id,
          patientId: widget.metric.patientId,
          systolic: int.parse(_systolicController.text),
          diastolic: int.parse(_diastolicController.text),
          measurementAt: measurementAt,
          notes: notes,
          createdAt: DateTime.now(),
          updatedAt: DateTime.now(),
        );

        if (widget.isNew) {
          await widget.healthMetricService.addBloodPressure(bloodPressure);
        } else {
          await widget.healthMetricService.updateBloodPressure(bloodPressure);
        }
      } else {
        final bloodSugar = BloodSugar(
          id: widget.metric.id,
          patientId: widget.metric.patientId,
          value: double.parse(_valueController.text),
          measurementType: _measurementType,
          measurementAt: measurementAt,
          notes: notes,
        );

        if (widget.isNew) {
          await widget.healthMetricService.addBloodSugar(bloodSugar);
        } else {
          await widget.healthMetricService.updateBloodSugar(bloodSugar);
        }
      }

      widget.onUpdate();
      if (mounted) {
        Navigator.of(context).pop();
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
              content:
                  Text('Measurement ${widget.isNew ? 'added' : 'updated'} successfully')),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error: ${e.toString()}')),
        );
      }
    } finally {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  bool _validateForm() {
    if (widget.metricType == 'blood_pressure') {
      if (_systolicController.text.isEmpty || _diastolicController.text.isEmpty) {
        _showError('Please enter both systolic and diastolic values');
        return false;
      }

      final systolic = int.tryParse(_systolicController.text);
      final diastolic = int.tryParse(_diastolicController.text);

      if (systolic == null || diastolic == null) {
        _showError('Please enter valid numbers');
        return false;
      }

      if (systolic <= 0 || diastolic <= 0) {
        _showError('Values must be positive numbers');
        return false;
      }

      if (systolic < diastolic) {
        _showError('Systolic must be higher than diastolic');
        return false;
      }
    } else {
      if (_valueController.text.isEmpty) {
        _showError('Please enter a blood sugar value');
        return false;
      }

      final value = double.tryParse(_valueController.text);

      if (value == null) {
        _showError('Please enter a valid number');
        return false;
      }

      if (value <= 0) {
        _showError('Value must be a positive number');
        return false;
      }
    }

    return true;
  }

  void _showError(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: Colors.red,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white, // Set Scaffold background to white
      appBar: AppBar(
        backgroundColor: Color(0xFF87CEEB), // Set AppBar background to sky blue
        elevation: 0, // Remove shadow for a clean look
        iconTheme: const IconThemeData(color: Color.fromARGB(255, 255, 255, 255)), // Black back arrow
        title: Text(
          '${widget.isNew ? 'Add' : 'Edit'} ${widget.metricType == 'blood_pressure' ? 'Blood Pressure' : 'Blood Sugar'}',
          style: const TextStyle(color: Color.fromARGB(255, 255, 255, 255)), // Black title text
        ),
        actions: widget.isNew
            ? null
            : [
                IconButton(
                  icon: const Icon(Icons.delete, color: Colors.red), // Red delete icon
                  onPressed: _isLoading ? null : _confirmDelete,
                ),
              ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : SingleChildScrollView(
              padding: const EdgeInsets.all(16.0),
              child: Form(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    if (widget.metricType == 'blood_pressure') ...[
                      _buildNumberField(
                        controller: _systolicController,
                        label: 'Systolic (mmHg)',
                        minValue: 1,
                        maxValue: 300,
                      ),
                      const SizedBox(height: 16),
                      _buildNumberField(
                        controller: _diastolicController,
                        label: 'Diastolic (mmHg)',
                        minValue: 1,
                        maxValue: 200,
                      ),
                    ] else ...[
                      _buildNumberField(
                        controller: _valueController,
                        label: 'Blood Sugar (mg/dL)',
                        minValue: 1,
                        maxValue: 500,
                      ),
                      const SizedBox(height: 16),
                      _buildMeasurementTypeDropdown(),
                    ],
                    const SizedBox(height: 16),
                    _buildDateTimePicker(),
                    const SizedBox(height: 16),
                    _buildNotesField(),
                    const SizedBox(height: 24),
                    _buildSubmitButton(),
                  ],
                ),
              ),
            ),
    );
  }

  Widget _buildNumberField({
    required TextEditingController controller,
    required String label,
    required int minValue,
    required int maxValue,
  }) {
    return TextFormField(
      controller: controller,
      decoration: InputDecoration(
        labelText: label,
        border: const OutlineInputBorder(),
        suffixText: widget.metricType == 'blood_pressure' ? 'mmHg' : 'mg/dL',
      ),
      keyboardType: TextInputType.number,
      validator: (value) {
        if (value == null || value.isEmpty) return 'Required';
        final numValue = num.tryParse(value);
        if (numValue == null) return 'Invalid number';
        if (numValue < minValue) return 'Too low';
        if (numValue > maxValue) return 'Too high';
        return null;
      },
    );
  }

  Widget _buildMeasurementTypeDropdown() {
    return DropdownButtonFormField<String>(
      value: _measurementType,
      decoration: const InputDecoration(
        labelText: 'Measurement Type',
        border: OutlineInputBorder(),
      ),
      items: const [
        DropdownMenuItem(
          value: 'fasting',
          child: Text('Fasting'),
        ),
        DropdownMenuItem(
          value: 'after_meal',
          child: Text('After Meal'),
        ),
        DropdownMenuItem(
          value: 'random',
          child: Text('Random'),
        ),
        DropdownMenuItem(
          value: 'bedtime',
          child: Text('Bedtime'),
        ),
      ],
      onChanged: (value) => setState(() => _measurementType = value!),
    );
  }

  Widget _buildDateTimePicker() {
    return Row(
      children: [
        Expanded(
          child: OutlinedButton.icon(
            icon: const Icon(Icons.calendar_today, color: Colors.blue), // Blue icon
            label: Text(
              DateFormat('yyyy-MM-dd').format(_measurementDate),
              style: const TextStyle(color: Colors.black87), // Darker text for readability
            ),
            onPressed: () => _selectDate(context),
            style: OutlinedButton.styleFrom(
              padding: const EdgeInsets.symmetric(vertical: 16),
              side: const BorderSide(color: Colors.grey), // Grey border
            ),
          ),
        ),
        const SizedBox(width: 8),
        Expanded(
          child: OutlinedButton.icon(
            icon: const Icon(Icons.access_time, color: Colors.blue), // Blue icon
            label: Text(
              _measurementTime.format(context),
              style: const TextStyle(color: Colors.black87), // Darker text for readability
            ),
            onPressed: () => _selectTime(context),
            style: OutlinedButton.styleFrom(
              padding: const EdgeInsets.symmetric(vertical: 16),
              side: const BorderSide(color: Colors.grey), // Grey border
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildNotesField() {
    return TextFormField(
      controller: _notesController,
      decoration: const InputDecoration(
        labelText: 'Notes (Optional)',
        border: OutlineInputBorder(),
      ),
      maxLines: 3,
    );
  }

  Widget _buildSubmitButton() {
    return ElevatedButton(
      onPressed: _isLoading ? null : _submitForm,
      style: ElevatedButton.styleFrom(
        backgroundColor:  Colors.lightBlueAccent[400], 
        padding: const EdgeInsets.symmetric(vertical: 16),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(8), // Slightly rounded corners
        ),
      ),
      child: Text(
        widget.isNew ? 'Add Measurement' : 'Save Changes',
        style: const TextStyle(color: Colors.white, fontSize: 16), // White text on button
      ),
    );
  }

  Future<void> _confirmDelete() async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Confirm Delete'),
        content: const Text('Are you sure you want to delete this measurement?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(false),
            child: const Text('Cancel'),
          ),
          TextButton(
            onPressed: () => Navigator.of(context).pop(true),
            child: const Text('Delete', style: TextStyle(color: Colors.red)),
          ),
        ],
      ),
    );

    if (confirmed == true) {
      setState(() => _isLoading = true);
      try {
        if (widget.metricType == 'blood_pressure') {
          await widget.healthMetricService.deleteBloodPressure(widget.metric.id);
        } else {
          await widget.healthMetricService.deleteBloodSugar(widget.metric.id);
        }

        widget.onUpdate();
        if (mounted) {
          Navigator.of(context).pop();
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Measurement deleted successfully')),
          );
        }
      } catch (e) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text('Error: ${e.toString()}')),
          );
        }
      } finally {
        if (mounted) {
          setState(() => _isLoading = false);
        }
      }
    }
  }
}