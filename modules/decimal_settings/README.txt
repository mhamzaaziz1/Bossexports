Decimal Settings Module
=======================

This module allows you to configure the system to use 2, 3, or 4 decimal places for calculations and display.

Installation:
1. Go to Setup -> Modules.
2. Activate "Decimal Settings".
3. Go to Setup -> Settings -> Decimal Settings.
4. Select the desired number of decimal places (e.g., 4) and Save.

IMPORTANT - Database Schema Update:
By default, Perfex CRM stores amounts with 2 decimal places (DECIMAL(15,2)).
To fully support 3 or 4 decimal places without rounding errors in storage, you must update your database schema.

A SQL script `sql_updates.sql` is provided in this module's directory.
Please run this script in your database (e.g., using phpMyAdmin or standard SQL client).
MAKE SURE TO BACKUP YOUR DATABASE BEFORE RUNNING ANY SQL MENUALY.

If you do not run this script, calculations will be performed with 4 decimals in memory, but stored as 2 decimals, leading to potential discrepancies when reloading saved items.
