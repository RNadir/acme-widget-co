# Raw PHP Basket System

This project is a **raw PHP implementation** of a simple shopping basket system. It provides a structure to handle products, special offers, and delivery rules in a highly configurable way.

---

## 🚀 **Features**
- Product Catalogue Handling
- Special Offers
  - Percentage-based discounts (e.g., 50% off).
  - Limited application of offers (`max_apply_count`).
- Delivery Rules
  - Tier-based pricing based on the total amount.
- Validations for all configurations to ensure structure integrity.
- Clean separation of logic via **helper methods** for better readability.

---

## 🔨 **Installation**
1. Clone the repository.
2. Place the PHP files in your local server environment (e.g., XAMPP, MAMP).
3. Run the `run.php` file to test.

---

## 📜 **Usage**

### **Product Catalogue**
```php
$catalogue = [
  'R01' => 32.95,
  'G01' => 24.95,
  'B01' => 7.95
];
```

### **Special Offers**
```php
$specialOffers = [
  'R01' => [
    'percentage' => 50,
    'min_buy' => 2,
    'max_apply_count' => 1
  ]
];
```

### **Delivery Rules**
```php
$deliveryRules = [
  'tiers' => [
    ['min_total' => 0, 'cost' => 4.95],
    ['min_total' => 50, 'cost' => 2.95],
    ['min_total' => 90, 'cost' => 0]
  ]
];
```

### **Example Usage**
```php
$basket = new Basket($catalogue, $deliveryRules, $specialOffers);
$basket->add('B01');
$basket->add('G01');
echo "Total: $" . $basket->total() . "\n";
```

---

## 💡 ** Explanation **

1. I made sure that productCatalogue, deliveryRules, and specialOffers are highly customizable, making the class highly reusable.
2. The Basket class implements a modular approach where each responsibility (product handling, special offers, delivery rules) is separated for clarity and maintainability.
3. The system is designed to validate configurations upfront (in the constructor) to prevent runtime errors caused by invalid configurations. ( that is why there are no validations in the total method )
4. The deliveryRules can be easily expanded with more complex rules such as location-based pricing or user-specific conditions.

---