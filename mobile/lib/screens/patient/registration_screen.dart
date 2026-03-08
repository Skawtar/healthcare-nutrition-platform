import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:flutter_application_1/screens/patient/medical_dossier_screen.dart';
import 'package:flutter_application_1/services/auth/api_service.dart';

class RegistrationScreen extends StatefulWidget {
  const RegistrationScreen({super.key});

  @override
  _RegistrationScreenState createState() => _RegistrationScreenState();
}

class _RegistrationScreenState extends State<RegistrationScreen> {
  final _formKey = GlobalKey<FormState>();
  final ApiService apiService = ApiService();

  final TextEditingController _passwordController = TextEditingController();
  final TextEditingController _confirmPasswordController = TextEditingController();

  String cin = '';
  String nom = '';
  String prenom = '';
  String dateNaissance = '';
  String genre = 'H';
  String email = '';
  String telephone = '';
  String adresse = '';
  bool _isLoading = false;
  bool _obscurePassword = true;
  bool _obscureConfirmPassword = true;

  Future<void> _register() async {
    if (!_formKey.currentState!.validate()) return;
    
    _formKey.currentState!.save();
    setState(() => _isLoading = true);

    try {
      final response = await apiService.registerPatient(
        cin: cin,
        nom: nom,
        prenom: prenom,
        dateNaissance: dateNaissance,
        genre: genre,
        email: email,
        password: _passwordController.text,
        passwordConfirmation: _confirmPasswordController.text,
        telephone: telephone,
        adresse: adresse,
      );

      final prefs = await SharedPreferences.getInstance();
      await prefs.setString('authToken', response['token']);
      await prefs.setString('patientId', response['patient']['id'].toString());
      await prefs.setString('patientNom', response['patient']['nom']);
      await prefs.setString('patientEmail', response['patient']['email']);

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(response['message'] ?? 'Registration successful'),
          backgroundColor: Colors.green,
        ),
      );

      Navigator.pushReplacement(
        context,
        MaterialPageRoute(builder: (context) => MedicalDossierScreen()),
      );
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Registration failed: ${e.toString().replaceAll('Exception: ', '')}'),
          backgroundColor: Colors.red,
        ),
      );
    } finally {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  @override
  void dispose() {
    _passwordController.dispose();
    _confirmPasswordController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      appBar: AppBar(
        title: Text(
          'Create Account',
          style: TextStyle(
            color: Colors.blue[800],
            fontWeight: FontWeight.bold,
          ),
        ),
        centerTitle: true,
        backgroundColor: Colors.white,
        elevation: 0,
        automaticallyImplyLeading: false,
        iconTheme: IconThemeData(color: Colors.blue[800]),
      ),
      body: SingleChildScrollView(
        padding: EdgeInsets.symmetric(horizontal: 24, vertical: 16),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                'Personal Information',
                style: TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                  color: Colors.blue[800],
                ),
              ),
              SizedBox(height: 20),
              _buildTextFormField('CIN', Icons.credit_card, (value) => value!.isEmpty ? 'CIN is required' : null,
                  (value) => cin = value!),
              SizedBox(height: 16),
              Row(
                children: [
                  Expanded(
                    child: _buildTextFormField('First Name', Icons.person, (value) => value!.isEmpty ? 'Required' : null,
                        (value) => nom = value!),
                  ),
                  SizedBox(width: 16),
                  Expanded(
                    child: _buildTextFormField('Last Name', Icons.person_outline, (value) => value!.isEmpty ? 'Required' : null,
                        (value) => prenom = value!),
                  ),
                ],
              ),
              SizedBox(height: 16),
              _buildTextFormField('Birth Date (YYYY-MM-DD)', Icons.cake, (value) => value!.isEmpty ? 'Required' : null,
                  (value) => dateNaissance = value!),
              SizedBox(height: 16),
              DropdownButtonFormField<String>(
                value: genre,
                items: [
                  DropdownMenuItem(
                    value: 'H',
                    child: Text('Male', style: TextStyle(color: Colors.blue[800])),
                  ),
                  DropdownMenuItem(
                    value: 'F',
                    child: Text('Female', style: TextStyle(color: Colors.blue[800])),
                  ),
                ],
                onChanged: (value) => setState(() => genre = value!),
                decoration: InputDecoration(
                  labelText: 'Gender',
                  labelStyle: TextStyle(color: Colors.blue[700]),
                  prefixIcon: Icon(Icons.transgender, color: Colors.blue[700]),
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(10),
                    borderSide: BorderSide(color: Colors.blue[200]!),
                  ),
                  enabledBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(10),
                    borderSide: BorderSide(color: Colors.blue[200]!),
                  ),
                  filled: true,
                  fillColor: Colors.blue[50],
                ),
              ),
              SizedBox(height: 24),
              Text(
                'Contact Information',
                style: TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                  color: Colors.blue[800],
                ),
              ),
              SizedBox(height: 20),
              _buildTextFormField('Email', Icons.email, (value) {
                if (value!.isEmpty) return 'Email is required';
                if (!RegExp(r'^[^@]+@[^@]+\.[^@]+').hasMatch(value)) {
                  return 'Invalid email';
                }
                return null;
              }, (value) => email = value!, keyboardType: TextInputType.emailAddress),
              SizedBox(height: 16),
              _buildTextFormField('Phone Number', Icons.phone, (value) => value!.isEmpty ? 'Required' : null,
                  (value) => telephone = value!, keyboardType: TextInputType.phone),
              SizedBox(height: 16),
              _buildTextFormField('Address', Icons.home, (value) => value!.isEmpty ? 'Required' : null,
                  (value) => adresse = value!),
              SizedBox(height: 24),
              Text(
                'Account Security',
                style: TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                  color: Colors.blue[800],
                ),
              ),
              SizedBox(height: 20),
              _buildPasswordField('Password', _passwordController, _obscurePassword, () {
                setState(() => _obscurePassword = !_obscurePassword);
              }, (value) {
                if (value!.isEmpty) return 'Password is required';
                if (value.length < 6) return 'Minimum 6 characters';
                return null;
              }),
              SizedBox(height: 16),
              _buildPasswordField('Confirm Password', _confirmPasswordController, _obscureConfirmPassword, () {
                setState(() => _obscureConfirmPassword = !_obscureConfirmPassword);
              }, (value) {
                if (value!.isEmpty) return 'Please confirm password';
                if (value != _passwordController.text) return 'Passwords don\'t match';
                return null;
              }),
              SizedBox(height: 32),
              SizedBox(
                width: double.infinity,
                height: 50,
                child: ElevatedButton(
                  onPressed: _isLoading ? null : _register,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.blue[600],
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(10),
                    ),
                    elevation: 0,
                  ),
                  child: _isLoading
                      ? CircularProgressIndicator(color: Colors.white, strokeWidth: 2)
                      : Text(
                          'CREATE ACCOUNT',
                          style: TextStyle(
                            color: Colors.white,
                            fontWeight: FontWeight.bold,
                            fontSize: 16,
                          ),
                        ),
                ),
              ),
              SizedBox(height: 20),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildTextFormField(String label, IconData icon, FormFieldValidator<String> validator, 
      FormFieldSetter<String> onSaved, {TextInputType? keyboardType}) {
    return TextFormField(
      decoration: InputDecoration(
        labelText: label,
        labelStyle: TextStyle(color: Colors.blue[700]),
        prefixIcon: Icon(icon, color: Colors.blue[700]),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(10),
          borderSide: BorderSide(color: Colors.blue[200]!),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(10),
          borderSide: BorderSide(color: Colors.blue[200]!),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(10),
          borderSide: BorderSide(color: Colors.blue[400]!),
        ),
        filled: true,
        fillColor: Colors.blue[50],
      ),
      validator: validator,
      onSaved: onSaved,
      keyboardType: keyboardType,
    );
  }

  Widget _buildPasswordField(String label, TextEditingController controller, 
      bool obscureText, VoidCallback onToggleVisibility, FormFieldValidator<String> validator) {
    return TextFormField(
      controller: controller,
      obscureText: obscureText,
      decoration: InputDecoration(
        labelText: label,
        labelStyle: TextStyle(color: Colors.blue[700]),
        prefixIcon: Icon(Icons.lock, color: Colors.blue[700]),
        suffixIcon: IconButton(
          icon: Icon(
            obscureText ? Icons.visibility : Icons.visibility_off,
            color: Colors.blue[700],
          ),
          onPressed: onToggleVisibility,
        ),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(10),
          borderSide: BorderSide(color: Colors.blue[200]!),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(10),
          borderSide: BorderSide(color: Colors.blue[200]!),
        ),
        filled: true,
        fillColor: Colors.blue[50],
      ),
      validator: validator,
    );
  }
}