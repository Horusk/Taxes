@extends('web::layouts.grids.12')

@section('title', trans('socialistmining::seat.name'))
@section('page_header', trans('socialistmining::seat.name'))
@section('page_description', trans('socialistmining::seat.name'))

@inject('request', Illuminate\Http\Request')

@section('full')
<div class="col-md-12">
    <!-- Custom Tabs -->
  <div class="form-inline">
      <div class="input-group">
        <div class="input-group-addon">
          <i class="fa fa-calendar"></i>
        </div>
      <input type="text" class="form-control" id="datefilter">
    </div>
    <div class="form-group ">
        <label for="tax-amount">Tax</label>
        <input type="number" class="form-control" id="tax-amount" min="0" max="1" step="0.001" value="0.1">
    </div>
    <button  class="btn btn-primary" id="fetchavg">Fetch avg</button>
    <button class="btn btn-warning" id="recalculate">Recalculate</button>
    <button class="btn btn-info" id="toggle-details">Toggle details</button>
    <button class="btn btn-info" id="toggle-evepraisal">Toggle evepraisal mode</button>
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
              <th>type</th>
              <th>date</th>
              <th>quantity</th>
              <th>value</th>
              <th>compressed type</th>
              <th>compressed quantity</th>
              <th>compressed avg price</th>
              <th>compressed total value</th>
              <th>compressed tax</th>
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
    $('#toggle-evepraisal').click(function(){
      //lazy check
      if($('.evepraisalMode').first().is(':hidden')){
        $('.evepraisalMode').show();
      }
      else{
        $('.evepraisalMode').hide();
      }
    });
    $('#toggle-details').click(function(){
      //lazy check
      if($('tr[class^=player-]').first().is(':hidden')){
        $('tr[class^=player-]').show();
      }
      else{
        $('tr[class^=player-]').hide();
      }
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
          d.$taxAmount = $('#tax-amount').val();
          $('[id^=ore-]').each(function(index,element){
            d.$compressedPrices.push({
            price: $(element).val(),
            typeId: element.id.split('-')[1]
            });
          });

      }},
      columns         : [
        {data: 'userName', name: 'userName', class: 'evepraisalMode'},
        {data: 'typeName', name: 'typeName', class: 'evepraisalMode'},
        {data: 'miningDate', name: 'miningDate', class: 'evepraisalMode'},
        {data: 'quantity', name: 'quantity',render: $.fn.dataTable.render.number( ',', '.', 2 ), class: 'evepraisalMode'},
        {data: 'originalAmounts', name: 'originalAmounts',render: $.fn.dataTable.render.number( ',', '.', 2 ), class: 'evepraisalMode'},
        {data: 'compressedTypeName', name: 'compressedTypeName'},
        {data: 'compressed_quantity', name: 'compressed_quantity',render: $.fn.dataTable.render.number( ',', '.', 2 )},
        {data: 'compressedAveragePrice', name: 'compressedAveragePrice',render: $.fn.dataTable.render.number( ',', '.', 2 ), class: 'evepraisalMode'},
        {data: 'compressedAmounts', name: 'compressedAmounts',render: $.fn.dataTable.render.number( ',', '.', 3 ), class: 'evepraisalMode'},
        {data: 'userGroupId', name: 'userGroupId', visible: false},
        {data: 'compressedTax', name: 'compressedTax',render: $.fn.dataTable.render.number( ',', '.', 3 ), class: 'evepraisalMode'}
      ],
	  orderFixed:[9,'asc'],
	  rowGroup: {
            startRender: function ( rows, group ) {

                var userNames = rows
                    .data()
                    .pluck('userName')
                    .unique();


                var originalAmountsSum = rows
                    .data()
                    .pluck('originalAmounts')
                    .reduce( function (a, b) {
                        return (Number(!a?0:a) + Number(!b?0:b));
                    }, 0).toFixed(2);
                var originalQuantitySum = rows
                    .data()
                    .pluck('quantity')
                    .reduce( function (a, b) {
                        return (Number(!a?0:a) + Number(!b?0:b));
                    }, 0).toFixed(2);


                var compressedQuantitySum = rows
                    .data()
                    .pluck('compressed_quantity')
                    .reduce( function (a, b) {
                        return (Number(!a?0:a) + Number(!b?0:b));
                    }, 0).toFixed(2);
                var compressedAmountsSum = rows
                    .data()
                    .pluck('compressedAmounts')
                    .reduce( function (a, b) {
                        return (Number(!a?0:a) + Number(!b?0:b));
                    }, 0).toFixed(2);
                var compressedTaxSum = rows
                    .data()
                    .pluck('compressedTax')
                    .reduce( function (a, b) {
                        return (Number(!a?0:a) + Number(!b?0:b));
                    }, 0).toFixed(2);


                //amountsAvg = $.fn.dataTable.render.number(',', '.', 0, '$').display( amountsAvg );
                var userNamesFormatted = '';
                for(var userNameIndex = 0; userNameIndex < userNames.length; userNameIndex++)
                  userNamesFormatted += userNames[userNameIndex] + ' ';

                return $('<tr id="sum-player-'+group+'"/>')
                    .append( '<td colspan="1">Sum for '+userNamesFormatted+'</td>' )
                    .append( '<td class="evepraisalMode"></td>' )
                    .append( '<td class="evepraisalMode"></td>' )
                    .append( '<td class="evepraisalMode">'+ addCommas(originalQuantitySum) +'</td>' )
                    .append( '<td class="evepraisalMode">'+ addCommas(originalAmountsSum) +'</td>' )
                    .append( '<td class="evepraisalMode"></td>' )
                    .append( '<td>'+ addCommas(compressedQuantitySum) +'</td>' )
                    .append( '<td class="evepraisalMode"></td>' )
                    .append( '<td id="compressedsum-'+group+'">'+ addCommas(compressedAmountsSum) +'</td>' )
                    .append( '<td id="compressedtaxsum-'+group+'">'+ addCommas(compressedTaxSum) +'</td>' )
                    .append( '<td/>' );
            },
            endRender: null,
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
