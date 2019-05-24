@extends('web::layouts.grids.12')

@section('title', trans('socialistmining::seat.name'))
@section('page_header', trans('socialistmining::seat.name'))
@section('page_description', trans('socialistmining::seat.name'))

@inject('request', Illuminate\Http\Request')

@section('full')
UKOC - 693378155
<div class="col-md-12">
    <!-- Custom Tabs -->
	<div>
  <div class="input-group">
    <input type="text" class="form-control pull-right" id="datefilter">
  </div>
	</div>
    <div class="nav-tabs-custom">
      <div class="tab-content">
          <table class="table compact table-condensed table-hover table-responsive"
                 id="socialistmining-table" data-page-length=100>
            <thead>
            <tr>
              <th>Player</th>
              <th>typeName</th>
              <th>miningDate</th>
              <th>originalQuantity</th>
              <th>originalAmounts</th>
              <th>compressedQuantity</th>
              <th>compressedAmounts</th>
              <th></th>
            </tr>
            </thead>
          </table>
      </div>
      <!-- /.tab-content -->
    </div>
    <!-- nav-tabs-custom -->
  </div>

@stop

@push('javascript')
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
  <script>
    $(document).ready(function(){      
      selectedStartDate = moment().subtract(1,'month').startOf('month');
      selectedEndDate = moment().subtract(1,'month').endOf('month');
        $('#datefilter').daterangepicker({
          viewMode:1,
          startDate: selectedStartDate,
          endDate: selectedEndDate
        }, function(start,end,label){
          selectedStartDate = start;
          selectedEndDate = end;
          character_list.draw();
        });
    

    var character_list = $('table#socialistmining-table').DataTable({
      processing      : true,
      serverSide      : true,
      ajax            : {
        url : '{{ route('socialistmining.corp.ledger') }}',
        data: function ( d ) {
		      d.$startDate = selectedStartDate.startOf('day').format('YYYY-MM-DD');
		      d.$endDate = selectedEndDate.endOf('day').format('YYYY-MM-DD');
		      d.$get = true;
      }},
      columns         : [
        {data: 'userName', name: 'userName'},
        {data: 'typeName', name: 'typeName'},
        {data: 'miningDate', name: 'miningDate'},
        {data: 'quantity', name: 'quantity'},
        {data: 'originalAmounts', name: 'originalAmounts'},
        {data: 'compressed_quantity', name: 'compressed_quantity'},
        {data: 'compressedAmounts', name: 'compressedAmounts'}
      ],
	  orderFixed:[0,'asc'],
	  rowGroup: {
            startRender: function ( rows, group ) {
                var originalAmountsSum = rows
                    .data()
                    .pluck('originalAmounts')
                    .reduce( function (a, b) {
                        return (Number(!a?0:a) + Number(!b?0:b)).toFixed(2);
                    }, 0);

                var compressedAmountsSum = rows
                    .data()
                    .pluck('compressedAmounts')
                    .reduce( function (a, b) {
                        return (Number(!a?0:a) + Number(!b?0:b)).toFixed(2);
                    }, 0);
                //amountsAvg = $.fn.dataTable.render.number(',', '.', 0, '$').display( amountsAvg );
                var userNames = rows
                    .data()
                    .pluck('userName')
                    .unique();
                console.log(userNames);
                console.log(userNames[0]);
                console.log(userNames[1]);
                var userNamesFormatted = '';
                for(var userNameIndex = 0; userNameIndex < userNames.length; userNameIndex++)
                  userNamesFormatted += userNames[userNameIndex] + ' ';

                return $('<tr/>')
                    .append( '<td colspan="4">Sum for '+userNamesFormatted+'</td>' )
                    .append( '<td>'+ addCommas(originalAmountsSum) +'</td>' )
                    .append( '<td></td>' )
                    .append( '<td>'+ addCommas(compressedAmountsSum) +'</td>' )
                    .append( '<td/>' );
            },
            dataSrc: 'userGroupId'},
      drawCallback: function () {
        $("img").unveil(100);
        ids_to_names();
      },
    });
  });
	function addCommas(nStr)
{
	nStr += '';
	x = nStr.split('.');
	x1 = x[0];
	x2 = x.length > 1 ? '.' + x[1] : '';
	var rgx = /(\d+)(\d{3})/;
	while (rgx.test(x1)) {
		x1 = x1.replace(rgx, '$1' + ',' + '$2');
	}
	return x1 + x2;
}
  </script>
  
  @include('web::includes.javascript.id-to-name')

  @endpush
