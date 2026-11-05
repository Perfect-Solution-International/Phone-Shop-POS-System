@extends('layouts.shop')

@section('title', 'POS Counter')
@section('page-title', 'Point of Sale (POS) Counter')

@section('content')
<style>
    /* ==========================================================================
       MODERN POS COUNTER STYLES
       ========================================================================== */
    .pos-workspace-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.15rem;
        flex-wrap: wrap;
        gap: 0.85rem;
        background: #ffffff;
        border: 1px solid rgba(226, 232, 240, 0.9);
        border-radius: 14px;
        padding: 0.85rem 1.25rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
    }

    .pos-layout {
        display: grid;
        grid-template-columns: 1.65fr 1fr;
        gap: 1.25rem;
        min-height: calc(100vh - 165px);
        align-items: start;
    }

    /* Latest Sale Announcement Banner */
    .latest-sale-banner {
        background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
        border: 1.5px solid #86efac;
        border-radius: 14px;
        padding: 0.75rem 1.25rem;
        margin-bottom: 1.15rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.85rem;
        box-shadow: 0 2px 8px rgba(16, 185, 129, 0.06);
        transition: all 0.25s ease;
    }

    /* Left Catalog Container */
    .pos-catalog {
        display: flex;
        flex-direction: column;
        background: #ffffff;
        border: 1px solid rgba(226, 232, 240, 0.9);
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03), 0 6px 16px -4px rgba(0, 0, 0, 0.04);
    }

    .pos-catalog-header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        gap: 0.85rem;
        align-items: center;
        background: #ffffff;
    }

    .search-input-shell {
        position: relative;
        flex: 1;
        display: flex;
        align-items: center;
    }

    .search-input-shell svg {
        position: absolute;
        left: 1rem;
        color: #94a3b8;
        pointer-events: none;
    }

    .search-input-shell input {
        padding-left: 2.75rem;
        height: 44px;
        border-radius: 12px;
        font-size: 0.9rem;
        font-weight: 500;
        border: 1.5px solid #e2e8f0;
        background: #f8fafc;
        transition: all 0.2s ease;
        width: 100%;
    }

    .search-input-shell input:focus {
        background: #ffffff;
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12);
        outline: none;
    }

    .pos-category-select {
        max-width: 200px;
        height: 44px;
        border-radius: 12px;
        font-size: 0.875rem;
        font-weight: 600;
        border: 1.5px solid #e2e8f0;
        background: #f8fafc;
        color: #334155;
        padding: 0 1rem;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .pos-category-select:focus {
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12);
        outline: none;
    }

    /* Tabs */
    .pos-tabs {
        display: inline-flex;
        background: #f1f5f9;
        padding: 0.35rem;
        margin: 0.75rem 1rem 0.25rem;
        border-radius: 12px;
        gap: 0.35rem;
        width: fit-content;
        border: 1px solid #e2e8f0;
    }

    .pos-tab {
        padding: 0.55rem 1.15rem;
        font-size: 0.8125rem;
        font-weight: 700;
        cursor: pointer;
        border-radius: 9px;
        color: #64748b;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        transition: all 0.18s ease;
        border: none;
        user-select: none;
    }

    .pos-tab:hover {
        color: #1e293b;
        background: rgba(255, 255, 255, 0.6);
    }

    .pos-tab.active {
        color: #4f46e5;
        background: #ffffff;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
        font-weight: 800;
    }

    /* Quick Filter Sub-bar */
    .pos-filter-subbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.55rem 1.15rem 0.65rem;
        background: #ffffff;
        border-bottom: 1px solid #f1f5f9;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .filter-pills {
        display: flex;
        align-items: center;
        gap: 0.45rem;
        flex-wrap: wrap;
    }

    .filter-pill {
        padding: 0.35rem 0.85rem;
        font-size: 0.75rem;
        font-weight: 700;
        border-radius: 9999px;
        border: 1.5px solid #e2e8f0;
        background: #ffffff;
        color: #64748b;
        cursor: pointer;
        transition: all 0.18s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }

    .filter-pill:hover {
        border-color: #cbd5e1;
        color: #0f172a;
        background: #f8fafc;
    }

    .filter-pill.active {
        background: #4f46e5 !important;
        color: #ffffff !important;
        border-color: #4f46e5 !important;
        box-shadow: 0 2px 8px rgba(79, 70, 229, 0.28) !important;
    }

    .filter-pill.active-instock {
        background: #059669 !important;
        color: #ffffff !important;
        border-color: #059669 !important;
        box-shadow: 0 2px 8px rgba(5, 150, 105, 0.28) !important;
    }

    .filter-pill.active-sold {
        background: #e11d48 !important;
        color: #ffffff !important;
        border-color: #e11d48 !important;
        box-shadow: 0 2px 8px rgba(225, 29, 72, 0.28) !important;
    }

    /* Items Grid */
    .pos-items-grid {
        padding: 1.15rem;
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(215px, 1fr));
        gap: 1rem;
        overflow-y: auto;
        flex: 1;
        background: #f8fafc;
        max-height: calc(100vh - 280px);
    }

    /* POS Item Card */
    .pos-item-card {
        border: 1.5px solid #e2e8f0;
        border-radius: 16px;
        padding: 1rem;
        background: #ffffff;
        transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02), 0 3px 8px -2px rgba(0, 0, 0, 0.02);
    }

    .pos-item-card:hover {
        border-color: #818cf8;
        box-shadow: 0 12px 28px -4px rgba(79, 70, 229, 0.14);
        transform: translateY(-2px);
    }

    .pos-item-card.item-sold {
        background: #fbfcfe;
        border-color: #edf2f7;
    }

    .pos-item-card.item-sold:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.04);
        transform: none;
    }

    /* Brand Chip */
    .pos-item-brand {
        display: inline-block;
        font-size: 0.68rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #0284c7;
        background: #f0f9ff;
        border: 1px solid #bae6fd;
        padding: 0.2rem 0.55rem;
        border-radius: 6px;
        line-height: 1.2;
    }

    /* Status Badges */
    .badge-status-instock {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        background: #ecfdf5;
        color: #047857;
        border: 1px solid #a7f3d0;
        font-size: 0.68rem;
        font-weight: 750;
        padding: 0.2rem 0.6rem;
        border-radius: 9999px;
        white-space: nowrap;
    }

    .status-dot-green {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #10b981;
        box-shadow: 0 0 6px #10b981;
        display: inline-block;
    }

    .badge-status-sold {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        background: #fef2f2;
        color: #dc2626;
        border: 1px solid #fecaca;
        font-size: 0.68rem;
        font-weight: 750;
        padding: 0.2rem 0.6rem;
        border-radius: 9999px;
        white-space: nowrap;
    }

    /* Model & Specs */
    .pos-card-model {
        font-weight: 800;
        font-size: 1.05rem;
        color: #0f172a;
        line-height: 1.3;
        margin-top: 0.45rem;
        margin-bottom: 0.35rem;
    }

    .pos-card-specs {
        display: flex;
        align-items: center;
        gap: 0.25rem;
        flex-wrap: wrap;
        margin-top: 0.25rem;
    }

    .pos-spec-chip {
        font-size: 0.68rem;
        font-weight: 650;
        padding: 0.14rem 0.45rem;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        line-height: 1.2;
        white-space: nowrap;
    }

    .pos-spec-chip.chip-storage {
        background: #eff6ff;
        color: #2563eb;
        border: 1px solid #dbeafe;
        font-weight: 750;
    }

    .pos-spec-chip.chip-color {
        background: #f8fafc;
        color: #475569;
        border: 1px solid #e2e8f0;
    }

    .pos-spec-chip.chip-condition-new {
        background: #ecfdf5;
        color: #059669;
        border: 1px solid #a7f3d0;
        font-weight: 700;
    }

    .pos-spec-chip.chip-condition-used {
        background: #fef3c7;
        color: #b45309;
        border: 1px solid #fde68a;
        font-weight: 700;
    }

    .pos-spec-chip.chip-condition-refurbished {
        background: #f3e8ff;
        color: #7e22ce;
        border: 1px solid #e9d5ff;
        font-weight: 700;
    }

    /* IMEI Chip */
    .pos-imei-chip {
        font-family: 'JetBrains Mono', monospace;
        font-size: 0.68rem;
        color: #475569;
        margin-top: 0.45rem;
        background: #f8fafc;
        padding: 0.28rem 0.55rem;
        border-radius: 7px;
        border: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .pos-imei-chip:hover {
        background: #eff6ff;
        border-color: #60a5fa;
        color: #1d4ed8;
    }

    /* Seamless Buyer Info (No Clunky Nested Box) */
    .phone-buyer-box {
        margin-top: 0.55rem;
        padding-top: 0.5rem;
        border-top: 1px dashed #e2e8f0;
        background: transparent;
    }

    .pos-buyer-row {
        display: flex;
        align-items: flex-start;
        gap: 0.5rem;
    }

    .pos-buyer-avatar {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: #e0e7ff;
        color: #4338ca;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        margin-top: 1px;
    }

    .pos-buyer-content {
        flex: 1;
        min-width: 0;
    }

    .pos-buyer-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.35rem;
        line-height: 1.2;
    }

    .pos-buyer-label {
        font-size: 0.62rem;
        font-weight: 750;
        text-transform: uppercase;
        color: #64748b;
        letter-spacing: 0.04em;
    }

    .pos-buyer-invoice {
        font-family: 'JetBrains Mono', monospace;
        font-size: 0.62rem;
        color: #64748b;
        background: #f1f5f9;
        padding: 1px 5px;
        border-radius: 4px;
        font-weight: 600;
        white-space: nowrap;
    }

    .pos-buyer-name {
        font-weight: 800;
        color: #0f172a;
        font-size: 0.8125rem;
        margin-top: 2px;
        line-height: 1.25;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .pos-buyer-phone {
        color: #2563eb;
        font-size: 0.72rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        margin-top: 2px;
    }

    /* Card Footer Price & Button */
    .pos-item-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 0.75rem;
        padding-top: 0.65rem;
        border-top: 1px solid #f1f5f9;
        gap: 0.5rem;
    }

    .pos-item-price-val {
        font-weight: 850;
        color: #0f172a;
        font-size: 1.05rem;
        letter-spacing: -0.02em;
    }

    .btn-sell-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.35rem;
        height: 34px;
        padding: 0 1rem;
        border-radius: 9px;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: #ffffff;
        font-weight: 750;
        font-size: 0.78rem;
        border: none;
        box-shadow: 0 2px 8px rgba(16, 185, 129, 0.32);
        cursor: pointer;
        transition: all 0.18s ease;
        white-space: nowrap;
    }

    .btn-sell-action:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.42);
        transform: translateY(-1px);
    }

    .btn-out-of-stock {
        height: 34px;
        padding: 0 0.85rem;
        border-radius: 9px;
        background: #f1f5f9;
        color: #94a3b8;
        font-weight: 700;
        font-size: 0.74rem;
        border: 1px solid #e2e8f0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: not-allowed;
        white-space: nowrap;
    }

    /* Customer Purchases Panel (Right) */
    .pos-purchases-panel {
        background: #ffffff;
        border: 1.5px solid #e2e8f0;
        border-radius: 16px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03), 0 6px 16px -4px rgba(0, 0, 0, 0.04);
        position: sticky;
        top: 80px;
        max-height: calc(100vh - 165px);
    }

    .pos-purchases-header {
        padding: 1.1rem 1.25rem;
        border-bottom: 1.5px solid #e2e8f0;
        background: #ffffff;
    }

    .purchase-feed-list {
        padding: 0.85rem;
        overflow-y: auto;
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        background: #f8fafc;
    }

    .purchase-feed-card {
        background: #ffffff;
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        padding: 0.95rem 1.1rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        transition: all 0.18s ease;
    }

    .purchase-feed-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
    }

    /* ==========================================================================
       PREMIUM QUICK SELL MODAL & CONTROLS
       ========================================================================== */
    .modal-box-modern {
        max-width: 500px;
        width: 100%;
        max-height: calc(100vh - 2rem);
        border-radius: 16px;
        background: #ffffff;
        box-shadow: 0 25px 60px -15px rgba(15, 23, 42, 0.25), 0 0 0 1px rgba(15, 23, 42, 0.05);
        border: 1px solid #e2e8f0;
        padding: 0.95rem 1.15rem;
        position: relative;
        overflow-y: auto;
        overflow-x: hidden;
        display: flex;
        flex-direction: column;
    }

    .modal-box-modern::-webkit-scrollbar {
        width: 6px;
    }
    .modal-box-modern::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 9999px;
    }

    .modal-box-modern::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #10b981 0%, #059669 50%, #047857 100%);
    }

    .qs-modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.55rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid #f1f5f9;
    }

    .qs-header-icon {
        width: 32px;
        height: 32px;
        border-radius: 9px;
        background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
        border: 1px solid #86efac;
        color: #059669;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 1px 3px rgba(16, 185, 129, 0.2);
    }

    .qs-close-btn {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #64748b;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.18s ease;
    }

    .qs-close-btn:hover {
        background: #f1f5f9;
        color: #0f172a;
        transform: rotate(90deg);
    }

    /* Wizard Steps Bar */
    .qs-wizard-steps {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 0.55rem;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 0.3rem 0.65rem;
    }

    .qs-step-indicator {
        display: flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.74rem;
        font-weight: 750;
        color: #64748b;
        cursor: pointer;
        transition: all 0.2s ease;
        user-select: none;
    }

    .qs-step-indicator.active {
        color: #059669;
    }

    .qs-step-indicator.completed {
        color: #047857;
    }

    .qs-step-num {
        width: 19px;
        height: 19px;
        border-radius: 50%;
        background: #e2e8f0;
        color: #64748b;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.68rem;
        font-weight: 850;
        transition: all 0.2s ease;
    }

    .qs-step-indicator.active .qs-step-num {
        background: #059669;
        color: #ffffff;
        box-shadow: 0 1px 4px rgba(16, 185, 129, 0.35);
    }

    .qs-step-indicator.completed .qs-step-num {
        background: #10b981;
        color: #ffffff;
    }

    .qs-step-divider {
        flex: 1;
        height: 2px;
        background: #e2e8f0;
        margin: 0 0.55rem;
        border-radius: 2px;
        transition: all 0.2s ease;
    }

    .qs-step-divider.active {
        background: linear-gradient(90deg, #10b981 0%, #059669 100%);
    }

    /* Hero Product Card in Modal */
    .qs-product-hero {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border: 1.5px solid #e2e8f0;
        border-radius: 11px;
        padding: 0.55rem 0.85rem;
        margin-bottom: 0.55rem;
        position: relative;
    }

    .qs-product-badge {
        font-size: 0.65rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        background: #eef2ff;
        color: #4338ca;
        border: 1px solid #c7d2fe;
        padding: 2px 7px;
        border-radius: 5px;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }

    .qs-price-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 0.45rem;
        padding-top: 0.45rem;
        border-top: 1px dashed #cbd5e1;
    }

    /* Customer Details Card */
    .qs-customer-card {
        background: #ffffff;
        border: 1.5px solid #e2e8f0;
        border-radius: 11px;
        padding: 0.6rem 0.85rem;
        margin-bottom: 0.55rem;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02);
    }

    .qs-customer-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.45rem;
    }

    .qs-input-field {
        display: flex;
        flex-direction: column;
        gap: 0.18rem;
    }

    .qs-input-label {
        font-size: 0.7rem;
        font-weight: 750;
        color: #475569;
        display: flex;
        align-items: center;
        gap: 0.2rem;
    }

    .qs-input-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }

    .qs-input-wrapper svg {
        position: absolute;
        left: 0.65rem;
        width: 13px;
        height: 13px;
        color: #94a3b8;
        pointer-events: none;
        transition: color 0.18s ease;
    }

    .qs-input-wrapper input {
        width: 100%;
        height: 35px;
        padding-left: 2.05rem;
        padding-right: 0.65rem;
        border-radius: 8px;
        border: 1.5px solid #cbd5e1;
        background: #f8fafc;
        font-size: 0.8125rem;
        font-weight: 600;
        color: #0f172a;
        transition: all 0.2s ease;
    }

    .qs-input-wrapper input:focus {
        background: #ffffff;
        border-color: #10b981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.14);
        outline: none;
    }

    .qs-input-wrapper input:focus + svg,
    .qs-input-wrapper:focus-within svg {
        color: #059669;
    }

    .qs-input-wrapper input.input-error {
        border-color: #ef4444 !important;
        background: #fef2f2 !important;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.15) !important;
    }

    /* Summary Strip on Step 2 */
    .qs-summary-strip {
        background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
        border: 1.5px solid #86efac;
        border-radius: 11px;
        padding: 0.55rem 0.8rem;
        margin-bottom: 0.65rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.65rem;
        box-shadow: 0 1px 2px rgba(16, 185, 129, 0.06);
    }

    .qs-summary-buyer {
        font-size: 0.875rem;
        font-weight: 850;
        color: #0f172a;
        line-height: 1.35;
    }

    .qs-summary-item {
        font-size: 0.75rem;
        color: #475569;
        margin-top: 3px;
        display: flex;
        align-items: center;
        gap: 0.35rem;
    }

    .qs-summary-edit-btn {
        background: #ffffff;
        border: 1px solid #86efac;
        color: #047857;
        font-size: 0.72rem;
        font-weight: 750;
        padding: 4px 9px;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.15s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        white-space: nowrap;
    }

    .qs-summary-edit-btn:hover {
        background: #dcfce7;
    }

    /* Payment Methods Grid */
    .qs-pay-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0.5rem;
    }

    .qs-pay-card {
        border: 1.5px solid #e2e8f0;
        border-radius: 10px;
        padding: 0.55rem 0.45rem;
        cursor: pointer;
        background: #ffffff;
        transition: all 0.2s ease;
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 0.2rem;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
    }

    .qs-pay-card:hover {
        border-color: #a7f3d0;
        background: #f0fdf4;
        transform: translateY(-1px);
    }

    .qs-pay-card.active,
    .qs-pay-card:has(input:checked) {
        border: 2px solid #059669;
        background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
        box-shadow: 0 3px 10px rgba(16, 185, 129, 0.15);
    }

    .qs-pay-icon {
        width: 28px;
        height: 28px;
        border-radius: 7px;
        background: #f1f5f9;
        color: #64748b;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }

    .qs-pay-card.active .qs-pay-icon,
    .qs-pay-card:has(input:checked) .qs-pay-icon {
        background: #dcfce7;
        color: #059669;
    }

    .qs-pay-name {
        font-weight: 800;
        font-size: 0.78rem;
        color: #1e293b;
    }

    .qs-pay-card.active .qs-pay-name,
    .qs-pay-card:has(input:checked) .qs-pay-name {
        color: #065f46;
    }

    .qs-pay-sub {
        font-size: 0.65rem;
        color: #64748b;
        font-weight: 600;
    }

    .qs-pay-check {
        position: absolute;
        top: 5px;
        right: 5px;
        width: 15px;
        height: 15px;
        border-radius: 50%;
        background: #059669;
        color: #ffffff;
        display: none;
        align-items: center;
        justify-content: center;
    }

    .qs-pay-card.active .qs-pay-check,
    .qs-pay-card:has(input:checked) .qs-pay-check {
        display: flex;
    }

    /* Cash Tender Calculator */
    .qs-cash-calculator {
        background: #f8fafc;
        border: 1.5px solid #e2e8f0;
        border-radius: 11px;
        padding: 0.65rem 0.8rem;
        margin-top: 0.65rem;
    }

    .qs-quick-pills {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
        margin-top: 0.4rem;
    }

    .qs-quick-pill {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        color: #334155;
        font-size: 0.7rem;
        font-weight: 750;
        padding: 2px 7px;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .qs-quick-pill:hover {
        background: #ecfdf5;
        border-color: #86efac;
        color: #047857;
    }

    /* Card Brand Selector Buttons */
    .qs-card-brand-btn {
        background: #ffffff;
        border: 1.5px solid #cbd5e1;
        color: #334155;
        font-size: 0.72rem;
        font-weight: 750;
        padding: 4px 10px;
        border-radius: 7px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        transition: all 0.18s ease;
    }

    .qs-card-brand-btn:hover {
        border-color: #94a3b8;
        background: #f8fafc;
    }

    .qs-card-brand-btn.active {
        background: #ecfdf5 !important;
        border-color: #10b981 !important;
        color: #065f46 !important;
        box-shadow: 0 1px 3px rgba(16, 185, 129, 0.22) !important;
    }

    /* Footer Buttons */
    .qs-modal-footer {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 0.5rem;
        border-top: 1px solid #f1f5f9;
        padding-top: 0.6rem;
        margin-top: 0.45rem;
        position: sticky;
        bottom: -0.95rem;
        background: #ffffff;
        padding-bottom: 0.25rem;
        z-index: 10;
    }

    .qs-btn-cancel {
        height: 36px;
        padding: 0 1rem;
        border-radius: 8px;
        font-weight: 700;
        font-size: 0.825rem;
        background: #f8fafc;
        border: 1.5px solid #e2e8f0;
        color: #475569;
        cursor: pointer;
        transition: all 0.18s ease;
    }

    .qs-btn-cancel:hover {
        background: #f1f5f9;
        color: #0f172a;
    }

    .qs-btn-confirm {
        height: 36px;
        padding: 0 1.25rem;
        border-radius: 8px;
        font-weight: 850;
        font-size: 0.825rem;
        background: linear-gradient(135deg, #059669 0%, #10b981 100%);
        color: #ffffff;
        border: none;
        box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .qs-btn-confirm:hover {
        background: linear-gradient(135deg, #047857 0%, #059669 100%);
        box-shadow: 0 6px 18px rgba(16, 185, 129, 0.45);
        transform: translateY(-1px);
    }

    .qs-btn-confirm:active {
        transform: translateY(0);
    }

    /* Responsiveness */
    @media (max-width: 1024px) {
        .pos-layout {
            grid-template-columns: 1fr;
        }
        .pos-purchases-panel {
            position: static;
            max-height: none;
        }
    }

    @media (max-width: 640px) {
        .pos-items-grid {
            grid-template-columns: 1fr;
            padding: 0.85rem;
        }
        .pos-catalog-header {
            flex-direction: column;
            align-items: stretch;
        }
        .pos-category-select {
            max-width: 100%;
        }
    }
</style>

<!-- Modern Status Bar Above POS -->
<div class="pos-workspace-bar">
    <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <span class="status-dot-green"></span>
            <span style="font-size: 0.85rem; color: #64748b;">
                Active Counter: <strong style="color: #0f172a;">{{ session('active_outlet_name', auth()->user()->shop->mainOutlet->name ?? 'Main Branch') }}</strong>
            </span>
        </div>
        <span class="badge" style="background: #eef2ff; color: #4f46e5; font-weight: 700; border-radius: 9999px; padding: 3px 10px;">Live POS Terminal</span>
    </div>

    <div style="display: flex; gap: 0.65rem; align-items: center; flex-wrap: wrap;">
        <button type="button" class="btn btn-secondary btn-sm" onclick="openModal('recentSalesModal')" style="border-radius: 8px; font-weight: 650; gap: 0.45rem;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
            <span>Recent Invoices ({{ count($recentSales) }})</span>
        </button>
        <a href="{{ route('shop.sales') }}" class="btn btn-secondary btn-sm" style="border-radius: 8px; font-weight: 650; gap: 0.45rem;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
            <span>Sales Directory</span>
        </a>
    </div>
</div>

<!-- Sleek Live Stock & Buyer Notification Banner -->
<div id="latestSaleBanner" class="latest-sale-banner" style="{{ count($recentSales) > 0 ? '' : 'display: none;' }}">
    <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
        <span class="badge" style="background: #16a34a; color: #ffffff; font-size: 0.72rem; font-weight: 800; padding: 0.25rem 0.65rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 0.35rem;">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
            <span>LATEST SALE</span>
        </span>
        @if(count($recentSales) > 0)
            @php $ls = $recentSales->first(); @endphp
            <span id="latestSaleText" style="color: #14532d; font-weight: 650; font-size: 0.85rem;">
                <strong>{{ $ls->items->first()?->item_name ?? 'Device' }}</strong> sold to 
                <strong style="background: #dcfce7; padding: 2px 7px; border-radius: 5px; color: #0f172a;">{{ $ls->customer->name ?? 'Walk-in Customer' }}</strong> 
                ({{ $ls->customer->phone ?? 'Counter' }}) &bull; Total: <span style="font-weight: 850; color: #047857;">LKR {{ number_format($ls->total_amount, 2) }}</span>
            </span>
        @else
            <span id="latestSaleText" style="color: #14532d; font-weight: 650;">-</span>
        @endif
    </div>
    <span style="font-size: 0.72rem; color: #047857; font-weight: 800; background: #ffffff; padding: 0.25rem 0.75rem; border-radius: 9999px; border: 1px solid #86efac; display: inline-flex; align-items: center; gap: 0.3rem;">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
        Stock Deducted Live
    </span>
</div>

<div class="pos-layout" id="posApp">
    <!-- Left: Catalog & Instant Sell Items -->
    <div class="pos-catalog">
        <!-- Top Search & Category Filter -->
        <div class="pos-catalog-header">
            <div class="search-input-shell">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                <input type="text" id="searchInput" class="form-control" placeholder="Scan Barcode or Search by Model, IMEI, Brand..." onkeyup="filterItems()">
            </div>
            <select id="categoryFilter" class="pos-category-select" onchange="filterItems()">
                <option value="">All Categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->name }}">{{ $category->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Main Product Tabs -->
        <div class="pos-tabs">
            <div class="pos-tab active" onclick="switchTab('phones', this)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="5" y="2" width="14" height="20" rx="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line></svg>
                <span>Mobile Phones (IMEI)</span>
            </div>
            <div class="pos-tab" onclick="switchTab('products', this)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>
                <span>Accessories &amp; Items</span>
            </div>
        </div>

        <!-- Quick Filter Sub-bar: In-Stock vs Sold -->
        <div class="pos-filter-subbar">
            <div class="filter-pills" id="phonesFilterPills">
                <button type="button" class="filter-pill active" onclick="filterByStockStatus('all', this)">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="5" y="2" width="14" height="20" rx="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line></svg>
                    <span>All Phones (<span id="allPhonesCount">{{ $phones->count() }}</span>)</span>
                </button>
                <button type="button" class="filter-pill" onclick="filterByStockStatus('instock', this)">
                    <span class="status-dot-green"></span>
                    <span>In Stock (<span id="inStockPhonesCount">{{ $phones->where('status', '!=', 'sold')->count() }}</span>)</span>
                </button>
                <button type="button" class="filter-pill" onclick="filterByStockStatus('sold', this)">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    <span>Sold Archive (<span id="soldPhonesCount">{{ $phones->where('status', 'sold')->count() }}</span>)</span>
                </button>
            </div>
            <div style="font-size: 0.72rem; color: #166534; font-weight: 600; display: inline-flex; align-items: center; gap: 0.35rem; background: #f0fdf4; border: 1px solid #bbf7d0; padding: 0.25rem 0.65rem; border-radius: 8px;">
                <span>Click</span>
                <strong style="color: #047857; display: inline-flex; align-items: center; gap: 0.2rem;">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                    Sell Now
                </strong>
                <span>to deduct stock &amp; record buyer</span>
            </div>
        </div>

        <!-- Phone Items Grid -->
        <div id="tabPhones" class="pos-items-grid">
            @forelse($phones as $phone)
                @php $isSold = in_array(strtolower($phone->status), ['sold']); @endphp
                <div class="pos-item-card item-card {{ $isSold ? 'item-sold' : '' }}" 
                     id="phoneCard{{ $phone->id }}"
                     data-type="phone" 
                     data-id="{{ $phone->id }}" 
                     data-name="{{ $phone->brand }} {{ $phone->model }}" 
                     data-price="{{ $phone->selling_price }}" 
                     data-imei="{{ $phone->imei_1 }}"
                     data-category="Phones">
                    
                    @if($phone->image_url)
                        <img src="{{ $phone->image_url }}" alt="{{ $phone->brand }} {{ $phone->model }}" style="width: 100%; height: 90px; object-fit: cover; border-radius: 8px; margin-bottom: 0.5rem; border: 1px solid var(--surface-border);">
                    @endif

                    <div>
                        <!-- Brand & Status Pill -->
                        <div style="display: flex; justify-content: space-between; align-items: center; gap: 0.4rem;">
                            <span class="pos-item-brand">{{ $phone->brand }}</span>
                            @if($isSold)
                                <span class="badge-status-sold" id="phoneBadge{{ $phone->id }}">✓ Sold</span>
                            @else
                                <span class="badge-status-instock" id="phoneBadge{{ $phone->id }}">
                                    <span class="status-dot-green"></span> In Stock
                                </span>
                            @endif
                        </div>

                        <!-- Model Name -->
                        <div class="pos-card-model">{{ $phone->model }}</div>

                        <!-- Color & Storage & Condition Chips -->
                        <div class="pos-card-specs">
                            @if($phone->color)
                                <span class="pos-spec-chip chip-color">
                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="9"></circle><path d="M12 3v18"></path></svg>
                                    <span>{{ $phone->color }}</span>
                                </span>
                            @endif
                            @if($phone->storage)
                                <span class="pos-spec-chip chip-storage">
                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><ellipse cx="12" cy="5" rx="9" ry="3"></ellipse><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path></svg>
                                    <span>{{ $phone->storage }}</span>
                                </span>
                            @endif
                            @if($phone->condition)
                                @php
                                    $cond = strtolower(trim($phone->condition));
                                    $condClass = match($cond) {
                                        'new' => 'chip-condition-new',
                                        'refurbished' => 'chip-condition-refurbished',
                                        default => 'chip-condition-used',
                                    };
                                    $condIcon = match($cond) {
                                        'new' => '✨',
                                        'refurbished' => '🛠',
                                        default => '🔄',
                                    };
                                @endphp
                                <span class="pos-spec-chip {{ $condClass }}">
                                    <span>{{ $condIcon }}</span>
                                    <span style="text-transform: capitalize;">{{ $phone->condition }}</span>
                                </span>
                            @endif
                        </div>

                        <!-- Monospace IMEI Chip with Quick Copy -->
                        <div class="pos-imei-chip" title="Click to copy IMEI" onclick="navigator.clipboard && navigator.clipboard.writeText('{{ $phone->imei_1 }}'); showCopiedToast('IMEI {{ $phone->imei_1 }}')">
                            <span style="font-weight: 700; color: #64748b; font-size: 0.65rem; letter-spacing: 0.04em;">IMEI:</span>
                            <span style="font-weight: 800; color: #0284c7; letter-spacing: 0.02em;">{{ $phone->imei_1 }}</span>
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2.5"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                        </div>

                        <!-- Buyer Customer Info Display (Shown on sold phones - Seamless & Clean) -->
                        <div id="phoneBuyerBox{{ $phone->id }}" class="phone-buyer-box" style="{{ $isSold && $phone->sale && $phone->sale->customer ? 'display: block;' : 'display: none;' }}">
                            <div class="pos-buyer-row">
                                <div class="pos-buyer-avatar">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                </div>
                                <div class="pos-buyer-content">
                                    <div class="pos-buyer-top">
                                        <span class="pos-buyer-label">Sold to</span>
                                        @if($phone->sale)
                                            <span class="pos-buyer-invoice">#{{ $phone->sale->invoice_number }}</span>
                                        @endif
                                    </div>
                                    <div class="pos-buyer-name" id="phoneBuyerName{{ $phone->id }}">
                                        {{ $phone->sale->customer->name ?? '' }}
                                    </div>
                                    <div class="pos-buyer-phone" id="phoneBuyerPhone{{ $phone->id }}">
                                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                                        <span>{{ $phone->sale->customer->phone ?? '' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer: Price & Sell Action -->
                    <div class="pos-item-footer">
                        <div>
                            <span style="font-size: 0.68rem; color: #64748b; font-weight: 700; display: block; text-transform: uppercase;">Price</span>
                            <span class="pos-item-price-val">LKR {{ number_format($phone->selling_price, 2) }}</span>
                        </div>
                        @if($isSold)
                            <button type="button" class="btn-out-of-stock" disabled>
                                ✓ Out of Stock
                            </button>
                        @else
                            <button type="button" 
                                    id="phoneSellBtn{{ $phone->id }}"
                                    class="btn-sell-action item-add-btn" 
                                    onclick="openQuickSellModal('phone', {{ $phone->id }}, '{{ addslashes($phone->brand.' '.$phone->model) }} (IMEI: {{ $phone->imei_1 }})', {{ $phone->selling_price }}, 1)">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                                <span>Sell Now</span>
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div style="grid-column: 1/-1; text-align: center; color: var(--text-muted); padding: 3.5rem 1rem;">
                    <div style="width: 52px; height: 52px; border-radius: 50%; background: #f1f5f9; color: #94a3b8; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 0.75rem;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line></svg>
                    </div>
                    <div style="font-weight: 700; color: #1e293b; font-size: 0.95rem;">No serialized phones registered yet.</div>
                    <div style="font-size: 0.8rem; margin-top: 0.35rem; color: #64748b;">Add phones under <strong>Phone Inventory</strong> to start making sales.</div>
                </div>
            @endforelse
        </div>

        <!-- Products / Accessories Tab -->
        <div id="tabProducts" class="pos-items-grid" style="display: none;">
            @forelse($products as $product)
                @php $isOut = $product->stock_quantity <= 0; @endphp
                <div class="pos-item-card item-card {{ $isOut ? 'item-sold' : '' }}" 
                     id="prodCard{{ $product->id }}"
                     data-type="product" 
                     data-id="{{ $product->id }}" 
                     data-name="{{ $product->name }}" 
                     data-price="{{ $product->selling_price }}" 
                     data-barcode="{{ $product->barcode }}"
                     data-category="{{ $product->category->name ?? 'General' }}">
                    
                    @if($product->image_url)
                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" style="width: 100%; height: 90px; object-fit: cover; border-radius: 8px; margin-bottom: 0.5rem; border: 1px solid var(--surface-border);">
                    @endif

                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; gap: 0.4rem;">
                            <span class="pos-item-brand" style="color: #4f46e5; background: #eef2ff;">{{ $product->category->name ?? 'Accessory' }}</span>
                            <span class="badge {{ $isOut ? 'badge-danger' : ($product->isLowStock() ? 'badge-warning' : 'badge-success') }}" id="prodStockBadge{{ $product->id }}" style="font-size: 0.68rem; padding: 2px 7px; border-radius: 9999px;">
                                {{ $isOut ? 'Out of Stock' : 'Stock: '.$product->stock_quantity.' units' }}
                            </span>
                        </div>

                        <div class="pos-card-model">{{ $product->name }}</div>

                        <div class="pos-card-specs">
                            <span>SKU: {{ $product->sku ?? 'N/A' }}</span>
                            @if($product->barcode)
                                <span>&bull;</span>
                                <span>Barcode: {{ $product->barcode }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="pos-item-footer">
                        <div>
                            <span style="font-size: 0.68rem; color: #64748b; font-weight: 700; display: block; text-transform: uppercase;">Price</span>
                            <span class="pos-item-price-val">LKR {{ number_format($product->selling_price, 2) }}</span>
                        </div>
                        @if($isOut)
                            <button type="button" class="btn-out-of-stock" disabled>
                                Out of Stock
                            </button>
                        @else
                            <button type="button" 
                                    id="prodSellBtn{{ $product->id }}"
                                    class="btn-sell-action item-add-btn" 
                                    onclick="openQuickSellModal('product', {{ $product->id }}, '{{ addslashes($product->name) }}', {{ $product->selling_price }}, {{ $product->stock_quantity }})">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                                <span>Sell Now</span>
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div style="grid-column: 1/-1; text-align: center; color: var(--text-muted); padding: 3.5rem 1rem;">
                    <div style="width: 52px; height: 52px; border-radius: 50%; background: #f1f5f9; color: #94a3b8; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 0.75rem;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>
                    </div>
                    <div style="font-weight: 700; color: #1e293b; font-size: 0.95rem;">No accessories or items registered yet.</div>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Right: Customer Purchases & Stock Deductions Audit Panel -->
    <div class="pos-purchases-panel">
        <!-- Panel Header -->
        <div class="pos-purchases-header">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 0.65rem;">
                    <span style="display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 10px; background: #e0e7ff; color: #4338ca;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    </span>
                    <div>
                        <h2 style="font-size: 1rem; font-weight: 850; color: #0f172a; margin: 0; line-height: 1.2;">Customer Purchases</h2>
                        <div style="font-size: 0.75rem; color: #64748b; margin-top: 1px;">Live sold inventory &amp; buyer ledger</div>
                    </div>
                </div>
                <span class="badge" id="purchasesCountBadge" style="background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; font-size: 0.75rem; font-weight: 800; padding: 0.3rem 0.65rem; border-radius: 9999px;">
                    {{ $recentSales->count() }} Recorded
                </span>
            </div>

            <!-- Instant Search in Purchases Feed -->
            <div style="margin-top: 0.85rem; position: relative;">
                <input type="text" id="filterPurchasesInput" class="form-control" style="height: 38px; font-size: 0.825rem; border-radius: 10px; padding-left: 2rem; background: #f8fafc;" placeholder="Search buyer customer, phone, or invoice..." oninput="filterCustomerPurchases(this.value)">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2.5" style="position: absolute; left: 0.75rem; top: 12px; pointer-events: none;"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            </div>
        </div>

        <!-- Feed List of Sold Products & Buyer Customer Details -->
        <div class="purchase-feed-list" id="customerPurchasesFeed">
            @forelse($recentSales as $sale)
                <div class="purchase-feed-card" 
                     data-search="{{ strtolower(($sale->customer->name ?? '').' '.($sale->customer->phone ?? '').' '.($sale->customer->nic ?? '').' '.$sale->invoice_number.' '.implode(' ', $sale->items->pluck('item_name')->toArray())) }}">
                    
                    <!-- Header with Monospace Invoice Number & Relative Time -->
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.55rem;">
                        <span style="font-family: 'JetBrains Mono', monospace; font-size: 0.75rem; font-weight: 850; color: #4338ca; background: #eef2ff; border: 1px solid #c7d2fe; padding: 2px 8px; border-radius: 6px;">
                            #{{ $sale->invoice_number }}
                        </span>
                        <span style="font-size: 0.7rem; color: #64748b; font-weight: 600; display: inline-flex; align-items: center; gap: 0.25rem;">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                            {{ $sale->created_at->diffForHumans() }}
                        </span>
                    </div>

                    <!-- Items Sold Section -->
                    <div style="margin-bottom: 0.65rem; padding-bottom: 0.65rem; border-bottom: 1px dashed #e2e8f0;">
                        @foreach($sale->items as $item)
                            <div style="font-size: 0.85rem; font-weight: 800; color: #0f172a; line-height: 1.3;">
                                {{ $item->item_name }}
                                @if($item->quantity > 1)
                                    <span style="color: #64748b; font-size: 0.75rem; font-weight: 600;">(x{{ $item->quantity }})</span>
                                @endif
                            </div>
                            @if($item->phoneDevice)
                                <div style="font-family: 'JetBrains Mono', monospace; font-size: 0.7rem; color: #0284c7; background: #f0f9ff; padding: 2px 6px; border-radius: 4px; display: inline-block; margin-top: 2px;">
                                    IMEI: {{ $item->phoneDevice->imei_1 }}
                                </div>
                            @endif
                        @endforeach
                        
                        <div style="margin-top: 0.45rem; display: flex; align-items: center; justify-content: space-between;">
                            <span class="badge" style="background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; font-size: 0.68rem; font-weight: 750; padding: 2px 7px; border-radius: 6px;">
                                ✓ Stock Deducted
                            </span>
                            <span style="font-size: 0.85rem; font-weight: 850; color: #047857;">
                                LKR {{ number_format($sale->total_amount, 2) }}
                            </span>
                        </div>
                    </div>

                    <!-- Buyer Customer Details Box -->
                    <div style="background: #f8fafc; border-radius: 10px; padding: 0.6rem 0.75rem; border: 1px solid #e2e8f0;">
                        <div style="font-size: 0.68rem; font-weight: 800; text-transform: uppercase; color: #475569; letter-spacing: 0.03em; margin-bottom: 3px; display: flex; align-items: center; gap: 0.35rem;">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                            <span>Buying Customer</span>
                        </div>
                        <div style="font-size: 0.875rem; font-weight: 850; color: #0f172a;">
                            {{ $sale->customer->name ?? 'Walk-in Customer' }}
                        </div>
                        <div style="font-size: 0.75rem; color: #475569; margin-top: 3px; display: flex; align-items: center; gap: 0.45rem; flex-wrap: wrap;">
                            @if($sale->customer?->phone)
                                <span style="color: #2563eb; font-weight: 650; display: inline-flex; align-items: center; gap: 0.25rem;">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                                    <span>{{ $sale->customer->phone }}</span>
                                </span>
                            @endif
                            @if($sale->customer?->nic)
                                <span style="color: #cbd5e1;">&bull;</span>
                                <span style="display: inline-flex; align-items: center; gap: 0.25rem;">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="3" y="4" width="18" height="16" rx="2"></rect><circle cx="9" cy="10" r="2"></circle><line x1="15" y1="8" x2="17" y2="8"></line><line x1="15" y1="12" x2="17" y2="12"></line><line x1="7" y1="16" x2="17" y2="16"></line></svg>
                                    <span>{{ $sale->customer->nic }}</span>
                                </span>
                            @endif
                            @if($sale->customer?->address)
                                <span style="color: #cbd5e1;">&bull;</span>
                                <span style="display: inline-flex; align-items: center; gap: 0.25rem;">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                    <span>{{ $sale->customer->address }}</span>
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Receipt & Slip Buttons -->
                    <div style="margin-top: 0.65rem; display: flex; justify-content: flex-end; align-items: center; gap: 0.4rem;">
                        @if($sale->bank_slip_url)
                            <a href="{{ $sale->bank_slip_url }}" target="_blank" class="btn btn-sm" style="font-size: 0.72rem; padding: 0.25rem 0.55rem; color: #2563eb; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 0.25rem;">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path></svg>
                                <span>Slip</span>
                            </a>
                        @endif
                        <button type="button" onclick="openSaleReceipt({{ $sale->id }})" class="btn btn-sm btn-secondary" style="font-size: 0.75rem; padding: 0.25rem 0.75rem; border-radius: 6px; font-weight: 700; gap: 0.35rem; cursor: pointer; display: inline-flex; align-items: center;">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                            <span>View Receipt</span>
                        </button>
                    </div>
                </div>
            @empty
                <div id="noPurchasesNotice" style="text-align: center; color: var(--text-muted); padding: 3rem 1rem;">
                    <div style="width: 52px; height: 52px; border-radius: 50%; background: #ffffff; color: #94a3b8; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 0.75rem; box-shadow: 0 2px 6px rgba(0,0,0,0.04);">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                    </div>
                    <div style="font-weight: 800; color: #1e293b; font-size: 0.95rem;">No customer purchases yet</div>
                    <div style="font-size: 0.78rem; margin-top: 0.35rem; color: #64748b; line-height: 1.4;">When customers buy phones or accessories, customer details and deducted inventory will appear here live.</div>
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- ==========================================================================
     MODALS
     ========================================================================== -->

<!-- Direct Customer Sale Modal (Quick 1-Click Sell Without Shopping Cart) -->
<div class="modal-backdrop" id="quickSellModal" onclick="if(event.target === this) closeQuickSellModal()">
    <div class="modal-box modal-box-modern">
        <!-- Modern Modal Header -->
        <div class="qs-modal-header">
            <div style="display: flex; align-items: center; gap: 0.65rem;">
                <div class="qs-header-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                </div>
                <div>
                    <h3 style="font-size: 1.05rem; font-weight: 850; color: #0f172a; margin: 0; letter-spacing: -0.01em;">
                        Record Customer Sale
                    </h3>
                    <div style="font-size: 0.72rem; color: #64748b; margin-top: 1px;">Deducts stock immediately and saves buyer details</div>
                </div>
            </div>
            <button type="button" class="qs-close-btn" onclick="closeQuickSellModal()" aria-label="Close modal">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>

        <!-- 2-Step Wizard Progress Indicator -->
        <div class="qs-wizard-steps" id="qsWizardSteps">
            <div class="qs-step-indicator active" id="qsStepIndicator1" onclick="setQsStep(1)" style="cursor: pointer;" title="Step 1: Customer Details">
                <span class="qs-step-num" id="qsStepNum1">1</span>
                <span class="qs-step-label">Customer Details</span>
            </div>
            <div class="qs-step-divider" id="qsStepDivider"></div>
            <div class="qs-step-indicator" id="qsStepIndicator2" onclick="goToQsStep2()" style="cursor: pointer;" title="Step 2: Proceed to Payment">
                <span class="qs-step-num" id="qsStepNum2">2</span>
                <span class="qs-step-label">Payment &amp; Finalize</span>
                <span style="font-size: 0.68rem; background: #ecfdf5; color: #047857; border: 1px solid #86efac; padding: 1px 6px; border-radius: 5px; font-weight: 800;">Pay &rarr;</span>
            </div>
        </div>

        <form id="quickSellForm" onsubmit="submitQuickSell(event)">
            <input type="hidden" id="qsItemType">
            <input type="hidden" id="qsItemId">

            <!-- ================= STEP 1: CUSTOMER & ITEM ================= -->
            <div id="qsStep1Container">
                <!-- Selected Product Hero Card -->
                <div class="qs-product-hero">
                    <div style="display: flex; justify-content: space-between; align-items: center; gap: 0.75rem;">
                        <div style="min-width: 0; flex: 1;">
                            <div style="display: flex; align-items: center; gap: 0.45rem; flex-wrap: wrap;">
                                <span id="qsItemTitle" style="font-weight: 850; color: #0f172a; font-size: 0.95rem; line-height: 1.3;"></span>
                                <span id="qsItemImeiChip" style="display: none;"></span>
                            </div>
                            <div style="font-size: 0.68rem; color: #64748b; margin-top: 2px;">POS Direct Checkout</div>
                        </div>
                        <div style="text-align: right; flex-shrink: 0;">
                            <div style="font-size: 0.62rem; color: #64748b; font-weight: 750; text-transform: uppercase; letter-spacing: 0.03em;">Selling Price</div>
                            <strong id="qsItemPrice" style="font-size: 1.18rem; color: #047857; font-weight: 900; letter-spacing: -0.01em;"></strong>
                        </div>
                    </div>
                </div>

                <!-- Quantity (for accessories) -->
                <div id="qsQtyRow" style="display: none; margin-bottom: 0.55rem; background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 0.5rem 0.8rem;">
                    <label style="font-size: 0.72rem; font-weight: 800; color: #334155; margin-bottom: 0.25rem; display: block; text-transform: uppercase; letter-spacing: 0.03em;">Quantity Sold</label>
                    <input type="number" id="qsQuantity" class="form-control" value="1" min="1" style="height: 34px; border-radius: 8px; font-weight: 750; font-size: 0.875rem;" oninput="updateQsTotal()">
                    <div id="qsStockHint" style="font-size: 0.7rem; color: #64748b; margin-top: 2px;"></div>
                </div>

                <!-- Customer Details Inputs Card -->
                <div class="qs-customer-card">
                    <div class="qs-customer-header">
                        <span style="font-size: 0.76rem; font-weight: 850; text-transform: uppercase; color: #1e293b; letter-spacing: 0.04em; display: inline-flex; align-items: center; gap: 0.35rem;">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#4f46e5" stroke-width="2.2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                            <span>Customer Details</span>
                        </span>
                    </div>

                    <div id="qsDirectFields" style="display: flex; flex-direction: column; gap: 0.45rem;">
                        <!-- Row 1: Name and Phone -->
                        <div style="display: grid; grid-template-columns: 1.15fr 1fr; gap: 0.5rem;">
                            <div class="qs-input-field">
                                <label class="qs-input-label" for="qsCustName">
                                    <span>Customer Name</span>
                                </label>
                                <div class="qs-input-wrapper">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                    <input type="text" id="qsCustName" placeholder="Full Name or Walk-in" oninput="this.classList.remove('input-error')">
                                </div>
                            </div>
                            <div class="qs-input-field">
                                <label class="qs-input-label" for="qsCustPhone">
                                    <span>Contact Phone</span>
                                </label>
                                <div class="qs-input-wrapper">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                                    <input type="text" id="qsCustPhone" placeholder="07XXXXXXXX or Counter" oninput="this.classList.remove('input-error')">
                                </div>
                            </div>
                        </div>

                        <!-- Row 2: NIC and Address -->
                        <div style="display: grid; grid-template-columns: 1fr 1.25fr; gap: 0.5rem;">
                            <div class="qs-input-field">
                                <label class="qs-input-label" for="qsCustNic" style="color: #64748b;">
                                    <span>NIC / National ID</span>
                                </label>
                                <div class="qs-input-wrapper">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="3" y="4" width="18" height="16" rx="2"></rect><line x1="7" y1="8" x2="17" y2="8"></line><line x1="7" y1="12" x2="13" y2="12"></line></svg>
                                    <input type="text" id="qsCustNic" placeholder="NIC Number">
                                </div>
                            </div>
                            <div class="qs-input-field">
                                <label class="qs-input-label" for="qsCustAddress" style="color: #64748b;">
                                    <span>City / Address</span>
                                </label>
                                <div class="qs-input-wrapper">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                    <input type="text" id="qsCustAddress" placeholder="Address or City">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 1 Footer -->
                <div class="qs-modal-footer">
                    <button type="button" class="qs-btn-cancel" onclick="closeQuickSellModal()">Cancel</button>
                    <button type="button" class="qs-btn-confirm" onclick="goToQsStep2()" id="btnGoToStep2" style="min-width: 90px;">
                        <span>Next</span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </button>
                </div>
            </div>

            <!-- ================= STEP 2: PAYMENT & CONFIRMATION ================= -->
            <div id="qsStep2Container" style="display: none;">
                <!-- Summary Strip of Selected Product & Customer -->
                <div class="qs-summary-strip">
                    <div style="flex: 1; min-width: 0;">
                        <div style="display: flex; align-items: center; gap: 0.45rem;">
                            <span style="font-size: 0.68rem; font-weight: 850; text-transform: uppercase; color: #047857; letter-spacing: 0.04em; background: #dcfce7; padding: 1px 6px; border-radius: 4px;">Customer</span>
                            <span class="qs-summary-buyer" id="qsSummaryBuyerName">Walk-in Customer</span>
                        </div>
                        <div class="qs-summary-item">
                            <span id="qsSummaryBuyerPhone" style="color: #2563eb; font-weight: 700;"></span>
                            <span style="color: #cbd5e1;">&bull;</span>
                            <span id="qsSummaryItemTitle" style="color: #334155; font-weight: 650; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 200px;"></span>
                        </div>
                    </div>
                    <div style="text-align: right; display: flex; flex-direction: column; align-items: flex-end; gap: 0.25rem;">
                        <div id="qsSummaryItemPrice" style="font-size: 1.15rem; font-weight: 900; color: #047857;"></div>
                        <button type="button" class="qs-summary-edit-btn" onclick="setQsStep(1)" title="Edit Customer Details">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                            <span>Edit Details</span>
                        </button>
                    </div>
                </div>

                <!-- Payment Method Received As -->
                <div style="margin-bottom: 0.65rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                        <label style="font-size: 0.72rem; font-weight: 850; color: #334155; text-transform: uppercase; letter-spacing: 0.04em; margin: 0;">
                            Payment Received As
                        </label>
                        <span style="font-size: 0.68rem; color: #64748b; font-weight: 600;">Immediate Tender</span>
                    </div>
                    <div class="qs-pay-grid">
                        <label class="qs-pay-card active" id="qsPayCard_cash" onclick="selectQsPayment('cash')">
                            <input type="radio" name="qs_payment_method" value="cash" checked style="display: none;">
                            <div class="qs-pay-icon">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="6" width="20" height="12" rx="2"></rect><circle cx="12" cy="12" r="2"></circle><path d="M6 12h.01M18 12h.01"></path></svg>
                            </div>
                            <span class="qs-pay-name">Cash</span>
                            <span class="qs-pay-sub">Cash Register</span>
                            <div class="qs-pay-check">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            </div>
                        </label>

                        <label class="qs-pay-card" id="qsPayCard_card" onclick="selectQsPayment('card')">
                            <input type="radio" name="qs_payment_method" value="card" style="display: none;">
                            <div class="qs-pay-icon">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                            </div>
                            <span class="qs-pay-name">Card</span>
                            <span class="qs-pay-sub">POS Terminal</span>
                            <div class="qs-pay-check">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            </div>
                        </label>

                        <label class="qs-pay-card" id="qsPayCard_bank" onclick="selectQsPayment('bank_transfer')">
                            <input type="radio" name="qs_payment_method" value="bank_transfer" style="display: none;">
                            <div class="qs-pay-icon">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="21" x2="21" y2="21"></line><line x1="3" y1="10" x2="21" y2="10"></line><polyline points="5 6 12 3 19 6"></polyline><line x1="4" y1="10" x2="4" y2="21"></line><line x1="20" y1="10" x2="20" y2="21"></line></svg>
                            </div>
                            <span class="qs-pay-name">Bank</span>
                            <span class="qs-pay-sub">Direct Transfer</span>
                            <div class="qs-pay-check">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Cash Tender & Change Calculator (Shown for Cash) -->
                <div class="qs-cash-calculator" id="qsCashCalculator">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                        <label for="qsCashTendered" style="font-size: 0.72rem; font-weight: 800; color: #334155; text-transform: uppercase; letter-spacing: 0.03em; margin: 0; display: inline-flex; align-items: center; gap: 0.3rem;">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="2" y="6" width="20" height="12" rx="2"></rect><circle cx="12" cy="12" r="2"></circle></svg>
                            <span>Amount Tendered / Paid (LKR)</span>
                        </label>
                        <div id="qsCashChangeDisplay" style="font-size: 0.78rem; font-weight: 800;"></div>
                    </div>
                    <div class="qs-input-wrapper">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                        <input type="number" id="qsCashTendered" step="0.01" min="0" placeholder="0.00" oninput="calculateQsChange()">
                    </div>
                    <div class="qs-quick-pills">
                        <button type="button" class="qs-quick-pill" onclick="setCashTenderQuick('exact')">Exact Amount</button>
                        <button type="button" class="qs-quick-pill" onclick="setCashTenderQuick(500)">Round 500</button>
                        <button type="button" class="qs-quick-pill" onclick="setCashTenderQuick(1000)">Round 1,000</button>
                        <button type="button" class="qs-quick-pill" onclick="setCashTenderQuick(5000)">Round 5,000</button>
                    </div>
                </div>

                <!-- Card Payment & POS Terminal Details (Shown for Card) -->
                <div id="qsCardPaymentSection" style="display: none; background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 0.75rem 0.9rem; margin-top: 0.65rem; box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.6rem; padding-bottom: 0.45rem; border-bottom: 1px solid #f1f5f9;">
                        <div style="display: flex; align-items: center; gap: 0.45rem;">
                            <div style="width: 26px; height: 26px; border-radius: 7px; background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%); color: #059669; display: flex; align-items: center; justify-content: center; box-shadow: 0 1px 2px rgba(5, 150, 105, 0.15);">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                            </div>
                            <div>
                                <div style="font-size: 0.78rem; font-weight: 800; color: #0f172a; line-height: 1.2;">Card POS Terminal</div>
                                <div style="font-size: 0.66rem; color: #64748b; font-weight: 500;">Swipe, Chip or Contactless Tap</div>
                            </div>
                        </div>
                        <span style="font-size: 0.67rem; font-weight: 700; color: #047857; background: #ecfdf5; padding: 3px 8px; border-radius: 9999px; border: 1px solid #a7f3d0; display: inline-flex; align-items: center; gap: 0.3rem;">
                            <span style="width: 6px; height: 6px; border-radius: 50%; background: #10b981; display: inline-block;"></span>
                            Terminal Ready
                        </span>
                    </div>

                    <!-- Card Brand Quick Selector -->
                    <div style="margin-bottom: 0.55rem;">
                        <label style="font-size: 0.7rem; font-weight: 750; color: #475569; display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                            <span>Card Network</span>
                            <span style="font-size: 0.63rem; color: #94a3b8; font-weight: 500;">Select network</span>
                        </label>
                        <div style="display: flex; gap: 0.35rem; flex-wrap: wrap;">
                            <button type="button" class="qs-card-brand-btn active" data-brand="Visa" onclick="selectQsCardBrand('Visa')">
                                <span style="font-weight: 900; color: #1e40af; letter-spacing: -0.02em;">VISA</span>
                            </button>
                            <button type="button" class="qs-card-brand-btn" data-brand="Mastercard" onclick="selectQsCardBrand('Mastercard')">
                                <span style="display: inline-flex; align-items: center; margin-right: 2px;"><span style="width: 8px; height: 8px; border-radius: 50%; background: #eb001b; display: inline-block;"></span><span style="width: 8px; height: 8px; border-radius: 50%; background: #f79e1b; display: inline-block; margin-left: -3px;"></span></span> Mastercard
                            </button>
                            <button type="button" class="qs-card-brand-btn" data-brand="LankaPay" onclick="selectQsCardBrand('LankaPay')">
                                <span style="font-weight: 800; color: #0d9488;">LankaPay</span>
                            </button>
                            <button type="button" class="qs-card-brand-btn" data-brand="Amex" onclick="selectQsCardBrand('Amex')">
                                <span style="font-weight: 800; color: #0284c7;">AMEX</span>
                            </button>
                        </div>
                        <input type="hidden" id="qsCardBrand" value="Visa">
                    </div>

                    <!-- Row with Approval Code & Card Last 4 Digits -->
                    <div style="display: grid; grid-template-columns: 1.25fr 1fr; gap: 0.55rem; margin-bottom: 0.55rem;">
                        <div class="qs-input-field">
                            <label class="qs-input-label" for="qsCardApprRef" style="color: #475569; font-size: 0.7rem; font-weight: 750; margin-bottom: 0.2rem; display: flex; justify-content: space-between;">
                                <span>Auth / Approval Code</span>
                                <span style="font-size: 0.63rem; color: #94a3b8; font-weight: 500;">Slip trace</span>
                            </label>
                            <div class="qs-input-wrapper">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                <input type="text" id="qsCardApprRef" placeholder="e.g. 648291 / Trace 102" style="font-family: 'JetBrains Mono', monospace; font-size: 0.8125rem;">
                            </div>
                        </div>
                        <div class="qs-input-field">
                            <label class="qs-input-label" for="qsCardLast4" style="color: #475569; font-size: 0.7rem; font-weight: 750; margin-bottom: 0.2rem; display: flex; justify-content: space-between;">
                                <span>Last 4 Digits</span>
                                <span style="font-size: 0.63rem; color: #94a3b8; font-weight: 500;">Card no.</span>
                            </label>
                            <div class="qs-input-wrapper">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                                <input type="text" id="qsCardLast4" maxlength="4" placeholder="•••• 4321" style="font-family: 'JetBrains Mono', monospace; font-size: 0.8125rem; letter-spacing: 0.05em;" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 4)">
                            </div>
                        </div>
                    </div>

                    <!-- Direct Terminal Sale Reassurance Banner (Replacing slip upload) -->
                    <div style="display: flex; align-items: center; gap: 0.5rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.45rem 0.65rem;">
                        <div style="width: 20px; height: 20px; border-radius: 50%; background: #ecfdf5; color: #059669; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        </div>
                        <div style="font-size: 0.71rem; color: #475569; line-height: 1.35;">
                            <span style="font-weight: 750; color: #0f172a;">Counter Payment:</span> Swipe or tap card on the terminal to process payment. Slip upload is not required.
                        </div>
                    </div>
                </div>

                <!-- Bank Transfer Details & Deposit Slip Upload (Shown for Bank) -->
                <div id="qsBankTransferSection" style="display: none; background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 0.75rem 0.9rem; margin-top: 0.65rem; box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.6rem; padding-bottom: 0.45rem; border-bottom: 1px solid #f1f5f9;">
                        <div style="display: flex; align-items: center; gap: 0.45rem;">
                            <div style="width: 26px; height: 26px; border-radius: 7px; background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); color: #2563eb; display: flex; align-items: center; justify-content: center; box-shadow: 0 1px 2px rgba(37, 99, 235, 0.15);">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><line x1="3" y1="21" x2="21" y2="21"></line><line x1="3" y1="10" x2="21" y2="10"></line><polyline points="5 6 12 3 19 6"></polyline><line x1="4" y1="10" x2="4" y2="21"></line><line x1="20" y1="10" x2="20" y2="21"></line></svg>
                            </div>
                            <div>
                                <div style="font-size: 0.78rem; font-weight: 800; color: #0f172a; line-height: 1.2;">Bank Transfer &amp; Deposit</div>
                                <div style="font-size: 0.66rem; color: #64748b; font-weight: 500;">Direct Deposit / Online Transfer</div>
                            </div>
                        </div>
                        <span style="font-size: 0.67rem; font-weight: 700; color: #2563eb; background: #eff6ff; padding: 3px 8px; border-radius: 9999px; border: 1px solid #bfdbfe;">
                            Direct Bank Deposit
                        </span>
                    </div>

                    <!-- Reference Number -->
                    <div class="qs-input-field" style="margin-bottom: 0.55rem;">
                        <label class="qs-input-label" for="qsBankRef" style="color: #475569; font-size: 0.7rem; font-weight: 750; margin-bottom: 0.2rem; display: flex; justify-content: space-between;">
                            <span>Bank Reference / Transaction ID</span>
                            <span style="font-size: 0.63rem; color: #94a3b8; font-weight: 500;">Payer ref / Slip ID</span>
                        </label>
                        <div class="qs-input-wrapper">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                            <input type="text" id="qsBankRef" placeholder="e.g. TXN-984726, Slip No, Payer Name" style="font-family: 'JetBrains Mono', monospace; font-size: 0.8125rem;">
                        </div>
                    </div>

                    <!-- Slip File Upload -->
                    <div>
                        <label style="font-size: 0.7rem; font-weight: 750; color: #475569; display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.2rem;">
                            <span>Upload Bank Transfer Slip (Photo / PDF)</span>
                            <span style="font-size: 0.65rem; color: #94a3b8; font-weight: 500;">JPG, PNG, PDF up to 5MB</span>
                        </label>

                        <!-- Upload Drop Box -->
                        <div id="qsBankSlipDropzone" style="background: #ffffff; border: 1.5px dashed #94a3b8; border-radius: 8px; padding: 0.6rem; text-align: center; cursor: pointer; transition: all 0.2s ease; position: relative;" onclick="document.getElementById('qsBankSlipInput').click()">
                            <input type="file" id="qsBankSlipInput" accept="image/png,image/jpeg,image/webp,application/pdf" style="display: none;" onchange="handleQsBankSlipSelected(this)">
                            
                            <div id="qsBankSlipPlaceholder" style="display: flex; flex-direction: column; align-items: center; gap: 0.2rem;">
                                <div style="width: 30px; height: 30px; border-radius: 50%; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center;">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                                </div>
                                <div style="font-size: 0.78rem; font-weight: 700; color: #1e293b;">Click to upload customer bank slip</div>
                                <div style="font-size: 0.68rem; color: #64748b;">Or drag &amp; drop bank deposit receipt / transfer screenshot</div>
                            </div>

                            <!-- Preview (shown once selected) -->
                            <div id="qsBankSlipPreview" style="display: none; align-items: center; justify-content: space-between; gap: 0.65rem; text-align: left;">
                                <div style="display: flex; align-items: center; gap: 0.55rem; min-width: 0;">
                                    <img id="qsBankSlipImg" src="" alt="Slip Preview" style="width: 42px; height: 42px; object-fit: cover; border-radius: 6px; border: 1px solid #cbd5e1; display: none;">
                                    <div id="qsBankSlipPdfIcon" style="width: 42px; height: 42px; border-radius: 6px; background: #fee2e2; color: #dc2626; display: none; align-items: center; justify-content: center; font-weight: 800; font-size: 0.72rem; flex-shrink: 0;">
                                        PDF
                                    </div>
                                    <div style="min-width: 0;">
                                        <div id="qsBankSlipFileName" style="font-size: 0.78rem; font-weight: 700; color: #0f172a; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"></div>
                                        <div id="qsBankSlipFileSize" style="font-size: 0.68rem; color: #10b981; font-weight: 600;">✓ Ready to attach to sale</div>
                                    </div>
                                </div>
                                <button type="button" onclick="event.stopPropagation(); clearQsBankSlip();" class="btn btn-sm" style="background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; border-radius: 5px; padding: 0.2rem 0.5rem; font-size: 0.7rem; font-weight: 700; cursor: pointer;">
                                    Remove
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2 Footer Buttons -->
                <div class="qs-modal-footer">
                    <button type="button" class="qs-btn-cancel" onclick="setQsStep(1)">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"></polyline></svg>
                        <span>Back</span>
                    </button>
                    <button type="submit" id="btnConfirmQuickSell" class="qs-btn-confirm" style="flex: 1; height: 38px; font-size: 0.875rem;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        <span>Complete</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Receipt Modal -->
<div class="modal-backdrop" id="receiptModal">
    <div class="modal-box" style="max-width: 480px; font-family: 'JetBrains Mono', monospace; border-radius: 20px; overflow: hidden; box-shadow: 0 25px 60px -15px rgba(15, 23, 42, 0.25); border: 1px solid #e2e8f0;">
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 1.15rem 1.4rem; border-bottom: 1px solid #f1f5f9; background: #ffffff;">
            <div style="display: flex; align-items: center; gap: 0.65rem;">
                <div style="width: 36px; height: 36px; border-radius: 10px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #059669; display: flex; align-items: center; justify-content: center;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                </div>
                <div>
                    <h3 style="font-weight: 850; font-size: 1.05rem; color: #0f172a; margin: 0;">Official POS Receipt</h3>
                    <div style="font-size: 0.72rem; color: #64748b;">Instant printed customer receipt &amp; ledger proof</div>
                </div>
            </div>
            <button class="qs-close-btn" onclick="closeModal('receiptModal')" aria-label="Close modal">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>
        <div class="modal-body" id="receiptContent" style="font-size: 0.8125rem; padding: 1.25rem 1.4rem; background: #ffffff;">
            <!-- Dynamic Receipt populated by JS -->
        </div>
        <div style="padding: 1rem 1.4rem; border-top: 1px solid #f1f5f9; display: flex; gap: 0.65rem; background: #f8fafc;">
            <a href="#" id="receiptFullViewBtn" target="_blank" class="qs-btn-cancel" style="font-size: 0.8rem; display: inline-flex; align-items: center; text-decoration: none; padding: 0 0.95rem;">
                View Full Invoice
            </a>
            <button class="qs-btn-confirm" style="flex: 1; height: 42px;" onclick="printPosReceipt()">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                <span>Print Receipt</span>
            </button>
            <button class="qs-btn-cancel" onclick="closeModal('receiptModal')" style="padding: 0 1rem;">Done</button>
        </div>
    </div>
</div>

<!-- Recent Sales Modal -->
<div class="modal-backdrop" id="recentSalesModal">
    <div class="modal-box" style="max-width: 780px; border-radius: 16px;">
        <div class="modal-header">
            <h3 style="font-weight: 850; display: flex; align-items: center; gap: 0.5rem; font-size: 1.1rem;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                <span>Recent Invoices &amp; Buyer Log</span>
            </h3>
            <button class="modal-close" onclick="closeModal('recentSalesModal')">&times;</button>
        </div>
        <div class="modal-body" style="padding: 0;">
            <div class="table-responsive" style="max-height: 460px; overflow-y: auto;">
                <table class="data-table" style="margin: 0;">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Time</th>
                            <th>Buying Customer</th>
                            <th>Items &amp; IMEI</th>
                            <th>Total</th>
                            <th style="text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentSales as $sale)
                        <tr>
                            <td>
                                <strong style="color: #4f46e5; font-family: 'JetBrains Mono', monospace; font-size: 0.8125rem; background: #eef2ff; padding: 3px 7px; border-radius: 6px; border: 1px solid #c7d2fe;">
                                    #{{ $sale->invoice_number }}
                                </strong>
                            </td>
                            <td style="font-size: 0.75rem; color: var(--text-muted); white-space: nowrap;">
                                {{ \Carbon\Carbon::parse($sale->created_at)->format('h:i A') }}
                            </td>
                            <td>
                                @if($sale->customer)
                                    <div style="font-size: 0.85rem; font-weight: 800; color: #0f172a;">{{ $sale->customer->name }}</div>
                                    <div style="font-size: 0.75rem; color: #2563eb; font-weight: 600; display: inline-flex; align-items: center; gap: 0.25rem;">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                                        <span>{{ $sale->customer->phone }}</span>
                                    </div>
                                    @if($sale->customer->nic)
                                        <div style="font-size: 0.7rem; color: #64748b;">ID: {{ $sale->customer->nic }}</div>
                                    @endif
                                @else
                                    <span style="color: #64748b; font-size: 0.8rem;">Walk-in Customer</span>
                                @endif
                            </td>
                            <td>
                                <div style="font-size: 0.8125rem; max-width: 280px;">
                                    @foreach($sale->items as $it)
                                        <div style="margin-bottom: 2px;">
                                            <span style="font-weight: 650; color: #0f172a;">{{ $it->item_name }}</span>
                                            @if($it->phoneDevice)
                                                <span class="badge" style="background: #e0f2fe; color: #0284c7; font-size: 0.65rem; font-family: 'JetBrains Mono', monospace; padding: 1px 5px; border-radius: 4px;">
                                                    IMEI: {{ $it->phoneDevice->imei_1 }}
                                                </span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                            <td>
                                <strong style="color: #047857; font-size: 0.875rem;">LKR {{ number_format($sale->total_amount, 2) }}</strong>
                                <div style="font-size: 0.7rem; color: #64748b; text-transform: uppercase;">{{ $sale->payment_method }}</div>
                                @if($sale->bank_slip_url)
                                    <div style="margin-top: 2px;">
                                        <a href="{{ $sale->bank_slip_url }}" target="_blank" style="font-size: 0.68rem; color: #2563eb; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 0.2rem;">
                                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path></svg>
                                            <span>Slip</span>
                                        </a>
                                    </div>
                                @endif
                            </td>
                            <td style="text-align: right; white-space: nowrap;">
                                <button type="button" onclick="closeModal('recentSalesModal'); openSaleReceipt({{ $sale->id }});" class="btn btn-secondary btn-sm" style="padding: 0.3rem 0.65rem; font-size: 0.75rem; border-radius: 6px; font-weight: 650; cursor: pointer; display: inline-flex; align-items: center; gap: 0.35rem;">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                                    <span>View Receipt</span>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 3rem;">
                                No recent sales transactions found today.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--surface-border); display: flex; justify-content: space-between; align-items: center; background: #fafbfc; border-radius: 0 0 16px 16px;">
            <a href="{{ route('shop.sales') }}" class="btn btn-secondary btn-sm" style="border-radius: 8px; font-weight: 650;">View Full Sales Directory</a>
            <button type="button" class="btn btn-primary btn-sm" onclick="closeModal('recentSalesModal')" style="border-radius: 8px; font-weight: 700;">Close</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // In-memory map of sales records for instant receipt popup
    const salesMap = {};
    @foreach($recentSales as $sale)
        salesMap[{{ $sale->id }}] = @json($sale);
    @endforeach

    async function openSaleReceipt(saleId) {
        if (salesMap[saleId]) {
            showReceipt(salesMap[saleId]);
            return;
        }
        try {
            const res = await fetch(`/shop/sales/${saleId}`, {
                headers: { 
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            if (res.ok) {
                const sale = await res.json();
                salesMap[sale.id] = sale;
                showReceipt(sale);
            } else {
                window.open(`/shop/sales/${saleId}`, '_blank');
            }
        } catch (err) {
            window.open(`/shop/sales/${saleId}`, '_blank');
        }
    }

    let currentStockFilter = 'all'; // 'all' | 'instock' | 'sold'

    function switchTab(tab, el) {
        document.querySelectorAll('.pos-tab').forEach(t => t.classList.remove('active'));
        el.classList.add('active');
        if (tab === 'phones') {
            document.getElementById('tabPhones').style.display = 'grid';
            document.getElementById('tabProducts').style.display = 'none';
        } else {
            document.getElementById('tabPhones').style.display = 'none';
            document.getElementById('tabProducts').style.display = 'grid';
        }
    }

    function filterByStockStatus(status, btn) {
        currentStockFilter = status;
        document.querySelectorAll('#phonesFilterPills .filter-pill').forEach(p => {
            p.classList.remove('active', 'active-instock', 'active-sold');
        });
        if (status === 'all') {
            btn.classList.add('active');
        } else if (status === 'instock') {
            btn.classList.add('active-instock');
        } else if (status === 'sold') {
            btn.classList.add('active-sold');
        }
        filterItems();
    }

    function filterItems() {
        const query = (document.getElementById('searchInput').value || '').toLowerCase().trim();
        const cat = (document.getElementById('categoryFilter').value || '').toLowerCase().trim();

        document.querySelectorAll('.item-card').forEach(card => {
            const name = (card.dataset.name || '').toLowerCase();
            const imei = (card.dataset.imei || '').toLowerCase();
            const barcode = (card.dataset.barcode || '').toLowerCase();
            const category = (card.dataset.category || '').toLowerCase();
            const isSold = card.classList.contains('item-sold');

            const matchesQuery = !query || name.includes(query) || imei.includes(query) || barcode.includes(query);
            const matchesCat = !cat || category.includes(cat);

            let matchesStatus = true;
            if (currentStockFilter === 'instock') {
                matchesStatus = !isSold;
            } else if (currentStockFilter === 'sold') {
                matchesStatus = isSold;
            }

            card.style.display = (matchesQuery && matchesCat && matchesStatus) ? 'flex' : 'none';
        });
    }

    function updateStockCounts() {
        const phoneCards = document.querySelectorAll('#tabPhones .pos-item-card');
        let total = phoneCards.length;
        let sold = 0;
        phoneCards.forEach(c => {
            if (c.classList.contains('item-sold')) sold++;
        });
        let inStock = total - sold;

        const elTotal = document.getElementById('allPhonesCount');
        const elInStock = document.getElementById('inStockPhonesCount');
        const elSold = document.getElementById('soldPhonesCount');

        if (elTotal) elTotal.innerText = total;
        if (elInStock) elInStock.innerText = inStock;
        if (elSold) elSold.innerText = sold;
    }

    function showCopiedToast(text) {
        let toast = document.getElementById('posCopiedToast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'posCopiedToast';
            toast.style.cssText = 'position: fixed; bottom: 30px; right: 30px; z-index: 9999; background: #0f172a; color: #ffffff; padding: 0.65rem 1rem; border-radius: 10px; font-size: 0.8rem; font-weight: 700; box-shadow: 0 10px 25px rgba(0,0,0,0.25); display: flex; align-items: center; gap: 0.5rem; transition: all 0.25s ease; opacity: 0; transform: translateY(10px); pointer-events: none;';
            document.body.appendChild(toast);
        }
        toast.innerHTML = `<span style="color: #38bdf8;">✓ Copied:</span> <span>${text}</span>`;
        toast.style.opacity = '1';
        toast.style.transform = 'translateY(0)';
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(10px)';
        }, 1800);
    }

    let currentQsUnitPrice = 0;
    let currentQsMaxStock = 1;
    let currentQsStep = 1;

    function setQsStep(step) {
        currentQsStep = step;
        const step1Container = document.getElementById('qsStep1Container');
        const step2Container = document.getElementById('qsStep2Container');
        const ind1 = document.getElementById('qsStepIndicator1');
        const ind2 = document.getElementById('qsStepIndicator2');
        const num1 = document.getElementById('qsStepNum1');
        const divider = document.getElementById('qsStepDivider');

        if (step === 2) {
            if (step1Container) step1Container.style.display = 'none';
            if (step2Container) step2Container.style.display = 'block';

            if (ind1) {
                ind1.classList.remove('active');
                ind1.classList.add('completed');
            }
            if (num1) {
                num1.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>';
            }
            if (divider) divider.classList.add('active');
            if (ind2) {
                ind2.classList.add('active');
            }

            // Sync Summary
            const custName = (document.getElementById('qsCustName')?.value || '').trim() || 'Walk-in Customer';
            const custPhone = (document.getElementById('qsCustPhone')?.value || '').trim() || 'Counter';
            const title = document.getElementById('qsItemTitle')?.innerText || '';
            const price = document.getElementById('qsItemPrice')?.innerText || '';

            const sumBuyer = document.getElementById('qsSummaryBuyerName');
            const sumPhone = document.getElementById('qsSummaryBuyerPhone');
            const sumTitle = document.getElementById('qsSummaryItemTitle');
            const sumPrice = document.getElementById('qsSummaryItemPrice');

            if (sumBuyer) sumBuyer.innerText = custName;
            if (sumPhone) sumPhone.innerText = custPhone;
            if (sumTitle) sumTitle.innerText = title;
            if (sumPrice) sumPrice.innerText = price;

            // Initialize cash tender calculator with total
            const qty = parseInt(document.getElementById('qsQuantity')?.value) || 1;
            const total = currentQsUnitPrice * qty;
            const cashTenderInp = document.getElementById('qsCashTendered');
            if (cashTenderInp && (!cashTenderInp.value || parseFloat(cashTenderInp.value) <= 0)) {
                cashTenderInp.value = total.toFixed(2);
            }
            calculateQsChange();
        } else {
            if (step1Container) step1Container.style.display = 'block';
            if (step2Container) step2Container.style.display = 'none';

            if (ind1) {
                ind1.classList.add('active');
                ind1.classList.remove('completed');
            }
            if (num1) {
                num1.innerHTML = '1';
            }
            if (divider) divider.classList.remove('active');
            if (ind2) {
                ind2.classList.remove('active', 'completed');
            }

            setTimeout(() => document.getElementById('qsCustName')?.focus(), 100);
        }
    }


    function goToQsStep2() {
        const nameInp = document.getElementById('qsCustName');
        const phoneInp = document.getElementById('qsCustPhone');

        // Automatically default to Walk-in Customer if left blank so payment is never blocked
        if (nameInp && !nameInp.value.trim()) {
            nameInp.value = 'Walk-in Customer';
        }
        if (phoneInp && !phoneInp.value.trim()) {
            phoneInp.value = 'Counter';
        }

        if (nameInp) nameInp.classList.remove('input-error');
        if (phoneInp) phoneInp.classList.remove('input-error');

        setQsStep(2);
    }

    function selectQsPayment(method) {
        document.querySelectorAll('.qs-pay-card').forEach(card => card.classList.remove('active'));
        const activeCard = document.getElementById('qsPayCard_' + (method === 'bank_transfer' ? 'bank' : method));
        if (activeCard) {
            activeCard.classList.add('active');
            const radio = activeCard.querySelector('input[type="radio"]');
            if (radio) radio.checked = true;
        }

        const cashCalc = document.getElementById('qsCashCalculator');
        if (cashCalc) {
            cashCalc.style.display = (method === 'cash') ? 'block' : 'none';
        }

        const cardSec = document.getElementById('qsCardPaymentSection');
        if (cardSec) {
            cardSec.style.display = (method === 'card') ? 'block' : 'none';
        }

        const bankSec = document.getElementById('qsBankTransferSection');
        if (bankSec) {
            bankSec.style.display = (method === 'bank_transfer') ? 'block' : 'none';
        }
    }

    function selectQsCardBrand(brand) {
        const brandInp = document.getElementById('qsCardBrand');
        if (brandInp) brandInp.value = brand;
        document.querySelectorAll('.qs-card-brand-btn').forEach(btn => {
            if (btn.getAttribute('data-brand') === brand) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });
    }

    function clearQsCardSlip() {
        const input = document.getElementById('qsCardSlipInput');
        if (input) input.value = '';
    }

    function calculateQsChange() {
        const qty = parseInt(document.getElementById('qsQuantity')?.value) || 1;
        const total = currentQsUnitPrice * qty;
        const tenderInp = document.getElementById('qsCashTendered');
        const tenderVal = parseFloat(tenderInp?.value) || 0;
        const change = tenderVal - total;
        const display = document.getElementById('qsCashChangeDisplay');
        if (!display) return;

        if (tenderVal <= 0) {
            display.innerHTML = '';
        } else if (change >= 0) {
            display.innerHTML = `<span style="color: #047857; font-weight: 800;">Change: LKR ${change.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>`;
        } else {
            display.innerHTML = `<span style="color: #dc2626; font-weight: 800;">Short: LKR ${Math.abs(change).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>`;
        }
    }

    function setCashTenderQuick(type) {
        const qty = parseInt(document.getElementById('qsQuantity')?.value) || 1;
        const total = currentQsUnitPrice * qty;
        let tendered = total;
        if (type === 'exact') tendered = total;
        else if (type === 500) tendered = Math.ceil(total / 500) * 500;
        else if (type === 1000) tendered = Math.ceil(total / 1000) * 1000;
        else if (type === 5000) tendered = Math.ceil(total / 5000) * 5000;
        if (tendered < total) tendered = total;

        const tenderInp = document.getElementById('qsCashTendered');
        if (tenderInp) {
            tenderInp.value = tendered.toFixed(2);
            calculateQsChange();
        }
    }

    function openQuickSellModal(type, id, title, price, maxQty) {
        document.getElementById('qsItemType').value = type;
        document.getElementById('qsItemId').value = id;
        
        // Format title and IMEI pill cleanly
        const titleEl = document.getElementById('qsItemTitle');
        const imeiChip = document.getElementById('qsItemImeiChip');
        const imeiMatch = title.match(/\(IMEI:\s*([^)]+)\)/i);
        if (imeiMatch) {
            const cleanTitle = title.replace(/\(IMEI:[^)]+\)/i, '').trim();
            titleEl.innerText = cleanTitle;
            imeiChip.style.display = 'inline-flex';
            imeiChip.innerHTML = `
                <span style="font-family: 'JetBrains Mono', monospace; font-size: 0.7rem; background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; padding: 1px 6px; border-radius: 5px; font-weight: 750; display: inline-flex; align-items: center; gap: 0.25rem;">
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="5" y="2" width="14" height="20" rx="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line></svg>
                    <span>IMEI: ${imeiMatch[1]}</span>
                </span>
            `;
        } else {
            titleEl.innerText = title;
            imeiChip.style.display = 'none';
            imeiChip.innerHTML = '';
        }

        currentQsUnitPrice = parseFloat(price);
        currentQsMaxStock = maxQty;

        const qtyRow = document.getElementById('qsQtyRow');
        const qtyInp = document.getElementById('qsQuantity');
        if (type === 'product' && maxQty > 1) {
            qtyRow.style.display = 'block';
            qtyInp.max = maxQty;
            qtyInp.value = 1;
            document.getElementById('qsStockHint').innerText = 'Maximum ' + maxQty + ' units available in stock.';
        } else {
            qtyRow.style.display = 'none';
            qtyInp.value = 1;
        }

        // Reset inputs, error classes, & cash tendered
        const custNameInp = document.getElementById('qsCustName');
        if (custNameInp) { custNameInp.value = ''; custNameInp.classList.remove('input-error'); }
        const custPhoneInp = document.getElementById('qsCustPhone');
        if (custPhoneInp) { custPhoneInp.value = ''; custPhoneInp.classList.remove('input-error'); }
        const custNicInp = document.getElementById('qsCustNic');
        if (custNicInp) custNicInp.value = '';
        const custAddrInp = document.getElementById('qsCustAddress');
        if (custAddrInp) custAddrInp.value = '';

        const cashTenderInp = document.getElementById('qsCashTendered');
        if (cashTenderInp) cashTenderInp.value = '';

        // Reset card inputs & terminal slip
        const cardApprRef = document.getElementById('qsCardApprRef');
        if (cardApprRef) cardApprRef.value = '';
        const cardLast4 = document.getElementById('qsCardLast4');
        if (cardLast4) cardLast4.value = '';
        selectQsCardBrand('Visa');
        clearQsCardSlip();

        // Reset bank transfer inputs & slip upload
        const bankRefInp = document.getElementById('qsBankRef');
        if (bankRefInp) bankRefInp.value = '';
        clearQsBankSlip();

        selectQsPayment('cash');
        updateQsTotal();
        setQsStep(1);

        const confirmBtn = document.getElementById('btnConfirmQuickSell');
        if (confirmBtn) {
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> <span>Complete</span>';
        }

        document.getElementById('quickSellModal').classList.add('open');
        setTimeout(() => document.getElementById('qsCustName')?.focus(), 150);
    }

    function handleQsBankSlipSelected(input) {
        if (!input.files || !input.files[0]) return;
        const file = input.files[0];

        // 5MB limit
        if (file.size > 5 * 1024 * 1024) {
            alert('File size exceeds 5MB limit. Please choose a smaller image or PDF.');
            input.value = '';
            return;
        }

        const placeholder = document.getElementById('qsBankSlipPlaceholder');
        const preview = document.getElementById('qsBankSlipPreview');
        const img = document.getElementById('qsBankSlipImg');
        const pdfIcon = document.getElementById('qsBankSlipPdfIcon');
        const fileName = document.getElementById('qsBankSlipFileName');
        const dropzone = document.getElementById('qsBankSlipDropzone');

        if (fileName) fileName.textContent = file.name;
        if (dropzone) dropzone.style.borderColor = '#10b981';

        if (file.type === 'application/pdf') {
            if (img) img.style.display = 'none';
            if (pdfIcon) pdfIcon.style.display = 'flex';
        } else {
            if (pdfIcon) pdfIcon.style.display = 'none';
            if (img) {
                const reader = new FileReader();
                reader.onload = e => {
                    img.src = e.target.result;
                    img.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        }

        if (placeholder) placeholder.style.display = 'none';
        if (preview) preview.style.display = 'flex';
    }

    function clearQsBankSlip() {
        const input = document.getElementById('qsBankSlipInput');
        if (input) input.value = '';
        const placeholder = document.getElementById('qsBankSlipPlaceholder');
        const preview = document.getElementById('qsBankSlipPreview');
        const img = document.getElementById('qsBankSlipImg');
        const pdfIcon = document.getElementById('qsBankSlipPdfIcon');
        const dropzone = document.getElementById('qsBankSlipDropzone');

        if (placeholder) placeholder.style.display = 'flex';
        if (preview) preview.style.display = 'none';
        if (img) {
            img.src = '';
            img.style.display = 'none';
        }
        if (pdfIcon) pdfIcon.style.display = 'none';
        if (dropzone) dropzone.style.borderColor = '#94a3b8';
    }

    function updateQsTotal() {
        const qty = parseInt(document.getElementById('qsQuantity').value) || 1;
        const total = (currentQsUnitPrice * qty).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('qsItemPrice').innerText = 'LKR ' + total;
        const sumPrice = document.getElementById('qsSummaryItemPrice');
        if (sumPrice) sumPrice.innerText = 'LKR ' + total;
        calculateQsChange();
    }

    function closeQuickSellModal() {
        document.getElementById('quickSellModal').classList.remove('open');
    }



    function filterCustomerPurchases(query) {
        const q = (query || '').toLowerCase().trim();
        const cards = document.querySelectorAll('.purchase-feed-card');
        cards.forEach(card => {
            const text = (card.getAttribute('data-search') || '').toLowerCase();
            card.style.display = (!q || text.includes(q)) ? 'block' : 'none';
        });
    }

    async function submitQuickSell(e) {
        e.preventDefault();

        // If currently in step 1, advance to step 2 first
        if (currentQsStep === 1) {
            goToQsStep2();
            return;
        }

        const type = document.getElementById('qsItemType').value;
        const id = parseInt(document.getElementById('qsItemId').value);
        const qty = parseInt(document.getElementById('qsQuantity').value) || 1;
        let custName = (document.getElementById('qsCustName')?.value || '').trim();
        let custPhone = (document.getElementById('qsCustPhone')?.value || '').trim();
        const custNic = (document.getElementById('qsCustNic')?.value || '').trim();
        const custAddress = (document.getElementById('qsCustAddress')?.value || '').trim();
        const customerId = document.getElementById('qsExistingSelect') ? document.getElementById('qsExistingSelect').value : null;
        const paymentMethod = document.querySelector('input[name="qs_payment_method"]:checked')?.value || 'cash';

        if (!custName) custName = 'Walk-in Customer';
        if (!custPhone) custPhone = 'Counter';

        const totalAmount = currentQsUnitPrice * qty;
        const tenderInp = document.getElementById('qsCashTendered');
        const cashTendered = parseFloat(tenderInp?.value) || totalAmount;
        const changeAmount = Math.max(0, cashTendered - totalAmount);

        const btn = document.getElementById('btnConfirmQuickSell');
        btn.disabled = true;
        btn.innerText = 'Completing...';

        const formData = new FormData();
        if (customerId) formData.append('customer_id', customerId);
        formData.append('customer_name', custName);
        formData.append('customer_phone', custPhone);
        if (custNic) formData.append('customer_nic', custNic);
        if (custAddress) formData.append('customer_address', custAddress);
        formData.append('discount_amount', '0');

        if (type === 'product') {
            formData.append('items[0][product_id]', id);
        } else {
            formData.append('items[0][phone_device_id]', id);
        }
        formData.append('items[0][quantity]', qty);
        formData.append('items[0][unit_price]', currentQsUnitPrice);

        formData.append('payments[0][payment_method]', paymentMethod);
        formData.append('payments[0][amount]', totalAmount);

        if (paymentMethod === 'card') {
            const brand = (document.getElementById('qsCardBrand')?.value || '').trim();
            const apprRef = (document.getElementById('qsCardApprRef')?.value || '').trim();
            const last4 = (document.getElementById('qsCardLast4')?.value || '').trim();

            let refParts = [];
            if (brand) refParts.push(brand);
            if (apprRef) refParts.push('Auth: ' + apprRef);
            if (last4) refParts.push('Card: •••• ' + last4);

            const cardRef = refParts.join(' • ');
            if (cardRef) {
                formData.append('payments[0][reference]', cardRef);
            }

            const cardSlipInput = document.getElementById('qsCardSlipInput');
            if (cardSlipInput && cardSlipInput.files && cardSlipInput.files[0]) {
                formData.append('card_slip', cardSlipInput.files[0]);
            }
        } else if (paymentMethod === 'bank_transfer') {
            const bankRef = (document.getElementById('qsBankRef')?.value || '').trim();
            if (bankRef) {
                formData.append('payments[0][reference]', bankRef);
            }
            const slipInput = document.getElementById('qsBankSlipInput');
            if (slipInput && slipInput.files && slipInput.files[0]) {
                formData.append('bank_slip', slipInput.files[0]);
            }
        }

        try {
            const res = await fetch("{{ route('shop.pos.checkout') }}", {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: formData
            });

            const data = await res.json();
            if (!res.ok) {
                throw new Error(data.message || 'Error processing sale.');
            }

            // 1. Immediately deduct stock on screen and display buyer details
            if (type === 'phone') {
                const card = document.getElementById('phoneCard' + id);
                const badge = document.getElementById('phoneBadge' + id);
                const sellBtn = document.getElementById('phoneSellBtn' + id);
                const buyerBox = document.getElementById('phoneBuyerBox' + id);
                const buyerName = document.getElementById('phoneBuyerName' + id);
                const buyerPhone = document.getElementById('phoneBuyerPhone' + id);

                if (card) {
                    card.classList.add('item-sold');
                }
                if (badge) {
                    badge.className = 'badge-status-sold';
                    badge.innerHTML = '✓ Sold';
                }
                if (sellBtn) {
                    sellBtn.outerHTML = '<button type="button" class="btn-out-of-stock" disabled>✓ Out of Stock</button>';
                }
                if (buyerBox) {
                    buyerBox.style.display = 'block';
                    if (buyerName) buyerName.innerText = custName;
                    if (buyerPhone) buyerPhone.innerText = custPhone;
                }
            } else {
                // Accessory stock decrement
                const stockBadge = document.getElementById('prodStockBadge' + id);
                const sellBtn = document.getElementById('prodSellBtn' + id);
                const card = document.getElementById('prodCard' + id);
                const remaining = currentQsMaxStock - qty;
                if (stockBadge) {
                    if (remaining <= 0) {
                        stockBadge.className = 'badge badge-danger';
                        stockBadge.innerText = 'Out of Stock';
                        if (sellBtn) sellBtn.outerHTML = '<button type="button" class="btn-out-of-stock" disabled>Out of Stock</button>';
                        if (card) {
                            card.classList.add('item-sold');
                        }
                    } else {
                        stockBadge.innerText = 'Stock: ' + remaining + ' units';
                    }
                }
            }

            // Update Counts
            updateStockCounts();

            // 2. Add to Customer Purchases feed on the right panel
            const feed = document.getElementById('customerPurchasesFeed');
            const emptyNotice = document.getElementById('noPurchasesNotice');
            if (emptyNotice) emptyNotice.remove();

            const sale = data.sale;
            salesMap[sale.id] = sale;

            const newCard = document.createElement('div');
            newCard.className = 'purchase-feed-card';
            newCard.setAttribute('data-search', (custName + ' ' + custPhone + ' ' + custNic + ' ' + sale.invoice_number).toLowerCase());
            newCard.style.cssText = 'background: #ffffff; border: 1.5px solid #86efac; border-radius: 12px; padding: 0.95rem 1.1rem; box-shadow: 0 4px 14px rgba(16, 185, 129, 0.12); animation: fadeIn 0.3s ease;';
            newCard.innerHTML = `
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.55rem;">
                    <span style="font-family: 'JetBrains Mono', monospace; font-size: 0.75rem; font-weight: 850; color: #047857; background: #dcfce7; border: 1px solid #86efac; padding: 2px 8px; border-radius: 6px;">
                        #${sale.invoice_number}
                    </span>
                    <span style="font-size: 0.7rem; color: #16a34a; font-weight: 700; display: inline-flex; align-items: center; gap: 0.25rem;">
                        <span class="status-dot-green"></span> Just Now
                    </span>
                </div>
                <div style="margin-bottom: 0.65rem; padding-bottom: 0.65rem; border-bottom: 1px dashed #e2e8f0;">
                    <div style="font-size: 0.85rem; font-weight: 800; color: #0f172a; line-height: 1.3;">
                        ${sale.items && sale.items[0] ? sale.items[0].item_name : 'Sold Item'} (x${qty})
                    </div>
                    <div style="margin-top: 0.45rem; display: flex; align-items: center; justify-content: space-between;">
                        <span class="badge" style="background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; font-size: 0.68rem; font-weight: 750; padding: 2px 7px; border-radius: 6px;">
                            ✓ Stock Deducted (-${qty})
                        </span>
                        <span style="font-size: 0.85rem; font-weight: 850; color: #047857;">
                            LKR ${(currentQsUnitPrice * qty).toLocaleString('en-US', {minimumFractionDigits: 2})}
                        </span>
                    </div>
                </div>
                <div style="background: #f8fafc; border-radius: 10px; padding: 0.6rem 0.75rem; border: 1px solid #e2e8f0;">
                    <div style="font-size: 0.68rem; font-weight: 800; text-transform: uppercase; color: #047857; margin-bottom: 3px; display: inline-flex; align-items: center; gap: 0.25rem;">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                        <span>Buying Customer</span>
                    </div>
                    <div style="font-size: 0.875rem; font-weight: 850; color: #0f172a;">${custName}</div>
                    <div style="font-size: 0.75rem; color: #475569; margin-top: 3px; display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap;">
                        <span style="display: inline-flex; align-items: center; gap: 0.25rem;">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                            <strong>${custPhone}</strong>
                        </span>
                        ${custNic ? `
                            <span style="color: #cbd5e1;">&bull;</span>
                            <span style="display: inline-flex; align-items: center; gap: 0.25rem;">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="3" y="4" width="18" height="16" rx="2"></rect><line x1="7" y1="8" x2="17" y2="8"></line><line x1="7" y1="12" x2="13" y2="12"></line></svg>
                                <span>${custNic}</span>
                            </span>
                        ` : ''}
                    </div>
                </div>
                <div style="margin-top: 0.65rem; display: flex; justify-content: flex-end; align-items: center; gap: 0.4rem;">
                    ${sale.bank_slip_url ? `
                        <a href="${sale.bank_slip_url}" target="_blank" class="btn btn-sm" style="font-size: 0.72rem; padding: 0.25rem 0.55rem; color: #2563eb; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 0.25rem;">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path></svg>
                            <span>Slip</span>
                        </a>
                    ` : ''}
                    <button type="button" onclick="openSaleReceipt(${sale.id})" class="btn btn-sm btn-secondary" style="font-size: 0.75rem; padding: 0.25rem 0.75rem; border-radius: 6px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 0.35rem;">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                        <span>View Receipt</span>
                    </button>
                </div>
            `;
            feed.prepend(newCard);

            const countBadge = document.getElementById('purchasesCountBadge');
            if (countBadge) {
                const currentCount = parseInt(countBadge.innerText) || 0;
                countBadge.innerText = (currentCount + 1) + ' Recorded';
            }

            // 3. Update top banner
            const banner = document.getElementById('latestSaleBanner');
            const bannerText = document.getElementById('latestSaleText');
            if (banner && bannerText) {
                banner.style.display = 'flex';
                bannerText.innerHTML = `<strong>${sale.items && sale.items[0] ? sale.items[0].item_name : 'Item'}</strong> sold to <strong style="background: #dcfce7; padding: 2px 7px; border-radius: 5px; color: #0f172a;">${custName}</strong> (${custPhone}) &bull; Total: <span style="font-weight: 850; color: #047857;">LKR ${(currentQsUnitPrice * qty).toLocaleString('en-US', {minimumFractionDigits: 2})}</span>`;
            }

            // 4. Close Sell Modal
            closeQuickSellModal();

            // 5. Render Invoice Receipt Modal with change calculation
            showReceipt(sale, cashTendered, changeAmount);

        } catch (err) {
            alert(err.message || 'Transaction could not be processed.');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> <span>Complete</span>';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        updateStockCounts();

        const searchInp = document.getElementById('searchInput');
        if (searchInp) {
            searchInp.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const val = this.value.trim().toLowerCase();
                    if (!val) return;

                    const visibleCards = Array.from(document.querySelectorAll('.item-card')).filter(c => c.style.display !== 'none');
                    if (visibleCards.length === 1) {
                        const btn = visibleCards[0].querySelector('.btn-sell-action');
                        if (btn) btn.click();
                    } else if (visibleCards.length > 1) {
                        const exact = visibleCards.find(c => (c.dataset.imei && c.dataset.imei.toLowerCase() === val) || (c.dataset.barcode && c.dataset.barcode.toLowerCase() === val));
                        if (exact) {
                            const btn = exact.querySelector('.btn-sell-action');
                            if (btn) btn.click();
                        }
                    }
                }
            });
        }

        // Drag & drop bank slip upload support
        const dropzone = document.getElementById('qsBankSlipDropzone');
        if (dropzone) {
            ['dragenter', 'dragover'].forEach(eventName => {
                dropzone.addEventListener(eventName, e => {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.style.borderColor = '#2563eb';
                    dropzone.style.background = '#eff6ff';
                }, false);
            });
            ['dragleave', 'drop'].forEach(eventName => {
                dropzone.addEventListener(eventName, e => {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.style.borderColor = '#94a3b8';
                    dropzone.style.background = '#ffffff';
                }, false);
            });
            dropzone.addEventListener('drop', e => {
                const dt = e.dataTransfer;
                const files = dt.files;
                if (files && files.length > 0) {
                    const input = document.getElementById('qsBankSlipInput');
                    input.files = files;
                    handleQsBankSlipSelected(input);
                }
            }, false);
        }
    });

    function showReceipt(sale, cashTendered = 0, changeAmount = 0) {
        const rc = document.getElementById('receiptContent');
        const items = sale.items || [];
        const payments = sale.payments || [];

        const itemsHtml = items.map(item => `
            <div style="display:flex; justify-content:space-between; margin-bottom: 0.35rem; font-size: 0.8125rem;">
                <div style="padding-right: 0.5rem;">
                    <strong>${item.item_name || (item.product ? item.product.name : 'Item')}</strong> x${item.quantity}
                    ${item.phone_device && item.phone_device.imei_1 ? `<div style="font-size: 0.7rem; color: #0284c7; font-family: 'JetBrains Mono', monospace;">IMEI: ${item.phone_device.imei_1}</div>` : ''}
                    ${item.phoneDevice && item.phoneDevice.imei_1 ? `<div style="font-size: 0.7rem; color: #0284c7; font-family: 'JetBrains Mono', monospace;">IMEI: ${item.phoneDevice.imei_1}</div>` : ''}
                </div>
                <div style="font-weight: 700;">LKR ${parseFloat(item.subtotal || (item.quantity * item.unit_price)).toFixed(2)}</div>
            </div>
        `).join('');

        const paymentsHtml = payments.map(p => `
            <div style="display:flex; justify-content:space-between; align-items:center; font-size: 0.75rem; color: #475569; margin-bottom: 0.2rem;">
                <span style="display:inline-flex; align-items:center; gap:0.35rem;">
                    <span>${p.payment_method.toUpperCase().replace('_', ' ')}:</span>
                    ${p.reference ? `<span style="font-size:0.68rem; background:#f1f5f9; padding:1px 5px; border-radius:4px; font-family:'JetBrains Mono',monospace;">Ref: ${p.reference}</span>` : ''}
                </span>
                <span>LKR ${parseFloat(p.amount).toFixed(2)}</span>
            </div>
            ${p.slip_url || sale.bank_slip_url ? `
                <div style="margin: 0.25rem 0 0.45rem; text-align: left;">
                    <a href="${p.slip_url || sale.bank_slip_url}" target="_blank" style="font-size: 0.7rem; color: #2563eb; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; padding: 2px 7px; text-decoration: none; font-weight: 700; display: inline-flex; align-items: center; gap: 0.25rem;">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path></svg>
                        <span>View Attached ${p.payment_method === 'card' ? 'Card Slip' : 'Bank Slip'}</span>
                    </a>
                </div>
            ` : ''}
        `).join('');

        rc.innerHTML = `
            <div id="posReceiptPrintArea">
                <div style="text-align: center; margin-bottom: 0.75rem; border-bottom: 1px dashed #cbd5e1; padding-bottom: 0.75rem;">
                    <h4 style="margin: 0; font-size: 1.15rem; font-weight: 800;">{{ auth()->user()->shop->name ?? 'Phone Shop' }}</h4>
                    <div style="font-size: 0.75rem; color: #64748b;">Official POS Tax Invoice &amp; Receipt</div>
                    <div style="font-size: 0.8125rem; font-weight: 700; color: #4f46e5; margin-top: 0.35rem; font-family: 'JetBrains Mono', monospace;">Invoice: #${sale.invoice_number}</div>
                    <div style="font-size: 0.75rem; color: #64748b;">Date: ${sale.sale_date || 'Today'} | Cashier: ${sale.cashier ? sale.cashier.name : '{{ auth()->user()->name }}'}</div>
                    <div style="background: #f1f5f9; border-radius: 6px; padding: 0.45rem 0.65rem; margin-top: 0.45rem; font-size: 0.775rem; text-align: left; display: flex; flex-direction: column; gap: 0.2rem;">
                        <div style="font-weight: 750; color: #0f172a; display: inline-flex; align-items: center; gap: 0.35rem;">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                            <span>Customer: ${sale.customer ? sale.customer.name : 'Walk-in Customer'}</span>
                        </div>
                        ${sale.customer && sale.customer.phone ? `
                            <div style="font-size: 0.72rem; color: #475569; display: inline-flex; align-items: center; gap: 0.35rem;">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                                <span>Phone: ${sale.customer.phone}</span>
                            </div>` : ''}
                        ${sale.customer && sale.customer.nic ? `
                            <div style="font-size: 0.7rem; color: #64748b; display: inline-flex; align-items: center; gap: 0.35rem;">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="3" y="4" width="18" height="16" rx="2"></rect><line x1="7" y1="8" x2="17" y2="8"></line><line x1="7" y1="12" x2="13" y2="12"></line></svg>
                                <span>NIC / ID: ${sale.customer.nic}</span>
                            </div>` : ''}
                        ${sale.customer && sale.customer.address ? `
                            <div style="font-size: 0.7rem; color: #64748b; display: inline-flex; align-items: center; gap: 0.35rem;">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                <span>Address: ${sale.customer.address}</span>
                            </div>` : ''}
                    </div>
                </div>
                <div style="margin-bottom: 0.75rem;">
                    ${itemsHtml}
                </div>
                <div style="border-top: 1px dashed #cbd5e1; padding-top: 0.5rem; margin-bottom: 0.75rem;">
                    <div style="display:flex; justify-content:space-between; font-size: 0.8125rem;">
                        <span style="color: #64748b;">Subtotal:</span>
                        <span>LKR ${parseFloat(sale.subtotal || sale.total_amount).toFixed(2)}</span>
                    </div>
                    ${sale.discount_amount > 0 ? `
                        <div style="display:flex; justify-content:space-between; font-size: 0.8125rem; color: #b91c1c;">
                            <span>Discount:</span>
                            <span>-LKR ${parseFloat(sale.discount_amount).toFixed(2)}</span>
                        </div>
                    ` : ''}
                    <div style="display:flex; justify-content:space-between; font-weight:800; font-size: 1.1rem; color: #0f172a; border-top: 1px solid #e2e8f0; padding-top: 0.35rem; margin-top: 0.35rem;">
                        <span>Grand Total:</span>
                        <span style="color: #047857;">LKR ${parseFloat(sale.total_amount).toFixed(2)}</span>
                    </div>
                </div>
                <div style="background: #f8fafc; border-radius: 6px; padding: 0.5rem 0.75rem; margin-bottom: 0.75rem;">
                    ${paymentsHtml}
                    ${changeAmount > 0 ? `
                        <div style="display:flex; justify-content:space-between; font-size: 0.8125rem; font-weight: 800; color: #047857; border-top: 1px dashed #cbd5e1; padding-top: 0.35rem; margin-top: 0.35rem;">
                            <span>Change Returned:</span>
                            <span>LKR ${parseFloat(changeAmount).toFixed(2)}</span>
                        </div>
                    ` : ''}
                </div>
                <div style="font-size: 0.72rem; color: #64748b; text-align: center; margin-top: 0.75rem;">
                    Thank you for your purchase!<br>
                    Official Warranty applies per serial/IMEI record.
                </div>
            </div>
        `;
        document.getElementById('receiptFullViewBtn').href = `/shop/sales/${sale.id}`;
        openModal('receiptModal');
    }

    function printPosReceipt() {
        const printContent = document.getElementById('posReceiptPrintArea');
        if (!printContent) return;

        const printWindow = window.open('', '', 'width=450,height=600');
        printWindow.document.write(`
            <html>
                <head>
                    <title>Receipt</title>
                    <style>
                        body { font-family: 'JetBrains Mono', monospace, Courier; margin: 15px; font-size: 12px; }
                        @media print { body { margin: 0; } }
                    </style>
                </head>
                <body>
                    ${printContent.innerHTML}
                </body>
            </html>
        `);
        printWindow.document.close();
        printWindow.focus();
        setTimeout(() => {
            printWindow.print();
            printWindow.close();
        }, 300);
    }
</script>
@endpush
@endsection
