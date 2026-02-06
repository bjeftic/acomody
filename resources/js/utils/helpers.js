import moment from "moment";

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
    display = "symbol",
) => {
    if (amount === null || amount === undefined || isNaN(amount)) {
        return "";
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
    let [integerPart, decimalPart] = fixed.split(".");

    integerPart = integerPart.replace(
        /\B(?=(\d{3})+(?!\d))/g,
        thousands_separator,
    );

    const formattedNumber =
        decimals > 0
            ? `${integerPart}${decimal_separator}${decimalPart}`
            : integerPart;

    let suffixOrPrefix = null;

    if (display === "symbol") {
        suffixOrPrefix = symbol;
    } else if (display === "code") {
        suffixOrPrefix = code;
    }

    if (!suffixOrPrefix || display === "none") {
        return formattedNumber;
    }

    return symbol_position === "before"
        ? `${suffixOrPrefix} ${formattedNumber}`
        : `${formattedNumber} ${suffixOrPrefix}`;
};

export const sortSearchResults = (results) => {
    let sortedResults = [];

    results.hits.forEach((hit) => {
        let res = {
            collection: results.request_params.collection_name,
            data: hit.document,
            text_match: hit.text_match,
            highlight: hit.highlight,
            highlights: hit.highlights,
        };

        sortedResults.push(res);
    });

    sortedResults.sort((a, b) => b.text_match - a.text_match);

    return sortedResults;
};

export const nil = (...params) => params.some((p) => p === undefined || p === null);

export const clone = (obj, propertyWhitelist = []) => {
    // propertyWhitelist allows us to specify a filter of fields we want cloned
    // but the filter is applied only to the top-level fields
    let copy;

    // Handle the simple types, and null or undefined
    if (nil(obj) || "object" !== typeof obj) {
        return obj;
    }

    // handle Moment.js
    if (moment.isMoment(obj)) {
        return moment(obj);
    }

    // Handle Date
    if (obj instanceof Date) {
        copy = new Date();
        copy.setTime(obj.getTime());
        return copy;
    }

    // handle Promise
    if (obj instanceof Promise) {
        return obj.then();
    }

    // Handle Array
    if (obj instanceof Array) {
        copy = [];
        for (let i = 0, len = obj.length; i < len; i++) {
            copy[i] = clone(obj[i]);
        }
        return copy;
    }

    // Handle Object
    if (obj instanceof Object) {
        copy = {};
        for (const attr in obj) {
            if (
                Object.prototype.hasOwnProperty.call(obj, attr) &&
                (propertyWhitelist.length === 0 ||
                    propertyWhitelist.includes(attr))
            ) {
                copy[attr] = clone(obj[attr]);
            }
        }
        return copy;
    }

    throw new Error("Unable to copy obj! Its type isn't supported.");
};
