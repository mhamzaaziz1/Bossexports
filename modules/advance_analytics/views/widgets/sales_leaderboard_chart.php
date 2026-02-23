<div class="panel_s">
    <div class="panel-body">
        <p class="bold">Sales Leaderboard (Revenue)</p>
        <div style="height:350px"><canvas id="sales_leaderboard"></canvas></div>
    </div>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function(event) {
        var salesData = <?php echo json_encode($widget_data); ?>;
        new Chart($('#sales_leaderboard'), {
            type: 'bar',
            data: salesData,
            options: { 
                indexAxis: 'y', // Horizontal
                responsive:true, 
                maintainAspectRatio:false,
                scales:{xAxes:[{ticks:{beginAtZero:true}}]} 
            }
        });
    });
</script>
