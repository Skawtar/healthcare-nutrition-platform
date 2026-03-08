import 'package:intl/intl.dart';
import 'package:flutter/foundation.dart'; // For debugPrint in enums if needed

enum SubscriptionStatus { active, cancelled, expired, pending, unknown }

// Helper function to parse string to enum
SubscriptionStatus _parseSubscriptionStatus(String? statusString) {
  switch (statusString?.toLowerCase()) {
    case 'active':
      return SubscriptionStatus.active;
    case 'cancelled':
      return SubscriptionStatus.cancelled;
    case 'expired':
      return SubscriptionStatus.expired;
    case 'pending':
      return SubscriptionStatus.pending;
    default:
      debugPrint('Unknown subscription status: $statusString'); // Log unknown values
      return SubscriptionStatus.unknown;
  }
}

class Subscription {
  final String id;
  final String serviceId;
  final String serviceName;
  final SubscriptionStatus status; // Changed to enum
  final DateTime startDate;
  final DateTime endDate;
  final String paymentMethod;

  Subscription({
    required this.id,
    required this.serviceId,
    required this.serviceName,
    required this.status,
    required this.startDate,
    required this.endDate,
    required this.paymentMethod,
  });

  factory Subscription.fromJson(Map<String, dynamic> json) {
    return Subscription(
      id: json['id'].toString(), // Ensure string type
      serviceId: json['service_id'].toString(), // Ensure string type
      serviceName: json['service_name'] as String,
      status: _parseSubscriptionStatus(json['status']), // Use helper
      startDate: DateTime.parse(json['start_date'] as String),
      endDate: DateTime.parse(json['end_date'] as String),
      paymentMethod: json['payment_method'] as String,
    );
  }

  String get formattedPeriod {
    final format = DateFormat('MMM d, y');
    return '${format.format(startDate)} - ${format.format(endDate)}';
  }

  bool get isActive => status == SubscriptionStatus.active && endDate.isAfter(DateTime.now());
}