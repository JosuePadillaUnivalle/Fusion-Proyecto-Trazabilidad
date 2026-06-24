@props(['kpis' => []])



<div class="rpt-kpis">

    @foreach($kpis as $kpi)

        <div class="rpt-kpi">

            <div class="rpt-kpi__val">{{ $kpi['value'] }}</div>

            <div class="rpt-kpi__lbl">{{ $kpi['label'] }}</div>

        </div>

    @endforeach

</div>


