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
	<input type="text" id="corp_id" value="1579752358"/>
	<a href="#" data-submit="load">load</a>
	</div>
    <div class="nav-tabs-custom">
      <ul class="nav nav-tabs">
        <li class="active"><a href="#" data-toggle="tab" data-all="true">{{ trans_choice('web::seat.character', 2) }}</a></li>
        @if(auth()->user()->has('character.list', false))
          <li><a href="#" data-toggle="tab" data-all="false">SeAT Group only</a></li>
        @endif
      </ul>
      <div class="tab-content">
          <table class="table compact table-condensed table-hover table-responsive"
                 id="socialistmining-table" data-page-length=100>
            <thead>
            <tr>
              <th>character_id</th>
              <th>typeName</th>
              <th>miningDate</th>
              <th>quantity</th>
              <th>compressed_quantity</th>
              <th>amounts</th>
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

  <script>
    $('a[data-submit="load"]').on('click', function () {
      character_list.draw();
    });
    //function filtered() {
      //return !$("div.nav-tabs-custom > ul > li.active > a").data('all');
    //}
    var character_list = $('table#socialistmining-table').DataTable({
      processing      : true,
      serverSide      : true,
      ajax            : {
        url : '{{ route('socialistmining.corp.ledger') }}',
        data: function ( d ) {
		d.$corporation_id = $('#corp_id').val();
		d.$year = JSON.stringify(2019);
		d.$month = JSON.stringify(4);
		d.$get = true;
      }},
      columns         : [
        {data: 'character_id', name: 'character_id'},
        {data: 'typeName', name: 'typeName'},
        {data: 'miningDate', name: 'miningDate'},
        {data: 'quantity', name: 'quantity'},
        {data: 'compressed_quantity', name: 'compressed_quantity'},
        {data: 'amounts', name: 'amounts'}
      ],
	  orderFixed:[0,'asc'],
	  rowGroup: {
            startRender: function ( rows, group ) {
                var amountsSum = rows
                    .data()
                    .pluck('amounts')
                    .reduce( function (a, b) {
                        return (Number(!a?0:a) + Number(!b?0:b)).toFixed(2);
                    }, 0);
                //amountsAvg = $.fn.dataTable.render.number(',', '.', 0, '$').display( amountsAvg );
 
 
                return $('<tr/>')
                    .append( '<td colspan="5">Sum for '+group+'</td>' )
                    .append( '<td>'+ addCommas(amountsSum) +'</td>' )
                    .append( '<td/>' );
            },
            dataSrc: 'character_id'},
      drawCallback: function () {
        $("img").unveil(100);
        ids_to_names();
      },
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
