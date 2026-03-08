// lib/widgets/doctor_card.dart
import 'package:flutter/material.dart';
import 'package:flutter_application_1/models/doctor.dart';

class DoctorCard extends StatelessWidget {
  final Doctor doctor;
  final VoidCallback onTap;

  const DoctorCard({
    Key? key,
    required this.doctor,
    required this.onTap,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: const EdgeInsets.only(bottom: 16.0),
      color: Colors.white, // Set card color to white
      elevation: 4,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(12),
        child: Padding(
          padding: const EdgeInsets.all(16.0),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Doctor Profile Picture
              CircleAvatar(
                radius: 30,
                backgroundColor: const Color(0xFFE6F7FF), // Light blue background for avatar
                backgroundImage: doctor.imageUrl != null && doctor.imageUrl!.isNotEmpty
                    ? NetworkImage(doctor.imageUrl!) as ImageProvider<Object>?
                    : null,
                child: doctor.imageUrl == null || doctor.imageUrl!.isEmpty
                    ? Icon(Icons.person, size: 30, color: const Color(0xFF4682B4)) // Default icon
                    : null,
              ),
              const SizedBox(width: 16),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      doctor.name, // Use 'name' from your Doctor model
                      style: const TextStyle(
                        fontWeight: FontWeight.bold,
                        fontSize: 18,
                        color: Color(0xFF4682B4), // Steel Blue
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      doctor.specialty ?? 'General Practitioner', // Display specialty
                      style: TextStyle(
                        fontSize: 14,
                        color: Colors.grey[700],
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      doctor.email ?? 'No email provided', // Display email or a placeholder
                      style: TextStyle(
                        fontSize: 13,
                        color: Colors.grey[600],
                      ),
                    ),
                    const SizedBox(height: 2),
                    if (doctor.telephone != null && doctor.telephone!.isNotEmpty)
                      Text(
                        doctor.telephone!,
                        style: TextStyle(
                          fontSize: 13,
                          color: Colors.grey[600],
                        ),
                      ),
                    const SizedBox(height: 8),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text(
                          doctor.formattedFee, // Use the formattedFee getter
                          style: const TextStyle(
                            fontWeight: FontWeight.bold,
                            fontSize: 15,
                            color: Color(0xFF32CD32), // Lime Green for fee
                          ),
                        ),
                        if (doctor.city != null && doctor.city!.isNotEmpty)
                          Text(
                            doctor.city!, // Display city
                            style: TextStyle(
                              fontSize: 14,
                              color: Colors.grey[700],
                            ),
                          ),
                      ],
                    ),
                    const SizedBox(height: 4),
                    if (doctor.workingDays.isNotEmpty)
                      Text(
                        'Working Days: ${doctor.workingDays.join(', ')}',
                        style: TextStyle(
                          fontSize: 13,
                          fontStyle: FontStyle.italic,
                          color: Colors.grey[500],
                        ),
                      ),
                  ],
                ),
              ),
              const Icon(Icons.arrow_forward_ios, color: Color(0xFF87CEEB), size: 20), // Light blue arrow
            ],
          ),
        ),
      ),
    );
  }
}