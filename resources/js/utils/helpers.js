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
