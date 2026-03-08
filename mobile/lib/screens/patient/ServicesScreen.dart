// lib/screens/patient/ServicesScreen.dart

import 'package:flutter/material.dart';
import 'package:flutter_application_1/models/service_model.dart';
import 'package:flutter_application_1/models/subscription_model.dart';
import 'package:flutter_application_1/services/subscription_api_service.dart'; // Import the new service
// You might still need ApiService for general patient data if not handled elsewhere
// import 'package:flutter_application_1/services/auth/api_service.dart';


class ServicesScreen extends StatefulWidget {
  const ServicesScreen({super.key});

  @override
  State<ServicesScreen> createState() => _ServicesScreenState();
}

class _ServicesScreenState extends State<ServicesScreen> {
  final SubscriptionApiService _subscriptionApiService = SubscriptionApiService(); // Use the new service
  List<Service> _availableServices = [];
  Subscription? _currentSubscription;
  bool _isLoading = true;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _fetchData();
  }

  Future<void> _fetchData() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final services = await _subscriptionApiService.fetchAvailableServices();
      final currentSubscription = await _subscriptionApiService.fetchCurrentSubscription();
      setState(() {
        _availableServices = services;
        _currentSubscription = currentSubscription;
      });
    } catch (e) {
      setState(() {
        _errorMessage = 'Failed to load data: ${e.toString()}';
      });
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  Future<void> _subscribe(String serviceId) async {
    setState(() {
      _isLoading = true; // Show loading indicator during action
    });
    try {
      await _subscriptionApiService.subscribeToService(serviceId);
      await _fetchData(); // Refresh data
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Subscribed successfully!')),
      );
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Failed to subscribe: ${e.toString()}')),
      );
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  Future<void> _cancelSubscription() async {
    setState(() {
      _isLoading = true; // Show loading indicator during action
    });
    try {
      await _subscriptionApiService.cancelSubscription();
      await _fetchData(); // Refresh data
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Subscription cancelled.')),
      );
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Failed to cancel subscription: ${e.toString()}')),
      );
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Our Services', style: TextStyle(color: Colors.white)),
        backgroundColor: const Color(0xFF87CEEB),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _errorMessage != null
              ? Center(child: Text(_errorMessage!))
              : RefreshIndicator(
                  onRefresh: _fetchData,
                  child: SingleChildScrollView(
                    padding: const EdgeInsets.all(16.0),
                    physics: const AlwaysScrollableScrollPhysics(), // Allow pull to refresh even if content is small
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        if (_currentSubscription != null && _currentSubscription!.isActive)
                          _buildCurrentSubscriptionCard(),
                        const SizedBox(height: 20),
                        Text(
                          'Available Plans',
                          style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                            fontWeight: FontWeight.bold,
                            color: const Color(0xFF4682B4),
                          ),
                        ),
                        const SizedBox(height: 15),
                        _availableServices.isEmpty
                            ? const Center(child: Text('No services available at the moment.'))
                            : ListView.builder(
                                shrinkWrap: true,
                                physics: const NeverScrollableScrollPhysics(), // Nested scroll view
                                itemCount: _availableServices.length,
                                itemBuilder: (context, index) {
                                  final service = _availableServices[index];
                                  return _buildServiceCard(service);
                                },
                              ),
                      ],
                    ),
                  ),
                ),
    );
  }

  Widget _buildCurrentSubscriptionCard() {
    return Card(
      elevation: 4,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(15)),
      color: Colors.lightBlue.shade50,
      child: Padding(
        padding: const EdgeInsets.all(20.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                const Icon(Icons.check_circle, color: Colors.green, size: 30),
                const SizedBox(width: 10),
                Text(
                  'Your Current Subscription',
                  style: Theme.of(context).textTheme.titleLarge?.copyWith(
                    fontWeight: FontWeight.bold,
                    color: const Color(0xFF4682B4),
                  ),
                ),
              ],
            ),
            const Divider(height: 20, thickness: 0.5),
            _buildInfoRow(
                Icons.medical_services, 'Plan Name', _currentSubscription!.serviceName),
            _buildInfoRow(Icons.calendar_today, 'Period', _currentSubscription!.formattedPeriod),
            _buildInfoRow(Icons.payments, 'Payment Method', _currentSubscription!.paymentMethod),
            _buildInfoRow(Icons.fiber_manual_record, 'Status', _currentSubscription!.status.name.toUpperCase(),
                valueColor: _currentSubscription!.status == SubscriptionStatus.active ? Colors.green : Colors.orange),
            if (_currentSubscription!.status == SubscriptionStatus.active)
              Padding(
                padding: const EdgeInsets.only(top: 15.0),
                child: Center(
                  child: ElevatedButton.icon(
                    onPressed: _cancelSubscription,
                    icon: const Icon(Icons.cancel),
                    label: const Text('Cancel Subscription'),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.red,
                      foregroundColor: Colors.white,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
                    ),
                  ),
                ),
              ),
          ],
        ),
      ),
    );
  }

  Widget _buildServiceCard(Service service) {
    final bool isCurrent = _currentSubscription?.serviceId == service.id && _currentSubscription!.isActive;
    return Card(
      elevation: 2,
      margin: const EdgeInsets.only(bottom: 15),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
      color: isCurrent ? Colors.blue.shade50 : Colors.white,
      child: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              service.name,
              style: Theme.of(context).textTheme.titleLarge?.copyWith(
                fontWeight: FontWeight.bold,
                color: isCurrent ? const Color(0xFF4682B4) : Colors.black87,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              service.description,
              style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                color: Colors.grey[700],
              ),
            ),
            const SizedBox(height: 8),
            Text(
              '${service.price.toStringAsFixed(2)} TND / ${service.billingPeriod.name}',
              style: Theme.of(context).textTheme.titleMedium?.copyWith(
                fontWeight: FontWeight.bold,
                color: const Color(0xFF2E8B57), // Sea Green
              ),
            ),
            if (service.features.isNotEmpty)
              Padding(
                padding: const EdgeInsets.only(top: 10.0),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: service.features
                      .map((feature) => Padding(
                    padding: const EdgeInsets.symmetric(vertical: 2.0),
                    child: Row(
                      children: [
                        Icon(Icons.check, size: 18, color: Colors.green[700]),
                        const SizedBox(width: 8),
                        Expanded(
                          child: Text(
                            feature,
                            style: Theme.of(context).textTheme.bodySmall,
                          ),
                        ),
                      ],
                    ),
                  ))
                      .toList(),
                ),
              ),
            const SizedBox(height: 15),
            Center(
              child: isCurrent
                  ? ElevatedButton.icon(
                      onPressed: null, // Disabled
                      icon: const Icon(Icons.check_circle_outline),
                      label: const Text('Current Plan'),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Colors.grey, // Grey it out
                        foregroundColor: Colors.white,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
                      ),
                    )
                  : ElevatedButton(
                      onPressed: () => _subscribe(service.id),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF4682B4), // Steel Blue
                        foregroundColor: Colors.white,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
                      ),
                      child: const Text('Subscribe Now'),
                    ),
            ),
          ],
        ),
      ),
    );
  }

  // Helper widget for information rows
  Widget _buildInfoRow(IconData icon, String label, String value, {Color? valueColor}) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4.0),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, color: const Color(0xFF87CEEB), size: 20),
          const SizedBox(width: 10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  label,
                  style: Theme.of(context).textTheme.bodySmall?.copyWith(
                    color: Colors.grey[600],
                    fontWeight: FontWeight.w500,
                  ),
                ),
                Text(
                  value,
                  style: Theme.of(context).textTheme.titleSmall?.copyWith(
                    fontWeight: FontWeight.bold,
                    color: valueColor ?? Colors.black87,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}