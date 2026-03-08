import 'dart:convert'; 
import 'package:flutter/material.dart'; 

class DocumentMedical {
  final String id;
  final String patientId;
  final String? medecinId; 
  final String? medicalRecordId; 
  final String title; // This is derived from nomFichier
  final String documentType;
  final DateTime dateCreation;
  final String? fichier; // <-- Make nullable, as per DB (blob might be null)
  final bool? estSigne; // <-- Make nullable, check DB if it's always set
  final String? nomFichier; // <-- Make nullable, though used for title
  final String? description; // <-- Make nullable, as per DB (can be NULL)
  final bool isRead; // This is a client-side property, not from DB for now

  // Add created_at and updated_at since they are in your DB
  final String? createdAt;
  final String? updatedAt;

  DocumentMedical({
    required this.id,
    required this.patientId,
    this.medecinId, // No 'required' for nullable
    this.medicalRecordId, // No 'required' for nullable
    required this.title, // Derived from nomFichier or default
    required this.documentType,
    required this.dateCreation,
    this.fichier,
    this.estSigne, // No 'required' for nullable
    this.nomFichier,
    this.description,
    this.isRead = false, // Default value for client-side
    this.createdAt,
    this.updatedAt,
  });


factory DocumentMedical.fromJson(Map<String, dynamic> json) {
  // Handle potential null date_creation
  final dateCreation = DateTime.tryParse(json['date_creation']?.toString() ?? '') ?? 
                      DateTime.now();
  
  return DocumentMedical(
    id: json['id']?.toString() ?? 'no-id',
    patientId: json['patient_id']?.toString() ?? 'no-patient',
    medecinId: json['medecin_id']?.toString(),
    medicalRecordId: json['medical_record_id']?.toString(),
    title: json['title']?.toString() ?? // Try different field names
           json['nom_fichier']?.toString() ?? 
           json['name']?.toString() ??
           'Untitled Document',
    documentType: json['document_type']?.toString()?.toUpperCase() ?? 
                 json['type']?.toString()?.toUpperCase() ??
                 'UNKNOWN',
    dateCreation: dateCreation,
    fichier: json['fichier']?.toString(),
    estSigne: json['est_signe'] as bool?,
    nomFichier: json['nom_fichier']?.toString(),
    description: json['description']?.toString(),
  );
}



  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'patient_id': patientId,
      'medecin_id': medecinId,
      'medical_record_id': medicalRecordId,
      'nom_fichier': nomFichier,
      'document_type': documentType,
      'date_creation': dateCreation.toIso8601String(),
      'fichier': fichier,
      'est_signe': estSigne,
      'description': description,
      'created_at': createdAt, // Add for consistency if needed for sending
      'updated_at': updatedAt, // Add for consistency if needed for sending
    };
  }
}