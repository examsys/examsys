function drawRgraph(type, target, data, labels, tooltips) {
  var graph;

  switch (type) {
    case 'bar':
      graph = new RGraph.Bar(target, data);
      graph.Set('chart.background.grid', false);
      graph.Set('chart.colors', ['#1E3C7B']);
      graph.Set('chart.gutter.left', 35);
      graph.Set('chart.labels', labels);
      break;
    case 'pie':
      graph = new RGraph.Pie(target, data);
      graph.Set('chart.key', labels);
      graph.Set('chart.key.interactive', true);
      graph.Set('chart.linewidth', 5);
      graph.Set('chart.stroke', 'red');
      graph.Set('chart.highlight.style', '3d');
      graph.Set('chart.colors', ['#23D74C', '#4A8FF8', '#C5E903', '#6A3E3C', '#CB3542', '#C650C9', '#613D13', '#5C7361', '#6AA80C', '#5787AB', '#972C1D', '#67D84D', '#A901BE', '#110B94']);
      break;
  }

  if (typeof tooltips != 'undefined') {
    graph.Set('chart.tooltips', tooltips);
    graph.Set('chart.tooltips.event', 'onmousemove');
  }
  graph.Draw();
}