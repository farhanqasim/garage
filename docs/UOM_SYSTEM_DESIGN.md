# Unit of Measurement (UOM) System Design
## Vyapar/Tally-Style Production-Ready Implementation

---

## 📋 TABLE OF CONTENTS

1. [Core Design Principles](#core-design-principles)
2. [Data Structure Design](#data-structure-design)
3. [Unit Categories & Types](#unit-categories--types)
4. [Item-Unit Relationship](#item-unit-relationship)
5. [Conversion Logic & Formulas](#conversion-logic--formulas)
6. [Item Creation Flow](#item-creation-flow)
7. [Stock & Billing Logic](#stock--billing-logic)
8. [Implementation Examples](#implementation-examples)
9. [Best Practices](#best-practices)

---

## 🎯 CORE DESIGN PRINCIPLES

### Principle 1: Single Base Unit Per Item
- **Every item has EXACTLY ONE primary (base) unit**
- Base unit is immutable once set (or requires explicit change process)
- All internal calculations are ALWAYS done in base unit
- Base unit determines the unit category (Weight, Quantity, Volume, Length)

### Principle 2: Multiple Secondary Units
- An item can have **multiple secondary units** for display and input
- Secondary units are **conversion-only** - they never store data
- User can input/display in any secondary unit, but system converts to base

### Principle 3: Conversion Factor Storage
- Conversion factors are stored in database (NOT hardcoded)
- Formula: `base_quantity = secondary_quantity × conversion_factor`
- Example: If base is "kg" and secondary is "g" (1000g = 1kg), factor = 0.001

### Principle 4: Automatic Conversion
- All database operations (stock, purchase, sale, adjustment) convert automatically
- No manual conversion needed in application code
- System handles all unit conversions transparently

---

## 📊 DATA STRUCTURE DESIGN

### 1. Units Table (Master Data)

```sql
units
├── id (Primary Key)
├── name (e.g., "Kilogram", "Piece", "Liter")
├── short_name (e.g., "kg", "Pcs", "L")
├── unit_category (weight, quantity, volume, length, area, time, other)
├── is_base_unit (true for kg, piece, liter, meter - the standard base units)
├── allow_decimal (boolean - can this unit have decimal values?)
├── sort_order (display order)
├── status (active/inactive)
└── timestamps
```

**Base Units (Standard):**
- Weight: `kg` (Kilogram)
- Quantity: `Pcs` (Piece)
- Volume: `L` (Liter)
- Length: `m` (Meter)

### 2. Item Unit Conversions Table (Core UOM System)

```sql
item_unit_conversions
├── id
├── item_id (Foreign Key → items.id)
├── unit_id (Foreign Key → units.id)
├── unit_role (enum: 'base' | 'secondary')
├── conversion_factor (decimal 20,8)
│   └── For base unit: always 1.00000000
│   └── For secondary: how many base units = 1 of this unit
├── display_order (integer)
├── is_active (boolean)
└── timestamps

UNIQUE CONSTRAINT: (item_id, unit_role) WHERE unit_role = 'base'
```

**Example Data:**
```
Item: "Battery 12V"
├── Base Unit: kg (conversion_factor = 1.0)
├── Secondary Unit 1: g (conversion_factor = 0.001)
└── Secondary Unit 2: Quintal (conversion_factor = 100)
```

---

## 🏷️ UNIT CATEGORIES & TYPES

### Weight Category
- **Base Unit:** Kilogram (kg)
- **Secondary Units:** Gram (g), Milligram (mg), Quintal, Metric Ton, Pound (lb)

### Quantity Category
- **Base Unit:** Piece (Pcs)
- **Secondary Units:** Dozen (12 Pcs), Box (customizable), Packet, Carton

### Volume Category
- **Base Unit:** Liter (L)
- **Secondary Units:** Milliliter (ml), Kiloliter (kL)

### Length Category
- **Base Unit:** Meter (m)
- **Secondary Units:** Centimeter (cm), Kilometer (km), Feet (ft)

---

## 🔗 ITEM-UNIT RELATIONSHIP

### Relationship Rules:
1. **One-to-One:** Item → Base Unit
2. **One-to-Many:** Item → Secondary Units (0 to N)

### Database Schema:
```php
// Item Model Relationships
item->baseUnit()              // Returns single Unit (unit_role = 'base')
item->secondaryUnits()        // Returns Collection of Units (unit_role = 'secondary')
item->allUnits()              // Returns all units (base + secondary)
item->unitConversions()       // Returns ItemUnitConversion models
```

---

## 🧮 CONVERSION LOGIC & FORMULAS

### Formula 1: Convert TO Base Unit
```
base_quantity = input_quantity × conversion_factor

Example:
- Input: 500 g
- Conversion Factor: 0.001 (1g = 0.001kg)
- Result: 500 × 0.001 = 0.5 kg
```

### Formula 2: Convert FROM Base Unit
```
output_quantity = base_quantity ÷ conversion_factor

Example:
- Base Quantity: 24 pieces
- Conversion Factor: 12 (1 box = 12 pieces)
- Result: 24 ÷ 12 = 2 boxes
```

### Formula 3: Convert Between Non-Base Units
```
Step 1: Convert source to base
base_qty = source_qty × source_conversion_factor

Step 2: Convert base to target
target_qty = base_qty ÷ target_conversion_factor
```

### Price Conversion:
```
price_per_base_unit = price_per_secondary_unit ÷ conversion_factor
price_per_secondary_unit = price_per_base_unit × conversion_factor
```

---

## 📝 ITEM CREATION FLOW

### Step 1: User Selects Item Type
- Stock Item or Service Item

### Step 2: User Selects Primary (Base) Unit
- **CRITICAL:** This becomes the item's base unit
- Cannot be changed later (or requires admin approval)
- Determines which category the item belongs to

### Step 3: User Adds Secondary Units (Optional)
- Can add multiple secondary units
- For each secondary unit, define conversion factor:
  - **Question:** "How many [base_unit] = 1 [secondary_unit]?"
  - **Example:** Base = kg, Secondary = g → Answer: 1000 (1kg = 1000g)
  - **Store:** conversion_factor = 0.001 (inverse: 1g = 0.001kg)

### Step 4: Opening Stock Entry
- User enters opening stock in any unit (base or secondary)
- System automatically converts to base unit
- Database stores ONLY base unit quantity

### Step 5: Price Entry
- User can enter price in any unit
- System converts to price per base unit for internal storage
- Display can show price in any unit

---

## 📦 STOCK & BILLING LOGIC

### Stock Entry (Purchase/Receipt)

**Scenario 1:**
- Item Base Unit: `kg`
- Purchase: `500 g`
- Conversion: `500 × 0.001 = 0.5 kg`
- **Stock Added:** 0.5 kg (stored in database)

**Scenario 2:**
- Item Base Unit: `piece`
- Purchase: `2 boxes` (1 box = 12 pieces)
- Conversion: `2 × 12 = 24 pieces`
- **Stock Added:** 24 pieces (stored in database)

### Stock Issue (Sale/Dispatch)

**Scenario 1:**
- Item Base Unit: `kg`
- Current Stock: `10.5 kg`
- Sale: `2500 g`
- Conversion: `2500 × 0.001 = 2.5 kg`
- **Stock Remaining:** `10.5 - 2.5 = 8 kg`

**Scenario 2:**
- Item Base Unit: `piece`
- Current Stock: `120 pieces`
- Sale: `5 boxes` (1 box = 12 pieces)
- Conversion: `5 × 12 = 60 pieces`
- **Stock Remaining:** `120 - 60 = 60 pieces`

### Stock Adjustment

**Automatic Conversion:**
- All adjustments (increase/decrease) are converted to base unit
- System never stores non-base unit quantities

---

## 💡 IMPLEMENTATION EXAMPLES

### Example 1: Weight-Based Item (Battery)

**Item Setup:**
```
Product: Car Battery 12V
Base Unit: kg
Secondary Units:
  - Gram (g): conversion_factor = 0.001
  - Quintal: conversion_factor = 100
```

**Purchase Entry:**
```
User Input: 2500 g
System Process:
  1. Convert to base: 2500 × 0.001 = 2.5 kg
  2. Add to stock: current_stock + 2.5 kg
  3. Store: on_hand = [base_unit_quantity]
```

**Sale Entry:**
```
User Input: 1500 g
System Process:
  1. Convert to base: 1500 × 0.001 = 1.5 kg
  2. Deduct from stock: current_stock - 1.5 kg
  3. Update: on_hand = [base_unit_quantity]
```

### Example 2: Quantity-Based Item (LED Bulbs)

**Item Setup:**
```
Product: LED Bulb 9W
Base Unit: piece
Secondary Units:
  - Box: conversion_factor = 12 (1 box = 12 pieces)
  - Carton: conversion_factor = 24 (1 carton = 24 pieces)
```

**Purchase Entry:**
```
User Input: 10 boxes
System Process:
  1. Convert to base: 10 × 12 = 120 pieces
  2. Add to stock: current_stock + 120 pieces
```

**Sale Entry:**
```
User Input: 3 cartons
System Process:
  1. Convert to base: 3 × 24 = 72 pieces
  2. Deduct from stock: current_stock - 72 pieces
```

### Example 3: Price Calculation

**Item Setup:**
```
Product: Oil 1L
Base Unit: liter
Secondary Unit: ml (conversion_factor = 0.001)
Price per liter: Rs. 500
```

**Price Display:**
```
Per Liter: Rs. 500 (base unit)
Per ml: Rs. 500 ÷ 1000 = Rs. 0.50
```

**Sale Entry:**
```
Quantity: 500 ml
Unit Price: Rs. 0.50/ml
Total: 500 × 0.50 = Rs. 250

Internal Calculation:
Base Quantity: 500 × 0.001 = 0.5 liters
Base Price: Rs. 500/liter
Total (base): 0.5 × 500 = Rs. 250 ✓
```

---

## ✅ BEST PRACTICES

### 1. Always Use Base Unit for Calculations
```php
// ❌ WRONG
$stock = $item->on_hand . ' ' . $request->unit;

// ✅ CORRECT
$baseQty = UnitConversionHelper::convertToBaseUnit($request->quantity, $item->id, $request->unit_id);
$item->on_hand += $baseQty;
```

### 2. Store Only Base Unit Quantities
```php
// Database should NEVER store:
// - on_hand = "500 g"  ❌
// - on_hand = "2 boxes"  ❌

// Database should ALWAYS store:
// - on_hand = 0.5 (in kg, if base unit is kg)  ✅
// - on_hand = 24 (in pieces, if base unit is piece)  ✅
```

### 3. Validate Unit Compatibility
```php
// Before conversion, validate:
$baseUnit = UnitConversionHelper::getBaseUnit($itemId);
$inputUnit = Unit::find($request->unit_id);

if ($baseUnit->unit_category !== $inputUnit->unit_category) {
    throw new Exception('Unit category mismatch!');
}
```

### 4. Display in User-Friendly Format
```php
// For display, convert to user's preferred unit:
$displayQty = UnitConversionHelper::convertFromBaseUnit(
    $item->on_hand, 
    $item->id, 
    $userPreferredUnitId
);

echo $displayQty . ' ' . $userPreferredUnit->short_name;
```

### 5. Handle Edge Cases
```php
// Zero or negative conversions
if ($conversionFactor <= 0) {
    throw new Exception('Invalid conversion factor!');
}

// Division by zero
if ($quantity == 0) {
    return 0; // Avoid unnecessary calculations
}
```

---

## 🔧 HELPER CLASS USAGE

### UnitConversionHelper Class

```php
use App\Helpers\UnitConversionHelper;

// Get base unit for item
$baseUnit = UnitConversionHelper::getBaseUnit($itemId);

// Get secondary units
$secondaryUnits = UnitConversionHelper::getSecondaryUnits($itemId);

// Convert to base unit
$baseQty = UnitConversionHelper::convertToBaseUnit(500, $itemId, $unitId); // 500g → 0.5kg

// Convert from base unit
$displayQty = UnitConversionHelper::convertFromBaseUnit(2.5, $itemId, $unitId); // 2.5kg → 2500g

// Convert between units
$result = UnitConversionHelper::convertBetweenUnits(500, $itemId, $sourceUnitId, $targetUnitId);

// Get conversion factor
$factor = UnitConversionHelper::getConversionFactor($itemId, $unitId);

// Format for display
$formatted = UnitConversionHelper::formatQuantity(2.5, $unit); // "2.5 kg"
```

---

## 📌 KEY TAKEAWAYS

1. **One Base Unit Per Item** - Always, no exceptions
2. **All Calculations in Base Unit** - Internal system never uses secondary units
3. **Automatic Conversion** - User never needs to manually convert
4. **Store Conversion Factors** - Not hardcoded, stored in database
5. **Category-Based Units** - Weight, Quantity, Volume, Length are separate
6. **Scalable Design** - Add new units without code changes

---

**This system works exactly like Vyapar/Tally internally handles units!**

