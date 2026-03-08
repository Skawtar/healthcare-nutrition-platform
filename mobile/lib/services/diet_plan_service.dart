import 'dart:convert';
import 'package:flutter_application_1/models/diet_plan.dart';
import 'package:flutter_application_1/services/auth/api_service.dart';
import 'package:http/http.dart' as http;

class RegimeAlimentaireApi {
  static const String _baseUrl = 'http://localhost:8000/api';
  final ApiService apiService;

  RegimeAlimentaireApi({required this.apiService});

  Future<List<RegimeAlimentaire>> fetchDietsByPatient(int patientId) async {
    final token = await apiService.getAuthToken();
    final response = await http.get(
      Uri.parse('$_baseUrl/patients/$patientId/regimes-alimentaires'),
      headers: {
        'Accept': 'application/json',
        'Authorization': 'Bearer $token',
      },
    );

    if (response.statusCode == 200) {
      final Map<String, dynamic> responseData = json.decode(response.body);
      if (responseData['status'] == 'success' && responseData['data'] is List) {
        return (responseData['data'] as List)
            .map((json) => RegimeAlimentaire.fromJson(json))
            .toList();
      }
      throw Exception('Invalid API response format');
    } else {
      throw Exception('Failed to load diets. Status: ${response.statusCode}');
    }
  }
}