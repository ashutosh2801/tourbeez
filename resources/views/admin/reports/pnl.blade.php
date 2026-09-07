<x-admin>
    @section('title', 'PNL Report')

    <style>
        .pnl-table th, .pnl-table td { white-space: nowrap; font-size: 12px; vertical-align: middle; }
        .pnl-table th { background: #f5f7fb; }
        .pnl-negative { color: #dc3545; }
    </style>

    <div class="card card-primary bg-white border rounded-lg-custom mb-3">
        <div class="card-header"><h3 class="card-title">PNL Report</h3></div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.report.pnl') }}">
                <div class="row">
                    <div class="col-md-3">
                        <label>Travel Date</label>
                        <input type="date" name="travel_start_date" class="form-control" value="{{ request('travel_start_date') }}">
                    </div>
                    <div class="col-md-3">
                        <label>Travel Date To</label>
                        <input type="date" name="travel_end_date" class="form-control" value="{{ request('travel_end_date') }}">
                    </div>
                </div>
                <div class="mt-3">
                    <button class="btn btn-primary">Apply</button>
                    <a href="{{ route('admin.report.pnl') }}" class="btn btn-secondary">Reset</a>
                    @if(request()->hasAny(['travel_start_date','travel_end_date']))
                        <a href="{{ route('admin.report.pnl.export', request()->query()) }}" class="btn btn-success">Download Excel</a>
                    @endif
                </div>
            </form>
            <small class="text-muted d-block mt-3">Commission and Stripe charges are calculated at 5.5% of Revenue With HST.</small>
        </div>
    </div>

    @if(!request()->hasAny(['travel_start_date','travel_end_date']))
        <div class="alert alert-info">Select an order-date range or travel-date range to view the PNL report.</div>
    @else
        <div class="card card-primary bg-white border rounded-lg-custom">
            <div class="card-body table-responsive">
                <table class="table table-bordered table-hover pnl-table">
                    <thead><tr>
                        <th>Travel Date</th><th>Revenue Without HST</th><th>Commission-Stripe Revenue</th><th>Direct Cost Without HST</th>
                        <th>Transport Cost For Reference</th><th>Total Direct Cost</th><th>Gross Profit/Loss</th>
                        <th>Ad Expenses</th><th>Net Profit/Loss</th>
                    </tr></thead>
                    <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td>{{ $row['travel_date'] ? \Carbon\Carbon::parse($row['travel_date'])->format('d/m/Y') : '-' }}</td>
                            @foreach(['revenue_without_hst','commission_stripe_revenue','supplier_cost_without_hst','transport_cost','total_direct_cost','gross_profit_loss','ad_expenses','net_profit_loss'] as $key)
                                @php($value = (float) $row[$key])
                                <td class="{{ $value < 0 ? 'pnl-negative' : '' }}" align="right">{{ $value < 0 ? '(' : '' }}${{ number_format(abs($value), 2) }}{{ $value < 0 ? ')' : '' }}</td>
                            @endforeach
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center">No data found</td></tr>
                    @endforelse
                    </tbody>
                    @if(count($rows))
                        <tfoot>
                            <tr class="font-weight-bold">
                                <td>Grand Total</td>
                                @foreach(['revenue_without_hst','commission_stripe_revenue','supplier_cost_without_hst','transport_cost','total_direct_cost','gross_profit_loss','ad_expenses','net_profit_loss'] as $key)
                                    @php($total = (float) collect($rows)->sum($key))
                                    <td align="right" class="{{ $total < 0 ? 'pnl-negative' : '' }}">{{ $total < 0 ? '(' : '' }}${{ number_format(abs($total), 2) }}{{ $total < 0 ? ')' : '' }}</td>
                                @endforeach
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    @endif
</x-admin>
