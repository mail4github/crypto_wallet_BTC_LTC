// many graphs
const createChart = (id, data, isPositive) => {
    const ctx = document.getElementById(id).getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, 50);
    gradient.addColorStop(0, isPositive ? 'rgba(0, 255, 135, 0.4)' : 'rgba(255, 77, 77, 0.4)');
    gradient.addColorStop(1, 'rgba(0, 0, 0, 0)');
  
    new Chart(ctx, {
        type: 'line',
        data: {
          labels: Array(data.length).fill(''),
          datasets: [
            {
              data,
              borderColor: isPositive ? '#7AC231' : '#FF173D',
              borderWidth: 2,
              tension: 0.2, // Небольшое скругление
              pointRadius: 0,
              fill: false, // Убираем фон
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { display: false } },
          scales: {
            x: { display: false },
            y: { display: false },
          },
        },
      });
  };
  
  // Example data
  createChart('chart-ethereum', [30, 28, 5, 40, 10, 39, 10], false);
  createChart('chart-bitcoin', [50, 48, 45, 52, 12, 35, 4], false);
  createChart('chart-litecoin', [10, 48, 20, 25, 4, 35, 2], true);
  createChart('chart-solana', [30, 28, 5, 40, 10, 39, 10], false);
  createChart('chart-binance', [50, 48, 45, 52, 12, 35, 4], true);
  createChart('chart-ripple', [10, 48, 20, 25, 4, 35, 2], true);

const ethereumData = [3000, 1200, 3100, 2150, 3120, 3080, 3070, 1005, 1990, 2150, 3500];
new Chart(document.getElementById("chart1").getContext("2d"), {
  type: "line",
  data: {
    labels: ethereumData.map((_, i) => i), 
    datasets: [
      {
        data: ethereumData,
        borderColor: "#7AC231",
        borderWidth: 2,
        tension: 0,
        pointRadius: (ctx) => (ctx.dataIndex === ethereumData.length - 1 ? 3 : 0), // Только последняя точка
        pointBackgroundColor: (ctx) =>
          ctx.dataIndex === ethereumData.length - 1 ? "#7AC231" : "transparent",
        pointBorderWidth: (ctx) => (ctx.dataIndex === ethereumData.length - 1 ? 8 : 0),
        pointBorderColor: (ctx) =>
          ctx.dataIndex === ethereumData.length - 1 ? "rgba(122, 194, 49, 0.5)" : "transparent",
        fill: false,
      },
    ],
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
      x: { display: false },
      y: { display: false },
    },
  },
});

// Bitcoin chart
const bitcoinData = [3100, 3200, 3000, 3050, 3100, 3150, 3200, 3180, 3120, 3090, 3070];
new Chart(document.getElementById("chart2").getContext("2d"), {
  type: "line",
  data: {
    labels: bitcoinData.map((_, i) => i),
    datasets: [
      {
        data: bitcoinData,
        borderColor: "#FF173D",
        borderWidth: 2,
        tension: 0,
        pointRadius: (ctx) => (ctx.dataIndex === bitcoinData.length - 1 ? 3 : 0),
        pointBackgroundColor: (ctx) =>
          ctx.dataIndex === bitcoinData.length - 1 ? "#FF173D" : "transparent",
        pointBorderWidth: (ctx) => (ctx.dataIndex === bitcoinData.length - 1 ? 8 : 0),
        pointBorderColor: (ctx) =>
          ctx.dataIndex === bitcoinData.length - 1 ? "rgba(255, 23, 61, 0.5)" : "transparent",
        fill: false,
      },
    ],
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
      x: { display: false },
      y: { display: false },
    },
  },
});

const litecoinData = [120, 230, 140, 150, 145, 248, 165, 360, 265, 170, 275];
new Chart(document.getElementById("chart3").getContext("2d"), {
  type: "line",
  data: {
    labels: litecoinData.map((_, i) => i),
    datasets: [
      {
        data: litecoinData,
        borderColor: "#7AC231",
        borderWidth: 2,
        tension: 0,
        pointRadius: (ctx) => (ctx.dataIndex === litecoinData.length - 1 ? 3 : 0),
        pointBackgroundColor: (ctx) =>
          ctx.dataIndex === litecoinData.length - 1 ? "#7AC231" : "transparent",
        pointBorderWidth: (ctx) => (ctx.dataIndex === litecoinData.length - 1 ? 8 : 0),
        pointBorderColor: (ctx) =>
          ctx.dataIndex === litecoinData.length - 1 ? "rgba(122, 194, 49, 0.5)" : "transparent",
        fill: false,
      },
    ],
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
      x: { display: false },
      y: { display: false },
    },
  },
});

