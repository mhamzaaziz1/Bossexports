<div class="panel_s">
    <div class="panel-body">
        <p class="bold">Leads Monthly Conversions</p>
        <div style="height:300px"><canvas id="leads_monthly"></canvas></div>
    </div>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function(event) {
        var leadsData = <?php echo json_encode($widget_data); ?>;
        new Chart($('#leads_monthly'), {
            type: 'line',
            data: leadsData,
            options: { responsive:true, maintainAspectRatio:false, scales:{yAxes:[{ticks:{beginAtZero:true}}]} }
        });
    });
</script>
