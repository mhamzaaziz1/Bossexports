<div class="panel_s">
    <div class="panel-body">
        <p class="bold">Cohort Analysis (Retention Heatmap)</p>
        <div class="table-responsive">
            <table class="table table-bordered table-condensed text-center font-medium-xs">
                <thead><tr style="background:#f3f4f6;"><th class="text-left">Cohort</th><th>Size</th><?php for($i=0; $i<12; $i++){ echo "<th>M{$i}</th>"; } ?></tr></thead>
                <tbody>
                    <?php if(is_array($widget_data) || is_object($widget_data)) { foreach($widget_data as $row){ ?>
                    <tr>
                        <td class="text-left bold"><?php echo $row['month']; ?></td>
                        <td class="text-muted"><?php echo $row['size']; ?></td>
                        <?php 
                        for($i=0; $i<12; $i++){
                            $pct = isset($row['data'][$i]) ? $row['data'][$i] : '';
                            $bg = '';
                            if($pct !== '') {
                                if($pct >= 80) $bg = '#d1fae5'; elseif($pct >= 50) $bg = '#fae8ff'; elseif($pct >= 20) $bg = '#fff7ed'; elseif($pct > 0) $bg = '#fff1f2';
                                echo "<td style='background:{$bg}'>{$pct}%</td>";
                            } else { echo "<td></td>"; }
                        }
                        ?>
                    </tr>
                    <?php } } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
