import 'package:flutter/foundation.dart'; // For debugPrint in enums if needed

enum BillingPeriod {
  monthly,
  annually,
  quarterly,
  yearly, // <--- ADDED 'yearly' here
  unknown
}

// Helper function to parse string to enum
BillingPeriod _parseBillingPeriod(String? period) {
  switch (period?.toLowerCase()) {
    case 'monthly':
      return BillingPeriod.monthly;
    case 'annually':
      return BillingPeriod.annually;
    case 'quarterly':
      return BillingPeriod.quarterly;
    case 'yearly': // <--- ADDED this case to recognize "yearly" from backend
      return BillingPeriod.yearly;
    default:
      debugPrint('Unknown billing period: $period'); // Log unknown values
      return BillingPeriod.unknown;
  }
}

class Service {
  final String id;
  final String name;
  final String description;
  final double price;
  final BillingPeriod billingPeriod; // Changed to enum
  final List<String> features;

  Service({
    required this.id,
    required this.name,
    required this.description,
    required this.price,
    required this.billingPeriod,
    required this.features,
  });

  factory Service.fromJson(Map<String, dynamic> json) {
    return Service(
      id: json['id'].toString(), // Ensure string type
      name: json['name'] as String,
      description: json['description'] as String,
      price: double.parse(json['price'].toString()),
      billingPeriod: _parseBillingPeriod(json['billing_period']), // Use helper
      features: List<String>.from(json['features'] ?? []),
    );
  }
}