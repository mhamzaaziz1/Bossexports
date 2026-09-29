<div class="panel_s">
    <div class="panel-body">
        <p class="bold">Project Status Breakdown</p>
        <div style="height:350px"><canvas id="project_status"></canvas></div>
    </div>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function(event) {
        var projData = <?php echo json_encode($widget_data); ?>;
        new Chart($('#project_status'), {
            type: 'doughnut',
            data: projData,
            options: { 
                responsive:true, 
                maintainAspectRatio:false,
                plugins: {
                    legend: { position: 'right' }
                }
            }
        });
    });
</script>
