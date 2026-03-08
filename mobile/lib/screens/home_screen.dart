// lib/screens/home_screen.dart
import 'package:flutter/material.dart';
import 'package:flutter_application_1/screens/NotificationsScreen.dart';
import 'package:flutter_application_1/screens/ai_chat_screen.dart';
import 'package:flutter_application_1/screens/appointments_screen.dart';
import 'package:flutter_application_1/screens/diet_plan_screen.dart';
import 'package:flutter_application_1/screens/doctor/doctors_screen.dart';
import 'package:flutter_application_1/services/auth/notifications_api.dart';
import 'package:flutter_application_1/services/bmi_calculator.dart';
import 'package:flutter_application_1/screens/health_metrics_screen.dart';
import 'package:flutter_application_1/screens/medicine_reminders_screen.dart';
import 'package:flutter_application_1/screens/patient/profile_screen.dart';
import 'package:flutter_application_1/screens/patient/ServicesScreen.dart';
import 'package:flutter_application_1/models/patient.dart';
import 'package:flutter_application_1/models/medical_dossier.dart';
import 'package:flutter_application_1/services/auth/api_service.dart';
import 'package:flutter_application_1/services/MedicalDossier-service.dart';
import 'package:flutter_application_1/widgets/bmi_gauge.dart';
import 'package:flutter_application_1/models/appointment.dart';

class HomeScreen extends StatefulWidget {
  @override
  _HomeScreenState createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  int _selectedIndex = 0;
  int _notificationCount = 0; // Initialize to 0, will be fetched dynamically
  Patient? _currentPatient;
  MedicalDossier? _medicalDossier;
  bool _isLoading = true;
  String? _errorMessage;
  double? _calculatedBMI;

  final ApiService _apiService = ApiService();
  late final MedicalDossierService _medicalDossierService;
  // NEW: Initialize NotificationsApi
  late final NotificationsApi _notificationsApi;

  List<Appointment> _upcomingAppointments = [];

  @override
  void initState() {
    super.initState();
    _medicalDossierService = MedicalDossierService(apiService: _apiService);
    _notificationsApi = NotificationsApi( _apiService); // Initialize NotificationsApi
    _loadInitialData();
  }

  Future<void> _loadInitialData() async {
    if (!mounted) return;
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      await Future.wait([
        _loadPatientInfo(),
        _loadMedicalDossier(),
        _loadUpcomingAppointments(),
        _loadNotificationCount(), // NEW: Load notification count
      ]);
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _errorMessage = 'Error loading initial data: ${e.toString()}';
      });
    } finally {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  // NEW: Method to load notification count
  Future<void> _loadNotificationCount() async {
    try {
      final count = await _notificationsApi.getUnreadNotificationCount();
      if (!mounted) return;
      setState(() {
        _notificationCount = count;
      });
    } catch (e) {
      if (!mounted) return;
      print('Failed to load notification count: ${e.toString()}');
      setState(() {
        _notificationCount = 0; // Reset on error
      });
    }
  }

  double? _calculateBMI(double? heightCm, double? weightKg) {
    if (heightCm != null && weightKg != null && heightCm > 0) {
      final double heightMeters = heightCm / 100;
      return weightKg / (heightMeters * heightMeters);
    }
    return null;
  }

  Future<void> _loadPatientInfo() async {
    try {
      final Patient? patient = await _apiService.getPatientProfile();
      if (!mounted) return;
      setState(() {
        _currentPatient = patient;
      });
    } catch (e) {
      if (!mounted) return;
      print('Failed to load patient profile: ${e.toString()}');
    }
  }

  Future<void> _loadMedicalDossier() async {
    try {
      final MedicalDossier? dossier =
          await _medicalDossierService.getMedicalDossier();

      if (!mounted) return;
      setState(() {
        _medicalDossier = dossier;
        _calculatedBMI = _calculateBMI(
          _medicalDossier?.taille,
          _medicalDossier?.poids,
        );
      });
    } catch (e) {
      if (!mounted) return;
      print('Failed to load medical dossier: ${e.toString()}');
      setState(() {
        _medicalDossier = null;
        _calculatedBMI = null;
      });
    }
  }

Future<void> _loadUpcomingAppointments() async {
    try {
      final List<Appointment> appointments =
          await _apiService.fetchAppointments(); // Assuming this calls getUpcomingAppointments

      if (!mounted) return;
      setState(() {
        // If backend already filters for strictly future and confirmed,
        // this additional filter is redundant but safe.
        // If backend changes to "strictly future confirmed appointments",
        // then apt.status == 'confirmed' && apt.dateHeure.isAfter(DateTime.now())
        // can be simplified or removed.
        _upcomingAppointments = appointments
            .where((apt) => apt.status == 'confirmed' && apt.dateHeure.isAfter(DateTime.now().subtract(Duration(minutes: 5)))) // Small buffer for client-server time sync
            .toList();
        // Sort by date to show the soonest first
        _upcomingAppointments.sort(
          (a, b) => a.dateHeure.compareTo(b.dateHeure),
        );
      });
    } catch (e) {
      if (!mounted) return;
      print('Failed to load appointments: ${e.toString()}');
      setState(() {
        _upcomingAppointments = []; // Clear appointments on error
      });
    }
  }
  void _openAIChat() {
    if (_currentPatient != null) {
      Navigator.push(
        context,
        MaterialPageRoute(builder: (context) => AIChatScreen(patient: _currentPatient!, medicalDossier: _medicalDossier,)),
      );
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Patient information not available.'),
        ),
      );
    }
  }

  Widget _buildActionButton(IconData icon, String label, VoidCallback onTap) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(12),
      hoverColor: Colors.blue.withOpacity(0.1),
      highlightColor: Colors.blue.withOpacity(0.2),
      splashColor: Colors.blue.withOpacity(0.2),
      child: Padding(
        padding: const EdgeInsets.all(4.0),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: const Color(0xFFE6F7FF),
                borderRadius: BorderRadius.circular(12),
                boxShadow: [
                  BoxShadow(
                    color: Colors.grey.withOpacity(0.2),
                    spreadRadius: 1,
                    blurRadius: 3,
                    offset: const Offset(0, 0.5),
                  ),
                ],
              ),
              child: Icon(icon, size: 28, color: const Color(0xFF4682B4)),
            ),
            const SizedBox(height: 8),
            Text(
              label,
              textAlign: TextAlign.center,
              style: const TextStyle(
                fontSize: 12,
                color: Color(0xFF4682B4),
                fontWeight: FontWeight.w500,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildAppointmentCard(Appointment appointment) {
    // Determine color based on status or type, or use a default
    Color cardColor;
    switch (appointment.status) {
      case 'confirmed':
        cardColor = Colors.green;
        break;
      case 'pending':
        cardColor = Colors.orange;
        break;
      case 'cancelled':
        cardColor = Colors.red;
        break;
      default:
        cardColor = Colors.blue; // Default color
    }

    // Format date and time using dateHeure
    final String formattedDate =
        '${appointment.dateHeure.day}/${appointment.dateHeure.month}/${appointment.dateHeure.year}';
    final String formattedTime =
        '${appointment.dateHeure.hour.toString().padLeft(2, '0')}:${appointment.dateHeure.minute.toString().padLeft(2, '0')}';

    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      elevation: 3,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(15)),
      child: Padding(
        padding: const EdgeInsets.symmetric(vertical: 8.0, horizontal: 16.0),
        child: Row(
          children: [
            Container(
              width: 8,
              height: 40,
              decoration: BoxDecoration(
                color: cardColor,
                borderRadius: BorderRadius.circular(4),
              ),
            ),
            const SizedBox(width: 16),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    appointment.motif ??
                        'Appointment', // Use motif as the title
                    style: const TextStyle(
                      fontWeight: FontWeight.bold,
                      fontSize: 16,
                      color: Color(0xFF4682B4),
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    '$formattedDate, $formattedTime', // Display formatted date and time
                    style: TextStyle(fontSize: 14, color: Colors.grey[600]),
                  ),
                  if (appointment.doctor != null)
                    Text(
                      'Dr. ${appointment.doctor?.name}',
                      style: TextStyle(fontSize: 12, color: Colors.grey[500]),
                    ),
                ],
              ),
            ),
            IconButton(
              icon: const Icon(
                Icons.arrow_forward_ios,
                color: Color(0xFF87CEEB),
                size: 20,
              ),
              onPressed: () {
                Navigator.push(
                  context,
                  MaterialPageRoute(
                    builder: (context) => const AppointmentsScreen(),
                  ),
                ).then((_) => _loadUpcomingAppointments());
              },
            ),
          ],
        ),
      ),
    );
  }

  void _onItemTapped(int index) {
    setState(() {
      _selectedIndex = index;
    });
    if (index == 0) {
      _loadInitialData(); // Home - already on home screen, refresh data
    } else if (index == 1) {
      Navigator.push(
        context,
        MaterialPageRoute(builder: (context) => const AppointmentsScreen()),
      ).then((_) => _loadUpcomingAppointments()); // Refresh on return
    } else if (index == 2) {
      Navigator.push(
        context,
        MaterialPageRoute(builder: (context) => const ServicesScreen()),
      );
    } else if (index == 3) {
      Navigator.push(
        context,
        MaterialPageRoute(
          builder: (context) => const ProfileScreen(),
        ),
      ).then(
        (_) => _loadInitialData(),
      ); // Refresh patient data and notification count on return
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return const Scaffold(
        body: Center(
          child: CircularProgressIndicator(color: Color(0xFF87CEEB)),
        ),
      );
    }

    if (_errorMessage != null) {
      return Scaffold(
        appBar: AppBar(
          title: const Text(
            'Health Companion',
            style: TextStyle(color: Colors.white),
          ),
          backgroundColor: const Color(0xFF87CEEB),
          iconTheme: const IconThemeData(color: Colors.white),
        ),
        body: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(Icons.error_outline, color: Colors.red, size: 48),
              const SizedBox(height: 16),
              Text(
                'Oops! $_errorMessage',
                textAlign: TextAlign.center,
                style: const TextStyle(fontSize: 16, color: Colors.red),
              ),
              const SizedBox(height: 24),
              ElevatedButton.icon(
                onPressed: _loadInitialData,
                icon: const Icon(Icons.refresh, color: Colors.white),
                label: const Text(
                  'Retry Loading Data',
                  style: TextStyle(color: Colors.white),
                ),
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF4682B4),
                  padding: const EdgeInsets.symmetric(
                    horizontal: 20,
                    vertical: 12,
                  ),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(10),
                  ),
                ),
              ),
            ],
          ),
        ),
      );
    }

    return Scaffold(
      appBar: AppBar(
        title: const Text(
          'Health Companion',
          style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold),
        ),
        backgroundColor: const Color(0xFF87CEEB),
        elevation: 0,
        actions: [
          IconButton(
            icon: Stack(
              children: [
                const Icon(Icons.notifications, color: Colors.white),
                if (_notificationCount > 0) // Only show badge if count > 0
                  Positioned(
                    right: 0,
                    child: CircleAvatar(
                      radius: 8,
                      backgroundColor: Colors.red,
                      child: Text(
                        _notificationCount.toString(),
                        style: const TextStyle(
                          fontSize: 10,
                          color: Colors.white,
                        ),
                      ),
                    ),
                  ),
              ],
            ),
            onPressed: () {
              // NEW: Navigate to NotificationsScreen
              Navigator.push(
                context,
                MaterialPageRoute(
                  builder: (context) => NotificationsScreen(), // Navigate to the new screen
                ),
              ).then((_) => _loadNotificationCount()); // Refresh count on return
            },
          ),
          IconButton(
            icon: CircleAvatar(
              backgroundColor: Colors.white.withOpacity(0.8),
              child: (_currentPatient != null &&
                      _currentPatient!.fullName.isNotEmpty)
                  ? Text(
                      _currentPatient!.fullName[0].toUpperCase(),
                      style: const TextStyle(
                        color: Color(0xFF4682B4),
                        fontWeight: FontWeight.bold,
                      ),
                    )
                  : const Icon(Icons.person, color: Color(0xFF4682B4)),
            ),
            onPressed: () {
              Navigator.push(
                context,
                MaterialPageRoute(
                  builder: (context) => const ProfileScreen(),
                ),
              );
            },
          ),
          const SizedBox(width: 8),
        ],
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            if (_currentPatient != null)
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Hello, ${_currentPatient!.fullName.split(' ')[0]}!',
                    style: Theme.of(context).textTheme.headlineMedium?.copyWith(
                          fontWeight: FontWeight.bold,
                          color: const Color(0xFF4682B4),
                        ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    'How are you feeling today?',
                    style: Theme.of(
                      context,
                    ).textTheme.titleMedium?.copyWith(color: Colors.grey[600]),
                  ),
                  const SizedBox(height: 24),
                ],
              ),
            const Text(
              'Your Body Mass Index',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.bold,
                color: Color(0xFF4682B4),
              ),
            ),
            const SizedBox(height: 12),
            Center(
              child: Column(
                children: [
                  BMIGauge(bmi: _calculatedBMI),
                  if (_calculatedBMI == null)
                    Padding(
                      padding: const EdgeInsets.only(top: 16.0),
                      child: Column(
                        children: [
                          const Text(
                            'BMI data not available. Please update your profile.',
                            textAlign: TextAlign.center,
                            style: TextStyle(fontSize: 16, color: Colors.grey),
                          ),
                          const SizedBox(height: 10),
                          ElevatedButton(
                            onPressed: () {
                              Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (context) => const ProfileScreen(),
                                ),
                              ).then((_) => _loadInitialData());
                            },
                            style: ElevatedButton.styleFrom(
                              backgroundColor: const Color(0xFF4682B4),
                              foregroundColor: Colors.white,
                              padding: const EdgeInsets.symmetric(
                                horizontal: 20,
                                vertical: 10,
                              ),
                              shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(8),
                              ),
                            ),
                            child: const Text('Update Profile'),
                          ),
                        ],
                      ),
                    ),
                ],
              ),
            ),
            const SizedBox(height: 30),
            const Text(
              'Quick Actions',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.bold,
                color: Color(0xFF4682B4),
              ),
            ),
            const SizedBox(height: 12),
            GridView.count(
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              crossAxisCount: 4,
              childAspectRatio: 0.8,
              mainAxisSpacing: 10,
              crossAxisSpacing: 10,
              children: [
                _buildActionButton(Icons.calendar_today, 'Appointments', () {
                  Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (context) => const AppointmentsScreen(),
                    ),
                  ).then(
                    (_) => _loadUpcomingAppointments(),
                  );
                }),
                _buildActionButton(Icons.calculate, 'BMI', () {
                  showModalBottomSheet(
                    context: context,
                    builder: (context) => const BMICalculator(),
                    isScrollControlled: true,
                    backgroundColor: Colors.transparent,
                  );
                }),
                _buildActionButton(Icons.medication, 'Medicines', () {
                  Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (context) => const MedicineRemindersScreen(),
                    ),
                  );
                }),
                _buildActionButton(Icons.favorite, 'Health Metrics', () {
                  Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (context) => const HealthMetricsScreen(),
                    ),
                  );
                }),
                _buildActionButton(Icons.restaurant_menu, 'Diet Plan', () {
                  if (_currentPatient != null && _currentPatient!.id != null) {
                    Navigator.push(
                      context,
                      MaterialPageRoute(
                        builder: (context) =>
                            PatientDietScreen(patientId: _currentPatient!.id!),
                      ),
                    );
                  } else {
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(
                        content: Text(
                          'Patient ID not available. Cannot view diet plan.',
                        ),
                      ),
                    );
                  }
                }),
                _buildActionButton(Icons.person_search, 'Find Doctors', () {
                  Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (context) => const DoctorsScreen(),
                    ),
                  );
                }),
                _buildActionButton(
                  Icons.chat_bubble_outline,
                  'AI Chat',
                  _openAIChat,
                ),
                _buildActionButton(Icons.file_copy, 'Documents', () {
                  // Navigate to MedicalDocumentsScreen
               
                }),
              ],
            ),
            const SizedBox(height: 30),
            // Upcoming Appointments Section
            const Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  'Upcoming Appointments',
                  style: TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                    color: Color(0xFF4682B4),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),
            if (_upcomingAppointments.isNotEmpty)
              ..._upcomingAppointments.map(_buildAppointmentCard)
            else
              Padding(
                padding: const EdgeInsets.all(16.0),
                child: Center(
                  child: Text(
                    'No upcoming appointments yet.',
                    style: TextStyle(fontSize: 16, color: Colors.grey[600]),
                    textAlign: TextAlign.center,
                  ),
                ),
              ),
            const SizedBox(height: 24),
          ],
        ),
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: _openAIChat,
        backgroundColor: const Color(0xFF87CEEB),
        foregroundColor: Colors.white,
        child: const Icon(Icons.chat_bubble_outline),
        tooltip: 'AI Health Assistant',
      ),
      bottomNavigationBar: BottomNavigationBar(
        currentIndex: _selectedIndex,
        onTap: _onItemTapped,
        type: BottomNavigationBarType.fixed,
        backgroundColor: const Color(0xFF87CEEB),
        selectedItemColor: const Color(0xFF4682B4),
        unselectedItemColor: const Color.fromARGB(255, 255, 255, 255),
        selectedLabelStyle: const TextStyle(fontWeight: FontWeight.bold),
        unselectedLabelStyle: const TextStyle(fontWeight: FontWeight.normal),
        items: const [
          BottomNavigationBarItem(icon: Icon(Icons.home), label: 'Home'),
          BottomNavigationBarItem(
            icon: Icon(Icons.calendar_month),
            label: 'Appointments',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.medical_services),
            label: 'Services',
          ),
          BottomNavigationBarItem(icon: Icon(Icons.person), label: 'Profile'),
        ],
      ),
    );
  }
}