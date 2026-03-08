// lib/screens/ai_chat_screen.dart
import 'package:flutter/material.dart';
import 'package:flutter_application_1/models/medical_dossier.dart';
import 'package:flutter_application_1/models/patient.dart';

class AIChatScreen extends StatefulWidget {
  final Patient patient;
  final MedicalDossier? medicalDossier;

  const AIChatScreen({
    super.key,
    required this.patient,
    this.medicalDossier,
  });

  @override
  State<AIChatScreen> createState() => _AIChatScreenState();
}

class _AIChatScreenState extends State<AIChatScreen> {
  final TextEditingController _messageController = TextEditingController();
  final List<Map<String, dynamic>> _messages = [];
  bool _isLoading = false;

  @override
  void initState() {
    super.initState();
    // Add welcome message with patient info
    _addSystemMessage();
  }

  void _addSystemMessage() {
    final patientInfo = '''
Patient: ${widget.patient.fullName}
Gender: ${widget.patient.genre ?? 'N/A'}
''';

    final medicalInfo = widget.medicalDossier != null ? '''
Height: ${widget.medicalDossier!.taille} cm
Weight: ${widget.medicalDossier!.poids} kg
Blood Type: ${widget.medicalDossier!.groupeSanguin ?? 'N/A'}
Allergies: ${widget.medicalDossier!.allergies?.join(', ') ?? 'None'}
''' : 'No medical dossier available';

    setState(() {
      _messages.add({
        'text': 'Hello! I\'m your AI Health Assistant. Here\'s what I know about you:\n\n'
                '$patientInfo\n$medicalInfo\n\n'
                'How can I help with your diet today?',
        'isUser': false,
      });
    });
  }

  void _sendMessage() async {
    if (_messageController.text.isEmpty) return;

    final message = _messageController.text;
    _messageController.clear();

    setState(() {
      _messages.add({'text': message, 'isUser': true});
      _isLoading = true;
    });

    // Simulate AI response (replace with actual API call)
    await Future.delayed(const Duration(seconds: 1));

    setState(() {
      _messages.add({
        'text': _generateAIResponse(message),
        'isUser': false,
      });
      _isLoading = false;
    });
  }

  String _generateAIResponse(String message) {
    // Simple mock responses - replace with real AI integration
    if (message.toLowerCase().contains('diet') || 
        message.toLowerCase().contains('food')) {
      return 'Based on your medical profile, I recommend:\n'
             '- Balanced meals with lean proteins\n'
             '- ${widget.medicalDossier?.allergies?.isNotEmpty ?? false ? 
                'Avoiding ${widget.medicalDossier!.allergies!.join(", ")}' : ''}\n'
             '- 5 servings of vegetables daily';
    }
    return 'I can help analyze your medical data to provide dietary advice. '
           'Ask me about meal plans, nutrition, or food recommendations.';
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
              itemCount: _messages.length,
              itemBuilder: (context, index) {
                final message = _messages[index];
                return Align(
                  alignment: message['isUser'] 
                      ? Alignment.centerRight 
                      : Alignment.centerLeft,
                  child: Container(
                    margin: const EdgeInsets.only(bottom: 8),
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: message['isUser']
                          ? const Color(0xFF4682B4)
                          : Colors.grey[200],
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Text(
                      message['text'],
                      style: TextStyle(
                        color: message['isUser'] ? Colors.white : Colors.black,
                      ),
                    ),
                  ),
                );
              },
            ),
          ),
          if (_isLoading)
            const Padding(
              padding: EdgeInsets.all(8.0),
              child: CircularProgressIndicator(),
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