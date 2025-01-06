@extends('layouts.index')



{!! Html::style('public/css/calendar/fullcalendar.css') !!}


@section('content')

<style type="text/css">
	
	.tbl{
		color: black;
	}

    .fc-/* event-time, .fc-event-title {
    padding: 0 1px;
}

    .fc-event-time, .fc-event-title {
    padding: 0 1px;
    white-space: nowrap;
}

.fc-title {
    white-space: normal;
}
.fc-time-grid .fc-slats td {
  height: 1em;  /* Edit as required */
}
 */
</style>


	<div>
		<div class="page-title">
            <div class="title_left tbl">
              <h3>Palaysikatan</h3>
            </div>
        </div>
	

    

		<div class="row">
			<div class="col-md-12">
				@include('layouts.message')
				<div class="x_panel">
					<div class="x_title tbl">
						<h2>Palaysikatan Farmers</h2>
						<div class="clearfix">

							<button class="btn btn-success btn-sm col-md-1 pull-right" id="loadBtn" style="float: right;">Filter</button>
						                       
                              
						<div class="col-md-3 pull-right">                                                      
                            <select class="form-control" id="station" name="station">                            
                                <option value="All">All</option>

                                @foreach ($barangay as $station)
                                    <option value="{{ $station->barangay }}">{{ $station->barangay }}</option>
                                @endforeach>
                            </select>
                        </div>
                        <div class="col-md-3 pull-right" align="right">                                                      
                            <label for="station">Barangay</label>    
                            </div>
						</div>
					</div>
					<div class="x_content">
					

						<div class="row">
							<div class="col-md-12">
								<div class="response"></div>
								<div id='calendar' style="background-color:#FFFFFF !important;"></div>  
							</div>
						</div>


					</div>
				</div>
			</div>
		</div>
		



	</div>

<div class="se-pre-con"></div>
<div class="send-loading"></div>

@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js" integrity="sha256-4iQZ6BVL4qNKlQ27TExEhBN1HFPvAvAMbFavKKosSWQ=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.js"></script>
	<script>
	$(window).on('load', function() {
		// Animate loader off screen
		$(".se-pre-con").fadeOut("slow");
	});
	
	
        $('#station').on('change',function(){
            $('#calendar').fullCalendar('rerenderEvents');
        })
     
         var SITEURL = "{{url('/')}}";
         $.ajaxSetup({
           headers: {
               'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
           }
         });
  
         var calendar = $('#calendar').fullCalendar({eventLimit: true, // for all non-TimeGrid views
            views: {
                timeGrid: {
                eventLimit: 6 // adjust to 6 only for timeGridWeek/timeGridDay
                }
            },
            editable: true,
            events: SITEURL + "/palaysikatan.calendar",
            displayEventTime: true,
            editable: true,
            eventRender : function(event, element) {    
                 return ['All', event.barangay].indexOf($('#station').val()) >= 0           
            },
            
            selectable: true,
            allDaySlot: true,
            selectHelper: true,
            
            select: function (start, end, allDay) {
                calendar.fullCalendar('unselect');
            },
             
            events: SITEURL+ '/palaysikatan/calendar-data',
            /* eventColor: '#3e9cbf', */
            eventTextColor: '#FFFFFF', 
 
            eventDrop: function (event, delta) {
              
            },
            eventClick: function (event, jsEvent, view) {
                var farmerId=event.id;
                if (confirm('Are you sure you want to GO to encoding form?')) {
    
                location.href="form/planting/"+farmerId;
                } else {
 
                    console.log('Thing was not saved to the database.');
                }
               
            },
            eventMouseover: function (event, jsEvent, view) {  
                //console.log('event:'+event.start);
                //tooltip.hide();        
                tooltip = '<div class="tooltiptopicevent" style="width:auto;height:auto;background:#f7f5f5;position:absolute;z-index:10001;padding:10px 10px 10px 10px ;  line-height: 200%;">' + 'Activity : ' + event.activity + '</br>' + 'Address : '+ event.Address + '</br>' + 'Crop Stablishmen : '+ event.crop +  '</div>';

                $("body").append(tooltip);
                $(this).mouseover(function (e) {
                    $(this).css('z-index', 10000);
                    $('.tooltiptopicevent').fadeIn('500');
                    $('.tooltiptopicevent').fadeTo('10', 1.9);
                }).mousemove(function (e) {
                    $('.tooltiptopicevent').css('top', e.pageY + 10);
                    $('.tooltiptopicevent').css('left', e.pageX + 20);
                });

            },
            eventMouseout: function (data, event, view) {
                $(this).css('z-index', 8);
    
                $('.tooltiptopicevent').remove();
    
            },
            dayClick: function () {
                tooltip.hide()
            },
            eventResizeStart: function () {
                tooltip.hide()
            },
            eventDragStart: function () {
                tooltip.hide()
            },
            viewDisplay: function () {
                tooltip.hide()
            }
  
         });
       

	</script>
@endpush
