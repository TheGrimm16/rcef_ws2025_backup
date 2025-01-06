<thead>
    <tr>
        <th scope="col" class="text-left">Cooperative Name</th>

        @foreach ($week as $k => $w)
            <th scope="col">
                @if ($k == 'w1')
                    First
                @elseif ($k == 'w2')
                    Second
                @elseif ($k == 'w3')
                    Third
                @elseif ($k == 'w4')
                    Fourth
                @elseif ($k == 'w5')
                    Fifth
                @elseif ($k == 'w6')
                    Sixth
                @endif
                Week (bag/s)
            </th>
        @endforeach

    </tr>
    <tr>
        <th scope="col"></th>
        @foreach ($week as $w)
            <th scope="col" style="height: 40px !important">{{ date('M d, Y', strtotime($w['start'])) }} -
                {{ date('M d, Y', strtotime($w['end'])) }}</th>
        @endforeach
    </tr>
</thead>
<tbody>
    @foreach ($data as $d)
        <tr class="">

            <td scope="row" class="text-left">{{ $d['name'] }}</td>
            @foreach ($week as $k => $w)
                <td>
                    @if (array_key_exists($k, $d))
                        <div class="main-pb">
                            <div class="submain-pb">
                                @if ($d[$k]['data']['delivered_per'] > 0)
                                    <div class="delivered-pb" style="width:{{ @$d[$k]['data']['delivered_per'] }}%">
                                        <span
                                            style="color: #28a745;position:relative;top: 25px">{{ @$d[$k]['data']['delivered'] }}</span>
                                    </div>
                                @endif
                                @if ($d[$k]['data']['scheduled_per'] > 0)
                                    <div class="scheduled-pb" style="width:{{ @$d[$k]['data']['scheduled_per'] }}%">
                                        <span
                                            style="color: #ffc107;position:relative;top: 25px">{{ @$d[$k]['data']['scheduled'] }}</span>
                                    </div>
                                @endif
                                @if ($d[$k]['data']['cancelled_per'] > 0)
                                    <div class="dangered-pb" style="width:{{ @$d[$k]['data']['cancelled_per'] }}%">
                                        <span
                                            style="color: #dc3545;position:relative;top: 25px">{{ @$d[$k]['data']['cancelled'] }}</span>
                                    </div>
                                @endif

                            </div>
                        </div>

                        <div class="" style="position:relative;top: 25px;">Total Scheduled:
                            {{ @$d[$k]['data']['total'] }}</div>
                    @endif
                </td>
            @endforeach
        </tr>
    @endforeach
</tbody>
