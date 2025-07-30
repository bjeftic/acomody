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
}
