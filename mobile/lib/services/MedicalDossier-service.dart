import 'dart:convert';
import 'dart:io';
import 'package:flutter/material.dart';
import 'package:flutter_application_1/models/medical_dossier.dart';
import 'package:flutter_application_1/services/auth/api_service.dart';
import 'package:http/http.dart' as http;

class MedicalDossierService {
  static const String _baseUrl = 'http://127.0.0.1:8000/api';
  final ApiService _apiService;

  MedicalDossierService({required ApiService apiService})
      : _apiService = apiService;

  Future<MedicalDossier> createMedicalDossier({
    required double poids,
    required double taille,
    String? groupeSanguin,
    String? allergies,
    String? antecedents,
    String? traitements,
    DateTime? derniereConsultation,
  }) async {
    final token = await _apiService.getAuthToken();
    if (token == null) throw Exception('Authentication required');

    try {
      if (poids <= 0 || poids > 300) {
        throw Exception('Invalid weight (0-300 kg)');
      }
      if (taille <= 0 || taille > 250) {
        throw Exception('Invalid height (0-250 cm)');
      }

      final response = await http.post(
        // CORRECTED URL for POST: /patient/medical-dossier
        Uri.parse('$_baseUrl/patient/medical-dossier'),
        headers: _buildHeaders(token),
        body: json.encode(
          _buildRequestBody(
            poids: poids,
            taille: taille,
            groupeSanguin: groupeSanguin,
            allergies: allergies,
            antecedents: antecedents,
            traitements: traitements,
            derniereConsultation: derniereConsultation,
          ),
        ),
      );

      return _handleResponse(response);
    } on SocketException {
      throw Exception('No internet connection. Please check your network.');
    } on FormatException {
      throw Exception('Invalid server response format.');
    } catch (e) {
      debugPrint('Create medical dossier error: $e');
      rethrow;
    }
  }

  // NOTE: This method `fetchMedicalDossier(int patientId)` might not align with your current backend routes.
  // The backend GET route `/patient/medical-dossier` does not seem to take a patient ID in the URL.
  // It typically fetches the medical dossier for the *authenticated* patient.
  // If you need to fetch a specific patient's dossier by ID (e.g., for an admin panel),
  // you'll need a separate backend route like `Route::get('/medical-dossiers/{id}', ...);`
  Future<MedicalDossier> fetchMedicalDossier(int patientId) async {
    final token = await _apiService.getAuthToken();
    if (token == null) throw Exception('Authentication required');

    try {
      // Current Laravel routes do not support /patient/medical-dossier/{id} for GET
      // Re-evaluate if this method is needed, or if backend route should be added.
      // For now, it remains as is, but will likely fail unless the backend changes.
      final response = await http.get(
        Uri.parse('$_baseUrl/medical-dossiers/$patientId'),
        headers: _buildHeaders(token),
      );

      return _handleResponse(response);
    } on SocketException {
      throw Exception('No internet connection. Please check your network.');
    } on FormatException {
      throw Exception('Invalid server response format.');
    } catch (e) {
      debugPrint('Fetch medical dossier error: $e');
      rethrow;
    }
  }

  Future<MedicalDossier> updateMedicalDossier({
    required int id, // 'id' might be sent in the body if URL doesn't include it
    double? poids,
    double? taille,
    String? groupeSanguin,
    String? allergies,
    String? antecedents,
    String? traitements,
    DateTime? derniereConsultation,
  }) async {
    final token = await _apiService.getAuthToken();
    if (token == null) throw Exception('Authentication required');

    try {
      if (poids != null && (poids <= 0 || poids > 300)) {
        throw Exception('Invalid weight (0-300 kg)');
      }
      if (taille != null && (taille <= 0 || taille > 250)) {
        throw Exception('Invalid height (0-250 cm)');
      }

      final response = await http.post(
        // CORRECTED URL for PUT: /patient/medical-dossier
        // Assuming 'storeOrUpdate' on the backend handles PUT for the authenticated user's dossier
        // without the ID in the URL. If the backend expects ID in URL, change this.
        Uri.parse('$_baseUrl/patient/medical-dossier'),
        headers: _buildHeaders(token),
        body: json.encode(
          _buildRequestBody(
            poids: poids,
            taille: taille,
            groupeSanguin: groupeSanguin,
            allergies: allergies,
            antecedents: antecedents,
            traitements: traitements,
            derniereConsultation: derniereConsultation,
          )..['id'] = id, // Add ID to the body if the backend needs it for update
        ),
      );

      return _handleResponse(response);
    } on SocketException {
      throw Exception('No internet connection. Please check your network.');
    } on FormatException {
      throw Exception('Invalid server response format.');
    } catch (e) {
      debugPrint('Update medical dossier error: $e');
      rethrow;
    }
  }

  Map<String, dynamic> _buildRequestBody({
    double? poids,
    double? taille,
    String? groupeSanguin,
    String? allergies,
    String? antecedents,
    String? traitements,
    DateTime? derniereConsultation,
  }) {
    return {
      if (poids != null) 'poids': poids,
      if (taille != null) 'taille': taille,
      if (groupeSanguin != null) 'groupe_sanguin': groupeSanguin,
      if (allergies != null) 'allergies': allergies,
      if (antecedents != null) 'antecedents': antecedents,
      if (traitements != null) 'traitements': traitements,
      if (derniereConsultation != null)
        'derniere_consultation': derniereConsultation.toIso8601String(),
    };
  }

  Map<String, String> _buildHeaders(String token) {
    return {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      'Authorization': 'Bearer $token',
    };
  }

MedicalDossier _handleResponse(http.Response response) {
  debugPrint('Response status: ${response.statusCode}');
  debugPrint('Response body: ${response.body}');

  if (response.statusCode >= 200 && response.statusCode < 300) {
    final responseData = json.decode(response.body);

    // Check if the API response contains a 'dossier' key
    if (responseData['dossier'] != null) {
      return MedicalDossier.fromJson(responseData['dossier']); // Parse 'dossier' key
    } else {
      // If 200 OK but 'dossier' data is missing or unexpected structure
      throw Exception(responseData['message'] ?? 'Medical dossier data not found in response');
    }
  } else {
    // Handles non-2xx status codes (e.g., 404, 500)
    final errorData = json.decode(response.body);
    throw Exception(
      errorData['message'] ??
          errorData['error'] ??
          'Request failed with status ${response.statusCode}',
    );
  }
}
  Future<MedicalDossier?> getMedicalDossier() async {
    final token = await _apiService.getAuthToken();
    if (token == null) throw Exception('Authentication required');
    try {
      final response = await http.get(
        // CORRECTED URL for GET: /patient/medical-dossier
        Uri.parse('$_baseUrl/patient/medical-dossier'),
        headers: _buildHeaders(token),
      );
      return _handleResponse(response);
    } on SocketException {
      throw Exception('No internet connection. Please check your network.');
    } on FormatException {
      throw Exception('Invalid server response format.');
    } catch (e) {
      debugPrint('Get medical dossier error: $e');
      rethrow;
    }
  }
}