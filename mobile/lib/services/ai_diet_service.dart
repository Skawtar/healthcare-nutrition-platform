import 'dart:convert';
import 'package:flutter_application_1/models/medical_dossier.dart';
import 'package:flutter_application_1/models/patient.dart';
import 'package:http/http.dart' as http;

class AIDietService {
  final Patient patient;
  final MedicalDossier? medicalDossier;

  // Changed to the new API endpoint
  static const String _apiUrl = 'https://g4f-api.vercel.app/v1/chat/completions';

  AIDietService({required this.patient, this.medicalDossier});

  Future<String> getAIResponse(String message) async {
    // For testing without API, you can uncomment this line:
    // await Future.delayed(const Duration(seconds: 1));
    // return _generateMockResponse(message);

    final prompt = '''
Patient Context:
${_buildPatientContext()}

User Question: $message

Provide specific dietary advice considering the medical context:
- Use bullet points
- Suggest portion sizes
- Consider any allergies
- Keep response under 150 words
''';

    try {
      final response = await http.post(
        Uri.parse(_apiUrl),
        headers: {
          'Content-Type': 'application/json',
          // No 'Authorization' header needed for this specific G4F API,
          // as it likely manages its own keys or is designed for direct access.
        },
        body: jsonEncode({
          "model": "gpt-3.5-turbo", // Model specified by the G4F API example
          "messages": [
            {"role": "user", "content": prompt}
          ],
          "temperature": 0.7,
          "max_tokens": 200,
        }),
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        // The structure might vary slightly based on the proxy API's response.
        // Assuming it's similar to OpenAI's 'choices[0].message.content'.
        return data['choices'][0]['message']['content'];
      } else {
        print('API Error Status: ${response.statusCode}');
        print('API Error Body: ${response.body}');
        throw Exception(
            'Failed to get AI response from G4F API. Status: ${response.statusCode}');
      }
    } catch (e) {
      print('Error during G4F API call: $e');
      // Fallback to mock response in case of API call failure
      return _generateMockResponse(message);
    }
  }

  String _buildPatientContext() {
    return '''
Name: ${patient.fullName}
Age: ${patient.age ?? 'N/A'}
Gender: ${patient.genre}
Height: ${medicalDossier?.taille ?? 'N/A'} cm
Weight: ${medicalDossier?.poids ?? 'N/A'} kg
Allergies: ${medicalDossier?.allergies?.join(', ') ?? 'None'}
Medical Conditions: ${medicalDossier?.antecedents?.join(', ') ?? 'None'}
Current Medications: ${medicalDossier?.traitements?.join(', ') ?? 'None'}
''';
  }

  String _generateMockResponse(String message) {
    if (message.toLowerCase().contains('breakfast')) {
      return '''Based on your profile, I recommend:
- Oatmeal with almond butter (1/2 cup oats)
- 1 medium banana
- 1 tbsp chia seeds
- 1 cup almond milk
Avoid nuts if allergic''';
    } else if (message.toLowerCase().contains('lose weight') &&
        (medicalDossier?.poids ?? 0) > 80) {
      return '''For healthy weight loss:
- Reduce portion sizes by 20%
- Focus on lean proteins (chicken, fish)
- 5 servings of vegetables daily
- Limit processed foods
- Drink 2L water daily''';
    } else {
      return '''I can provide personalized diet advice based on:
- Your weight and height
- Any allergies
- Medical conditions
Ask me about meal plans, recipes, or nutrition tips!''';
    }
  }

  double? calculateBMI(double? heightCm, double? weightKg) {
    if (heightCm == null || weightKg == null || heightCm <= 0) return null;
    final heightMeters = heightCm / 100;
    return weightKg / (heightMeters * heightMeters);
  }
}