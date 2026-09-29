/* Chart.js load guard — wait for Chart.js if not yet ready */
if (typeof Chart === 'undefined') {
  console.warn('tc_main.js: Chart.js not loaded yet. Charts will not render.');
}
/**
 * tc_main.js — Premium dashboard charts & interactions
 * Requires: Chart.js 4.x (loaded via CDN in controller)
 * ─────────────────────────────────────────────────────
 * HOW TO ADD A NEW CHART:
 *   1. Add <canvas id="chart-NAME"> in your view
 *   2. Call tcChart.bar/doughnut/line/area below
 *   3. Pass PHP data: var MY_DATA = <?=json_encode($data)?>;
 */

/* ── Global palette ── */
var TC_PALETTE = {
  blue:   '#1a56db', teal:  '#0694a2', green: '#057a55',
  orange: '#d03801', red:   '#c81e1e', purple:'#6c2bd9',
  indigo: '#3730a3', pink:  '#bf125d', yellow:'#c27803',
  blueFade:   'rgba(26,86,219,.12)',
  tealFade:   'rgba(6,148,162,.12)',
  greenFade:  'rgba(5,122,85,.12)',
  orangeFade: 'rgba(208,56,1,.12)',
  purpleFade: 'rgba(108,43,217,.12)',
};

var TC_GRADIENTS = {};

function tcGradient(ctx, color, alpha1, alpha2) {
  var key = color+alpha1+alpha2;
  if (TC_GRADIENTS[key]) return TC_GRADIENTS[key];
  var g = ctx.createLinearGradient(0,0,0,300);
  g.addColorStop(0, color.replace(')',','+alpha1+')').replace('rgb','rgba'));
  g.addColorStop(1, color.replace(')',','+alpha2+')').replace('rgb','rgba'));
  TC_GRADIENTS[key] = g;
  return g;
}

function hexToRgb(hex, a) {
  var r = parseInt(hex.slice(1,3),16), g = parseInt(hex.slice(3,5),16), b = parseInt(hex.slice(5,7),16);
  return a !== undefined ? 'rgba('+r+','+g+','+b+','+a+')' : 'rgb('+r+','+g+','+b+')';
}

/* ── Chart defaults ── */
Chart.defaults.font.family = '-apple-system,BlinkMacSystemFont,"Segoe UI",Arial,sans-serif';
Chart.defaults.font.size   = 12;
Chart.defaults.color       = '#6b7280';

/* ── Chart factory ── */
var tcChart = {

  bar: function(id, labels, datasets, opts) {
    var el = document.getElementById(id); if (!el) return;
    return new Chart(el, {
      type: 'bar',
      data: { labels:labels, datasets:datasets },
      options: Object.assign({
        responsive:true, maintainAspectRatio:true,
        plugins:{ legend:{ position:'bottom', labels:{ boxWidth:10, padding:16, font:{size:11} } }, tooltip:{ mode:'index', intersect:false, backgroundColor:'#1f2937', titleColor:'#f9fafb', bodyColor:'#d1d5db', cornerRadius:8, padding:10 } },
        scales:{ x:{ grid:{ display:false }, ticks:{ font:{size:11} } }, y:{ beginAtZero:true, grid:{ color:'#f3f4f6' }, ticks:{ font:{size:11} } } }
      }, opts||{})
    });
  },

  area: function(id, labels, datasets, opts) {
    var el = document.getElementById(id); if (!el) return;
    datasets.forEach(function(ds) {
      ds.fill = true; ds.tension = 0.4; ds.pointRadius = 3; ds.pointHoverRadius = 5; ds.pointBackgroundColor = ds.borderColor;
    });
    return new Chart(el, {
      type: 'line',
      data: { labels:labels, datasets:datasets },
      options: Object.assign({
        responsive:true, maintainAspectRatio:true,
        plugins:{ legend:{ position:'bottom', labels:{ boxWidth:10, padding:16, font:{size:11} } }, tooltip:{ mode:'index', intersect:false, backgroundColor:'#1f2937', titleColor:'#f9fafb', bodyColor:'#d1d5db', cornerRadius:8, padding:10 } },
        scales:{ x:{ grid:{ display:false }, ticks:{ font:{size:11} } }, y:{ beginAtZero:true, grid:{ color:'#f3f4f6' }, ticks:{ font:{size:11} } } }
      }, opts||{})
    });
  },

  doughnut: function(id, labels, data, colors, opts) {
    var el = document.getElementById(id); if (!el) return;
    return new Chart(el, {
      type: 'doughnut',
      data: { labels:labels, datasets:[{ data:data, backgroundColor:colors||Object.values(TC_PALETTE).slice(0,data.length), borderWidth:2, borderColor:'#fff', hoverOffset:4 }] },
      options: Object.assign({ responsive:true, maintainAspectRatio:true, cutout:'70%', plugins:{ legend:{ position:'bottom', labels:{ boxWidth:10, padding:14, font:{size:11} } }, tooltip:{ backgroundColor:'#1f2937', cornerRadius:8, padding:10 } } }, opts||{})
    });
  },

  horizontalBar: function(id, labels, data, color) {
    var el = document.getElementById(id); if (!el) return;
    return new Chart(el, {
      type: 'bar',
      data: { labels:labels, datasets:[{ data:data, backgroundColor:data.map(function(_,i){ return hexToRgb(color||TC_PALETTE.blue, 0.7+i*0.05); }), borderRadius:4, borderSkipped:false }] },
      options: { indexAxis:'y', responsive:true, maintainAspectRatio:true, plugins:{ legend:{display:false}, tooltip:{ backgroundColor:'#1f2937', cornerRadius:8, padding:10 } }, scales:{ x:{ beginAtZero:true, grid:{color:'#f3f4f6'}, ticks:{font:{size:11}} }, y:{ grid:{display:false}, ticks:{font:{size:11}} } } }
    });
  },

  spark: function(id, data, color) {
    var el = document.getElementById(id); if (!el) return;
    return new Chart(el, {
      type: 'line',
      data: { labels:data.map(function(_,i){return i;}), datasets:[{ data:data, borderColor:color||TC_PALETTE.blue, backgroundColor:hexToRgb(color||TC_PALETTE.blue,0.1), fill:true, tension:0.4, pointRadius:0, borderWidth:2 }] },
      options: { responsive:true, maintainAspectRatio:false, plugins:{legend:{display:false},tooltip:{enabled:false}}, scales:{ x:{display:false}, y:{display:false,beginAtZero:true} }, animation:{duration:1000} }
    });
  }
};

/* ── Animated progress rings ── */
function tcDrawRing(canvasId, pct, color) {
  var el = document.getElementById(canvasId); if (!el) return;
  var ctx = el.getContext('2d');
  var size = el.width, r = size/2 - 10, cx = size/2, cy = size/2;
  ctx.clearRect(0,0,size,size);
  // Track
  ctx.beginPath(); ctx.arc(cx,cy,r,0,Math.PI*2);
  ctx.strokeStyle='#f3f4f6'; ctx.lineWidth=10; ctx.lineCap='round'; ctx.stroke();
  // Fill
  var angle = (pct/100)*Math.PI*2 - Math.PI/2;
  ctx.beginPath(); ctx.arc(cx,cy,r,-Math.PI/2,angle);
  ctx.strokeStyle=color||TC_PALETTE.blue; ctx.lineWidth=10; ctx.lineCap='round'; ctx.stroke();
}

function tcAnimateRings() {
  document.querySelectorAll('[data-ring]').forEach(function(el) {
    var pct = parseInt(el.dataset.ring)||0;
    var color = el.dataset.color||TC_PALETTE.blue;
    var id = el.id;
    var start = null;
    function step(ts) {
      if (!start) start = ts;
      var prog = Math.min((ts-start)/800, 1);
      var ease = 1 - Math.pow(1-prog,3);
      tcDrawRing(id, pct*ease, color);
      if (prog < 1) requestAnimationFrame(step);
    }
    requestAnimationFrame(step);
  });
}

/* ── Funnel renderer ── */
function tcRenderFunnel(containerId, funnel) {
  var el = document.getElementById(containerId); if (!el) return;
  var stages = [
    { label:'Total Leads',   val: parseInt(funnel.total_leads||0),   color:TC_PALETTE.blue   },
    { label:'Interested',    val: parseInt(funnel.interested||0),     color:TC_PALETTE.teal   },
    { label:'Appointments',  val: parseInt(funnel.appointments||0),   color:TC_PALETTE.orange },
    { label:'Converted',     val: parseInt(funnel.converted||0),      color:TC_PALETTE.green  },
  ];
  var max = stages[0].val || 1;
  var html = '<div class="tc-funnel">';
  stages.forEach(function(s,i) {
    var w = Math.max((s.val/max)*100,2);
    var pct = i>0&&stages[0].val>0 ? Math.round(s.val/stages[0].val*100)+'%' : '100%';
    html += '<div class="tc-funnel-row">';
    html += '<div class="tc-funnel-label">'+s.label+'</div>';
    html += '<div class="tc-funnel-bar" style="width:'+w+'%;background:'+s.color+'"><span class="tc-funnel-cnt">'+s.val.toLocaleString()+'</span></div>';
    html += '<span class="tc-funnel-pct">'+pct+'</span>';
    html += '</div>';
  });
  html += '</div>';
  el.innerHTML = html;
}

/* ── Mini sparklines in KPI cards ── */
function tcRenderSparks(daily) {
  var calls = daily.map(function(d){return d.calls;});
  ['spark-calls','spark-appts','spark-convs'].forEach(function(id) {
    tcChart.spark(id, calls, TC_PALETTE.blue);
  });
}

/* ══════════════════════════════════════════════════
   ADMIN DASHBOARD INIT
══════════════════════════════════════════════════ */
function tcInitAdminDashboard(daily, funnel, agents, emailStats) {

  var labels = daily.map(function(d){return d.date;});
  var calls  = daily.map(function(d){return d.calls;});

  // Main area chart — daily calls
  tcChart.area('chart-daily', labels, [
    { label:'Calls Made', data:calls, borderColor:TC_PALETTE.blue, backgroundColor:hexToRgb(TC_PALETTE.blue,0.1) }
  ]);

  // Funnel
  tcRenderFunnel('tc-funnel-container', funnel);

  // Agent performance horizontal bar
  if (agents && agents.length) {
    var top8 = agents.slice(0,8);
    tcChart.horizontalBar('chart-agents',
      top8.map(function(a){return (a.firstname||'').charAt(0)+'.'+(a.lastname||'');}),
      top8.map(function(a){return parseInt(a.calls)||0;}),
      TC_PALETTE.blue
    );
  }

  // Email donut
  if (emailStats && parseInt(emailStats.total_sent)>0) {
    var sent = parseInt(emailStats.total_sent)||0;
    var opened = parseInt(emailStats.opened)||0;
    var bounced = parseInt(emailStats.bounced)||0;
    var unsub = parseInt(emailStats.unsubscribed)||0;
    var other = Math.max(sent - opened - bounced - unsub, 0);
    tcChart.doughnut('chart-email-donut',
      ['Opened','Bounced','Unsubscribed','Other'],
      [opened, bounced, unsub, other],
      [TC_PALETTE.green, TC_PALETTE.red, TC_PALETTE.orange, '#d1d5db']
    );
  }

  // Call status donut
  if (funnel) {
    tcChart.doughnut('chart-status-donut',
      ['Interested','Appointments','Converted','Other'],
      [parseInt(funnel.interested||0), parseInt(funnel.appointments||0), parseInt(funnel.converted||0), Math.max(parseInt(funnel.total_leads||0)-parseInt(funnel.interested||0),0)],
      [TC_PALETTE.yellow, TC_PALETTE.teal, TC_PALETTE.green, '#e5e7eb']
    );
  }

  // Animate rings
  tcAnimateRings();

  // Animate inline bars in agent table
  setTimeout(function() {
    document.querySelectorAll('.tc-mini-fill[data-w]').forEach(function(el) {
      el.style.width = el.dataset.w + '%';
    });
    document.querySelectorAll('.tc-bar-fill[data-w]').forEach(function(el) {
      el.style.width = el.dataset.w + '%';
    });
  }, 200);
}

/* ══════════════════════════════════════════════════
   AGENT DASHBOARD INIT
══════════════════════════════════════════════════ */
function tcInitAgentDashboard(daily, statusBreakdown) {
  var labels = daily.map(function(d){return d.date;});
  var calls  = daily.map(function(d){return d.calls;});

  tcChart.area('chart-daily', labels, [
    { label:'My Calls', data:calls, borderColor:TC_PALETTE.blue, backgroundColor:hexToRgb(TC_PALETTE.blue,0.1) }
  ]);

  if (statusBreakdown && statusBreakdown.length) {
    var colors = { not_answered:TC_PALETTE.indigo, callback_later:TC_PALETTE.yellow, interested:TC_PALETTE.teal, not_interested:TC_PALETTE.red, appointment_scheduled:TC_PALETTE.blue, converted:TC_PALETTE.green };
    tcChart.doughnut('chart-status',
      statusBreakdown.map(function(s){return (s.call_status||'').replace(/_/g,' ');}),
      statusBreakdown.map(function(s){return parseInt(s.cnt)||0;}),
      statusBreakdown.map(function(s){return colors[s.call_status]||TC_PALETTE.blue;})
    );
  }

  tcAnimateRings();
  setTimeout(function() {
    document.querySelectorAll('.tc-mini-fill[data-w],.tc-bar-fill[data-w]').forEach(function(el){ el.style.width=el.dataset.w+'%'; });
  }, 200);
}

/* ══════════════════════════════════════════════════
   GROUP LEADER DASHBOARD INIT
══════════════════════════════════════════════════ */
function tcInitGroupDashboard(daily, agents) {
  var labels = daily.map(function(d){return d.date;});
  tcChart.area('chart-daily', labels, [
    { label:'Team Calls', data:daily.map(function(d){return d.calls;}), borderColor:TC_PALETTE.teal, backgroundColor:hexToRgb(TC_PALETTE.teal,0.1) }
  ]);
  if (agents && agents.length) {
    var top6 = agents.slice(0,6);
    tcChart.bar('chart-group-agents',
      top6.map(function(a){return (a.firstname||'');}),
      [
        { label:'Calls', data:top6.map(function(a){return parseInt(a.calls)||0;}), backgroundColor:hexToRgb(TC_PALETTE.teal,0.8), borderRadius:5 },
        { label:'Conversions', data:top6.map(function(a){return parseInt(a.conversions)||0;}), backgroundColor:hexToRgb(TC_PALETTE.green,0.8), borderRadius:5 }
      ]
    );
  }
  tcAnimateRings();
  setTimeout(function(){ document.querySelectorAll('.tc-mini-fill[data-w],.tc-bar-fill[data-w]').forEach(function(el){el.style.width=el.dataset.w+'%';}); },200);
}

/* ══════════════════════════════════════════════════
   REPORTS PAGE INIT
══════════════════════════════════════════════════ */
function tcInitReports(agents, funnel) {
  if (agents && agents.length) {
    var top10 = agents.slice(0,10);
    tcChart.bar('chart-agents',
      top10.map(function(a){return (a.firstname||'')+' '+(a.lastname||'').charAt(0)+'.';}),
      [
        { label:'Calls', data:top10.map(function(a){return parseInt(a.calls)||0;}), backgroundColor:hexToRgb(TC_PALETTE.blue,0.85), borderRadius:4 },
        { label:'Appointments', data:top10.map(function(a){return parseInt(a.appointments)||0;}), backgroundColor:hexToRgb(TC_PALETTE.teal,0.85), borderRadius:4 },
        { label:'Conversions', data:top10.map(function(a){return parseInt(a.conversions)||0;}), backgroundColor:hexToRgb(TC_PALETTE.green,0.85), borderRadius:4 }
      ]
    );
  }
  tcRenderFunnel('tc-funnel-container', funnel||{});
}
