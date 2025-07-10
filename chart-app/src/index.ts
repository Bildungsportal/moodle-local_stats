import {ChartCallback, ChartJSNodeCanvas} from 'chartjs-node-canvas';
import {ChartConfiguration} from 'chart.js';
import {promises as fs} from 'fs';

let inputData = '';

process.stdin.setEncoding('utf8');

if (process.stdin.isTTY) {
  throw new Error('No Data piped into the script!');
}

process.stdin.on('data', function (chunk) {
  inputData += chunk;
});

process.stdin.on('end', function () {
  if (inputData.length == 0) {
    throw new Error('No data received.');
  }

  let jsonData = JSON.parse(inputData);
  if (!jsonData) {
    throw new Error('Invalid JSON data.');
  }

  run(jsonData);
});

// const configuration2: ChartConfiguration = {
//   type: 'bar',
//   data: {
//     labels: ['Red', 'Blue', 'Yellow', 'Green', 'Purple', 'Orange'],
//     datasets: [{
//       label: '# of Votes',
//       data: [12, 19, 3, 5, 2, 3],
//       backgroundColor: [
//         'rgba(255, 99, 132, 0.2)',
//         'rgba(54, 162, 235, 0.2)',
//         'rgba(255, 206, 86, 0.2)',
//         'rgba(75, 192, 192, 0.2)',
//         'rgba(153, 102, 255, 0.2)',
//         'rgba(255, 159, 64, 0.2)'
//       ],
//       borderColor: [
//         'rgba(255,99,132,1)',
//         'rgba(54, 162, 235, 1)',
//         'rgba(255, 206, 86, 1)',
//         'rgba(75, 192, 192, 1)',
//         'rgba(153, 102, 255, 1)',
//         'rgba(255, 159, 64, 1)'
//       ],
//       borderWidth: 1
//     }]
//   },
// };
//
// const configuration: ChartConfiguration = {
//   "type": "bar",
//   "data": {
//     datasets: [
//       {
//         "label": "test",
//         "data": [
//           82,
//           125,
//           38,
//           148,
//           18,
//           133,
//           189,
//           262,
//           93,
//           56,
//           401,
//           190,
//           341
//         ],
//         backgroundColor: [
//           "#ff7f0e",
//         ],
//       }
//     ],
//     "labels": [
//       "2024-01",
//       "2024-02",
//       "2024-03",
//       "2024-04",
//       "2024-05",
//       "2024-06",
//       "2024-07",
//       "2024-08",
//       "2024-09",
//       "2024-10",
//       "2024-12",
//       "2024-13",
//       "2024-14"
//     ],
//   },
// }

async function run(data): Promise<void> {

  const {chart, output_file, width, height} = data;

  let type = chart.type;
  if (type === 'pie' && chart.doughnut) {
    type = 'doughnut';
  }

  let configuration: ChartConfiguration = {
    type,
    data: {
      datasets: chart.series.map((series, series_i) => {
        return ({
          data: series.values,

          backgroundColor:
            series.fill
            ?? ((type == 'pie' || type == 'doughnut')
              ? chart.config_colorset
              : chart.config_colorset[series_i + 1])
            ?? "#ff7f0e",
          borderColor: (type == 'line')
            ? chart.config_colorset[series_i + 1] ?? "#ff7f0e"
            : undefined,

          // smooth interpolation
          tension: (series.smooth ?? chart.smooth) ? 0.4 : 0,

          ...series,
          // label: series.label || chart.title,
        });
      }),
      "labels": chart.labels,
    },
    options: {
      indexAxis: chart.horizontal ? 'y' : undefined,
      plugins: {
        legend: chart.legend_options || {},
        title: {
          display: true,
          text: chart.title,
        }
      },
      scales: (type == 'pie' || type == 'doughnut')
        ? {}
        : {
          x: {
            stacked: !!chart.stacked,
          },
          y: {
            stacked: !!chart.stacked,
          }
        }
    },
    plugins: [{
      // fill the background white (and not black by default)
      id: 'background-color',
      beforeDraw: (chart) => {
        const ctx = chart.ctx;
        ctx.save();
        ctx.fillStyle = 'white';
        ctx.fillRect(0, 0, chart.width, chart.height);
        ctx.restore();
      }
    }]
  }

  configuration = {...chart, ...configuration};

  const chartCallback: ChartCallback = (ChartJS) => {
    // ChartJS.defaults.responsive = true;
    // ChartJS.defaults.maintainAspectRatio = false;
  };

  try {
    const chartJSNodeCanvas = new ChartJSNodeCanvas({width, height, chartCallback});
    const buffer = await chartJSNodeCanvas.renderToBuffer(configuration);
    await fs.writeFile(output_file, buffer, 'base64');
  } catch (e) {
    console.error(e);
    process.exit(1);
  }

  console.log(`${output_file} written to file system.`);
}
