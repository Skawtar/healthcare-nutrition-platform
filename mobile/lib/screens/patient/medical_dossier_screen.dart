import 'package:flutter/material.dart';
import 'package:flutter_application_1/services/auth/api_service.dart';
import 'package:intl/intl.dart';
import 'package:flutter_application_1/models/medical_dossier.dart';
import 'package:flutter_application_1/screens/home_screen.dart';
import 'package:flutter_application_1/services/MedicalDossier-service.dart';

class MedicalDossierScreen extends StatefulWidget {
  @override
  _MedicalDossierScreenState createState() => _MedicalDossierScreenState();
}

class _MedicalDossierScreenState extends State<MedicalDossierScreen> {
  final _formKey = GlobalKey<FormState>();
  late final MedicalDossierService _medicalDossierService;

  // Form controllers
  final TextEditingController _weightController = TextEditingController();
  final TextEditingController _heightController = TextEditingController();
  final TextEditingController _allergiesController = TextEditingController();
  final TextEditingController _conditionsController = TextEditingController();
  final TextEditingController _medicationsController = TextEditingController();

  // Form state
  String? _bloodType;
  DateTime? _lastConsultationDate;
  String? _errorMessage;
  bool _isLoading = false;
  bool _isSaving = false;

  // Constants
  final List<String> _bloodTypes = [
    'A+',
    'A-',
    'B+',
    'B-',
    'AB+',
    'AB-',
    'O+',
    'O-',
  ];
  final DateFormat _dateFormat = DateFormat('MMM d, yyyy');

  // Color scheme
  final Color _primaryColor = const Color.fromARGB(255, 160, 196, 225); // Steel blue
  final Color _secondaryColor = const Color(0xFF6C8DFF); // Lighter blue
  final Color _accentColor = const Color(0xFF5D7AEA); // Medium blue
  final Color _textColor = const Color.fromARGB(255, 158, 201, 244); // Dark gray for text
  final Color _borderColor = const Color(0xFFDDDDDD); // Light gray for borders
  final Color _iconColor = const Color.fromARGB(239, 113, 182, 237); // Medium gray for icons

  @override
  void initState() {
    super.initState();
    _medicalDossierService = MedicalDossierService(apiService: ApiService());
  }

  @override
  void dispose() {
    _weightController.dispose();
    _heightController.dispose();
    _allergiesController.dispose();
    _conditionsController.dispose();
    _medicationsController.dispose();
    super.dispose();
  }

  void _updateFormFields(MedicalDossier dossier) {
    setState(() {
      _weightController.text = dossier.poids?.toStringAsFixed(1) ?? '';
      _heightController.text = dossier.taille?.toStringAsFixed(1) ?? '';
      _bloodType = dossier.groupeSanguin;
      _lastConsultationDate = dossier.derniereConsultation;
      _allergiesController.text = dossier.allergies?.join('\n') ?? '';
      _conditionsController.text = dossier.antecedents?.join('\n') ?? '';
      _medicationsController.text = dossier.traitements?.join('\n') ?? '';
    });
  }

  Future<void> _saveMedicalDossier() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() {
      _isSaving = true;
      _errorMessage = null;
    });

    try {
      final String allergiesString = _allergiesController.text
          .split('\n')
          .map((e) => e.trim())
          .where((e) => e.isNotEmpty)
          .join(',');

      final String conditionsString = _conditionsController.text
          .split('\n')
          .map((e) => e.trim())
          .where((e) => e.isNotEmpty)
          .join(',');

      final String medicationsString = _medicationsController.text
          .split('\n')
          .map((e) => e.trim())
          .where((e) => e.isNotEmpty)
          .join(',');

      await _medicalDossierService.createMedicalDossier(
        poids: double.tryParse(_weightController.text)!,
        taille: double.tryParse(_heightController.text)!,
        groupeSanguin: _bloodType,
        allergies: allergiesString,
        antecedents: conditionsString,
        traitements: medicationsString,
        derniereConsultation: _lastConsultationDate,
      );

      _showSuccessMessage();
      _navigateToHome();
    } catch (e) {
      _handleError('Failed to save medical dossier', e);
    } finally {
      if (mounted) {
        setState(() => _isSaving = false);
      }
    }
  }

  void _showSuccessMessage() {
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text('Medical dossier saved successfully'),
        backgroundColor: Colors.green,
        duration: Duration(seconds: 2),
      ),
    );
  }

  void _navigateToHome() {
    Navigator.pushReplacement(
      context,
      MaterialPageRoute(builder: (context) => HomeScreen()),
    );
  }

  void _handleError(String message, dynamic error) {
    debugPrint('$message: $error');
    setState(() {
      _errorMessage = '$message: ${error.toString()}';
    });
  }

  Future<void> _selectDate(BuildContext context) async {
    final pickedDate = await showDatePicker(
      context: context,
      initialDate: _lastConsultationDate ?? DateTime.now(),
      firstDate: DateTime(1900),
      lastDate: DateTime.now(),
      builder: (context, child) {
        return Theme(
          data: Theme.of(context).copyWith(
            colorScheme: ColorScheme.light(
              primary: _primaryColor,
              onPrimary: Colors.white,
              onSurface: _textColor,
            ),
            textButtonTheme: TextButtonThemeData(
              style: TextButton.styleFrom(
                foregroundColor: _primaryColor,
              ),
            ),
          ),
          child: child!,
        );
      },
    );

    if (pickedDate != null && pickedDate != _lastConsultationDate) {
      setState(() => _lastConsultationDate = pickedDate);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Medical Dossier', style: TextStyle(color: Colors.white)),
        backgroundColor: _primaryColor,
        iconTheme: const IconThemeData(color: Colors.white),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : SingleChildScrollView(
              padding: const EdgeInsets.all(16.0),
              child: Form(
                key: _formKey,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    if (_errorMessage != null) _buildErrorWidget(),
                    _buildWeightField(),
                    const SizedBox(height: 16.0),
                    _buildHeightField(),
                    const SizedBox(height: 16.0),
                    _buildBloodTypeDropdown(),
                    const SizedBox(height: 16.0),
                    _buildAllergiesField(),
                    const SizedBox(height: 16.0),
                    _buildConditionsField(),
                    const SizedBox(height: 16.0),
                    _buildMedicationsField(),
                    const SizedBox(height: 16.0),
                    _buildLastConsultationTile(),
                    const SizedBox(height: 24.0),
                    _buildSaveButton(),
                  ],
                ),
              ),
            ),
    );
  }

  Widget _buildErrorWidget() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(12.0),
      decoration: BoxDecoration(
        color: Colors.red[50],
        borderRadius: BorderRadius.circular(8.0),
        border: Border.all(color: Colors.red[200]!),
      ),
      child: Text(
        _errorMessage!,
        style: TextStyle(color: Colors.red[700]),
      ),
    );
  }

  Widget _buildWeightField() {
    return TextFormField(
      controller: _weightController,
      decoration: InputDecoration(
        labelText: 'Weight (kg)',
        labelStyle: TextStyle(color: _textColor),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(8.0),
          borderSide: BorderSide(color: _borderColor),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(8.0),
          borderSide: BorderSide(color: _primaryColor, width: 1.5),
        ),
        prefixIcon: Icon(Icons.monitor_weight, color: _iconColor),
      ),
      style: TextStyle(color: _textColor),
      keyboardType: TextInputType.number,
      validator: (value) {
        if (value == null || value.isEmpty) return 'Please enter your weight';
        final weight = double.tryParse(value);
        if (weight == null) return 'Enter a valid number';
        if (weight <= 0) return 'Weight must be positive';
        if (weight > 300) return 'Weight seems too high';
        return null;
      },
    );
  }

  Widget _buildHeightField() {
    return TextFormField(
      controller: _heightController,
      decoration: InputDecoration(
        labelText: 'Height (cm)',
        labelStyle: TextStyle(color: _textColor),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(8.0),
          borderSide: BorderSide(color: _borderColor),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(8.0),
          borderSide: BorderSide(color: _primaryColor, width: 1.5),
        ),
        prefixIcon: Icon(Icons.height, color: _iconColor),
      ),
      style: TextStyle(color: _textColor),
      keyboardType: TextInputType.number,
      validator: (value) {
        if (value == null || value.isEmpty) return 'Please enter your height';
        final height = double.tryParse(value);
        if (height == null) return 'Enter a valid number';
        if (height <= 0) return 'Height must be positive';
        if (height > 250) return 'Height seems too high';
        return null;
      },
    );
  }

  Widget _buildBloodTypeDropdown() {
    return DropdownButtonFormField<String>(
      value: _bloodType,
      decoration: InputDecoration(
        labelText: 'Blood Type',
        labelStyle: TextStyle(color: _textColor),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(8.0),
          borderSide: BorderSide(color: _borderColor),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(8.0),
          borderSide: BorderSide(color: _primaryColor, width: 1.5),
        ),
        prefixIcon: Icon(Icons.bloodtype, color: _iconColor),
      ),
      style: TextStyle(color: _textColor),
      dropdownColor: Colors.white,
      icon: Icon(Icons.arrow_drop_down, color: _iconColor),
      items: _bloodTypes
          .map((type) => DropdownMenuItem(
                value: type,
                child: Text(type, style: TextStyle(color: _textColor)),
              ))
          .toList(),
      onChanged: (value) => setState(() => _bloodType = value),
    );
  }

  Widget _buildAllergiesField() {
    return TextFormField(
      controller: _allergiesController,
      decoration: InputDecoration(
        labelText: 'Allergies (one per line)',
        labelStyle: TextStyle(color: _textColor),
        alignLabelWithHint: true,
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(8.0),
          borderSide: BorderSide(color: _borderColor),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(8.0),
          borderSide: BorderSide(color: _primaryColor, width: 1.5),
        ),
        prefixIcon: Icon(Icons.warning, color: _iconColor),
      ),
      style: TextStyle(color: _textColor),
      maxLines: 3,
    );
  }

  Widget _buildConditionsField() {
    return TextFormField(
      controller: _conditionsController,
      decoration: InputDecoration(
        labelText: 'Medical Conditions (one per line)',
        labelStyle: TextStyle(color: _textColor),
        alignLabelWithHint: true,
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(8.0),
          borderSide: BorderSide(color: _borderColor),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(8.0),
          borderSide: BorderSide(color: _primaryColor, width: 1.5),
        ),
        prefixIcon: Icon(Icons.medical_services, color: _iconColor),
      ),
      style: TextStyle(color: _textColor),
      maxLines: 3,
    );
  }

  Widget _buildMedicationsField() {
    return TextFormField(
      controller: _medicationsController,
      decoration: InputDecoration(
        labelText: 'Medications (one per line)',
        labelStyle: TextStyle(color: _textColor),
        alignLabelWithHint: true,
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(8.0),
          borderSide: BorderSide(color: _borderColor),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(8.0),
          borderSide: BorderSide(color: _primaryColor, width: 1.5),
        ),
        prefixIcon: Icon(Icons.medication, color: _iconColor),
      ),
      style: TextStyle(color: _textColor),
      maxLines: 3,
    );
  }

  Widget _buildLastConsultationTile() {
    return Container(
      decoration: BoxDecoration(
        border: Border.all(color: _borderColor),
        borderRadius: BorderRadius.circular(8.0),
      ),
      child: ListTile(
        title: Text(
          _lastConsultationDate == null
              ? 'Select last consultation date'
              : 'Last consultation: ${_dateFormat.format(_lastConsultationDate!)}',
          style: TextStyle(color: _textColor),
        ),
        trailing: Icon(Icons.calendar_today, color: _iconColor),
        onTap: () => _selectDate(context),
      ),
    );
  }

  Widget _buildSaveButton() {
    return SizedBox(
      width: double.infinity,
      child: ElevatedButton(
        onPressed: _isSaving ? null : _saveMedicalDossier,
        style: ElevatedButton.styleFrom(
          backgroundColor: _primaryColor,
          padding: const EdgeInsets.symmetric(vertical: 16.0),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(8.0),
          ),
          elevation: 2,
        ),
        child: _isSaving
            ? const SizedBox(
                width: 24.0,
                height: 24.0,
                child: CircularProgressIndicator(
                  color: Colors.white,
                  strokeWidth: 2.0,
                ),
              )
            : const Text(
                'SAVE MEDICAL DOSSIER',
                style: TextStyle(
                  color: Colors.white,
                  fontSize: 16.0,
                  fontWeight: FontWeight.bold,
                ),
              ),
      ),
    );
  }
}