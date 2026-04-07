{{-- Shared payment summary + cash/bank panel (Create Purchase & Create Sale). Wrap markup in .payment-panel-totals --}}
    .payment-panel-totals .total-section {
        padding-top: 16px;
        border-top: 2px dashed #e5e7eb;
        background: #f9fafb;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        padding: 16px;
    }
    .payment-panel-totals .total-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 4px 0;
        font-size: 11px;
        font-weight: 700;
        color: #6b7280;
    }
    .payment-panel-totals .discount-section {
        background: #dcfce7;
        border-radius: 12px;
        border: 1px solid #bbf7d0;
        padding: 8px 16px;
        margin: 8px 0;
    }
    .payment-panel-totals .discount-label {
        font-size: 10px;
        font-weight: 900;
        color: #16a34a;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .payment-panel-totals .rent-paid-section {
        background: #fff7ed;
        border-radius: 12px;
        border: 1px solid #fed7aa;
        padding: 8px 16px;
        margin: 8px 0;
    }
    .payment-panel-totals .rent-paid-label {
        font-size: 10px;
        font-weight: 900;
        color: #c2410c;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .payment-panel-totals .net-payable {
        background: #eff6ff;
        border-radius: 12px;
        border: 1px solid #bfdbfe;
        padding: 6px 16px;
        margin: 8px 0;
    }
    .payment-panel-totals .net-payable-label {
        font-size: 10px;
        font-weight: 900;
        color: #1e40af;
        text-transform: uppercase;
    }
    .payment-panel-totals .net-payable-value {
        font-size: 14px;
        font-weight: 900;
        color: #1e40af;
    }
    .payment-panel-totals .received-amount-section {
        background: #f9fafb;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        padding: 8px 16px;
        margin: 8px 0;
    }
    .payment-panel-totals .received-amount-label {
        font-size: 10px;
        font-weight: 900;
        color: #374151;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .payment-panel-totals .payment-card.border-blue-100 {
        background-color: #f8fafc;
        border: 1px solid #bfdbfe;
        border-radius: 12px;
        padding: 10px;
        margin-top: 5px;
    }
    #purchaseCashPaidWrapper .purchase-cash-amount-wrap,
    #salesCashPaidWrapper .purchase-cash-amount-wrap {
        flex: 0 0 auto;
        width: 142px;
        max-width: 142px;
        min-width: 118px;
        padding: 0.25rem 0.5rem 0.25rem 0.75rem;
        border-radius: 0.5rem !important;
        border: 1px solid #e5e7eb !important;
        background: #fff;
        align-items: stretch;
    }
    #purchaseCashPaidWrapper .purchase-cash-prefix,
    #salesCashPaidWrapper .purchase-cash-prefix {
        display: inline-flex;
        align-items: center;
        font-size: 0.95rem;
        font-weight: 700;
        color: #475569;
        letter-spacing: 0.04em;
        margin-right: 0.5rem;
        flex-shrink: 0;
        line-height: 1;
    }
    #purchaseCashPaidWrapper .purchase-cash-input,
    #salesCashPaidWrapper .purchase-cash-input {
        font-size: 1.0625rem;
        font-weight: 700;
        padding: 0.5rem 0.75rem !important;
        min-height: 44px;
        line-height: 1.25;
        text-align: right;
        color: #0f172a;
    }
    #purchaseCashPaidWrapper .purchase-cash-input:focus,
    #salesCashPaidWrapper .purchase-cash-input:focus {
        box-shadow: none;
        outline: none;
    }
    #purchaseCashPaidWrapper .purchase-cash-amount-wrap:focus-within,
    #salesCashPaidWrapper .purchase-cash-amount-wrap:focus-within {
        border-color: #93c5fd !important;
        box-shadow: 0 0 0 0.15rem rgba(13, 110, 253, 0.15);
    }
    @media (max-width: 576px) {
        #purchaseCashPaidWrapper .purchase-cash-amount-wrap,
        #salesCashPaidWrapper .purchase-cash-amount-wrap {
            width: min(100%, 142px);
            max-width: 142px;
        }
    }
    #purchaseBankPaidWrapper .purchase-bank-amount-wrap,
    #salesBankPaidWrapper .purchase-bank-amount-wrap {
        flex: 0 0 auto;
        width: 142px;
        max-width: 142px;
        min-width: 118px;
        padding: 0.25rem 0.5rem 0.25rem 0.75rem;
        border-radius: 0.5rem !important;
        border: 1px solid #e5e7eb !important;
        background: #fff;
        align-items: stretch;
    }
    #purchaseBankPaidWrapper .purchase-bank-amount-prefix,
    #salesBankPaidWrapper .purchase-bank-amount-prefix {
        display: inline-flex;
        align-items: center;
        font-size: 0.95rem;
        font-weight: 700;
        color: #475569;
        letter-spacing: 0.04em;
        margin-right: 0.5rem;
        flex-shrink: 0;
        line-height: 1;
    }
    #purchaseBankPaidWrapper .purchase-bank-amt,
    #salesBankPaidWrapper .purchase-bank-amt {
        font-size: 1.0625rem;
        font-weight: 700;
        padding: 0.5rem 0.75rem !important;
        min-height: 44px;
        line-height: 1.25;
        text-align: right;
        color: #0f172a;
    }
    #purchaseBankPaidWrapper .purchase-bank-amt:focus,
    #salesBankPaidWrapper .purchase-bank-amt:focus {
        box-shadow: none;
        outline: none;
    }
    #purchaseBankPaidWrapper .purchase-bank-amount-wrap:focus-within,
    #salesBankPaidWrapper .purchase-bank-amount-wrap:focus-within {
        border-color: #93c5fd !important;
        box-shadow: 0 0 0 0.15rem rgba(13, 110, 253, 0.15);
    }
    @media (max-width: 576px) {
        #purchaseBankPaidWrapper .purchase-bank-amount-wrap,
        #salesBankPaidWrapper .purchase-bank-amount-wrap {
            width: min(100%, 142px);
            max-width: 142px;
        }
    }
    .payment-panel-totals .purchase-payment-amount-rail {
        align-items: center;
    }
    #purchaseBankPaidWrapper .purchase-bank-left-stack,
    #salesBankPaidWrapper .purchase-bank-left-stack {
        display: flex;
        flex-direction: column;
        align-items: stretch;
        gap: 0.45rem;
        max-width: 100%;
        min-width: 0;
    }
    @media (min-width: 576px) {
        #purchaseBankPaidWrapper .purchase-bank-left-stack,
        #salesBankPaidWrapper .purchase-bank-left-stack {
            max-width: min(100%, 420px);
        }
    }
    #purchaseBankPaidWrapper .purchase-bank-select-wrap .purchase-bank-account,
    #salesBankPaidWrapper .purchase-bank-select-wrap .purchase-bank-account {
        font-size: 0.8125rem;
        font-weight: 500;
        padding: 0.35rem 0.65rem;
        min-height: 36px;
        line-height: 1.25;
        border-radius: 0.5rem;
        width: 100%;
    }
    #purchaseBankPaidWrapper .purchase-bank-select-wrap,
    #salesBankPaidWrapper .purchase-bank-select-wrap {
        margin-bottom: 0;
    }
    #purchaseBankPaidWrapper .purchase-bank-trans-wrap .purchase-bank-ref,
    #salesBankPaidWrapper .purchase-bank-trans-wrap .purchase-bank-ref {
        font-size: 0.8125rem;
        font-weight: 500;
        padding: 0.35rem 0.65rem;
        min-height: 36px;
        line-height: 1.25;
        border-radius: 0.5rem;
        width: 100%;
        box-sizing: border-box;
    }
    #purchaseBankPaidWrapper .purchase-bank-trans-wrap .purchase-bank-trans-label,
    #salesBankPaidWrapper .purchase-bank-trans-wrap .purchase-bank-trans-label {
        margin-bottom: 0.2rem;
    }
    #purchaseBankPaidWrapper .purchase-bank-trans-wrap .purchase-bank-trans-hint,
    #salesBankPaidWrapper .purchase-bank-trans-wrap .purchase-bank-trans-hint {
        display: block;
        margin-top: 0.15rem;
        line-height: 1.3;
    }
    #purchaseBankPaidWrapper .purchase-bank-receipt-wrap .purchase-bank-receipt-label,
    #salesBankPaidWrapper .purchase-bank-receipt-wrap .purchase-bank-receipt-label {
        padding: 0.4rem 0.65rem !important;
        margin-bottom: 0;
    }
    #purchaseBankPaidWrapper .purchase-bank-receipt-wrap .purchase-attach-preview,
    #salesBankPaidWrapper .purchase-bank-receipt-wrap .purchase-attach-preview {
        margin-top: 0.35rem !important;
    }
    .payment-panel-totals .purchase-bank-row {
        background-color: #f8fafc;
        border: 1px solid #e9d5ff;
        border-radius: 12px;
        padding: 10px;
        margin-top: 5px;
    }
    .payment-panel-totals .sales-previous-balance-section {
        background: #fffbeb;
        border-radius: 12px;
        border: 1px solid #fde68a;
        padding: 8px 16px;
        margin: 8px 0;
    }
    .payment-panel-totals .sales-previous-balance-label {
        font-size: 10px;
        font-weight: 900;
        color: #92400e;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .payment-panel-totals .sales-previous-balance-section .sales-previous-balance-detail {
        font-size: 11px;
        line-height: 1.35;
    }
    .payment-panel-totals .sales-previous-balance-section.is-receivable {
        background: #fef2f2;
        border-color: #fecaca !important;
    }
    .payment-panel-totals .sales-previous-balance-section.is-receivable .sales-previous-balance-label {
        color: #b91c1c;
    }
    .payment-panel-totals .sales-previous-balance-section.is-receivable .sales-previous-balance-amount {
        color: #dc2626;
    }
    .payment-panel-totals .sales-previous-balance-section.is-advance,
    .payment-panel-totals .sales-previous-balance-section.is-payable {
        background: #ecfdf5;
        border-color: #a7f3d0 !important;
    }
    .payment-panel-totals .sales-previous-balance-section.is-advance .sales-previous-balance-label,
    .payment-panel-totals .sales-previous-balance-section.is-payable .sales-previous-balance-label {
        color: #047857;
    }
    .payment-panel-totals .sales-previous-balance-section.is-advance .sales-previous-balance-amount,
    .payment-panel-totals .sales-previous-balance-section.is-payable .sales-previous-balance-amount {
        color: #059669;
    }
    .payment-panel-totals .sales-previous-balance-section.is-zero {
        background: #f9fafb;
        border-color: #e5e7eb !important;
    }
    .payment-panel-totals .sales-previous-balance-section.is-zero .sales-previous-balance-label {
        color: #6b7280;
    }
    .payment-panel-totals .sales-previous-balance-section.is-zero .sales-previous-balance-amount {
        color: #6b7280;
    }
