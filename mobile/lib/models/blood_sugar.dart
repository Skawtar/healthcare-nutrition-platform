
class BloodSugar {
  final int id;
  final int patientId;
  final double value;
  final String measurementType; // fasting, after_meal, random
  final DateTime measurementAt;
  final String? notes;

  BloodSugar({
    required this.id,
    required this.patientId,
    required this.value,
    required this.measurementType,
    required this.measurementAt,
    this.notes,
  });
factory BloodSugar.fromJson(Map<String, dynamic> json) {
  return BloodSugar(
    id: json['id'] is int ? json['id'] : int.parse(json['id'].toString()), // Ensure int
      patientId: json['patient_id'] as int? ?? 0,
      value: double.tryParse(json['value'].toString()) ?? 0.0, // Handle string values
    measurementType: json['measurement_type'] as String,
    measurementAt: DateTime.parse(json['measurement_at'] as String),
    notes: json['notes'] as String?,
  );
}
Map<String, dynamic> toJson() {
    return {
      'id': id,
      'patient_id': patientId,
      'value': value,
      'measurement_type': measurementType,
      'measurement_at': measurementAt.toIso8601String(),
      'notes': notes,
    };
  }
}