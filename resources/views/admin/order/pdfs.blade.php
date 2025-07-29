<x-admin>
    @section('title', 'Available PDF Files')
    <div class="card">
        <div class="pdf-grid">
            <div class="pdf-card">
                <table class="table table-striped table-bordered table-hover">
                    <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>File Name</th>
                        <th>View</th>
                    </tr>
                    </thead>
                    <tbody>
                    @php
                        $i=1;
                    @endphp
                    @foreach($pdfUrls as $pdf)
                    <tr>
                        <td><div class="pdf-icon">{{ $i++ }}</div></td>
                        <td><div class="pdf-name">{{ $pdf['name'] }}</div></td>
                        <td><a href="{{ $pdf['url'] }}" target="_blank" class="pdf-link">📄 View PDF</a></td>
                    </tr>
                    @endforeach
                </tbody>
                </table>
            </div>
        </div>
    </div>
</x-admin>
