<?php //Bootstrap Links for CSS and JS ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

<!-- Font Awesome -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<!-- Google Fonts: Inter -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<?php //Datatables Links for CSS and JS ?>
<!-- jQuery CDN -->
<script src="https://code.jquery.com/jquery-3.7.1.js"></script>

<!-- DataTables Core -->
<link rel="stylesheet" href="https://cdn.datatables.net/2.3.7/css/dataTables.dataTables.css">
<script src="https://cdn.datatables.net/2.3.7/js/dataTables.js"></script>

<!-- DataTables Extensions (Responsive & Buttons) -->
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

<link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.2.6/css/buttons.dataTables.css">
<script src="https://cdn.datatables.net/buttons/3.2.6/js/dataTables.buttons.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/3.2.6/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/3.2.6/js/buttons.print.min.js"></script>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Custom JS -->
<script src="../js/usersmanagement.js"></script>

<!-- JS PDF -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<!-- ═══ GLOBAL PHARMACY THEME ═══ -->
<style>
    :root {
        --pharm-red: #c0392b;
        --pharm-red-dark: #a93226;
        --pharm-red-lt: #fff5f5;
        --pharm-text: #1a2535;
        --pharm-muted: #94a3b8;
        --pharm-border: #e5e7eb;
        --pharm-bg: #f4f6f9;
    }

    /* ── Typography ── */
    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        color: var(--pharm-text);
        background: var(--pharm-bg);
    }

    /* ══ BUTTONS ══ */
    .btn {
        border-radius: 8px !important;
        font-weight: 600 !important;
        transition: all .18s !important;
    }

    .btn-sm {
        font-size: .78rem !important;
        padding: 5px 12px !important;
    }

    .btn-success,
    .btn-primary {
        background: linear-gradient(135deg, var(--pharm-red), #e74c3c) !important;
        border-color: var(--pharm-red) !important;
        color: #fff !important;
        box-shadow: 0 2px 8px rgba(192, 57, 43, .3) !important;
    }

    .btn-success:hover,
    .btn-primary:hover,
    .btn-success:focus,
    .btn-primary:focus {
        background: linear-gradient(135deg, var(--pharm-red-dark), var(--pharm-red)) !important;
        border-color: var(--pharm-red-dark) !important;
        box-shadow: 0 4px 14px rgba(192, 57, 43, .4) !important;
        transform: translateY(-1px);
    }

    .btn-success:active,
    .btn-primary:active {
        transform: translateY(0) !important;
    }

    .btn-outline-success,
    .btn-outline-primary {
        color: var(--pharm-red) !important;
        border-color: var(--pharm-red) !important;
        background: transparent !important;
        box-shadow: none !important;
    }

    .btn-outline-success:hover,
    .btn-outline-primary:hover {
        background: var(--pharm-red) !important;
        color: #fff !important;
        box-shadow: 0 3px 10px rgba(192, 57, 43, .3) !important;
    }

    .btn-info {
        background: transparent !important;
        border: 1.5px solid var(--pharm-text) !important;
        color: var(--pharm-text) !important;
        box-shadow: none !important;
    }

    .btn-info:hover {
        background: var(--pharm-text) !important;
        color: #fff !important;
        box-shadow: 0 3px 10px rgba(26, 37, 53, .25) !important;
        transform: translateY(-1px);
    }

    .btn-warning {
        background: linear-gradient(135deg, #d97706, #f59e0b) !important;
        border-color: #d97706 !important;
        color: #fff !important;
        box-shadow: 0 2px 8px rgba(217, 119, 6, .3) !important;
    }

    .btn-warning:hover {
        background: linear-gradient(135deg, #b45309, #d97706) !important;
        box-shadow: 0 4px 14px rgba(217, 119, 6, .4) !important;
        transform: translateY(-1px);
        color: #fff !important;
    }

    .btn-danger {
        background: linear-gradient(135deg, #dc2626, #ef4444) !important;
        border-color: #dc2626 !important;
        box-shadow: 0 2px 8px rgba(220, 38, 38, .3) !important;
    }

    .btn-danger:hover {
        background: linear-gradient(135deg, #b91c1c, #dc2626) !important;
        box-shadow: 0 4px 14px rgba(220, 38, 38, .4) !important;
        transform: translateY(-1px);
    }

    /* ══ SEMANTIC COLORS — PRESERVED ══
       .bg-success stays GREEN for "In Stock" badges
       .text-success stays GREEN for stock quantities
       .bg-warning stays AMBER for "Low Stock"            */
    .bg-primary {
        background-color: var(--pharm-red) !important;
    }

    .badge {
        border-radius: 50px !important;
        font-weight: 700 !important;
        font-size: .72rem !important;
        padding: 4px 10px !important;
    }

    /* ── Navbar ── */
    .navbar-brand {
        color: var(--pharm-red) !important;
        font-weight: 800;
        font-size: 1rem;
        letter-spacing: -.3px;
    }

    /* ── Sidebar ── */
    .nav-pills .nav-link.active,
    .nav-pills .show>.nav-link {
        background: linear-gradient(135deg, var(--pharm-red), #e74c3c) !important;
        box-shadow: 0 4px 10px rgba(192, 57, 43, .3) !important;
    }

    .nav-link:hover {
        color: var(--pharm-red) !important;
    }

    /* ── Forms ── */
    .form-control,
    .form-select {
        border-radius: 8px !important;
        border: 1.5px solid var(--pharm-border) !important;
        font-size: .88rem !important;
        transition: border-color .15s, box-shadow .15s !important;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: var(--pharm-red) !important;
        box-shadow: 0 0 0 3px rgba(192, 57, 43, .12) !important;
    }

    .form-check-input:checked {
        background-color: var(--pharm-red) !important;
        border-color: var(--pharm-red) !important;
    }

    label,
    .form-label {
        font-size: .8rem !important;
        font-weight: 600 !important;
        color: #374151 !important;
    }

    /* ── Cards ── */
    .card {
        border-radius: 14px !important;
        border: 1px solid rgba(0, 0, 0, .05) !important;
        box-shadow: 0 2px 12px rgba(0, 0, 0, .06) !important;
    }

    /* ── Bootstrap Tables ── */
    .table-dark th,
    thead.table-dark th {
        background: #1a2535 !important;
        color: #fff !important;
        font-size: .72rem !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: .6px !important;
        padding: 13px 16px !important;
        border-bottom: none !important;
    }

    .table>tbody>tr {
        transition: background .12s;
    }

    .table-hover>tbody>tr:hover>td {
        background: var(--pharm-red-lt) !important;
    }

    .table>tbody>tr>td {
        vertical-align: middle !important;
    }

    .table-striped>tbody>tr:nth-of-type(even)>td {
        background: #fafbfc;
    }

    /* ── DataTables ── */
    .dataTables_wrapper {
        font-family: 'Inter', sans-serif;
        font-size: .84rem;
    }

    .dataTables_filter input {
        border: 1.5px solid var(--pharm-border) !important;
        border-radius: 8px !important;
        padding: 6px 12px !important;
        font-size: .83rem !important;
        outline: none;
        transition: .15s;
    }

    .dataTables_filter input:focus {
        border-color: var(--pharm-red) !important;
        box-shadow: 0 0 0 3px rgba(192, 57, 43, .12) !important;
    }

    .dataTables_length select {
        border: 1.5px solid var(--pharm-border) !important;
        border-radius: 8px !important;
        padding: 5px 10px !important;
        font-size: .83rem !important;
    }

    table.dataTable {
        border-collapse: collapse !important;
        width: 100% !important;
    }

    table.dataTable thead th {
        background: #1a2535 !important;
        color: #fff !important;
        font-size: .72rem !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: .6px !important;
        padding: 13px 16px !important;
        border-bottom: none !important;
        border-right: 1px solid rgba(255, 255, 255, .08) !important;
        white-space: nowrap;
    }

    table.dataTable thead th:last-child {
        border-right: none !important;
    }

    table.dataTable thead th.sorting::after,
    table.dataTable thead th.sorting_asc::after,
    table.dataTable thead th.sorting_desc::after {
        color: rgba(255, 255, 255, .5) !important;
    }

    table.dataTable tbody tr {
        transition: background .12s;
    }

    table.dataTable tbody tr:hover td {
        background: var(--pharm-red-lt) !important;
    }

    table.dataTable tbody td {
        padding: 12px 16px !important;
        border-bottom: 1px solid #f1f5f9 !important;
        color: #374151 !important;
        font-size: .84rem !important;
        vertical-align: middle !important;
    }

    table.dataTable tbody tr:last-child td {
        border-bottom: none !important;
    }

    table.dataTable tbody tr:nth-child(even) td {
        background: #fafbfc;
    }

    /* Pagination */
    .dataTables_paginate .paginate_button {
        border-radius: 8px !important;
        margin: 0 2px !important;
        padding: 5px 11px !important;
        font-size: .8rem !important;
        font-weight: 600 !important;
        border: 1.5px solid var(--pharm-border) !important;
        color: #374151 !important;
        transition: .15s;
    }

    .dataTables_paginate .paginate_button.current,
    .dataTables_paginate .paginate_button.current:hover {
        background: linear-gradient(135deg, var(--pharm-red), #e74c3c) !important;
        border-color: var(--pharm-red) !important;
        color: #fff !important;
        box-shadow: 0 2px 8px rgba(192, 57, 43, .3) !important;
    }

    .dataTables_paginate .paginate_button:hover {
        background: var(--pharm-red-lt) !important;
        border-color: var(--pharm-red) !important;
        color: var(--pharm-red) !important;
    }

    /* Export Buttons */
    .dt-button {
        background: #1a2535 !important;
        color: #fff !important;
        border: none !important;
        border-radius: 8px !important;
        padding: 7px 14px !important;
        font-size: .78rem !important;
        font-weight: 600 !important;
        cursor: pointer !important;
        transition: all .15s !important;
        margin-right: 4px !important;
        box-shadow: none !important;
    }

    .dt-button:hover {
        background: var(--pharm-red) !important;
        box-shadow: 0 3px 10px rgba(192, 57, 43, .35) !important;
        transform: translateY(-1px) !important;
    }

    .dt-buttons {
        margin-bottom: 10px;
    }

    .dataTables_info {
        font-size: .78rem !important;
        color: var(--pharm-muted) !important;
    }

    /* ── Modals ── */
    .modal-content {
        border-radius: 16px !important;
        border: none !important;
        box-shadow: 0 20px 60px rgba(0, 0, 0, .15) !important;
    }

    .modal-header {
        border-bottom: 1px solid #f1f5f9 !important;
        padding: 18px 22px !important;
    }

    .modal-title {
        font-weight: 700 !important;
        font-size: .95rem !important;
    }

    .modal-body {
        padding: 22px !important;
    }

    .modal-footer {
        border-top: 1px solid #f1f5f9 !important;
        padding: 14px 22px !important;
    }

    /* ── Dropdown ── */
    .dropdown-item:hover {
        background: var(--pharm-red-lt) !important;
    }

    .dropdown-menu {
        border-radius: 12px !important;
        border: 1px solid rgba(0, 0, 0, .06) !important;
        box-shadow: 0 10px 30px rgba(0, 0, 0, .12) !important;
    }

    /* ── Table Action Buttons (compact inline) ── */
    .action-btns {
        display: flex;
        gap: 5px;
        align-items: center;
        flex-wrap: nowrap;
    }

    .btn-action-edit {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 11px;
        font-size: .73rem;
        font-weight: 600;
        border-radius: 6px;
        border: none;
        background: #1a2535;
        color: #fff;
        cursor: pointer;
        transition: all .15s;
        text-decoration: none;
        white-space: nowrap;
        font-family: 'Inter', sans-serif;
    }

    .btn-action-edit:hover {
        background: #2d3f57;
        transform: translateY(-1px);
        box-shadow: 0 3px 8px rgba(26, 37, 53, .25);
        color: #fff;
    }

    .btn-action-delete {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 11px;
        font-size: .73rem;
        font-weight: 600;
        border-radius: 6px;
        border: none;
        background: linear-gradient(135deg, #dc2626, #ef4444);
        color: #fff;
        cursor: pointer;
        transition: all .15s;
        text-decoration: none;
        white-space: nowrap;
        font-family: 'Inter', sans-serif;
    }

    .btn-action-delete:hover {
        background: linear-gradient(135deg, #b91c1c, #dc2626);
        transform: translateY(-1px);
        box-shadow: 0 3px 8px rgba(220, 38, 38, .3);
        color: #fff;
    }

    /* ── Floating Alert Panels ── */
    #lowStockAlert, #expiryAlert {
        position:fixed !important; right:20px !important; width:300px !important; max-height:380px !important;
        display:flex !important; flex-direction:column !important; background:#fff !important;
        border-radius:14px !important; z-index:9999 !important;
        box-shadow:0 8px 32px rgba(0,0,0,.14), 0 1px 4px rgba(0,0,0,.06) !important;
        border:1px solid rgba(0,0,0,.06) !important; overflow:hidden !important;
        animation:slideInRight .3s cubic-bezier(.22,.68,0,1.2) forwards !important;
    }
    #lowStockAlert { top:80px !important; border-top:3px solid #1a2535 !important; }
    #expiryAlert   { top:260px !important; border-top:3px solid #c0392b !important; }
    .alert-header { padding:12px 16px !important; display:flex !important; align-items:center !important; justify-content:space-between !important; }
    #lowStockAlert .alert-header { background:#1a2535 !important; }
    #expiryAlert .alert-header   { background:linear-gradient(135deg,#c0392b,#e74c3c) !important; }
    .alert-header h5 { margin:0 !important; font-size:.85rem !important; font-weight:700 !important; color:#fff !important; display:flex !important; align-items:center !important; gap:8px !important; }
    .alert-header .btn-close { filter:invert(1) !important; opacity:.7 !important; width:.7rem !important; height:.7rem !important; }
    .alert-items-container { overflow-y:auto !important; max-height:280px !important; padding:10px 16px !important; }
    .alert-item { font-size:.82rem !important; color:#374151 !important; display:flex !important; justify-content:space-between !important; align-items:center !important; padding:9px 0 !important; border-bottom:1px solid #f1f5f9 !important; gap:8px !important; }
    .alert-badge-stock, .alert-badge-near { background:#1a2535 !important; color:#fff !important; font-size:.68rem !important; font-weight:700 !important; padding:3px 10px !important; border-radius:50px !important; }
    .alert-badge-expired { background:linear-gradient(135deg,#c0392b,#e74c3c) !important; color:#fff !important; font-size:.68rem !important; font-weight:700 !important; padding:3px 10px !important; border-radius:50px !important; }

    @keyframes slideInRight {
        from { opacity:0; transform:translateX(110%); }
        to   { opacity:1; transform:translateX(0); }
    }
</style>