<div class="panel_s">
    <div class="panel-body">
        <p class="bold">Top 10 Selling Items (Revenue)</p>
        <div style="height:350px"><canvas id="items_leaderboard"></canvas></div>
    </div>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function(event) {
        var itemsData = <?php echo json_encode($widget_data); ?>;
        new Chart($('#items_leaderboard'), {
            type: 'bar',
            data: itemsData,
            options: { 
                responsive:true, 
                maintainAspectRatio:false,
                scales:{yAxes:[{ticks:{beginAtZero:true}}]} 
            }
        });
    });
</script>
