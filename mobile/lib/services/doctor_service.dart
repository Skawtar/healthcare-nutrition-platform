import 'dart:async';
import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:flutter_application_1/models/doctor.dart';
import 'package:flutter_application_1/services/auth/api_service.dart';
import 'package:http/http.dart' as http;

class DoctorService {
  final ApiService apiService;
  // Make sure this matches your Laravel API base URL and the 'medecins' endpoint
  static const String _baseUrl = 'http://127.0.0.1:8000/api';
  static const String _doctorsEndpoint = '/doctors'; 

  DoctorService(this.apiService);

  Future<List<Doctor>> fetchDoctors({int page = 1}) async {
    final token = await apiService.getAuthToken();
    if (token == null) throw Exception('Authentication required');

    try {
      final response = await http.get(
        // Use the corrected endpoint
        Uri.parse('$_baseUrl$_doctorsEndpoint?page=$page'),
        headers: {
          'Accept': 'application/json',
          'Authorization': 'Bearer $token',
        },
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body) as Map<String, dynamic>;

        if (data['success'] == true) {
          // Extract the doctors list from the paginated response
          // Your backend returns: { "success": true, "data": { "data": [ ... doctors ... ] } }
          final doctorsData = data['data']['data'] as List;
          return doctorsData.map((doctorJson) => Doctor.fromJson(doctorJson)).toList();
        }
        throw Exception(data['message'] ?? 'Invalid response format or success is false');
      } else {
        throw Exception('Failed to load doctors with status ${response.statusCode}: ${response.body}');
      }
    } catch (e) {
      debugPrint('Error fetching doctors: $e');
      throw Exception('Could not fetch doctors. Please try again. Error: ${e.toString()}');
    }
  }

  Future<Doctor> fetchDoctorById(int doctorId) async {
    final token = await apiService.getAuthToken();
    if (token == null) {
      throw Exception('Authentication required - Please login again');
    }

    try {
      final response = await http.get(
        // Use the corrected endpoint
        Uri.parse('$_baseUrl$_doctorsEndpoint/$doctorId'),
        headers: {
          'Accept': 'application/json',
          'Authorization': 'Bearer $token',
        },
      ).timeout(const Duration(seconds: 30));

      if (response.statusCode == 200) {
        final data = json.decode(response.body) as Map<String, dynamic>;

        if (data['success'] == true) {
          // Your backend returns: { "success": true, "data": { ... single doctor ... } }
          return Doctor.fromJson(data['data']);
        } else {
          throw Exception(data['message'] ?? 'Invalid response format or success is false');
        }
      } else if (response.statusCode == 404) {
        throw Exception('Doctor not found');
      } else if (response.statusCode == 401) {
        await apiService.clearAuthToken();
        throw Exception('Session expired. Please login again.');
      } else {
        throw Exception('Failed to load doctor details with status ${response.statusCode}: ${response.body}');
      }
    } on http.ClientException catch (e) {
      throw Exception('Network error: ${e.message}');
    } on TimeoutException {
      throw Exception('Request timed out. Please try again.');
    } catch (e) {
      throw Exception('Failed to fetch doctor: ${e.toString()}');
    }
  }
}