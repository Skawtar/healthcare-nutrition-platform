import 'package:flutter/material.dart';
import 'package:flutter_application_1/services/auth/api_service.dart';
import 'package:flutter_application_1/services/auth/notifications_api.dart';
import 'package:intl/intl.dart';

const Color kPrimaryColor = Color(0xFFADD8E6);
const Color kAccentColor = Color(0xFF4CAF50);
const Color kTextColor = Color(0xFF333333);
const Color kUnreadNotificationColor = Color(0xFFE0F7FA);
const Color kErrorColor = Color(0xFFE53935);

class NotificationsScreen extends StatefulWidget {
  @override
  _NotificationsScreenState createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends State<NotificationsScreen> {
  late Future<List<dynamic>> _notificationsFuture;
  late ApiService _apiService;
  late NotificationsApi _notificationsApi;
  int _retryCount = 0;
  final int _maxRetries = 3;

  @override
  void initState() {
    super.initState();
    _apiService = ApiService();
    _notificationsApi = NotificationsApi(_apiService);
    _fetchNotifications();
  }

  void _fetchNotifications() {
    setState(() {
      _notificationsFuture = _fetchNotificationsWithRetry();
    });
  }

  Future<List<dynamic>> _fetchNotificationsWithRetry() async {
    try {
      final notifications = await _notificationsApi.getNotifications();
      _retryCount = 0; // Reset retry count on success
      return notifications;
    } catch (e) {
      if (_retryCount < _maxRetries) {
        _retryCount++;
        await Future.delayed(Duration(seconds: 1));
        return _fetchNotificationsWithRetry();
      } else {
        throw e; // After max retries, propagate the error
      }
    }
  }

  void _markAsRead(String notificationId) async {
    try {
      bool success = await _notificationsApi.markNotificationAsRead(notificationId);
      if (success) {
        _fetchNotifications();
      } else {
        _showSnackBar('Failed to mark notification as read');
      }
    } catch (e) {
      _showSnackBar('Error: ${e.toString()}');
    }
  }

  void _markAllAsRead() async {
    try {
      bool success = await _notificationsApi.markAllNotificationsAsRead();
      if (success) {
        _fetchNotifications();
      } else {
        _showSnackBar('Failed to mark all notifications as read');
      }
    } catch (e) {
      _showSnackBar('Error: ${e.toString()}');
    }
  }

  void _showSnackBar(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: kErrorColor,
      ),
    );
  }

  String _parseNotificationType(String fullType) {
    if (fullType.contains('ConsultationStatusChanged')) {
      return 'consultation_status_changed';
    } else if (fullType.contains('NewConsultationRequest')) {
      return 'new_consultation_request';
    }
    return 'general';
  }

  void _showNotificationDetails(Map<String, dynamic> notification) {
    bool isRead = notification['read_at'] != null;
    Map<String, dynamic> notificationData = notification['data'] ?? {};
    String parsedType = _parseNotificationType(notification['type'] ?? '');

    String dialogTitleMessage = notificationData['message'] ?? 'Notification Details';
    Widget contentWidget;

    String doctorName = notificationData['doctor_name'] ?? 'N/A';
    String doctorSpeciality = notificationData['doctor_speciality'] ?? 'N/A';
    String patientName = notificationData['patient_name'] ?? 'N/A';
    String consultationStatus = notificationData['status'] ?? 'N/A';
    String consultationDate = notificationData['consultation_date'] ?? 'N/A';
    String consultationTime = notificationData['consultation_time'] ?? 'N/A';
    String consultationId = notificationData['consultation_id']?.toString() ?? 'N/A';

    if (parsedType == 'consultation_status_changed') {
      contentWidget = Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisSize: MainAxisSize.min,
        children: [
          Text('Doctor: $doctorName ($doctorSpeciality)', style: TextStyle(fontSize: 15)),
          Text('Your consultation has been ${consultationStatus.toLowerCase()}.',
            style: TextStyle(fontWeight: FontWeight.bold, color: kAccentColor, fontSize: 15)),
          SizedBox(height: 8),
          Text('Consultation ID: $consultationId'),
          Text('Patient: $patientName'),
          Text('Scheduled Date: $consultationDate'),
          Text('Scheduled Time: $consultationTime'),
        ],
      );
    } else if (parsedType == 'new_consultation_request') {
      contentWidget = Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisSize: MainAxisSize.min,
        children: [
          Text('You have a new consultation request from $patientName.', style: TextStyle(fontSize: 15)),
          SizedBox(height: 8),
          Text('Consultation ID: $consultationId'),
          Text('Requested Doctor: $doctorName ($doctorSpeciality)'),
          Text('Requested Date: $consultationDate'),
          Text('Requested Time: $consultationTime'),
        ],
      );
    } else {
      contentWidget = Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisSize: MainAxisSize.min,
        children: [
          Text(dialogTitleMessage),
        ],
      );
    }

    final receivedAt = notification['created_at'] != null
        ? DateFormat('MMM dd, yyyy HH:mm')
            .format(DateTime.parse(notification['created_at']).toLocal())
        : 'N/A';
    final readAtFormatted = notification['read_at'] != null
        ? DateFormat('MMM dd, yyyy HH:mm')
            .format(DateTime.parse(notification['read_at']).toLocal())
        : 'Unread';

    showDialog(
      context: context,
      builder: (BuildContext context) {
        return AlertDialog(
          title: Text(dialogTitleMessage, style: TextStyle(color: kTextColor, fontWeight: FontWeight.bold)),
          content: SingleChildScrollView(
            child: ListBody(
              children: <Widget>[
                contentWidget,
                Divider(height: 20, thickness: 1),
                Text('Received: $receivedAt'),
                Text('Status: ${isRead ? "Read ($readAtFormatted)" : "Unread"}',
                    style: TextStyle(
                        fontWeight: isRead ? FontWeight.normal : FontWeight.bold,
                        color: isRead ? kTextColor : Colors.red)),
              ],
            ),
          ),
          actions: <Widget>[
            if (!isRead)
              TextButton(
                child: Text('Mark as Read', style: TextStyle(color: kPrimaryColor)),
                onPressed: () {
                  _markAsRead(notification['id']);
                  Navigator.of(context).pop();
                },
              ),
            TextButton(
              child: Text('Close', style: TextStyle(color: kTextColor)),
              onPressed: () {
                Navigator.of(context).pop();
              },
            ),
          ],
        );
      },
    );
  }

  Widget _buildErrorWidget(Object error) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.error_outline, color: kErrorColor, size: 48),
          SizedBox(height: 16),
          Text(
            'Failed to load notifications',
            style: TextStyle(color: kErrorColor, fontSize: 18),
          ),
          SizedBox(height: 8),
          Text(
            error.toString(),
            textAlign: TextAlign.center,
            style: TextStyle(color: kTextColor),
          ),
          SizedBox(height: 16),
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: kPrimaryColor),
            onPressed: _fetchNotifications,
            child: Text('Retry', style: TextStyle(color: Colors.white)),
          ),
        ],
      ),
    );
  }

  Widget _buildNotificationItem(Map<String, dynamic> notification) {
    bool isRead = notification['read_at'] != null;
    Map<String, dynamic> notificationData = notification['data'] ?? {};
    String parsedType = _parseNotificationType(notification['type'] ?? '');

    String titleDisplay = 'New Notification';
    String subtitleDisplay = 'Tap to view details';
    IconData leadingIcon = Icons.notifications;
    Color iconColor = isRead ? Colors.grey : kPrimaryColor;

    if (parsedType == 'consultation_status_changed') {
      final doctorName = notificationData['doctor_name'] ?? 'Doctor';
      final doctorSpeciality = notificationData['doctor_speciality'] ?? 'N/A';
      final status = notificationData['status'] ?? 'Updated';
      final date = notificationData['consultation_date'] ?? '';
      final time = notificationData['consultation_time'] ?? '';

      titleDisplay = '$doctorName ($doctorSpeciality) confirmed your consultation.';
      if (status.toLowerCase() == 'accepted' || status.toLowerCase() == 'confirmed') {
        titleDisplay = '$doctorName ($doctorSpeciality) ${status.toLowerCase()} your consultation.';
      } else {
        titleDisplay = '$doctorName ($doctorSpeciality) consultation $status.';
      }

      subtitleDisplay = 'Date: $date, Time: $time';
      leadingIcon = Icons.event_note;
    } else if (parsedType == 'new_consultation_request') {
      final doctorName = notificationData['doctor_name'] ?? 'Doctor';
      final patientName = notificationData['patient_name'] ?? 'Patient';
      final date = notificationData['consultation_date'] ?? '';
      final time = notificationData['consultation_time'] ?? '';

      titleDisplay = 'New Consultation Request from $patientName.';
      subtitleDisplay = 'With $doctorName on $date at $time';
      leadingIcon = Icons.assignment_ind;
    } else {
      titleDisplay = notificationData['message'] ?? 'General Notification';
      String createdAt = '';
      try {
        createdAt = notification['created_at'] != null
            ? DateFormat('MMM dd, yyyy HH:mm')
                .format(DateTime.parse(notification['created_at']).toLocal())
            : 'Unknown Date';
      } catch (e) {
        createdAt = 'Invalid Date';
      }
      subtitleDisplay = createdAt;
    }

    return Card(
      color: isRead ? Colors.white : kUnreadNotificationColor,
      margin: EdgeInsets.symmetric(horizontal: 10, vertical: 5),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
      elevation: 2,
      child: ListTile(
        leading: CircleAvatar(
          backgroundColor: isRead ? Colors.grey[200] : kPrimaryColor.withOpacity(0.7),
          child: Icon(leadingIcon, color: iconColor),
        ),
        title: Text(
          titleDisplay,
          style: TextStyle(
            fontWeight: isRead ? FontWeight.normal : FontWeight.bold,
            color: kTextColor,
          ),
          maxLines: 2,
          overflow: TextOverflow.ellipsis,
        ),
        subtitle: Text(
          subtitleDisplay,
          style: TextStyle(color: Colors.grey[600]),
        ),
        onTap: () {
          _showNotificationDetails(notification);
        },
        trailing: isRead
            ? null
            : Icon(Icons.circle, size: 10, color: kAccentColor),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Notifications', style: TextStyle(color: Colors.white)),
        backgroundColor: kPrimaryColor,
        iconTheme: IconThemeData(color: Colors.white),
        actions: [
          IconButton(
            icon: Icon(Icons.mark_email_read, color: Colors.white),
            onPressed: _markAllAsRead,
            tooltip: 'Mark all as read',
          ),
        ],
      ),
      body: FutureBuilder<List<dynamic>>(
        future: _notificationsFuture,
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) {
            return Center(child: CircularProgressIndicator(color: kPrimaryColor));
          } else if (snapshot.hasError) {
            return _buildErrorWidget(snapshot.error!);
          } else if (!snapshot.hasData || snapshot.data!.isEmpty) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(Icons.notifications_off, size: 48, color: Colors.grey),
                  SizedBox(height: 16),
                  Text('No notifications', style: TextStyle(color: Colors.grey)),
                ],
              ),
            );
          } else {
            return RefreshIndicator(
              onRefresh: () async {
                _fetchNotifications();
                return;
              },
              child: ListView.builder(
                itemCount: snapshot.data!.length,
                itemBuilder: (context, index) {
                  return _buildNotificationItem(snapshot.data![index]);
                },
              ),
            );
          }
        },
      ),
    );
  }
}