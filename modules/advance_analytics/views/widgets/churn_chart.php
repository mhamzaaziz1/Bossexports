<div class="panel_s">
    <div class="panel-body">
        <p class="bold">Churn Risk Prediction</p>
        <div style="height:350px"><canvas id="churn_chart"></canvas></div>
    </div>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function(event) {
        var churnRaw = <?php echo json_encode($widget_data); ?>;
        new Chart($('#churn_chart'), {
            type: 'doughnut',
            data: { labels: ['High Risk', 'Medium Risk', 'Safe'], datasets: [{ data: [churnRaw.high_risk, churnRaw.medium_risk, churnRaw.safe], backgroundColor: ['#ef4444', '#f59e0b', '#10b981'] }] },
            options: { responsive:true, maintainAspectRatio:false, legend:{position:'bottom'} }
        });
    });
</script>
