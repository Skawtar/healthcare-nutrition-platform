import 'dart:convert';

import 'package:intl/intl.dart';

class Patient {
  final int id;
  final String cin;
  final String nom;
  final String prenom;
  final String email;
  final String? telephone;
  final String? adresse;
  final DateTime? dateNaissance;
  final String genre;
  final String? imageProfil;
  final bool isSubscribed;
  final String? subscriptionPlan;
  final DateTime? subscriptionStartDate;
  final DateTime? subscriptionEndDate;
  final String? paymentMethod;
  final DateTime createdAt;
  final DateTime updatedAt;
  final DateTime? deletedAt;
  final String profileImageUrl;
  final bool isActiveSubscriber;

  Patient({
    required this.id,
    required this.cin,
    required this.nom,
    required this.prenom,
    required this.email,
    this.telephone,
    this.adresse,
    this.dateNaissance,
    required this.genre,
    this.imageProfil,
    required this.isSubscribed,
    this.subscriptionPlan,
    this.subscriptionStartDate,
    this.subscriptionEndDate,
    this.paymentMethod,
    required this.createdAt,
    required this.updatedAt,
    this.deletedAt,
    required this.profileImageUrl,
    required this.isActiveSubscriber,
  });
// Add to your Patient class
int? get age {
  if (dateNaissance == null) return null;
  final now = DateTime.now();
  return now.year - dateNaissance!.year - 
    (now.month < dateNaissance!.month || 
     (now.month == dateNaissance!.month && now.day < dateNaissance!.day) ? 1 : 0);
}
  // Getter for full name
  String get fullName => '$prenom $nom';

  // Getter for formatted date of birth
  String get formattedDateOfBirth {
    if (dateNaissance == null) return 'N/A';
    return DateFormat('dd/MM/yyyy').format(dateNaissance!);
  }

  // Getter for subscription period
  String get subscriptionPeriod {
    if (!isSubscribed) return 'N/A';
    
    final start = subscriptionStartDate != null 
        ? DateFormat('dd/MM/yyyy').format(subscriptionStartDate!) 
        : 'N/A';
    final end = subscriptionEndDate != null 
        ? DateFormat('dd/MM/yyyy').format(subscriptionEndDate!) 
        : 'N/A';
    
    return '$start - $end';
  }

  // Factory method to create a Patient from JSON
  factory Patient.fromJson(Map<String, dynamic> json) {
    return Patient(
      id: json['id'],
      cin: json['cin'],
      nom: json['nom'],
      prenom: json['prenom'],
      email: json['email'],
      telephone: json['telephone'],
      adresse: json['adresse'],
      dateNaissance: json['date_naissance'] != null 
          ? DateTime.parse(json['date_naissance']) 
          : null,
      genre: json['genre'],
      imageProfil: json['image_profil'],
      isSubscribed: json['is_subscribed'] ?? false,
      subscriptionPlan: json['subscription_plan'],
      subscriptionStartDate: json['subscription_start_date'] != null 
          ? DateTime.parse(json['subscription_start_date']) 
          : null,
      subscriptionEndDate: json['subscription_end_date'] != null 
          ? DateTime.parse(json['subscription_end_date']) 
          : null,
      paymentMethod: json['payment_method'],
      createdAt: DateTime.parse(json['created_at']),
      updatedAt: DateTime.parse(json['updated_at']),
      deletedAt: json['deleted_at'] != null 
          ? DateTime.parse(json['deleted_at']) 
          : null,
      profileImageUrl: json['profile_image_url'] ?? '',
      isActiveSubscriber: json['is_active_subscriber'] ?? false,
    );
  }


  // Convert a Patient to JSON
  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'cin': cin,
      'nom': nom,
      'prenom': prenom,
      'email': email,
      'telephone': telephone,
      'adresse': adresse,
      'date_naissance': dateNaissance?.toIso8601String(),
      'genre': genre,
      'image_profil': imageProfil,
      'is_subscribed': isSubscribed,
      'subscription_plan': subscriptionPlan,
      'subscription_start_date': subscriptionStartDate?.toIso8601String(),
      'subscription_end_date': subscriptionEndDate?.toIso8601String(),
      'payment_method': paymentMethod,
      'created_at': createdAt.toIso8601String(),
      'updated_at': updatedAt.toIso8601String(),
      'deleted_at': deletedAt?.toIso8601String(),
      'profile_image_url': profileImageUrl,
      'is_active_subscriber': isActiveSubscriber,
    };
  }

  // Helper method to parse date strings
  static DateTime? _parseDate(String? dateString) {
    if (dateString == null) return null;
    return DateTime.tryParse(dateString);
  }

  // Create a copy of the patient with updated fields
  Patient copyWith({
    int? id,
    String? cin,
    String? nom,
    String? prenom,
    String? email,
    String? telephone,
    String? adresse,
    DateTime? dateNaissance,
    String? genre,
    String? imageProfil,
    bool? isSubscribed,
    String? subscriptionPlan,
    DateTime? subscriptionStartDate,
    DateTime? subscriptionEndDate,
    String? paymentMethod,
    DateTime? createdAt,
    DateTime? updatedAt,
    DateTime? deletedAt,
    String? profileImageUrl,
    bool? isActiveSubscriber,
  }) {
    return Patient(
      id: id ?? this.id,
      cin: cin ?? this.cin,
      nom: nom ?? this.nom,
      prenom: prenom ?? this.prenom,
      email: email ?? this.email,
      telephone: telephone ?? this.telephone,
      adresse: adresse ?? this.adresse,
      dateNaissance: dateNaissance ?? this.dateNaissance,
      genre: genre ?? this.genre,
      imageProfil: imageProfil ?? this.imageProfil,
      isSubscribed: isSubscribed ?? this.isSubscribed,
      subscriptionPlan: subscriptionPlan ?? this.subscriptionPlan,
      subscriptionStartDate: subscriptionStartDate ?? this.subscriptionStartDate,
      subscriptionEndDate: subscriptionEndDate ?? this.subscriptionEndDate,
      paymentMethod: paymentMethod ?? this.paymentMethod,
      createdAt: createdAt ?? this.createdAt,
      updatedAt: updatedAt ?? this.updatedAt,
      deletedAt: deletedAt ?? this.deletedAt,
      profileImageUrl: profileImageUrl ?? this.profileImageUrl,
      isActiveSubscriber: isActiveSubscriber ?? this.isActiveSubscriber,
    );
  }
}