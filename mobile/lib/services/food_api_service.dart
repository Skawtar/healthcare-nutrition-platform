import 'dart:convert';
import 'package:flutter_application_1/models/FoodProduct.dart';
import 'package:http/http.dart' as http;

class FoodApiService {
  static const String _baseUrl = 'https://world.openfoodfacts.org';

  Future<List<FoodProduct>> searchFoodProducts({
    required String query,
    int page = 1,
    int pageSize = 20, // Open Food Facts default is around 20-24
  }) async {
    if (query.isEmpty) {
      return []; // Return empty list if query is empty
    }

    final String url = '$_baseUrl/cgi/search.pl?'
        'search_terms=${Uri.encodeQueryComponent(query)}'
        '&search_simple=1'
        '&action=process'
        '&json=1'
        '&page=$page'
        '&page_size=$pageSize'; // You can specify page size

    try {
      final response = await http.get(Uri.parse(url));

      if (response.statusCode == 200) {
        final Map<String, dynamic> data = json.decode(response.body);
        final List<dynamic> productsJson = data['products'] ?? [];

        return productsJson.map((json) => FoodProduct.fromJson(json)).toList();
      } else {
        throw Exception('Failed to load products: HTTP ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('An error occurred while fetching food products: $e');
    }
  }

  /// Fetches a single product by its barcode.
  Future<FoodProduct?> getProductByBarcode(String barcode) async {
    if (barcode.isEmpty) {
      return null;
    }

    final String url = '$_baseUrl/api/v0/product/$barcode.json';

    try {
      final response = await http.get(Uri.parse(url));

      if (response.statusCode == 200) {
        final Map<String, dynamic> data = json.decode(response.body);
        if (data['status'] == 1) { // status 1 means product found
          return FoodProduct.fromJson(data['product']);
        } else {
          return null; // Product not found
        }
      } else {
        throw Exception('Failed to load product by barcode: HTTP ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('An error occurred while fetching product by barcode: $e');
    }
  }
}