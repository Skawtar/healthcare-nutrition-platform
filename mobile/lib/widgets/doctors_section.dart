import 'package:flutter/material.dart';
import 'doctor_card.dart';
import '../models/doctor.dart';

class DoctorsSection extends StatelessWidget {
  final String title;
  final List<Doctor> doctors;
  final VoidCallback onViewAll;
  final bool isLoading;
  final String? errorMessage;
  final VoidCallback onRetry;

  const DoctorsSection({
    Key? key,
    required this.title,
    required this.doctors,
    required this.onViewAll,
    this.isLoading = false,
    this.errorMessage,
    required this.onRetry,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // Section Header
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text(
              title,
              style: const TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.bold,
                color: Color(0xFF4682B4),
              ),
            ),
            TextButton(
              onPressed: onViewAll,
              child: const Text(
                'View All',
                style: TextStyle(color: Color(0xFF87CEEB)),
              ),
            ),
          ],
        ),
        const SizedBox(height: 12),
        
        // Content
        if (isLoading)
          _buildLoadingIndicator()
        else if (errorMessage != null)
          _buildErrorWidget()
        else if (doctors.isEmpty)
          _buildEmptyState()
        else
          _buildDoctorsList(),
      ],
    );
  }

  Widget _buildLoadingIndicator() {
    return SizedBox(
      height: 180,
      child: Center(
        child: CircularProgressIndicator(
          color: Color(0xFF87CEEB),
        ),
      ),
    );
  }

Widget _buildErrorWidget() {
  String errorText = errorMessage ?? 'Unknown error occurred';
  // Clean up the error message
  errorText = errorText.replaceAll('Exception: ', '');
  errorText = errorText.length > 100 
      ? '${errorText.substring(0, 100)}...' 
      : errorText;

  return Column(
    children: [
      Container(
        height: 150,
        decoration: BoxDecoration(
          color: Colors.grey[100],
          borderRadius: BorderRadius.circular(12),
        ),
        child: Center(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Icon(Icons.error_outline, color: Colors.red, size: 40),
              const SizedBox(height: 8),
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 16),
                child: Text(
                  errorText,
                  textAlign: TextAlign.center,
                  style: const TextStyle(color: Colors.red),
                ),
              ),
            ],
          ),
        ),
      ),
      const SizedBox(height: 8),
      ElevatedButton(
        style: ElevatedButton.styleFrom(
          backgroundColor: const Color(0xFF87CEEB),
        ),
        onPressed: onRetry,
        child: const Text('Retry', style: TextStyle(color: Colors.white)),
      ),
    ],
  );
}
  Widget _buildEmptyState() {
    return Container(
      height: 150,
      decoration: BoxDecoration(
        color: Colors.grey[100],
        borderRadius: BorderRadius.circular(12),
      ),
      child: Center(
        child: Text(
          'No doctors available',
          style: TextStyle(color: Colors.grey[600]),
        ),
      ),
    );
  }

  Widget _buildDoctorsList() {
    return SizedBox(
      height: 180,
      child: ListView.builder(
        scrollDirection: Axis.horizontal,
        itemCount: doctors.length,
        itemBuilder: (context, index) {
          return Container(
            width: 160,
            margin: EdgeInsets.only(
              right: index == doctors.length - 1 ? 0 : 16,
            ),
            child: DoctorCard(
              doctor: doctors[index],
              onTap: () {
                // Handle doctor tap if needed
              },
            ),
          );
        },
      ),
    );
  }
}