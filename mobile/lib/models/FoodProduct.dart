
class FoodProduct {
  final String barcode;
  final String name;
  final String? brand;
  final String? imageUrl;
  final double? energyKcalPer100g; // calories
  final String? productUrl; // URL to the product page on Open Food Facts

  FoodProduct({
    required this.barcode,
    required this.name,
    this.brand,
    this.imageUrl,
    this.energyKcalPer100g,
    this.productUrl,
  });

  factory FoodProduct.fromJson(Map<String, dynamic> json) {
    final nutriments = json['nutriments'] as Map<String, dynamic>?;
    
    // Attempt to parse energy-kcal_100g, handling potential type mismatches (int/double/string)
    double? parsedEnergy;
    final energyRaw = nutriments?['energy-kcal_100g'];
    if (energyRaw != null) {
      if (energyRaw is num) { // num covers both int and double
        parsedEnergy = energyRaw.toDouble();
      } else if (energyRaw is String) {
        parsedEnergy = double.tryParse(energyRaw);
      }
    }

    return FoodProduct(
      barcode: json['code'] ?? '',
      name: json['product_name'] ?? 'Unknown Product',
      brand: json['brands'] ?? 'N/A',
      imageUrl: json['image_url'],
      energyKcalPer100g: parsedEnergy,
      productUrl: json['url'],
    );
  }
}
