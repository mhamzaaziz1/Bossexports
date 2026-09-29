<div class="panel_s">
    <div class="panel-body">
        <p class="bold">Top Staff by Logged Hours (Tasks)</p>
        <div style="height:350px"><canvas id="staff_workload"></canvas></div>
    </div>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function(event) {
        var workloadData = <?php echo json_encode($widget_data); ?>;
        new Chart($('#staff_workload'), {
            type: 'bar',
            data: workloadData,
            options: { 
                responsive:true, 
                maintainAspectRatio:false,
                scales:{yAxes:[{ticks:{beginAtZero:true}}]} 
            }
        });
    });
</script>
