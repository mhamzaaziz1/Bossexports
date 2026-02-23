<div class="panel_s">
    <div class="panel-body">
        <p class="bold">Cost vs Revenue vs Profit</p>
        <div style="height:300px"><canvas id="profit_trend"></canvas></div>
    </div>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function(event) {
         var profitJSON = <?php echo json_encode($widget_data); ?>;
         new Chart($('#profit_trend'), {
            type: 'bar',
            data: profitJSON,
            options: { responsive:true, maintainAspectRatio:false, scales: {yAxes:[{ticks:{beginAtZero:true}}]} }
        });
    });
</script>
