/**
 * url中params参数转换object
 * @param {string} url
 * @returns {Object}
 */
export function param2Obj(url) {
  const search = url.split("?")[1];
  if (!search) {
    return {};
  }
  return JSON.parse(
    `{"${decodeURIComponent(search)
      .replace(/"/g, '\\"')
      .replace(/&/g, '","')
      .replace(/=/g, '":"')
      .replace(/\+/g, " ")}"}`
  );
}

/**
 * 获取url中参数
 * @param {string} url
 * @param {string} queryKey
 * @returns {string}
 */
export function getQueryString(url, queryKey) {
  const reg = new RegExp(`&{1}${queryKey}\\=[a-zA-Z0-9_-]+`, "g");
  const matchResult = url.replace(/\?/g, "&").match(reg)[0];
  return matchResult.substr(matchResult.indexOf("=") + 1);
}

export function getDateTimeDiff(dateTime) {
  const minute = 1000 * 60;
  const hour = minute * 60;
  const day = hour * 24;
  const week = day * 7;
  const month = day * 30;

  // 获取当前时间并转换为时间戳，方便计算
  const timestampCurrent = new Date().getTime();

  // 将传入的时间格式字符串解析为Date对象
  const _date = new Date(dateTime);

  // 将Date对象转换为时间戳，方便计算
  const timestampInput = _date.getTime();

  // 计算当前时间与传入的时间之间相差的时间戳
  const timestampDiff = timestampCurrent - timestampInput;

  // 计算时间差共有多少个分钟
  const minC = timestampDiff / minute;
  // 计算时间差共有多少个小时
  const hourC = timestampDiff / hour;
  // 计算时间差共有多少个天
  const dayC = timestampDiff / day;
  // 计算时间差共有多少个周
  const weekC = timestampDiff / week;
  // 计算时间差共有多少个月
  const monthC = timestampDiff / month;

  if (monthC >= 1 && monthC < 4) {
    return parseInt(monthC) + "月前";
  } else if (weekC >= 1 && weekC < 4) {
    return parseInt(weekC) + "周前";
  } else if (dayC >= 1 && dayC < 7) {
    return parseInt(dayC) + "天前";
  } else if (hourC >= 1 && hourC < 24) {
    return parseInt(hourC) + "小时前";
  } else if (minC >= 1 && minC < 60) {
    return parseInt(minC) + "分钟前";
  } else if (timestampDiff >= 0 && timestampDiff <= minute) {
    // 时间差大于0并且小于1分钟
    return "刚刚";
  } else {
    return (
      _date.getFullYear() +
      "年" +
      _date.getMonth() +
      "月" +
      _date.getDate() +
      "日"
    );
  }
}

export function processParamsObjectToString(params = null) {
  let paramsString = '';
  if (params) {
    const paramsObj = new URLSearchParams();
    Object.entries(params).forEach(([key, value]) => {
      paramsObj.append(key, value);
    });
    paramsString = paramsObj.toString();
  }
  return paramsString;
}

export function convertProductUserTypeToHumanReadable(productUserType) {
  switch (productUserType) {
    case 'self':
      return '官方';
    case 'user':
      return '用户';
    default:
      return '';
  }
}

export function convertProductTypeToHumanReadable(productType) {
  switch (productType) {
    case 'membership':
      return 'VIP会员';
    case 'subscription':
      return '关注';
    case 'media':
      return '媒体';
    case 'general':
      return '通用';
    default:
      return '';
  }
}

export function convertProductCurrencyNameToHumanReadable(currencyName) {
  switch (currencyName) {
    case 'CNY':
      return '人民币';
    case 'COIN':
      return '积分';
    default:
      return '';
  }
}

export function formatNumberToChineseDecimal(num) {
  if (!num || Number.isNaN(num)) {
    return num;
  }

  let unit = '';
  let number = num;

  // Convert the number based on its size
  if (num >= 100000000) {
    number = num / 100000000;
    unit = '亿';
  } else if (num >= 10000000) {
    number = num / 10000000;
    unit = '千万';
  } else if (num >= 1000000) {
    number = num / 1000000;
    unit = '百万';
  } else if (num >= 10000) {
    number = num / 10000;
    unit = '万';
  }

  let numberStr = number;

  if (number % 1 !== 0) {
    numberStr = number.toFixed(2);
    numberStr = numberStr.replace(/\.?0+$/, "");
  }

  return numberStr + unit;
}
