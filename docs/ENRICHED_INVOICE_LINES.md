# Enriched Invoice Lines

This document describes the purpose and structure of the `enriched_invoice_lines` table.

## Purpose

The `enriched_invoice_lines` table stores a copy of each invoice line after the pricing logic has been applied. This serves several purposes:

*   **Auditing:** It provides a historical record of how each line was priced, including the agreement version and pricing strategy used.
*   **Data Analysis:** The structured data in this table can be used for reporting and analysis of pricing and profitability.
*   **Debugging:** It helps in debugging pricing issues by providing a snapshot of the data at the time of processing.

## Schema

The table has the following columns:

*   `id`: Primary key.
*   `raw_line`: A JSON representation of the original invoice line data as it was received.
*   `nett_total`: The calculated total price of the line before VAT.
*   `vat_amount`: The amount of VAT applied to the line.
*   `line_total`: The final total price of the line, including VAT.
*   `currency`: The currency of the line total.
*   `agreement_version`: The version of the customer agreement used for pricing.
*   `agreement_type`: The type of agreement (e.g., `standard` or `custom`).
*   `pricing_strategy`: The pricing strategy used to calculate the line total (e.g., `standard`).
*   `processing_metadata`: A JSON object containing metadata about the processing, such as the timestamp.
*   `created_at` / `updated_at`: Timestamps.