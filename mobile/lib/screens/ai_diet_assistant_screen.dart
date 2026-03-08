// lib/screens/ai_diet_assistant_screen.dart
import 'package:flutter/material.dart';
import 'package:flutter_application_1/models/medical_dossier.dart';
import 'package:flutter_application_1/models/patient.dart';
import 'package:flutter_application_1/models/chat_message.dart'; // Import the ChatMessage model
import 'package:flutter_application_1/services/ai_diet_service.dart'; // Import the AIDietService
import 'package:intl/intl.dart'; // Add this import for DateFormat

class AIDietAssistantScreen extends StatefulWidget {
  final Patient patient;
  final MedicalDossier? medicalDossier;

  const AIDietAssistantScreen({
    super.key,
    required this.patient,
    required this.medicalDossier,
  });

  @override
  State<AIDietAssistantScreen> createState() => _AIDietAssistantScreenState();
}

class _AIDietAssistantScreenState extends State<AIDietAssistantScreen> {
  final TextEditingController _messageController = TextEditingController();
  final List<ChatMessage> _messages = [];
  late AIDietService _aiDietService; // Declare the service
  bool _isLoading = false;
  bool _hasError = false;

  @override
  void initState() {
    super.initState();
    _aiDietService = AIDietService(
      patient: widget.patient,
      medicalDossier: widget.medicalDossier,
    );
    _addWelcomeMessage();
  }

  void _addWelcomeMessage() {
    final patientInfo = '''
Patient: ${widget.patient.fullName}
Age: ${widget.patient.age ?? 'N/A'}
Gender: ${widget.patient.genre}
''';

    final medicalInfo = widget.medicalDossier != null
        ? '''
Height: ${widget.medicalDossier!.taille} cm
Weight: ${widget.medicalDossier!.poids} kg
BMI: ${_aiDietService.calculateBMI(widget.medicalDossier!.taille, widget.medicalDossier!.poids)?.toStringAsFixed(1) ?? 'N/A'}
Blood Type: ${widget.medicalDossier!.groupeSanguin ?? 'N/A'}
Allergies: ${widget.medicalDossier!.allergies?.join(', ') ?? 'None'}
Medical History: ${widget.medicalDossier!.antecedents?.join(', ') ?? 'None'}
Current Treatments: ${widget.medicalDossier!.traitements?.join(', ') ?? 'None'}
'''
        : 'No medical dossier available';

    _addMessage(
      'Hello! I\'m your AI Diet Assistant. Here\'s what I know about you:\n\n'
      '$patientInfo\n$medicalInfo\n\n'
      'How can I help with your diet today?',
      isUser: false,
    );
  }

  void _addMessage(String text, {required bool isUser}) {
    setState(() {
      _messages.add(ChatMessage(
        text: text,
        isUser: isUser,
        timestamp: DateTime.now(),
      ));
    });
  }

  Future<void> _sendMessage() async {
    if (_messageController.text.isEmpty) return;

    final message = _messageController.text;
    _messageController.clear();

    _addMessage(message, isUser: true);
    setState(() {
      _isLoading = true;
      _hasError = false;
    });

    try {
      final response = await _aiDietService.getAIResponse(message);
      _addMessage(response, isUser: false);
    } catch (e) {
      _addMessage('Sorry, I encountered an error. Please try again later.',
          isUser: false);
      setState(() => _hasError = true);
    } finally {
      setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('AI Diet Assistant'),
        backgroundColor: const Color(0xFF87CEEB),
      ),
      body: Column(
        children: [
          Expanded(
            child: ListView.builder(
              padding: const EdgeInsets.all(16),
              reverse: false,
              itemCount: _messages.length,
              itemBuilder: (context, index) {
                final message = _messages[index];
                return ChatBubble(
                  message: message.text,
                  isUser: message.isUser,
                  timestamp: message.timestamp,
                );
              },
            ),
          ),
          if (_isLoading)
            const Padding(
              padding: EdgeInsets.all(8.0),
              child: CircularProgressIndicator(),
            ),
          if (_hasError)
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16.0),
              child: Text(
                'Connection error - using demo responses',
                style: TextStyle(color: Colors.red[600]),
              ),
            ),
          Padding(
            padding: const EdgeInsets.all(8.0),
            child: Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: _messageController,
                    decoration: InputDecoration(
                      hintText: 'Ask about diet...',
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(20),
                      ),
                      contentPadding: const EdgeInsets.symmetric(horizontal: 16),
                    ),
                    onSubmitted: (_) => _sendMessage(),
                  ),
                ),
                IconButton(
                  icon: const Icon(Icons.send, color: Color(0xFF4682B4)),
                  onPressed: _sendMessage,
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class ChatBubble extends StatelessWidget {
  final String message;
  final bool isUser;
  final DateTime timestamp;

  const ChatBubble({
    super.key,
    required this.message,
    required this.isUser,
    required this.timestamp,
  });

  @override
  Widget build(BuildContext context) {
    return Align(
      alignment: isUser ? Alignment.centerRight : Alignment.centerLeft,
      child: Container(
        margin: const EdgeInsets.only(bottom: 8),
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(
          color: isUser ? const Color(0xFF4682B4) : Colors.grey[200],
          borderRadius: BorderRadius.circular(12),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              message,
              style: TextStyle(
                color: isUser ? Colors.white : Colors.black,
              ),
            ),
            const SizedBox(height: 4),
            Text(
              DateFormat('HH:mm').format(timestamp),
              style: TextStyle(
                color: isUser ? Colors.white70 : Colors.grey[600],
                fontSize: 10,
              ),
            ),
          ],
        ),
      ),
    );
  }
}