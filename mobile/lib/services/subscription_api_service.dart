// lib/services/subscription_api_service.dart

import 'package:flutter/foundation.dart'; // For debugPrint
import 'package:flutter_application_1/models/service_model.dart';
import 'package:flutter_application_1/models/subscription_model.dart';
import 'package:flutter_application_1/services/auth/api_service.dart';

class SubscriptionApiService {
  final ApiService _apiService = ApiService();

  Future<List<Service>> fetchAvailableServices() async {
    try {
      final responseData = await _apiService.get('/services');

      if (responseData['services'] != null) {
        return (responseData['services'] as List)
            .map((serviceJson) => Service.fromJson(serviceJson))
            .toList();
      } else {
        throw Exception(responseData['message'] ?? 'Failed to load services: Data format error.');
      }
    } catch (e) {
      debugPrint('Error in fetchAvailableServices: $e');
      rethrow;
    }
  }

  Future<Subscription?> fetchCurrentSubscription() async {
    try {
      final responseData = await _apiService.get('/patient/subscription');

      // If _apiService.get() returns a response body (even for non-2xx if not throwing)
      // and 'subscription' is null, it means no active subscription (valid scenario).
      // However, based on your logs, _apiService.get() for 404 is throwing.
      if (responseData['subscription'] != null) {
        return Subscription.fromJson(responseData['subscription']);
      } else {
        // This 'else' block might not be reached if a 404 always throws an exception.
        debugPrint('No active subscription found (API returned null in body, if no exception was thrown).');
        return null;
      }
    } catch (e) {
      debugPrint('Error in fetchCurrentSubscription: $e');
      // If the exception indicates "No active subscription" (e.g., from a 404),
      // we want to treat it as a successful "null subscription" rather than a hard error.
      if (e.toString().contains('API Error 404') && e.toString().contains('No active subscription')) {
        debugPrint('Caught 404 for no active subscription, returning null gracefully.');
        return null; // Return null to indicate no active subscription
      }
      rethrow; // Re-throw other types of exceptions (e.g., network error, 401, 500)
    }
  }

  Future<void> subscribeToService(String serviceId) async {
    try {
      final responseData = await _apiService.post(
        '/patient/subscribe',
        {'service_id': serviceId},
      );
      debugPrint('Subscription successful: $responseData');
    } catch (e) {
      debugPrint('Error in subscribeToService: $e');
      rethrow;
    }
  }

  Future<void> cancelSubscription() async {
    try {
      final responseData = await _apiService.delete('/patient/subscription', {});
      debugPrint('Subscription cancellation successful: $responseData');
    } catch (e) {
      debugPrint('Error in cancelSubscription: $e');
      rethrow;
    }
  }
}