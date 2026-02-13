{{-- Vyapar-style design for Banks & Bank Accounts --}}
@push('styles')
<style>
/* Vyapar-style: Cash & Bank - Clean accounting software look */
.vyapar-bank-page { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; }
.vyapar-bank-page .page-header {
    background: linear-gradient(135deg, #0f766e 0%, #0d9488 50%, #14b8a6 100%);
    color: #fff;
    padding: 1.25rem 1.5rem;
    border-radius: 12px;
    margin-bottom: 1.5rem;
    box-shadow: 0 4px 14px rgba(13, 148, 136, 0.25);
}
.vyapar-bank-page .page-header h2 {
    font-weight: 700;
    font-size: 1.5rem;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.vyapar-bank-page .page-header .btn-primary {
    background: #fff;
    color: #0d9488;
    border: none;
    font-weight: 600;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.vyapar-bank-page .page-header .btn-primary:hover {
    background: #f0fdfa;
    color: #0f766e;
}
.vyapar-bank-page .vyapar-card {
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    overflow: hidden;
}
.vyapar-bank-page .vyapar-card .card-header {
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    padding: 1rem 1.25rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 0.75rem;
}
.vyapar-bank-page .vyapar-card .card-header .form-control {
    max-width: 280px;
    border-radius: 8px;
    border: 1px solid #cbd5e1;
    padding: 0.5rem 1rem;
}
.vyapar-bank-page .vyapar-table {
    margin: 0;
}
.vyapar-bank-page .vyapar-table thead th {
    background: #0d9488;
    color: #fff;
    font-weight: 600;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 0.875rem 1rem;
    border: none;
    white-space: nowrap;
}
.vyapar-bank-page .vyapar-table tbody tr {
    transition: background 0.15s ease;
}
.vyapar-bank-page .vyapar-table tbody tr:hover {
    background: #f0fdfa;
}
.vyapar-bank-page .vyapar-table tbody td {
    padding: 0.875rem 1rem;
    vertical-align: middle;
    border-color: #f1f5f9;
}
.vyapar-bank-page .vyapar-table .badge { font-size: 0.7rem; padding: 0.35em 0.6em; }
.vyapar-bank-page .vyapar-table .btn-sm {
    padding: 0.35rem 0.5rem;
    border-radius: 6px;
}
.vyapar-bank-page .vyapar-form-card {
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    overflow: hidden;
}
.vyapar-bank-page .vyapar-form-card .card-header {
    background: linear-gradient(135deg, #0f766e 0%, #0d9488 100%);
    color: #fff;
    padding: 1rem 1.5rem;
    font-weight: 600;
    font-size: 1.1rem;
}
.vyapar-bank-page .vyapar-form-card .form-control, .vyapar-bank-page .vyapar-form-card .form-select {
    border-radius: 8px;
    border: 1px solid #cbd5e1;
}
.vyapar-bank-page .vyapar-form-card .btn-primary {
    background: #0d9488;
    border-color: #0d9488;
    border-radius: 8px;
}
.vyapar-bank-page .vyapar-form-card .btn-primary:hover {
    background: #0f766e;
    border-color: #0f766e;
}
.vyapar-bank-page .empty-state {
    padding: 3rem 2rem !important;
    text-align: center;
    color: #64748b;
}
.vyapar-bank-page .empty-state a { color: #0d9488; font-weight: 600; }
/* Banks tabs */
.vyapar-bank-page .nav-tabs { border-bottom: 2px solid #e2e8f0; }
.vyapar-bank-page .nav-tabs .nav-link {
    border: none; color: #64748b; font-weight: 600;
    padding: 0.75rem 1.25rem; margin-bottom: -2px;
}
.vyapar-bank-page .nav-tabs .nav-link:hover { color: #0d9488; }
.vyapar-bank-page .nav-tabs .nav-link.active {
    color: #0d9488; border-bottom: 2px solid #0d9488;
    background: transparent;
}
</style>
@endpush
