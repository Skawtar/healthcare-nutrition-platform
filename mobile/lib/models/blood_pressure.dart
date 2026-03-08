class BloodPressure {
  final int id;
  final int patientId;
  final int systolic;
  final int diastolic;
  final DateTime measurementAt;
  final String? notes;
  final DateTime createdAt;
  final DateTime updatedAt;

  BloodPressure({
    required this.id,
    required this.patientId,
    required this.systolic,
    required this.diastolic,
    required this.measurementAt,
    this.notes,
    required this.createdAt,
    required this.updatedAt,
  });

  factory BloodPressure.fromJson(Map<String, dynamic> json) {
    return BloodPressure(
      id: json['id'] as int? ?? 0,
      patientId: json['patient_id'] as int? ?? 0,
      systolic: json['systolic'] as int? ?? 0,
      diastolic: json['diastolic'] as int? ?? 0,
      measurementAt: DateTime.parse(json['measurement_at'] as String? ?? DateTime.now().toIso8601String()),
      notes: json['notes'] as String?,
      createdAt: DateTime.parse(json['created_at'] as String? ?? DateTime.now().toIso8601String()),
      updatedAt: DateTime.parse(json['updated_at'] as String? ?? DateTime.now().toIso8601String()),
    );
  }

  Map<String, dynamic> toJson() => {
    'id': id,
    'patient_id': patientId,
    'systolic': systolic,
    'diastolic': diastolic,
    'measurement_at': measurementAt.toIso8601String(),
    'notes': notes,
    'created_at': createdAt.toIso8601String(),
    'updated_at': updatedAt.toIso8601String(),
  };
}