# How to show "5 LITER" (and CAN) for Oil items on Stock Report

The stock report shows oil product details in this format:
- **Line 1:** Quality • Grade • (e.g. 20W50 • G •)
- **Line 2:** Company • **5 LITER** • (e.g. KIXX • 5 LITER •)
- **Line 3:** CAN (packaging type)

"5 LITER" is taken from the **Unit** (and fallbacks) linked to the oil item. Configure it in any of these ways:

---

## 1. Unit name (recommended)

1. Go to **Units** (or wherever units are managed).
2. Create or edit a unit used for this oil, e.g. **"Can - 5 Liter"** or **"5 Liter Can"** or **"Can 5L"**.
3. On the **oil Item** (Edit Item), set **Unit** to this unit.
4. The report will parse the number + "liter/ltr/L" from the unit **name** and show e.g. **5 LITER**. If the name contains "can", **CAN** will show on line 3.

So: **Unit name** must include both:
- a number and the word **liter** (or **ltr** / **L**), and  
- optionally **can** for the CAN line.

---

## 2. Item “Filling” field

1. Edit the **oil Item**.
2. Set **Filling** (or “per-can liters”) to **5** (numeric).
3. If the unit name does not contain a liter value, the report uses **Filling** and displays **5 LITER**.

---

## 3. Item “Unit option”

1. Edit the **oil Item**.
2. If you use **Unit option** in format like `12_8_4` (e.g. pack sizes), the **last number** is treated as liters per can.
3. Example: `12_8_5` → **5 LITER** is shown.

---

## Summary

| Source        | Where to set                         | Example / result      |
|---------------|--------------------------------------|------------------------|
| Unit name     | Unit record → **name**               | "Can - 5 Liter" → 5 LITER + CAN |
| Filling       | Item → **Filling**                   | 5 → 5 LITER           |
| Unit option   | Item → **Unit option** (last number) | 12_8_5 → 5 LITER      |

The report uses them in this order: **Unit name** → **oil config liter_per_can** (from same logic) → **Filling** → **Unit option (last part)**.  
Ensure the oil item has **Unit** set (and the unit’s name contains “5” and “liter”), or **Filling** = 5, or **Unit option** ending in 5, so that **5 LITER** appears consistently from backend to report.
