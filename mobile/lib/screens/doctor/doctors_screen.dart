import 'package:flutter/material.dart';
import 'package:flutter_application_1/models/doctor.dart';
import 'package:flutter_application_1/services/doctor_service.dart';
import 'package:flutter_application_1/services/auth/api_service.dart';
import 'package:flutter_application_1/widgets/doctor_card.dart';
import 'package:flutter_application_1/screens/doctor/doctor_detail_screen.dart';

class DoctorsScreen extends StatefulWidget {
  const DoctorsScreen({super.key});

  @override
  State<DoctorsScreen> createState() => _DoctorsScreenState();
}

class _DoctorsScreenState extends State<DoctorsScreen> {
  final DoctorService _doctorService = DoctorService(ApiService());
  final TextEditingController _searchController = TextEditingController();
  final ScrollController _scrollController = ScrollController();

  List<Doctor> _allDoctors = [];
  List<Doctor> _filteredDoctors = [];

  // Pagination variables
  int _currentPage = 1;
  bool _hasMore = true;
  bool _isLoading = false;
  bool _isLoadingMore = false;
  String? _errorMessage;

  // Filter variables
  String? _selectedSpeciality;
  String? _selectedCity;
  double? _minFee;
  double? _maxFee;

  List<String> _availableSpecialities = [];
  List<String> _availableCities = [];
  final List<double> _feeRanges = [0, 50, 100, 150, 200, 250, 300, 400, 500, 1000];

  // Define your custom primary color based on the image
  static const Color _primaryColor = Color(0xFFADD8E6); // A light blue color, adjust as needed

  @override
  void initState() {
    super.initState();
    _searchController.addListener(_onSearchChanged);
    _scrollController.addListener(_scrollListener);
    _fetchInitialDoctors();
  }

  @override
  void dispose() {
    _searchController.removeListener(_onSearchChanged);
    _searchController.dispose();
    _scrollController.removeListener(_scrollListener);
    _scrollController.dispose();
    super.dispose();
  }

  Future<void> _fetchInitialDoctors() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final doctors = await _doctorService.fetchDoctors(page: 1);
      _updateDoctorLists(doctors, initialLoad: true);
    } catch (e) {
      _handleError(e);
    }
  }

  Future<void> _loadMoreDoctors() async {
    if (!_hasMore || _isLoadingMore) return;

    setState(() => _isLoadingMore = true);

    try {
      final doctors = await _doctorService.fetchDoctors(page: _currentPage + 1);
      _updateDoctorLists(doctors);
    } catch (e) {
      _handleError(e);
    }
  }

  void _updateDoctorLists(List<Doctor> newDoctors, {bool initialLoad = false}) {
    final allDoctors = initialLoad ? newDoctors : [..._allDoctors, ...newDoctors];

    setState(() {
      _allDoctors = allDoctors;
      _hasMore = newDoctors.isNotEmpty;
      _currentPage = initialLoad ? 1 : _currentPage + 1;

      // Update available filters only on initial load
      if (initialLoad) {
        _availableSpecialities = _extractUniqueSpecialities(allDoctors);
        _availableCities = _extractUniqueCities(allDoctors);
      }

      _filterDoctors();
      _isLoading = false;
      _isLoadingMore = false;
    });
  }

  List<String> _extractUniqueSpecialities(List<Doctor> doctors) {
    return doctors
        .map((d) => d.specialty)
        .where((s) => s != null && s.isNotEmpty)
        .toSet()
        .toList()
        .cast<String>()
        ..sort();
  }

  List<String> _extractUniqueCities(List<Doctor> doctors) {
    return doctors
        .map((d) => d.city)
        .where((c) => c != null && c.isNotEmpty)
        .toSet()
        .toList()
        .cast<String>()
        ..sort();
  }

  void _handleError(dynamic error) {
    debugPrint('Error fetching doctors: $error');
    setState(() {
      _errorMessage = error is String ? error : 'Failed to load doctors. Please try again.';
      _isLoading = false;
      _isLoadingMore = false;
    });

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(_errorMessage!)),
    );
  }

  void _scrollListener() {
    if (_scrollController.position.pixels ==
        _scrollController.position.maxScrollExtent) {
      _loadMoreDoctors();
    }
  }

  void _onSearchChanged() => _filterDoctors();

  void _filterDoctors() {
    setState(() {
      _filteredDoctors = _allDoctors.where((doctor) {
        final query = _searchController.text.toLowerCase();
        final matchesSearch = query.isEmpty ||
            doctor.name.toLowerCase().contains(query) ||
            (doctor.specialty?.toLowerCase().contains(query) ?? false) ||
            (doctor.address?.toLowerCase().contains(query) ?? false) ||
            (doctor.city?.toLowerCase().contains(query) ?? false);

        final matchesSpeciality = _selectedSpeciality == null ||
            _selectedSpeciality == 'All Specialties' ||
            doctor.specialty == _selectedSpeciality;

        final matchesCity = _selectedCity == null ||
            _selectedCity == 'All Cities' ||
            doctor.city == _selectedCity;

        final matchesFee = (_minFee == null || (doctor.consultationFee ?? 0) >= _minFee!) &&
            (_maxFee == null || (doctor.consultationFee ?? 0) <= _maxFee!);

        return matchesSearch && matchesSpeciality && matchesCity && matchesFee;
      }).toList();
    });
  }

  void _showFilterSheet() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      builder: (context) {
        return StatefulBuilder(
          builder: (BuildContext context, StateSetter setModalState) {
            return SingleChildScrollView(
              padding: EdgeInsets.only(
                bottom: MediaQuery.of(context).viewInsets.bottom,
              ),
              child: Padding(
                padding: const EdgeInsets.all(16.0),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    _buildFilterHeader(),
                    const SizedBox(height: 20),
                    _buildSpecialityFilter(setModalState),
                    const SizedBox(height: 15),
                    _buildCityFilter(setModalState),
                    const SizedBox(height: 15),
                    _buildFeeFilter(setModalState),
                    const SizedBox(height: 30),
                    _buildFilterButtons(),
                  ],
                ),
              ),
            );
          },
        );
      },
    );
  }

  Widget _buildFilterHeader() {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        const Text(
          'Filter Doctors',
          style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
        ),
        IconButton(
          icon: const Icon(Icons.close),
          onPressed: () => Navigator.pop(context),
        ),
      ],
    );
  }

  Widget _buildSpecialityFilter(StateSetter setModalState) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text('Specialty', style: TextStyle(fontWeight: FontWeight.w500)),
        DropdownButtonFormField<String>(
          value: _selectedSpeciality,
          items: ['All Specialties', ..._availableSpecialities]
              .map((value) => DropdownMenuItem(
                    value: value,
                    child: Text(value),
                  ))
              .toList(),
          onChanged: (value) => setModalState(() => _selectedSpeciality = value),
          decoration: const InputDecoration(
            border: OutlineInputBorder(),
            contentPadding: EdgeInsets.symmetric(horizontal: 10, vertical: 5),
          ),
        ),
      ],
    );
  }

  Widget _buildCityFilter(StateSetter setModalState) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text('City', style: TextStyle(fontWeight: FontWeight.w500)),
        DropdownButtonFormField<String>(
          value: _selectedCity,
          items: ['All Cities', ..._availableCities]
              .map((value) => DropdownMenuItem(
                    value: value,
                    child: Text(value),
                  ))
              .toList(),
          onChanged: (value) => setModalState(() => _selectedCity = value),
          decoration: const InputDecoration(
            border: OutlineInputBorder(),
            contentPadding: EdgeInsets.symmetric(horizontal: 10, vertical: 5),
          ),
        ),
      ],
    );
  }

  Widget _buildFeeFilter(StateSetter setModalState) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text('Consultation Fee (DH)',
            style: TextStyle(fontWeight: FontWeight.w500)),
        Row(
          children: [
            Expanded(
              child: DropdownButtonFormField<double>(
                value: _minFee,
                hint: const Text('Min Fee'),
                items: [null, ..._feeRanges].map((value) => DropdownMenuItem(
                      value: value,
                      child: Text(value == null ? 'Any' : 'DH${value.toInt()}'),
                    )).toList(),
                onChanged: (value) => setModalState(() => _minFee = value),
              ),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: DropdownButtonFormField<double>(
                value: _maxFee,
                hint: const Text('Max Fee'),
                items: [null, ..._feeRanges].map((value) => DropdownMenuItem(
                      value: value,
                      child: Text(value == null ? 'Any' : 'DH${value.toInt()}'),
                    )).toList(),
                onChanged: (value) => setModalState(() => _maxFee = value),
              ),
            ),
          ],
        ),
      ],
    );
  }

  Widget _buildFilterButtons() {
    return Row(
      children: [
        Expanded(
          child: OutlinedButton(
            onPressed: () {
              setState(() {
                _selectedSpeciality = null;
                _selectedCity = null;
                _minFee = null;
                _maxFee = null;
              });
              Navigator.pop(context);
              _filterDoctors();
            },
            child: const Text('Reset'),
          ),
        ),
        const SizedBox(width: 15),
        Expanded(
          child: ElevatedButton(
            // Apply primary color to the ElevatedButton
            style: ElevatedButton.styleFrom(
              backgroundColor: _primaryColor, // Background color
              foregroundColor: Colors.white, // Text color
            ),
            onPressed: () {
              Navigator.pop(context);
              _filterDoctors();
            },
            child: const Text('Apply'),
          ),
        ),
      ],
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Our Doctors'),
        // Set AppBar background color to match the primary color
        backgroundColor: _primaryColor,
        foregroundColor: Colors.white, // Set title and icon color to white for contrast
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _fetchInitialDoctors,
          ),
        ],
      ),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.all(16.0),
            child: TextField(
              controller: _searchController,
              decoration: InputDecoration(
                hintText: 'Search doctors...',
                prefixIcon: const Icon(Icons.search),
                suffixIcon: IconButton(
                  icon: const Icon(Icons.filter_list),
                  onPressed: _showFilterSheet,
                ),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(10),
                ),
              ),
            ),
          ),
          _buildActiveFilters(),
          Expanded(
            child: _buildDoctorList(),
          ),
        ],
      ),
    );
  }

  Widget _buildActiveFilters() {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16.0),
      child: Wrap(
        spacing: 8.0,
        runSpacing: 4.0,
        children: [
          if (_selectedSpeciality != null && _selectedSpeciality != 'All Specialties')
            _buildFilterChip(_selectedSpeciality!, () {
              setState(() {
                _selectedSpeciality = null;
                _filterDoctors();
              });
            }),
          if (_selectedCity != null && _selectedCity != 'All Cities')
            _buildFilterChip(_selectedCity!, () {
              setState(() {
                _selectedCity = null;
                _filterDoctors();
              });
            }),
          if (_minFee != null || _maxFee != null)
            _buildFilterChip(
              'Fee: ${_minFee != null ? 'DH${_minFee!.toInt()}' : ''}'
              '${_minFee != null && _maxFee != null ? '-' : ''}'
              '${_maxFee != null ? 'DH${_maxFee!.toInt()}' : ''}',
              () {
                setState(() {
                  _minFee = null;
                  _maxFee = null;
                  _filterDoctors();
                });
              },
            ),
        ],
      ),
    );
  }

  Widget _buildFilterChip(String label, VoidCallback onDeleted) {
    return Chip(
      label: Text(label),
      onDeleted: onDeleted,
      // Apply primary color with a lighter shade for the filter chip background
      backgroundColor: _primaryColor.withOpacity(0.3), // Lighter shade of the primary color
      deleteIconColor: Theme.of(context).colorScheme.onSurface, // Or a contrasting color
    );
  }

  Widget _buildDoctorList() {
    if (_isLoading) {
      return const Center(child: CircularProgressIndicator());
    }

    if (_errorMessage != null) {
      return Center(child: Text(_errorMessage!));
    }

    if (_filteredDoctors.isEmpty) {
      return Center(
        child: Text(
          _searchController.text.isNotEmpty ||
                  _selectedSpeciality != null ||
                  _selectedCity != null ||
                  _minFee != null ||
                  _maxFee != null
              ? 'No doctors match your filters'
              : 'No doctors available',
        ),
      );
    }

    return ListView.builder(
      controller: _scrollController,
      itemCount: _filteredDoctors.length + (_hasMore ? 1 : 0),
      itemBuilder: (context, index) {
        if (index == _filteredDoctors.length) {
          return _buildLoadMoreIndicator();
        }

        final doctor = _filteredDoctors[index];
        return DoctorCard(
          doctor: doctor,
          onTap: () => Navigator.push(
            context,
            MaterialPageRoute(
              builder: (context) => DoctorDetailScreen(doctor: doctor),
            ),
          ),
        );
      },
    );
  }

  Widget _buildLoadMoreIndicator() {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 16.0),
      child: Center(
        child: _isLoadingMore
            ? const CircularProgressIndicator()
            : ElevatedButton(
                // Apply primary color to the "Load More" button
                style: ElevatedButton.styleFrom(
                  backgroundColor: _primaryColor, // Background color
                  foregroundColor: Colors.white, // Text color
                ),
                onPressed: _loadMoreDoctors,
                child: const Text('Load More'),
              ),
      ),
    );
  }
}