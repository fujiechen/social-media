export const timestampToDatetime = (timestamp) => {
  const options = {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
    hour12: false
  };
  const date = new Date(timestamp);
  return date.toLocaleDateString('zh-CN', options);
};

export const secondsToDurationString = (seconds) => {
  const hours = Math.floor(seconds / 3600);
  const mins = Math.floor(seconds / 60 % 60).toString().padStart(2, '0');
  const secs = Math.floor(seconds % 60).toString().padStart(2, '0');
  if (hours) {
    return `${hours}:${mins}:${secs}`;
  }
  return `${mins}:${secs}`;
}

export const calculateSignificantDateDifference = (dateString) => {
  const givenDate = new Date(dateString);
  const today = new Date();

  const differenceInMs = today - givenDate;
  const differenceInDays = differenceInMs / (1000 * 60 * 60 * 24);
  const differenceInMonths = differenceInDays / 30;  // Approximation
  const differenceInYears = differenceInDays / 365;  // Approximation

  if (Math.abs(differenceInYears) >= 1) {
    return `${Math.round(differenceInYears)} 年`;
  } else if (Math.abs(differenceInMonths) >= 1) {
    return `${Math.round(differenceInMonths)} 月`;
  } else {
    return `${Math.round(differenceInDays)} 天`;
  }
}
