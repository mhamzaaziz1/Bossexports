<div class="panel_s">
    <div class="panel-body">
        <p class="bold">Proposal Conversion Funnel</p>
        <div style="height:350px"><canvas id="pipeline_funnel"></canvas></div>
    </div>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function(event) {
        var funnelData = <?php echo json_encode($widget_data); ?>;
        new Chart($('#pipeline_funnel'), {
            type: 'bar',
            data: funnelData,
            options: { 
                indexAxis: 'y', // Horizontal Bar Chart for Funnel effect
                responsive:true, 
                maintainAspectRatio:false,
                plugins: {
                    legend: { display: false }
                }
            }
        });
    });
</script>
