class MedicalDossier {
  final int? id;
  final int patientId;
  final double? poids;
  final double? taille;
  final String? groupeSanguin;
  final List<String>? allergies;
  final List<String>? antecedents;
  final List<String>? traitements;
  final DateTime? derniereConsultation;
  final DateTime? createdAt;
  final DateTime? updatedAt;

  MedicalDossier({
    this.id,
    required this.patientId,
    this.poids,
    this.taille,
    this.groupeSanguin,
    this.allergies,
    this.antecedents,
    this.traitements,
    this.derniereConsultation,
    this.createdAt,
    this.updatedAt,
  });

  factory MedicalDossier.fromJson(Map<String, dynamic> json) {
    return MedicalDossier(
      id: json['id'] as int?,
      patientId: json['patient_id'] as int,
      poids: (json['poids'] as num?)?.toDouble(),
      taille: (json['taille'] as num?)?.toDouble(),
      groupeSanguin: json['groupe_sanguin'] as String?,
      allergies: _parseStringToList(json['allergies']),
      antecedents: _parseStringToList(json['antecedents']),
      traitements: _parseStringToList(json['traitements']),
      derniereConsultation: _parseDateTime(json['derniere_consultation']),
      createdAt: _parseDateTime(json['created_at']),
      updatedAt: _parseDateTime(json['updated_at']),
    );
  }

  static List<String>? _parseStringToList(dynamic value) {
    if (value == null || value.toString().isEmpty) return null;
    return value.toString().split('\n');
  }

  static DateTime? _parseDateTime(dynamic value) {
    if (value == null) return null;
    return DateTime.tryParse(value.toString());
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'patient_id': patientId,
      'poids': poids,
      'taille': taille,
      'groupe_sanguin': groupeSanguin,
      'allergies': _listToString(allergies),
      'antecedents': _listToString(antecedents),
      'traitements': _listToString(traitements),
      'derniere_consultation': _dateToIsoString(derniereConsultation),
      'created_at': _dateToIsoString(createdAt),
      'updated_at': _dateToIsoString(updatedAt),
    };
  }

  Map<String, dynamic> toApiPayload() {
    return {
      'patient_id': patientId,
      'poids': poids,
      'taille': taille,
      'groupe_sanguin': groupeSanguin,
      'allergies': _listToString(allergies),
      'antecedents': _listToString(antecedents),
      'traitements': _listToString(traitements),
      'derniere_consultation': _dateToIsoString(derniereConsultation),
    };
  }

  static String? _listToString(List<String>? list) {
    if (list == null || list.isEmpty) return null;
    return list.join('\n');
  }

  static String? _dateToIsoString(DateTime? date) {
    return date?.toIso8601String();
  }

  MedicalDossier copyWith({
    int? id,
    int? patientId,
    double? poids,
    double? taille,
    String? groupeSanguin,
    List<String>? allergies,
    List<String>? antecedents,
    List<String>? traitements,
    DateTime? derniereConsultation,
    DateTime? createdAt,
    DateTime? updatedAt,
  }) {
    return MedicalDossier(
      id: id ?? this.id,
      patientId: patientId ?? this.patientId,
      poids: poids ?? this.poids,
      taille: taille ?? this.taille,
      groupeSanguin: groupeSanguin ?? this.groupeSanguin,
      allergies: allergies ?? this.allergies,
      antecedents: antecedents ?? this.antecedents,
      traitements: traitements ?? this.traitements,
      derniereConsultation: derniereConsultation ?? this.derniereConsultation,
      createdAt: createdAt ?? this.createdAt,
      updatedAt: updatedAt ?? this.updatedAt,
    );
  }
}