
  <div class="card">
    <div class="card-info">
      <div class="card-header">
            <h3 class="card-title"> Optimization Grade</h3>            
        </div>
        @php
          $result = checkWebsiteSeoContent($data->id);
          $percentage = (int) $result['percentage']; // remove % if needed

                $progressClass = 'bg-info';
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
                                    style="width: @php echo $percentage; @endphp %;">
                                    @php echo $percentage; @endphp %
                                </div>
                                </div>
                            </div>
                            <div class="col-md-12" style="margin-top: 14px;">
                                <div class="progress" style="height: 31px;">
                                    <div class="progress-bar bg-info progress-bar-striped" role="progressbar" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100" style="width:50%">Low</div>
                                    <div class="progress-bar bg-warning progress-bar-striped" role="progressbar" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100" style="width:25%">Medium</div>
                                    <div class="progress-bar bg-success progress-bar-striped" role="progressbar" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100" style="width:25%">High</div>
                                </div>
                            </div>
                            <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">Focus Keyword</label> 
                                <div>
                                
                                    <input type="hidden" name="id" value="{{ $data->id }}" disabled="disabled">
                                    <input type="text" class="form-control icon" name="focus_keyword" 
                                    id="focus_keyword" placeholder="10" autocomplete="off" value="{{ $data->title }}" 
                                    disabled="disabled">
                                </div>
                            </div>  
                            <button type="submit" name="submit" class="btn btn-success btn-flat btn-pri">Submit</button>
                        </div>
                        <br>
                        <div class="col-md-12">
                            @php
                            echo "<br /><h3>✅ Passed Checks:</h3><br />";
                            echo "<ul style='list-style:none;font-size:15px;line-height:1.7em'>";
                            foreach ($result['passed'] as $msg) {
                                    echo "<li>$msg</li>";
                                }
                            
                            echo "</ul><br /><br /><ul style='list-style:none;font-size:15px;line-height:1.7em; color:red; margin-left: -37px;'>";
                            echo "<h3>❌ Failed Checks:</h3><br />";
                            foreach ($result['failed'] as $msg) {
                                        echo "<li>$msg</li>";
                                    }
                            echo "<ul>";
                            
                           @endphp
                           
                        </div>
                        </div>
                    </div>
                </div>
            </div>
    </div>
</div>
