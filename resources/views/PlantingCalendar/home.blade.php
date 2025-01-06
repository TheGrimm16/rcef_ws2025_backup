<?php $qr_side = "active"; $qr_home="active"?>

@extends('layouts.index')

@section('styles')
  <link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
  <link rel="stylesheet" href="{{ asset('public/assets/iCheck/skins/flat/green.css') }}">
  <link rel="stylesheet" href="{{ asset('public/css/daterangepicker.css') }}">
  <style>

    .x_panel{

    }

    h4{
        font-weight: 700;
        margin: 0 0 0.4em 0;
        padding: 0;
    }

    select{
        border: 1px solid #888;
        border-radius: 0.6em;
        padding: 0.2em;
        font-size: 1.4em;
    }

    .selectors{
        display: flex;
        flex-wrap: wrap;
        gap: 1em;        
    }

    .submit{
        width: max-content;
        height: 30px;
        margin: 1px;
    }

    .group_selector{
        display: flex;
        gap: 20%;
    }

    .plantingCalendar_stats
    {
        margin 30px;
    }

    .blocker{
        color: silver;
        display: grid;
        place-items: center;
        position: absolute;
        background: #00000060;
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;
        font-size: 2em;
    }

    .blocker2{
        color: silver;
        display: grid;
        place-items: center;
        position: absolute;
        background: #00000060;
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;
        font-size: 2em;
    }

  </style>
@endsection

@section('content')

    <div class="clearfix"></div>

    @include('layouts.message')

    <div class="row">
        <div class="col-md-12">
            <div class="x_panel">
                <div class="x_title">
                    <h1>
                    Planting Calendar & Seed Variety Performance Report
                    </h1>
                </div>
                <section class="group_selector">
                <div class="selectors" style="display: flex; flex-wrap: wrap; gap: 10px;">
                    <div class="regions_container">
                        <h4>Region:</h4>
                        <select class="form-select" name="region" id="region">
                            <option value="default">All Regions</option>
                            @foreach(array_combine($regionNames, $regionCodes) as $name => $code)
                                <option value="{{$code}}">{{$name}}</option>
                            @endforeach
                        </select>
                    </div>
                    
                <div class="provinces_container">
                    <h4>Province:</h4>
                        <select name="provinces" id="provinces">
                            <option value="default">All Provinces</option>
                        </select>
                </div>

                    <div class="municipality_container">
                    <h4>Municipality:</h4>
                    <select name="municipality" id="municipality">
                        <option value="default">All Municipalities</option>
                    </select>
                    </div>

                    <div class="season_container">
                    <h4>Season:</h4>
                    <select name="season" id="season">
                    @foreach($seasons as $row)
                                <option value="{{$row->season_code}}">{{$row->season.' '.$row->season_year}}</option>
                            @endforeach
                    </select>
                    </div>

                    <div class="variety_container">
                    <h4>Seed Variety:</h4>
                    <select name="variety" id="variety">
                        <option value="default">Select Seed Variety</option>
                        @foreach($variety as $row)
                                <option value="{{$row->variety}}">{{$row->variety}}</option>
                            @endforeach                        
                    </select>
                    </div>

                    <button type="button" id='submit' class="btn btn-success submit" style="height: 30px; align-self: flex-end;">Submit</button>
                    <button type="button" id="reset" class="btn btn-secondary" style="height: 30px; align-self: flex-end;">Reset</button>
                </div>

                </section>
                
            </div>

            <div style="position: relative; overflow: hidden;">
            <div class="plantingCalendar_stats" id="plantingCalendar_stats" style=" width:100%; height:500px;"></div>
            <!-- <div class="blocker2" id="blocker2">No Data</div> -->
            </div>

            <div style="position: relative">

                <div class="seedVariety_stats" id="seedVariety_stats" style="margin: 100px 0; width:100%; height:500px;"></div>
                <!-- <div class="blocker" id="blocker">No Data For This Variety</div> -->
            </div>
        </div>
    </div>
    



@endsection()

@push('scripts')
    <script src=" {{ asset('public/js/jquery.inputmask.bundle.js') }} "></script>
    <script src=" {{ asset('public/js/select2.min.js') }} "></script>
    <script src=" {{ asset('public/js/parsely.js') }} "></script>
    <script src=" {{ asset('public/assets/iCheck/icheck.min.js') }} "></script>
    <script src=" {{ asset('public/js/daterangepicker.js') }} "></script>
    <!-- <script src=" {{ asset('public/js/highcharts.js') }} "></script> -->
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <script src="https://code.highcharts.com/modules/offline-exporting.js"></script>
    <script src="https://code.highcharts.com/modules/export-data.js"></script>

    
    <script>
        
        const genRanHex = size => [...Array(size)].map(() => Math.floor(Math.random() * 16).toString(16)).join('');
        load_plantingCalendar_chart();
        load_seedVariety_chart();

        $('#region').select2();
        $('#provinces').select2();
        $('#municipality').select2();
        $('#season').select2();
        $('#variety').select2();
        
        $('#region').on('change', () => {
            $reg = $('#region').val();
            $ssn = $('#season').val();
            $.ajax({ 
                type: 'POST',
                url: "{{ route('get_provinces') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    reg: $reg,
                    ssn: $ssn
                },
                success: function(data){
                    $processed = JSON.parse(data);
                    $('#provinces option:gt(0)').remove();
                    $('#municipality option:gt(0)').remove();
                    for(i = 0; i < $processed.length; i++){
                        $("#provinces").append('<option value="' + $processed[i].prv_code + '">' + $processed[i].province + '</option>');
                    }
                    
                }
            });
        });

        $('#provinces').change(() => {
            $prov = $('#provinces').val();
            $ssn = $('#season').val();
            $.ajax({ 
                type: 'POST',
                url: "{{ route('get_Municipalities') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    prov: $prov,
                    ssn: $ssn
                },
                success: function(data){
                    $processedMun = JSON.parse(data);
                    $('#municipality option:gt(0)').remove();
                    for(i = 0; i < $processedMun.length; i++){
                        $("#municipality").append('<option value="' + $processedMun[i].muncode + '">' + $processedMun[i].municipality + '</option>');
                    }
                }
            });


        });

        window.onload = () => {
            setTimeout(function() {
                document.getElementById("submit").click();
                }, 1);
            
            };

            


        // $('#municipality').change(() => {
        //     if($('#municipality').val() == "default"){
        //         $('#submit').attr("disabled", "true");
        //     }else{
        //         $('#submit').removeAttr("disabled");
        //     }
        // });



        $('#submit').on('click', () =>{
            $reg = $('#region').val();
            $prv = $('#provinces').val();
            $mun = $('#municipality').val();
            $ssn = $('#season').val();
            $vty = $('#variety').val();


            $.ajax({ 
                type: 'POST',
                url: "{{ route('get_plantingWeek') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    reg: $reg,
                    prv: $prv,
                    mun: $mun,
                    ssn: $ssn,
                },
                success: function(data){
                    $proc = JSON.parse(data);
                    var index = 0;
                    var weeks = [];
                    var farmers = [];
                    if($proc.length < 1){
                        // $('#blocker2').css('display', "grid");
                        $('#plantingCalendar_stats').highcharts().destroy();
                        return;
                    }
                    
                    $.each($proc, function(){
                        weeks.push($proc[index].planting_week);
                        farmers.push($proc[index].farmers);
                        index++;
                    });
                    // if(farmers.length > 0) $('#blocker2').css('display', "none");
                    $('#plantingCalendar_stats').css('display', "block");
                    load_plantingCalendar_chart(weeks, farmers);
                }
            });

            $.ajax({ 
                type: 'POST',
                url: "{{ route('get_VarietyYield') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    reg: $reg,
                    prv: $prv,
                    mun: $mun,
                    ssn: $ssn,
                    vty: $vty
                },
                success: function(data){
                    $proc = JSON.parse(data);
                    var index = 0;
                    var weeks = [];
                    var vty_yield = [];
                    if($proc.length < 1){
                        $('#seedVariety_stats').highcharts().destroy();
                        return;
                        // $('#blocker').css('display', "grid");
                    }
                    
                    $.each($proc, function(){
                        weeks.push($proc[index].planting_week);
                        vty_yield.push($proc[index].vty_yield);
                        index++;
                    });
                     
                    // if(vty_yield.length > 0) $('#blocker').css('display', "none");
                    $('#seedVariety_stats').css('display', "block");
                    load_seedVariety_chart(weeks, vty_yield);
                }
            });

        });

        function load_plantingCalendar_chart(week, farmers){
            $('#plantingCalendar_stats').highcharts({
                chart: {
                    type: 'column',
                    backgroundColor:'#F7F7F7', 
                },
                title:{
                    text:'Planting Calendar'
                },
                xAxis: {
                    categories: week
                },
                yAxis: {
                    title: {
                        text: 'Farmers'
                    }
                },
                tooltip: {
                    formatter: function() {
                        var number = this.y;
                        if (number >= 1000) {
                            number = number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                        }
                        return '<span style="font-size: 14px; color: #333333;">Number of farmers:</span><br><span style="font-size: 18px; color: #0070C0; font-weight: bold;">' + number + '</span>';
                    }
                },
                exporting: {
                    enabled: true,
                    buttons: {
                        contextButton: {
                            menuItems: [
                                'downloadPNG',
                                'downloadJPEG',
                                'downloadPDF',
                                'downloadCSV',
                                'downloadXLS'
                            ],
                            text: 'Download'
                        }
                    }
                },
                series: [{
                    name: 'Planting Week',
                    data: farmers,
                    color: "#"+genRanHex(6)+"80"
                }],
            });
        }




        function load_seedVariety_chart(week, vty_yield){
            var season = $('#season').val();
            var variety = $('#variety').val();

            if (season && (season.includes('ds') || season.includes('ws'))) {
            var year = season.slice(-4); // Get the year from the season value
            season = (season.includes('ds')) ? 'Dry Season ' + year : 'Wet Season ' + year;
    }

            $('#seedVariety_stats').highcharts({
                chart: {
                        type: 'column',
                        backgroundColor:'#F7F7F7', 
                    },
                    title:{
                        text:(season && variety && season !== 'default' && variety !== 'default') ? 'Seed Variety Performance Report ' + season + ' ' + variety : 'Seed Variety Performance Report'
                    },
                    xAxis: {
                        categories: week
                    },
                    yAxis: {
                        title: {
                            text: 'Yield in T/ha'
                        }
                    },
                    tooltip: {
                        formatter: function() {
                            return '<span style="font-size: 14px; color: #333333;">Yield</span><br><span style="font-size: 18px; color: #000000; font-weight: bold;">' + this.y + ' T/ha</span>';
                        }
                    },
                    exporting: {
                        enabled: true,
                        buttons: {
                            contextButton: {
                                menuItems: [
                                    'downloadPNG',
                                    'downloadJPEG',
                                    'downloadPDF',
                                    'downloadCSV',
                                    'downloadXLS'
                                ],
                                text: 'Download'
                            }
                        }
                    },
                    series: [{
                        name: 'Planting Week',
                        data: vty_yield,
                        color: "#"+genRanHex(6)+"80"
                    }],
            });

        }

        $("#download_btn").on("click", function(e){
            var reg = $('#region').val();
            var prv = $('#provinces').val();
            var mun = $('#municipality').val();
            var ssn = $('#season').val();
            var vty = $('#variety').val();
            
           /*  */
           window.open('../PlantingCalendar/api/exportPlantingCalendar/'+reg+'/'+prv+'/'+mun+'/'+ssn+'/'+vty);

        });

        $("#download_btn2").on("click", function(e){
            var reg = $('#region').val();
            var prv = $('#provinces').val();
            var mun = $('#municipality').val();
            var ssn = $('#season').val();
            var vty = $('#variety').val(); 
            
           /*  */
           window.open('../PlantingCalendar/api/exportVarietyYield/'+reg+'/'+prv+'/'+mun+'/'+ssn+'/'+vty);

        });

        function resetDropdowns() {
        $('#region').val('default').trigger('change');
        $('#provinces').val('default').trigger('change');
        $('#municipality').val('default').trigger('change');
        $('#season').val($('#season option:first-child').val()).trigger('change');
        $('#variety').val('default').trigger('change');
        }


    
        // Attach the reset function to the reset button
        document.getElementById("reset").addEventListener("click", resetDropdowns);
    </script>
@endpush