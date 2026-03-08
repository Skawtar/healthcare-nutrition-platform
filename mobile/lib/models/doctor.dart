// lib/models/doctor.dart
import 'package:flutter/material.dart'; // For debugPrint, if needed
import 'package:intl/intl.dart'; // For DateFormat

class Doctor {
  final int id;
  final String name;
  final String? specialty; // NOW RECEIVING 'specialty' from JSON, but can be null
  final String? imageUrl; // NOW RECEIVING 'profile_image' from JSON, but can be null

  final String? email;
  final String? telephone;
  final String? address;
  final double? consultationFee;
  final String? qualifications; // Can be null
  final String? experience;   // Can be null
  final String? city;         // Can be null

  final List<String> workingDays;
  final WorkingHours? workingHours;

  final bool isAvailableToday; // Derived property

  Doctor({
    required this.id,
    required this.name,
    this.specialty,
    this.imageUrl,
    this.email,
    this.telephone,
    this.address,
    this.consultationFee,
    this.qualifications,
    this.experience,
    this.city,
    this.workingDays = const [], // Provide a default empty list
    this.workingHours,
    this.isAvailableToday = false, // Provide a default value
  });

  factory Doctor.fromJson(Map<String, dynamic> json) {
    debugPrint('--- Doctor.fromJson for: ${json['name'] ?? 'Unknown Doctor'} ---');
    debugPrint('Full JSON for this doctor: $json'); // Keep for debugging

    // Safely parse consultation_fee
    final consultationFeeRaw = json['consultation_fee'];
    double? parsedConsultationFee;
    if (consultationFeeRaw is num) {
      parsedConsultationFee = consultationFeeRaw.toDouble();
    } else if (consultationFeeRaw is String && consultationFeeRaw.isNotEmpty) {
      try {
        parsedConsultationFee = double.parse(consultationFeeRaw);
      } catch (e) {
        debugPrint('Error parsing consultation_fee string "$consultationFeeRaw": $e');
        parsedConsultationFee = null;
      }
    } else {
      parsedConsultationFee = null;
    }

    // Safely parse working_days
    List<String> workingDaysList = [];
    if (json['working_days'] is List) {
      workingDaysList = List<String>.from(json['working_days'].whereType<String>());
    }

    // Safely parse working_hours
    WorkingHours? workingHours;
    if (json['working_hours'] is Map) {
      // Pass the potential null values to WorkingHours.fromJson
      // It will handle them internally
      try {
        workingHours = WorkingHours.fromJson(json['working_hours']);
      } catch (e) {
        debugPrint('Error parsing working_hours: $e');
        workingHours = null;
      }
    }

    // Determine isAvailableToday
    bool isAvailableToday = false;
    try {
      String currentDay = DateFormat('EEEE').format(DateTime.now()); // e.g., "Monday"
      if (workingDaysList.contains(currentDay) && workingHours != null) {
        // More sophisticated logic might check if current time is within workingHours
        isAvailableToday = true;
      }
    } catch (e) {
      debugPrint('Error determining isAvailableToday: $e');
    }

    return Doctor(
      id: json['id'] as int,
      name: json['name'] as String,
      // CORRECTED: Map API field names to model field names and use 'as String?'
      specialty: json['specialty'] as String?, // API sends 'specialty', not 'specialite_code'
      imageUrl: json['profile_image'] as String?, // API sends 'profile_image', not 'image_profil'
      email: json['email'] as String?,
      telephone: json['telephone'] as String?,
      address: json['address'] as String?,
      consultationFee: parsedConsultationFee,
      qualifications: json['qualifications'] as String?, // Added 'as String?'
      experience: json['experience'] as String?,     // Added 'as String?'
      city: json['city'] as String?,                 // Added 'as String?'
      workingDays: workingDaysList,
      workingHours: workingHours,
      isAvailableToday: isAvailableToday,
    );
  }

  String get formattedFee {
    if (consultationFee == null) {
      return 'N/A';
    }
    return '${consultationFee!.toStringAsFixed(2)} MAD';
  }
}

// Ensure this is in the same file or correctly imported if separate
class WorkingHours {
  final String startTime;
  final String endTime;

  WorkingHours({required this.startTime, required this.endTime});

  factory WorkingHours.fromJson(Map<String, dynamic> json) {
    debugPrint('--- WorkingHours.fromJson ---');
    debugPrint('start: ${json['start']} (${json['start']?.runtimeType})'); // API sends 'start'
    debugPrint('end: ${json['end']} (${json['end']?.runtimeType})');     // API sends 'end'

    // CORRECTED: Use 'as String?' and provide default if null
    return WorkingHours(
      startTime: json['start'] as String? ?? '00:00', // API sends 'start', can be null
      endTime: json['end'] as String? ?? '00:00',     // API sends 'end', can be null
    );
  }

  String formattedHours(BuildContext context) {
    return '$startTime - $endTime';
  }
}