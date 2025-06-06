    @section('css')
     <style>
     .cardbox {
        background: #f6f6f6;
        border-radius: 2px;
        display: inline-block;
        height: 93px;
        margin: 1rem;
        position: relative;
        width: 181%;
        }
        .focus{
                margin-top: 30px;
                margin-left: 11px;
        }
    </style>
    @endsection
  <div class="card">
    <div class="card-info">
      <div class="card-header">
            <h3 class="card-title"> Optimization Grade</h3>            
        </div>
        @php
        $focusKeyword = $detail->focus_keyword;
          $result = checkWebsiteSeoContent($data->id,$focusKeyword);
            $percentage = (int) $result['percentage']; 
                $progressClass = 'bg-danger';
                if ($percentage > 60) {
                    $progressClass = 'bg-success';
                } elseif ($percentage > 50) {
                    $progressClass = 'bg-warning';
                }
               @endphp
            <div class="card">
                    <div class="card-body">
                        <div class="row">
                          <div class="col-md-12">
                                <h3 class="w3_inner_tittle two">@php echo "<h3>Your score is: {$result['percentage']} %</h3>";@endphp</h3>
                            </div>  
                            <div class="col-md-12">
                                <div class="progress" style="height: 31px;">
                                    <div class="progress-bar @php echo $progressClass; @endphp progress-bar-striped"
                                     role="progressbar"
                                    aria-valuenow="@php echo $percentage; @endphp"
                                    aria-valuemin="0"
                                    aria-valuemax="100"
                                    style="width: <?php echo $percentage; ?>%;">
                                    <?php echo $percentage; ?>%
                                </div>
                                </div>
                            </div>
                            <div class="col-md-12" style="margin-top: 14px;">
                                <div class="progress" style="height: 31px;">
                                    <div class="progress-bar bg-danger progress-bar-striped" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="width:30%">Low</div>
                                    <div class="progress-bar bg-warning progress-bar-striped" role="progressbar" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100" style="width:35%">Medium</div>
                                    <div class="progress-bar bg-success progress-bar-striped" role="progressbar" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100" style="width:35%">High</div>
                                </div>
                            </div>
                      
                            <form  action="{{ route('admin.tour.addfocus', $data->id) }}" method="POST" enctype="multipart/form-data" autocomplete="off">
                              @csrf
                           <div class="cardbox">
                            <div class="row col-md-12 focus">
                            <div class="form-group col-md-2">
                                <label class="control-label">Focus Keyword</label> 
                            </div>
                             <div class="form-group col-md-8">
                                <div>
                                    <input type="hidden" name="id" value="{{ $data->id }}">
                                    <input type="text" class="form-control icon" name="focus_keyword" 
                                    id="focus_keyword" placeholder="10" autocomplete="off" value="{{ !empty($focusKeyword) ? $focusKeyword : $data->title }}">
                                </div>
                            </div> 
                            <div class="form-group col-md-2"> 
                                <button type="submit" name="submit" class="btn btn-success btn-flat btn-pri">Submit</button>
                            </div>     
                        </div>
                        </div>
                    </form>
                        <div class="col-md-12">
                             <div class="mb-3">
                                <h5 class="text-success">✅ Passed Checks</h5>
                                <ul style="list-style: none;">
                                    @forelse ($result['passed'] as $item)
                                        <li>{{ $item }}</li>
                                    @empty
                                        <li>None</li>
                                    @endforelse
                                </ul>
                            </div>

                            <div class="mb-3">
                                <h5 class="text-warning">⚠️ Warnings</h5>
                                <ul style="list-style: none;">
                                    @forelse ($result['warning'] as $item)
                                        <li>{{ $item }}</li>
                                    @empty
                                        <li>None</li>
                                    @endforelse
                                </ul>
                            </div>

                            <div class="mb-3">
                                <h5 class="text-danger">❌ Failed Checks</h5>
                                <ul style="list-style: none;">
                                    @forelse ($result['failed'] as $item)
                                        <li>{{ $item }}</li>
                                    @empty
                                        <li>None</li>
                                    @endforelse
                                </ul>
                            </div>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
    </div>
</div>
