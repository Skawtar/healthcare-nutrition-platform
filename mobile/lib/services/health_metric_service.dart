import 'dart:convert';
import 'package:flutter_application_1/models/blood_pressure.dart';
import 'package:flutter_application_1/models/blood_sugar.dart';
import 'package:flutter_application_1/services/auth/api_service.dart';
import 'package:http/http.dart' as http;
import 'package:intl/intl.dart';

class HealthMetricService {
  static const String _baseUrl = 'http://127.0.0.1:8000/api';
  final ApiService apiService;

  HealthMetricService(this.apiService);

  Future<Map<String, String>> _getHeaders() async {
    final token = await apiService.getAuthToken();
    if (token == null) throw Exception('Authentication required');

    return {
      'Accept': 'application/json',
      'Authorization': 'Bearer $token',
      'Content-Type': 'application/json',
    };
  }

  // ==================== Blood Pressure ====================
  Future<List<BloodPressure>> getBloodPressures() async {
    try {
      final response = await http.get(
        Uri.parse('$_baseUrl/blood-pressures'),
        headers: await _getHeaders(),
      );

      if (response.statusCode == 200) {
        final List<dynamic> jsonList = json.decode(response.body);
        return jsonList.map((jsonItem) => BloodPressure.fromJson(jsonItem)).toList();
      } else {
        throw _handleError(response);
      }
    } catch (e) {
      throw _handleException(e);
    }
  }
Future<void> addBloodPressure(BloodPressure reading) async {
  try {
    // Client-side validation
    if (reading.diastolic >= reading.systolic) {
      throw 'Diastolic must be less than systolic';
    }
    if (reading.measurementAt.isAfter(DateTime.now())) {
      throw 'Measurement time cannot be in the future';
    }

    final response = await http.post(
      Uri.parse('$_baseUrl/blood-pressures'),
      headers: await _getHeaders(),
      body: json.encode({
        'systolic': reading.systolic,
        'diastolic': reading.diastolic,
        'measurement_at': reading.measurementAt.toUtc().toIso8601String(),
        'notes': reading.notes,
      }),
    );

    final responseData = json.decode(response.body);
    
    if (response.statusCode == 201) {
      return responseData['data'];
    } else {
      throw responseData['message'] ?? 'Failed to add reading';
    }
  } catch (e) {
    throw 'Failed to add blood pressure: ${e.toString()}';
  }
}
  Future<void> updateBloodPressure(BloodPressure bloodPressure) async {
    try {
      final response = await http.put(
        Uri.parse('$_baseUrl/blood-pressures/${bloodPressure.id}'),
        headers: await _getHeaders(),
        body: json.encode({
          'patient_id': bloodPressure.patientId,
          'systolic': bloodPressure.systolic,
          'diastolic': bloodPressure.diastolic,
          'measurement_at': bloodPressure.measurementAt.toIso8601String(),
          'notes': bloodPressure.notes,
        }),
      );

      if (response.statusCode != 200) {
        throw _handleError(response);
      }
    } catch (e) {
      throw _handleException(e);
    }
  }

  Future<void> deleteBloodPressure(int id) async {
    try {
      final response = await http.delete(
        Uri.parse('$_baseUrl/blood-pressures/$id'),
        headers: await _getHeaders(),
      );

      if (response.statusCode != 204) {
        throw _handleError(response);
      }
    } catch (e) {
      throw _handleException(e);
    }
  }

  // ==================== Blood Sugar ====================
  Future<List<BloodSugar>> getBloodSugars() async {
    try {
      final response = await http.get(
        Uri.parse('$_baseUrl/blood-sugars'),
        headers: await _getHeaders(),
      );

      if (response.statusCode == 200) {
        final List<dynamic> jsonList = json.decode(response.body);
        return jsonList.map((jsonItem) {
          // Convert string values to numbers if needed
          if (jsonItem['value'] is String) {
            jsonItem['value'] = double.tryParse(jsonItem['value']) ?? 0.0;
          }
          return BloodSugar.fromJson(jsonItem);
        }).toList();
      } else {
        throw _handleError(response);
      }
    } catch (e) {
      throw _handleException(e);
    }
  }
Future<BloodSugar> addBloodSugar(BloodSugar bloodSugar) async {
  try {
    // Client-side validation
    if (bloodSugar.value < 50 || bloodSugar.value > 500) {
      throw 'Blood sugar value must be between 50 and 500 mg/dL';
    }

    if (bloodSugar.measurementAt.isAfter(DateTime.now())) {
      throw 'Measurement time cannot be in the future';
    }

    final response = await http.post(
      Uri.parse('$_baseUrl/blood-sugars'),
      headers: await _getHeaders(),
      body: json.encode({
        'value': bloodSugar.value,
        'measurement_type': bloodSugar.measurementType, // Fixed typo (was measurement_type)
        'measurement_at': bloodSugar.measurementAt.toUtc().toIso8601String(), // Fixed typo and added UTC
        'notes': bloodSugar.notes,
        'patient_id': bloodSugar.patientId,
      }),
    );

    final responseData = json.decode(response.body);

    if (response.statusCode == 201) {
      return BloodSugar.fromJson(responseData['data']);
    } else {
      throw responseData['message'] ?? 
           'Failed to add blood sugar reading (Status: ${response.statusCode})';
    }
  } catch (e) {
    throw 'Failed to add blood sugar: ${e.toString()}';
  }
}
  Future<void> updateBloodSugar(BloodSugar bloodSugar) async {
    try {
      final response = await http.put(
        Uri.parse('$_baseUrl/blood-sugars/${bloodSugar.id}'),
        headers: await _getHeaders(),
        body: json.encode({
          'value': bloodSugar.value,
          'measurement_type': bloodSugar.measurementType,
          'measurement_at': bloodSugar.measurementAt.toIso8601String(),
          'notes': bloodSugar.notes,
        }),
      );

      if (response.statusCode != 200) {
        throw _handleError(response);
      }
    } catch (e) {
      throw _handleException(e);
    }
  }

  Future<void> deleteBloodSugar(int id) async {
    try {
      final response = await http.delete(
        Uri.parse('$_baseUrl/blood-sugars/$id'),
        headers: await _getHeaders(),
      );

      if (response.statusCode != 204) {
        throw _handleError(response);
      }
    } catch (e) {
      throw _handleException(e);
    }
  }

  // ==================== Helper Methods ====================
  Exception _handleError(http.Response response) {
    final statusCode = response.statusCode;
    final errorBody = json.decode(response.body);
    final errorMessage = errorBody['message'] ?? 'Unknown error occurred';

    switch (statusCode) {
      case 400:
        return Exception('Bad Request: $errorMessage');
      case 401:
        return Exception('Unauthorized: $errorMessage');
      case 403:
        return Exception('Forbidden: $errorMessage');
      case 404:
        return Exception('Not Found: $errorMessage');
      case 422:
        return Exception('Validation Error: $errorMessage');
      case 500:
        return Exception('Server Error: $errorMessage');
      default:
        return Exception('HTTP Error $statusCode: $errorMessage');
    }
  }

  Exception _handleException(dynamic e) {
    if (e is http.ClientException) {
      return Exception('Network error: ${e.message}');
    } else if (e is FormatException) {
      return Exception('Data parsing error: ${e.message}');
    } else {
      return Exception('Unexpected error: ${e.toString()}');
    }
  }
}