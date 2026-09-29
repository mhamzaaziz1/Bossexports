<div class="panel_s">
    <div class="panel-body">
        <p class="bold">ABC Analysis (Pareto)</p>
            <div class="row">
                <div class="col-md-5"><div style="height:250px"><canvas id="abc_chart"></canvas></div></div>
                <div class="col-md-7">
                    <table class="table table-condensed text-xs">
                        <thead><tr><th>Class</th><th>Items</th><th>Examples</th></tr></thead>
                        <tbody>
                            <tr><td class="text-success bold">A</td><td><?php echo $widget_data['classes']['A']; ?></td><td><?php echo implode(', ', array_slice($widget_data['details']['A'], 0, 1)); ?></td></tr>
                            <tr><td class="text-warning bold">B</td><td><?php echo $widget_data['classes']['B']; ?></td><td><?php echo implode(', ', array_slice($widget_data['details']['B'], 0, 1)); ?></td></tr>
                            <tr><td class="text-danger bold">C</td><td><?php echo $widget_data['classes']['C']; ?></td><td><?php echo implode(', ', array_slice($widget_data['details']['C'], 0, 1)); ?></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
    </div>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function(event) {
        var abcData = <?php echo json_encode(array_values($widget_data['classes'])); ?>;
        new Chart($('#abc_chart'), {
            type: 'doughnut',
            data: { labels: ['A', 'B', 'C'], datasets: [{ data: abcData, backgroundColor: ['#10b981', '#f59e0b', '#ef4444'] }] },
            options: { responsive:true, maintainAspectRatio:false, legend:{position:'right'} }
        });
    });
</script>
