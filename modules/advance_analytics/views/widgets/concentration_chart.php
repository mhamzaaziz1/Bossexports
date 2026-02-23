<div class="panel_s">
    <div class="panel-body">
        <p class="bold">Revenue Concentration (Risk)</p>
        <div style="height:300px"><canvas id="concentration_chart"></canvas></div>
    </div>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function(event) {
        var concJSON = <?php echo json_encode($widget_data); ?>;
        new Chart($('#concentration_chart'), {
            type: 'doughnut',
            data: concJSON,
            options: { responsive:true, maintainAspectRatio:false }
        });
    });
</script>
