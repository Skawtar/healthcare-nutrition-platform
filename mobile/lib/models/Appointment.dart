import 'dart:convert';
import 'package:flutter_application_1/models/Doctor.dart';

class Appointment {
  final String id;
  final int patientId;
  final int medecinId;
  final DateTime dateHeure;
  final String status;
  final String motif;
  final String? notes;
  final dynamic ordonnance;
  final Doctor? doctor;
  final DateTime createdAt;
  final DateTime updatedAt;

  Appointment({
    required this.id,
    required this.patientId,
    required this.medecinId,
    required this.dateHeure,
    required this.status,
    required this.motif,
    this.notes,
    this.ordonnance,
    this.doctor,
    required this.createdAt,
    required this.updatedAt,
  });

  factory Appointment.fromJson(Map<String, dynamic> json) {
    return Appointment(
      id: json['id']?.toString() ?? '',
      patientId: int.tryParse(json['patient_id']?.toString() ?? '0') ?? 0,
      medecinId: int.tryParse(json['medecin_id']?.toString() ?? '0') ?? 0,
      dateHeure: DateTime.parse(json['date_heure']?.toString() ?? DateTime.now().toIso8601String()),
      status: json['status']?.toString()?.toLowerCase() ?? 'pending',
      motif: json['motif']?.toString() ?? '',
      notes: json['notes']?.toString(),
      ordonnance: _parseOrdonnance(json['ordonnance']),
      doctor: json['medecin'] != null ? Doctor.fromJson(json['medecin']) : null,
      createdAt: DateTime.parse(json['created_at']?.toString() ?? DateTime.now().toIso8601String()),
      updatedAt: DateTime.parse(json['updated_at']?.toString() ?? DateTime.now().toIso8601String()),
    );
  }

  static dynamic _parseOrdonnance(dynamic ordonnance) {
    if (ordonnance == null) return null;
    if (ordonnance is Map) return ordonnance;
    if (ordonnance is String) {
      try {
        return jsonDecode(ordonnance);
      } catch (e) {
        return null;
      }
    }
    return null;
  }

  Map<String, dynamic>? get parsedOrdonnance {
    if (ordonnance == null) return null;
    if (ordonnance is Map) return ordonnance as Map<String, dynamic>;
    return null;
  }
}