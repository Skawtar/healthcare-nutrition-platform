import 'package:flutter/material.dart';
import 'package:flutter_application_1/models/FoodProduct.dart';
// http and dart:convert are no longer directly used in this file
// as the service encapsulates them.
// import 'package:http/http.dart' as http;
// import 'dart:convert';
// url_launcher is no longer directly used here since there's no tap action
// import 'package:url_launcher/url_launcher.dart';

// Import your new FoodApiService
import 'package:flutter_application_1/services/food_api_service.dart';

class FoodSearchScreen extends StatefulWidget {
  const FoodSearchScreen({super.key});

  @override
  State<FoodSearchScreen> createState() => _FoodSearchScreenState();
}

class _FoodSearchScreenState extends State<FoodSearchScreen> {
  final TextEditingController _searchController = TextEditingController();
  List<FoodProduct> _searchResults = [];
  bool _isLoading = false;
  String? _errorMessage;
  String _currentQuery = '';
  int _currentPage = 1;
  bool _hasMore = true;
  final ScrollController _scrollController = ScrollController();

  late final FoodApiService _foodApiService; // Declare the service

  @override
  void initState() {
    super.initState();
    _foodApiService = FoodApiService(); // Initialize the service
    _scrollController.addListener(_onScroll);
  }

  @override
  void dispose() {
    _searchController.dispose();
    _scrollController.dispose();
    super.dispose();
  }

  void _onScroll() {
    if (_scrollController.position.pixels ==
            _scrollController.position.maxScrollExtent &&
        _hasMore &&
        !_isLoading) {
      _fetchFoodProducts(loadMore: true);
    }
  }

  Future<void> _fetchFoodProducts({bool loadMore = false}) async {
    if (_searchController.text.isEmpty && !loadMore) {
      setState(() {
        _searchResults = [];
        _errorMessage = null;
        _isLoading = false;
        _currentPage = 1;
        _hasMore = true;
      });
      return;
    }

    if (!loadMore) {
      _currentPage = 1;
      _searchResults = [];
      _currentQuery = _searchController.text;
    } else {
      _currentPage++;
    }

    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    final String query = _currentQuery.trim();
    if (query.isEmpty) {
      setState(() {
        _isLoading = false;
        _errorMessage = "Please enter a search term.";
      });
      return;
    }

    try {
      // Use the service to fetch products
      final List<FoodProduct> products =
          await _foodApiService.searchFoodProducts(query: query, page: _currentPage);

      setState(() {
        if (loadMore) {
          _searchResults.addAll(products);
        } else {
          _searchResults = products;
        }
        _hasMore = products.isNotEmpty;
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _errorMessage = "An error occurred: ${e.toString()}";
        _isLoading = false;
      });
    }
  }

  // Removed _launchUrl function as it's no longer needed for card taps.
  // If you decide to add other external links, you might reintroduce it.

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text(
          'Search Food Products',
          style: TextStyle(color: Colors.white),
        ),
        backgroundColor: const Color(0xFF87CEEB), // Sky Blue
        elevation: 4,
      ),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.all(8.0),
            child: TextField(
              controller: _searchController,
              decoration: InputDecoration(
                labelText: 'Search for food (e.g., "apple", "milk")',
                suffixIcon: IconButton(
                  icon: const Icon(Icons.search),
                  onPressed: () => _fetchFoodProducts(),
                ),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(10),
                ),
                filled: true,
                fillColor: const Color.fromARGB(255, 255, 255, 255),
              ),
              onSubmitted: (value) => _fetchFoodProducts(),
            ),
          ),
          if (_isLoading && _searchResults.isEmpty)
            const Expanded(
              child: Center(
                child: CircularProgressIndicator(
                  strokeWidth: 2.0,
                  valueColor: AlwaysStoppedAnimation<Color>(Colors.blue),
                ),
              ),
            )
          else if (_errorMessage != null)
            Expanded(
              child: Center(
                child: Padding(
                  padding: const EdgeInsets.all(16.0),
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const Icon(
                        Icons.error_outline,
                        color: Colors.red,
                        size: 60,
                      ),
                      const SizedBox(height: 16),
                      Text(
                        'Error: $_errorMessage',
                        textAlign: TextAlign.center,
                        style: const TextStyle(color: Colors.red, fontSize: 16),
                      ),
                      const SizedBox(height: 16),
                      ElevatedButton.icon(
                        onPressed: () {
                          if (_searchController.text.isNotEmpty) {
                            _fetchFoodProducts();
                          } else {
                            ScaffoldMessenger.of(context).showSnackBar(
                              const SnackBar(
                                content: Text(
                                  'Please enter a search term to try again.',
                                ),
                              ),
                            );
                          }
                        },
                        icon: const Icon(Icons.refresh),
                        label: const Text('Try Again'),
                      ),
                    ],
                  ),
                ),
              ),
            )
          else if (_searchResults.isEmpty &&
              _searchController.text.isNotEmpty &&
              !_isLoading)
            const Expanded(
              child: Center(
                child: Text(
                  'No products found for your search.',
                  style: TextStyle(fontSize: 16, color: Colors.grey),
                ),
              ),
            )
          else if (_searchResults.isEmpty &&
              _searchController.text.isEmpty &&
              !_isLoading)
            const Expanded(
              child: Center(
                child: Text(
                  'Start typing to search for food products...',
                  style: TextStyle(fontSize: 16, color: Colors.grey),
                ),
              ),
            )
          else
            Expanded(
              child: ListView.builder(
                controller: _scrollController,
                itemCount: _searchResults.length + (_isLoading ? 1 : 0),
                itemBuilder: (context, index) {
                  if (index == _searchResults.length) {
                    return const Center(
                      child: Padding(
                        padding: EdgeInsets.all(8.0),
                        child: CircularProgressIndicator(),
                      ),
                    );
                  }
                  final product = _searchResults[index];
                  final productName = product.name;
                  final brands = product.brand;
                  final imageUrl = product.imageUrl;
                  final calories =
                      product.energyKcalPer100g?.toStringAsFixed(0) ?? 'N/A';
                  // The 'url' variable is no longer needed since it's not used for onTap
                  // final url = product.productUrl;

                  return Card(
                    margin: const EdgeInsets.symmetric(
                      horizontal: 10,
                      vertical: 6,
                    ),
                    elevation: 3,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(10),
                    ),
                    color: Colors.white,
                    // No InkWell or GestureDetector means no tap functionality
                    child: Padding(
                      padding: const EdgeInsets.all(12.0),
                      child: Row(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          if (imageUrl != null)
                            ClipRRect(
                              borderRadius: BorderRadius.circular(8.0),
                              child: Image.network(
                                imageUrl,
                                width: 80,
                                height: 80,
                                fit: BoxFit.cover,
                                errorBuilder: (context, error, stackTrace) =>
                                    Container(
                                  width: 80,
                                  height: 80,
                                  color: Colors.grey[200],
                                  child: const Icon(
                                    Icons.broken_image,
                                    color: Colors.grey,
                                  ),
                                ),
                              ),
                            )
                          else
                            Container(
                              width: 80,
                              height: 80,
                              decoration: BoxDecoration(
                                color: Colors.grey[200],
                                borderRadius: BorderRadius.circular(8.0),
                              ),
                              child: const Icon(
                                Icons.fastfood,
                                color: Colors.grey,
                              ),
                            ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  productName,
                                  style: const TextStyle(
                                    fontWeight: FontWeight.bold,
                                    fontSize: 16,
                                  ),
                                  maxLines: 2,
                                  overflow: TextOverflow.ellipsis,
                                ),
                                const SizedBox(height: 4),
                                Text(
                                  brands ?? 'N/A',
                                  style: TextStyle(
                                    color: Colors.grey[600],
                                    fontSize: 13,
                                  ),
                                ),
                                const SizedBox(height: 8),
                                if (calories != 'N/A')
                                  Row(
                                    children: [
                                      Icon(
                                        Icons.local_fire_department,
                                        size: 16,
                                        color: Colors.orange[700],
                                      ),
                                      const SizedBox(width: 4),
                                      Text(
                                        '$calories kcal / 100g',
                                        style: const TextStyle(
                                          fontSize: 14,
                                          fontWeight: FontWeight.w500,
                                        ),
                                      ),
                                    ],
                                  ),
                              ],
                            ),
                          ),
                        ],
                      ),
                    ),
                  );
                },
              ),
            ),
        ],
      ),
    );
  }
}