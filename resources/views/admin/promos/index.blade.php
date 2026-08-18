<x-admin>
    <style>
        .promo-search-panel{margin:-1px -1px 18px;padding:20px;border:1px solid #e5e7eb;border-radius:8px;background:linear-gradient(180deg,#f8fafc 0%,#fff 100%);}
        .promo-search-heading{margin-bottom:14px;}
        .promo-search-heading h5{margin:0 0 3px;color:#172033;font-size:17px;font-weight:700;}
        .promo-search-heading p{margin:0;color:#6b7280;font-size:13px;}
        .promo-search-field label{display:block;margin:0 0 6px;color:#374151;font-size:12px;font-weight:600;}
        .promo-search-inline{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:10px;align-items:stretch;}
        .promo-search-wrap{position:relative;}
        .promo-search-wrap>i{position:absolute;top:50%;left:13px;color:#9ca3af;transform:translateY(-50%);z-index:1;}
        .promo-search-wrap .form-control{height:40px;padding-left:37px;border-color:#d7dce3;border-radius:7px;font-size:13px;}
        .promo-search-submit{min-width:120px;height:40px;display:inline-flex;align-items:center;justify-content:center;gap:7px;border:1px solid #4f46e5;border-radius:7px;background:#4f46e5;color:#fff;font-size:13px;font-weight:600;}
        .promo-search-submit:hover{background:#4338ca;color:#fff;}
        .promo-action-btn{width:34px;height:34px;display:inline-flex!important;align-items:center;justify-content:center;padding:0!important;vertical-align:middle;}
        .promo-actions form{vertical-align:middle;}
        @media(max-width:575.98px){.promo-search-panel{padding:16px}.promo-search-inline{grid-template-columns:1fr}.promo-search-submit{width:100%}}
    </style>
    @section('title','Promo Codes')

    <!-- HEADER -->
    <div class="extra-header card card-primary">
        <div class="card-header">
            <div class="row">
                <div class="col-md-8">
                    <h3 class="card-title">Promo Codes</h3>
                </div>
                <div class="col-md-4 text-right">
                    <a href="{{ route('admin.promos.create') }}" class="btn btn-sm btn-success"> + Create New</a>
                </div>
            </div>            
        </div>
    </div>

    <!-- BODY -->
    <div class="extra-addon-body">
        <div class="card card-primary bg-white border rounded-lg-custom">
            <div class="card-body p-3">

                <form id="promoSearchForm" class="promo-search-panel">
                    <div class="promo-search-heading">
                        <h5><i class="fas fa-search mr-2 text-primary"></i>Find promos</h5>
                        <p>Search promo codes and matching table details.</p>
                    </div>
                    <div class="promo-search-field">
                        <label for="promoSearch">Promo search</label>
                        <div class="promo-search-inline">
                            <div class="promo-search-wrap">
                                <i class="fas fa-search"></i>
                                <input type="search" id="promoSearch" class="form-control" placeholder="Code, status, value or date" autocomplete="off">
                            </div>
                            <button type="submit" class="promo-search-submit"><i class="fas fa-search"></i> Apply filters</button>
                        </div>
                    </div>
                </form>

                <!-- TABLE -->
                <table class="table table-striped" id="promoTable">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Status</th>
                            <th>Value</th>
                            <th>Redemptions</th>
                            <th>Validity Date</th>
                            <th>Travel Date</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($promos as $promo)
                            <tr>
                                <td>{{ $promo->code }}</td>
                                <td>{{ $promo->status }}</td>
                                <td>
                                    @if(str_contains($promo->value_type, 'VALUE'))
                                        ${{ $promo->voucher_value }}
                                    @elseif(str_contains($promo->value_type, 'PERCENT'))
                                        {{ $promo->value_percent }}%
                                    @endif
                                </td>
                                <td>
                                    @if($promo->redemption_limit == 'UNLIMITED')
                                        Unlimited
                                    @else
                                        {{ $promo->max_uses }}
                                    @endif
                                </td>
                                <td>
                                    {{ $promo->issue_date }} - {{ $promo->expiry_date }}
                                </td>
                                <td>
                                    @if($promo->travel_from_date && $promo->travel_to_date)
                                        {{ $promo->travel_from_date }} - {{ $promo->travel_to_date }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td class="text-center text-nowrap promo-actions">
                                    <a href="{{ route('admin.promos.edit', $promo->id) }}"
                                       class="btn btn-sm btn-edit promo-action-btn"
                                       title="Edit promo"
                                       aria-label="Edit promo {{ $promo->code }}">
                                        <i class="far fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.promos.copy', $promo->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit"
                                                class="btn btn-sm btn-warning promo-action-btn"
                                                title="Copy promo"
                                                aria-label="Copy promo {{ $promo->code }}">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.promos.destroy', $promo->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="btn btn-sm btn-danger btn-delete promo-action-btn"
                                                title="Delete promo"
                                                aria-label="Delete promo {{ $promo->code }}">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>
        </div>
    </div>

    @section('js')
        <script>
            $(document).ready(function() {
                var table = $('#promoTable').DataTable({
                    "paging": false,
                    "ordering": true,
                    "responsive": true,
                    "info": false,
                    "lengthChange": false,
                    "dom": "rt"
                });

                $('#promoSearch').on('input', function() {
                    table.search(this.value).draw();
                });

                $('#promoSearchForm').on('submit', function(event) {
                    event.preventDefault();
                    table.search($('#promoSearch').val()).draw();
                });
            });
        </script>
    @endsection
</x-admin>
