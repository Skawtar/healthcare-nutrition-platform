// lib/services/auth/api_service.dart

import 'dart:convert';
import 'dart:io'; // Required for Platform.isAndroid
import 'package:flutter/foundation.dart'; // Required for kIsWeb and debugPrint
import 'package:flutter_application_1/models/medical_dossier.dart'; // Assuming these models exist
import 'package:flutter_application_1/models/patient.dart'; // Assuming these models exist
import 'package:flutter_application_1/models/appointment.dart'; // Assuming Appointment model exists
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

class ApiService {
  // --- Base URL Configuration ---
  // Determines the base URL based on the platform (Web, Android, iOS).
  String get baseUrl {
    if (kIsWeb) {
      // For Flutter Web (Chrome, etc.), use localhost as the backend is on the same machine.
      return 'http://localhost:8000/api';
    }
    // For Android emulator, '10.0.2.2' maps to the host machine's localhost.
    // However, if you are running on a physical Android device and your backend
    // is on your local machine, you'll need your machine's actual local IP (e.g., 192.168.1.X).
    // The previous prompt mentioned 192.168.1.102, so let's stick to that for Android device.
    // For iOS simulator, 'localhost' or '127.0.0.1' works.
    return Platform.isAndroid
        ? 'http://192.168.1.102:8000/api' // Use your machine's local IP for physical Android device or 10.0.2.2 for emulator
        : 'http://localhost:8000/api'; // For iOS Simulator
  }

  // --- SharedPreferences Keys ---
  static const String authTokenKey = 'authToken';
  static const String patientDataKey = 'patientData';

  // --- SharedPreferences Instance Management ---
  // Private static instance to ensure SharedPreferences is loaded once.
  static SharedPreferences? _prefsInstance;

  // Lazily initializes and returns the SharedPreferences instance.
  static Future<SharedPreferences> _getPrefs() async {
    _prefsInstance ??= await SharedPreferences.getInstance();
    return _prefsInstance!;
  }

  // --- Generic HTTP Request Method ---
  // This method sends all HTTP requests, handling headers (including Authorization),
  // JSON encoding/decoding, and basic error handling.
  Future<Map<String, dynamic>> _sendRequest(
    String method,
    String endpoint, {
    Map<String, dynamic>? data,
  }) async {
    final prefs = await _getPrefs();
    final token = prefs.getString(
      authTokenKey,
    ); // Retrieve token before each request

    final url = Uri.parse('$baseUrl$endpoint');
    final headers = <String, String>{
      'Accept': 'application/json',
      'Content-Type': 'application/json',
    };

    if (token != null) {
      headers['Authorization'] =
          'Bearer $token'; // Add Authorization header if token exists
      debugPrint(
        'Sending request to $endpoint with token: $token',
      ); // Debugging: show token sent
    } else {
      debugPrint(
        'No token found for endpoint: $endpoint. Requesting without auth.',
      ); // Debugging: show if no token
    }

    http.Response response;

    try {
      switch (method) {
        case 'GET':
          response = await http.get(url, headers: headers);
          break;
        case 'POST':
          response = await http.post(
            url,
            headers: headers,
            body: jsonEncode(data),
          );
          break;
        case 'PUT':
          response = await http.put(
            url,
            headers: headers,
            body: jsonEncode(data),
          );
          break;
        case 'DELETE':
          response = await http.delete(
            url,
            headers: headers,
            body: jsonEncode(data),
          );
          break;
        default:
          throw Exception('Unsupported HTTP method: $method');
      }

      debugPrint('API Response Status for $endpoint: ${response.statusCode}');
      debugPrint('API Response Body for $endpoint: ${response.body}');

      if (response.statusCode >= 200 && response.statusCode < 300) {
        // Handle 204 No Content for successful operations without a body
        if (response.body.isEmpty && response.statusCode == 204) {
          return {}; // Return an empty map for no content
        }
        return jsonDecode(response.body);
      } else {
        // --- Error Handling for Non-2xx Responses ---
        // Specifically handle 401 Unauthorized errors
        if (response.statusCode == 401) {
          debugPrint(
            'Authentication error (401) for $endpoint. Clearing token and patient data.',
          );
          await clearAuthToken(); // Clear token on 401
          await _clearPatientData(); // Clear patient data on 401
          throw Exception(
            'Unauthorized: Session expired. Please log in again.',
          );
        }

        // Try to parse error message from response body
        try {
          final Map<String, dynamic> errorBody = jsonDecode(response.body);
          final String errorMessage = errorBody['message'] ??
              (errorBody['errors'] != null
                  ? jsonEncode(errorBody['errors'])
                  : 'Unknown API error');
          throw Exception(errorMessage);
        } catch (e) {
          // Fallback if response body is not valid JSON or message is missing
          throw Exception('API Error ${response.statusCode}: ${response.body}');
        }
      }
    } catch (e) {
      debugPrint('Network or processing error for $endpoint: $e');
      rethrow; // Re-throw the exception for the calling function to handle
    }
  }

  // --- Public HTTP Request Wrappers ---
  // These methods provide a cleaner interface for making specific HTTP requests.
  Future<Map<String, dynamic>> get(String endpoint) =>
      _sendRequest('GET', endpoint);
  Future<Map<String, dynamic>> post(
    String endpoint,
    Map<String, dynamic> data,
  ) =>
      _sendRequest('POST', endpoint, data: data);
  Future<Map<String, dynamic>> put(
    String endpoint,
    Map<String, dynamic> data,
  ) =>
      _sendRequest('PUT', endpoint, data: data);
  Future<Map<String, dynamic>> delete(
    String endpoint,
    Map<String, dynamic> data,
  ) =>
      _sendRequest('DELETE', endpoint, data: data);

  // --- Auth and Patient Data Management ---

  // Helper method to get auth token
  Future<String?> getAuthToken() async {
    final prefs = await _getPrefs();
    return prefs.getString(authTokenKey);
  }

  // Helper method to save auth token after successful login/registration
  Future<void> _saveAuthToken(String token) async {
    final prefs = await _getPrefs();
    await prefs.setString(authTokenKey, token);
    debugPrint('Auth token saved successfully!');
  }

  // Helper method to clear auth token (for logout or 401 errors)
  Future<void> clearAuthToken() async {
    final prefs = await _getPrefs();
    await prefs.remove(authTokenKey);
    debugPrint('Auth token cleared.');
  }

  // Helper method to save patient data locally after login/registration
  Future<void> _savePatientData(Patient patient) async {
    final prefs = await _getPrefs();
    await prefs.setString(patientDataKey, jsonEncode(patient.toJson()));
    debugPrint('Patient data saved locally.');
  }

  // Helper method to get patient data from local storage
  Future<Patient?> getLocalPatientData() async {
    final prefs = await _getPrefs();
    final patientData = prefs.getString(patientDataKey);
    if (patientData != null) {
      try {
        return Patient.fromJson(jsonDecode(patientData));
      } catch (e) {
        debugPrint('Error parsing stored patient data: $e');
        return null;
      }
    }
    return null;
  }

  // Helper method to clear patient data locally (for logout or 401 errors)
  Future<void> _clearPatientData() async {
    final prefs = await _getPrefs();
    await prefs.remove(patientDataKey);
    debugPrint('Patient data cleared locally.');
  }

  // New getter to retrieve the current authenticated patient's ID
  Future<String?> get currentPatientId async {
    final patient = await getLocalPatientData();
    // Assuming your Patient model has an 'id' field that can be converted to String
    return patient?.id.toString();
  }

  // --- Specific API Endpoints for Patient Management ---

  Future<Map<String, dynamic>> registerPatient({
    required String cin,
    required String nom,
    required String prenom,
    required String dateNaissance,
    required String genre,
    required String email,
    required String password,
    required String passwordConfirmation,
    required String telephone,
    required String adresse,
  }) async {
    final responseData = await post('/patient/register', {
      'cin': cin,
      'nom': nom,
      'prenom': prenom,
      'date_naissance': dateNaissance,
      'genre': genre,
      'email': email,
      'password': password,
      'password_confirmation': passwordConfirmation,
      'telephone': telephone,
      'adresse': adresse,
    });

    if (responseData['token'] != null) {
      await _saveAuthToken(responseData['token']);
    }
    if (responseData['patient'] != null) {
      final patient = Patient.fromJson(responseData['patient']);
      await _savePatientData(patient);
    }
    return responseData;
  }

  Future<Map<String, dynamic>> loginPatient({
    required String email,
    required String password,
  }) async {
    final responseData = await post('/patient/login', {
      'email': email,
      'password': password,
    });

    if (responseData['token'] != null) {
      await _saveAuthToken(responseData['token']);
    }
    if (responseData['patient'] != null) {
      final patient = Patient.fromJson(responseData['patient']);
      await _savePatientData(patient);
    }
    return responseData;
  }

  Future<Patient?> getPatientProfile() async {
    final responseData = await get('/patient/profile'); // No body for GET
    if (responseData['patient'] != null) {
      return Patient.fromJson(responseData['patient']);
    }
    return null;
  }

  Future<Patient> updatePatientProfile(Map<String, dynamic> data) async {
    final responseData = await put('/patient/profile', data);
    return Patient.fromJson(responseData['patient']);
  }

  Future<void> logout() async {
    await post('/patient/logout', {}); // No specific body needed for logout
    await clearAuthToken(); // Clear token after successful logout
    await _clearPatientData(); // Clear patient data on logout
  }

  Future<Map<String, dynamic>> changePassword({
    required String currentPassword,
    required String newPassword,
  }) async {
    final token = await getAuthToken();
    if (token == null) {
      throw Exception('Authentication required. Please log in again.');
    }

    try {
      final response = await http.post(
        Uri.parse('$baseUrl/change-password'),
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
        },
        body: json.encode({
          'current_password': currentPassword,
          'new_password': newPassword,
          'new_password_confirmation':
              newPassword, // Laravel often expects this for validation
        }),
      );

      final responseData = json.decode(response.body);
      debugPrint('Change Password Response Status: ${response.statusCode}');
      debugPrint('Change Password Response Body: ${response.body}');

      if (response.statusCode == 200 || response.statusCode == 201) {
        // Assuming your backend sends {"success": true, "message": "Password updated successfully"}
        return {
          'success': true,
          'message': responseData['message'] ?? 'Password updated successfully'
        };
      } else if (response.statusCode == 422) {
        // Laravel validation error
        final errors = responseData['errors'] as Map<String, dynamic>?;
        String errorMessage = 'Validation Error';
        if (errors != null && errors.isNotEmpty) {
          // Combine all validation messages
          errorMessage = errors.values.expand((e) => e as List).join('\n');
        }
        return {'success': false, 'message': errorMessage};
      } else {
        return {
          'success': false,
          'message': responseData['message'] ?? 'Failed to change password.'
        };
      }
    } on SocketException {
      throw Exception('No internet connection. Please check your network.');
    } on FormatException {
      throw Exception('Invalid server response format.');
    } catch (e) {
      debugPrint('Change password API error: $e');
      rethrow;
    }
  }

  // Upload Profile Picture
Future<String> uploadProfilePicture(Uint8List imageBytes, {String? fileName}) async {
  try {
    final uri = Uri.parse('$baseUrl/patient/profile-picture');
    final token = await getAuthToken();
    if (token == null) {
      throw Exception('Authentication required. Please log in again.');
    }

    // Create multipart request
    var request = http.MultipartRequest('POST', uri);

    // Add token to headers
    request.headers['Authorization'] = 'Bearer $token';

    // Generate a filename if not provided
    final finalFileName = fileName ?? 'profile_${DateTime.now().millisecondsSinceEpoch}.jpg';

    // Add the image bytes
    var multipartFile = http.MultipartFile.fromBytes(
      'profile_image',
      imageBytes,
      filename: finalFileName,
    );
    request.files.add(multipartFile);

    // Send the request
    var response = await request.send();
    var responseString = await response.stream.bytesToString();

    if (response.statusCode == 200) {
      final jsonResponse = json.decode(responseString);
      return jsonResponse['profile_image_url'];
    } else {
      throw Exception('Failed to upload profile picture: ${response.statusCode}');
    }
  } catch (e) {
    throw Exception('Error uploading profile picture: $e');
  }
}
  // --- Specific API Endpoints for Medical Dossier ---

  // Fetch Appointments for the current patient
  Future<List<Appointment>> fetchAppointments() async {
    final responseData = await get(
      '/patient/appointments',
    ); // Uses your existing get() method

    if (responseData['appointments'] != null &&
        responseData['appointments'] is List) {
      return (responseData['appointments'] as List)
          .map((e) => Appointment.fromJson(e))
          .toList();
    } else if (responseData['message'] != null) {
      throw Exception(
        'Failed to load appointments: ${responseData['message']}',
      );
    } else {
      throw Exception('Failed to load appointments: Unknown error');
    }
  }
}
