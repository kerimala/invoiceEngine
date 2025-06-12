# Technical Architecture Specification – Invoice Engine

## 1. Introduction 

This document outlines the technical architecture of the **Invoice Engine**, a Python-based microservice developed for the Greenway project. Its primary function is to compute detailed invoice lines from customer-specific agreements and shipment orders.

### 1.1 Business Goal

The **Invoice Engine** aims to reduce manual billing effort, minimize invoicing errors, and support business scalability by automating the application of complex pricing agreements to completed shipments.


---

## 2. Scope & Out of Scope

### In Scope
- Parsing carrier invoices
- Supporting "dry runs" of carrier (invoices for testing and previewing billing outcomes without persistence).
- Accepting order and agreement input via API
- Matching invoices to customer orders
- Applying pricing logic from agreements
- Generating structured invoice lines
- Creating invoice PDFs
- Persisting invoice data for reporting and audit
- Each services includes standardized error handling

### Out of Scope
- Email notification delivery
- UI/dashboard components
- User/role management
- Security architecture
- Infrastructure/hosting decisions

---

## 3. Component Overview


The Invoice Engine is a group of stateless, one-purpose only, micro-services connected through an event bus.  
Each service owns exactly one responsibility and creates an immutable event after finishing its job.  
This split allows independent deployment, scaling and clear error localisation.
### 3.1 Overall Component Diagram
[![](https://img.plantuml.biz/plantuml/svg/VP9HQuCm58NV1V_3nIVxa3ymIgcTTWYJYZBsCFQGnbiRgab9OlSmzj-Nc6xKKdoyELzEJaworeOgC_HT68J1cR8JRH6YN5maPmYPQBb0Y5FPKouM4No70S37LIPRtY79HOlQV0BLa6zpM2wPxCzIe32hrOGkgTAeF5ZES0Krx8WAuk9ruUdY0LqgphXeYOyuScKsuvfeZVs--mkGFB9CjwH2qQ2wKcKrOrYFf7UhxxaVLoVhvD10b5ti8cm67JbsyrQ7GKCDtLDjczufQm8rgelr785TbTu2IQiGU-Tm2oKlrIsKIZPunaZbYNJtx3Au9o6D_19avO-srK_dOZhR4gTwSgTNTthzFzlay0JsGvb1l-F-AEKfNhWGJY2AjqT3_2OR7qY71pkNcVUyoZRsw66l4P-RmxXzRPiUV-Wl)](https://editor.plantuml.com/uml/VP9HQuCm58NV1V_3nIVxa3ymIgcTTWYJYZBsCFQGnbiRgab9OlSmzj-Nc6xKKdoyELzEJaworeOgC_HT68J1cR8JRH6YN5maPmYPQBb0Y5FPKouM4No70S37LIPRtY79HOlQV0BLa6zpM2wPxCzIe32hrOGkgTAeF5ZES0Krx8WAuk9ruUdY0LqgphXeYOyuScKsuvfeZVs--mkGFB9CjwH2qQ2wKcKrOrYFf7UhxxaVLoVhvD10b5ti8cm67JbsyrQ7GKCDtLDjczufQm8rgelr785TbTu2IQiGU-Tm2oKlrIsKIZPunaZbYNJtx3Au9o6D_19avO-srK_dOZhR4gTwSgTNTthzFzlay0JsGvb1l-F-AEKfNhWGJY2AjqT3_2OR7qY71pkNcVUyoZRsw66l4P-RmxXzRPiUV-Wl)
[![](https://img.plantuml.biz/plantuml/svg/XPJFZfim4CRlVeefboO7zW6gLRHiI2gaeY1erRDZ9iHQi95j8bLLtxtZK0WfHCa5o_FxllcJWJVhk75TKqEYBbeAtAbAAjpgfjKAbOibhmnls2TtnWQXhRbKZ3dfQXmKq4jW5Uk6eciOnJ2eMSl51wyGWWUQ07up034A1oxSonh3H5LeNG33x_Lr93SRu1QIVR8ipxYnQ6xA1_9iMPro9yveO9L-MmUFrUczEZDII5M5VOlFScUxvToHjTWSw_lYvxcZ_tB7-p17LQAPTRsKEr_ENLmTdawjO7yO4xNkIXyFTXTlyjxPEo61xpyALdB7ZzpI1XmCUKE1vaAfWWbsoEC95bK6QUF8SOeI5dtD9rYiHTTJJflHZh3ui6NiCuGXZ7LHgqgXS58hIm56MqEFpRM-98FRERsTiztaFfhSvvFxudQc9iPpdpwcMg55OwLrg6ZrkIf1yBe-qZjnhMFmefBX2rZVoAejJ-lHs9lw07UT_Od7izOVg_LWZwt_jvIGQ44hcCrjwFJbPUDdHqOdfBKUENBopT_21fsHUC4x8dr6-47DYN_lGjxLQBrDj6ZJh_glhLOdMNdEJsJEVSU6lZ-kTogMY0ATetGUO6yq5lgkiRy0)](https://editor.plantuml.com/uml/XPJFZfim4CRlVeefboO7zW6gLRHiI2gaeY1erRDZ9iHQi95j8bLLtxtZK0WfHCa5o_FxllcJWJVhk75TKqEYBbeAtAbAAjpgfjKAbOibhmnls2TtnWQXhRbKZ3dfQXmKq4jW5Uk6eciOnJ2eMSl51wyGWWUQ07up034A1oxSonh3H5LeNG33x_Lr93SRu1QIVR8ipxYnQ6xA1_9iMPro9yveO9L-MmUFrUczEZDII5M5VOlFScUxvToHjTWSw_lYvxcZ_tB7-p17LQAPTRsKEr_ENLmTdawjO7yO4xNkIXyFTXTlyjxPEo61xpyALdB7ZzpI1XmCUKE1vaAfWWbsoEC95bK6QUF8SOeI5dtD9rYiHTTJJflHZh3ui6NiCuGXZ7LHgqgXS58hIm56MqEFpRM-98FRERsTiztaFfhSvvFxudQc9iPpdpwcMg55OwLrg6ZrkIf1yBe-qZjnhMFmefBX2rZVoAejJ-lHs9lw07UT_Od7izOVg_LWZwt_jvIGQ44hcCrjwFJbPUDdHqOdfBKUENBopT_21fsHUC4x8dr6-47DYN_lGjxLQBrDj6ZJh_glhLOdMNdEJsJEVSU6lZ-kTogMY0ATetGUO6yq5lgkiRy0)
[![](https://img.plantuml.biz/plantuml/svg/XLLBZzem4BxxLqov50vixzKARPQ4WjG8bBJQimb3i4Zio7POhQh-zuwJpQi98fSOVuzvn6DUZENQbda6EtbJ8i50faBYMkM5aYYjBtYgUVucx2NGMcd3ljWBvWX5neLavYfamOa06QdiHu6WjBqengom6JQcKBl2t5SvoHRG-iDd07Z_1bK0s79v8hEKiO8dLvuYpEwupE0F0qXQguQtt8WCbpkPeh4pQ9xdvvQvMW4tiDlifkK7hWtgJdb7VfXMXzmc5zGm3tykPlVLuL1zq2AXefTrYOzY7uBfs9unc1-pOVBZsDv_iVtDCa9vGZsgUYfsD1qxhXmUTYqUgdlVV1nyf0wsx2zZIQRAayDdmSsPl9Pce2JYw--OdRZbHsxe_EmrPGOnwXibEkjO-yZliRnK8vriCkmnYUNzY7eiNoLbJR9ADsR4ylThnhx2SWbjKbIdb9XOeQGXWD61ezUgIcvtW6uTTkj3qDltUljHRp_krbH2kwx3-vGg5AYDC1ObJGtr5XAUPKSQfogFnejIXkzWN1tpudHUjCREr07SbkODZnUbhlD5Ou-5-z0o4MX0IHXrhQdqwMdbMaT6PwI9Q7dav8hlO8rM2xpXW14-OhX6ZHcVOnIL6HfduqtQr5FoiLRoB5B7SnqPytvXJhUShPtYAKQCrb8uqtQdFixdJBsRVX-2BOcKhA-aYjFhmYTOtJej5WwmwavfiARoHbUNt2gz0ThS2Aqu6i3rsBVQU02wYMuWza8_w6_Y7m00)](https://editor.plantuml.com/uml/XLLBZzem4BxxLqov50vixzKARPQ4WjG8bBJQimb3i4Zio7POhQh-zuwJpQi98fSOVuzvn6DUZENQbda6EtbJ8i50faBYMkM5aYYjBtYgUVucx2NGMcd3ljWBvWX5neLavYfamOa06QdiHu6WjBqengom6JQcKBl2t5SvoHRG-iDd07Z_1bK0s79v8hEKiO8dLvuYpEwupE0F0qXQguQtt8WCbpkPeh4pQ9xdvvQvMW4tiDlifkK7hWtgJdb7VfXMXzmc5zGm3tykPlVLuL1zq2AXefTrYOzY7uBfs9unc1-pOVBZsDv_iVtDCa9vGZsgUYfsD1qxhXmUTYqUgdlVV1nyf0wsx2zZIQRAayDdmSsPl9Pce2JYw--OdRZbHsxe_EmrPGOnwXibEkjO-yZliRnK8vriCkmnYUNzY7eiNoLbJR9ADsR4ylThnhx2SWbjKbIdb9XOeQGXWD61ezUgIcvtW6uTTkj3qDltUljHRp_krbH2kwx3-vGg5AYDC1ObJGtr5XAUPKSQfogFnejIXkzWN1tpudHUjCREr07SbkODZnUbhlD5Ou-5-z0o4MX0IHXrhQdqwMdbMaT6PwI9Q7dav8hlO8rM2xpXW14-OhX6ZHcVOnIL6HfduqtQr5FoiLRoB5B7SnqPytvXJhUShPtYAKQCrb8uqtQdFixdJBsRVX-2BOcKhA-aYjFhmYTOtJej5WwmwavfiARoHbUNt2gz0ThS2Aqu6i3rsBVQU02wYMuWza8_w6_Y7m00)
### 3.2 Pre-Shipment Component Diagram
[![](https://img.plantuml.biz/plantuml/svg/XP9DQiCm44Rt0jrXcbMxQ0vGWabnkoZqZsHPl13EXorgoKPIqaKfv23jvNAIAh8bR4ofLa9wtzFCeBLrmhYzQkZLO5lBJa6xl5LTg_tcJehA2CNPXQkXG5qZfERCITVW7BYE2KeHIXdEEb6-YmgqUD27LfQWUrr9ZGLzSaRqzc9sCBH12Nc0AMYwLgioEhECXAM0Hpe8Sc6Cz8hRoW2XZ0sSFmcsMNwW4pnHRD6WTAsXowV7g-Al4WOwmhsXRYrql2h-QQAd1vYph_EO9etHSfdDIgKRRzfRUCVf-FsCZtuOf-CFPpDs94XTJV89jMD1OaD-N6ReP6B8QuWEriLtWeUpDVJEVoNEVW40)](https://editor.plantuml.com/uml/XP9DQiCm44Rt0jrXcbMxQ0vGWabnkoZqZsHPl13EXorgoKPIqaKfv23jvNAIAh8bR4ofLa9wtzFCeBLrmhYzQkZLO5lBJa6xl5LTg_tcJehA2CNPXQkXG5qZfERCITVW7BYE2KeHIXdEEb6-YmgqUD27LfQWUrr9ZGLzSaRqzc9sCBH12Nc0AMYwLgioEhECXAM0Hpe8Sc6Cz8hRoW2XZ0sSFmcsMNwW4pnHRD6WTAsXowV7g-Al4WOwmhsXRYrql2h-QQAd1vYph_EO9etHSfdDIgKRRzfRUCVf-FsCZtuOf-CFPpDs94XTJV89jMD1OaD-N6ReP6B8QuWEriLtWeUpDVJEVoNEVW40)

### 3.3 Post-Shipment Component Diagram

[![](https://img.plantuml.biz/plantuml/svg/VP9DJiCm48NtaNA7KLQmo0LOe2PDArLgbQfTeYxSP4Yj9dOqTX4LGk8Et92Ju9y0TN_OpdplpOoVHqepqeUkZIErvw9dn3fekK1zx14awPsCevvLUzOW93Fn8dc5C16DGU3hunEMKkbqlUDzXq8dy1P704y3bqvvY-bCDAZq1jBqyQ-pPAIGDl00bbbhILW7qXyWK2sOhdP8SBUOtrljc15nfA4zDpjDNZMnIf-PXJfdkIHUST6a_XHhvg7wnCnvd8F1GfbIs6rRVuabB_LNKBz0CKrNA6eahnwu5RBww0S7G31sxSfkPlqyCERYzpYgcMPRfanM9PcgWY9FW2aeY_oqcJM4Q1Fx1J8BLBBhfJ1bIF9MRi7cOe9tG3ulGwAGrS5QECepHJcEepYwdRyt-ISNj-VLDyzyv-9HtCoHARbF_0q0)](https://editor.plantuml.com/uml/VP9DJiCm48NtaNA7KLQmo0LOe2PDArLgbQfTeYxSP4Yj9dOqTX4LGk8Et92Ju9y0TN_OpdplpOoVHqepqeUkZIErvw9dn3fekK1zx14awPsCevvLUzOW93Fn8dc5C16DGU3hunEMKkbqlUDzXq8dy1P704y3bqvvY-bCDAZq1jBqyQ-pPAIGDl00bbbhILW7qXyWK2sOhdP8SBUOtrljc15nfA4zDpjDNZMnIf-PXJfdkIHUST6a_XHhvg7wnCnvd8F1GfbIs6rRVuabB_LNKBz0CKrNA6eahnwu5RBww0S7G31sxSfkPlqyCERYzpYgcMPRfanM9PcgWY9FW2aeY_oqcJM4Q1Fx1J8BLBBhfJ1bIF9MRi7cOe9tG3ulGwAGrS5QECepHJcEepYwdRyt-ISNj-VLDyzyv-9HtCoHARbF_0q0)

[![](https://img.plantuml.biz/plantuml/svg/XPHHRgim54J_lOghVBKVRA2gK6a9KgIY85Y12pS41MnaErMhwabl3zrXL_BkDQOlfLJDNvWpdinWvSuwQTnvw8KzTMgKHWxGTmhTwuYWZJjg8KPPdsIBiDcfPztL25lL4W8VVz-XqDP5rR4R1rHk0sy2eDR3g1Lz_JH4MTTZj5CjMcBcp-tj9nd782tiijswlP36eldl_C5UhBjpwUeZ6hZA7wytFxlph-x2T7MdscZg-bjsiLtFJgp5uQd_-kE_PoUBx3IBIbGDcc-jrxBBzUpAd_3hQfKWEqqFKHJJXW0tmA-hSjfWmn8zybBEqeCqfaCJCXvfcksBCx9s4vjFRB5bTXg-kM09OSaVbp32Aoy9LlmX9HD1Ar4shsmlWxtozkOGTch47o4QwUIJj7HJzuOUMaZlGtqgka_lgVsCnF45dRG6aIzmS12TBEcBWNaUgD0yJ-iIdLMBPI_nLDTdFXpyf1lcq_t3WXVp6yXyCSievvHue7QgngjhCQ_BJMwWr_fq7drHqkPP4wyTa9OFDxWyzsYP2CjnTWYWUqQ2kAC9wH_W7m00)](https://editor.plantuml.com/uml/XPHHRgim54J_lOghVBKVRA2gK6a9KgIY85Y12pS41MnaErMhwabl3zrXL_BkDQOlfLJDNvWpdinWvSuwQTnvw8KzTMgKHWxGTmhTwuYWZJjg8KPPdsIBiDcfPztL25lL4W8VVz-XqDP5rR4R1rHk0sy2eDR3g1Lz_JH4MTTZj5CjMcBcp-tj9nd782tiijswlP36eldl_C5UhBjpwUeZ6hZA7wytFxlph-x2T7MdscZg-bjsiLtFJgp5uQd_-kE_PoUBx3IBIbGDcc-jrxBBzUpAd_3hQfKWEqqFKHJJXW0tmA-hSjfWmn8zybBEqeCqfaCJCXvfcksBCx9s4vjFRB5bTXg-kM09OSaVbp32Aoy9LlmX9HD1Ar4shsmlWxtozkOGTch47o4QwUIJj7HJzuOUMaZlGtqgka_lgVsCnF45dRG6aIzmS12TBEcBWNaUgD0yJ-iIdLMBPI_nLDTdFXpyf1lcq_t3WXVp6yXyCSievvHue7QgngjhCQ_BJMwWr_fq7drHqkPP4wyTa9OFDxWyzsYP2CjnTWYWUqQ2kAC9wH_W7m00)

---

## 4. Technical Architecture

# Agreements: Versioning and Extensibility

Agreements are versioned, immutable configuration objects that encapsulate all business rules and settings relevant to invoice processing. Each Agreement version is uniquely identified and may include, but is not limited to:

- Pricing margins and rules
- Language and localization specifications  
- Number formatting and conversions
- Branding elements (e.g., logo inclusion)
- Any other business-specific configuration

## Key Principles

### Versioning
Every Agreement is versioned. Once created, a version is immutable and must be referenced explicitly by all processing components and events.

### Extensibility
The Agreement schema is intentionally flexible and extensible, allowing for the addition of new configuration fields without impacting existing versions or historical data.

### Historical Accuracy
All invoice processing must use the Agreement version that was in effect at the time of the invoice's creation or event occurrence, ensuring full auditability and reproducibility.

### Component Responsibility
The Agreement Service is responsible for resolving and providing the correct Agreement version for a given invoice or event. All downstream components (e.g., Pricing Engine, PDF Renderer, Invoice Assembler) must use the configuration from the referenced Agreement version, not a default or latest version.

### Event Sourcing
All events that depend on Agreement data must include the Agreement version (or a resolvable reference) in their payloads.

### Microservices

Each service is designed to be stateless, microservice-ready, and independently deployable. Here's a brief technology overview:

| Service                  | Input Event                                           | Output Event                  | One-Line Responsibility                                                                         |
| ------------------------ | ----------------------------------------------------- | ----------------------------- | ----------------------------------------------------------------------------------------------- |
| **invoice-file-ingest**  | _InvoiceFileReceived_ (raw file drop / webhook / API) | _FileStored_                  | Accept carrier invoice files, store them and emit reference metadata.                           |
| **invoice-parser**       | _FileStored_                                          | _CarrierInvoiceLineExtracted_ | Parse & validate each file, convert line items to canonical JSON.                               |
| **pricing-engine**       | _MatchedInvoiceLine_                                  | _PricedInvoiceLine_           | Apply customer agreement rules and calculate final price for the line.                          |
| **invoice-assembler**    | _PricedInvoiceLine_                                   | _InvoiceReady_                | Group lines per customer & invoice period, compute totals, persist DB rows.                     |
| **pdf-renderer**         | _InvoiceReady_                                        | _PdfRendered_                 | Render PDF with templating engine, store in object store.                                       |
| **invoice-sender**       | _PdfRendered_                                         | _InvoiceSent_                 | Deliver invoice to ERP/email webhook, update status.                                            |

### Machine Learning

#### Recommendation: Fuzzy Matching for Order Reconciliation

**Recommendation (to be discussed):** Use a **fuzzy-string matching** approach to link each carrier invoice line to its originating Order. Fuzzy matching measures the "distance" between two text fields—here, for example, the carrier's description ("DHL AWB 1234-XYZ") vs. your internal order reference ("Order #100234 / AWB 1234 XYZ").

**Why fuzzy matching?**
 
- **Simplicity & transparency**: no complex ML pipelines, easy to reason about and debug.
    
- **Mature libraries**: we can leverage battle-tested tools like RapidFuzz (Python) or Apache Commons Text (Java), which implement algorithms such as Levenshtein distance, Jaro–Winkler, and token-set ratio.
    
 - **Sufficient accuracy**: in our domain the typical variations are small (dropped hyphens, case differences, extra whitespace), so a threshold of e.g. Jaro–Winkler ≥ 0.88 will correctly match > 95 % of lines in our pilot data.
    
 
**How it works**

1. **Preprocess strings**: normalize to uppercase, remove punctuation, collapse whitespace.
    
2. **Compute similarity score**: use token-set or Jaro–Winkler distance between invoice line's `tracking_id` + `description` and order's `tracking_id` + `reference`.
    
3. **Thresholding**: if score ≥ 0.88 → accept match; if 0.75–0.88 → flag for manual review; if < 0.75 → treat as unmatched and route to special handling. All fuzzy matches are logged with the following metadata: original strings, match score, and timestamp. This ensures full traceability and auditability of pricing decisions.

    

**Next steps**

- **Pilot**: run on last month's invoices and measure true/false match rates.
    
- **Tune**: adjust threshold and choose best algorithm (token-set vs. Levenshtein vs. Jaro–Winkler).
     
- **Future**: if we later need higher precision/recall, we can explore a lightweight ML model (e.g. a small neural embedder), but start with fuzzy matching for now.
    

> _All parameters (algorithm, threshold, review rules) remain configurable in the `invoice-matcher` service and require final team approval._

### 4.1 Domain Model Class Diagram

[![](https://img.plantuml.biz/plantuml/svg/bPB1QiCm38Rl0R-3puE-G4uTx58O30Oz1zTMKcsn7MGbOxHxzyMfqoofByjD-cr_Vtvf7rWHzxOL1JX6_fPlP83aHHHIFg6HfJmU3ozJfVl0tW9Lg_ONO7DccRvI6j1eLHib9gK_qJNnGfG2qrX59Ponwy1KYjCbnS1eGHG_ToFF3G7OiVY76QXhq8m3L3LvcnwEmW0KqBl59ZhEmjxx5d90DHzO0xLt1ZczNCgFuDqfzZj2FPhmPFjMBN--00zkyEEHLtHP7DxaDSp7qSnXpitU8LSJRXE-PzK-3EN1G0wUpxO3Gc-smQOk-_iSD_0DNHIt1RjUTpj_-Hk3A6PBEMtPXgIk9YQAFJWphV4l)](https://editor.plantuml.com/uml/bPB1QiCm38Rl0R-3puE-G4uTx58O30Oz1zTMKcsn7MGbOxHxzyMfqoofByjD-cr_Vtvf7rWHzxOL1JX6_fPlP83aHHHIFg6HfJmU3ozJfVl0tW9Lg_ONO7DccRvI6j1eLHib9gK_qJNnGfG2qrX59Ponwy1KYjCbnS1eGHG_ToFF3G7OiVY76QXhq8m3L3LvcnwEmW0KqBl59ZhEmjxx5d90DHzO0xLt1ZczNCgFuDqfzZj2FPhmPFjMBN--00zkyEEHLtHP7DxaDSp7qSnXpitU8LSJRXE-PzK-3EN1G0wUpxO3Gc-smQOk-_iSD_0DNHIt1RjUTpj_-Hk3A6PBEMtPXgIk9YQAFJWphV4l)

---

## 5. Data Flow & Process Flows

1. Order event triggers Invoice Engine.
2. Engine fetches applicable customer agreement.
3. Applies rules to match orders and invoice lines.
4. Calculates final invoice data.
5. Generates PDF and stores result.

### 5.1 High Level Activity Diagram

[![](https://img.plantuml.biz/plantuml/svg/LP11Ri8m44Ntbdo7wIuiUW5O50I1LgK2zGQM-IGZEJQo9vQGMFGElM5FKYSHejsD_dyp_skMIKoKldFqvdDWYzetxB6omfdbgjMJjEX05sVOQoKi3dUK9fP-lhxfbPlsUOyCHuzThMx_qPjMOzvWpJzXmHbqY2T4gZEyB8gyXTYMrdsIq1LzrKIK0gEd5P-f-Z05OD_GuVrbHgMUf-gADDEAQBusplREoAFaneXPZDuE7d6mZfljf1mH4oUviwDOSYzQmILGvSDqTHyQgcCCyI_XJtmUEYtBL1werIy0)](https://editor.plantuml.com/uml/LP11Ri8m44Ntbdo7wIuiUW5O50I1LgK2zGQM-IGZEJQo9vQGMFGElM5FKYSHejsD_dyp_skMIKoKldFqvdDWYzetxB6omfdbgjMJjEX05sVOQoKi3dUK9fP-lhxfbPlsUOyCHuzThMx_qPjMOzvWpJzXmHbqY2T4gZEyB8gyXTYMrdsIq1LzrKIK0gEd5P-f-Z05OD_GuVrbHgMUf-gADDEAQBusplREoAFaneXPZDuE7d6mZfljf1mH4oUviwDOSYzQmILGvSDqTHyQgcCCyI_XJtmUEYtBL1werIy0)

### 5.2 Sequence Diagram

[![](https://img.plantuml.biz/plantuml/svg/NPB1QiCm38Rl1h-3w6axb6vXfnv6GsaX44FOrWVm4ZKrE4wmtR7swIV5wRR6Xs3qV_gbtsGJelRnt5af1hgOsWpQHTXgQz7Vrufz0Jh0ed0jXhYOYHMCOgLzG3yNLHALZlK9FJoeyd66LBkftrF5jNE3FAbv2DXzsd6056b9MBwEgwsXEdp0ohYltjGOLaTZKGfM29XGWhAd3FAwnY5YyolpzdpMiAUkzzZxKQGgY-ecRhNt1dsHHfJ6uUuxUbqdApNI72JHAMtpZvR2zH71c_R4Zo85g1AEv-lrikmOfk1Ie6k0CIdOYkVdDqT-d9cXjX72cVA4G7Q4_eNbhCymqM-ecmkPsu3adiPnWxfhUCaoEBWkBPu6q3fIREWx_YC_)](https://editor.plantuml.com/uml/NPB1QiCm38Rl1h-3w6axb6vXfnv6GsaX44FOrWVm4ZKrE4wmtR7swIV5wRR6Xs3qV_gbtsGJelRnt5af1hgOsWpQHTXgQz7Vrufz0Jh0ed0jXhYOYHMCOgLzG3yNLHALZlK9FJoeyd66LBkftrF5jNE3FAbv2DXzsd6056b9MBwEgwsXEdp0ohYltjGOLaTZKGfM29XGWhAd3FAwnY5YyolpzdpMiAUkzzZxKQGgY-ecRhNt1dsHHfJ6uUuxUbqdApNI72JHAMtpZvR2zH71c_R4Zo85g1AEv-lrikmOfk1Ie6k0CIdOYkVdDqT-d9cXjX72cVA4G7Q4_eNbhCymqM-ecmkPsu3adiPnWxfhUCaoEBWkBPu6q3fIREWx_YC_)
[![](https://img.plantuml.biz/plantuml/svg/ZPJ1Rjim38RlUWgkf-qm90ssImz3lIB50jH0qAxOcRFPHAYiU1BJBMy_YhAwE6wBwM309l-Fr2_Abv4nyBvpiF9FEzlGqc-ifplwjIVVq0_BssK8kn3DEzvIHz0xjDx4H-jx3DY1asm-z0IxmDkTCMpPKO51fa71mUyPA0w-eMz5kZ466vRtAtIEWX4csyRsw1bMrol026Shlw78P-FO6ZIsnX0fdCJg7AkHcoq5U5_VV1Xdh9U3EiWNlpcHLpbOnKZTJvVe9dS77xzeKnUmllyECvlt66SFrf51n_24phQWho0hvOfa48jiCLz0rJJKCxN76MatQLt0jMZln0-9QqAYzccF4FSYZD70i4IJb7LHNc48bi9W_8sullp3GSmjg8BhaYqJd2BhX4pcP6Gsi3r2cb6-DfSjV96Nbj1IcrLX9ilVG1Oi3kQ_ojL-goDxaxldPUvk2Tl3H4DEV7mcFw1r-q3_G_uydyCm3WSTtodQj-EDQArYpckbiZcZCiD8JgkCAYWz7-HBx7oGpznFYmNS-owVfde-WUPsTT1EWNdGHZ2zTVzQL8eIv4gyTGL4BLlyYkrj-Xz4DggybAVyWFu2)](https://editor.plantuml.com/uml/ZPJ1Rjim38RlUWgkf-qm90ssImz3lIB50jH0qAxOcRFPHAYiU1BJBMy_YhAwE6wBwM309l-Fr2_Abv4nyBvpiF9FEzlGqc-ifplwjIVVq0_BssK8kn3DEzvIHz0xjDx4H-jx3DY1asm-z0IxmDkTCMpPKO51fa71mUyPA0w-eMz5kZ466vRtAtIEWX4csyRsw1bMrol026Shlw78P-FO6ZIsnX0fdCJg7AkHcoq5U5_VV1Xdh9U3EiWNlpcHLpbOnKZTJvVe9dS77xzeKnUmllyECvlt66SFrf51n_24phQWho0hvOfa48jiCLz0rJJKCxN76MatQLt0jMZln0-9QqAYzccF4FSYZD70i4IJb7LHNc48bi9W_8sullp3GSmjg8BhaYqJd2BhX4pcP6Gsi3r2cb6-DfSjV96Nbj1IcrLX9ilVG1Oi3kQ_ojL-goDxaxldPUvk2Tl3H4DEV7mcFw1r-q3_G_uydyCm3WSTtodQj-EDQArYpckbiZcZCiD8JgkCAYWz7-HBx7oGpznFYmNS-owVfde-WUPsTT1EWNdGHZ2zTVzQL8eIv4gyTGL4BLlyYkrj-Xz4DggybAVyWFu2)

---

## 6. Design Decisions

- **Single Responsibility Principle**: Each service does one job—and only that.
- **Modular Microservices**: Prefer many dumb, focused services over smart monoliths.
- **Stateless & API-first**: Services are independent and can be containerized (Docker).
- **Rule-Based Engine**: Business logic is extensible via Python classes.
- **Decoupled Architecture**: Services do not depend on each other's internal states; data is exchanged via APIs or events only.
- **Extensibility by Design**: The system supports future requirements through pluggable rule logic and flexible schema handling.
- **Python**:  Python was chosen for its readability and developer speed.

Example Python structure:

```python
class InvoiceEngine:
    def generate_invoice_lines(self, orders, agreement):
        invoice_lines = []
        for order in orders:
            for rule in agreement.rules:
                if rule.applies_to(order):
                    invoice_lines.append(rule.apply(order))
        return invoice_lines
```
## 7. Risks & Dependencies

### Risks
- Variability in carrier invoice formats  
- ML model accuracy and training requirements  
- Agreement rule complexity and versioning  

### Dependencies
- Agreement Service  
- Order Service  
- Stable API contracts between services  
- External libraries for PDF generation  

---

## 8. Recommendations

| Topic                      | Recommendation (needs team approval)                                                   |
| -------------------------- | -------------------------------------------------------------------------------------- |
| Agreement schema evolution | Use JSON-Schema; version via semantic + `effective_from` date, never mutate history.   |
| Rule sandboxing            | Provide a lightweight **RuleTestRunner** container used in CI and by analysts locally. |
| Error recovery             | Retry 3×, then DLQ topic `invoice-dead-letter`                                         |
| PDF optionality            | Always render & store; add flag `send_to_customer = false` for special cases.          |
| ERP integration            | Expose idempotent POST `/invoices/{id}` webhook; ERP pulls PDF from pre-signed URL.    |


PHP = 8.2
Laravel
Test Driven
