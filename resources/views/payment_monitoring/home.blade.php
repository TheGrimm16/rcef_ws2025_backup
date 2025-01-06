@extends('layouts.index')

@section('styles')
    <style>
        .shadow-sm	{box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);}
        .shadow	{box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);}
        .shadow-md	{box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);}
        .shadow-lg	{box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);}
        .shadow-xl	{box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);}
        .shadow-2xl	{box-shadow: 0 25px 50px -12px rgb(0 0 0 / 0.25);}
        .shadow-inner	{box-shadow: inset 0 2px 4px 0 rgb(0 0 0 / 0.05);}
        .shadow-none	{box-shadow: 0 0 #0000;}

        .btn-success.disabled, .btn-success[disabled]{
            background-color: #5cb85c;
            border-color: #4cae4c;
        }

        .datepicker{
            display:flex;
            align-items: center;
            justify-content: space-between;
        }
    </style>
@endsection

@section('content')
{{-- CSRF TOKEN --}}
<input type="hidden" name="_token" value="{{ csrf_token() }}">

<div class="row">
    <div class="col-md-12">
        <div class="x_panel">
            <div class="x_title">
                <h1>Stagerred Seed Delivery & Payment Monitoring</h1>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <div>
                    <div class='datepicker'>
                        <h2>Daily Transactions</h2>
                        <div>
                            <input type="text" id="date1" /><button
                                type="button"
                                id="submitDate"
                                class="btn btn-success submit">
                                Submit
                            </button>
                        </div>
                    </div>
                    
                    <table
                    class="table table-hover table-striped table-bordered"
                    id="tbl_monitoring"
                    style="background-color: white">            
                        <thead style="background-color: white">
                            <th>Batch Ticket Number</th>
                            <th>IAR Number</th>
                            <th>Region</th>
                            <th>Province</th>
                            <th>Municipality</th>
                            <th>Cooperative Name</th>
                            <th>Number of Bags</th>
                            <th>Date Inspected</th>
                            <th>Status of Payment</th>
                        </thead>
                        <tbody>

                            <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            </tr>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection

@push('scripts')

<script>
    $("#date1").daterangepicker({
    startDate: moment().startOf('month'),
    endDate: moment().endOf('month')
    });

    

    window.onload = function() {
        $('#tbl_monitoring').DataTable();
        $("#tbl_monitoring").DataTable({
      bDestroy: true,
      autoWidth: false,
      searchHighlight: true,
      processing: true,
      serverSide: true,
      orderMulti: true,
      order: [],
      ajax: {
        url: "{{ route('getInitialData') }}",
        dataType: "json",
        type: "POST",
        data: {
          _token: "{{ csrf_token() }}",
        },
      },
      columns: [
        { data: "iar_no" },
        { data: "batchTicketNumber" },
        { data: "region" },
        { data: "province" },
        { data: "municipality" },
        { data: "coopName" },
        { data: "bags" },
        { data: "dateInspected" },
        { data: "paymentStatus" },
      ],
    });
};

</script>
@endpush()
