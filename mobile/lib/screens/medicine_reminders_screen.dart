import 'package:flutter/material.dart';
import 'package:intl/intl.dart'; // Ensure intl is in your pubspec.yaml

// --- Data Model ---
class MedicineReminder {
  final String id;
  final String medicineName;
  final String dosage; // e.g., "500 mg", "1 tablet"
  final String frequency; // e.g., "Daily", "Twice a day", "Every 8 hours"
  final DateTime startDate;
  final DateTime? endDate; // Optional end date
  final List<TimeOfDay> reminderTimes; // Specific times for the day
  bool isActive; // Whether the reminder is currently active

  MedicineReminder({
    required this.id,
    required this.medicineName,
    required this.dosage,
    required this.frequency,
    required this.startDate,
    this.endDate,
    required this.reminderTimes,
    this.isActive = true,
  });

  // Helper to check if there are reminder times
  bool get hasReminderTimes => reminderTimes.isNotEmpty;

  // Getter to format reminder times as a string
  String get formattedReminderTimes {
    if (reminderTimes.isEmpty) {
      return 'No specific times';
    }
    // Note: TimeOfDay.format requires a BuildContext, but for display purposes here, we use a fallback.
    // In widgets, prefer using time.format(context).
    return reminderTimes.map((t) => '${t.hour.toString().padLeft(2, '0')}:${t.minute.toString().padLeft(2, '0')}').join(', ');
  }
}

// --- MedicineRemindersScreen ---
class MedicineRemindersScreen extends StatefulWidget {
  const MedicineRemindersScreen({super.key});

  @override
  State<MedicineRemindersScreen> createState() => _MedicineRemindersScreenState();
}

class _MedicineRemindersScreenState extends State<MedicineRemindersScreen> {
  // Dummy Data for demonstration
  final List<MedicineReminder> _reminders = [
    MedicineReminder(
      id: 'med001',
      medicineName: 'Amoxicillin',
      dosage: '500 mg',
      frequency: 'Twice a day',
      startDate: DateTime.now().subtract(const Duration(days: 3)),
      endDate: DateTime.now().add(const Duration(days: 4)),
      reminderTimes: [const TimeOfDay(hour: 9, minute: 0), const TimeOfDay(hour: 21, minute: 0)],
    ),
    MedicineReminder(
      id: 'med002',
      medicineName: 'Vitamin D',
      dosage: '1 tablet',
      frequency: 'Daily',
      startDate: DateTime.now().subtract(const Duration(days: 30)),
      reminderTimes: [const TimeOfDay(hour: 10, minute: 0)],
      isActive: true,
    ),
    MedicineReminder(
      id: 'med003',
      medicineName: 'Ibuprofen',
      dosage: '200 mg',
      frequency: 'As needed',
      startDate: DateTime.now().subtract(const Duration(days: 10)),
      reminderTimes: [], // No specific fixed times
      isActive: true,
    ),
  ];

  @override
  Widget build(BuildContext context) {
    // Sort reminders by start date, active ones first
    _reminders.sort((a, b) {
      if (a.isActive != b.isActive) {
        return a.isActive ? -1 : 1; // Active first
      }
      return a.startDate.compareTo(b.startDate); // Then by start date
    });

    return Scaffold(
      appBar: AppBar(
        title: const Text('Medicine Reminders', style: TextStyle(color: Colors.white)),
        backgroundColor: const Color(0xFF87CEEB), // Light Blue
        actions: [
          IconButton(
            icon: const Icon(Icons.history, color: Colors.white),
            onPressed: () {
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(content: Text('View history of taken doses')),
              );
            },
          ),
        ],
      ),
      body: _reminders.isEmpty
          ? Center(
              child: Padding(
                padding: const EdgeInsets.all(16.0),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Icon(
                      Icons.medication_liquid_outlined,
                      size: 60,
                      color: Colors.grey[400],
                    ),
                    const SizedBox(height: 10),
                    Text(
                      'No medicine reminders set yet.',
                      style: TextStyle(fontSize: 18, color: Colors.grey[600]),
                      textAlign: TextAlign.center,
                    ),
                    const SizedBox(height: 20),
                    ElevatedButton.icon(
                      onPressed: () => _showAddReminderBottomSheet(context),
                      icon: const Icon(Icons.add, color: Colors.white),
                      label: const Text('Add Your First Reminder', style: TextStyle(color: Colors.white)),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF4682B4), // Steel Blue
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(10),
                        ),
                        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
                      ),
                    ),
                  ],
                ),
              ),
            )
          : ListView.builder(
              padding: const EdgeInsets.all(16.0),
              itemCount: _reminders.length,
              itemBuilder: (context, index) {
                final reminder = _reminders[index];
                return MedicineReminderCard(
                  reminder: reminder,
                  onToggleActive: (isActive) {
                    setState(() {
                      // In a real app, you'd update your backend here
                      reminder.isActive = isActive;
                    });
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(content: Text('${reminder.medicineName} reminder ${isActive ? 'activated' : 'deactivated'}')),
                    );
                  },
                  onTap: () {
                    _showReminderDetails(context, reminder);
                  },
                );
              },
            ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => _showAddReminderBottomSheet(context),
        label: const Text('Add Reminder', style: TextStyle(color: Colors.white)),
        icon: const Icon(Icons.add, color: Colors.white),
        backgroundColor: const Color(0xFF4682B4), // Steel Blue
      ),
    );
  }

  void _showAddReminderBottomSheet(BuildContext context) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true, // Allows content to be scrollable if it's too tall
      builder: (context) {
        return Padding(
          padding: EdgeInsets.only(bottom: MediaQuery.of(context).viewInsets.bottom),
          child: _AddMedicineReminderForm(
            onReminderAdded: (newReminder) {
              setState(() {
                _reminders.add(newReminder);
              });
              Navigator.pop(context); // Close bottom sheet
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(content: Text('${newReminder.medicineName} reminder added!')),
              );
            },
          ),
        );
      },
    );
  }

  void _showReminderDetails(BuildContext context, MedicineReminder reminder) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      builder: (context) {
        return SingleChildScrollView(
          child: Container(
            padding: const EdgeInsets.all(20),
            decoration: const BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisSize: MainAxisSize.min,
              children: [
                Align(
                  alignment: Alignment.center,
                  child: Container(
                    width: 40,
                    height: 5,
                    margin: const EdgeInsets.only(bottom: 15),
                    decoration: BoxDecoration(
                      color: Colors.grey[300],
                      borderRadius: BorderRadius.circular(5),
                    ),
                  ),
                ),
                Text(
                  reminder.medicineName,
                  style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                        fontWeight: FontWeight.bold,
                        color: const Color(0xFF4682B4),
                      ),
                ),
                const Divider(height: 30, thickness: 1),
                _buildDetailRow(Icons.medical_services, 'Dosage', reminder.dosage),
                _buildDetailRow(Icons.repeat, 'Frequency', reminder.frequency),
                _buildDetailRow(Icons.date_range, 'Start Date', DateFormat('MMM d,yyyy').format(reminder.startDate)),
                if (reminder.endDate != null)
                  _buildDetailRow(Icons.event_busy, 'End Date', DateFormat('MMM d,yyyy').format(reminder.endDate!)),
                _buildDetailRow(Icons.access_time, 'Times', reminder.formattedReminderTimes),
                _buildDetailRow(Icons.toggle_on, 'Status', reminder.isActive ? 'Active' : 'Inactive',
                    color: reminder.isActive ? Colors.green : Colors.red),
                _buildDetailRow(
                  Icons.access_time,
                  'Times',
                  reminder.hasReminderTimes
                      ? reminder.reminderTimes.map((time) => time.format(context)).join(', ')
                      : 'No specific times',
                ),
                Center(
                  child: ElevatedButton.icon(
                    onPressed: () {
                      Navigator.pop(context);
                      ScaffoldMessenger.of(context).showSnackBar(
                        SnackBar(content: Text('Simulating taking a dose of ${reminder.medicineName}')),
                      );
                      // TODO: Implement "Mark as taken" or "Edit" logic
                    },
                    icon: const Icon(Icons.check_circle_outline, color: Colors.white),
                    label: const Text('Mark as Taken', style: TextStyle(color: Colors.white)),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.teal,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
                    ),
                  ),
                ),
                const SizedBox(height: 10),
              ],
            ),
          ),
        );
      },
    );
  }

  Widget _buildDetailRow(IconData icon, String label, String value, {Color? color}) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8.0),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, color: color ?? const Color(0xFF87CEEB), size: 24),
          const SizedBox(width: 15),
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
                const SizedBox(height: 2),
                Text(
                  value,
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                        fontWeight: FontWeight.bold,
                        color: color ?? Colors.black87,
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

// --- Widget for displaying individual medicine reminder cards ---
class MedicineReminderCard extends StatelessWidget {
  final MedicineReminder reminder;
  final ValueChanged<bool> onToggleActive;
  final VoidCallback onTap;

  const MedicineReminderCard({
    super.key,
    required this.reminder,
    required this.onToggleActive,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    Color statusColor = reminder.isActive ? Colors.green : Colors.grey;
    IconData statusIcon = reminder.isActive ? Icons.alarm_on : Icons.alarm_off;

    return Card(
      margin: const EdgeInsets.only(bottom: 16.0),
      elevation: 4,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(15),
      ),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(15),
        child: Padding(
          padding: const EdgeInsets.all(20.0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Flexible(
                    child: Text(
                      reminder.medicineName,
                      style: Theme.of(context).textTheme.titleLarge?.copyWith(
                            fontWeight: FontWeight.bold,
                            color: const Color(0xFF4682B4), // Steel Blue
                          ),
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                    decoration: BoxDecoration(
                      color: statusColor.withOpacity(0.15),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Row(
                      children: [
                        Icon(statusIcon, size: 16, color: statusColor),
                        const SizedBox(width: 5),
                        Text(
                          reminder.isActive ? 'Active' : 'Inactive',
                          style: TextStyle(
                            color: statusColor,
                            fontWeight: FontWeight.w500,
                            fontSize: 12,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 5),
              Text(
                '${reminder.dosage} • ${reminder.frequency}',
                style: Theme.of(context).textTheme.titleSmall?.copyWith(
                      color: Colors.grey[700],
                    ),
              ),
              const Divider(height: 25, thickness: 0.5),
              Row(
                children: [
                  Expanded(
                    child: Text(
                      reminder.hasReminderTimes
                          ? reminder.reminderTimes.map((time) => time.format(context)).join(', ')
                          : 'No specific times',
                      style: Theme.of(context).textTheme.bodyLarge,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 8),
              Row(
                children: [
                  Icon(Icons.calendar_today, size: 18, color: Colors.grey[600]),
                  const SizedBox(width: 10),
                  Text(
                    '${DateFormat('MMM d,yyyy').format(reminder.startDate)} - ${reminder.endDate != null ? DateFormat('MMM d,yyyy').format(reminder.endDate!) : 'Ongoing'}',
                    style: Theme.of(context).textTheme.bodyLarge,
                  ),
                ],
              ),
              const SizedBox(height: 10),
              Align(
                alignment: Alignment.centerRight,
                child: SwitchListTile(
                  title: const Text('Reminder Active'),
                  value: reminder.isActive,
                  onChanged: onToggleActive,
                  activeColor: const Color(0xFF4682B4),
                  contentPadding: EdgeInsets.zero,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

// --- Form for adding a new reminder ---
class _AddMedicineReminderForm extends StatefulWidget {
  final Function(MedicineReminder) onReminderAdded;

  const _AddMedicineReminderForm({required this.onReminderAdded});

  @override
  State<_AddMedicineReminderForm> createState() => _AddMedicineReminderFormState();
}

class _AddMedicineReminderFormState extends State<_AddMedicineReminderForm> {
  final _formKey = GlobalKey<FormState>();
  final TextEditingController _medicineNameController = TextEditingController();
  final TextEditingController _dosageController = TextEditingController();

  String _selectedFrequency = 'Daily';
  List<String> _frequencies = ['Daily', 'Twice a day', 'Three times a day', 'Every X hours (Custom)', 'As needed'];

  DateTime _startDate = DateTime.now();
  DateTime? _endDate;
  List<TimeOfDay> _selectedTimes = [];

  @override
  void initState() {
    super.initState();
    _updateDefaultTimes(_selectedFrequency);
  }

  void _updateDefaultTimes(String frequency) {
    setState(() {
      _selectedTimes.clear();
      if (frequency == 'Daily') {
        _selectedTimes.add(const TimeOfDay(hour: 9, minute: 0));
      } else if (frequency == 'Twice a day') {
        _selectedTimes.addAll([const TimeOfDay(hour: 9, minute: 0), const TimeOfDay(hour: 21, minute: 0)]);
      } else if (frequency == 'Three times a day') {
        _selectedTimes.addAll([const TimeOfDay(hour: 9, minute: 0), const TimeOfDay(hour: 15, minute: 0), const TimeOfDay(hour: 21, minute: 0)]);
      }
      // For 'Every X hours' or 'As needed', leave times empty for user to add
    });
  }

  Future<void> _selectDate(BuildContext context, {required bool isStartDate}) async {
    final DateTime? picked = await showDatePicker(
      context: context,
      initialDate: isStartDate ? _startDate : (_endDate ?? _startDate),
      firstDate: DateTime.now().subtract(const Duration(days: 365 * 5)), // 5 years back
      lastDate: DateTime.now().add(const Duration(days: 365 * 5)), // 5 years forward
    );
    if (picked != null && picked != (isStartDate ? _startDate : _endDate)) {
      setState(() {
        if (isStartDate) {
          _startDate = picked;
          if (_endDate != null && _startDate.isAfter(_endDate!)) {
            _endDate = _startDate; // Ensure end date is not before start date
          }
        } else {
          _endDate = picked;
          if (_startDate.isAfter(_endDate!)) {
            _startDate = _endDate!; // Ensure start date is not after end date
          }
        }
      });
    }
  }

  Future<void> _selectTime(BuildContext context, {int? indexToReplace}) async {
    final TimeOfDay? picked = await showTimePicker(
      context: context,
      initialTime: (indexToReplace != null && _selectedTimes.length > indexToReplace)
          ? _selectedTimes[indexToReplace]
          : TimeOfDay.now(),
    );
    if (picked != null) {
      setState(() {
        if (indexToReplace != null && _selectedTimes.length > indexToReplace) {
          _selectedTimes[indexToReplace] = picked;
        } else {
          _selectedTimes.add(picked);
          _selectedTimes.sort((a, b) => (a.hour * 60 + a.minute).compareTo(b.hour * 60 + b.minute));
        }
      });
    }
  }

  @override
  void dispose() {
    _medicineNameController.dispose();
    _dosageController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: const BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      child: Form(
        key: _formKey,
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Align(
              alignment: Alignment.center,
              child: Container(
                width: 40,
                height: 5,
                margin: const EdgeInsets.only(bottom: 15),
                decoration: BoxDecoration(
                  color: Colors.grey[300],
                  borderRadius: BorderRadius.circular(5),
                ),
              ),
            ),
            Text(
              'Add New Medicine Reminder',
              style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                    fontWeight: FontWeight.bold,
                    color: const Color(0xFF4682B4),
                  ),
            ),
            const Divider(height: 30, thickness: 1),
            TextFormField(
              controller: _medicineNameController,
              decoration: InputDecoration(
                labelText: 'Medicine Name',
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                prefixIcon: const Icon(Icons.medication_outlined, color: Color(0xFF87CEEB)),
              ),
              validator: (value) {
                if (value == null || value.isEmpty) {
                  return 'Please enter medicine name';
                }
                return null;
              },
            ),
            const SizedBox(height: 15),
            TextFormField(
              controller: _dosageController,
              decoration: InputDecoration(
                labelText: 'Dosage (e.g., 500mg, 1 tablet)',
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                prefixIcon: const Icon(Icons.numbers, color: Color(0xFF87CEEB)),
              ),
              validator: (value) {
                if (value == null || value.isEmpty) {
                  return 'Please enter dosage';
                }
                return null;
              },
            ),
            const SizedBox(height: 15),
            DropdownButtonFormField<String>(
              value: _selectedFrequency,
              decoration: InputDecoration(
                labelText: 'Frequency',
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                prefixIcon: const Icon(Icons.repeat, color: Color(0xFF87CEEB)),
              ),
              items: _frequencies.map((String freq) {
                return DropdownMenuItem<String>(
                  value: freq,
                  child: Text(freq),
                );
              }).toList(),
              onChanged: (String? newValue) {
                setState(() {
                  _selectedFrequency = newValue!;
                  _updateDefaultTimes(_selectedFrequency);
                });
              },
            ),
            const SizedBox(height: 15),
            Row(
              children: [
                Expanded(
                  child: GestureDetector(
                    onTap: () => _selectDate(context, isStartDate: true),
                    child: InputDecorator(
                      decoration: InputDecoration(
                        labelText: 'Start Date',
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                        prefixIcon: const Icon(Icons.calendar_today, color: Color(0xFF87CEEB)),
                      ),
                      child: Text(DateFormat('MMM d,yyyy').format(_startDate)),
                    ),
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: GestureDetector(
                    onTap: () => _selectDate(context, isStartDate: false),
                    child: InputDecorator(
                      decoration: InputDecoration(
                        labelText: 'End Date (Optional)',
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                        prefixIcon: const Icon(Icons.event_busy, color: Color(0xFF87CEEB)),
                      ),
                      child: Text(_endDate != null ? DateFormat('MMM d,yyyy').format(_endDate!) : 'Never'),
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 15),
            Text('Reminder Times:', style: Theme.of(context).textTheme.titleMedium?.copyWith(color: const Color(0xFF4682B4))),
            const SizedBox(height: 8),
            _selectedFrequency == 'As needed'
                ? const Text(
                    'No fixed times for "As needed" medications.',
                    style: TextStyle(fontStyle: FontStyle.italic, color: Colors.grey),
                  )
                : Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Wrap(
                        spacing: 10,
                        runSpacing: 5,
                        children: _selectedTimes.asMap().entries.map((entry) {
                          int idx = entry.key;
                          TimeOfDay time = entry.value;
                          return Chip(
                            label: Text(time.format(context)),
                            onDeleted: () {
                              setState(() {
                                _selectedTimes.removeAt(idx);
                              });
                            },
                            deleteIcon: const Icon(Icons.cancel, size: 18),
                            backgroundColor: const Color(0xFFE3F2FD),
                            labelStyle: const TextStyle(color: Color(0xFF4682B4)),
                          );
                        }).toList(),
                      ),
                      const SizedBox(height: 10),
                      ElevatedButton.icon(
                        onPressed: () => _selectTime(context),
                        icon: const Icon(Icons.add_alarm, color: Color(0xFF4682B4)),
                        label: const Text('Add Time', style: TextStyle(color: Color(0xFF4682B4))),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.grey[100],
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                        ),
                      ),
                    ],
                  ),
            const SizedBox(height: 25),
            Center(
              child: ElevatedButton.icon(
                onPressed: () {
                  if (_formKey.currentState!.validate()) {
                    if (_selectedFrequency != 'As needed' && _selectedTimes.isEmpty) {
                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(content: Text('Please add at least one reminder time for the selected frequency.')),
                      );
                      return;
                    }

                    final newReminder = MedicineReminder(
                      id: DateTime.now().millisecondsSinceEpoch.toString(),
                      medicineName: _medicineNameController.text,
                      dosage: _dosageController.text,
                      frequency: _selectedFrequency,
                      startDate: _startDate,
                      endDate: _endDate,
                      reminderTimes: _selectedTimes,
                      isActive: true,
                    );
                    widget.onReminderAdded(newReminder);
                  }
                },
                icon: const Icon(Icons.check_circle, color: Colors.white),
                label: const Text('Save Reminder', style: TextStyle(color: Colors.white, fontSize: 16)),
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF4682B4),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                  padding: const EdgeInsets.symmetric(horizontal: 30, vertical: 15),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}