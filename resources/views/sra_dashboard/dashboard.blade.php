@extends('layouts.index')

@section('content')
<style>
    .title_count{
        height: 70px;
    }
</style>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-4">
            <div class="x_panel">
                <div class="x_title">
                    <h2>TOTAL FARMERS (DISTRIBUTED)</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal form-label-left" style="padding-bottom: 0;padding-left: 0;">
                    <div class="row tile_count" style="margin: 0">
                        <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0 !important;padding-top: 20px !important;padding-left: 20px !important;height:90px;line-height:40px">
                            <div class="count" style="font-size:5vh">{{$total_count}} <span style="font-size:1.5vh; font-weight:normal"><i>{{$percentage}}% of total farmer beneficiaries</i></span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="x_panel">
                <div class="x_title">
                    <h2>TOTAL AREA & BAGS (DISTRIBUTED)</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal form-label-left" style="padding-bottom: 0;padding-left: 0;">
                    <div class="row tile_count" style="margin: 0">
                        <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0 !important;padding-left: 20px !important;height:90px">
                            <div class="count" style="font-size:24px">{{$total_area}} ha</div>
                            <div class="count" style="font-size:24px">{{$total_bags}} bags</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="x_panel">
                <div class="x_title">
                    <h2>MALE,FEMALE RATIO (DISTRIBUTED)</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content form-horizontal form-label-left" style="padding-bottom: 0;padding-left: 0;">
                    <div class="row tile_count" style="margin: 0">
                        <div class="col-md-12 col-sm-12 col-xs-12 tile_stats_count" style="padding-bottom: 0 !important;padding-left: 20px !important;height:90px">
                            <div class="count" style="font-size:24px">Male = {{$male}}</div>
                            <div class="count" style="font-size:24px">Female = {{$female}}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <center>
                <div class="x_content form-horizontal form-label-left">
                    <div id="graph_container" style="width: auto; border-style: solid; " >
                        <div class="">Loading.........</div>
                    </div>
                </div>
            </center>
         </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src=" {{ asset('public/js/highchart/highcharts.js') }} "></script>
    <script src=" {{ asset('public/js/highchart/modules/drilldown.js') }} "></script>
	<script>

function graphTwoLoad(){
    
    $.ajax({
                type: 'POST',
                url: "{{ route('paymaya.graph.dashboard') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                },
                success: function(data){
                  chart(data);
                }
            });
}


      graphTwoLoad();


function chart(data){
        var province = [];
        var delivered =[];
        var confirmed =[];
        var claimed = [];
        var series_data =[];

        var municipality = [];
        var m_series_data =[];
        

        var mun;
        for(var x=0; x<data.length; x++){
            
            var m_delivered = [];
            var m_confirmed = [];
            var m_claimed = [];
            
            var p = data[x]["province"];
            province.push(p);
            var pid_d = p.replace(/\W/g,'_')+"_delivered";
            var pid_c = p.replace(/\W/g,'_')+"_confirmed";
            var pid_cl = p.replace(/\W/g,'_')+"_claimed";
            delivered.push({name: p, y: data[x]["delivered"], drilldown: pid_d});
            confirmed.push({name: p, y: data[x]["confirmed"], drilldown: pid_c});
            claimed.push({name: p, y: data[x]["claimed"], drilldown: pid_cl});
            
            
            mun = data[x]['mun_data'];
            for(var i=0; i< mun.length; i++){
                
                var m = mun[i]["municipality"];
                municipality.push(m);
                m_delivered.push([m, mun[i]["delivered"]]);
                m_confirmed.push([m, mun[i]["confirmed"]]);
                m_claimed.push([m, mun[i]["claimed"]]);
            }

            m_series_data.push({name: "Delivered", data: m_delivered, id: pid_d});
            m_series_data.push({name: "Confirmed", data: m_confirmed, id: pid_c});
            m_series_data.push({name: "Claimed", data: m_claimed, id: pid_cl });

        }
        series_data.push({name: "Delivered", data: delivered, type: 'column'});
        series_data.push({name: "Confirmed", data: confirmed, type: 'column'});
        series_data.push({name: "Claimed", data: claimed, type: 'column' });

        
        
           console.log(m_series_data);
           console.log(series_data);

            Highcharts.chart('graph_container', {
            chart: {
                type: 'column'
            },
            title: {
                text: 'Paymaya Delivery Statistics'
            },
            subtitle: {
                text: ''
            },
            accessibility: {
                announceNewData: {
                    enabled: true
                }
            },
            xAxis: {
                type: 'category',
                crosshair: true
            },
            yAxis: {
                title: {
                    text: 'Bags (Count)'
                }
            },

            

            legend: {
                layout: 'vertical',
                align: 'left',
                x: 100,
                verticalAlign: 'top',
                y: 50,
                floating: true,
                backgroundColor:
                    Highcharts.defaultOptions.legend.backgroundColor || 
                    'rgba(255,255,255,0.25)'
            },
            series: series_data,
            drilldown: {
                allowPointDrilldown: false,
                series: m_series_data
            }
        }); 
    }

	</script>
@endpush
