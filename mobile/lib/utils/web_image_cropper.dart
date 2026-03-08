// web_image_cropper.dart
import 'dart:async';
import 'dart:html' as html;
import 'dart:js' as js;
import 'dart:typed_data';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';

class WebImageCropper {
  static Future<Uint8List?> cropImage(XFile imageFile) async {
    try {
      final bytes = await imageFile.readAsBytes();
      final blob = html.Blob([bytes]);
      final url = html.Url.createObjectUrl(blob);
      
      // Call our JavaScript cropper
      final croppedUrl = await js.context.callMethod('startImageCropper', [url]);
      html.Url.revokeObjectUrl(url);
      
      if (croppedUrl == null) return null;
      
      // Convert data URL to bytes
      final parts = croppedUrl.split(',');
      final decoded = html.window.atob(parts[1]);
      final length = decoded.length;
      final result = Uint8List(length);
      
      for (var i = 0; i < length; i++) {
        result[i] = decoded.codeUnitAt(i);
      }
      
      return result;
    } catch (e) {
      debugPrint('Web cropping error: $e');
      return null;
    }
  }
}