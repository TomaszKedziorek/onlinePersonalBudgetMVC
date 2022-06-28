function drawChart(userData, canvasID, title) {
  const objectUserData = userData;
  function createLabels(obj) {
    labels = []
    for (let i = 0; i < obj.length; i++) {
      labels[i] = obj[i]['name'];
    }
    return labels;
  }
  function createColors(arr) {
    colors = []
    for (let i = 0; i < arr.length; i++) {
      const r = Math.floor(Math.random() * 255);
      const g = Math.floor(Math.random() * 255);
      const b = Math.floor(Math.random() * 255);
      colors[i] = 'rgba(' + r + ',' + g + ',' + b + ',' + '1)';
    }
    return colors;
  }
  const dataLabels = createLabels(objectUserData);
  const dataColors = createColors(objectUserData);
  const dataPlugin = {
    id: 'dataPlugin',
    legend: { position: 'right', fullSize: true, labels: { boxWidth: 16, color: 'white' } },
    title: { display: true, text: title, color: 'white', font: { size: 30 } }
  };

  //DATA
  const chartData = {
    labels: dataLabels,
    datasets: [{
      label: 'Data',
      data: objectUserData,
      backgroundColor: dataColors,
      borderColor: 'rgba(3,13,33,1)',
      borderWidth: 1
    }]
  };
  //CONFIG
  const chartConfig = {
    type: 'doughnut',
    data: chartData,
    options: {
      parsing: {
        key: 'amount'
      },
      plugins: dataPlugin,
      responsive: false
    }
  };

  Chart.defaults.font.size = 16;
  Chart.defaults.font.family = "'Times New Roman', 'Times', 'Georgia', serif";
  new Chart(
    document.getElementById(canvasID),
    chartConfig
  );
}