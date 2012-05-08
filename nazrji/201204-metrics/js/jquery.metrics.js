function drawRgraph(type, target, data, labels, tooltips) {
  var graph;

  switch (type) {
    case 'bar':
      graph = new RGraph.Bar(target, data);
      break;
  }

  graph.Set('chart.background.grid', false);
  graph.Set('chart.gutter.left', 35);
  graph.Set('chart.colors', ['#1E3C7B']);
  graph.Set('chart.labels', labels);
  if (typeof tooltips != 'undefined') {
    graph.Set('chart.tooltips', tooltips);
    graph.Set('chart.tooltips.event', 'onmousemove');
  }
  graph.Draw();
}