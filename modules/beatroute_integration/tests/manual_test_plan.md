# Manual Test Plan for Beatroute Integration

## Overview
This document outlines the manual tests to verify that the Beatroute Integration module is working correctly, particularly the four sections showing live data from Beatroute.

## Prerequisites
1. Ensure the Beatroute Integration module is installed and configured
2. Ensure the API credentials are correctly set up in the settings
3. Ensure you have permission to view and edit Beatroute integration data

## Test Cases

### 1. Test API Connection
1. Go to Beatroute Integration Settings
2. Click on "Test Connection" button
3. Verify that the connection is successful

### 2. Test SKUs Section
1. Go to Beatroute Integration > SKUs
2. Verify that local SKUs are displayed correctly
3. Verify that live SKUs from Beatroute are displayed correctly in the "Live Beatroute SKUs" section
4. Test the "Sync SKUs" button to ensure it syncs data from Beatroute to local database
5. Test the "Sync to Local" option in the live data section to ensure it syncs the selected SKU

### 3. Test Customers Section
1. Go to Beatroute Integration > Customers
2. Verify that local customers are displayed correctly
3. Verify that live customers from Beatroute are displayed correctly in the "Live Beatroute Customers" section
4. Test the "Sync Customers" button to ensure it syncs data from Beatroute to local database
5. Test the "Sync to Local" option in the live data section to ensure it syncs the selected customer

### 4. Test Invoices Section
1. Go to Beatroute Integration > Invoices
2. Verify that local invoices are displayed correctly
3. Verify that live invoices from Beatroute are displayed correctly in the "Live Beatroute Invoices" section
4. Test the "Sync Invoices" button to ensure it syncs data from Beatroute to local database
5. Test the "Sync to Local" option in the live data section to ensure it syncs the selected invoice

### 5. Test Payments Section
1. Go to Beatroute Integration > Payments
2. Verify that local payments are displayed correctly
3. Verify that live payments from Beatroute are displayed correctly in the "Live Beatroute Payments" section
4. Test the "Sync Payments" button to ensure it syncs data from Beatroute to local database
5. Test the "Sync to Local" option in the live data section to ensure it syncs the selected payment

### 6. Test Edge Cases
1. Test with empty data (no SKUs, customers, invoices, or payments in Beatroute)
2. Test with large datasets to ensure performance is acceptable
3. Test with invalid or malformed data from Beatroute API

## Test Results
Document the results of each test case, including any issues encountered and their resolution.

## Conclusion
Summarize the overall test results and provide recommendations for any improvements or fixes needed.