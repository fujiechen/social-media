const CHART_GREEN = 'rgba(140, 193, 82,1)';
const CHART_RED = 'rgba(218, 68, 83,1)';

export default {
  CHART: {
    GREEN: CHART_GREEN,
    RED: CHART_RED,
    OPTION: {
      colors: [CHART_GREEN, CHART_RED],
      chart: {
        animations: { enabled: false },
        toolbar: { show: false },
      },
      legend: {
        show: false,
        position: 'bottom',
      },
      grid: { show: false },
      xaxis: {
        labels: { show: false },
        axisBorder: { show: false },
        axisTicks: { show: false },
      },
      yaxis: { labels: { show: false } },
      dataLabels: { enabled: false },
      stroke: { width: 0 },
      tooltip: { enabled: false },
    },
  },
  LAST_DAYS: [30, 60, 90],
};
