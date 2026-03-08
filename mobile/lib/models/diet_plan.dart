import 'package:flutter/material.dart';
import 'package:flutter_application_1/models/doctor.dart';
import 'package:flutter_application_1/models/patient.dart';
import 'package:intl/intl.dart';

class RegimeAlimentaire {
  final int id;
  final DateTime datePrescription;
  final DateTime dateExpiration;
  final int caloriesJournalieres;
  final List<String> restrictions;
  final String recommandations;
  final Patient? patient;
  final Doctor? medecin;
  final String statusCode;
  final String? statusName;
  final String? statusColor; // This will now hold the color name or hex

  RegimeAlimentaire({
    required this.id,
    required this.datePrescription,
    required this.dateExpiration,
    required this.caloriesJournalieres,
    required this.statusCode,
    required this.restrictions,
    required this.recommandations,
    this.patient,
    this.medecin,
    this.statusName,
    this.statusColor,
  });

  factory RegimeAlimentaire.fromJson(Map<String, dynamic> json) {
    try {
      return RegimeAlimentaire(
        id: _parseInt(json['id']),
        datePrescription: _parseDate(json['date_prescription'])!,
        dateExpiration: _parseDate(json['date_expiration'])!,
        caloriesJournalieres: _parseInt(json['calories_journalieres']),
        statusCode: _parseString(json['status_code']) ?? 'UNKNOWN',
        statusName: _parseString(json['status']?['name']),
        statusColor: _parseString(json['status']?['color']), // Now correctly parses "green", "orange", etc.
        restrictions: _parseStringList(json['restrictions']),
        recommandations: _parseString(json['recommandations']) ?? '',
        patient: _parsePatient(json['patient']),
        medecin: _parseDoctor(json['medecin']),
      );
    } catch (e) {
      throw FormatException('Failed to parse RegimeAlimentaire: $e');
    }
  }

  // Safe getters for UI
  String get patientName => patient?.fullName ?? 'Not specified';
  String get doctorName => medecin?.name ?? 'Not specified';
  String get formattedCalories => '$caloriesJournalieres kcal';
  String get statusDisplay => statusName?.toUpperCase() ?? statusCode.toUpperCase();

  // Modified getter to handle both hex and named colors
  Color get statusColorValue {
    if (statusColor != null) {
      // First, try to parse as a hex color
      if (statusColor!.startsWith('#') || (statusColor!.length == 6 && int.tryParse(statusColor!, radix: 16) != null)) {
        try {
          return _hexToColor(statusColor!);
        } catch (e) {
          // If hex parsing fails, fall back to named color parsing
          return _parseNamedColor(statusColor!);
        }
      } else {
        // If it doesn't look like a hex string, assume it's a named color
        return _parseNamedColor(statusColor!);
      }
    }
    // If statusColor is null, use the default based on statusCode
    return _getDefaultStatusColorByStatusCode(statusCode);
  }

  // Date formatters
  String get formattedPrescriptionDate => 
      DateFormat('dd/MM/yyyy').format(datePrescription);
  String get formattedExpirationDate => 
      DateFormat('dd/MM/yyyy').format(dateExpiration);

  // JSON conversion
  Map<String, dynamic> toJson() => {
    'id': id,
    'date_prescription': datePrescription.toIso8601String(),
    'date_expiration': dateExpiration.toIso8601String(),
    'calories_journalieres': caloriesJournalieres,
    'status_code': statusCode,
    'status_name': statusName,
    'status_color': statusColor,
    'restrictions': restrictions,
    'recommandations': recommandations,
    if (patient != null) 'patient': patient!.toJson(),
    if (medecin != null) 'medecin': medecin,
  };

  // Helper parsing methods
  static int _parseInt(dynamic value) => 
      (value is num) ? value.toInt() : int.tryParse(value.toString()) ?? 0;

  static DateTime? _parseDate(dynamic value) {
    if (value == null) return null;
    try {
      return DateTime.parse(value.toString());
    } catch (e) {
      return DateTime.now(); // Fallback to current date
    }
  }

  static String? _parseString(dynamic value) => 
      (value != null) ? value.toString() : null;

  static List<String> _parseStringList(dynamic value) {
    if (value is List) {
      return value.map((e) => e.toString()).toList();
    }
    return [];
  }

  static Patient? _parsePatient(dynamic value) {
    try {
      return (value != null) 
          ? Patient.fromJson(value as Map<String, dynamic>) 
          : null;
    } catch (e) {
      return null;
    }
  }

  static Doctor? _parseDoctor(dynamic value) {
    try {
      return (value != null) 
          ? Doctor.fromJson(value as Map<String, dynamic>) 
          : null;
    } catch (e) {
      return null;
    }
  }

  // Color conversion for hex codes
  static Color _hexToColor(String hexString) {
    final buffer = StringBuffer();
    if (hexString.length == 6 || hexString.length == 7) buffer.write('ff');
    buffer.write(hexString.replaceFirst('#', ''));
    return Color(int.parse(buffer.toString(), radix: 16));
  }

  // New helper to parse common named colors
  static Color _parseNamedColor(String colorName) {
    switch (colorName.toLowerCase()) {
      case 'green': return Colors.green;
      case 'orange': return Colors.orange;
      case 'red': return Colors.red;
      case 'blue': return Colors.blue;
      case 'purple': return Colors.purple;
      case 'yellow': return Colors.yellow;
      case 'black': return Colors.black;
      case 'white': return Colors.white;
      case 'grey': return Colors.grey;
      // Add more named colors as needed
      default: return Colors.grey; // Default for unknown names
    }
  }

  // Renamed and kept for statusCode fallback
  static Color _getDefaultStatusColorByStatusCode(String status) {
    switch (status.toLowerCase()) {
      case 'actif': return Colors.green;
      case 'annule': return Colors.orange;
      case 'expire': return Colors.red;
      case 'active': return Colors.green;
      case 'expired': return Colors.red;
      case 'pending': return Colors.orange;
      default: return Colors.grey;
    }
  }

  // Copy with method for updates
  RegimeAlimentaire copyWith({
    int? id,
    DateTime? datePrescription,
    DateTime? dateExpiration,
    int? caloriesJournalieres,
    String? statusCode,
    String? statusName,
    String? statusColor,
    List<String>? restrictions,
    String? recommandations,
    Patient? patient,
    Doctor? medecin,
  }) {
    return RegimeAlimentaire(
      id: id ?? this.id,
      datePrescription: datePrescription ?? this.datePrescription,
      dateExpiration: dateExpiration ?? this.dateExpiration,
      caloriesJournalieres: caloriesJournalieres ?? this.caloriesJournalieres,
      statusCode: statusCode ?? this.statusCode,
      statusName: statusName ?? this.statusName,
      statusColor: statusColor ?? this.statusColor,
      restrictions: restrictions ?? this.restrictions,
      recommandations: recommandations ?? this.recommandations,
      patient: patient ?? this.patient,
      medecin: medecin ?? this.medecin,
    );
  }
}
