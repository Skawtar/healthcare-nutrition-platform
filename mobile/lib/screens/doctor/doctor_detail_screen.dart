import 'package:flutter/material.dart';
import 'package:flutter_application_1/models/doctor.dart';
import 'package:flutter_application_1/services/doctor_service.dart';
import 'package:flutter_application_1/services/auth/api_service.dart';
import 'package:flutter_application_1/services/appointment_service.dart';
import 'package:flutter_application_1/services/auth/notifications_api.dart';
import 'package:intl/intl.dart';

class DoctorDetailScreen extends StatefulWidget {
  final Doctor doctor;
  final VoidCallback? onAppointmentBooked;

  const DoctorDetailScreen({
    super.key,
    required this.doctor,
    this.onAppointmentBooked,
  });

  @override
  State<DoctorDetailScreen> createState() => _DoctorDetailScreenState();
}

class _DoctorDetailScreenState extends State<DoctorDetailScreen> {
  late Future<Doctor> _doctorDetailsFuture;
  final ApiService _apiService = ApiService();
  late final DoctorService _doctorService;
  late final AppointmentService _appointmentService;
  late final NotificationsApi _notificationService;

  bool _isLoading = false;
  final TextEditingController _motifController = TextEditingController();
  final TextEditingController _notesController = TextEditingController();
  final _formKey = GlobalKey<FormState>();

  static const Color _primaryColor = Color(0xFFADD8E6);
  static const Color _darkerPrimaryColor = Color(0xFF7CB9E8);

  @override
  void initState() {
    super.initState();
    _notificationService = NotificationsApi( _apiService);
    _doctorService = DoctorService(_apiService);
    _appointmentService = AppointmentService(
      apiService: _apiService,
    );
    _doctorDetailsFuture = _doctorService.fetchDoctorById(widget.doctor.id);
  }

  @override
  void dispose() {
    _motifController.dispose();
    _notesController.dispose();
    super.dispose();
  }

  Future<DateTime?> _showDatePicker() async {
    return await showDatePicker(
      context: context,
      initialDate: DateTime.now(),
      firstDate: DateTime.now(),
      lastDate: DateTime.now().add(const Duration(days: 365)),
      builder: (context, child) {
        return Theme(
          data: ThemeData.light().copyWith(
            colorScheme: const ColorScheme.light(
              primary: _darkerPrimaryColor,
              onPrimary: Colors.white,
              onSurface: Colors.black,
            ),
            textButtonTheme: TextButtonThemeData(
              style: TextButton.styleFrom(
                foregroundColor: _darkerPrimaryColor,
              ),
            ),
          ),
          child: child!,
        );
      },
    );
  }

  Future<TimeOfDay?> _showTimePicker() async {
    return await showTimePicker(
      context: context,
      initialTime: TimeOfDay.now(),
      builder: (context, child) {
        return Theme(
          data: ThemeData.light().copyWith(
            colorScheme: const ColorScheme.light(
              primary: _darkerPrimaryColor,
              onPrimary: Colors.white,
              onSurface: Colors.black,
            ),
            textButtonTheme: TextButtonThemeData(
              style: TextButton.styleFrom(
                foregroundColor: _darkerPrimaryColor,
              ),
            ),
          ),
          child: child!,
        );
      },
    );
  }

  Future<void> _showDateTimeSelectionAndBook() async {
    if (_isLoading) return;

    setState(() => _isLoading = true);

    final pickedDate = await _showDatePicker();
    if (pickedDate == null) {
      setState(() => _isLoading = false);
      return;
    }

    final pickedTime = await _showTimePicker();
    if (pickedTime == null) {
      setState(() => _isLoading = false);
      return;
    }

    final appointmentDateTime = DateTime(
      pickedDate.year,
      pickedDate.month,
      pickedDate.day,
      pickedTime.hour,
      pickedTime.minute,
    );

    setState(() => _isLoading = false);

    final confirmed = await _showConfirmationDialog(pickedDate, pickedTime);
    if (!confirmed) return;

    setState(() => _isLoading = true);
    if (_formKey.currentState?.validate() ?? false) {
      await _bookAppointment(appointmentDateTime);
    } else {
      setState(() => _isLoading = false);
    }
  }

  Future<bool> _showConfirmationDialog(DateTime date, TimeOfDay time) async {
    return await showDialog<bool>(
      context: context,
      builder: (context) {
        return AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(15)),
          title: const Text('Confirm Appointment', style: TextStyle(fontWeight: FontWeight.bold)),
          content: Form(
            key: _formKey,
            child: SingleChildScrollView(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  _buildDetailRow(Icons.person_outline, 'Doctor', widget.doctor.name),
                  _buildDetailRow(Icons.calendar_today, 'Date', DateFormat.yMMMMd().format(date)),
                  _buildDetailRow(Icons.access_time, 'Time', time.format(context)),
                  const SizedBox(height: 20),
                  TextFormField(
                    controller: _motifController,
                    decoration: InputDecoration(
                      labelText: 'Reason for visit (optional)',
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(10),
                      ),
                      prefixIcon: Icon(Icons.info_outline, color: _darkerPrimaryColor),
                      alignLabelWithHint: true,
                    ),
                    maxLines: 2,
                    keyboardType: TextInputType.multiline,
                  ),
                  const SizedBox(height: 10),
                  TextFormField(
                    controller: _notesController,
                    decoration: InputDecoration(
                      labelText: 'Additional notes (optional)',
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(10),
                      ),
                      prefixIcon: Icon(Icons.note_alt_outlined, color: _darkerPrimaryColor),
                      alignLabelWithHint: true,
                    ),
                    maxLines: 3,
                    keyboardType: TextInputType.multiline,
                  ),
                ],
              ),
            ),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context, false),
              style: TextButton.styleFrom(
                foregroundColor: Colors.grey[700],
              ),
              child: const Text('Cancel'),
            ),
            ElevatedButton(
              onPressed: () {
                if (_formKey.currentState?.validate() ?? false) {
                  Navigator.pop(context, true);
                }
              },
              style: ElevatedButton.styleFrom(
                backgroundColor: _primaryColor,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(8),
                ),
                padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
              ),
              child: const Text('Confirm', style: TextStyle(color: Colors.white)),
            ),
          ],
        );
      },
    ) ?? false;
  }

  Future<void> _bookAppointment(DateTime dateTime) async {
    setState(() => _isLoading = true);
    try {
      final String? authToken = await _apiService.getAuthToken();
      if (authToken == null) {
        throw Exception('Authentication required to book appointments. Please log in.');
      }

      await _appointmentService.createAppointment(
        doctorId: widget.doctor.id,
        dateHeure: dateTime,
        motif: _motifController.text.trim(),
        notes: _notesController.text.trim(),
      );

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Appointment booked successfully!')),
        );
        widget.onAppointmentBooked?.call();
        Navigator.of(context).pop();
      }
    } on Exception catch (e) {
      debugPrint('Error during appointment booking: $e');
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Failed to book appointment: ${e.toString()}')),
        );
      }
    } finally {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  Widget _buildDetailRow(IconData icon, String label, String? value, {Color? valueColor}) {
    final displayValue = (value == null || value.isEmpty) ? 'Not specified' : value;

    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4.0),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, color: _darkerPrimaryColor, size: 20),
          const SizedBox(width: 10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  label,
                  style: TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w500,
                    color: Colors.grey[600],
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  displayValue,
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.w400,
                    color: valueColor ?? Colors.black87,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildDoctorInfo(Doctor doctor) {
    return Column(
      children: [
        Card(
          color: Colors.white,
          elevation: 3,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(15)),
          margin: const EdgeInsets.symmetric(vertical: 10),
          child: Padding(
            padding: const EdgeInsets.all(20.0),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                CircleAvatar(
                  radius: 40,
                  backgroundColor: _primaryColor.withOpacity(0.1),
                  backgroundImage: doctor.imageUrl != null && doctor.imageUrl!.isNotEmpty
                      ? NetworkImage(doctor.imageUrl!) as ImageProvider
                      : null,
                  child: doctor.imageUrl == null || doctor.imageUrl!.isEmpty
                      ? Icon(Icons.person, size: 50, color: _darkerPrimaryColor)
                      : null,
                ),
                const SizedBox(width: 20),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        doctor.name,
                        style: const TextStyle(
                            fontSize: 26,
                            fontWeight: FontWeight.bold,
                            color: Colors.black87),
                      ),
                      const SizedBox(height: 4),
                      if (doctor.specialty != null && doctor.specialty!.isNotEmpty)
                        Text(
                          doctor.specialty!,
                          style: TextStyle(
                            fontSize: 18,
                            color: Colors.grey[700],
                            fontStyle: FontStyle.italic,
                          ),
                        ),
                      if (doctor.city != null && doctor.city!.isNotEmpty)
                        Padding(
                          padding: const EdgeInsets.only(top: 4.0),
                          child: Text(
                            doctor.city!,
                            style: TextStyle(
                              fontSize: 16,
                              color: Colors.grey[600],
                            ),
                          ),
                        ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ),
        const SizedBox(height: 10),
        Card(
          color: Colors.white,
          elevation: 3,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(15)),
          margin: const EdgeInsets.symmetric(vertical: 10),
          child: Padding(
            padding: const EdgeInsets.all(20.0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Contact Information',
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                        fontWeight: FontWeight.bold,
                        color: _darkerPrimaryColor,
                      ),
                ),
                const SizedBox(height: 10),
                _buildDetailRow(Icons.email, 'Email', doctor.email),
                _buildDetailRow(Icons.phone, 'Phone Number', doctor.telephone),
                _buildDetailRow(Icons.location_on, 'Address', doctor.address),
              ],
            ),
          ),
        ),
        const SizedBox(height: 10),
        Card(
          color: Colors.white,
          elevation: 3,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(15)),
          margin: const EdgeInsets.symmetric(vertical: 10),
          child: Padding(
            padding: const EdgeInsets.all(20.0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Professional Details',
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                        fontWeight: FontWeight.bold,
                        color: _darkerPrimaryColor,
                      ),
                ),
                const SizedBox(height: 10),
                _buildDetailRow(Icons.attach_money, 'Consultation Fee', doctor.formattedFee),
                _buildDetailRow(Icons.school, 'Qualifications', doctor.qualifications),
                _buildDetailRow(Icons.work, 'Experience', doctor.experience),
              ],
            ),
          ),
        ),
        const SizedBox(height: 10),
        Card(
          color: Colors.white,
          elevation: 3,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(15)),
          margin: const EdgeInsets.symmetric(vertical: 10),
          child: Padding(
            padding: const EdgeInsets.all(20.0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Availability',
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                        fontWeight: FontWeight.bold,
                        color: _darkerPrimaryColor,
                      ),
                ),
                const SizedBox(height: 10),
                _buildDetailRow(
                  Icons.calendar_month,
                  'Working Days',
                  doctor.workingDays.isNotEmpty ? doctor.workingDays.join(', ') : null,
                ),
                _buildDetailRow(
                  Icons.access_time,
                  'Working Hours',
                  doctor.workingHours != null ? doctor.workingHours!.formattedHours(context) : null,
                ),
                Padding(
                  padding: const EdgeInsets.symmetric(vertical: 4.0),
                  child: Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Icon(
                        Icons.check_circle_outline,
                        color: doctor.isAvailableToday ? Colors.green : Colors.red,
                        size: 20,
                      ),
                      const SizedBox(width: 10),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'Available Today',
                              style: TextStyle(
                                fontSize: 14,
                                fontWeight: FontWeight.w500,
                                color: Colors.grey[600],
                              ),
                            ),
                            const SizedBox(height: 2),
                            Text(
                              doctor.isAvailableToday ? 'Yes' : 'No',
                              style: TextStyle(
                                fontSize: 16,
                                fontWeight: FontWeight.w400,
                                color: doctor.isAvailableToday ? Colors.green : Colors.red,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ),
      ],
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text(
          'Doctor Details',
          style: TextStyle(
            fontWeight: FontWeight.bold,
            color: Colors.white,
          ),
        ),
        centerTitle: true,
        backgroundColor: _primaryColor,
        elevation: 0.5,
        iconTheme: const IconThemeData(color: Colors.white),
      ),
      backgroundColor: const Color(0xFFF0F2F5),
      body: FutureBuilder<Doctor>(
        future: _doctorDetailsFuture,
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) {
            return Center(child: CircularProgressIndicator(color: _primaryColor));
          }

          if (snapshot.hasError) {
            debugPrint('Doctor detail screen error: ${snapshot.error}');
            return Center(
                child: Text(
              'Error loading details: ${snapshot.error}',
              style: const TextStyle(color: Colors.red),
            ));
          }

          if (!snapshot.hasData || snapshot.data == null) {
            return const Center(child: Text('No doctor details available.'));
          }

          final doctor = snapshot.data!;

          return SingleChildScrollView(
            padding: const EdgeInsets.all(16),
            child: Column(
              children: [
                _buildDoctorInfo(doctor),
                const SizedBox(height: 30),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton(
                    onPressed: _isLoading ? null : _showDateTimeSelectionAndBook,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: _primaryColor,
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 16),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(10),
                      ),
                      elevation: 5,
                    ),
                    child: _isLoading
                        ? const CircularProgressIndicator(color: Colors.white)
                        : const Text(
                            'BOOK APPOINTMENT',
                            style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                          ),
                  ),
                ),
              ],
            ),
          );
        },
      ),
    );
  }
}