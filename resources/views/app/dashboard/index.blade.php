@extends('app.include.master')

@section('title','Dashboard')

@section('content')
<div class="content">
  <div class="container-fluid">
    <div class="header text-center">
      <h3 class="title">APLIKASI PEMETAAN</h3>
      <p class="category">VAKSINASI COVID PADA WILAYAH
        <a>BENGKULU</a>
      </p>
    </div>
  	<div class="row">
      <div class="col-md-4">
        <div class="card no-margin-top">
          <div class="card-header card-header-danger card-header-icon">
            <div class="card-icon">
              <i class="material-icons">assignment</i>
            </div>
            <h4 class="card-title semi-bold">
              Data Vaksinasi
              <button id="form-vaksin-btn" type="button" class="close transition" data-dismiss="alert" aria-label="Close" onclick="toogleHideGrafik('#form-vaksin')">
                <i class="fa fa-plus" aria-hidden="true"></i>
              </button>
            </h4>
          </div>
          <div class="card-body no-padding-bottom">
            <div id="form-vaksin">
              <form id="prosesBobot" action="{{route('prosesNilaiBobot')}}" enctype="multipart/form-data" method="POST">
                <input type="hidden" name="_token" value="{{ Session::token() }}" />
                <div class="row">
                  <div class="col-md-12">
                    <div class="form-group bmd-form-group">
                      <label for="exampleEmail" class="bmd-label-floating" id="lblIP">Jumlah Vaksinasi</label>
                      <input type="text" class="form-control" id="txtCount" name="txtCount" required>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-12">
                    <div class="form-group">
                      <select class="selectpicker" data-live-search="true" id="slctWilayah" name="slctWilayah" data-size="7" data-style="select-with-transition" title="Wilayah" data-select="wizard-select" required onchange="getDataWilayah(this.value)">
                        <option selected="" disabled="" value="0">Wilayah</option>
                        @foreach($mDataWilayah as $tmp)
                        <option value="{{$tmp->id}}">{{$tmp->nama_wilayah}}</option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-12">
                    <div class="form-group bmd-form-group">
                      <input type="text" class="form-control" id="txtJmlhPdk" name="txtJmlhPdk" readonly placeholder="Jumlah Penduduk">
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-12">
                    <div class="form-group bmd-form-group">
                      <label for="exampleEmail" class="bmd-label-floating" id="lblIP">Tanggal</label>
                      <input type="text" class="form-control datepicker" id="txtDate" name="txtDate" value="{{date('Y-m-d')}}" required>
                    </div>
                  </div>
                </div>
                <div class="row padding-5 semi-bold">
                  <div class="col-md-12 col-sm-12 col-12">
                    <button type="submit" class="btn btn-success btn-sm"  style="float: right;">
                      <i class="material-icons">save_alt</i> Proses
                    </button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-8">
        <div class="card no-margin-top">
          <div class="card-header card-header-danger card-header-icon">
            <div class="card-icon">
              <i class="material-icons">map</i>
            </div>
            <h4 class="card-title semi-bold">
              Peta Bengkulu
              <button id="divMapBE-btn" type="button" class="close transition" data-dismiss="alert" aria-label="Close" onclick="toogleHideGrafik('#divMapBE')">
                <i class="fa fa-plus" aria-hidden="true"></i>
              </button>
            </h4>
          </div>
          <div class="card-body no-padding-bottom">
            <div id="divMapBE">
              <div id="map-be"></div>
              <div class="row padding-5 semi-bold">
                @if(count($dataNilaiBobot) < 4)
                  <!-- {{ $disabled = 'disabled' }} -->
                @else
                  <!-- {{ $disabled = '' }} -->
                @endif
                <div class="col-md-6">
                  <a href="/">
                    <button type="button" {{ $disabled }} class="btn btn-sm btn-info" style="float: left;">Clear</button>
                  </a>
                  <a href="/index/cluster/1">
                    <button type="button" {{ $disabled }} class="btn btn-sm btn-success" style="float: left;">Cluster 1</button>
                  </a>
                  <a href="/index/cluster/2">
                    <button type="button" {{ $disabled }} class="btn btn-sm btn-danger" style="float: left;">Cluster 2</button>
                  </a>
                </div>
                <div class="col-md-4">
                  <div class="form-group bmd-form-group">
                    <label for="exampleEmail" class="bmd-label-floating" id="lblIP">Tanggal</label>
                    <input type="text" class="form-control datepicker" id="txtDateFilter" name="txtDateFilter" value="{{date('Y-m-d')}}" required>
                  </div>
                </div>
                <div class="col-md-2">
                  <a onclick="filterDate()">
                    <button type="button" class="btn btn-sm btn-info" style="float: left;">
                      <i class="material-icons">search</i>Filter
                    </button>
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-7">
        <div class="card no-margin-top">
          <div class="card-header card-header-danger card-header-icon">
            <div class="card-icon">
              <i class="material-icons">assignment</i>
            </div>
            <h4 class="card-title semi-bold">
              Nilai Bobot Wilayah ( Minimal 4 Data Untuk Process Cluster )
              <button id="tableNilaiBobot-btn" type="button" class="close transition" data-dismiss="alert" aria-label="Close" onclick="toogleHideGrafik('#tableNilaiBobot')">
                <i class="fa fa-plus" aria-hidden="true"></i>
              </button>
            </h4>
          </div>
          <div class="card-body">
            <div id="tableNilaiBobot">
              <div class="material-datatables">
                <table id="dataList" class="table table-striped table-no-bordered table-hover dataTables" cellspacing="0" width="100%" style="width:100%">
                  <thead class="bold">
                    <tr>
                      <th class="disabled-sorting">No</th>
                      <th>Date</th>
                      @foreach($mDataWilayah as $tmp)
                      <th width="7%" class="disabled-sorting">{{$tmp->kode_wilayah}}</th>
                      @endforeach
                    </tr>
                  </thead>
                  <!-- {{$no = 1}} -->
                  <tbody>
                    @foreach($dataNilaiBobot as $tmpBbt)
                    <tr>
                      <td>{{$no++}}</td>
                      <td>{{$tmpBbt->date}}</td>
                      @foreach($mDataWilayah as $tmpWil)
                      <!-- {{ $arCode = 'ar_'.strtolower($tmpWil->kode_wilayah) }} -->
                      <td class="">{{$tmpBbt->$arCode}}</td>
                      @endforeach
                    </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-5">
        <div class="card no-margin-top">
          <div class="card-header card-header-danger card-header-icon">
            <div class="card-icon">
              <i class="material-icons">assignment</i>
            </div>
            <h4 class="card-title semi-bold">
              Data Cluster
              <button id="tableDataCluster-btn" type="button" class="close transition" data-dismiss="alert" aria-label="Close" onclick="toogleHideGrafik('#tableDataCluster')">
                <i class="fa fa-plus" aria-hidden="true"></i>
              </button>
            </h4>
          </div>
          <div class="card-body">
            <div id="tableDataCluster">
              <div class="material-datatables">
                <table id="dataList" class="table table-striped table-no-bordered table-hover dataTables" cellspacing="0" width="100%" style="width:100%">
                  <thead class="bold">
                    <tr>
                      <th class="disabled-sorting">No</th>
                      <th>Date</th>
                      <th class="disabled-sorting">Dc1</th>
                      <th class="disabled-sorting">Dc2</th>
                      <th class="disabled-sorting">Cluster</th>
                    </tr>
                  </thead>
                  <!-- {{$no = 1}} -->
                  <tbody>
                  @foreach($dataClusterWilayah as $tmp)
                    <tr>
                      <td>{{$no++}}</td>
                      <td>{{$tmp->date}}</td>
                      <td>{{$tmp->dc1}}</td>
                      <td>{{$tmp->dc2}}</td>
                      <td>{{$tmp->cluster}}</td>
                    </tr>
                  @endforeach
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
@section('additionalCSS')
<style type="text/css">

</style>
@endsection
@section('additionalJS')
<script src="https://code.highcharts.com/maps/highmaps.js"></script>
<script src="https://code.highcharts.com/maps/modules/map.js"></script>
<script src="{{ asset('assets/js/hm-id-all.js') }}"></script>
<script type="text/javascript">
  var table = "";

  function toogleHideGrafik(canvas) {
    $(canvas+"-btn").html("<i class='fa fa-minus' aria-hidden='true'></i>");
    $(canvas+"-btn").attr("onclick","toogleShowGrafik('"+canvas+"')");
    $(canvas).slideUp();
  }

  function toogleShowGrafik(canvas) {
    $(canvas+"-btn").html("<i class='fa fa-plus' aria-hidden='true'></i>");
    $(canvas+"-btn").attr("onclick","toogleHideGrafik('"+canvas+"')");
    $(canvas).slideDown();
  }


  $('.datepicker').datetimepicker({
    format: 'YYYY-MM-DD',
    icons: {
      time: "fa fa-clock-o",
      date: "fa fa-calendar",
      up: "fa fa-chevron-up",
      down: "fa fa-chevron-down",
      previous: 'fa fa-chevron-left',
      next: 'fa fa-chevron-right',
      today: 'fa fa-screenshot',
      clear: 'fa fa-trash',
      close: 'fa fa-remove'
    }
  });

  function filterDate() {
    location.href = '/index/date/'+$('#txtDateFilter').val();
  }

  function getDataWilayah(value) {
    $.ajax({
      url:"{{ route('getDataWilayah') }}",
      data:{id : value},
      type:'GET',
      success:function(data){
        // console.log(data);
        $('#txtJmlhPdk').val(addCommas(data[0].penduduk_wilayah));
      }
    });
  }

  $(document).ready(function() {
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
    });
   // Prepare demo data
// Data is joined to map using value of 'hc-key' property by default.
// See API docs for 'joinBy' for more info on linking data and map.
// Create the chart
     
    Highcharts.mapChart('map-be', {
      title: {
        text: 'Chart Peta Bengkulu'
      },

      mapNavigation: {
        enabled: true,
        buttonOptions: {
            verticalAlign: 'bottom'
        }
      },

      legend: {
        enabled:false
      },

      tooltip: {
        formatter: function () {
          return '<b>Series Name: ' + this.series.name + '</b><br>' +
            'Wilayah: ' + this.point.name + '<br>' +
             this.point.value;
          }
      },

      <?php echo $dataSeriesMaps; ?>      
    });
});
</script>
@endsection