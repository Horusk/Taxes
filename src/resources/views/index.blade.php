@extends('web::layouts.grids.12')

@section('title', trans('socialistmining::seat.name'))
@section('page_header', trans('socialistmining::seat.name'))
@section('page_description', trans('socialistmining::seat.name'))

@inject('request', Illuminate\Http\Request')

@section('full')
UKOC - 693378155
<div class="col-md-12">
    <!-- Custom Tabs -->
  <div class="row">
    <div class=" col-sm-4">
      <div class="input-group">
        <div class="input-group-addon">
          <i class="fa fa-calendar"></i>
        </div>
      <input type="text" class="form-control" id="datefilter">
    </div>
    </div>
    <div class=" col-sm-8">
      <button  class="btn btn-primary" id="fetchavg">Fetch avg</button>
      <button class="btn btn-warning" id="recalculate">Recalculate</button>
      <button class="btn btn-info" id="toggle-details">Toggle details</button>
    </div>
  </div>
  <form id="orePrices" class="form-inline">
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
    var itemTypes = [];
    var itemTypeIds = [];

    $('#recalculate').click(function(){
      character_list.draw();      
    });
    $('#toggle-details').click(function(){

      $('tr.odd').toggle();
      $('tr.even').toggle();     
    });

    $('#fetchavg').click(function(){
      $.ajax({
        url:"{{ url('/averageprices') }}",
        method:'POST',
        data: {
          typeIds:itemTypeIds,
          startDate: selectedStartDate.endOf('day').format('YYYY-MM-DD'),
          endDate: selectedEndDate.endOf('day').format('YYYY-MM-DD'),
        },
        dataType: 'json',
        success:function(priceResponse){
          for(var rIndex=0;rIndex < priceResponse.length; rIndex++){
            var currentResponseItem = priceResponse[rIndex];
            $('#ore-'+currentResponseItem.typeId).val(currentResponseItem.avgPrice);
          }
        },
        error: function(err){
          console.log(err);
        }
      });
    });

    var character_list = $('table#socialistmining-table').DataTable({
      processing      : true,
      serverSide      : true,
      paging       : false, 
      searching       : false, 
      ajax            : {
        url : '{{ route('socialistmining.corp.ledger') }}',
        data: function ( d ) {
		      d.$startDate = selectedStartDate.startOf('day').format('YYYY-MM-DD');
		      d.$endDate = selectedEndDate.endOf('day').format('YYYY-MM-DD');
		      d.$get = true;
          d.$compressedPrices = [];
          $('[id^=ore-]').each(function(index,element){
            d.$compressedPrices.push({
            price: $(element).val(),
            typeId: element.id.split('-')[1]
            });
          });

      }},
      columns         : [
        {data: 'userName', name: 'userName'},
        {data: 'typeName', name: 'typeName'},
        {data: 'miningDate', name: 'miningDate'},
        {data: 'quantity', name: 'quantity',render: $.fn.dataTable.render.number( ',', '.', 2 )},
        {data: 'originalAmounts', name: 'originalAmounts',render: $.fn.dataTable.render.number( ',', '.', 2 )},
        {data: 'compressed_quantity', name: 'compressed_quantity',render: $.fn.dataTable.render.number( ',', '.', 2 )},
        {data: 'compressedAmounts', name: 'compressedAmounts',render: $.fn.dataTable.render.number( ',', '.', 2 )}
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
                var originalQuantitySum = rows
                    .data()
                    .pluck('quantity')
                    .reduce( function (a, b) {
                        return (Number(!a?0:a) + Number(!b?0:b)).toFixed(2);
                    }, 0);

                var compressedAmountsSum = rows
                    .data()
                    .pluck('compressedAmounts')
                    .reduce( function (a, b) {
                        return (Number(!a?0:a) + Number(!b?0:b)).toFixed(2);
                    }, 0);
                var compressedQuantitySum = rows
                    .data()
                    .pluck('compressed_quantity')
                    .reduce( function (a, b) {
                        return (Number(!a?0:a) + Number(!b?0:b)).toFixed(2);
                    }, 0);
                //amountsAvg = $.fn.dataTable.render.number(',', '.', 0, '$').display( amountsAvg );
                var userNames = rows
                    .data()
                    .pluck('userName')
                    .unique();
                var userNamesFormatted = '';
                for(var userNameIndex = 0; userNameIndex < userNames.length; userNameIndex++)
                  userNamesFormatted += userNames[userNameIndex] + ' ';

                return $('<tr id="sum-player-'+group+'"/>')
                    .append( '<td colspan="3">Sum for '+userNamesFormatted+'</td>' )
                    .append( '<td>'+ addCommas(originalQuantitySum) +'</td>' )
                    .append( '<td>'+ addCommas(originalAmountsSum) +'</td>' )
                    .append( '<td>'+ addCommas(compressedQuantitySum) +'</td>' )
                    .append( '<td id="compressedsum-'+group+'">'+ addCommas(compressedAmountsSum) +'</td>' )
                    .append( '<td/>' );
            },
            dataSrc: 'userGroupId'},
      drawCallback: function (response) {
        //clear
        for(var dataIndex=0;dataIndex < response.json.data.length;dataIndex++){
          var dataItem = response.json.data[dataIndex];
                    
          if(itemTypeIds.filter(function(itemTypeId){
            return itemTypeId===dataItem.compressedTypeId;
          }).length === 0){
            itemTypeIds.push(dataItem.compressedTypeId);
            $('#orePrices').append('<div class="input-group col-sm-3"><label for="ore-' + dataItem.compressedTypeId + '">' + dataItem.compressedTypeName + '</label><input class="form-control" type="number" id="ore-' + dataItem.compressedTypeId + '"/></div>');
          }
        }

        $("img").unveil(100);
      }
    });
  character_list.on('draw',function(){
    var sumElements = $('[id^=sum-player-]');
          sumElements.each(function(index,element){
            var playerId = element.id.split('-player-')[1];
            $(element).click(function(){
                $('tr.player-'+playerId).toggle();
            });
        });
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
