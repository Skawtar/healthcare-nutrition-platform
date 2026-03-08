// lib/services/platform_file_uploader.dart
import 'dart:typed_data';
import 'package:http/http.dart' as http;
import 'package:flutter/foundation.dart' show kIsWeb;

// Abstract class to define the contract for file uploading
abstract class PlatformFileUploader {
  Future<http.MultipartFile> createMultipartFile({
    required String fieldName,
    required String fileName,
    String? filePath,
    Uint8List? fileBytes,
  });
}

// Concrete implementation for Web (using bytes)
class WebFileUploader implements PlatformFileUploader {
  @override
  Future<http.MultipartFile> createMultipartFile({
    required String fieldName,
    required String fileName,
    String? filePath, // Not used for web, will be null
    Uint8List? fileBytes,
  }) async {
    if (fileBytes == null) {
      // This case should ideally not happen if file_picker is correctly used for web
      throw Exception("fileBytes is required for web uploads. filePath is unavailable on web.");
    }
    return http.MultipartFile.fromBytes(
      fieldName,
      fileBytes,
      filename: fileName,
    );
  }
}

// Helper function to get the correct uploader for the *current* platform.
// This function will only be used if dart.library.io is FALSE (i.e., on web).
PlatformFileUploader getPlatformFileUploader() => WebFileUploader();