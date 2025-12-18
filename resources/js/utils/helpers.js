export const toCamelCase = (str) => {
    if (!str) {
        return "";
    }
    return str.replace(/^([A-Z])|[\s-_](\w)/g, function (g) {
        return g[1].toUpperCase();
    });
};

export const toSnakeCase = (str) => {
    if (!str) {
        return "";
    }
    return str
        .replace(/([A-Z])/g, " $1")
        .split(" ")
        .join("_")
        .toLowerCase();
};

export const handle422ValidationErrors = (data) => {
    if (data.errors && typeof data.errors === "object") {
        const fieldErrors = {};
        Object.keys(data.errors).forEach((field) => {
            const fieldName = toCamelCase(field);
            const errors = Array.isArray(data.errors[field])
                ? data.errors[field]
                : [data.errors[field]];
            fieldErrors[fieldName] = errors.join("; ");
        });
        return fieldErrors;
    }
    return {};
};

export const formatPrice = (
  amount,
  currency,
  showDecimals = true,
  display = 'symbol'
) => {
  if (amount === null || amount === undefined || isNaN(amount)) {
    return '';
  }

  if (!currency) {
    return String(amount);
  }

  const {
    symbol,
    code,
    symbol_position,
    decimal_places,
    thousands_separator,
    decimal_separator,
  } = currency;

  const number = Number(amount);
  const decimals = showDecimals ? decimal_places : 0;

  const fixed = number.toFixed(decimals);
  let [integerPart, decimalPart] = fixed.split('.');

  integerPart = integerPart.replace(
    /\B(?=(\d{3})+(?!\d))/g,
    thousands_separator
  );

  const formattedNumber =
    decimals > 0
      ? `${integerPart}${decimal_separator}${decimalPart}`
      : integerPart;

  let suffixOrPrefix = null;

  if (display === 'symbol') {
    suffixOrPrefix = symbol;
  } else if (display === 'code') {
    suffixOrPrefix = code;
  }

  if (!suffixOrPrefix || display === 'none') {
    return formattedNumber;
  }

  return symbol_position === 'before'
    ? `${suffixOrPrefix} ${formattedNumber}`
    : `${formattedNumber} ${suffixOrPrefix}`;
};
