import 'package:flutter/material.dart';
import 'package:flutter_application_1/models/Appointment.dart';
import 'package:flutter_application_1/models/doctor.dart';
import 'package:flutter_application_1/screens/doctor/doctors_screen.dart';
import 'package:flutter_application_1/services/appointment_service.dart';
import 'package:flutter_application_1/services/auth/api_service.dart';
import 'package:flutter_application_1/services/auth/notifications_api.dart';
import 'package:intl/intl.dart';

class AppointmentsScreen extends StatefulWidget {
  const AppointmentsScreen({Key? key}) : super(key: key);

  @override
  _AppointmentsScreenState createState() => _AppointmentsScreenState();
}

class _AppointmentsScreenState extends State<AppointmentsScreen> {
  late final AppointmentService _appointmentService;
  late Future<List<Appointment>> _appointmentsFuture;
  int _selectedTab = 0; // 0 for upcoming, 1 for past

  @override
  void initState() {
    super.initState();
    // Initialize services
    final apiService = ApiService();
    final notificationService = NotificationsApi( apiService);
    _appointmentService = AppointmentService(
      apiService: apiService,
       );
    _loadAppointments();
  }

  void _loadAppointments() {
    setState(() {
      _appointmentsFuture = _selectedTab == 0
          ? _appointmentService.getUpcomingAppointments()
          : _appointmentService.getPastAppointments();
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      appBar: AppBar(
        backgroundColor: const Color(0xFF87CEEB),
        elevation: 2,
        title: const Text(
          'My Appointments',
          style: TextStyle(color: Colors.white),
        ),
        iconTheme: const IconThemeData(color: Colors.white),
      ),
      body: Column(
        children: [
          // Tab selector
          Padding(
            padding: const EdgeInsets.all(16.0),
            child: Container(
              decoration: BoxDecoration(
                color: Colors.grey[200],
                borderRadius: BorderRadius.circular(10),
              ),
              child: Row(
                children: [
                  _buildTabButton(0, 'Upcoming'),
                  _buildTabButton(1, 'Past'),
                ],
              ),
            ),
          ),
          // Appointment list
          Expanded(
            child: FutureBuilder<List<Appointment>>(
              future: _appointmentsFuture,
              builder: (context, snapshot) {
                if (snapshot.connectionState == ConnectionState.waiting) {
                  return const Center(child: CircularProgressIndicator());
                } else if (snapshot.hasError) {
                  return _buildErrorWidget(snapshot.error.toString());
                } else if (!snapshot.hasData || snapshot.data!.isEmpty) {
                  return _buildEmptyStateWidget();
                } else {
                  return _buildAppointmentList(snapshot.data!);
                }
              },
            ),
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () {
          Navigator.push(
            context,
            MaterialPageRoute(builder: (context) => const DoctorsScreen()),
          );
        },
        backgroundColor: const Color(0xFF87CEEB),
        child: const Icon(Icons.add, color: Colors.white),
      ),
    );
  }

  Widget _buildTabButton(int tabIndex, String label) {
    return Expanded(
      child: InkWell(
        onTap: () {
          if (_selectedTab != tabIndex) {
            setState(() => _selectedTab = tabIndex);
            _loadAppointments();
          }
        },
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 12),
          decoration: BoxDecoration(
            color: _selectedTab == tabIndex
                ? const Color.fromARGB(255, 120, 209, 244)
                : Colors.transparent,
            borderRadius: BorderRadius.circular(8),
          ),
          child: Center(
            child: Text(
              label,
              style: TextStyle(
                color: _selectedTab == tabIndex ? Colors.white : Colors.black87,
                fontWeight: FontWeight.w600,
              ),
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildErrorWidget(String error) {
    return Column(
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        const Icon(
          Icons.error_outline,
          color: Colors.red,
          size: 60,
        ),
        const SizedBox(height: 16),
        Text(
          'Error loading appointments: $error',
          style: TextStyle(
            color: Colors.red.shade700,
            fontSize: 16,
          ),
          textAlign: TextAlign.center,
        ),
        const SizedBox(height: 16),
        ElevatedButton.icon(
          onPressed: _loadAppointments,
          icon: const Icon(Icons.refresh),
          label: const Text('Retry'),
          style: ElevatedButton.styleFrom(
            backgroundColor: const Color.fromARGB(255, 141, 196, 242),
            foregroundColor: Colors.white,
          ),
        ),
      ],
    );
  }

  Widget _buildEmptyStateWidget() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(
            Icons.calendar_month_outlined,
            size: 48,
            color: const Color.fromARGB(255, 162, 190, 203),
          ),
          const SizedBox(height: 16),
          Text(
            _selectedTab == 0 ? 'No upcoming appointments' : 'No past appointments',
            style: TextStyle(
              color: const Color.fromARGB(255, 162, 190, 203),
              fontSize: 16,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildAppointmentList(List<Appointment> appointments) {
    return ListView.builder(
      itemCount: appointments.length,
      itemBuilder: (context, index) {
        return _buildAppointmentCard(appointments[index]);
      },
    );
  }

  Widget _buildAppointmentCard(Appointment appointment) {
    return Card(
      margin: const EdgeInsets.symmetric(horizontal: 16.0, vertical: 8.0),
      elevation: 4,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(12),
      ),
      color: Colors.white,
      child: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            if (appointment.doctor != null) ...[
              Text(
                'Dr. ${appointment.doctor!.name}',
                style: TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                  color: Colors.blue.shade800,
                ),
              ),
              const SizedBox(height: 4),
              Text(
                appointment.doctor?.specialty?.isNotEmpty == true 
                    ? appointment.doctor!.specialty!.toUpperCase()
                    : 'GENERALISTE',
                style: TextStyle(
                  fontSize: 14, 
                  color: Colors.grey[600],
                  fontWeight: FontWeight.w500,
                ),
              ),
              const SizedBox(height: 12),
            ],
            Row(
              children: [
                const Icon(Icons.calendar_today, size: 18, color: Colors.blue),
                const SizedBox(width: 10),
                Text(
                  appointment.dateHeure != null 
                    ? '${DateFormat('dd/MM/yyyy').format(appointment.dateHeure)} at ${DateFormat('HH:mm').format(appointment.dateHeure)}'
                    : 'Date not set',
                  style: const TextStyle(fontSize: 15, color: Colors.black87),
                ),
              ],
            ),
            const SizedBox(height: 12),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  appointment.notes ?? 'No notes available',
                  style: TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                    color: Theme.of(context).primaryColor,
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                  decoration: BoxDecoration(
                    color: _getStatusColor(appointment.status).withOpacity(0.1),
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(
                      color: _getStatusColor(appointment.status),
                      width: 1,
                    ),
                  ),
                  child: Text(
                    appointment.status.toUpperCase(),
                    style: TextStyle(
                      color: _getStatusColor(appointment.status),
                      fontWeight: FontWeight.bold,
                      fontSize: 12,
                      letterSpacing: 0.5,
                    ),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Color _getStatusColor(String status) {
    switch (status.toLowerCase()) {
      case 'confirmed':
        return Colors.green;
      case 'pending':
        return Colors.orange.shade700;
      case 'cancelled':
        return Colors.red.shade700;
      case 'completed':
        return Colors.blue.shade700;
      default:
        return Colors.blueGrey;
    }
  }
}