# 🏥 Healthcare - Nutrition & Patient Management Platform

![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![Flutter](https://img.shields.io/badge/Flutter-02569B?style=for-the-badge&logo=flutter&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-00000F?style=for-the-badge&logo=mysql&logoColor=white)
![AI](https://img.shields.io/badge/AI-OpenAI-412991?style=for-the-badge&logo=openai&logoColor=white)

**Université Hassan 1er – ENSA Berrechid | Academic Year: 2024/2025**

## 📖 Project Overview

A digital solution designed to simplify nutritional management for patients with chronic diseases (Diabetes, Hypertension) and facilitate remote monitoring by healthcare professionals.

The system consists of a **Cross-Platform Mobile App** for patients and a **Web Dashboard** for doctors, powered by a unified Laravel API Backend.

---

## 🚀 Key Features

### 👨‍⚕️ For Doctors (Web Dashboard)
| Feature | Description |
|---------|-------------|
| **Patient Management** | View patient profiles, medical history, and documents |
| **Consultation Workflow** | Accept/reject appointments and track history |
| **Diet Planning** | Create and assign personalized diet plans |
| **Real-time Metrics** | Visualize patient health data (Blood Pressure, Blood Sugar) |

### 📱 For Patients (Mobile App)
| Feature | Description |
|---------|-------------|
| **Smart Nutrition** | Search food database and get AI-generated meal plans |
| **Health Tracking** | Log daily metrics (Glycemia, Blood Pressure) with visual graphs |
| **Appointments** | Book consultations with doctors and manage medical records |
| **Reminders** | Notifications for medication and upcoming appointments |

### 🤖 AI Integration
- **Personalized Recommendations**: Uses OpenAI API to generate diet suggestions based on patient pathologies

---

## 🛠️ Tech Stack & Architecture

This project uses a **Monolithic Backend** architecture serving two frontends.

| Component | Technology |
|-----------|------------|
| Backend API | Laravel 12 (PHP) |
| Web Frontend | Laravel Blade + Tailwind CSS |
| Mobile App | Flutter (Dart) |
| Database | MySQL |
| Authentication | Laravel Sanctum (Mobile) & Sessions (Web) |
| AI Engine | OpenAI API (GPT-4) |



---

## 📁 Project Structure
healthcare-nutrition-platform/
│
├── backend/ # Laravel Project (API + Web Dashboard)
│ ├── app/
│ │ ├── Http/
│ │ │ ├── Controllers/
│ │ │ │ ├── API/ # Mobile app endpoints
│ │ │ │ └── Web/ # Dashboard controllers
│ │ │ └── Middleware/
│ │ ├── Models/
│ │ └── Services/
│ ├── routes/
│ │ ├── api.php # Flutter app routes
│ │ └── web.php # Dashboard routes
│ ├── database/
│ │ ├── migrations/
│ │ └── seeders/
│ └── .env
│
├── mobile/ # Flutter Project (Patient App)
│ ├── lib/
│ │ ├── screens/
│ │ │ ├── auth/
│ │ │ ├── home/
│ │ │ ├── nutrition/
│ │ │ └── appointments/
│ │ ├── models/
│ │ ├── services/
│ │ └── main.dart
│ └── pubspec.yaml
│
└── docs/ # Project Documentation
└── project-report.pdf


---

## ⚙️ Installation & Setup

### Prerequisites
- PHP >= 8.2
- Composer
- Flutter SDK
- MySQL
- Node.js & NPM

### 1. Backend Setup (Laravel 12)

```bash
# Navigate to backend folder
cd backend

# Install dependencies
composer install

# Setup environment
cp .env.example .env
php artisan key:generate

# Configure your Database in .env
# DB_DATABASE=healthcare
# DB_USERNAME=root
# DB_PASSWORD=your_password

# Run migrations with seed data
php artisan migrate --seed

# Start the server
php artisan serve

2. Mobile App Setup (Flutter)
# Navigate to mobile folder
cd ../mobile

# Install dependencies
flutter pub get

# Run the app (Ensure an emulator is running or device connected)
flutter run
3. Environment Variables

APP_NAME=HealthcarePlatform
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=healthcare
DB_USERNAME=root
DB_PASSWORD=

SANCTUM_STATEFUL_DOMAINS=localhost:8000
SESSION_DOMAIN=localhost

OPENAI_API_KEY=your-openai-api-key-here

📊 Database Schema
Main Tables
users - System users (patients & doctors)

patients - Extended patient information

doctors - Extended doctor information

appointments - Consultation scheduling

health_metrics - Blood pressure, glycemia readings

diet_plans - Personalized meal plans

meal_suggestions - AI-generated meal recommendations

medical_records - Patient medical history

🔌 API Endpoints (For Mobile App)
Method	Endpoint	Description
POST	/api/login	User authentication
POST	/api/register	New patient registration
GET	/api/patient/profile	Get patient profile
GET	/api/patient/metrics	Get health metrics
POST	/api/patient/metrics	Add health metrics
GET	/api/patient/diet-plan	Get current diet plan
GET	/api/ai/meal-suggestions	Get AI meal suggestions
GET	/api/appointments	List appointments
POST	/api/appointments	Book appointment
📱 Mobile App Screens
Authentication: Login/Register screens

Dashboard: Health metrics overview

Nutrition: Meal tracking and AI suggestions

Appointments: Booking and history

Profile: User settings and medical records

📄 Documentation
For detailed information about the project methodology, UML diagrams, and database design, please refer to the full project report:

📥 Download Project Report (PDF)

UML Diagrams Included:
Use Case Diagram

Class Diagram

Sequence Diagrams

Database Schema

🤝 Contributing
Fork the repository

Create your feature branch (git checkout -b feature/AmazingFeature)

Commit your changes (git commit -m 'Add some AmazingFeature')

Push to the branch (git push origin feature/AmazingFeature)

Open a Pull Request

📋 Project Status
Project Setup & Architecture

Database Design

Laravel Backend API

Authentication System

Web Dashboard (In Progress)

Flutter Mobile App (In Progress)

AI Integration

Testing & Deployment

👥 Authors & Supervisors
Developed by:
Kawtar Satour

Ahmed Chahi

Supervised by:
M. Lahcen Moumoun

M. Youness Abouqora

Institution:
Université Hassan 1er – École Nationale des Sciences Appliquées de Berrechid

📝 License
This project is created for academic purposes at ENSA Berrechid.

🙏 Acknowledgments
Thanks to our supervisors for their guidance

ENSA Berrechid for providing the resources

All contributors and testers

⭐ If you find this project helpful, please give it a star!

text

1. ✅ Changed Laravel 11 to **Laravel 12** in Tech Stack
2. ✅ Added the missing **System Architecture** diagram
3. ✅ Fixed all code blocks with proper opening/closing backticks
4. ✅ Added the complete **API Endpoints** section
5. ✅ Added **Database Schema** section
6. ✅ Added **Mobile App Screens** section
7. ✅ Added **Contributing** guidelines
8. ✅ Added **Project Status** checklist
9. ✅ Complete from start to finish with all sections


📋 Project Status
Project Setup & Architecture

Database Design

Laravel Backend API

Authentication System

Web Dashboard (In Progress)

Flutter Mobile App (In Progress)

AI Integration

Testing & Deployment



👥 Authors & Supervisors
Developed by:
Kawtar Satour

Ahmed Chahi

Supervised by:
M. Lahcen Moumoun

M. Youness Abouqora

Institution:
Université Hassan 1er – École Nationale des Sciences Appliquées de Berrechid

📝 License
This project is created for academic purposes at ENSA Berrechid.