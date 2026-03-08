import 'package:flutter/material.dart';
import 'package:flutter_application_1/models/diet_plan.dart';
import 'package:flutter_application_1/services/auth/api_service.dart';
import 'package:flutter_application_1/services/diet_plan_service.dart';
import 'package:intl/intl.dart';
// Import the new screen you'll create
import 'package:flutter_application_1/screens/food_search_screen.dart'; // <--- NEW IMPORT

class PatientDietScreen extends StatefulWidget {
  final int patientId;

  const PatientDietScreen({super.key, required this.patientId});

  @override
  State<PatientDietScreen> createState() => _PatientDietScreenState();
}

class _PatientDietScreenState extends State<PatientDietScreen> {
  late Future<List<RegimeAlimentaire>> _futureDiets;
  late final RegimeAlimentaireApi _apiService;

  @override
  void initState() {
    super.initState();
    _apiService = RegimeAlimentaireApi(apiService: ApiService());
    _loadDiets();
  }

  Future<void> _loadDiets() async {
    try {
      setState(() {
        _futureDiets = _apiService.fetchDietsByPatient(widget.patientId);
      });
    } catch (e) {
      setState(() {
        _futureDiets = Future.error(e.toString());
      });
    }
  }

  Future<void> _refreshDiets() async {
    await _loadDiets();
  }

  // --- NEW METHOD TO NAVIGATE TO FOOD SEARCH SCREEN ---
  void _navigateToFoodSearchScreen() {
    Navigator.of(context).push(
      MaterialPageRoute(
        builder: (context) => const FoodSearchScreen(), // Navigate to your new screen
      ),
    );
  }
  // ---------------------------------------------------

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
title: const Text(
  'Patient Dietary Regimens',
  style: TextStyle(color: Colors.white), // <-- Make title text white
),

        backgroundColor: Color(0xFF87CEEB),
        elevation: 4,
        actions: [
          // Existing refresh button (optional, you can keep or remove it)
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _refreshDiets,
            tooltip: 'Refresh Diets',
            color: Color.fromARGB(255, 255, 255, 255),

          ),
          // --- NEW BUTTON FOR FOOD SEARCH ---
          IconButton(
            icon: const Icon(Icons.fastfood), // Changed icon
            onPressed: _navigateToFoodSearchScreen, // New onPressed handler
            tooltip: 'Search Food Items', // New tooltip
            color: Color.fromARGB(234, 255, 255, 255),

          ),
          // ---------------------------------
        ],
      ),
      body: RefreshIndicator(
        onRefresh: _refreshDiets,
        child: Padding(
          padding: const EdgeInsets.all(16.0),
          child: FutureBuilder<List<RegimeAlimentaire>>(
            future: _futureDiets,
            builder: (context, snapshot) {
              if (snapshot.connectionState == ConnectionState.waiting) {
                return const Center(
                  child: CircularProgressIndicator(
                    strokeWidth: 2.0,
                    valueColor: AlwaysStoppedAnimation<Color>(Colors.blue),
                  ),
                );
              }

              if (snapshot.hasError) {
                return _buildErrorState(snapshot.error.toString());
              }

              if (!snapshot.hasData || snapshot.data!.isEmpty) {
                return _buildEmptyState();
              }

              return _buildDietList(snapshot.data!);
            },
          ),
        ),
      ),
    );
  }

  Widget _buildDietList(List<RegimeAlimentaire> diets) {
    return ListView.separated(
      itemCount: diets.length,
      separatorBuilder: (context, index) => const SizedBox(height: 12),
      itemBuilder: (context, index) {
        final regime = diets[index];
        return _buildDietCard(regime);
      },
    );
  }

  Widget _buildDietCard(RegimeAlimentaire regime) {
    return Card(
      elevation: 4,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(12),
        
      ),
      color: Colors.white, 

      child: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  'Dietary Regimen #${regime.id}',
                  style: TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                    color: Colors.blue.shade800,
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 12,
                    vertical: 6,
                  ),
                  decoration: BoxDecoration(
                    color: regime.statusColorValue.withOpacity(0.2),
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(
                      color: regime.statusColorValue,
                      width: 1,
                    ),
                  ),
                  child: Text(
                    regime.statusDisplay,
                    style: TextStyle(
                      color: regime.statusColorValue,
                      fontWeight: FontWeight.bold,
                      fontSize: 12,
                    ),
                  ),
                ),
              ],
            ),
            const Divider(height: 20, thickness: 1),
            _buildInfoRow('Prescribed by:', regime.doctorName, Icons.medical_services),
            _buildInfoRow('Prescription Date:', regime.formattedPrescriptionDate, Icons.calendar_today),
            _buildInfoRow('Expiration Date:', regime.formattedExpirationDate, Icons.calendar_month),
            _buildInfoRow('Daily Calories:', regime.formattedCalories, Icons.local_fire_department),
            
            const SizedBox(height: 12),
            _buildSectionTitle('Restrictions:'),
            regime.restrictions.isNotEmpty
                ? Wrap(
                    spacing: 8.0,
                    runSpacing: 4.0,
                    children: regime.restrictions
                        .map((r) => Chip(
                              label: Text(r),
                              backgroundColor: Colors.red.shade100,
                              labelStyle: TextStyle(
                                color: Colors.red.shade800),
                            ))
                        .toList(),
                  )
                : _buildNoContentText('No specific restrictions'),
            
            const SizedBox(height: 12),
            _buildSectionTitle('Recommendations:'),
            Text(
              regime.recommandations.isNotEmpty 
                  ? regime.recommandations 
                  : 'No specific recommendations',
              style: const TextStyle(
                fontSize: 15, 
                color: Colors.black87,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildInfoRow(String label, String value, IconData icon) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 6.0),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, size: 20, color: Colors.blue.shade600),
          const SizedBox(width: 10),
          Expanded(
            child: RichText(
              text: TextSpan(
                children: [
                  TextSpan(
                    text: '$label ',
                    style: TextStyle(
                      fontWeight: FontWeight.bold,
                      fontSize: 15,
                      color: Colors.grey.shade800,
                    ),
                  ),
                  TextSpan(
                    text: value,
                    style: const TextStyle(
                      fontSize: 15,
                      color: Colors.black87,
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSectionTitle(String title) {
    return Text(
      title,
      style: TextStyle(
        fontSize: 16,
        fontWeight: FontWeight.bold,
        color: Colors.grey.shade700,
      ),
    );
  }

  Widget _buildNoContentText(String text) {
    return Text(
      text,
      style: const TextStyle(
        color: Colors.grey,
        fontStyle: FontStyle.italic,
      ),
    );
  }

  Widget _buildErrorState(String error) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          const Icon(Icons.error_outline, color: Colors.red, size: 60),
          const SizedBox(height: 16),
          Text(
            'Failed to load dietary regimens',
            style: TextStyle(
              fontSize: 18,
              color: Colors.grey.shade700,
            ),
          ),
          const SizedBox(height: 8),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 32.0),
            child: Text(
              error,
              textAlign: TextAlign.center,
              style: const TextStyle(
                fontSize: 14, 
                color: Colors.red,
              ),
            ),
          ),
          const SizedBox(height: 20),
          ElevatedButton.icon(
            onPressed: _refreshDiets,
            icon: const Icon(Icons.refresh),
            label: const Text('Try Again'),
            style: ElevatedButton.styleFrom(
              foregroundColor: Colors.white,
              backgroundColor: Colors.blue,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(10),
              ),
              padding: const EdgeInsets.symmetric(
                horizontal: 20, 
                vertical: 12,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(
            Icons.info_outline,
            color: Colors.blue.shade300,
            size: 60,
          ),
          const SizedBox(height: 16),
          const Text(
            'No dietary regimens found',
            style: TextStyle(
              fontSize: 18,
              color: Colors.grey,
            ),
          ),
          const SizedBox(height: 8),
          const Text(
            'This patient currently has no dietary plans',
            style: TextStyle(
              fontSize: 14,
              color: Colors.grey,
            ),
          ),
          const SizedBox(height: 20),
          ElevatedButton.icon(
            onPressed: _refreshDiets,
            icon: const Icon(Icons.refresh),
            label: const Text('Refresh'),
            style: ElevatedButton.styleFrom(
              foregroundColor: Colors.white,
              backgroundColor: Colors.blue,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(10),
              ),
              padding: const EdgeInsets.symmetric(
                horizontal: 20, 
                vertical: 12,
              ),
            ), 
          ),
        ],
      ),
    );
  }
}