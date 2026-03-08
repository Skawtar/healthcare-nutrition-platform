import 'dart:async';
import 'dart:io';

import 'package:flutter_application_1/services/auth/api_service.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';

class NotificationsApi {
  final ApiService _apiService;

  NotificationsApi(this._apiService); // Initialize via constructor

  Future<String?> _getToken() async {
    return await _apiService.getAuthToken();
  }

  Future<Map<String, String>> _buildHeaders() async {
    final token = await _getToken();
    return {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      if (token != null) 'Authorization': 'Bearer $token',
    };
  }
Future<int> getUnreadNotificationCount() async {
  try {
    final headers = await _buildHeaders();
    final response = await http.get(
      Uri.parse('${_apiService.baseUrl}/notifications/unread/count'),
      headers: headers,
    ).timeout(Duration(seconds: 10));

    if (response.statusCode == 200) {
      return json.decode(response.body)['count'] ?? 0;
    } else {
      throw Exception('Failed to load count: ${response.statusCode}');
    }
  } catch (e) {
    throw Exception('Failed to load unread count: $e');
  }
}

Future<List<dynamic>> getNotifications() async {
  try {
    final headers = await _buildHeaders();
    final response = await http.get(
      Uri.parse('${_apiService.baseUrl}/notifications'),
      headers: headers,
    ).timeout(Duration(seconds: 10));

    if (response.statusCode == 200) {
      final Map<String, dynamic> responseData = json.decode(response.body);
      if (responseData['success'] == true) {
        return responseData['notifications'] ?? [];
      } else {
        throw Exception(responseData['message'] ?? 'Failed to load notifications');
      }
    } else {
      final errorData = json.decode(response.body);
      throw Exception(errorData['message'] ?? 'API Error: ${response.statusCode}');
    }
  } on SocketException {
    throw Exception('No internet connection');
  } on TimeoutException {
    throw Exception('Request timed out');
  } on FormatException {
    throw Exception('Invalid server response format');
  } catch (e) {
    throw Exception('Failed to load notifications: ${e.toString()}');
  }
}
  Future<bool> markNotificationAsRead(String notificationId) async {
    final headers = await _buildHeaders();
    if (headers['Authorization'] == null) return false;

    final response = await http.post(
      Uri.parse('${_apiService.baseUrl}/notifications/$notificationId/mark-as-read'),
      headers: headers,
    );

    return response.statusCode == 200;
  }

  Future<bool> markAllNotificationsAsRead() async {
    final headers = await _buildHeaders();
    if (headers['Authorization'] == null) return false;

    final response = await http.post(
      Uri.parse('${_apiService.baseUrl}/notifications/mark-all-as-read'),
      headers: headers,
    );

    return response.statusCode == 200;
  }
}