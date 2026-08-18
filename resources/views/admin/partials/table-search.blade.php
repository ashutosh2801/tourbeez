<style>
    .admin-table-search{margin:0 0 18px;padding:20px;border:1px solid #e5e7eb;border-radius:8px;background:linear-gradient(180deg,#f8fafc 0%,#fff 100%)}
    .admin-table-search h5{margin:0 0 3px;color:#172033;font-size:17px;font-weight:700}.admin-table-search p{margin:0 0 14px;color:#6b7280;font-size:13px}
    .admin-table-search label{display:block;margin:0 0 6px;color:#374151;font-size:12px;font-weight:600}.admin-table-search-inline{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:10px}
    .admin-table-search-wrap{position:relative}.admin-table-search-wrap>i{position:absolute;top:50%;left:13px;color:#9ca3af;transform:translateY(-50%)}
    .admin-table-search-wrap .form-control{height:40px;padding-left:37px;border-color:#d7dce3;border-radius:7px;font-size:13px}.admin-table-search-submit{min-width:120px;height:40px;display:inline-flex;align-items:center;justify-content:center;gap:7px;border:1px solid #4f46e5;border-radius:7px;background:#4f46e5;color:#fff;font-size:13px;font-weight:600}
    .admin-table-action{width:34px;height:34px;display:inline-flex!important;align-items:center;justify-content:center;padding:0!important;vertical-align:middle}.admin-table-actions form{vertical-align:middle}
    @media(max-width:575.98px){.admin-table-search{padding:16px}.admin-table-search-inline{grid-template-columns:1fr}.admin-table-search-submit{width:100%}}
</style>
<form class="admin-table-search" data-table-search="{{ $tableId }}" @isset($action) action="{{ $action }}" method="GET" @endisset>
    <h5><i class="fas fa-search mr-2 text-primary"></i>Find {{ $title }}</h5>
    <p>Search and filter the table details.</p>
    <label for="{{ $tableId }}Search">{{ $title }} search</label>
    <div class="admin-table-search-inline">
        <div class="admin-table-search-wrap"><i class="fas fa-search"></i><input type="search" id="{{ $tableId }}Search" @isset($inputName) name="{{ $inputName }}" @endisset value="{{ $value ?? '' }}" class="form-control" placeholder="{{ $placeholder }}" autocomplete="off"></div>
        <button type="submit" class="admin-table-search-submit"><i class="fas fa-search"></i> Apply filters</button>
    </div>
</form>
