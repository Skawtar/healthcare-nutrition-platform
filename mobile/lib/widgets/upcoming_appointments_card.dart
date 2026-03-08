// widgets/upcoming_appointments_card.dart
import 'package:flutter/material.dart';
import 'package:flutter_application_1/models/Appointment.dart';

class UpcomingAppointmentsCard extends StatelessWidget {
  final List<Appointment> appointments;

  const UpcomingAppointmentsCard({Key? key, required this.appointments}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Upcoming',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 8),
            ...appointments.take(2).map((appointment) => Column(
              children: [
                ListTile(
                  leading: const Icon(Icons.calendar_today),
                  title: Text(appointment.motif),
                  subtitle: Text(
                    '${_formatDate(appointment.dateHeure)}, ${_formatTime(appointment.dateHeure)}',
                  ),
                  trailing: const Icon(Icons.chevron_right),
                ),
                const Divider(),
              ],
            )),
            if (appointments.length > 2) TextButton(
              onPressed: () {
                // Navigate to full appointments screen
              },
              child: const Text('View All'),
            ),
          ],
        ),
      ),
    );
  }

  String _formatDate(DateTime date) {
    if (date.day == DateTime.now().day) return 'Today';
    if (date.day == DateTime.now().day + 1) return 'Tomorrow';
    return '${date.day}/${date.month}/${date.year}';
  }

  String _formatTime(DateTime date) {
    return '${date.hour}:${date.minute.toString().padLeft(2, '0')}';
  }
}