@extends('web::layouts.grids.12')

@section('title', trans('socialistmining::seat.name'))
@section('page_header', trans('socialistmining::seat.name'))
@section('page_description', trans('socialistmining::seat.name'))

@inject('request', Illuminate\Http\Request')

@section('full')
<div class="row" id="socialistmining">
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
      <button class="socialistmining btn btn-primary" id="fetchavg">Fetch avg</button>
      <button class="socialistmining btn btn-warning" id="recalculate">Recalculate</button>
      <button class="socialistmining btn btn-info" id="toggle-details">Toggle details</button>
      <button class="socialistmining btn btn-info" id="toggle-evepraisal">Toggle evepraisal mode</button>
    </div>
    <div id="regions" class="form-inline">
    <div id="custom-search-input">
      <div class="input-group">
          <input id="search-region" name="search" type="text" class="form-control" placeholder="Search regions" />
      </div>
      <button class="socialistmining btn btn-primary" id="add-region">Add region filter</button>
    </div>
    </div>
    <form id="orePrices" class="form-inline">
    </form>
      <div class="nav-tabs-custom">
        <div class="tab-content">
            <table class="table compact table-condensed table-hover table-responsive"
                   id="socialistmining-table" data-page-length=100>
              <thead>
              <tr>
                <th/>
                <th>Player</th>
                <th>Date</th>
                <th>Region</th>
                <th>Type</th>
                <th>Quantity</th>
                <th>Average price</th>
                <th>Total value</th>
                <th>Tax</th>
              </tr>
              </thead>
            </table>
        </div>
        <!-- /.tab-content -->
      </div>
      <!-- nav-tabs-custom -->
  </div>
</div>
@stop

@push('javascript')
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
  <script>

    function ajaxBeforeSend(){
      $('button.socialistmining').addClass("disabled");
      $('#socialistmining-table_processing').show();
    };
    function ajaxComplete(){
      $('button.socialistmining').removeClass("disabled");
      $('#socialistmining-table_processing').hide();
    };

    $(document).ready(function(){
      $( "#search-region" ).autocomplete({ 
            source: function(request, response) {
                $.ajax({
                url : '{{ route('regions.autocomplete') }}',
                data: {
                        term : request.term
                 },
                dataType: "json",
                success: function(data){
                  data = jQuery.grep(data, function(obj){
                    var include = true;
                    regions.forEach(function(region){
                      if(region.regionName == obj.regionName)
                        include = false;
                    });
                    return include;
                  });
                   var resp = $.map(data,function(obj){
                        return obj.regionName;
                   });
                   response(resp);
                }
            });
        },
        minLength: 1
     });
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


    $('#add-region').click(function(){
      var regionNameToAdd = $('#search-region').val();
      var url = '{{ route("region.get", ":id") }}';
      url = url.replace(':id', regionNameToAdd);
      $.ajax({
        url : url,
        type: 'GET',
        beforeSend: ajaxBeforeSend,
        complete: ajaxComplete,
        success: function(result){
          regions.push(result);
          renderRegions();
        }
      });
    });
    function renderRegions(){
      regions.forEach(function(region){
        if($('#region-'+region.regionID).length === 0){
          $('#regions').append('<button id="region-'+ region.regionID +'" class="label label-primary">'+region.regionName+'</button>');
          $('#region-'+ region.regionID).click(function(){
            $('#region-'+ region.regionID).remove();
            regions = jQuery.grep(regions, function(item){
              return item.regionID !== region.regionID;
            });
            $('#region-'+ region.regionID).remove();
          });
        }

      })
    }
    var regions = [];
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
        },
        beforeSend: ajaxBeforeSend,
        complete: ajaxComplete
      });
    });

    var character_list = $('table#socialistmining-table').DataTable({
      processing      : true,
      serverSide      : true,
      paging       : false, 
      responsive: false,
      searching       : false,
      ajax            : {
        url : '{{ route('socialistmining.corp.ledger') }}',
        type: 'POST',
        beforeSend: ajaxBeforeSend,
        complete: ajaxComplete,
        data: function ( data ) {
		      data.$startDate = selectedStartDate.format('YYYY-MM-DD');
		      data.$endDate = selectedEndDate.format('YYYY-MM-DD');
          data.$compressedPrices = [];
          data.$taxAmount = $('#tax-amount').val();
          $('[id^=ore-]').each(function(index,element){
            data.$compressedPrices.push({
            price: $(element).val(),
            typeId: element.id.split('-')[1]
            });
          });
          data.$regionIds = [];
          $('[id^=region-]').each(function(index,element){
            data.$regionIds.push(element.id.split('-')[1]);
          });

      }},
      columns         : [
        {data: 'userGroupId', name: 'userGroupId', visible: false},
        {data: 'userName', name: 'userName', class: 'evepraisalMode'},
        {data: 'miningDate', name: 'miningDate', class: 'evepraisalMode'},
        {data: 'regionName', name: 'regionName', class: 'evepraisalMode'},
        {data: 'compressedTypeName', name: 'compressedTypeName'},
        {data: 'compressed_quantity', name: 'compressed_quantity',render: $.fn.dataTable.render.number( ',', '.', 2 )},
        {data: 'compressedAveragePrice', name: 'compressedAveragePrice',render: $.fn.dataTable.render.number( ',', '.', 2 ), class: 'evepraisalMode'},
        {data: 'compressedAmounts', name: 'compressedAmounts',render: $.fn.dataTable.render.number( ',', '.', 3 ), class: 'evepraisalMode'},
        {data: 'compressedTax', name: 'compressedTax',render: $.fn.dataTable.render.number( ',', '.', 3 ), class: 'evepraisalMode'}
      ],
	  orderFixed:[0,'asc'],
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
                    .append( '<td>Sum for '+userNamesFormatted+'</td>' )
                    .append( '<td class="evepraisalMode"></td>' )
                    .append( '<td class="evepraisalMode"></td>' )
                    .append( '<td class="evepraisalMode"></td>' )
                    .append( '<td>'+ addCommas(compressedQuantitySum) +'</td>' )
                    .append( '<td class="evepraisalMode"></td>' )
                    .append( '<td id="compressedsum-'+group+'">'+ addCommas(compressedAmountsSum) +'</td>' )
                    .append( '<td id="compressedtaxsum-'+group+'">'+ addCommas(compressedTaxSum) +'</td>' )
                    .append( '</tr>' );
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
            $('#orePrices').append('<div class="input-group col-sm-3"><label for="ore-' + dataItem.compressedTypeId + '">' + dataItem.compressedTypeName + '</label><input class="form-control" type="number" step="0.01"  id="ore-' + dataItem.compressedTypeId + '"/></div>');
          }
        }
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
  <style type="text/css">
    #socialistmining-table_processing {
  top: 0;
  padding: 10px !important;
  z-index: 2147483647
}
  </script>
  @endpush
