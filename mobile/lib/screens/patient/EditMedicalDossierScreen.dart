import 'package:flutter/material.dart';
import 'package:flutter_application_1/models/medical_dossier.dart';
import 'package:flutter_application_1/services/auth/api_service.dart';
import 'package:flutter_application_1/services/MedicalDossier-service.dart'; // Corrected import name if necessary, assuming it's MedicalDossierService.dart

class EditMedicalDossierScreen extends StatefulWidget {
  final MedicalDossier medicalDossier;

  const EditMedicalDossierScreen({super.key, required this.medicalDossier});

  @override
  State<EditMedicalDossierScreen> createState() => _EditMedicalDossierScreenState();
}

class _EditMedicalDossierScreenState extends State<EditMedicalDossierScreen> {
  // You already have ApiService, now add MedicalDossierService
  late final MedicalDossierService _medicalDossierService; // Declare it here

  final _formKey = GlobalKey<FormState>();
  late TextEditingController _heightController;
  late TextEditingController _weightController;
  late TextEditingController _bloodTypeController;
  late TextEditingController _allergiesController;
  late TextEditingController _medicalHistoryController;
  late TextEditingController _currentTreatmentsController;
  String? _errorMessage;
  bool _isSaving = false;

  @override
  void initState() {
    super.initState();
    // Initialize MedicalDossierService here, passing ApiService
    _medicalDossierService = MedicalDossierService(apiService: ApiService());
    _initializeControllers();
  }

  void _initializeControllers() {
    _heightController = TextEditingController(text: widget.medicalDossier.taille?.toString());
    _weightController = TextEditingController(text: widget.medicalDossier.poids?.toString());
    _bloodTypeController = TextEditingController(text: widget.medicalDossier.groupeSanguin);
    // Join the lists back into comma-separated strings for display
    _allergiesController = TextEditingController(text: widget.medicalDossier.allergies?.join(', '));
    _medicalHistoryController = TextEditingController(text: widget.medicalDossier.antecedents?.join(', '));
    _currentTreatmentsController = TextEditingController(text: widget.medicalDossier.traitements?.join(', '));
  }

  @override
  void dispose() {
    _disposeControllers();
    super.dispose();
  }

  void _disposeControllers() {
    _heightController.dispose();
    _weightController.dispose();
    _bloodTypeController.dispose();
    _allergiesController.dispose();
    _medicalHistoryController.dispose();
    _currentTreatmentsController.dispose();
  }

  Future<void> _saveMedicalDossier() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() {
      _isSaving = true;
      _errorMessage = null;
    });

    try {
      // Prepare the data to be sent to the API
      // Note: The API expects comma-separated strings for these fields,
      // not lists of strings.
      final String allergiesString = _allergiesController.text
          .split(',')
          .map((e) => e.trim())
          .where((e) => e.isNotEmpty)
          .join(',');

      final String antecedentsString = _medicalHistoryController.text
          .split(',')
          .map((e) => e.trim())
          .where((e) => e.isNotEmpty)
          .join(',');

      final String treatmentsString = _currentTreatmentsController.text
          .split(',')
          .map((e) => e.trim())
          .where((e) => e.isNotEmpty)
          .join(',');

      final updatedDossier = await _medicalDossierService.updateMedicalDossier(
        id: widget.medicalDossier.id!, // Pass the ID of the medical dossier
        taille: double.tryParse(_heightController.text),
        poids: double.tryParse(_weightController.text),
        groupeSanguin: _bloodTypeController.text,
        allergies: allergiesString, // Send as comma-separated string
        antecedents: antecedentsString, // Send as comma-separated string
        traitements: treatmentsString, // Send as comma-separated string
      );

      if (mounted) { // Check if the widget is still mounted before calling setState or Navigator.pop
        Navigator.pop(context, updatedDossier);
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _errorMessage = 'Failed to update medical dossier: ${e.toString()}';
          _isSaving = false;
        });
      }
    }
  }

  Widget _buildFormField({
    required TextEditingController controller,
    required String label,
    required IconData icon,
    TextInputType? keyboardType,
    int? maxLines = 1,
    String? Function(String?)? validator,
    bool required = false,
  }) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 16.0),
      child: TextFormField(
        controller: controller,
        keyboardType: keyboardType,
        maxLines: maxLines,
        decoration: InputDecoration(
          labelText: label,
          border: OutlineInputBorder(
            borderRadius: BorderRadius.circular(10),
            borderSide: BorderSide(color: Colors.grey.shade300),
          ),
          enabledBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(10),
            borderSide: BorderSide(color: Colors.grey.shade300),
          ),
          focusedBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(10),
            borderSide: const BorderSide(color: Color(0xFF87CEEB), width: 2),
          ),
          prefixIcon: Icon(icon, color: const Color(0xFF87CEEB)),
          suffixIcon: required ? null : const Icon(Icons.edit, size: 20),
        ),
        validator: validator ?? (required ? (value) {
          if (value == null || value.isEmpty) {
            return 'This field is required';
          }
          return null;
        } : null),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Medical Information', style: TextStyle(color: Colors.white)),
        backgroundColor: const Color(0xFF87CEEB),
        elevation: 0,
        centerTitle: true,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              if (_errorMessage != null)
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: Colors.red.shade50,
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(color: Colors.red.shade200),
                  ),
                  child: Text(
                    _errorMessage!,
                    style: TextStyle(color: Colors.red.shade700),
                    textAlign: TextAlign.center,
                  ),
                ),
              const SizedBox(height: 20),
              const Text(
                'Vital Information',
                style: TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                  color: Color(0xFF4682B4),
                ),
              ),
              const SizedBox(height: 16),
              Row(
                children: [
                  Expanded(
                    child: _buildFormField(
                      controller: _heightController,
                      label: 'Height (cm)',
                      icon: Icons.height,
                      keyboardType: TextInputType.number,
                      validator: (value) {
                        if (value == null || value.isEmpty) return 'Please enter height';
                        if (double.tryParse(value) == null) return 'Invalid number';
                        return null;
                      },
                      required: true,
                    ),
                  ),
                  const SizedBox(width: 16),
                  Expanded(
                    child: _buildFormField(
                      controller: _weightController,
                      label: 'Weight (kg)',
                      icon: Icons.monitor_weight,
                      keyboardType: TextInputType.number,
                      validator: (value) {
                        if (value == null || value.isEmpty) return 'Please enter weight';
                        if (double.tryParse(value) == null) return 'Invalid number';
                        return null;
                      },
                      required: true,
                    ),
                  ),
                ],
              ),
              _buildFormField(
                controller: _bloodTypeController,
                label: 'Blood Type',
                icon: Icons.bloodtype,
              ),
              const SizedBox(height: 24),
              const Text(
                'Health Details',
                style: TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                  color: Color(0xFF4682B4),
                ),
              ),
              const SizedBox(height: 16),
              _buildFormField(
                controller: _allergiesController,
                label: 'Allergies (comma separated)',
                icon: Icons.sick,
                maxLines: 2,
              ),
              _buildFormField(
                controller: _medicalHistoryController,
                label: 'Medical History (comma separated)',
                icon: Icons.history,
                maxLines: 3,
              ),
              _buildFormField(
                controller: _currentTreatmentsController,
                label: 'Current Treatments (comma separated)',
                icon: Icons.healing,
                maxLines: 3,
              ),
              const SizedBox(height: 32),
              SizedBox(
                width: double.infinity,
                height: 50,
                child: ElevatedButton(
                  onPressed: _isSaving ? null : _saveMedicalDossier,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF4682B4),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(10),
                    ),
                  ),
                  child: _isSaving
                      ? const SizedBox(
                          width: 24,
                          height: 24,
                          child: CircularProgressIndicator(
                            color: Colors.white,
                            strokeWidth: 3,
                          ),
                        )
                      : const Text(
                          'SAVE MEDICAL INFORMATION',
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: 16,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}