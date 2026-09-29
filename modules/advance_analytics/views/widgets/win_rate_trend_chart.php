<div class="panel_s">
    <div class="panel-body">
        <p class="bold">Win Rate Trend</p>
        <div style="height:300px"><canvas id="win_rate_trend"></canvas></div>
    </div>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function(event) {
        var winData = <?php echo json_encode($widget_data); ?>;
        new Chart($('#win_rate_trend'), {
            type: 'line',
            data: winData,
            options: { 
                responsive:true, 
                maintainAspectRatio:false,
                scales:{yAxes:[{ticks:{beginAtZero:true, max: 100}}]} 
            }
        });
    });
</script>
