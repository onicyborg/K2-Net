# Resolved

All Excel export issues fixed:

1. **Export empty data**: `invoicesData`, `customersData`, `revenueData` return nested arrays. Fixed by extracting `['data']` / `['monthly']` and converting to array with `->toArray()`.

2. **Zero values not appearing**: PhpSpreadsheet 5.9 skips writing cells with `0` value using `FromCollection`. Fixed by replacing all three exports with manual `Spreadsheet` approach using `setCellValue()`, which correctly writes `0`.
