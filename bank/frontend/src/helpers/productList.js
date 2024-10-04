import ProductListConst from '@/constants/ProductList';

export const convertProductToListItem = (product) => {
  const { trend } = product;
  const nameColorClass = trend === 'up' ? ProductListConst.NAME_COLOR_CLASS_GREEN : ProductListConst.NAME_COLOR_CLASS_RED;
  const numberColorClass = trend === 'up' ? ProductListConst.NUMBER_COLOR_CLASS_GREEN : ProductListConst.NUMBER_COLOR_CLASS_RED;
  const chartColor = trend === 'up' ? ProductListConst.CHART_COLOR_GREEN : ProductListConst.CHART_COLOR_RED;
  const chartSeries = product.product_rates.map((productRate) => productRate.value);
  return {
    id: product.id,
    title: product.title,
    nameColorClass,
    currencySymbol: product.currency.name,
    startAmount: product.start_amount,
    numberColorClass,
    freezeDays: product.freeze_days,
    rate: product.estimate_rate,
    chartMetaData: {
      height: 80,
      width: 150,
      type: 'area',
      chartOptions: {
        colors: [chartColor],
        chart: {
          toolbar: {
            show: false,
          },
        },
        grid: {
          show: false,
        },
        xaxis: {
          labels: {
            show: false,
          },
          axisBorder: {
            show: false,
          },
          axisTicks: {
            show: false,
          },
        },
        yaxis: {
          labels: {
            show: false,
          },
        },
        dataLabels: {
          enabled: false,
        },
        stroke: {
          width: 1,
        },
        tooltip: {
          enabled: false,
        },
      },
      chartSeries: [{
        name: 'series1',
        data: chartSeries,
      }],
    },
  };
};

export const convertUserProductToListItem = (userProduct) => {
  const userOrder = userProduct.user_order;
  const { product } = userOrder;
  const { currency } = product;

  const nameColorClass = userProduct.trend === 'up' ? ProductListConst.NAME_COLOR_CLASS_GREEN : ProductListConst.NAME_COLOR_CLASS_RED;
  const numberColorClass = userProduct.trend === 'up' ? ProductListConst.NUMBER_COLOR_CLASS_GREEN : ProductListConst.NUMBER_COLOR_CLASS_RED;
  const chartColor = userProduct.trend === 'up' ? ProductListConst.CHART_COLOR_GREEN : ProductListConst.CHART_COLOR_RED;
  const chartSeries = product.product_rates.map((productRate) => productRate.value);
  return {
    id: userProduct.id,
    title: product.title,
    name: product.name,
    nameColorClass,
    currencySymbol: currency.name,
    releaseDate: userOrder.release_at,
    isActive: userProduct.is_active,
    totalMarketValue: userProduct.total_market_value,
    totalBookCost: userProduct.total_book_cost,
    totalIncreaseRate: userProduct.total_increase_rate,
    finalMarketValue: userProduct.final_market_value,
    finalBookCost: userProduct.final_book_cost,
    finalIncreaseRate: userProduct.final_increase_rate,
    numberColorClass,
    rate: product.estimate_rate,
    chartMetaData: {
      height: 80,
      width: 150,
      type: 'area',
      chartOptions: {
        colors: [chartColor],
        chart: {
          toolbar: {
            show: false,
          },
        },
        grid: {
          show: false,
        },
        xaxis: {
          labels: {
            show: false,
          },
          axisBorder: {
            show: false,
          },
          axisTicks: {
            show: false,
          },
        },
        yaxis: {
          labels: {
            show: false,
          },
        },
        dataLabels: {
          enabled: false,
        },
        stroke: {
          width: 1,
        },
        tooltip: {
          enabled: false,
        },
      },
      chartSeries: [{
        name: 'series1',
        data: chartSeries,
      }],
    },
  };
};

export const convertInvestProductListItemToInvestHistoryPopup = (listItem) => ({
  id: listItem.id,
  productInfo: {
    title: listItem.title,
    name: listItem.name,
    isActive: listItem.isActive,
    releaseDate: listItem.releaseDate,
    totalBookCost: listItem.isActive ? listItem.totalBookCost : listItem.finalBookCost,
    totalMarketValue: listItem.isActive ? listItem.totalMarketValue : listItem.finalMarketValue,
    currencySymbol: listItem.currencySymbol,
    currencySymbolColor: listItem.nameColorClass,
    numberColorClass: listItem.numberColorClass,
  },
});
