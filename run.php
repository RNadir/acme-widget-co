<?php

declare(strict_types=1);

class Basket
{
  private array $productCatalogue;
  private array $deliveryRules;
  private array $specialOffers;
  private array $items = [];

  public function __construct(array $productCatalogue, array $deliveryRules, array $specialOffers)
  {

    // Validators
    $this->validateProductCatalogue($productCatalogue);
    $this->validateDeliveryRules($deliveryRules);
    $this->validateSpecialOffers($specialOffers, $productCatalogue);

    $this->productCatalogue = $productCatalogue;
    $this->deliveryRules = $deliveryRules;
    $this->specialOffers = $specialOffers;
  }

  public function add(string $productCode): void
  {
    if (!isset($this->productCatalogue[$productCode])) {
      throw new InvalidArgumentException('Product code ' . $productCode . ' does not exist in the catalogue.');
    }

    $this->items[] = $productCode;
  }

  public function total(): float
  {
    $total = 0.0;
    $itemCounts = [];

    // Initial counts & sums
    foreach ($this->items as $item) {

      // Sum up price
      $total += $this->productCatalogue[$item];

      // Count up particular items
      $itemCounts[$item] = ($itemCounts[$item] ?? 0) + 1;
    }

    // Apply Special Offers
    foreach ($this->specialOffers as $productCode => $offerConfig) {

      // Product from special offer not used
      if (!isset($itemCounts[$productCode])) continue;

      // Min buy not satisfied
      if ($itemCounts[$productCode] < $offerConfig['min_buy']) continue;
      
      // How meny 'sets' of products are left to be applied to 
      $applicableCount = min(intdiv($itemCounts[$productCode], $offerConfig['min_buy']), $offerConfig['max_apply_count']);

      // No more applicable 'sets'
      if ($applicableCount < 1) continue;

      // Lower total from percentage
      $total -= ($this->productCatalogue[$productCode] * ($offerConfig['percentage'] / 100)) * $applicableCount;

    }

    // Apply Delivery Charges
    $deliveryCharge = 0;
    foreach ($this->deliveryRules['tiers'] as $tier) {
      if ($total >= $tier['min_total']) {
        $deliveryCharge = $tier['cost'];
      }
    }

    $total += $deliveryCharge;
    return round($total, 2);
  }

  private function validateProductCatalogue(array $productCatalogue): void
  {
    foreach ($productCatalogue as $code => $price) {
      if (!is_string($code) || !is_float($price) && !is_int($price) || $price <= 0) {
        throw new InvalidArgumentException('Invalid product in product catalogue: ' . $code . '. Price must be a positive number.');
      }
    }
  }
  private function validateDeliveryRules(array $deliveryRules): void
  {
    if (!isset($deliveryRules['tiers']) || !is_array($deliveryRules['tiers'])) {
      throw new InvalidArgumentException('Invalid delivery rules format. Must have a "tiers" key with an array of rules.');
    }

    foreach ($deliveryRules['tiers'] as $tier) {
      if (!isset($tier['min_total'], $tier['cost']) || !is_numeric($tier['min_total']) || !is_numeric($tier['cost'])) {
        throw new InvalidArgumentException('Invalid delivery tier structure. Each tier must have "min_total" and "cost" as numbers.');
      }
    }
  }
  private function validateSpecialOffers(array $specialOffers, array $productCatalogue): void
  {
    foreach ($specialOffers as $productCode => $offer) {

      if (!isset($productCatalogue[$productCode])) {
        throw new InvalidArgumentException('Special offer defined for non-existent product: ' . $productCode);
      }

      if (!isset($offer['percentage'], $offer['min_buy'], $offer['max_apply_count'])) {
        throw new InvalidArgumentException('Special offer for ' . $productCode . ' must have "percentage", "min_buy", and "max_apply_count".');
      }

      if (!is_int($offer['min_buy']) || $offer['min_buy'] <= 0) {
        throw new InvalidArgumentException('Invalid "min_buy" for product ' . $productCode . '. Must be a positive integer.');
      }

      if (!is_int($offer['max_apply_count']) || $offer['max_apply_count'] <= 0) {
        throw new InvalidArgumentException('Invalid "max_apply_count" for product ' . $productCode . '. Must be a positive integer.');
      }

      if (!is_int($offer['percentage']) || $offer['percentage'] <= 0 || $offer['percentage'] > 100) {
        throw new InvalidArgumentException('Invalid "percentage" for product ' . $productCode . '. Must be an integer between 1 and 100.');
      }
    }
  }
}

// Config
$catalogue = [
  'R01' => 32.95,
  'G01' => 24.95,
  'B01' => 7.95
];
$specialOffers = [
  'R01' => [
    'percentage' => 50,
    'min_buy' => 2,
    'max_apply_count' => 1
  ]
];
$deliveryRules = [
  'tiers' => [
    ['min_total' => 0, 'cost' => 4.95],
    ['min_total' => 50, 'cost' => 2.95],
    ['min_total' => 90, 'cost' => 0]
  ]
];

// Test cases
$basket = new Basket($catalogue, $deliveryRules, $specialOffers);
$basket->add('B01');
$basket->add('G01');
echo "Total: $" . $basket->total() . "\n";

$basket = new Basket($catalogue, $deliveryRules, $specialOffers);
$basket->add('R01');
$basket->add('R01');
echo "Total: $" . $basket->total() . "\n";

$basket = new Basket($catalogue, $deliveryRules, $specialOffers);
$basket->add('R01');
$basket->add('G01');
echo "Total: $" . $basket->total() . "\n";

$basket = new Basket($catalogue, $deliveryRules, $specialOffers);
$basket->add('B01');
$basket->add('B01');
$basket->add('R01');
$basket->add('R01');
$basket->add('R01');
echo "Total: $" . $basket->total() . "\n";
