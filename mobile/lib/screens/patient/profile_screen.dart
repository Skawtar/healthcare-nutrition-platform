import 'dart:io';
import 'dart:typed_data';
import 'package:flutter/material.dart';
import 'package:flutter/foundation.dart' show kIsWeb;
import 'package:flutter_application_1/screens/patient/EditMedicalDossierScreen.dart';
import 'package:flutter_application_1/screens/patient/EditProfileScreen.dart';
import 'package:flutter_application_1/screens/patient/change_password_screen.dart';
import 'package:flutter_application_1/screens/patient/login_screen.dart';
import 'package:flutter_application_1/models/patient.dart';
import 'package:flutter_application_1/services/auth/api_service.dart';
import 'package:flutter_application_1/models/medical_dossier.dart';
import 'package:flutter_application_1/services/MedicalDossier-service.dart';
import 'package:image_picker/image_picker.dart';

class ProfileScreen extends StatefulWidget {
  const ProfileScreen({super.key});

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  final ApiService _apiService = ApiService();
  late final MedicalDossierService _medicalDossierService;
  final ImagePicker _picker = ImagePicker();

  Patient? _currentPatient;
  MedicalDossier? _currentMedicalDossier;
  String? _errorMessage;
  bool _isLoading = true;
  bool _isUploading = false;

  @override
  void initState() {
    super.initState();
    _medicalDossierService = MedicalDossierService(apiService: _apiService);
    _fetchPatientAndMedicalData();
  }

  Future<void> _fetchPatientAndMedicalData() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final patient = await _apiService.getPatientProfile();
      final medicalDossier = await _medicalDossierService.getMedicalDossier();

      setState(() {
        _currentPatient = patient;
        _currentMedicalDossier = medicalDossier;
      });
    } catch (e) {
      setState(() {
        _errorMessage = 'Failed to load profile data: ${e.toString()}';
      });
    } finally {
      setState(() => _isLoading = false);
    }
  }

  void _showSnackBar(String message, {bool isError = false}) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: isError ? Colors.red.shade700 : Colors.green.shade700,
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
        margin: const EdgeInsets.all(10),
      ),
    );
  }
Future<void> _uploadProfilePicture() async {
  try {
    // Pick image from gallery
    final XFile? pickedFile = await _picker.pickImage(
      source: ImageSource.gallery,
      imageQuality: 80,
      maxWidth: 800,
      maxHeight: 800,
    );

    if (pickedFile == null) return;

    setState(() => _isUploading = true);

    // Read the file as bytes
    final Uint8List imageBytes = await pickedFile.readAsBytes();

    // Upload to server
    final String imageUrl = await _apiService.uploadProfilePicture(
      imageBytes,
      fileName: 'profile_${_currentPatient?.id}_${DateTime.now().millisecondsSinceEpoch}.jpg',
    );

    // Update state
    if (!mounted) return;
    setState(() {
      _currentPatient = _currentPatient?.copyWith(
        profileImageUrl: imageUrl,
        imageProfil: imageUrl.split('/').last,
      );
    });

    _showSnackBar('Profile picture updated successfully!');
  } on SocketException {
    if (mounted) {
      _showSnackBar('No internet connection', isError: true);
    }
  } catch (e) {
    if (mounted) {
      _showSnackBar('Failed to upload profile picture: ${e.toString()}', isError: true);
    }
    debugPrint('Upload error: $e');
  } finally {
    if (mounted) {
      setState(() => _isUploading = false);
    }
  }
}
  Future<void> _navigateToEditMedicalDossier() async {
    if (_currentMedicalDossier == null) {
      _showSnackBar('No medical dossier to edit. Please create one first.', isError: true);
      return;
    }

    final updatedDossier = await Navigator.push<MedicalDossier>(
      context,
      MaterialPageRoute(
        builder: (context) => EditMedicalDossierScreen(
          medicalDossier: _currentMedicalDossier!,
        ),
      ),
    );

    if (updatedDossier != null) {
      await _fetchPatientAndMedicalData();
      _showSnackBar('Medical dossier updated successfully!');
    }
  }

  Future<void> _navigateToEditProfile() async {
    if (_currentPatient == null) return;

    final updatedPatient = await Navigator.push<Patient>(
      context,
      MaterialPageRoute(
        builder: (context) => EditProfileScreen(
          patient: _currentPatient!,
        ),
      ),
    );

    if (updatedPatient != null) {
      await _fetchPatientAndMedicalData();
      _showSnackBar('Profile updated successfully!');
    }
  }

  Future<void> _logout() async {
    try {
      await _apiService.logout();
      if (mounted) {
        Navigator.pushAndRemoveUntil(
          context,
          MaterialPageRoute(builder: (context) => LoginScreen()),
          (Route<dynamic> route) => false,
        );
      }
    } catch (e) {
      if (mounted) {
        _showSnackBar('Logout failed: ${e.toString()}', isError: true);
      }
    }
  }

  void _navigateToChangePassword() {
    Navigator.push(
      context,
      MaterialPageRoute(builder: (context) => const ChangePasswordScreen()),
    );
  }

  double? _calculateBMI(double? heightCm, double? weightKg) {
    if (heightCm == null || weightKg == null || heightCm <= 0) return null;
    final heightMeters = heightCm / 100;
    return weightKg / (heightMeters * heightMeters);
  }

  Widget _buildProfileImageSection() {
    return Center(
      child: Stack(
        alignment: Alignment.center,
        children: [
          CircleAvatar(
            radius: 60,
            backgroundColor: Colors.grey[200],
            backgroundImage: _currentPatient!.profileImageUrl.isNotEmpty
                ? NetworkImage(_currentPatient!.profileImageUrl)
                : null,
            child: _isUploading
                ? const CircularProgressIndicator(color: Colors.white)
                : _currentPatient!.profileImageUrl.isEmpty
                    ? Icon(Icons.person, size: 60, color: Colors.grey[600])
                    : null,
          ),
          if (!_isUploading)
            Positioned(
              bottom: 0,
              right: 0,
              child: Container(
                decoration: BoxDecoration(
                  color: const Color(0xFF4682B4),
                  shape: BoxShape.circle,
                  border: Border.all(color: Colors.white, width: 2),
                ),
                child: IconButton(
                  icon: const Icon(Icons.camera_alt, size: 20, color: Colors.white),
                  onPressed: _uploadProfilePicture,
                ),
              ),
            ),
        ],
      ),
    );
  }

  List<Widget> _buildPersonalInfoRows() {
    return [
      _buildInfoRow(Icons.badge, 'CIN', _currentPatient!.cin),
      _buildInfoRow(Icons.phone, 'Phone', _currentPatient!.telephone ?? 'N/A'),
      _buildInfoRow(Icons.location_on, 'Address', _currentPatient!.adresse ?? 'N/A'),
      _buildInfoRow(Icons.cake, 'Date of Birth', _currentPatient!.formattedDateOfBirth),
      _buildInfoRow(Icons.wc, 'Gender', _currentPatient!.genre),
    ];
  }

  List<Widget> _buildMedicalInfoRows(double? bmi) {
    return _currentMedicalDossier != null
        ? [
            _buildInfoRow(Icons.height, 'Height', '${_currentMedicalDossier!.taille ?? 'N/A'} cm'),
            _buildInfoRow(Icons.monitor_weight, 'Weight', '${_currentMedicalDossier!.poids ?? 'N/A'} kg'),
            _buildInfoRow(Icons.fitness_center, 'BMI', bmi?.toStringAsFixed(1) ?? 'N/A'),
            _buildInfoRow(Icons.bloodtype, 'Blood Type', _currentMedicalDossier!.groupeSanguin ?? 'N/A'),
            _buildInfoRow(Icons.sick, 'Allergies', _currentMedicalDossier!.allergies?.join(', ') ?? 'None'),
            _buildInfoRow(Icons.history, 'Medical History', _currentMedicalDossier!.antecedents?.join(', ') ?? 'None'),
            _buildInfoRow(Icons.healing, 'Current Treatments', _currentMedicalDossier!.traitements?.join(', ') ?? 'None'),
          ]
        : [
            const Padding(
              padding: EdgeInsets.symmetric(vertical: 8.0),
              child: Text(
                'No medical dossier available. Please update your profile.',
                style: TextStyle(fontStyle: FontStyle.italic, color: Colors.grey),
              ),
            ),
          ];
  }

  List<Widget> _buildSettingsOptions() {
    return [
      _buildSettingsOption(
        context,
        icon: Icons.settings,
        title: 'App Settings',
        onTap: () => _showSnackBar('Settings Screen coming soon'),
      ),
      _buildSettingsOption(
        context,
        icon: Icons.lock,
        title: 'Change Password',
        onTap: _navigateToChangePassword,
      ),
      _buildSettingsOption(
        context,
        icon: Icons.logout,
        title: 'Logout',
        onTap: _logout,
        isDestructive: true,
      ),
    ];
  }

  Widget _buildInfoCard(BuildContext context, String title, List<Widget> children, {Widget? actionButton}) {
    return Card(
      elevation: 4,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(15)),
      color: Colors.white,
      child: Padding(
        padding: const EdgeInsets.all(20.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              title,
              style: Theme.of(context).textTheme.titleLarge?.copyWith(
                    fontWeight: FontWeight.bold,
                    color: const Color(0xFF4682B4),
                  ),
            ),
            const Divider(height: 25, thickness: 0.5),
            ...children,
            if (actionButton != null) ...[
              const SizedBox(height: 15),
              Center(child: actionButton),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildInfoRow(IconData icon, String label, String value, {Color? valueColor}) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8.0),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, color: const Color(0xFF87CEEB), size: 24),
          const SizedBox(width: 15),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  label,
                  style: Theme.of(context).textTheme.bodySmall?.copyWith(
                        color: Colors.grey[600],
                        fontWeight: FontWeight.w500,
                      ),
                ),
                const SizedBox(height: 2),
                Text(
                  value,
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                        fontWeight: FontWeight.bold,
                        color: valueColor ?? Colors.black87,
                      ),
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSettingsOption(BuildContext context, {
    required IconData icon,
    required String title,
    required VoidCallback onTap,
    bool isDestructive = false,
  }) {
    return Card(
      elevation: 2,
      margin: const EdgeInsets.only(bottom: 10),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
      color: Colors.white,
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(10),
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 15.0, vertical: 12.0),
          child: Row(
            children: [
              Icon(icon, color: isDestructive ? Colors.redAccent : const Color(0xFF4682B4), size: 28),
              const SizedBox(width: 15),
              Expanded(
                child: Text(
                  title,
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.w500,
                    color: isDestructive ? Colors.redAccent : Colors.black87,
                  ),
                ),
              ),
              Icon(Icons.arrow_forward_ios, size: 18, color: Colors.grey[400]),
            ],
          ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return Scaffold(
        appBar: AppBar(title: const Text('My Profile')),
        body: const Center(child: CircularProgressIndicator()),
      );
    }

    if (_errorMessage != null) {
      return Scaffold(
        appBar: AppBar(title: const Text('My Profile')),
        body: Center(
          child: Text(
            _errorMessage!,
            style: const TextStyle(color: Colors.red, fontSize: 16),
            textAlign: TextAlign.center,
          ),
        ),
      );
    }

    if (_currentPatient == null) {
      return Scaffold(
        appBar: AppBar(title: const Text('My Profile')),
        body: const Center(child: Text('No patient data available')),
      );
    }

    final bmi = _calculateBMI(_currentMedicalDossier?.taille, _currentMedicalDossier?.poids);

    return Scaffold(
      backgroundColor: Colors.white,
      appBar: AppBar(
        title: const Text('My Profile', style: TextStyle(color: Colors.white)),
        backgroundColor: const Color(0xFF87CEEB),
        actions: [
          PopupMenuButton<String>(
            icon: const Icon(Icons.more_vert, color: Colors.white),
            onSelected: (value) {
              if (value == 'edit_profile') {
                _navigateToEditProfile();
              } else if (value == 'edit_medical') {
                _navigateToEditMedicalDossier();
              }
            },
            itemBuilder: (BuildContext context) {
              return [
                const PopupMenuItem<String>(
                  value: 'edit_profile',
                  child: Text('Edit Profile'),
                ),
                const PopupMenuItem<String>(
                  value: 'edit_medical',
                  child: Text('Edit Medical Dossier'),
                ),
              ];
            },
          ),
        ],
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.center,
          children: [
            _buildProfileImageSection(),
            const SizedBox(height: 20),
            Text(
              _currentPatient!.fullName,
              style: Theme.of(context).textTheme.headlineMedium?.copyWith(
                    fontWeight: FontWeight.bold,
                    color: const Color(0xFF4682B4),
                  ),
            ),
            const SizedBox(height: 5),
            Text(
              _currentPatient!.email,
              style: Theme.of(context).textTheme.titleMedium?.copyWith(
                    color: Colors.grey[700],
                  ),
            ),
            const SizedBox(height: 30),

            _buildInfoCard(
              context,
              'Personal Information',
              _buildPersonalInfoRows(),
            ),
            const SizedBox(height: 20),

            _buildInfoCard(
              context,
              'Medical Information',
              _buildMedicalInfoRows(bmi),
            ),
            const SizedBox(height: 20),

            ..._buildSettingsOptions(),
          ],
        ),
      ),
    );
  }
}