    @section('css')
     <style>
     
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
            if($percentage <  30){
                $progressClass = 'bg-danger';
            } elseif ($percentage < 65) {
                $progressClass = 'bg-warning';
            }else{
                $progressClass = 'bg-success';
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
                
                    <div class="col-md-12">
                        <form  action="{{ route('admin.tour.addfocus', $data->id) }}" method="POST" enctype="multipart/form-data" autocomplete="off">
                            @csrf
                            <div class="cardbox">
                                <div class="row">
                                    <div class="form-group col-xl-2 col-lg-3 col-md-2">
                                        <label class="control-label">Focus Keyword</label> 
                                    </div>
                                    <div class="form-group col-xl-8 col-lg-6 col-md-8">
                                        <div>
                                            <input type="hidden" name="id" value="{{ $data->id }}">
                                            <input type="text" class="form-control icon" name="focus_keyword" 
                                            id="focus_keyword"  autocomplete="off" value="{{ !empty($data->detail->focus_keyword) ? $data->detail->focus_keyword : $data->title }}">
                                        </div>
                                    </div> 
                                
                                    <div class="form-group col-xl-2 col-lg-3 col-md-2"> 
                                        <button type="submit" name="submit" class="btn btn-success btn-flat btn-pri">Submit</button>
                                    </div>     
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="col-md-12">
                        <div class="seo-result">
                            <h5 class="sub-head text-success">Passed Checks</h5>
                            <ul>
                                @forelse ($result['passed'] as $item)
                                    <li>{{ $item }}</li>
                                @empty
                                    <li>None</li>
                                @endforelse
                            </ul>
                        </div>

                        <div class="seo-result">
                            <h5 class="sub-head text-warning">Warnings</h5>
                            <ul>
                                @forelse ($result['warning'] as $item)
                                    <li>{{ $item }}</li>
                                @empty
                                    <li>None</li>
                                @endforelse
                            </ul>
                        </div>

                        <div class="seo-result">
                            <h5 class="sub-head text-danger">Failed Checks</h5>
                            <ul>
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
            <div class="card-footer" style="display:block">
                <div class="row">
                    <div class="col-md-6">
                        <button style="padding:0.6rem 2rem" type="submit" id="submit" class="btn btn-success"> <i class="fas fa-save"></i> Save</button>
                    </div>
                    <div class="col-md-6 align-buttons">
                        <a style="padding:0.6rem 2rem" href="{{ route('admin.tour.edit.seo', encrypt($data->id)) }}" class="btn btn-secondary"> <i class="fas fa-chevron-left"></i> Back</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
