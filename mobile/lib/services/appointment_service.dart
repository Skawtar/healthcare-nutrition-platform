// lib/services/appointment_service.dart
import 'dart:convert';
import 'dart:io';
import 'package:flutter/material.dart'; // Ensure this is imported for debugPrint
import 'package:http/http.dart' as http;
import 'package:flutter_application_1/models/Appointment.dart';
import 'package:flutter_application_1/services/auth/api_service.dart';
import 'package:http/http.dart' as apiService;
import 'package:intl/intl.dart';

class AppointmentService {
  final ApiService _apiService;
  static const String _baseUrl = 'http://127.0.0.1:8000/api';

  AppointmentService({required ApiService apiService}) : _apiService = apiService;

  // Helper methods -----------------------------------------------------------

  Map<String, String> _buildHeaders(String token) {
    return {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      'Authorization': 'Bearer $token',
    };
  }

  String _formatAppointmentDate(DateTime dateTime) {
    // Ensure the date format matches what your backend expects or returns for creation
    // The .toUtc() and 'Z' are important for consistent timezone handling
    return DateFormat("yyyy-MM-dd'T'HH:mm:ss.SSS'Z'").format(dateTime.toUtc());
  }

  String _extractErrorMessage(dynamic responseData, int statusCode) {
    if (responseData is Map<String, dynamic>) {
      return responseData['message'] ??
             responseData['error'] ??
             (responseData['errors']?.values.join('\n') ?? 'Request failed with status $statusCode');
    }
    return 'Request failed with status $statusCode';
  }

  // Appointment operations ---------------------------------------------------

  Future<List<Appointment>> _fetchAppointments(String endpoint) async {
    final token = await _apiService.getAuthToken();
    if (token == null) {
      debugPrint('Authentication token is null. Throwing Exception.');
      throw Exception('Authentication required');
    }

    try {
      final response = await http.get(
        Uri.parse('$_baseUrl/$endpoint'),
        headers: _buildHeaders(token),
      );

      // --- ADDED DEBUG PRINTS FOR RESPONSE INSPECTION ---
      debugPrint('--- API Response Debug Start ---');
      debugPrint('Endpoint: $_baseUrl/$endpoint');
      debugPrint('Status Code: ${response.statusCode}');
      debugPrint('Response Body: ${response.body}');
      debugPrint('--- API Response Debug End ---');
      // --- END OF ADDED DEBUG PRINTS ---

      if (response.statusCode == 200) {
        final responseData = json.decode(response.body);
        if (responseData['success'] == true) {
          if (!responseData.containsKey('data') || responseData['data'] == null) {
            debugPrint('API response does not contain "data" key or "data" is null. Returning empty list.');
            return [];
          }

          List<dynamic> rawAppointments = responseData['data'] as List;
          if (rawAppointments.isEmpty) {
            debugPrint('API "data" array is empty. Returning empty list.');
            return [];
          }

          return rawAppointments.map((json) {
            // Handle ordonnance conversion if present
            if (json.containsKey('ordonnance') && json['ordonnance'] is String && json['ordonnance'].isNotEmpty) {
              try {
                json['ordonnance'] = jsonDecode(json['ordonnance']);
                debugPrint('Successfully parsed ordonnance for ID ${json['id'] ?? 'N/A'}.');
              } catch (e) {
                debugPrint('Failed to parse ordonnance for ID ${json['id'] ?? 'N/A'}: $e');
                json['ordonnance'] = null; // Set to null if parsing fails
              }
            } else if (json.containsKey('ordonnance') && json['ordonnance'] == null) {
              json['ordonnance'] = null;
            } else {
              json['ordonnance'] = null; // Default to null if missing or not string
            }

            debugPrint('Parsing appointment JSON: $json');
            return Appointment.fromJson(json); // <--- This should now work with fixed Doctor model
          }).toList();
        }
        throw Exception(responseData['message'] ?? 'Invalid response format: success was false');
      } else {
        debugPrint('API returned non-200 status: ${response.statusCode}, Body: ${response.body}');
        throw Exception(_extractErrorMessage(json.decode(response.body), response.statusCode));
      }
    } on SocketException {
      debugPrint('SocketException: No internet connection');
      throw Exception('No internet connection');
    } on FormatException {
      debugPrint('FormatException: Invalid JSON response from server');
      throw Exception('Invalid server response format');
    } catch (e, stacktrace) { // Catch stacktrace
      debugPrint('*** AppointmentService ERROR in _fetchAppointments ***');
      debugPrint('Error: $e');
      debugPrint('StackTrace: $stacktrace'); // Print the stacktrace
      throw Exception('Failed to load appointments: ${e.toString()}');
    }
  }

  Future<List<Appointment>> getUpcomingAppointments() => _fetchAppointments('appointments/upcoming');

  Future<List<Appointment>> getPastAppointments() => _fetchAppointments('appointments/past');


 Future<void> createAppointment({
  required int doctorId,
  required DateTime dateHeure,
  String? motif,
  String? notes,
}) async {
  try {
    // Format the date correctly for Laravel
    final formattedDate = DateFormat("yyyy-MM-dd'T'HH:mm:ss.SSS").format(dateHeure.toUtc());
    debugPrint('Formatted date for API: $formattedDate');

    final Map<String, dynamic> requestBody = {
      'medecin_id': doctorId,
      'date_heure': formattedDate, // Now matches "2023-12-25T14:30:00.000"
      'motif': motif,
      'notes': notes,
    };

    final response = await _apiService.post('/appointments', requestBody);
    
    if (response['success'] == true) {
      debugPrint('Appointment created successfully!');
    } else {
      throw Exception(response['message'] ?? 'Failed to create appointment.');
    }
  } catch (e) {
    debugPrint('Error in createAppointment: $e');
    rethrow;
  }
}
  
  
  
  Future<void> cancelAppointment(String appointmentId) async {
    final token = await _apiService.getAuthToken();
    if (token == null) throw Exception('Authentication required');

    try {
      final response = await http.put(
        Uri.parse('$_baseUrl/appointments/$appointmentId/cancel'),
        headers: _buildHeaders(token),
      );

      if (response.statusCode != 200) {
        throw Exception(_extractErrorMessage(json.decode(response.body), response.statusCode));
      }
    } on SocketException {
      throw Exception('No internet connection');
    } catch (e) {
      debugPrint('Error canceling appointment: $e');
      throw Exception('Failed to cancel appointment');
    }
  }
}